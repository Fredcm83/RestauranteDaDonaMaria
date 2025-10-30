<?php
// footer.php (v6, robust picker + payload submit, no optional chaining)
// Assumes BASE_PATH and db() exist
$base = rtrim(BASE_PATH ?? '', '/');

// Build item list for picker
$menu_items = [];
try {
  require_once __DIR__ . '/../db/db.php';
  $pdo = db();
  $stmt = $pdo->query("SELECT id,name,price FROM menu_items WHERE available=1 ORDER BY name ASC");
  $menu_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Throwable $e) {
  $menu_items = [];
}
?>
<footer role="contentinfo">
  <div class="container footer-inner">
    <div>&copy; <span id="year"></span> Restaurante da Dona Maria • Online only</div>
    <div class="small">
      Mon–Sat 9:00–18:00 •
      <a href="tel:+12019237409">+1 201-923-7409</a> •
      <a href="mailto:fcm030383@gmail.com">fcm030383@gmail.com</a>
    </div>
  </div>
</footer>

<?php
  // v6 bump + filemtime cache-bust
  $jsRel = '/assets/js/main.v3.js';
  $mtime = @filemtime(__DIR__ . '/../assets/js/main.v3.js');
  $v     = '6' . ($mtime ? ('-' . $mtime) : '');
  $jsUrl = $base . $jsRel . '?v=' . $v;
?>
<script src="<?php echo $jsUrl; ?>"></script>

