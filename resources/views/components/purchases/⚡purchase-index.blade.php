<?php

use App\Models\Purchase;
use Illuminate\Support\Facades\Artisan;
use Livewire\Component;
use Livewire\WithPagination;

new class extends Component
{
    use WithPagination;

    public string $migrationMessage = '';

    public function mount(): void
    {
        $this->authorize('view-purchases');
    }

    public function deletePurchase(int $purchaseId): void
    {
        $this->authorize('manage-purchases');

        Purchase::query()->findOrFail($purchaseId)->delete();

        session()->flash('status', 'Purchase deleted successfully.');
    }

    public function runLegacyMigration(): void
    {
        $this->authorize('run-legacy-migration');

        Artisan::call('migrate:legacy-purchases');

        $this->migrationMessage = trim(Artisan::output());
        $this->resetPage();

        session()->flash('status', $this->migrationMessage);
    }

    public function getPurchasesProperty()
    {
        return Purchase::query()->withCount('purchaseItems')->latest()->paginate(10);
    }
};
?>

<div class="space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-4">
        <div>
            <h1 class="text-2xl font-semibold text-slate-900">Purchases</h1>
            <p class="mt-1 text-sm text-slate-600">View purchase history and manage entries by role.</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            @can('run-legacy-migration')
                <button
                    type="button"
                    wire:click="runLegacyMigration"
                    wire:confirm="Run the legacy purchase migration?"
                    class="rounded-md border border-amber-300 bg-amber-50 px-4 py-2 text-sm font-medium text-amber-900 hover:bg-amber-100"
                >
                    Run legacy migration
                </button>
            @endcan

            @can('manage-purchases')
                <a href="{{ route('purchases.create') }}" class="rounded-md bg-slate-900 px-4 py-2 text-sm font-medium text-white hover:bg-slate-800">
                    New purchase
                </a>
            @endcan
        </div>
    </div>

    @if ($migrationMessage !== '')
        <div class="rounded-lg border border-slate-200 bg-white px-4 py-3 text-sm text-slate-700">
            {{ $migrationMessage }}
        </div>
    @endif

    <div class="overflow-hidden rounded-xl border border-slate-200 bg-white shadow-sm">
        <table class="min-w-full divide-y divide-slate-200 text-sm">
            <thead class="bg-slate-50 text-left text-slate-600">
                <tr>
                    <th class="px-4 py-3 font-medium">ID</th>
                    <th class="px-4 py-3 font-medium">Total</th>
                    <th class="px-4 py-3 font-medium">Items</th>
                    <th class="px-4 py-3 font-medium">Created</th>
                    <th class="px-4 py-3 font-medium text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-slate-200">
                @forelse ($this->purchases as $purchase)
                    <tr wire:key="purchase-{{ $purchase->id }}">
                        <td class="px-4 py-3 font-medium text-slate-900">#{{ $purchase->id }}</td>
                        <td class="px-4 py-3">{{ number_format($purchase->total, 2) }}</td>
                        <td class="px-4 py-3">{{ $purchase->purchase_items_count }}</td>
                        <td class="px-4 py-3">{{ $purchase->created_at->format('M d, Y') }}</td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('purchases.show', $purchase) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-slate-700 hover:bg-slate-100">
                                    View
                                </a>

                                @can('manage-purchases')
                                    <a href="{{ route('purchases.edit', $purchase) }}" class="rounded-md border border-slate-300 px-3 py-1.5 text-slate-700 hover:bg-slate-100">
                                        Edit
                                    </a>
                                    <button
                                        type="button"
                                        wire:click="deletePurchase({{ $purchase->id }})"
                                        wire:confirm="Delete this purchase?"
                                        class="rounded-md border border-red-200 px-3 py-1.5 text-red-700 hover:bg-red-50"
                                    >
                                        Delete
                                    </button>
                                @endcan
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-slate-500">No purchases yet.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $this->purchases->links() }}
    </div>
</div>
