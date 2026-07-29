@php
    $firstOfMonth = $month->copy()->startOfMonth();
    $startOffset = $firstOfMonth->dayOfWeek;
    $daysInMonth = $month->daysInMonth;
    $gridStart = $firstOfMonth->copy()->subDays($startOffset);
    $totalCells = (int) ceil(($startOffset + $daysInMonth) / 7) * 7;
    $typeTag = [
        'shift' => 'tag--accent',
        'meeting' => 'tag--blue',
        'delivery' => 'tag--amber',
        'maintenance' => 'tag--red',
        'other' => 'tag--accent',
    ];
@endphp
@extends('layouts.sidebar')

@section('title', 'Calendar')

@section('content')
<div class="flex items-center justify-between mb-6 flex-wrap gap-3">
    <div>
        <div class="text-[22px] font-extrabold tracking-tight">Calendar</div>
        <div class="text-[13px] text-ink-2 mt-0.5">Shifts, meetings, deliveries, and maintenance across branches</div>
    </div>
    <button type="button" class="btn-primary" onclick="openEventModal()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Event
    </button>
</div>

<div class="flex items-center justify-between mb-4">
    <a href="{{ route('calendar.index', ['month' => $prevMonth]) }}" class="pill-btn">&larr; Prev</a>
    <div class="text-[16px] font-extrabold">{{ $month->format('F Y') }}</div>
    <a href="{{ route('calendar.index', ['month' => $nextMonth]) }}" class="pill-btn">Next &rarr;</a>
</div>

<div class="grid grid-cols-7 gap-2 mb-2 text-center text-[11px] font-bold uppercase tracking-[.06em] text-ink-3">
    @foreach (['Sun','Mon','Tue','Wed','Thu','Fri','Sat'] as $dow)
        <div>{{ $dow }}</div>
    @endforeach
</div>

<div class="grid grid-cols-7 gap-2">
    @for ($i = 0; $i < $totalCells; $i++)
        @php
            $date = $gridStart->copy()->addDays($i);
            $inMonth = $date->month === $month->month;
            $dayEvents = $events->get($date->format('Y-m-d'), collect());
        @endphp
        <div class="card !p-2 min-h-[100px] flex flex-col gap-1 {{ $inMonth ? '' : 'opacity-40' }}">
            <div class="text-[12px] font-bold {{ $date->isToday() ? 'text-white bg-accent w-5 h-5 rounded-full flex items-center justify-center' : '' }}">{{ $date->day }}</div>
            @foreach ($dayEvents->take(3) as $ev)
                <button type="button" onclick='openEventModal(@json($ev))' class="tag {{ $typeTag[$ev->type] ?? 'tag--accent' }} !block w-full text-left truncate cursor-pointer border-none">
                    {{ $ev->all_day ? '' : $ev->starts_at->format('g:iA') . ' ' }}{{ $ev->title }}
                </button>
            @endforeach
            @if ($dayEvents->count() > 3)
                <span class="text-[10px] text-ink-3">+{{ $dayEvents->count() - 3 }} more</span>
            @endif
        </div>
    @endfor
</div>

{{-- ADD/EDIT EVENT MODAL --}}
<div class="modal-overlay" id="eventModal">
    <div class="modal-box">
        <h2 class="text-lg font-extrabold mb-5" id="eventModalTitle">Add Event</h2>
        <form id="eventForm" onsubmit="saveEvent(event)">
            <input type="hidden" id="eventId" value="">
            <div class="form-group">
                <div class="form-label">Title *</div>
                <input type="text" class="form-input" id="evTitle" required placeholder="e.g. Weekly branch meeting">
            </div>
            <div class="form-group">
                <div class="form-label">Type</div>
                <select class="form-input" id="evType">
                    <option value="shift">Shift</option>
                    <option value="meeting">Meeting</option>
                    <option value="delivery">Delivery</option>
                    <option value="maintenance">Maintenance</option>
                    <option value="other">Other</option>
                </select>
            </div>
            <div class="form-group">
                <div class="form-label">Branch <span class="opacity-50 font-normal">(leave blank for company-wide)</span></div>
                <select class="form-input" id="evBranch">
                    <option value="">All branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-3">
                <div class="form-group flex-1">
                    <div class="form-label">Starts *</div>
                    <input type="datetime-local" class="form-input" id="evStart" required>
                </div>
                <div class="form-group flex-1">
                    <div class="form-label">Ends</div>
                    <input type="datetime-local" class="form-input" id="evEnd">
                </div>
            </div>
            <div class="form-group">
                <div class="form-label">Description</div>
                <textarea class="form-input" id="evDescription" placeholder="Optional notes…"></textarea>
            </div>
            <div class="modal-footer !justify-between">
                <button type="button" class="btn-danger hidden" id="eventDeleteBtn" onclick="deleteEvent()">Delete</button>
                <div class="flex gap-2 ml-auto">
                    <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                    <button type="submit" class="btn-save">Save Event</button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

function toLocalInput(iso) {
    const d = new Date(iso);
    const pad = n => String(n).padStart(2, '0');
    return `${d.getFullYear()}-${pad(d.getMonth()+1)}-${pad(d.getDate())}T${pad(d.getHours())}:${pad(d.getMinutes())}`;
}

function openEventModal(ev) {
    const form = document.getElementById('eventForm');
    form.reset();
    if (ev) {
        document.getElementById('eventModalTitle').textContent = 'Edit Event';
        document.getElementById('eventId').value = ev.id;
        document.getElementById('evTitle').value = ev.title;
        document.getElementById('evType').value = ev.type;
        document.getElementById('evBranch').value = ev.branch_id ?? '';
        document.getElementById('evStart').value = toLocalInput(ev.starts_at);
        document.getElementById('evEnd').value = ev.ends_at ? toLocalInput(ev.ends_at) : '';
        document.getElementById('evDescription').value = ev.description ?? '';
        document.getElementById('eventDeleteBtn').classList.remove('hidden');
    } else {
        document.getElementById('eventModalTitle').textContent = 'Add Event';
        document.getElementById('eventId').value = '';
        document.getElementById('eventDeleteBtn').classList.add('hidden');
    }
    document.getElementById('eventModal').classList.add('is-open');
}
function closeModal() { document.getElementById('eventModal').classList.remove('is-open'); }

async function saveEvent(e) {
    e.preventDefault();
    const id = document.getElementById('eventId').value;
    const body = {
        title: document.getElementById('evTitle').value,
        type: document.getElementById('evType').value,
        branch_id: document.getElementById('evBranch').value || null,
        starts_at: document.getElementById('evStart').value,
        ends_at: document.getElementById('evEnd').value || null,
        description: document.getElementById('evDescription').value || null,
    };
    const url = id ? `{{ url('/calendar/events') }}/${id}` : '{{ route('calendar.events.store') }}';
    const method = id ? 'PUT' : 'POST';
    const res = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(body) });
    if (res.ok) location.reload(); else alert('Error saving event.');
}

async function deleteEvent() {
    const id = document.getElementById('eventId').value;
    if (!id || !confirm('Delete this event?')) return;
    const res = await fetch(`{{ url('/calendar/events') }}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
    if (res.ok) location.reload(); else alert('Error deleting event.');
}

document.querySelectorAll('.modal-overlay').forEach(el => el.addEventListener('click', e => { if (e.target === el) el.classList.remove('is-open'); }));
</script>
@endsection
