<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class GI_Shop_Grid_Widget extends \Elementor\Widget_Base {
    public function get_name() { return 'gi_shop_grid'; }
    public function get_title() { return __( 'Green India WooCommerce Shop Grid', 'green-india-woo' ); }
    public function get_icon() { return 'eicon-products'; }
    public function get_categories() { return [ 'green-india' ]; }

    protected function register_controls() {
        $this->start_controls_section( 'content_section', [
            'label' => __( 'Shop Layout & Sync', 'green-india-woo' ),
            'tab'   => \Elementor\Controls_Manager::TAB_CONTENT,
        ] );
        $this->add_control( 'columns_desktop', [
            'label'   => __( 'Columns (Desktop)', 'green-india-woo' ),
            'type'    => \Elementor\Controls_Manager::SELECT,
            'default' => '4',
            'options' => [ '2' => '2', '3' => '3', '4' => '4' ],
        ] );
        $this->add_control( 'show_category_filter', [
            'label'   => __( 'Show Category Filter Bar', 'green-india-woo' ),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ] );
        $this->add_control( 'show_stock_badge', [
            'label'   => __( 'Show Stock Status', 'green-india-woo' ),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ] );
        $this->add_control( 'show_size_chips', [
            'label'   => __( 'Show Size / Quality Chips', 'green-india-woo' ),
            'type'    => \Elementor\Controls_Manager::SWITCHER,
            'default' => 'yes',
        ] );
        $this->add_control( 'button_label', [
            'label'   => __( 'Button Label', 'green-india-woo' ),
            'type'    => \Elementor\Controls_Manager::TEXT,
            'default' => 'Choose & Add',
        ] );
        $this->end_controls_section();

        $this->start_controls_section( 'style_section', [
            'label' => __( 'Card & Color Styling', 'green-india-woo' ),
            'tab'   => \Elementor\Controls_Manager::TAB_STYLE,
        ] );
        $this->add_control( 'card_bg_color', [
            'label'     => __( 'Card Background', 'green-india-woo' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#ffffff',
            'selectors' => [ '{{WRAPPER}} .gi-card' => 'background-color: {{VALUE}};' ],
        ] );
        $this->add_control( 'image_panel_bg', [
            'label'     => __( 'Image Panel Background', 'green-india-woo' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#e8f3da',
            'selectors' => [ '{{WRAPPER}} .gi-card .gi-pic' => 'background-color: {{VALUE}};' ],
        ] );
        $this->add_control( 'primary_color', [
            'label'     => __( 'Primary Accent Color', 'green-india-woo' ),
            'type'      => \Elementor\Controls_Manager::COLOR,
            'default'   => '#2e7d32',
            'selectors' => [ '{{WRAPPER}} .gi-card small.gi-cat' => 'color: {{VALUE}};' ],
        ] );
        $this->add_control( 'card_border_radius', [
            'label'     => __( 'Card Border Radius (px)', 'green-india-woo' ),
            'type'      => \Elementor\Controls_Manager::NUMBER,
            'default'   => 18,
            'selectors' => [ '{{WRAPPER}} .gi-card' => 'border-radius: {{VALUE}}px;' ],
        ] );
        $this->end_controls_section();
    }

    protected function render() {
        if ( ! function_exists( 'wc_get_products' ) ) {
            echo '<p>WooCommerce is required for the dynamic shop grid.</p>';
            return;
        }
        $settings = $this->get_settings_for_display();
        $products = wc_get_products( [ 'status' => 'publish', 'limit' => -1, 'orderby' => 'menu_order', 'order' => 'ASC' ] );
        $cols     = intval( $settings['columns_desktop'] ?? 4 );
        ?>
        <div class="gi-woo-shop" data-cols="<?php echo esc_attr( $cols ); ?>">
            <?php if ( ( $settings['show_category_filter'] ?? 'yes' ) === 'yes' ) : ?>
                <div class="gi-filters" role="tablist">
                    <button type="button" class="gi-chip on" data-gi-cat="all">All Products (<?php echo count( $products ); ?>)</button>
                    <?php
                    $seen_cats = [];
                    foreach ( $products as $p ) {
                        $terms = get_the_terms( $p->get_id(), 'product_cat' );
                        if ( $terms && ! is_wp_error( $terms ) ) {
                            foreach ( $terms as $term ) {
                                if ( ! isset( $seen_cats[ $term->slug ] ) ) {
                                    $seen_cats[ $term->slug ] = $term->name;
                                    echo '<button type="button" class="gi-chip" data-gi-cat="' . esc_attr( $term->slug ) . '">' . esc_html( $term->name ) . '</button>';
                                }
                            }
                        }
                    }
                    ?>
                </div>
            <?php endif; ?>
            <div class="gi-grid gi-cols-<?php echo esc_attr( $cols ); ?>">
                <?php if ( empty( $products ) ) : ?>
                    <p class="gi-empty">No products found in WooCommerce. Add a product in WooCommerce > Products to display it here automatically.</p>
                <?php else : ?>
                    <?php foreach ( $products as $product ) :
                        $pid       = $product->get_id();
                        $terms     = get_the_terms( $pid, 'product_cat' );
                        $cat_name  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->name : 'Compostable';
                        $cat_slug  = ( $terms && ! is_wp_error( $terms ) ) ? $terms[0]->slug : 'all';
                        $unit      = get_post_meta( $pid, '_gi_package_unit', true ) ?: 'kg';
                        $sizes     = json_decode( get_post_meta( $pid, '_gi_sizes_json', true ) ?: '[]', true );
                        $qualities = json_decode( get_post_meta( $pid, '_gi_qualities_json', true ) ?: '[]', true );
                        $img_url   = get_the_post_thumbnail_url( $pid, 'medium_large' ) ?: wc_placeholder_img_src();
                        $stock_qty = $product->get_stock_quantity();
                        $in_stock  = $product->is_in_stock();
                        $actual    = (float) $product->get_price();
                        $mrp       = (float) ( get_post_meta( $pid, '_gi_mrp_price', true ) ?: $product->get_regular_price() );
                        $size_prices = [];
                        if ( is_array( $sizes ) ) {
                            foreach ( $sizes as $sz ) {
                                if ( ! empty( $sz['priceOverride'] ) && floatval( $sz['priceOverride'] ) > 0 ) {
                                    $size_prices[] = floatval( $sz['priceOverride'] );
                                }
                            }
                        }
                        $quality_prices = [];
                        if ( is_array( $qualities ) ) {
                            foreach ( $qualities as $q ) {
                                if ( ! empty( $q['price'] ) && floatval( $q['price'] ) > 0 ) {
                                    $quality_prices[] = floatval( $q['price'] );
                                }
                            }
                        }
                        if ( ! empty( $size_prices ) ) {
                            $actual = min( $size_prices );
                            foreach ( $sizes as $sz ) {
                                if ( isset( $sz['priceOverride'] ) && floatval( $sz['priceOverride'] ) == $actual && ! empty( $sz['mrpOverride'] ) ) {
                                    $mrp = floatval( $sz['mrpOverride'] );
                                    break;
                                }
                            }
                        } elseif ( ! empty( $quality_prices ) ) {
                            $actual = min( $quality_prices );
                            foreach ( $qualities as $q ) {
                                if ( isset( $q['price'] ) && floatval( $q['price'] ) == $actual && ! empty( $q['mrp'] ) ) {
                                    $mrp = floatval( $q['mrp'] );
                                    break;
                                }
                            }
                        }
                        $varied_prices = count( array_unique( $size_prices ) ) > 1 || count( array_unique( $quality_prices ) ) > 1;
                        $off_pct   = ( $mrp > $actual && $actual > 0 ) ? round( ( ( $mrp - $actual ) / $mrp ) * 100 ) : 0;
                    ?>
                    <article class="gi-card" data-cat="<?php echo esc_attr( $cat_slug ); ?>">
                        <a class="gi-pic" href="<?php echo esc_url( $product->get_permalink() ); ?>">
                            <?php if ( $off_pct > 0 ) : ?>
                                <span class="gi-off-badge"><?php echo esc_html( $off_pct ); ?>% OFF</span>
                            <?php endif; ?>
                            <img src="<?php echo esc_url( $img_url ); ?>" alt="<?php echo esc_attr( $product->get_name() ); ?>" loading="lazy" />
                        </a>
                        <div class="gi-card-body">
                            <small class="gi-cat"><?php echo esc_html( $cat_name ); ?></small>
                            <h3><a href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $product->get_name() ); ?></a></h3>
                            <p><?php echo esc_html( wp_trim_words( $product->get_short_description(), 20 ) ); ?></p>
                            <?php if ( ! empty( $qualities ) ) : ?>
                                <div class="gi-chips">
                                    <?php foreach ( $qualities as $q ) echo '<span>' . esc_html( $q['name'] ) . '</span>'; ?>
                                </div>
                            <?php elseif ( ( $settings['show_size_chips'] ?? 'yes' ) === 'yes' && ! empty( $sizes ) ) : ?>
                                <div class="gi-chips">
                                    <?php foreach ( array_slice( $sizes, 0, 4 ) as $s ) echo '<span>' . esc_html( $s['typeLabel'] ?? $s['sizeLabel'] ) . '</span>'; ?>
                                </div>
                            <?php endif; ?>
                            <?php if ( ( $settings['show_stock_badge'] ?? 'yes' ) === 'yes' ) : ?>
                                <div class="gi-stock-line">
                                    <?php echo $in_stock ? ( $stock_qty !== null ? 'In Stock (' . intval( $stock_qty ) . ' ' . esc_html( $unit ) . ')' : 'In Stock' ) : 'Out of Stock'; ?>
                                </div>
                            <?php endif; ?>
                            <div class="gi-card-row">
                                <div class="gi-price">
                                    <span><?php echo $varied_prices ? 'From' : 'Actual'; ?></span>
                                    ₹<?php echo esc_html( number_format( $actual, 0 ) ); ?>
                                    <?php if ( $mrp > $actual ) : ?>
                                        <del class="gi-mrp">₹<?php echo esc_html( number_format( $mrp, 0 ) ); ?></del>
                                    <?php endif; ?>
                                    <span>/ <?php echo esc_html( $unit ); ?></span>
                                </div>
                                <a class="gi-add-btn" href="<?php echo esc_url( $product->get_permalink() ); ?>"><?php echo esc_html( $settings['button_label'] ?: 'Choose & Add' ); ?></a>
                            </div>
                        </div>
                    </article>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
        <?php
    }
}
