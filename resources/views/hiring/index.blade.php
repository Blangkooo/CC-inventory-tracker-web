@php
    $statusBadge = [
        'applied' => 'badge-gray',
        'shortlisted' => 'badge-blue',
        'interviewed' => 'badge-amber',
        'hired' => 'badge-green',
        'rejected' => 'badge-red',
    ];
@endphp
@extends('layouts.sidebar')

@section('title', 'Hiring')

@section('content')
<div class="flex items-center justify-between mb-6">
    <div>
        <div class="text-[22px] font-extrabold tracking-tight">Hiring</div>
        <div class="text-[13px] text-ink-2 mt-0.5">Job openings and applicant pipeline</div>
    </div>
    <button type="button" class="btn-primary" onclick="openOpeningModal()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        New Opening
    </button>
</div>

<div class="utabs mb-4">
    <button type="button" class="utab is-active" id="tabOpenings" onclick="switchTab('openings')">Openings</button>
    <button type="button" class="utab" id="tabApplicants" onclick="switchTab('applicants')">Applicants</button>
</div>

{{-- OPENINGS --}}
<div id="panelOpenings" class="summary-table-wrap">
    @if ($openings->isEmpty())
        <div class="empty-state-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="7" width="20" height="14" rx="2"/><path d="M16 21V5a2 2 0 0 0-2-2h-4a2 2 0 0 0-2 2v16"/></svg>
            <span class="empty-state-text">No job openings yet. Click "New Opening" to post one.</span>
        </div>
    @else
        <table class="summary-table">
            <thead><tr><th>Title</th><th>Branch</th><th>Status</th><th>Applicants</th><th></th></tr></thead>
            <tbody>
                @foreach ($openings as $opening)
                <tr>
                    <td class="font-bold">{{ $opening->title }}</td>
                    <td>{{ $opening->branch?->name ?? 'All branches' }}</td>
                    <td><span class="{{ $opening->status === 'open' ? 'badge-green' : 'badge-gray' }}">{{ ucfirst($opening->status) }}</span></td>
                    <td><span class="font-bold text-accent">{{ $opening->applicants->count() }}</span></td>
                    <td>
                        <div class="flex gap-1.5">
                            <button class="btn-sm" onclick="openApplicantModal({{ $opening->id }}, '{{ addslashes($opening->title) }}')">Add Applicant</button>
                            <button class="btn-sm" onclick='openEditOpeningModal(@json($opening))'>Edit</button>
                            <button class="btn-sm danger" onclick="deleteOpening({{ $opening->id }}, '{{ addslashes($opening->title) }}')">Del</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- APPLICANTS --}}
<div id="panelApplicants" class="summary-table-wrap hidden">
    @if ($applicants->isEmpty())
        <div class="empty-state-icon">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M16 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="8.5" cy="7" r="4"/><path d="M20 8v6M23 11h-6"/></svg>
            <span class="empty-state-text">No applicants yet.</span>
        </div>
    @else
        <table class="summary-table">
            <thead><tr><th>Name</th><th>Opening</th><th>Contact</th><th>Status</th><th>Resume</th><th></th></tr></thead>
            <tbody>
                @foreach ($applicants as $applicant)
                <tr>
                    <td class="font-bold">{{ $applicant->name }}</td>
                    <td>{{ $applicant->opening?->title ?? '—' }}</td>
                    <td>{{ $applicant->email ?? '—' }}{{ $applicant->phone ? ' · '.$applicant->phone : '' }}</td>
                    <td>
                        <select class="form-input !h-8 !py-1 !text-[11px]" onchange="updateApplicantStatus({{ $applicant->id }}, this.value)">
                            @foreach (['applied','shortlisted','interviewed','hired','rejected'] as $st)
                                <option value="{{ $st }}" {{ $applicant->status === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
                            @endforeach
                        </select>
                    </td>
                    <td>
                        @if ($applicant->resume_path)
                            <a href="{{ \Illuminate\Support\Facades\Storage::url($applicant->resume_path) }}" target="_blank" class="btn-sm">View</a>
                        @else
                            <span class="text-ink-3 text-xs">—</span>
                        @endif
                    </td>
                    <td><button class="btn-sm danger" onclick="deleteApplicant({{ $applicant->id }}, '{{ addslashes($applicant->name) }}')">Del</button></td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- NEW/EDIT OPENING MODAL --}}
<div class="modal-overlay" id="openingModal">
    <div class="modal-box">
        <h2 class="text-lg font-extrabold mb-5" id="openingModalTitle">New Opening</h2>
        <form id="openingForm" onsubmit="saveOpening(event)">
            <input type="hidden" id="openingId">
            <div class="form-group"><div class="form-label">Title *</div><input type="text" class="form-input" id="opTitle" required placeholder="e.g. Barista — Full Time"></div>
            <div class="form-group">
                <div class="form-label">Branch <span class="opacity-50 font-normal">(leave blank for company-wide)</span></div>
                <select class="form-input" id="opBranch">
                    <option value="">All branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group" id="opStatusGroup" style="display:none">
                <div class="form-label">Status</div>
                <select class="form-input" id="opStatus">
                    <option value="open">Open</option>
                    <option value="closed">Closed</option>
                </select>
            </div>
            <div class="form-group"><div class="form-label">Description</div><textarea class="form-input" id="opDescription" placeholder="Role responsibilities, requirements…"></textarea></div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('openingModal')">Cancel</button>
                <button type="submit" class="btn-save">Save Opening</button>
            </div>
        </form>
    </div>
