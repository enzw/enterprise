<!-- resources/views/items/manage_stocks.blade.php -->
@extends('layouts.app')

@section('content')
<div class="container">
    <h1 class="my-4">Manage Stock for {{ $item->name }} ({{ $item->sku }})</h1>
    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Location</th>
                <th>Quantity On Hand</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stocks as $entry)
                <tr>
                    <td>{{ $entry['location']->name ?? 'Default' }}</td>
                    <td>{{ $entry['stock']->quantity_on_hand }}</td>
                    <td>
                        <a href="{{ route('items.editStock', ['item' => $item->id, 'location' => $entry['location']->id]) }}" class="btn btn-sm btn-primary">Edit</a>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <a href="{{ route('items.index') }}" class="btn btn-secondary mt-3">Back to Items</a>
</div>
@endsection
