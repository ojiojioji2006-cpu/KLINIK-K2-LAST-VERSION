(function () {
    var form = document.getElementById('inv-form');
    var tbody = document.getElementById('inv-services');
    var tpl = document.getElementById('invoice-row-template');
    var addBtn = document.getElementById('inv-add');
    var obatEl = document.getElementById('inv-obat-sum');
    var discEl = document.getElementById('inv-discount');
    var taxEl = document.getElementById('inv-tax');
    var subOut = document.getElementById('inv-subtotal');
    var totOut = document.getElementById('inv-total');
    if (!form || !tbody || !tpl) { return; }

    var idx = tbody.querySelectorAll('.inv-row').length;

    function rp(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    }

    function num(el) {
        if (!el) { return 0; }
        var v = parseFloat(el.value);
        return isNaN(v) ? 0 : v;
    }

    function recalcGrand() {
        var rows = tbody.querySelectorAll('.inv-row');
        var servicesSum = 0;
        rows.forEach(function (row) {
            var sel = row.querySelector('[data-inv-select]');
            var qtyEl = row.querySelector('[data-inv-qty]');
            var priceCell = row.querySelector('[data-inv-price]');
            var subCell = row.querySelector('[data-inv-subtotal]');
            var opt = (sel && sel.selectedOptions && sel.selectedOptions[0]) || null;
            var chosen = opt && opt.value ? opt : null;
            var price = chosen ? parseFloat(chosen.getAttribute('data-price') || '0') : 0;
            var qty = qtyEl ? parseFloat(qtyEl.value || '0') : 0;
            if (isNaN(qty) || qty < 0) { qty = 0; }
            var sub = price * qty;
            if (priceCell) { priceCell.textContent = chosen ? rp(price) : '-'; }
            if (subCell) { subCell.textContent = chosen ? rp(sub) : '-'; }
            servicesSum += sub;
        });

        var obatSum = obatEl ? parseFloat(obatEl.getAttribute('data-value') || '0') : 0;
        if (isNaN(obatSum)) { obatSum = 0; }
        var discount = num(discEl);
        var tax = num(taxEl);
        var subtotal = obatSum + servicesSum;
        var total = subtotal - discount + tax;
        if (total < 0) { total = 0; }

        if (subOut) { subOut.textContent = rp(subtotal); }
        if (totOut) { totOut.textContent = rp(total); }
    }

    function addRow() {
        var node = tpl.content.cloneNode(true);
        var row = node.querySelector('.inv-row');
        var fields = row.querySelectorAll('[name]');
        for (var k = 0; k < fields.length; k++) {
            var nm = fields[k].getAttribute('name');
            fields[k].setAttribute('name', nm.replace('__IDX__', String(idx)));
        }
        idx = idx + 1;
        tbody.appendChild(node);
        recalcGrand();
    }

    form.addEventListener('input', recalcGrand);
    form.addEventListener('change', recalcGrand);
    form.addEventListener('click', function (e) {
        if (e.target.closest('#inv-add')) { addRow(); return; }
        var rm = e.target.closest('.inv-remove');
        if (rm) {
            var row = rm.closest('.inv-row');
            if (row) { row.remove(); }
            recalcGrand();
        }
    });

    recalcGrand();
    document.addEventListener('DOMContentLoaded', recalcGrand);
})();