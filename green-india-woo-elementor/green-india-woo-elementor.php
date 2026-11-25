<?php
/**
 * Plugin Name: Green India WooCommerce & Elementor Suite
 * Description: Dynamic WooCommerce shop grid, stock/price/size/color/package/pieces manager, and native Elementor widgets matching greenindiabags.com.
 * Version: 1.0.0
 * Author: Green India Private Limited
 * Text Domain: green-india-woo
 * Requires at least: 6.0
 * Requires PHP: 7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! defined( 'GI_WOO_VERSION' ) ) define( 'GI_WOO_VERSION', '1.0.0' );
if ( ! defined( 'GI_WOO_PATH' ) ) define( 'GI_WOO_PATH', plugin_dir_path( __FILE__ ) );
if ( ! defined( 'GI_WOO_URL' ) ) define( 'GI_WOO_URL', plugin_dir_url( __FILE__ ) );

if ( ! class_exists( 'Green_India_Woo_Elementor' ) ) {
final class Green_India_Woo_Elementor {
    private static $instance = null;

    public static function instance() {
        if ( null === self::$instance ) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    private function __construct() {
        add_action( 'wp_enqueue_scripts', [ $this, 'enqueue_assets' ] );
        add_action( 'admin_enqueue_scripts', [ $this, 'enqueue_admin_assets' ] );

        // Elementor integration
        add_action( 'elementor/elements/categories_registered', [ $this, 'register_elementor_category' ] );
        add_action( 'elementor/widgets/register', [ $this, 'register_elementor_widgets' ] );

        // WooCommerce Product Data Tab & Fields (Stock, Price, Size, Color, Package, Pieces)
        add_filter( 'woocommerce_product_data_tabs', [ $this, 'add_product_data_tab' ] );
        add_action( 'woocommerce_product_data_panels', [ $this, 'render_product_data_panel' ] );
        add_action( 'woocommerce_process_product_meta', [ $this, 'save_product_data_meta' ] );

        // Admin Quick Stock / Price / Size / Color / Package / Pieces Manager + One-Click Importer
        add_action( 'admin_menu', [ $this, 'register_admin_menu' ] );
        add_action( 'admin_post_gi_quick_save_product', [ $this, 'handle_quick_save_product' ] );
        add_action( 'admin_post_gi_quick_delete_product', [ $this, 'handle_quick_delete_product' ] );
        add_action( 'admin_post_gi_quick_add_product', [ $this, 'handle_quick_add_product' ] );
        add_action( 'admin_post_gi_run_one_click_import', [ $this, 'handle_one_click_import' ] );

        // AJAX Add to WooCommerce Cart with custom Size/Color/Quality/Pieces metadata
        add_action( 'wp_ajax_gi_add_to_woo_cart', [ $this, 'ajax_add_to_woo_cart' ] );
        add_action( 'wp_ajax_nopriv_gi_add_to_woo_cart', [ $this, 'ajax_add_to_woo_cart' ] );
        add_filter( 'woocommerce_get_item_data', [ $this, 'display_cart_item_meta' ], 10, 2 );
        add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'save_order_item_meta' ], 10, 4 );
    }

    public function enqueue_assets() {
        wp_enqueue_style(
            'gi-google-fonts',
            'https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap',
            [],
            null
        );
        wp_enqueue_style( 'gi-shop-css', GI_WOO_URL . 'assets/css/gi-shop.css', [], GI_WOO_VERSION );
        wp_enqueue_script( 'gi-shop-js', GI_WOO_URL . 'assets/js/gi-shop.js', [ 'jquery' ], GI_WOO_VERSION, true );
        wp_localize_script( 'gi-shop-js', 'giWooConfig', [
            'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
            'nonce'       => wp_create_nonce( 'gi_woo_nonce' ),
            'whatsapp'    => '919489465676',
            'currency'    => '₹',
            'gstRate'     => 0.18,
            'cartUrl'     => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '/cart/',
            'checkoutUrl' => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : '/checkout/',
        ] );
    }

    public function enqueue_admin_assets( $hook ) {
        if ( strpos( $hook, 'green-india' ) !== false || get_post_type() === 'product' ) {
            wp_enqueue_style( 'gi-admin-css', GI_WOO_URL . 'assets/css/gi-admin.css', [], GI_WOO_VERSION );
        }
    }

    public function register_elementor_category( $elements_manager ) {
        $elements_manager->add_category( 'green-india', [
            'title' => esc_html__( 'Green India Elements', 'green-india-woo' ),
            'icon'  => 'eicon-leaf',
        ] );
    }

    public function register_elementor_widgets( $widgets_manager ) {
        require_once GI_WOO_PATH . 'includes/widgets/class-gi-hero-slider-widget.php';
        require_once GI_WOO_PATH . 'includes/widgets/class-gi-shop-grid-widget.php';
        require_once GI_WOO_PATH . 'includes/widgets/class-gi-product-configurator-widget.php';
        require_once GI_WOO_PATH . 'includes/widgets/class-gi-category-carousel-widget.php';
        require_once GI_WOO_PATH . 'includes/widgets/class-gi-contact-form-widget.php';

        $widgets_manager->register( new \GI_Hero_Slider_Widget() );
        $widgets_manager->register( new \GI_Shop_Grid_Widget() );
        $widgets_manager->register( new \GI_Product_Configurator_Widget() );
        $widgets_manager->register( new \GI_Category_Carousel_Widget() );
        $widgets_manager->register( new \GI_Contact_Form_Widget() );
    }

    public function add_product_data_tab( $tabs ) {
        $tabs['gi_specs'] = [
            'label'    => __( 'Green India Specs & Pieces', 'green-india-woo' ),
            'target'   => 'gi_specs_product_data',
            'class'    => [ 'show_if_simple', 'show_if_variable' ],
            'priority' => 25,
        ];
        return $tabs;
    }

    public function render_product_data_panel() {
        global $post;
        $mrp_price   = get_post_meta( $post->ID, '_gi_mrp_price', true ) ?: get_post_meta( $post->ID, '_regular_price', true );
        $total_stock = get_post_meta( $post->ID, '_gi_total_stock_qty', true ) ?: get_post_meta( $post->ID, '_stock', true );
        $unit        = get_post_meta( $post->ID, '_gi_package_unit', true ) ?: 'kg';
        $pkg_label   = get_post_meta( $post->ID, '_gi_package_label', true ) ?: '1 kg bundle';
        $max_qty     = get_post_meta( $post->ID, '_gi_max_order_qty', true ) ?: '100';
        $negotiable  = get_post_meta( $post->ID, '_gi_bulk_negotiable', true ) ?: 'yes';
        $sizes_json  = get_post_meta( $post->ID, '_gi_sizes_json', true ) ?: '[]';
        $colors_json = get_post_meta( $post->ID, '_gi_colors_json', true ) ?: '[]';
        $qual_json   = get_post_meta( $post->ID, '_gi_qualities_json', true ) ?: '[]';
        $highlights  = get_post_meta( $post->ID, '_gi_highlights', true ) ?: '';
        $storage     = get_post_meta( $post->ID, '_gi_storage_note', true ) ?: 'Store in a dry and dark place.';
        $material    = get_post_meta( $post->ID, '_gi_material_note', true ) ?: 'Compostable, starch-based';
        $standard    = get_post_meta( $post->ID, '_gi_standard_note', true ) ?: 'EN13432 / ISO 17088';
        $aka         = get_post_meta( $post->ID, '_gi_also_known_as', true ) ?: '';
        $size_note   = get_post_meta( $post->ID, '_gi_size_thickness_note', true ) ?: 'Standard sizes and thickness are mentioned in the table above, but we can also customise based on your requirement.';
        ?>
        <div id="gi_specs_product_data" class="panel woocommerce_options_panel">
            <div class="options_group">
                <?php
                woocommerce_wp_text_input( [
                    'id'          => '_gi_mrp_price',
                    'label'       => __( 'MRP Price (₹)', 'green-india-woo' ),
                    'type'        => 'number',
                    'value'       => $mrp_price,
                    'custom_attributes' => [ 'min' => '0', 'step' => '1' ],
                ] );
                woocommerce_wp_text_input( [
                    'id'          => '_gi_total_stock_qty',
                    'label'       => __( 'Total Stock Qty', 'green-india-woo' ),
                    'type'        => 'number',
                    'value'       => $total_stock,
                    'custom_attributes' => [ 'min' => '0', 'step' => '1' ],
                ] );
                woocommerce_wp_select( [
                    'id'      => '_gi_package_unit',
                    'label'   => __( 'Package Unit', 'green-india-woo' ),
                    'value'   => $unit,
                    'options' => [ 'kg' => 'Per kg', 'pack' => 'Per pack' ],
                ] );
                woocommerce_wp_text_input( [
                    'id'          => '_gi_package_label',
                    'label'       => __( 'Package Description', 'green-india-woo' ),
                    'value'       => $pkg_label,
                    'placeholder' => 'e.g., 1 kg bulk bundle / 100 pcs pack',
                ] );
                woocommerce_wp_text_input( [
                    'id'                => '_gi_max_order_qty',
                    'label'             => __( 'Max Standard Order Qty', 'green-india-woo' ),
                    'type'              => 'number',
                    'value'             => $max_qty,
                    'custom_attributes' => [ 'min' => '1', 'step' => '1' ],
                ] );
                woocommerce_wp_checkbox( [
                    'id'    => '_gi_bulk_negotiable',
                    'label' => __( 'Bulk Rate Negotiable', 'green-india-woo' ),
                    'value' => $negotiable,
                ] );
                woocommerce_wp_textarea_input( [
                    'id'          => '_gi_sizes_json',
                    'label'       => __( 'Sizes, Pieces & Size×Color Stock (JSON)', 'green-india-woo' ),
                    'value'       => $sizes_json,
                ] );
                woocommerce_wp_textarea_input( [
                    'id'    => '_gi_colors_json',
                    'label' => __( 'Colors & Hex Swatches (JSON)', 'green-india-woo' ),
                    'value' => $colors_json,
                ] );
                woocommerce_wp_textarea_input( [
                    'id'    => '_gi_qualities_json',
                    'label' => __( 'Qualities, Actual & MRP Prices (JSON)', 'green-india-woo' ),
                    'value' => $qual_json,
                ] );
                woocommerce_wp_textarea_input( [
                    'id'    => '_gi_highlights',
                    'label' => __( 'Product Highlights (one per line)', 'green-india-woo' ),
                    'value' => $highlights,
                ] );
                woocommerce_wp_text_input( [
                    'id'    => '_gi_material_note',
                    'label' => __( 'Material Note', 'green-india-woo' ),
                    'value' => $material,
                ] );
                woocommerce_wp_text_input( [
                    'id'    => '_gi_standard_note',
                    'label' => __( 'Standard Note', 'green-india-woo' ),
                    'value' => $standard,
                ] );
                woocommerce_wp_text_input( [
                    'id'    => '_gi_also_known_as',
                    'label' => __( 'Also Known As', 'green-india-woo' ),
                    'value' => $aka,
                ] );
                woocommerce_wp_text_input( [
                    'id'    => '_gi_size_thickness_note',
                    'label' => __( 'Size & Thickness Note', 'green-india-woo' ),
                    'value' => $size_note,
                ] );
                woocommerce_wp_text_input( [
                    'id'    => '_gi_storage_note',
                    'label' => __( 'Storage Note', 'green-india-woo' ),
                    'value' => $storage,
                ] );
                ?>
            </div>
        </div>
        <?php
    }

    public function save_product_data_meta( $post_id ) {
        $fields = [
            '_gi_mrp_price',
            '_gi_total_stock_qty',
            '_gi_package_unit',
            '_gi_package_label',
            '_gi_max_order_qty',
            '_gi_sizes_json',
            '_gi_colors_json',
            '_gi_qualities_json',
            '_gi_highlights',
            '_gi_material_note',
            '_gi_standard_note',
            '_gi_also_known_as',
            '_gi_size_thickness_note',
            '_gi_storage_note',
        ];
        foreach ( $fields as $field ) {
            if ( isset( $_POST[ $field ] ) ) {
                update_post_meta( $post_id, $field, wp_kses_post( wp_unslash( $_POST[ $field ] ) ) );
            }
        }
        update_post_meta( $post_id, '_gi_bulk_negotiable', isset( $_POST['_gi_bulk_negotiable'] ) ? 'yes' : 'no' );
    }

    public function register_admin_menu() {
        add_menu_page(
            __( 'Green India Shop Manager', 'green-india-woo' ),
            __( 'Green India Shop', 'green-india-woo' ),
            'manage_woocommerce',
            'green-india-shop-manager',
            [ $this, 'render_admin_manager_page' ],
            'dashicons-store',
            56
        );
    }

    public function render_admin_manager_page() {
        require_once GI_WOO_PATH . 'includes/admin/admin-manager-view.php';
    }

    public function handle_quick_save_product() {
        if ( ! current_user_can( 'manage_woocommerce' ) || ! check_admin_referer( 'gi_quick_save' ) ) {
            wp_die( 'Unauthorized' );
        }
        $product_id = absint( $_POST['product_id'] ?? 0 );
        $product    = wc_get_product( $product_id );
        if ( ! $product ) {
            wp_redirect( admin_url( 'admin.php?page=green-india-shop-manager&msg=notfound' ) );
            exit;
        }
        if ( isset( $_POST['name'] ) ) {
            $product->set_name( sanitize_text_field( wp_unslash( $_POST['name'] ) ) );
        }
        if ( isset( $_POST['short_desc'] ) ) {
            $product->set_short_description( sanitize_textarea_field( wp_unslash( $_POST['short_desc'] ) ) );
        }
        $actual_raw = isset( $_POST['price'] ) ? wc_format_decimal( wp_unslash( $_POST['price'] ) ) : $product->get_price();
        $mrp_raw    = isset( $_POST['mrp_price'] ) ? wc_format_decimal( wp_unslash( $_POST['mrp_price'] ) ) : '';
        $actual_val = floatval( $actual_raw );
        $mrp_val    = floatval( $mrp_raw );

        if ( $mrp_val > $actual_val && $actual_val > 0 ) {
            $product->set_regular_price( (string) $mrp_val );
            $product->set_sale_price( (string) $actual_val );
            $product->set_price( (string) $actual_val );
            update_post_meta( $product_id, '_gi_mrp_price', (string) $mrp_val );
        } else {
            $final_price = $actual_val > 0 ? $actual_val : $mrp_val;
            $product->set_regular_price( (string) $final_price );
            $product->set_sale_price( '' );
            $product->set_price( (string) $final_price );
            update_post_meta( $product_id, '_gi_mrp_price', (string) ( $mrp_val > 0 ? $mrp_val : $final_price ) );
        }

        if ( isset( $_POST['stock_qty'] ) ) {
            $qty = max( 0, intval( $_POST['stock_qty'] ) );
            $product->set_manage_stock( true );
            $product->set_stock_quantity( $qty );
            $product->set_stock_status( $qty > 0 ? 'instock' : 'outofstock' );
        }
        $product->save();

        if ( isset( $_POST['category'] ) && trim( $_POST['category'] ) !== '' ) {
            wp_set_object_terms( $product_id, sanitize_text_field( wp_unslash( $_POST['category'] ) ), 'product_cat' );
        }
        if ( isset( $_POST['total_stock_qty'] ) ) {
            update_post_meta( $product_id, '_gi_total_stock_qty', (string) max( 0, intval( $_POST['total_stock_qty'] ) ) );
        }
        if ( isset( $_POST['package_unit'] ) ) {
            update_post_meta( $product_id, '_gi_package_unit', sanitize_text_field( wp_unslash( $_POST['package_unit'] ) ) );
        }
        if ( isset( $_POST['package_label'] ) ) {
            update_post_meta( $product_id, '_gi_package_label', sanitize_text_field( wp_unslash( $_POST['package_label'] ) ) );
        }
        if ( isset( $_POST['sizes_json'] ) ) {
            update_post_meta( $product_id, '_gi_sizes_json', wp_unslash( $_POST['sizes_json'] ) );
        }
        if ( isset( $_POST['colors_json'] ) ) {
            update_post_meta( $product_id, '_gi_colors_json', wp_unslash( $_POST['colors_json'] ) );
        }
        if ( isset( $_POST['qualities_json'] ) ) {
            update_post_meta( $product_id, '_gi_qualities_json', wp_unslash( $_POST['qualities_json'] ) );
        }
        if ( isset( $_POST['material_note'] ) ) {
            update_post_meta( $product_id, '_gi_material_note', sanitize_text_field( wp_unslash( $_POST['material_note'] ) ) );
        }
        if ( isset( $_POST['standard_note'] ) ) {
            update_post_meta( $product_id, '_gi_standard_note', sanitize_text_field( wp_unslash( $_POST['standard_note'] ) ) );
        }
        if ( isset( $_POST['storage_note'] ) ) {
            update_post_meta( $product_id, '_gi_storage_note', sanitize_text_field( wp_unslash( $_POST['storage_note'] ) ) );
        }
        if ( isset( $_POST['also_known_as'] ) ) {
            update_post_meta( $product_id, '_gi_also_known_as', sanitize_text_field( wp_unslash( $_POST['also_known_as'] ) ) );
        }
        if ( isset( $_POST['size_thickness_note'] ) ) {
            update_post_meta( $product_id, '_gi_size_thickness_note', sanitize_text_field( wp_unslash( $_POST['size_thickness_note'] ) ) );
        }
        if ( isset( $_POST['highlights'] ) ) {
            update_post_meta( $product_id, '_gi_highlights', sanitize_textarea_field( wp_unslash( $_POST['highlights'] ) ) );
        }

        wp_redirect( admin_url( 'admin.php?page=green-india-shop-manager&msg=saved' ) );
        exit;
    }

    public function handle_quick_delete_product() {
        if ( ! current_user_can( 'manage_woocommerce' ) || ! check_admin_referer( 'gi_quick_delete' ) ) {
            wp_die( 'Unauthorized' );
        }
        $product_id = absint( $_GET['product_id'] ?? 0 );
        if ( $product_id ) {
            wp_trash_post( $product_id );
        }
        wp_redirect( admin_url( 'admin.php?page=green-india-shop-manager&msg=deleted' ) );
        exit;
    }

    public function handle_quick_add_product() {
        if ( ! current_user_can( 'manage_woocommerce' ) || ! check_admin_referer( 'gi_quick_add' ) ) {
            wp_die( 'Unauthorized' );
        }
        $product = new WC_Product_Simple();
        $product->set_name( sanitize_text_field( wp_unslash( $_POST['name'] ?? 'New Compostable Bag' ) ) );
        $actual = floatval( wc_format_decimal( wp_unslash( $_POST['price'] ?? '200' ) ) );
        $mrp    = floatval( wc_format_decimal( wp_unslash( $_POST['mrp_price'] ?? $actual ) ) );
        if ( $mrp > $actual && $actual > 0 ) {
            $product->set_regular_price( (string) $mrp );
            $product->set_sale_price( (string) $actual );
            $product->set_price( (string) $actual );
        } else {
            $product->set_regular_price( (string) $actual );
            $product->set_price( (string) $actual );
        }
        $product->set_short_description( sanitize_textarea_field( wp_unslash( $_POST['short_desc'] ?? '' ) ) );
        $product->set_manage_stock( true );
        $qty       = max( 0, intval( $_POST['stock_qty'] ?? 100 ) );
        $total_qty = max( $qty, intval( $_POST['total_stock_qty'] ?? $qty ) );
        $product->set_stock_quantity( $qty );
        $product->set_stock_status( $qty > 0 ? 'instock' : 'outofstock' );
        $product->set_status( 'publish' );
        $new_id = $product->save();

        if ( ! empty( $_POST['category'] ) ) {
            wp_set_object_terms( $new_id, sanitize_text_field( wp_unslash( $_POST['category'] ) ), 'product_cat' );
        }
        update_post_meta( $new_id, '_gi_mrp_price', (string) ( $mrp > 0 ? $mrp : $actual ) );
        update_post_meta( $new_id, '_gi_total_stock_qty', (string) $total_qty );
        update_post_meta( $new_id, '_gi_package_unit', sanitize_text_field( wp_unslash( $_POST['package_unit'] ?? 'kg' ) ) );
        update_post_meta( $new_id, '_gi_package_label', sanitize_text_field( wp_unslash( $_POST['package_label'] ?? '1 kg bundle' ) ) );
        update_post_meta( $new_id, '_gi_max_order_qty', '100' );
        update_post_meta( $new_id, '_gi_bulk_negotiable', 'yes' );
        update_post_meta( $new_id, '_gi_sizes_json', wp_unslash( $_POST['sizes_json'] ?? '[]' ) );
        update_post_meta( $new_id, '_gi_colors_json', wp_unslash( $_POST['colors_json'] ?? '[]' ) );

        wp_redirect( admin_url( 'admin.php?page=green-india-shop-manager&msg=added' ) );
        exit;
    }

    public function handle_one_click_import() {
        if ( ! current_user_can( 'manage_options' ) || ! check_admin_referer( 'gi_one_click_import' ) ) {
            wp_die( 'Unauthorized' );
        }
        require_once GI_WOO_PATH . 'includes/admin/one-click-importer.php';
        $result = gi_run_full_import();
        wp_redirect( admin_url( 'admin.php?page=green-india-shop-manager&msg=imported&count=' . intval( $result['products'] ) ) );
        exit;
    }

    public function ajax_add_to_woo_cart() {
        check_ajax_referer( 'gi_woo_nonce', 'nonce' );
        if ( ! function_exists( 'WC' ) || ! WC()->cart ) {
            wp_send_json_error( [ 'message' => 'WooCommerce cart not available.' ] );
        }
        $product_id = absint( $_POST['product_id'] ?? 0 );
        $qty        = max( 1, intval( $_POST['qty'] ?? 1 ) );
        $cart_item_data = [
            'gi_size'    => sanitize_text_field( wp_unslash( $_POST['size'] ?? '' ) ),
            'gi_color'   => sanitize_text_field( wp_unslash( $_POST['color'] ?? '' ) ),
            'gi_quality' => sanitize_text_field( wp_unslash( $_POST['quality'] ?? '' ) ),
            'gi_pieces'  => sanitize_text_field( wp_unslash( $_POST['pieces'] ?? '' ) ),
            'gi_unit'    => sanitize_text_field( wp_unslash( $_POST['unit'] ?? 'kg' ) ),
        ];
        if ( isset( $_POST['custom_price'] ) && floatval( $_POST['custom_price'] ) > 0 ) {
            $cart_item_data['gi_custom_price'] = floatval( $_POST['custom_price'] );
        }

        $key = WC()->cart->add_to_cart( $product_id, $qty, 0, [], $cart_item_data );
        if ( $key ) {
            wp_send_json_success( [
                'cart_count' => WC()->cart->get_cart_contents_count(),
                'cart_total' => WC()->cart->get_cart_total(),
            ] );
        }
        wp_send_json_error( [ 'message' => 'Could not add product to cart.' ] );
    }

    public function display_cart_item_meta( $item_data, $cart_item ) {
        $map = [
            'gi_quality' => __( 'Quality', 'green-india-woo' ),
            'gi_size'    => __( 'Size', 'green-india-woo' ),
            'gi_color'   => __( 'Color', 'green-india-woo' ),
            'gi_pieces'  => __( 'Approx. Pieces', 'green-india-woo' ),
        ];
        foreach ( $map as $k => $label ) {
            if ( ! empty( $cart_item[ $k ] ) ) {
                $item_data[] = [ 'key' => $label, 'value' => $cart_item[ $k ] ];
            }
        }
        return $item_data;
    }

    public function save_order_item_meta( $item, $cart_item_key, $values, $order ) {
        foreach ( [ 'gi_quality' => 'Quality', 'gi_size' => 'Size', 'gi_color' => 'Color', 'gi_pieces' => 'Approx. Pieces' ] as $k => $label ) {
            if ( ! empty( $values[ $k ] ) ) {
                $item->add_meta_data( $label, $values[ $k ], true );
            }
        }
    }
}

add_action( 'plugins_loaded', [ 'Green_India_Woo_Elementor', 'instance' ] );
}
