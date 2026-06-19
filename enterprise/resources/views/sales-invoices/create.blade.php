@extends('layouts.app')

@section('page-title', 'Create Sales Invoice')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-lg-10">
            <div class="card">
                <div class="card-header bg-success text-white"><h5 class="mb-0">Invoice Shipped Items - {{ $salesOrder->so_number }}</h5></div>
                <div class="card-body">
                    <div class="row g-3 mb-4">
                        <div class="col-md-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block">Customer</small><strong>{{ $salesOrder->customer->name }}</strong></div></div>
                        <div class="col-md-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block">Currency</small><strong>{{ $salesOrder->currency_code }}</strong></div></div>
                        <div class="col-md-4"><div class="p-3 bg-light rounded"><small class="text-muted d-block">Warehouse</small><strong>{{ $salesOrder->location->name }}</strong></div></div>
                    </div>
                    <form method="POST" action="{{ route('sales-invoices.store', $salesOrder) }}" id="invoice-form">
                        @csrf
                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label">Invoice Date *</label>
                                <input type="date" name="invoice_date" class="form-control" value="{{ old('invoice_date', date('Y-m-d')) }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Due Date</label>
                                <input type="date" name="due_date" class="form-control" value="{{ old('due_date', date('Y-m-d', strtotime('+30 days'))) }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Memo</label>
                                <input type="text" name="memo" class="form-control" value="{{ old('memo') }}">
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table table-bordered align-middle">
                                <thead class="table-light">
                                    <tr><th>Item</th><th class="text-end">Shipped</th><th class="text-end">Invoiced</th><th class="text-end">Available</th><th style="width:150px">Invoice Qty</th><th class="text-end">Price</th><th class="text-end">Amount</th></tr>
                                </thead>
                                <tbody>
                                    @foreach($salesOrder->items as $index => $line)
                                        @php $available = $line->quantity_shipped - $line->quantity_invoiced; @endphp
                                        @if($available > 0)
                                            <tr class="invoice-line" data-price="{{ $line->unit_price }}">
                                                <td>
                                                    <strong>{{ $line->item->sku }}</strong> - {{ $line->item->name }}
                                                    <input type="hidden" name="items[{{ $index }}][so_item_id]" value="{{ $line->id }}">
                                                </td>
                                                <td class="text-end">{{ $line->quantity_shipped }}</td>
                                                <td class="text-end">{{ $line->quantity_invoiced }}</td>
                                                <td class="text-end"><strong>{{ $available }}</strong></td>
                                                <td><input type="number" name="items[{{ $index }}][quantity]" class="form-control invoice-qty" min="0" max="{{ $available }}" value="{{ old("items.$index.quantity", $available) }}"></td>
                                                <td class="text-end">${{ number_format($line->unit_price, 2) }}</td>
                                                <td class="text-end"><strong class="invoice-line-amount">$0.00</strong></td>
                                            </tr>
                                        @endif
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="table-light"><td colspan="6" class="text-end"><strong>Total:</strong></td><td class="text-end"><strong id="invoice-total">$0.00</strong></td></tr>
                                </tfoot>
                            </table>
                        </div>
                        <div class="d-flex gap-2">
                            <button class="btn btn-success" type="submit">Create Draft Invoice</button>
                            <a href="{{ route('sales-orders.show', $salesOrder) }}" class="btn btn-secondary">Cancel</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function calculateInvoiceTotal() {
    let total = 0;
    document.querySelectorAll('.invoice-line').forEach(row => {
        const quantity = Number(row.querySelector('.invoice-qty').value || 0);
        const amount = quantity * Number(row.dataset.price || 0);
        row.querySelector('.invoice-line-amount').textContent = '$' + amount.toFixed(2);
        total += amount;
    });
    document.getElementById('invoice-total').textContent = '$' + total.toFixed(2);
}
document.querySelectorAll('.invoice-qty').forEach(input => input.addEventListener('input', calculateInvoiceTotal));
calculateInvoiceTotal();
</script>
@endsection
