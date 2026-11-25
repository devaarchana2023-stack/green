<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

define( 'GI_THEME_VERSION', '1.1.0' );
define( 'GI_THEME_DIR', get_template_directory() );
define( 'GI_THEME_URI', get_template_directory_uri() );

add_action( 'after_setup_theme', function() {
    add_theme_support( 'title-tag' );
    add_theme_support( 'post-thumbnails' );
    add_theme_support( 'html5', [ 'search-form', 'comment-form', 'comment-list', 'gallery', 'caption' ] );
    add_theme_support( 'woocommerce' );
    add_theme_support( 'wc-product-gallery-zoom' );
    add_theme_support( 'wc-product-gallery-lightbox' );
    add_theme_support( 'wc-product-gallery-slider' );

    register_nav_menus( [
        'primary' => __( 'Green India Main Menu', 'green-india-theme' ),
        'footer'  => __( 'Green India Footer Site Map', 'green-india-theme' ),
    ] );
} );

add_action( 'wp_enqueue_scripts', function() {
    wp_enqueue_style(
        'gi-theme-fonts',
        'https://fonts.googleapis.com/css2?family=Fraunces:wght@600;700;800&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap',
        [],
        null
    );
    wp_enqueue_style( 'gi-theme-style', get_stylesheet_uri(), [], GI_THEME_VERSION );
    wp_enqueue_style( 'gi-theme-shop', GI_THEME_URI . '/assets/css/gi-shop.css', [ 'gi-theme-style' ], GI_THEME_VERSION );
    wp_enqueue_script( 'gi-theme-shop-js', GI_THEME_URI . '/assets/js/gi-shop.js', [ 'jquery' ], GI_THEME_VERSION, true );
    wp_localize_script( 'gi-theme-shop-js', 'giWooConfig', [
        'ajaxUrl'     => admin_url( 'admin-ajax.php' ),
        'nonce'       => wp_create_nonce( 'gi_woo_nonce' ),
        'whatsapp'    => '919489465676',
        'currency'    => '₹',
        'gstRate'     => 0.18,
        'cartUrl'     => function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : '/cart/',
        'checkoutUrl' => function_exists( 'wc_get_checkout_url' ) ? wc_get_checkout_url() : '/checkout/',
    ] );
} );

// Load built-in WooCommerce + Elementor engine if companion plugin is not separately activated
if ( ! class_exists( 'Green_India_Woo_Elementor' ) && file_exists( GI_THEME_DIR . '/inc/green-india-engine.php' ) ) {
    require_once GI_THEME_DIR . '/inc/green-india-engine.php';
}
