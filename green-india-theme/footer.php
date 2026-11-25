</main>
<footer class="gi-site-footer">
  <div class="gi-wrap">
    <div class="gi-footer-grid">
      <div>
        <a class="gi-logo" href="<?php echo esc_url( home_url( '/' ) ); ?>" style="margin-bottom:10px">
          <svg aria-hidden="true" class="gi-lmark" focusable="false" viewBox="0 0 48 48"><defs><linearGradient gradientUnits="userSpaceOnUse" id="gi-logo-f" x1="6" x2="42" y1="2" y2="48"><stop stop-color="#4caf50"/><stop offset="1" stop-color="#1b5e20"/></linearGradient></defs><circle cx="24" cy="24" fill="url(#gi-logo-f)" r="24"/><path d="M24 41.5C13 37.5 10.5 24 24 6.5c13.5 17.5 11 31 0 35z" fill="#fff"/><path d="M24 40V16M24 32l-6.5-5.5M24 32l6.5-5.5M24 25l-5-4.5M24 25l5-4.5" fill="none" stroke="#2e7d32" stroke-linecap="round" stroke-width="1.7"/><circle cx="37.5" cy="10.5" fill="#ffb74d" r="3.6"/></svg>
          <span class="gi-logo-text">
            <b>Green <span>India</span></b>
            <small>Revolution Makes Green</small>
          </span>
        </a>
        <h3>From Nature, Back to Nature.</h3>
        <p style="margin:0">Certified compostable products by CPCB.</p>
      </div>
      <div class="gi-fsitemap">
        <h3>Site Map</h3>
        <a href="<?php echo esc_url( home_url( '/carry-bags/' ) ); ?>"><span class="gi-ficon" aria-hidden="true">🛍️</span>Shopping / Carry Bags</a>
        <a href="<?php echo esc_url( home_url( '/sambar-cover/' ) ); ?>"><span class="gi-ficon" aria-hidden="true">🥡</span>Sambar / Pouch Cover</a>
        <a href="<?php echo esc_url( home_url( '/grocery-bags/' ) ); ?>"><span class="gi-ficon" aria-hidden="true">🥬</span>Grocery Bags</a>
        <a href="<?php echo esc_url( home_url( '/transparent-bags/' ) ); ?>"><span class="gi-ficon" aria-hidden="true">📦</span>Transparent Packaging Bags</a>
        <a href="<?php echo esc_url( home_url( '/garbage-bags/' ) ); ?>"><span class="gi-ficon" aria-hidden="true">🗑️</span>Garbage Bags</a>
        <a href="<?php echo esc_url( home_url( '/d-cut/' ) ); ?>"><span class="gi-ficon" aria-hidden="true">✂️</span>Textile Bag / D-Cut Bag</a>
        <a href="<?php echo esc_url( home_url( '/flat-cover/' ) ); ?>"><span class="gi-ficon" aria-hidden="true">📄</span>Flat Cover</a>
        <a href="<?php echo esc_url( home_url( '/shop/' ) ); ?>"><span class="gi-ficon" aria-hidden="true">🛒</span>Shop</a>
        <a href="<?php echo esc_url( home_url( '/certification/' ) ); ?>"><span class="gi-ficon" aria-hidden="true">🏅</span>Certification</a>
      </div>
      <div>
        <h3>Contact</h3>
        <div class="gi-fcontact"><span class="gi-ficon" aria-hidden="true">📍</span><span>No.4, Sahjanand Ruber Industrial Estate, Bapinagar, Ahmedabad, Gujarat – 380024</span></div>
        <div class="gi-fcontact"><span class="gi-ficon" aria-hidden="true">☎</span><a href="tel:+919489465676">+91 94894 65676</a></div>
        <div class="gi-fcontact"><span class="gi-ficon" aria-hidden="true">✉</span><a href="mailto:greenindiabiobags.it@gmail.com">greenindiabiobags.it@gmail.com</a></div>
        <div class="gi-fcontact">
          <span class="gi-ficon" aria-hidden="true"><svg fill="currentColor" height="18" viewBox="0 0 24 24" width="18" xmlns="http://www.w3.org/2000/svg"><path d="M20.52 3.48A11.8 11.8 0 0 0 12.13 0C5.6 0 .28 5.31.28 11.85c0 2.09.55 4.13 1.6 5.93L.2 24l6.37-1.67a11.84 11.84 0 0 0 5.56 1.41h.01c6.53 0 11.85-5.32 11.85-11.85 0-3.17-1.23-6.15-3.47-8.41ZM12.14 21.7a9.8 9.8 0 0 1-4.99-1.36l-.36-.21-3.78.99 1.01-3.69-.24-.38a9.8 9.8 0 1 1 8.36 4.65Zm5.38-7.34c-.29-.15-1.72-.85-1.99-.95-.27-.1-.46-.15-.65.15-.19.29-.75.95-.92 1.14-.17.19-.34.22-.63.07-.29-.15-1.22-.45-2.33-1.44-.86-.77-1.44-1.72-1.61-2.01-.17-.29-.02-.45.13-.6.13-.13.29-.34.44-.51.15-.17.19-.29.29-.49.1-.19.05-.36-.02-.51-.07-.15-.65-1.57-.89-2.15-.23-.56-.47-.48-.65-.49h-.56c-.19 0-.51.07-.78.36-.27.29-1.02 1-1.02 2.43s1.05 2.82 1.19 3.01c.15.19 2.06 3.15 4.99 4.42.7.3 1.24.48 1.66.61.7.22 1.34.19 1.84.12.56-.08 1.72-.7 1.96-1.38.24-.68.24-1.26.17-1.38-.07-.12-.27-.19-.56-.34Z"/></svg></span>
          <a href="https://wa.me/919489465676" target="_blank" rel="noopener noreferrer">WhatsApp us</a>
        </div>
      </div>
    </div>
    <div class="gi-footer-copy">
      &copy; <?php echo esc_html( date( 'Y' ) ); ?> Green India Private Limited. All rights reserved. &middot; Revolution Makes Green
    </div>
  </div>
</footer>
<script>
(function(){
  var btn = document.getElementById('gi-burger');
  var nav = document.getElementById('gi-nav-wrap');
  var hd  = document.getElementById('gi-site-header');
  if (btn && nav) {
    btn.addEventListener('click', function(){
      var open = nav.classList.toggle('open');
      btn.setAttribute('aria-expanded', open ? 'true' : 'false');
      btn.textContent = open ? '✕' : '☰';
    });
  }
  if (hd) {
    window.addEventListener('scroll', function(){
      hd.classList.toggle('sc', window.scrollY > 20);
    }, { passive: true });
  }
})();
</script>
<?php wp_footer(); ?>
</body>
</html>
