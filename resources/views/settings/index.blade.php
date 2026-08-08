@extends('layouts.sidebar')

@section('title', 'Settings')

@section('content')
<style>@keyframes slideIn{from{transform:translateX(100%)}to{transform:translateX(0)}}</style>

    <div class="fixed top-0 right-0 bottom-0 z-100 flex justify-end cursor-pointer backdrop-blur-sm"
         style="left:250px;background:rgba(26,26,46,.4)"
         onclick="window.location.href='{{ url('/dashboard') }}'">

        <div class="w-[380px] max-w-[90vw] h-screen bg-card rounded-l-[20px] px-7 py-8 flex flex-col cursor-default text-ink"
             style="box-shadow:-8px 0 40px rgba(0,0,0,.18);animation:slideIn .22s ease-out"
             onclick="event.stopPropagation()">

            {{-- Header --}}
            <div class="flex items-center gap-3 mb-7 pb-5 border-b-[1.5px] border-line">
                <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <circle cx="12" cy="12" r="3"/>
                    <path d="M19.4 15a1.65 1.65 0 0 0 .33 1.82l.06.06a2 2 0 0 1-2.83 2.83l-.06-.06a1.65 1.65 0 0 0-1.82-.33 1.65 1.65 0 0 0-1 1.51V21a2 2 0 0 1-4 0v-.09A1.65 1.65 0 0 0 9 19.4a1.65 1.65 0 0 0-1.82.33l-.06.06a2 2 0 0 1-2.83-2.83l.06-.06A1.65 1.65 0 0 0 4.68 15a1.65 1.65 0 0 0-1.51-1H3a2 2 0 0 1 0-4h.09A1.65 1.65 0 0 0 4.6 9a1.65 1.65 0 0 0-.33-1.82l-.06-.06a2 2 0 0 1 2.83-2.83l.06.06A1.65 1.65 0 0 0 9 4.68a1.65 1.65 0 0 0 1-1.51V3a2 2 0 0 1 4 0v.09a1.65 1.65 0 0 0 1 1.51 1.65 1.65 0 0 0 1.82-.33l.06-.06a2 2 0 0 1 2.83 2.83l-.06.06A1.65 1.65 0 0 0 19.4 9a1.65 1.65 0 0 0 1.51 1H21a2 2 0 0 1 0 4h-.09a1.65 1.65 0 0 0-1.51 1z"/>
                </svg>
                <h2 class="text-xl font-extrabold">Settings</h2>
            </div>

            {{-- Profile Card --}}
            @php $user = auth()->user(); @endphp
            <div class="flex items-center gap-3.5 p-4 mb-6 bg-accent-light border border-line rounded-xl">
                <div class="w-[46px] h-[46px] rounded-full bg-accent text-white flex items-center justify-center text-lg font-extrabold shrink-0 uppercase">{{ mb_substr($user->name, 0, 1) }}</div>
                <div>
                    <div class="text-[15px] font-bold">{{ $user->name }}</div>
                    <div class="text-xs text-ink-2 mt-0.5">{{ $user->email }}</div>
                    <div class="text-[11px] font-semibold text-accent mt-[3px] capitalize">{{ str_replace('_', ' ', $user->role) }}</div>
                </div>
            </div>

            {{-- Account --}}
            <div class="mb-5">
                <h3 class="text-[11px] font-bold uppercase tracking-[.06em] opacity-50 mb-2">Account</h3>
                <div class="flex items-center justify-between py-[11px] text-[13.5px] font-medium border-b border-[rgba(92,45,27,.07)]">
                    <span>Name</span>
                    <span class="opacity-55 text-[13px] font-normal">{{ $user->name }}</span>
                </div>
                <div class="flex items-center justify-between py-[11px] text-[13.5px] font-medium border-b border-[rgba(92,45,27,.07)]">
                    <span>Email</span>
                    <span class="opacity-55 text-[13px] font-normal">{{ $user->email }}</span>
                </div>
                <div class="flex items-center justify-between py-[11px] text-[13.5px] font-medium border-b border-[rgba(92,45,27,.07)]">
                    <span>Role</span>
                    <span class="opacity-55 text-[13px] font-normal">{{ ucwords(str_replace('_', ' ', $user->role)) }}</span>
                </div>
                @if ($user->branch_id)
                    <div class="flex items-center justify-between py-[11px] text-[13.5px] font-medium">
                        <span>Branch</span>
                        <span class="opacity-55 text-[13px] font-normal">{{ $user->branch->name ?? '—' }}</span>
                    </div>
                @endif
                <div class="mt-3">
                    <a href="{{ url('/business/workers') }}?worker={{ $user->id }}" class="flex items-center gap-2 py-[9px] px-3.5 bg-[rgba(188,97,75,.08)] border border-[rgba(188,97,75,.2)] rounded-lg text-accent text-xs font-semibold no-underline transition-all duration-150 hover:bg-[rgba(188,97,75,.15)]">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                        Edit Profile
                    </a>
                </div>
            </div>

            {{-- Quick Actions --}}
            <div class="mb-5">
                <h3 class="text-[11px] font-bold uppercase tracking-[.06em] opacity-50 mb-2">Quick Actions</h3>
                <div class="flex items-center justify-between py-[11px] text-[13.5px] font-medium border-b border-[rgba(92,45,27,.07)] cursor-pointer" onclick="window.location.href='{{ url('/alerts') }}'">
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline align-middle mr-1.5">
                            <path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9"/><path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        View Alerts
                    </span>
                    <span class="opacity-55 text-[13px] font-normal">&rarr;</span>
                </div>
                <div class="flex items-center justify-between py-[11px] text-[13.5px] font-medium border-b border-[rgba(92,45,27,.07)] cursor-pointer" onclick="window.location.href='{{ url('/api-docs') }}'">
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline align-middle mr-1.5">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><polyline points="14 2 14 8 20 8"/>
                            <line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
                        </svg>
                        API Documentation
                    </span>
                    <span class="opacity-55 text-[13px] font-normal">&rarr;</span>
                </div>
                @if ($user->isOwner())
                <div class="flex items-center justify-between py-[11px] text-[13.5px] font-medium cursor-pointer" onclick="window.location.href='{{ url('/logistics') }}'">
                    <span>
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="inline align-middle mr-1.5">
                            <rect x="2" y="3" width="20" height="14" rx="2" ry="2"/><line x1="8" y1="21" x2="16" y2="21"/><line x1="12" y1="17" x2="12" y2="21"/>
                        </svg>
                        Logistics Dashboard
                    </span>
                    <span class="opacity-55 text-[13px] font-normal">&rarr;</span>
                </div>
                @endif
            </div>

            {{-- Preferences --}}
            <div class="mb-5">
                <h3 class="text-[11px] font-bold uppercase tracking-[.06em] opacity-50 mb-2">Preferences</h3>
                <div class="flex items-center justify-between py-[11px] text-[13.5px] font-medium border-b border-[rgba(92,45,27,.07)]">
                    <span>Currency</span>
                    <span class="opacity-55 text-[13px] font-normal">Philippine Peso (&#8369;)</span>
                </div>
                <div class="flex items-center justify-between py-[11px] text-[13.5px] font-medium border-b border-[rgba(92,45,27,.07)]">
                    <span>Language</span>
                    <span class="opacity-55 text-[13px] font-normal">English</span>
                </div>
                <div class="flex items-center justify-between py-[11px] text-[13.5px] font-medium border-b border-[rgba(92,45,27,.07)]">
                    <span>Notifications</span>
                    <span class="opacity-55 text-[13px] font-normal">Enabled</span>
                </div>
                <div class="flex items-center justify-between py-[11px] text-[13.5px] font-medium">
                    <span>Timezone</span>
                    <span class="opacity-55 text-[13px] font-normal">Asia/Manila (PHT)</span>
                </div>
            </div>

            {{-- System Settings (owner only) --}}
            @if ($user->isOwner())
            <div class="mb-5">
                <h3 class="text-[11px] font-bold uppercase tracking-[.06em] opacity-50 mb-2">System Settings</h3>
                <div class="py-[11px]">
                    <label class="block text-[13.5px] font-medium mb-1.5">Shift Variance Alert Threshold</label>
                    <p class="text-[11.5px] opacity-55 mb-2.5 leading-snug">A shift-closing count discrepancy raises a dashboard alert once it crosses either limit below.</p>
                    <div class="flex gap-2.5 mb-3.5">
                        <div class="flex-1">
                            <span class="block text-[11px] opacity-55 mb-1">Percent (%)</span>
                            <input type="number" id="varianceThresholdPct" value="{{ $varianceThresholdPct * 100 }}" step="0.1" min="0" max="100"
                                   class="w-full h-9 px-2.5 rounded-lg border border-line text-[13.5px] font-medium bg-transparent">
                        </div>
                        <div class="flex-1">
                            <span class="block text-[11px] opacity-55 mb-1">Amount (&#8369;)</span>
                            <input type="number" id="varianceThresholdPhp" value="{{ $varianceThresholdPhp }}" step="1" min="0"
                                   class="w-full h-9 px-2.5 rounded-lg border border-line text-[13.5px] font-medium bg-transparent">
                        </div>
                    </div>

                    <label class="block text-[13.5px] font-medium mb-1.5">Low Stock Threshold</label>
                    <p class="text-[11.5px] opacity-55 mb-2.5 leading-snug">When an ingredient's set capacity is known, its low-stock line is this percent of that capacity.</p>
                    <div class="mb-3.5">
                        <span class="block text-[11px] opacity-55 mb-1">Percent (%)</span>
                        <input type="number" id="lowStockThresholdPct" value="{{ $lowStockThresholdPct * 100 }}" step="1" min="0" max="100"
                               class="w-full h-9 px-2.5 rounded-lg border border-line text-[13.5px] font-medium bg-transparent">
                    </div>

                    <label class="block text-[13.5px] font-medium mb-1.5">Receipt/Log Retention</label>
                    <p class="text-[11.5px] opacity-55 mb-2.5 leading-snug">Receipt photos (OCR scans + payment proofs) older than this are purged from storage nightly; the underlying records stay for audit history. Legal documents are never auto-purged.</p>
                    <div class="mb-3.5">
                        <span class="block text-[11px] opacity-55 mb-1">Months</span>
                        <input type="number" id="retentionMonths" value="{{ $retentionMonths }}" step="1" min="1" max="120"
                               class="w-full h-9 px-2.5 rounded-lg border border-line text-[13.5px] font-medium bg-transparent">
                    </div>

                    <button type="button" onclick="saveSystemSettings()"
                            class="w-full h-9 rounded-lg bg-accent text-white text-[13px] font-bold border-0 cursor-pointer transition-opacity duration-150 hover:opacity-90">
                        Save Settings
                    </button>
                </div>
            </div>

            {{-- Payment Categories (owner only) --}}
            <div class="mb-5">
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
                           class="flex-1 h-9 px-2.5 rounded-lg border border-line text-[13px] font-medium bg-transparent">
                    <button type="button" onclick="addCategory()"
                            class="h-9 px-3.5 rounded-lg bg-accent text-white text-[12.5px] font-bold border-0 cursor-pointer transition-opacity duration-150 hover:opacity-90">
                        Add
                    </button>
                </div>
            </div>
            @endif

            {{-- Logout --}}
            <div class="mt-auto pt-5 border-t-[1.5px] border-line">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex items-center justify-center gap-2.5 w-full h-[50px] bg-transparent text-red-600 border-[1.5px] border-red-600 rounded-xl text-[15px] font-bold font-sans cursor-pointer transition-all duration-150 hover:bg-red-600 hover:text-white">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                        Log Out
                    </button>
                </form>
            </div>

        </div>
    </div>

    <div class="fixed bottom-5 z-[101] text-[11px] cursor-default" style="left:270px;color:rgba(255,255,255,.45)">Click outside to close</div>

    <script>
        function saveSystemSettings() {
            const variancePct = parseFloat(document.getElementById('varianceThresholdPct').value) / 100;
            const variancePhp = parseFloat(document.getElementById('varianceThresholdPhp').value);
            const lowStockPct = parseFloat(document.getElementById('lowStockThresholdPct').value) / 100;
            const retentionMonths = parseInt(document.getElementById('retentionMonths').value, 10);

            fetch('{{ route('settings.update') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({
                    variance_threshold_pct: variancePct,
                    variance_threshold_php: variancePhp,
                    low_stock_threshold_pct: lowStockPct,
                    receipt_retention_months: retentionMonths,
                }),
            })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        showToast('Settings updated', 'success');
                    } else {
                        showToast(data.message || 'Failed to update settings', 'error');
                    }
                })
                .catch(() => showToast('Failed to update settings', 'error'));
        }

        function addCategory() {
            const input = document.getElementById('newCategoryInput');
            const slug = input.value.trim().toLowerCase().replace(/[^a-z0-9]+/g, '_').replace(/^_+|_+$/g, '');
            if (!slug) { showToast('Enter a category name', 'error'); return; }

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
                    if (data.success) {
                        input.value = '';
                        showToast('Category added', 'success');
                        setTimeout(() => location.reload(), 600);
                    } else {
                        showToast(data.message || 'Failed to add category', 'error');
                    }
                })
                .catch(() => showToast('Failed to add category', 'error'));
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
                    if (data.success) {
                        showToast('Category removed', 'success');
                        setTimeout(() => location.reload(), 600);
                    } else {
                        showToast(data.message || 'Failed to remove category', 'error');
                    }
                })
                .catch(() => showToast('Failed to remove category', 'error'));
        }
    </script>

@endsection
