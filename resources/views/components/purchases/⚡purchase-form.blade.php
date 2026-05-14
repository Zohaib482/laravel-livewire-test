<?php

use App\Models\Brand;
use App\Models\Item;
use App\Models\Purchase;
use App\Models\PurchaseItem;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

new class extends Component
{
    public ?int $purchaseId = null;

    public array $rows = [];

    public array $itemSearch = [];

    public function mount(?int $purchaseId = null): void
    {
        $this->authorize('manage-purchases');

        $this->purchaseId = $purchaseId;

        if ($purchaseId) {
            $purchase = Purchase::query()->with('purchaseItems')->findOrFail($purchaseId);

            $this->rows = $purchase->purchaseItems->map(fn (PurchaseItem $line) => [
                'item_id' => $line->item_id,
                'brand_id' => $line->brand_id,
                'qty' => $line->qty,
                'price' => $line->price,
            ])->values()->all();
        }

        if ($this->rows === []) {
            $this->rows = [$this->blankRow()];
        }
    }

    public function addRow(): void
    {
        $this->rows[] = $this->blankRow();
    }

    public function removeRow(int $index): void
    {
        if (count($this->rows) === 1) {
            return;
        }

        unset($this->rows[$index], $this->itemSearch[$index]);
        $this->rows = array_values($this->rows);
        $this->itemSearch = array_values($this->itemSearch);
    }

    public function updated($property): void
    {
        if (str_starts_with($property, 'rows.')) {
            $this->validateOnly($property, $this->rules());
        }
    }

    public function save(): void
    {
        $this->validate($this->rules());
        $this->validateDuplicateCombinations();

        $total = collect($this->rows)->sum(fn (array $row) => $row['qty'] * $row['price']);

        DB::transaction(function () use ($total) {
            if ($this->purchaseId) {
                $purchase = Purchase::query()->findOrFail($this->purchaseId);
                $purchase->update(['total' => $total]);
                $purchase->purchaseItems()->delete();
            } else {
                $purchase = Purchase::query()->create(['total' => $total]);
                $this->purchaseId = $purchase->id;
            }

            foreach ($this->rows as $row) {
                PurchaseItem::query()->create([
                    'purchase_id' => $purchase->id,
                    'item_id' => $row['item_id'],
                    'brand_id' => $row['brand_id'],
                    'qty' => $row['qty'],
                    'price' => $row['price'],
                ]);
            }
        });

        session()->flash('status', 'Purchase saved successfully.');

        $this->redirectRoute('purchases.show', $this->purchaseId, navigate: true);
    }

    public function getItemsProperty(): Collection
    {
        return Item::query()->orderBy('name')->get();
    }

    public function getBrandsProperty(): Collection
    {
        return Brand::query()->orderBy('name')->get();
    }

    public function filteredItems(int $index): Collection
    {
        $search = strtolower($this->itemSearch[$index] ?? '');

        return $this->items->filter(function (Item $item) use ($search) {
            if ($search === '') {
                return true;
            }

            return str_contains(strtolower($item->name), $search);
        })->values();
    }

    protected function rules(): array
    {
        return [
            'rows' => ['required', 'array', 'min:1'],
            'rows.*.item_id' => ['required', 'integer', 'exists:items,id'],
            'rows.*.brand_id' => ['required', 'integer', 'exists:brands,id'],
            'rows.*.qty' => ['required', 'integer', 'min:1'],
            'rows.*.price' => ['required', 'numeric', 'min:0'],
        ];
    }

    protected function blankRow(): array
    {
        return [
            'item_id' => null,
            'brand_id' => null,
            'qty' => 1,
            'price' => 0,
        ];
    }

    protected function validateDuplicateCombinations(): void
    {
        $combinations = collect($this->rows)
            ->filter(fn (array $row) => $row['item_id'] && $row['brand_id'])
            ->map(fn (array $row) => $row['item_id'].'-'.$row['brand_id']);

        if ($combinations->count() !== $combinations->unique()->count()) {
            throw ValidationException::withMessages([
                'rows' => 'Duplicate item and brand combinations are not allowed in one purchase.',
            ]);
        }
    }
};
?>

