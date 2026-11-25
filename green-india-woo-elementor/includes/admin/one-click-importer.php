<?php
if ( ! defined( 'ABSPATH' ) ) exit;

if ( ! function_exists( 'gi_run_full_import' ) ) {
function gi_run_full_import() {
    $seed_file      = GI_WOO_PATH . 'data/products-seed.json';
    $templates_file = GI_WOO_PATH . 'data/elementor-templates.json';
    $products_data  = file_exists( $seed_file ) ? json_decode( file_get_contents( $seed_file ), true ) : [];
    $templates_data = file_exists( $templates_file ) ? json_decode( file_get_contents( $templates_file ), true ) : [];

    require_once ABSPATH . 'wp-admin/includes/image.php';
    require_once ABSPATH . 'wp-admin/includes/file.php';
    require_once ABSPATH . 'wp-admin/includes/media.php';

    // 1. Upload all 9 .webp images into WordPress Media Library & /uploads/green-india/
    $upload_dir = wp_upload_dir();
    $gi_dir     = trailingslashit( $upload_dir['basedir'] ) . 'green-india/';
    wp_mkdir_p( $gi_dir );

    $img_dir = GI_WOO_PATH . 'assets/img/';
    $media_map = [];
    if ( is_dir( $img_dir ) ) {
        foreach ( glob( $img_dir . '*.webp' ) as $filepath ) {
            $filename = basename( $filepath );
            @copy( $filepath, $gi_dir . $filename );
            $existing = get_posts( [ 'post_type' => 'attachment', 'meta_key' => '_gi_asset_name', 'meta_value' => $filename, 'numberposts' => 1 ] );
            if ( ! empty( $existing ) ) {
                $media_map[ $filename ] = $existing[0]->ID;
                continue;
            }
            $upload = wp_upload_bits( $filename, null, file_get_contents( $filepath ) );
            if ( empty( $upload['error'] ) ) {
                $attach_id = wp_insert_attachment( [
                    'post_mime_type' => 'image/webp',
                    'post_title'     => sanitize_file_name( $filename ),
                    'post_status'    => 'inherit',
                ], $upload['file'] );
                wp_update_attachment_metadata( $attach_id, wp_generate_attachment_metadata( $attach_id, $upload['file'] ) );
                update_post_meta( $attach_id, '_gi_asset_name', $filename );
                $media_map[ $filename ] = $attach_id;
            }
        }
    }

    // 2. Import WooCommerce Products with MRP (regular_price) & Actual Selling Price (sale_price / price)
    $created_products = 0;
    if ( class_exists( 'WC_Product_Simple' ) && is_array( $products_data ) ) {
        foreach ( $products_data as $item ) {
            $existing_id = wc_get_product_id_by_sku( $item['sku'] );
            $product = $existing_id ? wc_get_product( $existing_id ) : new WC_Product_Simple();
            $product->set_name( $item['name'] );
            $product->set_sku( $item['sku'] );
            $actual = floatval( $item['basePrice'] );
            $mrp    = floatval( ! empty( $item['mrpPrice'] ) ? $item['mrpPrice'] : $actual );
            if ( $mrp > $actual && $actual > 0 ) {
                $product->set_regular_price( (string) $mrp );
                $product->set_sale_price( (string) $actual );
                $product->set_price( (string) $actual );
            } else {
                $product->set_regular_price( (string) $actual );
                $product->set_sale_price( '' );
                $product->set_price( (string) $actual );
            }
            $product->set_short_description( $item['shortDesc'] );
            $product->set_description( $item['longDesc'] ?? $item['shortDesc'] );
            $product->set_manage_stock( true );
            $product->set_stock_quantity( intval( $item['stockQty'] ) );
            $product->set_stock_status( $item['stockStatus'] );
            $product->set_status( 'publish' );
            if ( ! empty( $media_map[ $item['exportImageFilename'] ] ) ) {
                $product->set_image_id( $media_map[ $item['exportImageFilename'] ] );
            }
            $pid = $product->save();

            wp_set_object_terms( $pid, $item['category'], 'product_cat' );
            update_post_meta( $pid, '_gi_mrp_price', (string) $mrp );
            update_post_meta( $pid, '_gi_total_stock_qty', (string) intval( $item['totalStockQty'] ?? $item['stockQty'] ) );
            update_post_meta( $pid, '_gi_package_unit', $item['packageUnit'] );
            update_post_meta( $pid, '_gi_package_label', $item['packageLabel'] );
            update_post_meta( $pid, '_gi_max_order_qty', $item['maxOrderQty'] );
            update_post_meta( $pid, '_gi_bulk_negotiable', $item['bulkNegotiable'] ? 'yes' : 'no' );
            update_post_meta( $pid, '_gi_sizes_json', wp_json_encode( $item['sizes'] ) );
            update_post_meta( $pid, '_gi_colors_json', wp_json_encode( $item['colors'] ) );
            update_post_meta( $pid, '_gi_qualities_json', wp_json_encode( $item['qualities'] ) );
            update_post_meta( $pid, '_gi_highlights', implode( "\n", $item['highlights'] ) );
            update_post_meta( $pid, '_gi_material_note', $item['materialNote'] ?? 'Compostable, starch-based' );
            update_post_meta( $pid, '_gi_standard_note', $item['standardNote'] ?? 'EN13432 / ISO 17088' );
            update_post_meta( $pid, '_gi_also_known_as', $item['alsoKnownAs'] ?? '' );
            update_post_meta( $pid, '_gi_size_thickness_note', $item['sizeAndThicknessNote'] ?? 'Standard sizes and thickness are mentioned in the table above, but we can also customise based on your requirement.' );
            update_post_meta( $pid, '_gi_storage_note', $item['storageNote'] );
            $created_products++;
        }
    }

    // 3. Create WordPress Pages with Native Elementor Data
    $page_ids = [];
    if ( is_array( $templates_data ) ) {
        foreach ( $templates_data as $tpl ) {
            if ( in_array( $tpl['pageType'], [ 'header', 'footer' ], true ) ) continue;
            $existing_page = get_page_by_path( $tpl['slug'] );
            $page_id = $existing_page ? $existing_page->ID : wp_insert_post( [
                'post_title'  => str_replace( 'Green India - ', '', $tpl['title'] ),
                'post_name'   => $tpl['slug'],
                'post_status' => 'publish',
                'post_type'   => 'page',
            ] );
            if ( $page_id ) {
                $page_ids[ $tpl['slug'] ] = $page_id;
                update_post_meta( $page_id, '_elementor_edit_mode', 'builder' );
                update_post_meta( $page_id, '_elementor_template_type', 'wp-page' );
                update_post_meta( $page_id, '_elementor_version', '3.25.0' );
                update_post_meta( $page_id, '_elementor_data', wp_slash( wp_json_encode( $tpl['content'] ) ) );
                if ( $tpl['slug'] === 'home' ) {
                    update_option( 'show_on_front', 'page' );
                    update_option( 'page_on_front', $page_id );
                }
                if ( $tpl['slug'] === 'shop' ) {
                    update_option( 'woocommerce_shop_page_id', $page_id );
                }
            }
        }
    }

    // 4. Create Navigation Menu matching greenindiabags.com (Home, About Us, Our Products + 7 dropdowns, Shop, Certification, Contact)
    $menu_name = 'Green India Main Menu';
    $menu_obj  = wp_get_nav_menu_object( $menu_name );
    $menu_id   = $menu_obj ? $menu_obj->term_id : wp_create_nav_menu( $menu_name );
    if ( ! is_wp_error( $menu_id ) && empty( wp_get_nav_menu_items( $menu_id ) ) ) {
        $add_item = function( $title, $slug, $parent = 0 ) use ( $menu_id, $page_ids ) {
            $pid = $page_ids[ $slug ] ?? 0;
            return wp_update_nav_menu_item( $menu_id, 0, [
                'menu-item-title'     => $title,
                'menu-item-object'    => $pid ? 'page' : 'custom',
                'menu-item-object-id' => $pid,
                'menu-item-type'      => $pid ? 'post_type' : 'custom',
                'menu-item-url'       => $pid ? get_permalink( $pid ) : home_url( '/' . $slug . '/' ),
                'menu-item-status'    => 'publish',
                'menu-item-parent-id' => $parent,
            ] );
        };
        $add_item( 'Home', 'home' );
        $add_item( 'About Us', 'about-us' );
        $our_products_item_id = $add_item( 'Our Products', 'our-products' );
        $add_item( 'Shopping / Carry Bags', 'carry-bags', $our_products_item_id );
        $add_item( 'Sambar / Pouch Cover', 'sambar-cover', $our_products_item_id );
        $add_item( 'Grocery Bags', 'grocery-bags', $our_products_item_id );
        $add_item( 'Transparent Packaging Bags', 'transparent-bags', $our_products_item_id );
        $add_item( 'Garbage Bags', 'garbage-bags', $our_products_item_id );
        $add_item( 'Textile Bag / D-Cut Bag', 'd-cut', $our_products_item_id );
        $add_item( 'Flat Cover', 'flat-cover', $our_products_item_id );
        $add_item( 'Shop', 'shop' );
        $add_item( 'Certification', 'certification' );
        $add_item( 'Contact', 'contact' );

        $locations = get_theme_mod( 'nav_menu_locations', [] );
        $locations['primary'] = $menu_id;
        set_theme_mod( 'nav_menu_locations', $locations );
    }

    return [ 'products' => $created_products ];
}
}
