<?php
if ( ! defined( 'ABSPATH' ) ) exit;

class GI_Category_Carousel_Widget extends \Elementor\Widget_Base {
    public function get_name() { return 'gi_category_carousel'; }
    public function get_title() { return __( 'Green India Category Showcase', 'green-india-woo' ); }
    public function get_icon() { return 'eicon-gallery-grid'; }
    public function get_categories() { return [ 'green-india' ]; }

    protected function render() {
        if ( ! function_exists( 'wc_get_products' ) ) return;
        $products = wc_get_products( [ 'status' => 'publish', 'limit' => 8, 'orderby' => 'menu_order', 'order' => 'ASC' ] );
        echo '<div class="gi-cat-grid">';
        foreach ( $products as $idx => $product ) {
            $img = get_the_post_thumbnail_url( $product->get_id(), 'medium' ) ?: wc_placeholder_img_src();
            $grad = 'gi-cat-grad-' . ( ( $idx % 7 ) + 1 );
            echo '<a class="gi-cat-card" href="' . esc_url( $product->get_permalink() ) . '">';
            echo '<div class="gi-cat-img ' . esc_attr( $grad ) . '"><img src="' . esc_url( $img ) . '" alt="' . esc_attr( $product->get_name() ) . '" loading="lazy"/></div>';
            echo '<h3>' . esc_html( $product->get_name() ) . '</h3>';
            echo '<p>' . esc_html( wp_trim_words( $product->get_short_description(), 14 ) ) . '</p>';
            echo '</a>';
        }
        echo '</div>';
    }
}