<script>
// ====== harden for older browsers (no optional chaining) ======
(function(){
  // expose items for picker
  window.RDDM_PICKER_ITEMS = <?php echo json_encode($menu_items, JSON_UNESCAPED_UNICODE); ?> || [];

  function q(sel, root){ return (root||document).querySelector(sel); }
  function qa(sel, root){ return Array.prototype.slice.call((root||document).querySelectorAll(sel)); }

  function escapeHtml(s){
    return String(s).replace(/[&<>"']/g, function(m){return ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]);});
  }

  // ----- rows
  function appendOrderRow(name, price, qty){
    var container = q('#orderItems'); if(!container) return;
    var row = document.createElement('div');
    row.className = 'row';
    row.innerHTML =
      '<div><label>Item <input class="i-name" value="'+escapeHtml(name||'')+'" readonly></label></div>' +
      '<div><label>Price <input class="i-price" value="'+escapeHtml(String(price||''))+'" readonly></label></div>' +
      '<div><label>Qty <input class="i-qty" type="number" min="1" value="'+String(qty||1)+'"></label></div>';
    container.appendChild(row);
  }

  // ----- ensure order modal opens from product buttons
  function ensureOpen(trigger){
    var modal = q('#modal'); if(!modal) return;
    var name  = trigger && trigger.getAttribute('data-name')  || '';
    var price = trigger && trigger.getAttribute('data-price') || '';
    var items = q('#orderItems');
    if(items){ items.innerHTML=''; appendOrderRow(name, price, 1); }
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden','false');
    var close = q('#closeModal');
    if(close){ close.onclick = function(){ modal.style.display='none'; modal.setAttribute('aria-hidden','true'); }; }
  }

  // Public API fallback
  window.RDDM = window.RDDM || {};
  if (typeof window.RDDM.openOrder !== 'function') {
    window.RDDM.openOrder = ensureOpen;
  }

  // ----- picker
  function buildPicker(){
    // if exists, just return it
    var ex = q('#picker');
    if (ex) return ex;

    var wrap = document.createElement('div');
    wrap.id = 'picker';
    wrap.className = 'modal-backdrop';
    wrap.style.display = 'none';
    wrap.setAttribute('aria-hidden','true');
    wrap.innerHTML =
      '<div class="modal small" role="dialog" aria-modal="true" aria-labelledby="pickTitle">' +
        '<header style="display:flex;justify-content:space-between;align-items:center">' +
          '<h3 id="pickTitle" style="margin:0">Add item</h3>' +
          '<button type="button" class="btn secondary" id="pickerClose">Close</button>' +
        '</header>' +
        '<div style="margin:.75rem 0">' +
          '<input id="pickerSearch" type="text" placeholder="Search dishes…" style="width:100%;padding:.55rem;border:1px solid #ddd;border-radius:8px">' +
        '</div>' +
        '<div id="pickerList" style="max-height:320px;overflow:auto;border:1px solid #eee;border-radius:10px;padding:.5rem"></div>' +
      '</div>';
    document.body.appendChild(wrap);

    // close
    q('#pickerClose', wrap).onclick = function(){ hidePicker(); };

    // search input
    q('#pickerSearch', wrap).addEventListener('input', function(){
      renderPickerList(this.value);
    });

    renderPickerList('');
    return wrap;
  }

  function renderPickerList(filterText){
    var list = q('#pickerList'); if(!list) return;
    var items = Array.isArray(window.RDDM_PICKER_ITEMS) ? window.RDDM_PICKER_ITEMS : [];
    var qtxt = (filterText||'').toLowerCase();
    var filtered = qtxt ? items.filter(function(x){ return (x.name||'').toLowerCase().includes(qtxt); }) : items;

    list.innerHTML = filtered.map(function(x){
      return '<button type="button" data-pick="'+x.id+'" ' +
             'style="width:100%;text-align:left;margin:.25rem 0;padding:.55rem .7rem;border:1px solid #eee;border-radius:8px;background:#fafafa;cursor:pointer">' +
             '<div style="display:flex;justify-content:space-between;gap:10px">' +
             '<span>'+escapeHtml(x.name||'')+'</span>' +
             '<span class="muted">$'+Number(x.price||0).toFixed(2)+'</span>' +
             '</div></button>';
    }).join('') || '<div class="small muted">No matches.</div>';

    qa('[data-pick]', list).forEach(function(btn){
      btn.onclick = function(){
        var id = btn.getAttribute('data-pick');
        var arr = Array.isArray(window.RDDM_PICKER_ITEMS) ? window.RDDM_PICKER_ITEMS : [];
        var found = arr.find(function(z){ return String(z.id) === String(id); });
        if(found){ appendOrderRow(found.name, Number(found.price||0).toFixed(2), 1); hidePicker(); }
      };
    });
  }

  function showPicker(){
    // if we have zero items (db not loaded), don't block user: add blank row
    if (!Array.isArray(window.RDDM_PICKER_ITEMS) || window.RDDM_PICKER_ITEMS.length === 0) {
      appendOrderRow('', '', 1);
      return;
    }
    var wrap = buildPicker();
    wrap.style.display = 'flex';
    wrap.setAttribute('aria-hidden','false');
    var input = q('#pickerSearch', wrap);
    if (input){ setTimeout(function(){ input.focus(); }, 0); }
  }

  function hidePicker(){
    var wrap = q('#picker'); if(!wrap) return;
    wrap.style.display = 'none';
    wrap.setAttribute('aria-hidden','true');
  }

  // ----- serialize payload on submit (JSON + legacy arrays)
  function readItems(){
    var rows = qa('#orderItems .row');
    var items = []; var total = 0;
    rows.forEach(function(r){
      var name  = (q('.i-name',  r)||{}).value || '';
      var price = parseFloat(((q('.i-price', r)||{}).value || '0')) || 0;
      var qty   = parseInt(  ((q('.i-qty',   r)||{}).value || '1'), 10) || 1;
      name = name.trim();
      if (!name || qty < 1) return;
      var line_total = price * qty;
      total += line_total;
      items.push({ name:name, price:price, qty:qty, line_total:line_total });
    });
    return { items: items, total: total };
  }

  function wireForm(){
    var form = q('#orderForm'); if(!form) return;
    form.addEventListener('submit', function(){
      var data = readItems();
      var payload = q('#payload'); if (payload) { payload.value = JSON.stringify({ items: data.items, total: data.total }); }
      // legacy arrays for paranoid hosts
      qa('input[name="item[]"],input[name="price[]"],input[name="qty[]"]', form).forEach(function(n){ n.parentNode.removeChild(n); });
      data.items.forEach(function(it){
        var i1=document.createElement('input'); i1.type='hidden'; i1.name='item[]';  i1.value=it.name;
        var i2=document.createElement('input'); i2.type='hidden'; i2.name='price[]'; i2.value=String(it.price);
        var i3=document.createElement('input'); i3.type='hidden'; i3.name='qty[]';   i3.value=String(it.qty);
        form.appendChild(i1); form.appendChild(i2); form.appendChild(i3);
      });
    });
  }

  // ----- bind clicks safely (and provide inline fallback)
  function wireClicks(){
    document.addEventListener('click', function(e){
      var orderBtn = e.target.closest ? e.target.closest('[data-order]') : null;
      if (orderBtn){ e.preventDefault(); (window.RDDM && typeof window.RDDM.openOrder==='function' ? window.RDDM.openOrder : ensureOpen)(orderBtn); return; }
      var addBtn = e.target.closest ? e.target.closest('#addItem') : null;
      if (addBtn){ e.preventDefault(); showPicker(); return; }
    });

    // Also attach a direct onclick fallback to #addItem (in case closest is weird)
    var addInline = q('#addItem');
    if (addInline){
      addInline.onclick = function(ev){ ev.preventDefault(); showPicker(); };
    }
  }

  // boot after DOM is ready (plus immediate attempt)
  function boot(){
    wireForm();
    wireClicks();
  }
  if (document.readyState === 'loading'){
    document.addEventListener('DOMContentLoaded', boot);
  } else {
    boot();
  }

  // expose fallback for inline onclick
  window.__RDDM_showPicker = showPicker;

  // expose for debugging if needed
  window.__RDDM_appendRow = appendOrderRow;

})();
</script>

<!-- Order modal markup -->
<div class="modal-backdrop" id="modal" aria-hidden="true">
  <div class="modal" role="dialog" aria-modal="true" aria-labelledby="orderTitle">
    <header><h2 id="orderTitle">Order</h2></header>

    <form id="orderForm" method="post" action="<?php echo $base; ?>/order.php">
      <div id="orderItems"></div>

      <!-- Inline fallback calls a global function if events are blocked -->
      <button class="btn secondary" id="addItem" type="button" onclick="window.__RDDM_showPicker && window.__RDDM_showPicker(); return false;">Add another item</button>

      <hr class="sep"/>

      <div class="row">
        <div><label>Name <input required name="name" id="custName"/></label></div>
        <div><label>Phone <input required name="phone" id="custPhone"/></label></div>
        <div><label>Email <input name="email" id="custEmail" type="email"/></label></div>
        <div><label>Preferred time <input name="time" id="custTime"/></label></div>
      </div>

      <label>Delivery address <input name="address" id="custAddress"/></label>
      <label>Notes <textarea name="notes" id="custNotes" rows="3"></textarea></label>

      <input type="hidden" name="payload" id="payload"/>

      <div id="hoursNote" class="notice hidden">
        We’re currently closed. We’ll confirm once we open.
      </div>

      <div class="actions">
        <button class="btn" id="submitOrder" type="submit">Submit & Open WhatsApp</button>
        <button class="btn secondary" id="closeModal" type="button">Close</button>
      </div>
    </form>
  </div>
</div>
