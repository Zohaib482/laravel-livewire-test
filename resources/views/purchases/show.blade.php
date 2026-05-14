@extends('layouts.app')

@section('title', 'Purchase #'.$purchase->id)

@section('content')
    <div class="space-y-6">
        <div class="flex items-center justify-between gap-4">
            <div>
                <h1 class="text-2xl font-semibold text-slate-900">Purchase #{{ $purchase->id }}</h1>
                <p class="mt-1 text-sm text-slate-600">Created {{ $purchase->created_at->format('M d, Y g:i A') }}</p>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('purchases.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
                    Back to list
                </a>

                @can('manage-purchases')
                    <a href="{{ route('purchases.edit', $purchase) }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                        Edit
                    </a>
                @endcan
            </div>
        </div>

        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <table class="min-w-full divide-y divide-slate-200 text-sm">
                <thead class="bg-slate-50 text-left text-slate-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">Item</th>
                        <th class="px-4 py-3 font-medium">Brand</th>
                        <th class="px-4 py-3 font-medium">Qty</th>
                        <th class="px-4 py-3 font-medium">Price</th>
                        <th class="px-4 py-3 font-medium">Line Total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-200">
                    @foreach ($purchase->purchaseItems as $line)
                        <tr>
                            <td class="px-4 py-3">{{ $line->item->name }}</td>
                            <td class="px-4 py-3">{{ $line->brand->name }}</td>
                            <td class="px-4 py-3">{{ $line->qty }}</td>
                            <td class="px-4 py-3">{{ number_format($line->price, 2) }}</td>
                            <td class="px-4 py-3">{{ number_format($line->qty * $line->price, 2) }}</td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-slate-50">
                    <tr>
                        <td colspan="4" class="px-4 py-3 text-right font-medium text-slate-700">Grand Total</td>
                        <td class="px-4 py-3 font-semibold text-slate-900">{{ number_format($purchase->total, 2) }}</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
@endsection
