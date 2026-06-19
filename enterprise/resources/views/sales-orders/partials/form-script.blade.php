<script>
const salesItems = @json($items);
const salesInitialLines = @json($initialLines);
let salesRowIndex = 0;

function salesEscape(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function salesItemOptions(selectedId) {
    return '<option value="">Select Item</option>' + salesItems.map(item =>
        `<option value="${item.id}" data-price="${item.base_price ?? 0}" ${String(item.id) === String(selectedId) ? 'selected' : ''}>
            ${salesEscape(item.sku)} - ${salesEscape(item.name)} (${salesEscape(item.type.replace('_', ' '))})
        </option>`
    ).join('');
}

function addSalesRow(values = {}) {
    const index = salesRowIndex++;
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><select name="items[${index}][item_id]" class="form-select item-select" required>${salesItemOptions(values.item_id)}</select></td>
        <td><input type="number" name="items[${index}][quantity]" class="form-control qty-input" min="1" value="${salesEscape(values.quantity ?? 1)}" required></td>
        <td><input type="number" name="items[${index}][unit_price]" class="form-control price-input" min="0" step="0.01" value="${salesEscape(values.unit_price ?? 0)}" required></td>
        <td class="text-end"><strong class="line-amount">$0.00</strong></td>
        <td><button type="button" class="btn btn-sm btn-danger remove-row">&times;</button></td>
    `;
    document.getElementById('so-item-rows').appendChild(row);

    row.querySelector('.item-select').addEventListener('change', event => {
        const option = event.target.options[event.target.selectedIndex];
        row.querySelector('.price-input').value = Number(option.dataset.price || 0).toFixed(2);
        calculateSalesRow(row);
    });
    row.querySelector('.qty-input').addEventListener('input', () => calculateSalesRow(row));
    row.querySelector('.price-input').addEventListener('input', () => calculateSalesRow(row));
    row.querySelector('.remove-row').addEventListener('click', () => {
        if (document.querySelectorAll('#so-item-rows tr').length > 1) {
            row.remove();
            calculateSalesTotal();
        }
    });
    calculateSalesRow(row);
}

function calculateSalesRow(row) {
    const quantity = Number(row.querySelector('.qty-input').value || 0);
    const price = Number(row.querySelector('.price-input').value || 0);
    row.querySelector('.line-amount').textContent = '$' + (quantity * price).toFixed(2);
    calculateSalesTotal();
}

function calculateSalesTotal() {
    let total = 0;
    document.querySelectorAll('#so-item-rows tr').forEach(row => {
        total += Number(row.querySelector('.qty-input').value || 0)
            * Number(row.querySelector('.price-input').value || 0);
    });
    document.getElementById('so-subtotal').textContent = '$' + total.toFixed(2);
}

function syncSalesSubsidiary() {
    const select = document.getElementById('customer_id');
    const option = select.options[select.selectedIndex];
    document.getElementById('subsidiary_id').value = option?.dataset.subsidiary || '';
    document.getElementById('subsidiary_display').value = option?.dataset.subsidiaryName || '';
}

document.getElementById('customer_id').addEventListener('change', syncSalesSubsidiary);
document.getElementById('add-so-row').addEventListener('click', () => addSalesRow());
salesInitialLines.forEach(addSalesRow);
syncSalesSubsidiary();
</script>
