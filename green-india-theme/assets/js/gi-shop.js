(function($){
  // Hero slider
  $(function(){
    var $sl = $('#gi-hero-slider');
    if ($sl.length) {
      var idx = 0, $slides = $sl.find('.gi-slide'), $dots = $sl.find('.gi-dots i');
      function showSlide(n){
        idx = (n + $slides.length) % $slides.length;
        $slides.removeClass('on').eq(idx).addClass('on');
        $dots.removeClass('on').eq(idx).addClass('on');
      }
      $sl.on('click', '[data-gi-sl]', function(){ showSlide(idx + parseInt($(this).data('gi-sl'), 10)); });
      $sl.on('click', '[data-gi-dot]', function(){ showSlide(parseInt($(this).data('gi-dot'), 10)); });
      setInterval(function(){ showSlide(idx + 1); }, 5500);
    }
  });

  // Shop category filter
  $(document).on('click', '.gi-chip', function(){
    var cat = $(this).data('gi-cat');
    $(this).siblings().removeClass('on').end().addClass('on');
    $('.gi-card').each(function(){ $(this).toggle(cat === 'all' || $(this).data('cat') === cat); });
  });

  // Product configurator live price, MRP, % OFF, quality pieces & per-(size x color) stock
  function updatePdp($pdp){
    var $qSel = $pdp.find('[data-opt-group="quality"] .gi-opt.on');
    var $sSel = $pdp.find('[data-opt-group="size"] .gi-opt.on');
    var $cSel = $pdp.find('[data-opt-group="color"] .gi-opt.on');

    var sPrice = $sSel.length && $sSel.attr('data-price-override') ? parseFloat($sSel.attr('data-price-override')) : 0;
    var sMrp = $sSel.length && $sSel.attr('data-mrp-override') ? parseFloat($sSel.attr('data-mrp-override')) : 0;
    var price = sPrice > 0 ? sPrice : ($qSel.length ? parseFloat($qSel.data('price')) : parseFloat($pdp.data('base-price')));
    var mrp = sMrp > 0 ? sMrp : ($qSel.length && $qSel.data('mrp') ? parseFloat($qSel.data('mrp')) : parseFloat($pdp.data('base-mrp') || price));

    var piecesPerUnit = $sSel.length ? $sSel.data('pieces') : 0;
    var qualityUnavailable = false;
    if ($sSel.length && $qSel.length) {
      var pbqRaw = $sSel.attr('data-pieces-by-quality');
      var pbq = {};
      try { pbq = pbqRaw ? JSON.parse(pbqRaw) : {}; } catch(e){}
      var qName = $qSel.data('val');
      if (qName && pbq && Object.prototype.hasOwnProperty.call(pbq, qName)) {
        if (pbq[qName] === null || pbq[qName] === '') {
          qualityUnavailable = true;
          piecesPerUnit = 0;
        } else {
          piecesPerUnit = pbq[qName];
        }
      }
    }
    var qty = Math.max(1, parseInt($pdp.find('.gi-q-in').val(), 10) || 1);
    var pkgLabel = ($sSel.length && $sSel.attr('data-package')) ? $sSel.attr('data-package') : ($pdp.data('package-label') || '');
    var capacityLabel = ($sSel.length && $sSel.attr('data-capacity')) ? $sSel.attr('data-capacity') : 'Standard';

    $pdp.find('.gi-live-price').text('₹' + Math.round(price).toLocaleString('en-IN'));
    if (pkgLabel) $pdp.find('.gi-live-pkg').text(pkgLabel);
    $pdp.find('.gi-live-capacity').text(capacityLabel);
    if (mrp > price && price > 0) {
      var off = Math.round(((mrp - price) / mrp) * 100);
      $pdp.find('.gi-live-mrp').text('MRP ₹' + Math.round(mrp).toLocaleString('en-IN')).show();
      $pdp.find('.gi-live-off').text(off + '% OFF').show();
    } else {
      $pdp.find('.gi-live-mrp, .gi-live-off').hide();
    }
    if (qualityUnavailable) {
      $pdp.find('.gi-live-pieces').text('Not available in this quality');
    } else if (typeof piecesPerUnit === 'string' && isNaN(parseFloat(piecesPerUnit))) {
      $pdp.find('.gi-live-pieces').text(piecesPerUnit);
    } else if (parseFloat(piecesPerUnit) > 0) {
      $pdp.find('.gi-live-pieces').text((parseFloat(piecesPerUnit) * qty).toLocaleString('en-IN') + ' pcs');
    } else {
      $pdp.find('.gi-live-pieces').text('Custom');
    }

    // Variant stock lookup for selected size + color & total stock
    var unit = $pdp.data('unit') || 'kg';
    var stockVal = parseInt($pdp.data('base-stock') || 0, 10);
    var totalStockVal = parseInt($pdp.data('total-stock') || stockVal, 10);
    if ($sSel.length) {
      var cStocksRaw = $sSel.attr('data-color-stocks');
      var cStocks = {};
      try { cStocks = cStocksRaw ? JSON.parse(cStocksRaw) : {}; } catch(e){}
      var colorName = $cSel.length ? $cSel.data('val') : '';
      if (colorName && cStocks && cStocks[colorName] !== undefined) {
        stockVal = parseInt(cStocks[colorName], 10) || 0;
      } else if ($sSel.attr('data-stock') !== undefined && $sSel.attr('data-stock') !== '') {
        stockVal = parseInt($sSel.attr('data-stock'), 10) || 0;
      }
      if ($sSel.attr('data-total-stock') !== undefined && $sSel.attr('data-total-stock') !== '') {
        totalStockVal = parseInt($sSel.attr('data-total-stock'), 10) || 0;
      }
    }
    $pdp.find('.gi-live-total-stock').text(totalStockVal + ' ' + unit);
    var $stLine = $pdp.find('.gi-variant-stock-line');
    var $btn = $pdp.find('.gi-woo-add');
    if (qualityUnavailable) {
      $stLine.addClass('out').find('.gi-live-stock').text('Not available in this quality');
      $btn.prop('disabled', true).text('Unavailable');
    } else if (stockVal <= 0) {
      $stLine.addClass('out').find('.gi-live-stock').text('Out of Stock for selected option');
      $btn.prop('disabled', true).text('Out of Stock');
    } else {
      $stLine.removeClass('out').find('.gi-live-stock').text('Available Stock: ' + stockVal + ' ' + unit);
      $btn.prop('disabled', false).text('Add to WooCommerce Cart');
    }
  }

  $(document).on('click', '.gi-pdp .gi-opt', function(){
    $(this).siblings().removeClass('on').end().addClass('on');
    updatePdp($(this).closest('.gi-pdp'));
  });
  $(document).on('click', '.gi-q-inc', function(){
    var $pdp = $(this).closest('.gi-pdp'), $in = $pdp.find('.gi-q-in');
    var max = parseInt($pdp.data('max-qty'), 10) || 100;
    $in.val(Math.min(max, (parseInt($in.val(), 10) || 1) + 1));
    updatePdp($pdp);
  });
  $(document).on('click', '.gi-q-dec', function(){
    var $pdp = $(this).closest('.gi-pdp'), $in = $pdp.find('.gi-q-in');
    $in.val(Math.max(1, (parseInt($in.val(), 10) || 1) - 1));
    updatePdp($pdp);
  });
  $(document).on('click', '.gi-woo-add', function(){
    var $pdp = $(this).closest('.gi-pdp'), $btn = $(this);
    $btn.prop('disabled', true).text('Adding...');
    $.post(giWooConfig.ajaxUrl, {
      action: 'gi_add_to_woo_cart',
      nonce: giWooConfig.nonce,
      product_id: $pdp.data('product-id'),
      qty: $pdp.find('.gi-q-in').val(),
      size: $pdp.find('[data-opt-group="size"] .gi-opt.on').data('val') || '',
      color: $pdp.find('[data-opt-group="color"] .gi-opt.on').data('val') || '',
      quality: $pdp.find('[data-opt-group="quality"] .gi-opt.on').data('val') || '',
      pieces: $pdp.find('.gi-live-pieces').text(),
      unit: $pdp.data('unit')
    }, function(res){
      $btn.prop('disabled', false).text(res.success ? 'Added to Cart ✓' : 'Add to WooCommerce Cart');
      if (res.success && res.data && res.data.cart_count !== undefined) {
        $('.gi-header-cart b').text(res.data.cart_count);
      }
    });
  });

  // Contact WhatsApp form
  $(document).on('click', '.gi-cf-submit', function(){
    var $box = $(this).closest('.gi-contact-form');
    var name = $.trim($box.find('.gi-cf-name').val());
    var phone = String($box.find('.gi-cf-phone').val() || '').replace(/\D/g, '').slice(-10);
    var msg = $.trim($box.find('.gi-cf-msg').val());
    var $err = $box.find('.gi-cf-err').text('');
    if (!name) { $err.text('Please enter your name or company.'); return; }
    if (phone.length !== 10) { $err.text('Please enter a valid 10-digit mobile number.'); return; }
    if (!msg) { $err.text('Please tell us what you need.'); return; }
    var wa = $box.data('wa') || giWooConfig.whatsapp;
    var text = 'Hello Green India, I have an enquiry:\n\nName: ' + name + '\nPhone: ' + phone + '\nEnquiry: ' + msg;
    window.open('https://wa.me/' + wa + '?text=' + encodeURIComponent(text), '_blank', 'noopener');
  });

  $(function(){ $('.gi-pdp').each(function(){ updatePdp($(this)); }); });
})(jQuery);
