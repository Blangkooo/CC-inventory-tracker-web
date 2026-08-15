@extends('layouts.sidebar')

@section('title', 'Mail/Messages')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <div class="text-[22px] font-extrabold tracking-tight">Mail/Messages</div>
        <div class="text-[13px] text-ink-2 mt-0.5">Company-wide notices, posted for every branch to see</div>
    </div>
    <button type="button" class="btn-primary" onclick="openComposeModal()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Compose
    </button>
</div>

<div class="flex flex-col gap-3">
    @forelse ($notices as $notice)
        <div class="card p-5">
            <div class="flex items-start justify-between gap-3">
                <div>
                    <div class="text-[14px] font-extrabold">{{ $notice->title }}</div>
                    <div class="text-[11px] text-ink-3 mt-0.5">
                        {{ $notice->poster?->name ?? 'System' }} · {{ $notice->branch?->name ?? 'All branches' }} · {{ $notice->created_at->diffForHumans() }}
                    </div>
                </div>
                @if (auth()->id() === $notice->posted_by || auth()->user()->isSuperAdmin())
                    <button class="btn-sm danger shrink-0" onclick="deleteNotice({{ $notice->id }}, '{{ addslashes($notice->title) }}')">Delete</button>
                @endif
            </div>
            <p class="text-[13px] text-ink mt-2.5 leading-relaxed whitespace-pre-line">{{ $notice->body }}</p>
        </div>
    @empty
        <div class="card empty-state-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22 6 12 13 2 6"/></svg>
            <span class="empty-state-text">No messages yet. Click "Compose" to post the first one.</span>
        </div>
    @endforelse
</div>

{{-- COMPOSE MODAL --}}
<div class="modal-overlay" id="composeModal">
    <div class="modal-box">
        <h2 class="text-lg font-extrabold mb-5">Compose Message</h2>
        <form id="composeForm" onsubmit="submitCompose(event)">
            <div class="form-group"><div class="form-label">Title *</div><input type="text" class="form-input" id="cTitle" required placeholder="e.g. Holiday schedule update"></div>
            @if ($branches->count() > 1)
            <div class="form-group">
                <div class="form-label">Branch <span class="opacity-50 font-normal">(leave blank for all branches)</span></div>
                <select class="form-input" id="cBranch">
                    <option value="">All branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif
            <div class="form-group"><div class="form-label">Message *</div><textarea class="form-input" id="cBody" rows="5" required placeholder="Write your message…"></textarea></div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-save" id="composeSubmitBtn">Post Message</button>
            </div>
        </form>
    </div>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

function closeModal() { document.getElementById('composeModal').classList.remove('is-open'); }
function openComposeModal() { document.getElementById('composeForm').reset(); document.getElementById('composeModal').classList.add('is-open'); }

async function submitCompose(e) {
    e.preventDefault();
    const btn = document.getElementById('composeSubmitBtn');
    btn.disabled = true; btn.textContent = 'Posting…';
    const body = {
        title: document.getElementById('cTitle').value,
        body: document.getElementById('cBody').value,
        branch_id: document.getElementById('cBranch')?.value || null,
    };
    const res = await fetch('{{ route('notices.store') }}', { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(body) });
    if (res.ok) location.reload();
    else { alert('Error posting message.'); btn.disabled = false; btn.textContent = 'Post Message'; }
}

async function deleteNotice(id, title) {
    if (!confirm(`Delete "${title}"?`)) return;
    const res = await fetch(`{{ url('/mail') }}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
    if (res.ok) location.reload();
}

document.querySelectorAll('.modal-overlay').forEach(el => el.addEventListener('click', e => { if (e.target === el) el.classList.remove('is-open'); }));
</script>
@endsection
