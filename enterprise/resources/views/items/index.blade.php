@extends('layouts.app')

@section('page-title', 'Items')

@section('content')
<div class="container-fluid">
    <div class="row mb-3">
        <div class="col-md-6">
            <h3>Items</h3>
        </div>
        <div class="col-md-6 text-end">
            <a href="{{ route('items.create') }}" class="btn btn-success">+ Create Item</a>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>SKU</th>
                        <th>Name</th>
                        <th>Type</th>
                        <th>Purchase Price</th>
                        <th>Base Price</th>
                        <th>Active</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($items as $item)
                    <tr>
                        <td><strong>{{ $item->sku }}</strong></td>
                        <td>{{ $item->name }}</td>
                        <td><span class="badge bg-info">{{ ucfirst(str_replace('_', ' ', $item->type)) }}</span></td>
                        <td>${{ $item->purchase_price }}</td>
                        <td>${{ $item->base_price }}</td>
                        <td>
                            @if($item->is_active)
                            <span class="badge bg-success">Yes</span>
                            @else
                            <span class="badge bg-danger">No</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('items.edit', $item) }}" class="btn btn-sm btn-warning">Edit</a>
                            @if(auth()->user()->current_role === 'admin')
                            <form method="POST" action="{{ route('items.destroy', $item) }}" style="display:inline;">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('Are you sure?')">Delete</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-muted py-4">No items found</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-3">
        {{ $items->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection
