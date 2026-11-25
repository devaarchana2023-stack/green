<!doctype html>
<html <?php language_attributes(); ?>>
<head>
  <meta charset="<?php bloginfo( 'charset' ); ?>" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <?php wp_head(); ?>
</head>
<body <?php body_class(); ?>>
<?php wp_body_open(); ?>
<header class="gi-site-header" id="gi-site-header">
  <div class="gi-wrap gi-nav-row">
    <a class="gi-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" aria-label="Green India – Revolution Makes Green">
      <svg aria-hidden="true" class="gi-lmark" focusable="false" viewBox="0 0 48 48"><defs><linearGradient gradientUnits="userSpaceOnUse" id="gi-logo-h" x1="6" x2="42" y1="2" y2="48"><stop stop-color="#4caf50"/><stop offset="1" stop-color="#1b5e20"/></linearGradient></defs><circle cx="24" cy="24" fill="url(#gi-logo-h)" r="24"/><path d="M24 41.5C13 37.5 10.5 24 24 6.5c13.5 17.5 11 31 0 35z" fill="#fff"/><path d="M24 40V16M24 32l-6.5-5.5M24 32l6.5-5.5M24 25l-5-4.5M24 25l5-4.5" fill="none" stroke="#2e7d32" stroke-linecap="round" stroke-width="1.7"/><circle cx="37.5" cy="10.5" fill="#ffb74d" r="3.6"/></svg>
      <span class="gi-logo-text">
        <b>Green <span>India</span></b>
        <small>Revolution Makes Green</small>
      </span>
    </a>

    <nav class="gi-nav-wrap" id="gi-nav-wrap" aria-label="Main Navigation">
      <?php
      if ( has_nav_menu( 'primary' ) ) {
          wp_nav_menu( [
              'theme_location' => 'primary',
              'menu_class'     => 'gi-main-menu',
              'container'      => false,
          ] );
      } else {
          ?>
          <ul class="gi-main-menu">
            <li><a href="<?php echo esc_url( home_url( '/' ) ); ?>">Home</a></li>
            <li><a href="<?php echo esc_url( home_url( '/about-us/' ) ); ?>">About Us</a></li>
            <li class="menu-item-has-children">
              <a href="<?php echo esc_url( home_url( '/our-products/' ) ); ?>">Our Products</a>
              <ul class="sub-menu">
                <li><a href="<?php echo esc_url( home_url( '/carry-bags/' ) ); ?>">Shopping / Carry Bags</a></li>
                <li><a href="<?php echo esc_url( home_url( '/sambar-cover/' ) ); ?>">Sambar / Pouch Cover</a></li>
                <li><a href="<?php echo esc_url( home_url( '/grocery-bags/' ) ); ?>">Grocery Bags</a></li>
                <li><a href="<?php echo esc_url( home_url( '/transparent-bags/' ) ); ?>">Transparent Packaging Bags</a></li>
                <li><a href="<?php echo esc_url( home_url( '/garbage-bags/' ) ); ?>">Garbage Bags</a></li>
                <li><a href="<?php echo esc_url( home_url( '/d-cut/' ) ); ?>">Textile Bag / D-Cut Bag</a></li>
                <li><a href="<?php echo esc_url( home_url( '/flat-cover/' ) ); ?>">Flat Cover</a></li>
              </ul>
            </li>
            <li><a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>">Shop</a></li>
            <li><a href="<?php echo esc_url( home_url( '/certification/' ) ); ?>">Certification</a></li>
            <li><a href="<?php echo esc_url( home_url( '/contact/' ) ); ?>">Contact</a></li>
          </ul>
          <?php
      }
      ?>
    </nav>

    <div class="gi-header-actions">
      <?php
      $cart_count = ( function_exists( 'WC' ) && WC()->cart ) ? WC()->cart->get_cart_contents_count() : 0;
      $cart_url   = function_exists( 'wc_get_cart_url' ) ? wc_get_cart_url() : home_url( '/cart/' );
      ?>
      <a class="gi-header-cart" href="<?php echo esc_url( $cart_url ); ?>">
        <span>🛒 Cart</span>
        <b><?php echo intval( $cart_count ); ?></b>
      </a>
      <button type="button" class="gi-burger" id="gi-burger" aria-controls="gi-nav-wrap" aria-expanded="false" aria-label="Menu">☰</button>
    </div>
  </div>
</header>
<main class="gi-main-content">
