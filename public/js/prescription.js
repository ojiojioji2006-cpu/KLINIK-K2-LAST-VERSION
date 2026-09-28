(function () {
    var tbody = document.getElementById('rx-items');
    var tpl = document.getElementById('rx-row-template');
    var addBtn = document.getElementById('rx-add');
    if (!tbody || !tpl) { return; }

    var idx = tbody.querySelectorAll('.rx-row').length;

    function rp(n) {
        return 'Rp ' + Number(n || 0).toLocaleString('id-ID');
    }

    function recalcRow(row) {
        var sel = row.querySelector('[data-rx-select]');
        var qtyEl = row.querySelector('[data-rx-qty]');
        var priceCell = row.querySelector('[data-rx-price]');
        var subCell = row.querySelector('[data-rx-subtotal]');
        var opt = (sel && sel.selectedOptions && sel.selectedOptions[0]) || null;
        var chosen = opt && opt.value ? opt : null;
        var price = chosen ? parseFloat(chosen.getAttribute('data-price') || '0') : 0;
        var qty = qtyEl ? parseFloat(qtyEl.value || '0') : 0;
        if (priceCell) { priceCell.textContent = chosen ? rp(price) : '-'; }
        if (subCell) { subCell.textContent = chosen ? rp(price * qty) : '-'; }
    }

    function recalcAll() {
        tbody.querySelectorAll('.rx-row').forEach(recalcRow);
    }

    function addRow() {
        var node = tpl.content.cloneNode(true);
        var row = node.querySelector('.rx-row');
        var fields = row.querySelectorAll('[name]');
        for (var k = 0; k < fields.length; k++) {
            var nm = fields[k].getAttribute('name');
            fields[k].setAttribute('name', nm.replace('__IDX__', String(idx)));
        }
        idx = idx + 1;
        tbody.appendChild(node);
        recalcRow(tbody.lastElementChild);
    }

    if (addBtn) { addBtn.addEventListener('click', addRow); }

    tbody.addEventListener('input', function (e) {
        var row = e.target.closest('.rx-row');
        if (row) { recalcRow(row); }
    });
    tbody.addEventListener('change', function (e) {
        var row = e.target.closest('.rx-row');
        if (row) { recalcRow(row); }
    });
    tbody.addEventListener('click', function (e) {
        var btn = e.target.closest('.rx-remove');
        if (btn) {
            var row = btn.closest('.rx-row');
            if (row) { row.remove(); }
        }
    });

    recalcAll();
    document.addEventListener('DOMContentLoaded', recalcAll);
})();