</div>

{{-- ADD APPLICANT MODAL --}}
<div class="modal-overlay" id="applicantModal">
    <div class="modal-box">
        <h2 class="text-lg font-extrabold mb-5">Add Applicant <span class="opacity-50 font-normal text-sm" id="applicantForOpening"></span></h2>
        <form id="applicantForm" onsubmit="saveApplicant(event)">
            <input type="hidden" id="applicantOpeningId">
            <div class="form-group"><div class="form-label">Name *</div><input type="text" class="form-input" id="apName" required></div>
            <div class="form-group"><div class="form-label">Email</div><input type="email" class="form-input" id="apEmail"></div>
            <div class="form-group"><div class="form-label">Phone</div><input type="text" class="form-input" id="apPhone"></div>
            <div class="form-group"><div class="form-label">Resume <span class="opacity-50 font-normal">(PDF/DOC — max 5MB)</span></div><input type="file" class="form-input" id="apResume" accept=".pdf,.doc,.docx"></div>
            <div class="form-group"><div class="form-label">Notes</div><textarea class="form-input" id="apNotes"></textarea></div>
            <div class="modal-footer">
                <button type="button" class="btn-cancel" onclick="closeModal('applicantModal')">Cancel</button>
                <button type="submit" class="btn-save" id="applicantSubmitBtn">Add Applicant</button>
            </div>
        </form>
    </div>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;

function switchTab(tab) {
    document.getElementById('tabOpenings').classList.toggle('is-active', tab === 'openings');
    document.getElementById('tabApplicants').classList.toggle('is-active', tab === 'applicants');
    document.getElementById('panelOpenings').classList.toggle('hidden', tab !== 'openings');
    document.getElementById('panelApplicants').classList.toggle('hidden', tab !== 'applicants');
}

function closeModal(id) { document.getElementById(id).classList.remove('is-open'); }

function openOpeningModal() {
    document.getElementById('openingForm').reset();
    document.getElementById('openingId').value = '';
    document.getElementById('openingModalTitle').textContent = 'New Opening';
    document.getElementById('opStatusGroup').style.display = 'none';
    document.getElementById('openingModal').classList.add('is-open');
}
function openEditOpeningModal(opening) {
    document.getElementById('openingId').value = opening.id;
    document.getElementById('opTitle').value = opening.title;
    document.getElementById('opBranch').value = opening.branch_id ?? '';
    document.getElementById('opDescription').value = opening.description ?? '';
    document.getElementById('opStatus').value = opening.status;
    document.getElementById('openingModalTitle').textContent = 'Edit Opening';
    document.getElementById('opStatusGroup').style.display = '';
    document.getElementById('openingModal').classList.add('is-open');
}

async function saveOpening(e) {
    e.preventDefault();
    const id = document.getElementById('openingId').value;
    const body = {
        title: document.getElementById('opTitle').value,
        branch_id: document.getElementById('opBranch').value || null,
        description: document.getElementById('opDescription').value || null,
    };
    let url = '{{ route('hiring.openings.store') }}';
    let method = 'POST';
    if (id) {
        body.status = document.getElementById('opStatus').value;
        url = `{{ url('/hiring/openings') }}/${id}`;
        method = 'PUT';
    }
    const res = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(body) });
    if (res.ok) location.reload(); else alert('Error saving opening.');
}

async function deleteOpening(id, title) {
    if (!confirm(`Delete opening "${title}"? This also removes its applicants.`)) return;
    const res = await fetch(`{{ url('/hiring/openings') }}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
    if (res.ok) location.reload();
}

function openApplicantModal(openingId, openingTitle) {
    document.getElementById('applicantForm').reset();
    document.getElementById('applicantOpeningId').value = openingId;
    document.getElementById('applicantForOpening').textContent = '— ' + openingTitle;
    document.getElementById('applicantModal').classList.add('is-open');
}

async function saveApplicant(e) {
    e.preventDefault();
    const openingId = document.getElementById('applicantOpeningId').value;
    const btn = document.getElementById('applicantSubmitBtn');
    btn.disabled = true; btn.textContent = 'Adding…';
    const fd = new FormData();
    fd.append('name', document.getElementById('apName').value);
    fd.append('email', document.getElementById('apEmail').value);
    fd.append('phone', document.getElementById('apPhone').value);
    fd.append('notes', document.getElementById('apNotes').value);
    const file = document.getElementById('apResume').files[0];
    if (file) fd.append('resume', file);
    const res = await fetch(`{{ url('/hiring/openings') }}/${openingId}/applicants`, { method: 'POST', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: fd });
    if (res.ok) location.reload();
    else { alert('Error adding applicant.'); btn.disabled = false; btn.textContent = 'Add Applicant'; }
}

async function updateApplicantStatus(id, status) {
    const res = await fetch(`{{ url('/hiring/applicants') }}/${id}/status`, { method: 'PUT', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify({ status }) });
    if (!res.ok) alert('Error updating status.');
}

async function deleteApplicant(id, name) {
    if (!confirm(`Delete applicant "${name}"?`)) return;
    const res = await fetch(`{{ url('/hiring/applicants') }}/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
    if (res.ok) location.reload();
}

document.querySelectorAll('.modal-overlay').forEach(el => el.addEventListener('click', e => { if (e.target === el) el.classList.remove('is-open'); }));
</script>
@endsection
