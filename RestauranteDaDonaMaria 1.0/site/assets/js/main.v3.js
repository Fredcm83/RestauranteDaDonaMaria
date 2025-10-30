// main.v3.js — tiny frontend brain for the order modal

(function () {
  // Hours badge + small helpers can live here if you want… trimmed for focus.

  // Build items from the modal rows
  function readItemsFromModal() {
    const rows = document.querySelectorAll('#orderItems .row');
    const items = [];
    let total = 0;
    rows.forEach(r => {
      const name  = (r.querySelector('.i-name')  ?.value || '').trim();
      const price = parseFloat(r.querySelector('.i-price') ?.value || '0') || 0;
      const qty   = parseInt(  r.querySelector('.i-qty')   ?.value || '1', 10) || 1;
      if (!name || qty < 1) return;
      const line_total = price * qty;
      total += line_total;
      items.push({ name, price, qty, line_total });
    });
    return { items, total };
  }

  // Serialize items into hidden fields before submit
  function wireOrderForm() {
    const form = document.getElementById('orderForm');
    if (!form) return;

    form.addEventListener('submit', function (e) {
      // If you want to preview/inspect, comment the next line for a minute
      // e.preventDefault();

      const { items, total } = readItemsFromModal();

      // Write JSON payload
      const payloadInput = document.getElementById('payload');
      if (payloadInput) {
        payloadInput.value = JSON.stringify({ items, total });
      }

      // Also add legacy fields item[]/price[]/qty[] for hosts that hate JSON
      // First clean any previous ones
      form.querySelectorAll('input[name="item[]"],input[name="price[]"],input[name="qty[]"]').forEach(n => n.remove());
      items.forEach(it => {
        const i1 = document.createElement('input');
        i1.type = 'hidden'; i1.name = 'item[]';  i1.value = it.name;
        const i2 = document.createElement('input');
        i2.type = 'hidden'; i2.name = 'price[]'; i2.value = String(it.price);
        const i3 = document.createElement('input');
        i3.type = 'hidden'; i3.name = 'qty[]';   i3.value = String(it.qty);
        form.appendChild(i1); form.appendChild(i2); form.appendChild(i3);
      });
    });
  }

  // Public API for opening the modal (the footer fallback uses this)
  window.RDDM = window.RDDM || {};
  window.RDDM.openOrder = function (trigger) {
    const modal = document.getElementById('modal');
    if (!modal) return;
    // Populate first row with clicked item
    const name  = trigger?.getAttribute('data-name')  || '';
    const price = trigger?.getAttribute('data-price') || '';
    const items = document.getElementById('orderItems');
    if (items) {
      items.innerHTML = '';
      const row = document.createElement('div');
      row.className = 'row';
      row.innerHTML =
        '<div><label>Item <input class="i-name" value="' + name.replace(/"/g,'&quot;') + '" readonly></label></div>' +
        '<div><label>Price <input class="i-price" value="' + String(price).replace(/"/g,'&quot;') + '" readonly></label></div>' +
        '<div><label>Qty <input class="i-qty" type="number" min="1" value="1"></label></div>';
      items.appendChild(row);
    }
    modal.style.display = 'flex';
    modal.setAttribute('aria-hidden', 'false');
    const close = document.getElementById('closeModal');
    if (close) close.onclick = () => { modal.style.display = 'none'; modal.setAttribute('aria-hidden','true'); };
  };

  // One-time click delegation for any [data-order] button
  if (!window.__RDDM_WIRED__) {
    window.__RDDM_WIRED__ = true;
    document.addEventListener('click', function (e) {
      const t = e.target.closest('[data-order]');
      if (!t) return;
      e.preventDefault();
      window.RDDM.openOrder(t);
    });
  }

  // Wire the form once DOM is ready
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', wireOrderForm);
  } else {
    wireOrderForm();
  }
})();
