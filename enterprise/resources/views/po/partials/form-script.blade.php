<script>
const poItems = @json($items);
const poDepartments = @json($departments);
const initialLines = @json($initialLines);
let poRowIndex = 0;

function poEscape(value) {
    return String(value ?? '')
        .replaceAll('&', '&amp;')
        .replaceAll('<', '&lt;')
        .replaceAll('>', '&gt;')
        .replaceAll('"', '&quot;')
        .replaceAll("'", '&#039;');
}

function itemOptions(selectedId) {
    return '<option value="">Select Item</option>' + poItems.map(item =>
        `<option value="${item.id}" data-price="${item.purchase_price ?? 0}" ${String(item.id) === String(selectedId) ? 'selected' : ''}>
            ${poEscape(item.name)} (${poEscape(item.sku)})
        </option>`
    ).join('');
}

function departmentOptions(selectedId) {
    return '<option value="">None</option>' + poDepartments.map(department =>
        `<option value="${department.id}" ${String(department.id) === String(selectedId) ? 'selected' : ''}>
            ${poEscape(department.name)}
        </option>`
    ).join('');
}

function addPoRow(values = {}) {
    const index = poRowIndex++;
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><select name="items[${index}][item_id]" class="form-select item-select" required>${itemOptions(values.item_id)}</select></td>
        <td><select name="items[${index}][department_id]" class="form-select">${departmentOptions(values.department_id)}</select></td>
        <td><input type="number" name="items[${index}][quantity]" class="form-control qty-input" min="1" value="${poEscape(values.quantity ?? 1)}" required></td>
        <td><input type="number" name="items[${index}][unit_price]" class="form-control price-input" min="0" step="0.01" value="${poEscape(values.unit_price ?? 0)}" required></td>
        <td class="text-end"><strong class="line-amount">$0.00</strong></td>
        <td><button type="button" class="btn btn-sm btn-danger remove-row">&times;</button></td>
    `;
    document.getElementById('item-rows').appendChild(row);
    attachPoRowEvents(row);
    calculatePoRow(row);
}

function attachPoRowEvents(row) {
    row.querySelector('.qty-input').addEventListener('input', () => calculatePoRow(row));
    row.querySelector('.price-input').addEventListener('input', () => calculatePoRow(row));
    row.querySelector('.item-select').addEventListener('change', event => {
        const option = event.target.options[event.target.selectedIndex];
        row.querySelector('.price-input').value = Number(option.dataset.price || 0).toFixed(2);
        calculatePoRow(row);
    });
    row.querySelector('.remove-row').addEventListener('click', () => {
        if (document.querySelectorAll('#item-rows tr').length > 1) {
            row.remove();
            calculatePoSubtotal();
        }
    });
}

function calculatePoRow(row) {
    const quantity = Number(row.querySelector('.qty-input').value || 0);
    const price = Number(row.querySelector('.price-input').value || 0);
    row.querySelector('.line-amount').textContent = '$' + (quantity * price).toFixed(2);
    calculatePoSubtotal();
}

function calculatePoSubtotal() {
    let subtotal = 0;
    document.querySelectorAll('#item-rows tr').forEach(row => {
        subtotal += Number(row.querySelector('.qty-input').value || 0)
            * Number(row.querySelector('.price-input').value || 0);
    });
    document.getElementById('subtotal').textContent = '$' + subtotal.toFixed(2);
}

function syncSubsidiary() {
    const select = document.getElementById('vendor_id');
    const option = select.options[select.selectedIndex];
    document.getElementById('subsidiary_id').value = option?.dataset.subsidiary || '';
    document.getElementById('subsidiary_display').value = option?.dataset.subsidiaryName || '';
}

document.getElementById('vendor_id').addEventListener('change', syncSubsidiary);
document.getElementById('add-row').addEventListener('click', () => addPoRow());
initialLines.forEach(addPoRow);
syncSubsidiary();
</script>