<div
    class="space-y-6"
    x-data="{
        rows: $wire.entangle('rows'),
        lineTotal(row) {
            return (Number(row.qty || 0) * Number(row.price || 0));
        },
        grandTotal() {
            return this.rows.reduce((total, row) => total + this.lineTotal(row), 0);
        }
    }"
>
    <div class="flex items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">
                {{ $purchaseId ? 'Edit Purchase' : 'Create Purchase' }}
            </h1>
            <p class="mt-1 text-sm text-slate-600">Add line items, review totals, and save the purchase.</p>
        </div>

        <a href="{{ route('purchases.index') }}" class="rounded-md border border-slate-300 px-4 py-2 text-sm text-slate-700 hover:bg-slate-100">
            Back to list
        </a>
    </div>

    <form wire:submit="save" class="space-y-4">
        <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
            <div class="grid grid-cols-12 gap-4 border-b border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-600">
                <div class="col-span-4">Item</div>
                <div class="col-span-3">Brand</div>
                <div class="col-span-2">Qty</div>
                <div class="col-span-2">Price</div>
                <div class="col-span-1 text-right">Line Total</div>
            </div>

            @foreach ($rows as $index => $row)
                <div wire:key="purchase-row-{{ $index }}" class="grid grid-cols-12 gap-4 border-b border-slate-100 px-4 py-4">
                    <div class="col-span-4 space-y-2">
                        <input
                            type="text"
                            wire:model.live.debounce.300ms="itemSearch.{{ $index }}"
                            placeholder="Search items..."
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none"
                        >
                        <select
                            wire:model.live="rows.{{ $index }}.item_id"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none"
                        >
                            <option value="">Select item</option>
                            @foreach ($this->filteredItems($index) as $item)
                                <option value="{{ $item->id }}">{{ $item->name }}</option>
                            @endforeach
                        </select>
                        @error('rows.'.$index.'.item_id')
                            <p class="text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-3">
                        <select
                            wire:model.live="rows.{{ $index }}.brand_id"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none"
                        >
                            <option value="">Select brand</option>
                            @foreach ($this->brands as $brand)
                                <option value="{{ $brand->id }}">{{ $brand->name }}</option>
                            @endforeach
                        </select>
                        @error('rows.'.$index.'.brand_id')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-2">
                        <input
                            type="number"
                            min="1"
                            wire:model.live="rows.{{ $index }}.qty"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none"
                        >
                        @error('rows.'.$index.'.qty')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-2">
                        <input
                            type="number"
                            min="0"
                            step="0.01"
                            wire:model.live="rows.{{ $index }}.price"
                            class="w-full rounded-md border border-slate-300 px-3 py-2 text-sm shadow-sm focus:border-slate-500 focus:outline-none"
                        >
                        @error('rows.'.$index.'.price')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="col-span-1 flex items-start justify-between gap-2">
                        <span class="pt-2 text-sm font-medium text-slate-700" x-text="lineTotal(rows[{{ $index }}]).toFixed(2)"></span>
                        <button
                            type="button"
                            wire:click="removeRow({{ $index }})"
                            class="rounded-md border border-red-200 px-2 py-1 text-xs text-red-700 hover:bg-red-50"
                        >
                            Remove
                        </button>
                    </div>
                </div>
            @endforeach
        </div>

        @error('rows')
            <p class="text-sm text-red-600">{{ $message }}</p>
        @enderror

        <div class="flex flex-wrap items-center justify-between gap-4">
            <button
                type="button"
                wire:click="addRow"
                class="rounded-md border border-slate-300 px-4 py-2 text-sm font-medium text-slate-700 hover:bg-slate-100"
            >
                Add row
            </button>

            <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm">
                <span class="text-slate-600">Grand total:</span>
                <span class="ml-2 text-lg font-semibold text-slate-900" x-text="grandTotal().toFixed(2)"></span>
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="rounded-md bg-slate-900 px-5 py-2.5 text-sm font-medium text-white hover:bg-slate-800">
                Save purchase
            </button>
        </div>
    </form>
</div>
