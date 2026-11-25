<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$products = function_exists( 'wc_get_products' ) ? wc_get_products( [ 'status' => [ 'publish', 'draft' ], 'limit' => -1 ] ) : [];
$msg = sanitize_text_field( wp_unslash( $_GET['msg'] ?? '' ) );
?>
<div class="wrap gi-admin-wrap">
    <h1>Green India — WooCommerce Shop, Stock, Price, Size, Color & Pieces Manager</h1>
    <p>Every change here updates your WooCommerce catalog and automatically updates your Elementor Shop Page.</p>

    <?php if ( $msg ) : ?>
        <div class="notice notice-success is-dismissible"><p>Action completed: <strong><?php echo esc_html( $msg ); ?></strong></p></div>
    <?php endif; ?>

    <div class="gi-admin-banner">
        <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
            <?php wp_nonce_field( 'gi_one_click_import' ); ?>
            <input type="hidden" name="action" value="gi_run_one_click_import" />
            <button type="submit" class="button button-primary button-hero">One-Click Import All Green India Pages, Menus, Images & WooCommerce Products</button>
        </form>
    </div>

    <h2>Add New Shop Product</h2>
    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" class="gi-admin-add-form">
        <?php wp_nonce_field( 'gi_quick_add' ); ?>
        <input type="hidden" name="action" value="gi_quick_add_product" />
        <input type="text" name="name" placeholder="Product Name" required />
        <input type="text" name="category" placeholder="Category (e.g., Carry Bags)" />
        <input type="number" name="mrp_price" placeholder="MRP Price (₹)" required />
        <input type="number" name="price" placeholder="Actual Price (₹)" required />
        <input type="number" name="total_stock_qty" placeholder="Total Stock" value="120" required />
        <input type="number" name="stock_qty" placeholder="Available Stock" value="100" required />
        <select name="package_unit"><option value="kg">Per kg</option><option value="pack">Per pack</option></select>
        <input type="text" name="package_label" placeholder="Package Label (e.g., 1 kg bundle)" />
        <input type="text" name="short_desc" placeholder="Short description" />
        <button type="submit" class="button button-primary">+ Add Product to Shop</button>
    </form>

    <h2>Products Dashboard (Click &ldquo;Edit Product&rdquo; to Manage Price, Stock, Quality, Sizes &amp; Colors)</h2>
    <table class="widefat striped">
        <thead>
            <tr>
                <th>Product</th>
                <th>SKU &amp; Category</th>
                <th>Stock Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ( $products as $p ) :
            $pid         = $p->get_id();
            $unit        = get_post_meta( $pid, '_gi_package_unit', true ) ?: 'kg';
            $pkg_label   = get_post_meta( $pid, '_gi_package_label', true ) ?: '1 kg bundle';
            $mrp_val     = get_post_meta( $pid, '_gi_mrp_price', true ) ?: $p->get_regular_price();
            $act_val     = $p->get_sale_price() !== '' ? $p->get_sale_price() : $p->get_price();
            $avail_stock = intval( $p->get_stock_quantity() ?? 0 );
            $total_stock = get_post_meta( $pid, '_gi_total_stock_qty', true );
            $total_stock = ( $total_stock !== '' && $total_stock !== false ) ? intval( $total_stock ) : $avail_stock;
            $sizes       = get_post_meta( $pid, '_gi_sizes_json', true ) ?: '[]';
            $colors      = get_post_meta( $pid, '_gi_colors_json', true ) ?: '[]';
            $qualities   = get_post_meta( $pid, '_gi_qualities_json', true ) ?: '[]';
            $material    = get_post_meta( $pid, '_gi_material_note', true ) ?: 'Compostable, starch-based';
            $standard    = get_post_meta( $pid, '_gi_standard_note', true ) ?: 'EN13432 / ISO 17088';
            $storage     = get_post_meta( $pid, '_gi_storage_note', true ) ?: 'Store in a dry and dark place.';
            $aka         = get_post_meta( $pid, '_gi_also_known_as', true ) ?: '';
            $size_note   = get_post_meta( $pid, '_gi_size_thickness_note', true ) ?: 'Standard sizes and thickness are mentioned in the table above, but we can also customise based on your requirement.';
            $highlights  = get_post_meta( $pid, '_gi_highlights', true ) ?: '';
            $cat_terms   = wp_get_post_terms( $pid, 'product_cat', [ 'fields' => 'names' ] );
            $cat_name    = ! empty( $cat_terms ) && ! is_wp_error( $cat_terms ) ? $cat_terms[0] : '';
            $in_stock    = $p->is_in_stock() && $avail_stock > 0;
        ?>
            <tr class="gi-admin-row"
                data-pid="<?php echo esc_attr( $pid ); ?>"
                data-name="<?php echo esc_attr( $p->get_name() ); ?>"
                data-category="<?php echo esc_attr( $cat_name ); ?>"
                data-short="<?php echo esc_attr( $p->get_short_description() ); ?>"
                data-mrp="<?php echo esc_attr( $mrp_val ); ?>"
                data-price="<?php echo esc_attr( $act_val ); ?>"
                data-total-stock="<?php echo esc_attr( $total_stock ); ?>"
                data-avail-stock="<?php echo esc_attr( $avail_stock ); ?>"
                data-unit="<?php echo esc_attr( $unit ); ?>"
                data-pkg="<?php echo esc_attr( $pkg_label ); ?>"
                data-sizes="<?php echo esc_attr( $sizes ); ?>"
                data-colors="<?php echo esc_attr( $colors ); ?>"
                data-qualities="<?php echo esc_attr( $qualities ); ?>"
                data-material="<?php echo esc_attr( $material ); ?>"
                data-standard="<?php echo esc_attr( $standard ); ?>"
                data-storage="<?php echo esc_attr( $storage ); ?>"
                data-aka="<?php echo esc_attr( $aka ); ?>"
                data-size-note="<?php echo esc_attr( $size_note ); ?>"
                data-highlights="<?php echo esc_attr( $highlights ); ?>">
                <td><strong><?php echo esc_html( $p->get_name() ); ?></strong></td>
                <td><code><?php echo esc_html( $p->get_sku() ?: ( 'GI-' . $pid ) ); ?></code> &middot; <?php echo esc_html( $cat_name ); ?></td>
                <td><?php echo $in_stock ? esc_html( 'In Stock (' . $avail_stock . ' / ' . $total_stock . ' ' . $unit . ')' ) : 'Out of Stock'; ?></td>
                <td>
                    <button type="button" class="button button-primary gi-adm-toggle-edit">Edit Product</button>
                    <a class="button button-link-delete" href="<?php echo esc_url( wp_nonce_url( admin_url( 'admin-post.php?action=gi_quick_delete_product&product_id=' . $pid ), 'gi_quick_delete' ) ); ?>" onclick="return confirm('Remove this product from Shop?');">Delete</a>
                </td>
            </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
