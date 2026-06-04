@extends('layouts.app')

@section('page-title', 'Create Item')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header bg-success text-white">
                    <h5 class="mb-0">Create New Item</h5>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('items.store') }}">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="subsidiary_id" class="form-label">Subsidiary *</label>
                                    <select name="subsidiary_id" id="subsidiary_id" class="form-control @error('subsidiary_id') is-invalid @enderror" required>
                                        <option value="">Select Subsidiary</option>
                                        @foreach($subsidiaries as $sub)
                                        <option value="{{ $sub->id }}" @if(old('subsidiary_id') == $sub->id) selected @endif>{{ $sub->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="type" class="form-label">Item Type *</label>
                                    <select name="type" id="type" class="form-control @error('type') is-invalid @enderror" required>
                                        <option value="">Select Type</option>
                                        <option value="inventory" @if(old('type') == 'inventory') selected @endif>Inventory Item</option>
                                        <option value="non_inventory" @if(old('type') == 'non_inventory') selected @endif>Non-Inventory Item</option>
                                        <option value="service" @if(old('type') == 'service') selected @endif>Service Item</option>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Item Name *</label>
                                    <input type="text" name="name" id="name" class="form-control @error('name') is-invalid @enderror" value="{{ old('name') }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="sku" class="form-label">SKU (Stock Keeping Unit) *</label>
                                    <input type="text" name="sku" id="sku" class="form-control @error('sku') is-invalid @enderror" value="{{ old('sku') }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea name="description" id="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="units_type" class="form-label">Units Type</label>
                                    <input type="text" name="units_type" id="units_type" class="form-control" value="{{ old('units_type', 'Each') }}">
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="purchase_price" class="form-label">Purchase Price</label>
                                    <input type="number" name="purchase_price" id="purchase_price" class="form-control" step="0.01" value="{{ old('purchase_price') }}">
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="base_price" class="form-label">Base Price</label>
                                    <input type="number" name="base_price" id="base_price" class="form-control" step="0.01" value="{{ old('base_price') }}">
                                </div>
                            </div>
                        </div>

                        <hr>
                        <h6 class="mb-3">Accounting Configuration</h6>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="income_account_id" class="form-label">Income Account *</label>
                                    <select name="income_account_id" id="income_account_id" class="form-control @error('income_account_id') is-invalid @enderror" required>
                                        <option value="">Select Account</option>
                                        @foreach($accounts->where('type', 'income') as $acc)
                                        <option value="{{ $acc->id }}" @if(old('income_account_id') == $acc->id) selected @endif>
                                            {{ $acc->number }} - {{ $acc->name }}
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="tax_schedule_id" class="form-label">Tax Schedule</label>
                                    <select name="tax_schedule_id" id="tax_schedule_id" class="form-control">
                                        <option value="">None</option>
                                        @foreach($taxSchedules as $tax)
                                        <option value="{{ $tax->id }}" @if(old('tax_schedule_id') == $tax->id) selected @endif>
                                            {{ $tax->name }} ({{ $tax->rate }}%)
                                        </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div id="inventoryAccounts" style="display:none;">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="cogs_account_id" class="form-label">COGS Account</label>
                                        <select name="cogs_account_id" id="cogs_account_id" class="form-control">
                                            <option value="">Select Account</option>
                                            @foreach($accounts->where('type', 'expense') as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->number }} - {{ $acc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="mb-3">
                                        <label for="asset_account_id" class="form-label">Asset Account</label>
                                        <select name="asset_account_id" id="asset_account_id" class="form-control">
                                            <option value="">Select Account</option>
                                            @foreach($accounts->where('type', 'asset') as $acc)
                                            <option value="{{ $acc->id }}">{{ $acc->number }} - {{ $acc->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <div class="d-flex gap-2">
                                <button type="submit" class="btn btn-success">Create Item</button>
                                <a href="{{ route('items.index') }}" class="btn btn-secondary">Cancel</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card bg-light">
                <div class="card-body">
                    <h6>Item Types Guide</h6>
                    <hr>
                    <p><strong>Inventory Item:</strong><br>
                    <small>Purchased and stored in inventory. Can be sold. Requires COGS, Asset, and Income accounts.</small></p>
                    <p><strong>Non-Inventory Item:</strong><br>
                    <small>Purchased and sold but not stored. Requires Income and Expense accounts.</small></p>
                    <p><strong>Service Item:</strong><br>
                    <small>Services offered for sale. Requires only Income account.</small></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('type').addEventListener('change', function() {
    const inventoryAccounts = document.getElementById('inventoryAccounts');
    if (this.value === 'inventory') {
        inventoryAccounts.style.display = 'block';
    } else {
        inventoryAccounts.style.display = 'none';
    }
});

// Trigger on page load
document.getElementById('type').dispatchEvent(new Event('change'));
</script>
@endsection
