<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class GI_Product_Configurator_Widget extends \Elementor\Widget_Base {
    public function get_name() { return 'gi_product_configurator'; }
    public function get_title() { return __( 'Green India Product Configurator', 'green-india-woo' ); }
    public function get_icon() { return 'eicon-product-add-to-cart'; }
    public function get_categories() { return [ 'green-india' ]; }

    protected function register_controls() {
        $this->start_controls_section( 'content_section', [
            'label' => __( 'Product Binding', 'green-india-woo' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );
        $this->add_control( 'product_sku', [
            'label'       => __( 'Product SKU (leave empty on Single Product template)', 'green-india-woo' ),
            'type'        => \Elementor\Controls_Manager::TEXT,
            'default'     => '',
        ] );
        $this->add_control( 'show_size_table', [
            'label'   => __( 'Show Size & Pieces Table', 'green-india-woo' ),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'no',
        ] );
        $this->add_control( 'show_color_swatches', [
            'label'   => __( 'Show Color Swatches', 'green-india-woo' ),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ] );
        $this->add_control( 'show_related_products', [
            'label'   => __( 'Show All Other Products Grid', 'green-india-woo' ),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ] );
        $this->end_controls_section();
    }

    protected function render() {
        if ( ! function_exists( 'wc_get_product' ) ) return;
        $settings   = $this->get_settings_for_display();
        $sku        = $settings['product_sku'] ?? '';
        $product_id = $sku ? wc_get_product_id_by_sku( $sku ) : get_the_ID();
        $product    = wc_get_product( $product_id );
        if ( ! $product ) {
            echo '<p>Select or publish a WooCommerce product to preview the configurator.</p>';
            return;
        }
        $pid         = $product->get_id();
        $unit        = get_post_meta( $pid, '_gi_package_unit', true ) ?: 'kg';
        $pkg_label   = get_post_meta( $pid, '_gi_package_label', true ) ?: '1 kg bundle';
        $max_qty     = intval( get_post_meta( $pid, '_gi_max_order_qty', true ) ?: 100 );
        $sizes       = json_decode( get_post_meta( $pid, '_gi_sizes_json', true ) ?: '[]', true );
        $colors      = json_decode( get_post_meta( $pid, '_gi_colors_json', true ) ?: '[]', true );
        $qualities   = json_decode( get_post_meta( $pid, '_gi_qualities_json', true ) ?: '[]', true );
        $highlights  = array_filter( array_map( 'trim', explode( "\n", (string) get_post_meta( $pid, '_gi_highlights', true ) ) ) );
        $aka         = get_post_meta( $pid, '_gi_also_known_as', true ) ?: '';
        $size_note   = get_post_meta( $pid, '_gi_size_thickness_note', true ) ?: 'Standard sizes and thickness are mentioned in the table above, and we also customise based on your requirement.';
        $storage     = get_post_meta( $pid, '_gi_storage_note', true ) ?: 'Store in a dry and dark place.';
        $img_url     = get_the_post_thumbnail_url( $pid, 'large' ) ?: wc_placeholder_img_src();
        $base        = (float) $product->get_price();
        $base_mrp    = (float) ( get_post_meta( $pid, '_gi_mrp_price', true ) ?: $product->get_regular_price() ?: $base );
        $stock_qty   = intval( $product->get_stock_quantity() ?? 0 );
        $total_stock = intval( get_post_meta( $pid, '_gi_total_stock_qty', true ) ?: $stock_qty );
        ?>
        <div class="gi-pdp" data-product-id="<?php echo esc_attr( $pid ); ?>" data-base-price="<?php echo esc_attr( $base ); ?>" data-base-mrp="<?php echo esc_attr( $base_mrp ); ?>" data-base-stock="<?php echo esc_attr( $stock_qty ); ?>" data-total-stock="<?php echo esc_attr( $total_stock ); ?>" data-package-label="<?php echo esc_attr( $pkg_label ); ?>" data-unit="<?php echo esc_attr( $unit ); ?>" data-max-qty="<?php echo esc_attr( $max_qty ); ?>">
            <div class="gi-pdp-media"><img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" /></div>
            <div class="gi-pdp-info">
                <h1><?php echo esc_html( $product->get_name() ); ?></h1>
                <p class="gi-pdp-desc"><?php echo esc_html( $product->get_short_description() ); ?></p>
                <div class="gi-pbig">
                    <span class="gi-live-price">₹<?php echo esc_html( number_format( $base, 0 ) ); ?></span>
                    <del class="gi-mrp gi-live-mrp" <?php echo $base_mrp <= $base ? 'style="display:none"' : ''; ?>>MRP ₹<?php echo esc_html( number_format( $base_mrp, 0 ) ); ?></del>
                    <span class="gi-off-badge gi-live-off" <?php echo $base_mrp <= $base ? 'style="display:none"' : ''; ?>></span>
                    <small>Actual Price per <?php echo esc_html( $unit ); ?> (<span class="gi-live-pkg"><?php echo esc_html( $pkg_label ); ?></span>) + 18% GST</small>
                </div>

                <?php if ( ! empty( $qualities ) ) : ?>
                <div class="gi-opts">
                    <h4>Quality</h4>
                    <div class="gi-ob" data-opt-group="quality">
                        <?php foreach ( $qualities as $idx => $q ) :
                            $q_act = floatval( $q['price'] ?? $base );
                            $q_mrp = floatval( $q['mrp'] ?? $base_mrp );
                        ?>
                        <button type="button" class="gi-opt <?php echo $idx === 0 ? 'on' : ''; ?>"
                            data-price="<?php echo esc_attr( $q_act ); ?>"
                            data-mrp="<?php echo esc_attr( $q_mrp ); ?>"
                            data-val="<?php echo esc_attr( $q['name'] ); ?>">
                            <?php echo esc_html( $q['name'] ); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ( ! empty( $sizes ) ) : ?>
                <div class="gi-opts">
                    <h4>Size</h4>
                    <div class="gi-ob" data-opt-group="size">
                        <?php foreach ( $sizes as $idx => $s ) :
                            $pbq = ! empty( $s['piecesByQuality'] ) ? $s['piecesByQuality'] : new stdClass();
                        ?>
                        <button type="button" class="gi-opt <?php echo $idx === 0 ? 'on' : ''; ?>"
                            data-pieces="<?php echo esc_attr( $s['piecesPerUnit'] ?? 0 ); ?>"
                            data-pieces-by-quality="<?php echo esc_attr( wp_json_encode( $pbq ) ); ?>"
                            data-package="<?php echo esc_attr( $s['packageOverride'] ?? '' ); ?>"
                            data-capacity="<?php echo esc_attr( $s['carryingCapacity'] ?? '' ); ?>"
                            data-price-override="<?php echo esc_attr( $s['priceOverride'] ?? '' ); ?>"
                            data-mrp-override="<?php echo esc_attr( $s['mrpOverride'] ?? '' ); ?>"
                            data-stock="<?php echo esc_attr( $s['stockQty'] ?? '' ); ?>"
                            data-total-stock="<?php echo esc_attr( $s['totalStockQty'] ?? '' ); ?>"
                            data-color-stocks="<?php echo esc_attr( wp_json_encode( $s['colorStocks'] ?? new stdClass() ) ); ?>"
                            data-val="<?php echo esc_attr( $s['sizeLabel'] ); ?>">
                            <?php echo esc_html( $s['sizeLabel'] ); ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <?php if ( ! empty( $colors ) && ( $settings['show_color_swatches'] ?? 'yes' ) === 'yes' ) : ?>
                <div class="gi-opts">
                    <h4>Colour</h4>
                    <div class="gi-ob" data-opt-group="color">
                        <?php foreach ( $colors as $idx => $c ) : ?>
                        <button type="button" class="gi-opt <?php echo $idx === 0 ? 'on' : ''; ?>" data-val="<?php echo esc_attr( $c['name'] ); ?>"><i style="background:<?php echo esc_attr( $c['hex'] ); ?>"></i><?php echo esc_html( $c['name'] ); ?></button>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>

                <div class="gi-buy-bar">
                    <div class="gi-qty-box">
                        <button type="button" class="gi-q-dec">−</button>
                        <input type="number" class="gi-q-in" value="1" min="1" max="<?php echo esc_attr( $max_qty ); ?>" />
                        <span><?php echo esc_html( $unit ); ?></span>
                        <button type="button" class="gi-q-inc">+</button>
                    </div>
                    <button type="button" class="gi-btn gi-btn-primary gi-woo-add">Add to WooCommerce Cart</button>
                    <a class="gi-btn gi-btn-outline" href="https://wa.me/919489465676?text=<?php echo rawurlencode( 'Hello Green India, I would like to enquire about ' . $product->get_name() ); ?>" target="_blank" rel="noopener">WhatsApp Enquiry</a>
                </div>
                <div class="gi-pieces-note">Approx. pieces: <strong class="gi-live-pieces">0 pcs</strong> · Carrying capacity: <strong class="gi-live-capacity">—</strong></div>

                <?php if ( ! empty( $highlights ) ) : ?>
                <h4 class="gi-subh">Features</h4>
                <ul class="gi-list">
                    <?php foreach ( $highlights as $hl ) echo '<li>' . esc_html( $hl ) . '</li>'; ?>
                </ul>
                <?php endif; ?>
            </div>
        </div>

        <?php if ( ! empty( $sizes ) && ( $settings['show_size_table'] ?? 'yes' ) === 'yes' ) :
            $has_type_col = false;
            foreach ( $sizes as $sz_chk ) { if ( ! empty( $sz_chk['typeLabel'] ) ) { $has_type_col = true; break; } }
        ?>
        <div class="gi-sizes-box">
            <h3>Available Sizes, Quality</h3>
            <table class="gi-sizes-tbl">
                <thead>
                    <tr>
                        <th>Size</th>
                        <?php if ( $has_type_col ) : ?><th>Quality</th><?php endif; ?>
                        <th>Package</th>
                        <?php if ( ! empty( $qualities ) ) : ?>
                            <?php foreach ( $qualities as $q ) : ?>
                                <th><?php echo esc_html( $q['name'] ); ?> (pcs/<?php echo esc_html( $unit ); ?>)</th>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <th>Approx. pieces per <?php echo esc_html( $unit ); ?></th>
                        <?php endif; ?>
                        <th>Carrying capacity</th>
                        <th>Available / Total Stock</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ( $sizes as $sz ) :
                        $sz_avail = isset( $sz['stockQty'] ) ? intval( $sz['stockQty'] ) : $stock_qty;
                        $sz_tot   = isset( $sz['totalStockQty'] ) ? intval( $sz['totalStockQty'] ) : max( $sz_avail, $total_stock );
                    ?>
                    <tr>
                        <th><?php echo esc_html( $sz['sizeLabel'] ); ?></th>
                        <?php if ( $has_type_col ) : ?><td><?php echo esc_html( $sz['typeLabel'] ?? 'Standard' ); ?></td><?php endif; ?>
                        <td><?php echo esc_html( ! empty( $sz['packageOverride'] ) ? $sz['packageOverride'] : $pkg_label ); ?></td>
                        <?php if ( ! empty( $qualities ) ) : ?>
                            <?php foreach ( $qualities as $q ) :
                                $q_pcs = isset( $sz['piecesByQuality'][ $q['name'] ] ) ? $sz['piecesByQuality'][ $q['name'] ] : $sz['piecesPerUnit'];
                            ?>
                                <td><?php echo ( $q_pcs === null || $q_pcs === '' ) ? '—' : esc_html( $q_pcs ); ?></td>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <td><?php echo esc_html( $sz['piecesPerUnit'] ?? '—' ); ?></td>
                        <?php endif; ?>
                        <td><?php echo esc_html( $sz['carryingCapacity'] ?? '—' ); ?></td>
                        <td><?php echo esc_html( $sz_avail . ' / ' . $sz_tot . ' ' . $unit ); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>

        <?php if ( ( $settings['show_size_table'] ?? 'no' ) === 'yes' ) : ?>
        <div class="gi-feat-grid">
            <?php if ( $aka ) : ?><div><h4>Also known as</h4><p><?php echo esc_html( $aka ); ?></p></div><?php endif; ?>
            <div><h4>Size and thickness</h4><p><?php echo esc_html( $size_note ); ?></p></div>
            <div><h4>Storage</h4><p><?php echo esc_html( $storage ); ?></p></div>
            <?php if ( ! empty( $colors ) ) : ?>
            <div><h4>Colour availability</h4><div class="gi-sw-row"><?php foreach ( $colors as $c ) echo '<span><i style="background:' . esc_attr( $c['hex'] ) . '"></i>' . esc_html( $c['name'] ) . '</span>'; ?></div></div>
            <?php endif; ?>
        </div>
        <?php endif; ?>

        <?php if ( ( $settings['show_related_products'] ?? 'yes' ) === 'yes' ) :
            $other_products = wc_get_products( [
                'status'  => 'publish',
                'limit'   => -1,
                'exclude' => [ $pid ],
                'orderby' => 'menu_order',
                'order'   => 'ASC',
            ] );
            if ( ! empty( $other_products ) ) :
        ?>
        <div class="gi-rel-wrap">
            <h2 class="gi-rel-title">All Other Products</h2>
            <div class="gi-rel-grid">
                <?php foreach ( $other_products as $op ) :
                    $op_id     = $op->get_id();
                    $op_img    = get_the_post_thumbnail_url( $op_id, 'medium' ) ?: wc_placeholder_img_src();
                    $op_terms  = get_the_terms( $op_id, 'product_cat' );
                    $op_cat    = ( $op_terms && ! is_wp_error( $op_terms ) ) ? $op_terms[0]->name : 'Compostable';
                    $op_unit   = get_post_meta( $op_id, '_gi_package_unit', true ) ?: 'kg';
                    $op_act    = floatval( $op->get_price() ?: get_post_meta( $op_id, '_regular_price', true ) );
                    $op_mrp    = floatval( $op->get_regular_price() ?: get_post_meta( $op_id, '_gi_mrp_price', true ) ?: $op_act );
                ?>
                <article class="gi-card">
                    <a class="gi-pic" href="<?php echo esc_url( $op->get_permalink() ); ?>">
                        <img src="<?php echo esc_url( $op_img ); ?>" alt="<?php echo esc_attr( $op->get_name() ); ?>" loading="lazy" />
                    </a>
                    <div class="gi-card-body">
                        <small class="gi-cat"><?php echo esc_html( $op_cat ); ?></small>
                        <h3><a href="<?php echo esc_url( $op->get_permalink() ); ?>"><?php echo esc_html( $op->get_name() ); ?></a></h3>
                        <div class="gi-card-row">
                            <div class="gi-price">
                                ₹<?php echo esc_html( number_format( $op_act, 0 ) ); ?>
                                <?php if ( $op_mrp > $op_act ) : ?>
                                    <del class="gi-mrp">₹<?php echo esc_html( number_format( $op_mrp, 0 ) ); ?></del>
                                <?php endif; ?>
                                <span>/ <?php echo esc_html( $op_unit ); ?></span>
                            </div>
                            <a class="gi-add-btn" href="<?php echo esc_url( $op->get_permalink() ); ?>">Choose &amp; Add</a>
                        </div>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; endif; ?>
        <?php
    }
}
