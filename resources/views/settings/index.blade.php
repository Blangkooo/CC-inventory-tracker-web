@extends('layouts.sidebar')

@section('title', 'Settings')

@section('content')
@php $user = auth()->user(); @endphp

<div class="card p-5" style="max-width:560px">
    <div class="flex items-center gap-3.5 p-4 mb-6 bg-accent-light border border-line rounded-xl">
        <div class="w-[46px] h-[46px] rounded-full bg-accent text-white flex items-center justify-center text-lg font-extrabold shrink-0 uppercase">{{ mb_substr($user->name, 0, 1) }}</div>
        <div>
            <div class="text-[15px] font-bold">{{ $user->name }}</div>
            <div class="text-xs text-ink-2 mt-0.5">{{ $user->email }}</div>
            <div class="text-[11px] font-semibold text-accent mt-[3px] capitalize">{{ str_replace('_', ' ', $user->role) }}</div>
        </div>
    </div>

    @if ($user->isSuperAdmin())
    <div>
        <h3 class="text-[11px] font-bold uppercase tracking-[.06em] opacity-50 mb-2">Payment Categories</h3>
        <p class="text-[11.5px] opacity-55 mb-2.5 leading-snug">Categories available when recording an outgoing payment. "other" can't be removed — it's the fallback.</p>
        <div id="categoryChips" class="flex flex-wrap gap-1.5 mb-3">
            @foreach ($paymentCategories as $cat)
                <span class="flex items-center gap-1.5 pl-3 pr-2 py-1 rounded-full border border-line bg-accent-light text-[12px] font-semibold">
                    {{ str_replace('_', ' ', $cat) }}
                    @if ($cat !== 'other')
                        <button type="button" onclick="removeCategory('{{ $cat }}')"
                                class="w-4 h-4 flex items-center justify-center rounded-full bg-[rgba(0,0,0,.08)] border-0 cursor-pointer text-[11px] leading-none hover:bg-[rgba(214,48,49,.18)] hover:text-red-600">&times;</button>
                    @endif
                </span>
            @endforeach
        </div>
        <div class="flex gap-2">
            <input type="text" id="newCategoryInput" placeholder="e.g. ice delivery"
                   class="form-input flex-1">
            <button type="button" onclick="addCategory()" class="btn-primary">Add</button>
        </div>
    </div>
    @else
    <p class="text-[13px] text-ink-2">No account-level settings are available for your role.</p>
    @endif
</div>

<script>
    function addCategory() {
        const input = document.getElementById('newCategoryInput');
        const slug = input.value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
        if (!slug) { alert('Enter a category name'); return; }

        fetch('{{ route('settings.payment-categories.store') }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
            body: JSON.stringify({ category: slug }),
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
                else alert(data.message || 'Failed to add category');
            })
            .catch(() => alert('Failed to add category'));
    }

    function removeCategory(category) {
        if (!confirm(`Remove the "${category.replace(/_/g, ' ')}" category?`)) return;

        fetch(`{{ url('/settings/payment-categories') }}/${encodeURIComponent(category)}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            },
        })
            .then(res => res.json())
            .then(data => {
                if (data.success) location.reload();
                else alert(data.message || 'Failed to remove category');
            })
            .catch(() => alert('Failed to remove category'));
    }
</script>
@endsection