</div>
<script>
(function(){
  var saveActionUrl = <?php echo wp_json_encode( admin_url( 'admin-post.php' ) ); ?>;
  var saveNonce = <?php echo wp_json_encode( wp_create_nonce( 'gi_quick_save' ) ); ?>;
  document.querySelectorAll('.gi-adm-toggle-edit').forEach(function(btn){
    btn.addEventListener('click', function(){
      var tr = btn.closest('tr');
      var next = tr.nextElementSibling;
      if (next && next.classList.contains('gi-adm-matrix-row')) { next.remove(); return; }
      var pid = tr.getAttribute('data-pid');
      var pName = tr.getAttribute('data-name') || '';
      var pCat = tr.getAttribute('data-category') || '';
      var pShort = tr.getAttribute('data-short') || '';
      var pMrp = tr.getAttribute('data-mrp') || '0';
      var pPrice = tr.getAttribute('data-price') || '0';
      var pTotStock = tr.getAttribute('data-total-stock') || '0';
      var pAvailStock = tr.getAttribute('data-avail-stock') || '0';
      var pUnit = tr.getAttribute('data-unit') || 'kg';
      var pPkg = tr.getAttribute('data-pkg') || '1 kg bundle';
      var pMaterial = tr.getAttribute('data-material') || '';
      var pStandard = tr.getAttribute('data-standard') || '';
      var pStorage = tr.getAttribute('data-storage') || '';
      var pAka = tr.getAttribute('data-aka') || '';
      var pSizeNote = tr.getAttribute('data-size-note') || '';
      var pHighlights = tr.getAttribute('data-highlights') || '';
      var sizes = [], colors = [], qualities = [];
      try { sizes = JSON.parse(tr.getAttribute('data-sizes') || '[]'); } catch(e){}
      try { colors = JSON.parse(tr.getAttribute('data-colors') || '[]'); } catch(e){}
      try { qualities = JSON.parse(tr.getAttribute('data-qualities') || '[]'); } catch(e){}

      var mRow = document.createElement('tr');
      mRow.className = 'gi-adm-matrix-row';
      var td = document.createElement('td');
      td.colSpan = 4;
      td.style.background = '#f6f8f1';
      td.style.padding = '16px';

      var form = document.createElement('form');
      form.method = 'post';
      form.action = saveActionUrl;
      form.innerHTML =
        '<input type="hidden" name="action" value="gi_quick_save_product" />' +
        '<input type="hidden" name="_wpnonce" value="' + saveNonce + '" />' +
        '<input type="hidden" name="product_id" value="' + pid + '" />' +
        '<input type="hidden" name="sizes_json" class="gi-adm-sizes" />' +
        '<input type="hidden" name="colors_json" class="gi-adm-colors" />' +
        '<input type="hidden" name="qualities_json" class="gi-adm-qualities" />';

      var topGrid = document.createElement('div');
      topGrid.style.cssText = 'display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:10px;background:#fff;border:1px solid #ccd0d4;padding:12px;border-radius:6px;margin-bottom:12px';
      topGrid.innerHTML =
        '<div><label style="display:block;font-size:11px;font-weight:600">Product Name</label><input type="text" name="name" style="width:100%" /></div>' +
        '<div><label style="display:block;font-size:11px;font-weight:600">Category</label><input type="text" name="category" style="width:100%" /></div>' +
        '<div><label style="display:block;font-size:11px;font-weight:600">MRP (₹)</label><input type="number" name="mrp_price" style="width:100%" /></div>' +
        '<div><label style="display:block;font-size:11px;font-weight:600">Actual Price (₹)</label><input type="number" name="price" style="width:100%" /></div>' +
        '<div><label style="display:block;font-size:11px;font-weight:600">Package Unit</label><select name="package_unit" style="width:100%"><option value="kg">kg</option><option value="pack">pack</option></select></div>' +
        '<div><label style="display:block;font-size:11px;font-weight:600">Package Label</label><input type="text" name="package_label" style="width:100%" /></div>' +
        '<div><label style="display:block;font-size:11px;font-weight:600">Total Stock</label><input type="number" name="total_stock_qty" class="gi-adm-total-cap-stock" style="width:100%" /></div>' +
        '<div><label style="display:block;font-size:11px;font-weight:600">Available Stock</label><input type="number" name="stock_qty" class="gi-adm-total-stock" style="width:100%" /></div>' +
        '<div style="grid-column:1/-1"><label style="display:block;font-size:11px;font-weight:600">Short Description</label><input type="text" name="short_desc" style="width:100%" /></div>';

      topGrid.querySelector('[name="name"]').value = pName;
      topGrid.querySelector('[name="category"]').value = pCat;
      topGrid.querySelector('[name="mrp_price"]').value = pMrp;
      topGrid.querySelector('[name="price"]').value = pPrice;
      topGrid.querySelector('[name="package_unit"]').value = pUnit;
      topGrid.querySelector('[name="package_label"]').value = pPkg;
      topGrid.querySelector('[name="total_stock_qty"]').value = pTotStock;
      topGrid.querySelector('[name="stock_qty"]').value = pAvailStock;
      topGrid.querySelector('[name="short_desc"]').value = pShort;

      var sTa = form.querySelector('.gi-adm-sizes');
      var cTa = form.querySelector('.gi-adm-colors');
      var qTa = form.querySelector('.gi-adm-qualities');
      var availIn = topGrid.querySelector('.gi-adm-total-stock');
      var totalCapIn = topGrid.querySelector('.gi-adm-total-cap-stock');
      var matrixMount = document.createElement('div');

      function syncJson(updateProductTotals){
        sTa.value = JSON.stringify(sizes);
        cTa.value = JSON.stringify(colors);
        qTa.value = JSON.stringify(qualities);
        var grandAvail = 0, grandTotal = 0;
        sizes.forEach(function(s){
          if (s.colorStocks && colors.length) {
            var rSum = 0;
            colors.forEach(function(c){ rSum += parseInt(s.colorStocks[c.name] || 0, 10) || 0; });
            s.stockQty = rSum;
            grandAvail += rSum;
          } else {
            grandAvail += parseInt(s.stockQty || 0, 10) || 0;
          }
          grandTotal += parseInt(s.totalStockQty !== undefined ? s.totalStockQty : (s.stockQty || 0), 10) || 0;
        });
        if (updateProductTotals && sizes.length) {
          if (availIn) availIn.value = grandAvail;
          if (totalCapIn) totalCapIn.value = Math.max(grandAvail, grandTotal);
        }
      }

      function render(){
        matrixMount.innerHTML = '';

        // Quality Section
        var qBox = document.createElement('div');
        qBox.style.cssText = 'background:#fff;border:1px solid #ccd0d4;padding:12px;border-radius:6px;margin-bottom:12px';
        qBox.innerHTML = '<strong>Quality Section (Price Tiers):</strong>';
        var qList = document.createElement('div');
        qList.style.cssText = 'display:flex;flex-wrap:wrap;gap:8px;margin-top:8px';
        qualities.forEach(function(q, qi){
          var qCard = document.createElement('div');
          qCard.style.cssText = 'display:flex;align-items:center;gap:6px;background:#f6f8f1;border:1px solid #ccd0d4;padding:6px 8px;border-radius:6px';
          qCard.innerHTML =
            '<input class="q-name" style="width:120px" placeholder="Quality Name" value="'+(q.name||'')+'" />'+
            '<span style="font-size:11px">MRP:</span><input type="number" class="q-mrp" style="width:70px" value="'+(q.mrp||q.price||0)+'" />'+
            '<span style="font-size:11px">Actual:</span><input type="number" class="q-price" style="width:70px" value="'+(q.price||0)+'" />';
          qCard.querySelector('.q-name').oninput = function(e){
            var oldName = q.name; var nextName = e.target.value;
            q.name = nextName;
            sizes.forEach(function(s){
              if (s.piecesByQuality && oldName in s.piecesByQuality) {
                s.piecesByQuality[nextName] = s.piecesByQuality[oldName];
                if (nextName !== oldName) delete s.piecesByQuality[oldName];
              }
            });
            syncJson(false);
          };
          qCard.querySelector('.q-mrp').oninput = function(e){ q.mrp = parseFloat(e.target.value)||0; syncJson(false); };
          qCard.querySelector('.q-price').oninput = function(e){ q.price = parseFloat(e.target.value)||0; syncJson(false); };
          var rmQ = document.createElement('button'); rmQ.type='button'; rmQ.className='button-link-delete'; rmQ.textContent='×';
          rmQ.onclick = function(){
            var removed = qualities.splice(qi,1)[0];
            sizes.forEach(function(s){ if (s.piecesByQuality && removed) delete s.piecesByQuality[removed.name]; });
            syncJson(false); render();
          };
          qCard.appendChild(rmQ);
          qList.appendChild(qCard);
        });
        qBox.appendChild(qList);
        var addQRow = document.createElement('div');
        addQRow.style.cssText = 'display:flex;gap:6px;align-items:center;margin-top:8px';
        addQRow.innerHTML =
          '<input class="nq-name" placeholder="Quality Name (e.g. Nice quality)" style="width:170px" />'+
          '<input type="number" class="nq-mrp" placeholder="MRP (₹)" style="width:85px" />'+
          '<input type="number" class="nq-price" placeholder="Actual (₹)" style="width:85px" />'+
          '<button type="button" class="button nq-btn">+ Add Quality</button>';
        addQRow.querySelector('.nq-btn').onclick = function(){
          var qn = addQRow.querySelector('.nq-name').value.trim();
          var qp = parseFloat(addQRow.querySelector('.nq-price').value)||0;
          var qm = parseFloat(addQRow.querySelector('.nq-mrp').value)||qp;
          if (!qn || qp <= 0) return;
          qualities.push({ id: qn.toLowerCase().replace(/\W+/g,'-'), name: qn, price: qp, mrp: Math.max(qp, qm) });
          sizes.forEach(function(s){ s.piecesByQuality = s.piecesByQuality || {}; s.piecesByQuality[qn] = s.piecesPerUnit || 0; });
          syncJson(false); render();
        };
        qBox.appendChild(addQRow);
        matrixMount.appendChild(qBox);

        var cWrap = document.createElement('div');
        cWrap.style.marginBottom = '10px';
        cWrap.innerHTML = '<strong>Colors: </strong>';
        colors.forEach(function(c, ci){
          var chip = document.createElement('span');
          chip.style.cssText = 'display:inline-flex;align-items:center;gap:4px;background:#fff;border:1px solid #ccd0d4;padding:2px 8px;border-radius:12px;margin:2px;font-size:12px';
          chip.innerHTML = '<i style="width:10px;height:10px;border-radius:50%;background:'+c.hex+';border:1px solid #999;display:inline-block"></i>'+c.name+' ';
          var rm = document.createElement('a');
          rm.href = '#'; rm.textContent = '×'; rm.style.color = '#b32d2e'; rm.style.textDecoration = 'none';
          rm.onclick = function(e){ e.preventDefault(); colors.splice(ci,1); sizes.forEach(function(s){ if(s.colorStocks) delete s.colorStocks[c.name]; }); syncJson(true); render(); };
          chip.appendChild(rm);
          cWrap.appendChild(chip);
        });
        var newC = document.createElement('input'); newC.placeholder = 'New Color'; newC.style.width = '110px'; newC.style.marginLeft = '8px';
        var newH = document.createElement('input'); newH.type = 'color'; newH.value = '#2e7d32';
        var addC = document.createElement('button'); addC.type = 'button'; addC.className = 'button'; addC.textContent = '+ Color';
        addC.onclick = function(){ if(!newC.value.trim()) return; var nm=newC.value.trim(); colors.push({id:nm.toLowerCase().replace(/\W+/g,'-'),name:nm,hex:newH.value}); sizes.forEach(function(s){ s.colorStocks=s.colorStocks||{}; s.colorStocks[nm]=0; }); syncJson(true); render(); };
        cWrap.appendChild(newC); cWrap.appendChild(newH); cWrap.appendChild(addC);
        matrixMount.appendChild(cWrap);

        var tbl = document.createElement('table'); tbl.className = 'widefat striped';
        var th = '<thead><tr><th>Size</th><th>Quality</th><th>Package</th>';
        if (qualities.length) {
          qualities.forEach(function(q){ th += '<th>'+q.name+' pcs</th>'; });
        } else {
          th += '<th>Pieces / kg</th>';
        }
        th += '<th>Carrying Cap. (kg)</th><th>MRP (₹)</th><th>Actual (₹)</th><th>Total Stock</th>';
        colors.forEach(function(c){ th += '<th>'+c.name+' Avail.</th>'; });
        th += '<th>Avail. Stock</th><th></th></tr></thead><tbody></tbody>';
        tbl.innerHTML = th;
        var tb = tbl.querySelector('tbody');
        sizes.forEach(function(s, si){
          var tr2 = document.createElement('tr');
          var szTotVal = s.totalStockQty !== undefined ? s.totalStockQty : (s.stockQty || 0);
          var rowHtml =
            '<td><input class="sz-l" style="width:90px" value="'+(s.sizeLabel||'')+'"/></td>'+
            '<td><input class="sz-qt" style="width:90px" placeholder="Quality" value="'+(s.typeLabel||'')+'"/></td>'+
            '<td><input class="sz-pkg" style="width:95px" placeholder="Default pkg" value="'+(s.packageOverride||'')+'"/></td>';
          if (qualities.length) {
            qualities.forEach(function(q, qi){
              var pv = (s.piecesByQuality && s.piecesByQuality[q.name] !== undefined) ? (s.piecesByQuality[q.name] === null ? '' : s.piecesByQuality[q.name]) : (s.piecesPerUnit || '');
              rowHtml += '<td><input class="sz-pq" data-qi="'+qi+'" style="width:60px" placeholder="—" value="'+pv+'"/></td>';
            });
          } else {
            rowHtml += '<td><input class="sz-p" style="width:65px" value="'+(s.piecesPerUnit!==undefined?s.piecesPerUnit:'')+'"/></td>';
          }
          rowHtml +=
            '<td><input class="sz-cap" style="width:75px" placeholder="e.g. 2 kg" value="'+(s.carryingCapacity||'')+'"/></td>'+
            '<td><input type="number" class="sz-m" style="width:70px" placeholder="Default" value="'+(s.mrpOverride||'')+'"/></td>'+
            '<td><input type="number" class="sz-a" style="width:70px" placeholder="Default" value="'+(s.priceOverride||'')+'"/></td>'+
            '<td><input type="number" class="sz-ts" style="width:70px" value="'+szTotVal+'"/></td>';
          tr2.innerHTML = rowHtml;
          tr2.querySelector('.sz-l').oninput = function(e){ s.sizeLabel = e.target.value; syncJson(false); };
          tr2.querySelector('.sz-qt').oninput = function(e){ s.typeLabel = e.target.value || undefined; syncJson(false); };
          tr2.querySelector('.sz-pkg').oninput = function(e){ s.packageOverride = e.target.value; syncJson(false); };
          if (qualities.length) {
            tr2.querySelectorAll('.sz-pq').forEach(function(inp){
              inp.oninput = function(e){
                var qi = parseInt(inp.getAttribute('data-qi'), 10);
                var qObj = qualities[qi]; if (!qObj) return;
                s.piecesByQuality = s.piecesByQuality || {};
                var raw = e.target.value.trim();
                s.piecesByQuality[qObj.name] = raw === '' ? null : (/^\d+$/.test(raw) ? parseInt(raw,10) : raw);
                if (qi === 0 && raw !== '') s.piecesPerUnit = s.piecesByQuality[qObj.name];
                syncJson(false);
              };
            });
          } else {
            tr2.querySelector('.sz-p').oninput = function(e){ var v=e.target.value; s.piecesPerUnit = /^\d+$/.test(v.trim()) ? parseInt(v,10) : v; syncJson(false); };
          }
          tr2.querySelector('.sz-cap').oninput = function(e){ s.carryingCapacity = e.target.value; syncJson(false); };
          tr2.querySelector('.sz-m').oninput = function(e){ s.mrpOverride = e.target.value ? parseFloat(e.target.value) : undefined; syncJson(false); };
          tr2.querySelector('.sz-a').oninput = function(e){ s.priceOverride = e.target.value ? parseFloat(e.target.value) : undefined; syncJson(false); };
          tr2.querySelector('.sz-ts').oninput = function(e){ s.totalStockQty = Math.max(0, parseInt(e.target.value,10)||0); syncJson(true); };
          colors.forEach(function(c){
            var tdC = document.createElement('td');
            var inp = document.createElement('input'); inp.type = 'number'; inp.style.width = '55px';
            inp.value = (s.colorStocks && s.colorStocks[c.name] !== undefined) ? s.colorStocks[c.name] : 0;
            inp.oninput = function(e){ s.colorStocks = s.colorStocks || {}; s.colorStocks[c.name] = Math.max(0, parseInt(e.target.value,10)||0); syncJson(true); tdTot.textContent = s.stockQty; };
            tdC.appendChild(inp); tr2.appendChild(tdC);
          });
          var tdTot = document.createElement('td');
          if (colors.length) {
            tdTot.textContent = s.stockQty || 0;
          } else {
            var sAvailIn = document.createElement('input'); sAvailIn.type='number'; sAvailIn.style.width='65px'; sAvailIn.value = s.stockQty || 0;
            sAvailIn.oninput = function(e){ s.stockQty = Math.max(0, parseInt(e.target.value,10)||0); syncJson(true); };
            tdTot.appendChild(sAvailIn);
          }
          tr2.appendChild(tdTot);
          var tdRm = document.createElement('td');
          var rmB = document.createElement('button'); rmB.type='button'; rmB.className='button-link-delete'; rmB.textContent='Remove';
          rmB.onclick = function(){ sizes.splice(si,1); syncJson(true); render(); };
          tdRm.appendChild(rmB); tr2.appendChild(tdRm);
          tb.appendChild(tr2);
        });
        matrixMount.appendChild(tbl);

        var addBox = document.createElement('div');
        addBox.style.cssText = 'display:flex;flex-wrap:wrap;gap:6px;align-items:center;margin-top:10px;padding:10px;background:#fff;border:1px solid #ccd0d4;border-radius:6px';
        addBox.innerHTML =
          '<strong>Add Size:</strong>'+
          '<input class="ns-l" placeholder="Size (e.g. 12 × 16 in)" style="width:110px" />'+
          '<input class="ns-qt" placeholder="Quality (opt.)" style="width:95px" />'+
          '<input class="ns-pkg" placeholder="Package (e.g. 1 kg bundle)" style="width:120px" />'+
          '<input class="ns-p" type="number" placeholder="Pieces / kg" value="100" style="width:80px" />'+
          '<input class="ns-cap" placeholder="Capacity (e.g. 2 kg)" style="width:95px" />'+
          '<input class="ns-m" type="number" placeholder="MRP (₹)" style="width:75px" />'+
          '<input class="ns-a" type="number" placeholder="Actual (₹)" style="width:80px" />'+
          '<input class="ns-ts" type="number" placeholder="Total Stock" value="30" style="width:80px" />'+
          '<input class="ns-as" type="number" placeholder="Avail. Stock" value="25" style="width:80px" />'+
          '<button type="button" class="button ns-btn">+ Add Size</button>';
        addBox.querySelector('.ns-btn').onclick = function(){
          var lbl = addBox.querySelector('.ns-l').value.trim();
          if (!lbl) return;
          var pcsVal = parseInt(addBox.querySelector('.ns-p').value,10)||0;
          var avail = Math.max(0, parseInt(addBox.querySelector('.ns-as').value,10)||0);
          var tot = Math.max(avail, parseInt(addBox.querySelector('.ns-ts').value,10)||0);
          var cs = {};
          if (colors.length) {
            var each = Math.floor(avail / colors.length);
            var rem = avail % colors.length;
            colors.forEach(function(c, idx){ cs[c.name] = each + (idx === 0 ? rem : 0); });
          }
          var pbq = {};
          if (qualities.length) {
            qualities.forEach(function(q){ pbq[q.name] = pcsVal; });
          }
          sizes.push({
            id: 'sz-' + Date.now(),
            sizeLabel: lbl,
            typeLabel: addBox.querySelector('.ns-qt').value.trim() || undefined,
            packageOverride: addBox.querySelector('.ns-pkg').value.trim() || undefined,
            piecesPerUnit: pcsVal,
            piecesByQuality: qualities.length ? pbq : undefined,
            carryingCapacity: addBox.querySelector('.ns-cap').value.trim() || undefined,
            mrpOverride: addBox.querySelector('.ns-m').value ? parseFloat(addBox.querySelector('.ns-m').value) : undefined,
            priceOverride: addBox.querySelector('.ns-a').value ? parseFloat(addBox.querySelector('.ns-a').value) : undefined,
            totalStockQty: tot,
            stockQty: avail,
            colorStocks: cs
          });
          syncJson(true); render();
        };
        matrixMount.appendChild(addBox);
      }

      syncJson(false);
      render();

      var specsGrid = document.createElement('div');
      specsGrid.style.cssText = 'display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:10px;background:#fff;border:1px solid #ccd0d4;padding:12px;border-radius:6px;margin-top:12px';
      specsGrid.innerHTML =
        '<div style="grid-column:1/-1;font-weight:600">Material, Standard, Storage, Size &amp; Thickness &amp; Highlights</div>' +
        '<div><label style="display:block;font-size:11px;font-weight:600">Material Note</label><input type="text" name="material_note" style="width:100%" /></div>' +
        '<div><label style="display:block;font-size:11px;font-weight:600">Standard Note</label><input type="text" name="standard_note" style="width:100%" /></div>' +
        '<div><label style="display:block;font-size:11px;font-weight:600">Storage Note</label><input type="text" name="storage_note" style="width:100%" /></div>' +
        '<div><label style="display:block;font-size:11px;font-weight:600">Also Known As</label><input type="text" name="also_known_as" style="width:100%" /></div>' +
        '<div style="grid-column:1/-1"><label style="display:block;font-size:11px;font-weight:600">Size and Thickness Note</label><input type="text" name="size_thickness_note" style="width:100%" /></div>' +
        '<div style="grid-column:1/-1"><label style="display:block;font-size:11px;font-weight:600">Highlights (one per line)</label><textarea name="highlights" rows="3" style="width:100%"></textarea></div>';
      specsGrid.querySelector('[name="material_note"]').value = pMaterial;
      specsGrid.querySelector('[name="standard_note"]').value = pStandard;
      specsGrid.querySelector('[name="storage_note"]').value = pStorage;
      specsGrid.querySelector('[name="also_known_as"]').value = pAka;
      specsGrid.querySelector('[name="size_thickness_note"]').value = pSizeNote;
      specsGrid.querySelector('[name="highlights"]').value = pHighlights;

      var saveBar = document.createElement('div');
      saveBar.style.cssText = 'display:flex;justify-content:flex-end;gap:8px;margin-top:12px';
      var cancelBtn = document.createElement('button'); cancelBtn.type='button'; cancelBtn.className='button'; cancelBtn.textContent='Close';
      cancelBtn.onclick = function(){ mRow.remove(); };
      var saveBtn = document.createElement('button'); saveBtn.type='submit'; saveBtn.className='button button-primary'; saveBtn.textContent='Save Product Changes';
      saveBar.appendChild(cancelBtn); saveBar.appendChild(saveBtn);

      form.appendChild(topGrid);
      form.appendChild(matrixMount);
      form.appendChild(specsGrid);
      form.appendChild(saveBar);
      td.appendChild(form);
      mRow.appendChild(td);
      tr.parentNode.insertBefore(mRow, tr.nextSibling);
    });
  });
})();
</script>
