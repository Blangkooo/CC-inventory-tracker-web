@extends('layouts.sidebar')

@section('title', 'Supplier Directory')

@section('styles')
    .page-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; }
    .page-title { font-size: 22px; font-weight: 800; letter-spacing: -.02em; }
    .page-subtitle { font-size: 13px; color: var(--text-2); margin-top: 2px; }

    .btn-add {
        display: flex; align-items: center; gap: 6px;
        padding: 10px 20px; background: var(--accent); color: #fff;
        border: none; border-radius: 10px; font-size: 13px; font-weight: 700;
        font-family: var(--font); cursor: pointer; transition: all .15s;
    }
    .btn-add:hover { background: #d35e47; transform: translateY(-1px); box-shadow: 0 4px 12px rgba(225,112,85,.3); }

    .stats-row { display: grid; grid-template-columns: repeat(auto-fit, minmax(160px, 1fr)); gap: 12px; margin-bottom: 24px; }
    .stat-card {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius); padding: 20px; box-shadow: var(--shadow);
    }
    .stat-card__value { font-size: 26px; font-weight: 800; color: var(--text); }
    .stat-card__label { font-size: 11px; font-weight: 600; color: var(--text-3); text-transform: uppercase; letter-spacing: .06em; margin-top: 4px; }

    .table-wrap {
        background: var(--card); border: 1px solid var(--border);
        border-radius: var(--radius); overflow: hidden; box-shadow: var(--shadow);
    }
    table { width: 100%; border-collapse: collapse; font-size: 13px; }
    thead { background: rgba(0,0,0,.02); }
    th { padding: 12px 16px; text-align: left; font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-3); }
    td { padding: 14px 16px; border-top: 1px solid var(--border); vertical-align: middle; }
    tr:hover td { background: var(--accent-light); }

    .supplier-name { font-weight: 700; color: var(--text); }
    .supplier-contact { color: var(--text-2); font-size: 12px; margin-top: 2px; }
    .ingredient-count { font-weight: 700; color: var(--accent); }

    .actions { display: flex; gap: 6px; }
    .btn-sm {
        padding: 5px 12px; border-radius: 8px; font-size: 11px; font-weight: 600;
        border: 1.5px solid var(--border); background: var(--card); color: var(--text-2);
        cursor: pointer; font-family: var(--font); transition: all .15s;
    }
    .btn-sm:hover { background: var(--text); color: #fff; border-color: var(--text); }
    .btn-sm.danger:hover { background: #d63031; color: #fff; border-color: #d63031; }

    .modal-overlay {
        display: none; position: fixed; inset: 0; z-index: 200;
        background: rgba(0,0,0,.5); backdrop-filter: blur(4px);
        align-items: center; justify-content: center; padding: 24px;
    }
    .modal-overlay.is-open { display: flex; }
    .modal {
        background: var(--card); border-radius: 18px; width: 100%; max-width: 520px;
        max-height: 90vh; overflow-y: auto; padding: 32px;
        box-shadow: 0 24px 64px rgba(0,0,0,.2);
    }
    .modal h2 { font-size: 18px; font-weight: 800; margin-bottom: 20px; }

    .form-group { margin-bottom: 14px; }
    .form-label { font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: var(--text-3); margin-bottom: 5px; }
    .form-input {
        width: 100%; height: 42px; padding: 0 14px;
        border: 1.5px solid var(--border); border-radius: 10px;
        font-size: 13px; color: var(--text); font-family: var(--font);
        background: var(--card); transition: all .15s;
    }
    .form-input:focus { outline: none; border-color: var(--accent); box-shadow: 0 0 0 3px rgba(225,112,85,.1); }
    textarea.form-input { height: 80px; padding: 10px 14px; resize: vertical; }

    .modal-actions { display: flex; gap: 8px; justify-content: flex-end; margin-top: 20px; }
    .btn-cancel {
        padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 600;
        border: 1.5px solid var(--border); background: var(--card); color: var(--text-2);
        cursor: pointer; font-family: var(--font);
    }
    .btn-save {
        padding: 10px 20px; border-radius: 10px; font-size: 13px; font-weight: 700;
        border: none; background: var(--accent); color: #fff;
        cursor: pointer; font-family: var(--font);
    }
    .btn-save:hover { background: #d35e47; }

    .detail-header { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 20px; }
    .detail-name { font-size: 20px; font-weight: 800; }
    .detail-section { margin-bottom: 20px; }
    .detail-section-title { font-size: 12px; font-weight: 700; text-transform: uppercase; color: var(--text-3); margin-bottom: 8px; }
    .detail-info { font-size: 13px; line-height: 1.7; }

    .ingredient-list { display: flex; flex-direction: column; gap: 6px; }
    .ingredient-item {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 14px; background: rgba(0,0,0,.02); border-radius: 10px; font-size: 13px;
    }
    .ingredient-item__name { font-weight: 600; }
    .ingredient-item__cost { font-weight: 700; color: var(--accent); }
    .ingredient-item__primary { font-size: 10px; font-weight: 700; background: rgba(0,184,148,.1); color: var(--green); padding: 2px 8px; border-radius: 99px; }

    .purchase-row { display: flex; align-items: center; justify-content: space-between; padding: 8px 0; border-bottom: 1px solid var(--border); font-size: 12px; }
    .purchase-row:last-child { border-bottom: none; }

    @media (max-width: 768px) {
        .stats-row { grid-template-columns: 1fr; }
        table { font-size: 12px; }
        th, td { padding: 10px 12px; }
    }
@endsection

@section('content')
<div class="page-header">
    <div>
        <div class="page-title">Supplier Directory</div>
        <div class="page-subtitle">Manage ingredient suppliers, contacts, and purchase history</div>
    </div>
    <button class="btn-add" onclick="openAddModal()">
        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
        Add Supplier
    </button>
</div>

<div class="stats-row">
    <div class="stat-card">
        <div class="stat-card__value">{{ $suppliers->count() }}</div>
        <div class="stat-card__label">Total Suppliers</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value">{{ $suppliers->where('is_active', true)->count() }}</div>
        <div class="stat-card__label">Active</div>
    </div>
    <div class="stat-card">
        <div class="stat-card__value">{{ $ingredients->count() }}</div>
        <div class="stat-card__label">Ingredients Tracked</div>
    </div>
</div>

<div class="table-wrap">
    @if ($suppliers->isEmpty())
        <div class="empty-state"><p>No suppliers added yet. Click "Add Supplier" to get started.</p></div>
    @else
        <table>
            <thead><tr><th>Supplier</th><th>Contact</th><th>Address / Landmark</th><th>Ingredients</th><th>Status</th><th></th></tr></thead>
            <tbody>
                @foreach ($suppliers as $supplier)
                <tr>
                    <td>
                        <div class="supplier-name">{{ $supplier->name }}</div>
                        @if ($supplier->contact_person)<div class="supplier-contact">{{ $supplier->contact_person }}</div>@endif
                    </td>
                    <td>{{ $supplier->contact_number ?? '—' }}</td>
                    <td>{{ Str::limit($supplier->address, 30) }}@if ($supplier->landmark)<br><span style="opacity:.6;font-size:11px;">Near: {{ $supplier->landmark }}</span>@endif</td>
                    <td><span class="ingredient-count">{{ $supplier->ingredients_count }}</span></td>
                    <td>@if ($supplier->is_active)<span class="badge badge-green">Active</span>@else<span class="badge badge-gray">Inactive</span>@endif</td>
                    <td>
                        <div class="actions">
                            <button class="btn-sm" onclick="viewSupplier({{ $supplier->id }})">View</button>
                            <button class="btn-sm" onclick="editSupplier({{ $supplier->id }}, {{ json_encode($supplier) }})">Edit</button>
                            <button class="btn-sm danger" onclick="deleteSupplier({{ $supplier->id }}, '{{ $supplier->name }}')">Del</button>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    @endif
</div>

{{-- ADD/EDIT MODAL --}}
<div class="modal-overlay" id="supplierModal">
    <div class="modal">
        <h2 id="modalTitle">Add Supplier</h2>
        <form id="supplierForm" onsubmit="saveSupplier(event)">
            <input type="hidden" id="supplierId" value="">
            <div class="form-group"><div class="form-label">Supplier Name *</div><input type="text" class="form-input" id="fName" required placeholder="e.g. Marikina Market Vendor"></div>
            <div class="form-group"><div class="form-label">Contact Person</div><input type="text" class="form-input" id="fContactPerson" placeholder="e.g. Mang Juan"></div>
            <div class="form-group"><div class="form-label">Contact Number</div><input type="text" class="form-input" id="fContactNumber" placeholder="e.g. 0917-123-4567"></div>
            <div class="form-group"><div class="form-label">Address</div><textarea class="form-input" id="fAddress" placeholder="Full address..."></textarea></div>
            <div class="form-group"><div class="form-label">Nearest Landmark</div><input type="text" class="form-input" id="fLandmark" placeholder="e.g. Near BPI Marikina Market"></div>
            <div class="form-group"><div class="form-label">Notes</div><textarea class="form-input" id="fNotes" placeholder="Delivery schedule, minimum order, etc."></textarea></div>
            <div class="modal-actions">
                <button type="button" class="btn-cancel" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn-save">Save Supplier</button>
            </div>
        </form>
    </div>
</div>

{{-- DETAIL MODAL --}}
<div class="modal-overlay" id="detailModal">
    <div class="modal" style="max-width:600px;">
        <div class="detail-header"><div><div class="detail-name" id="detailName">—</div></div><button class="btn-sm" onclick="closeDetail()">Close</button></div>
        <div class="detail-section"><div class="detail-section-title">Contact Info</div><div class="detail-info" id="detailContact">—</div></div>
        <div class="detail-section"><div class="detail-section-title">Location</div><div class="detail-info" id="detailLocation">—</div></div>
        <div class="detail-section"><div class="detail-section-title">Linked Ingredients</div><div class="ingredient-list" id="detailIngredients"><div class="empty-state">No linked ingredients yet.</div></div></div>
        <div class="detail-section"><div class="detail-section-title">Recent Purchases</div><div id="detailPurchases"><div class="empty-state">No purchase history recorded.</div></div></div>
        <hr style="border:none;border-top:1px solid var(--border);margin:20px 0;">
        <div class="detail-section">
            <div class="detail-section-title">Link an Ingredient</div>
            <form id="linkForm" onsubmit="linkIngredient(event)" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:140px;"><div class="form-label">Ingredient</div><select class="form-input" id="linkIngredientId" required style="height:42px;" onchange="updateUnitLabels()">@foreach ($ingredients as $ing)<option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}">{{ $ing->name }} ({{ $ing->unit }})</option>@endforeach</select></div>
                <div style="width:100px;"><div class="form-label">Unit Cost <span id="linkCostUnit" style="opacity:.5;font-weight:400;"></span></div><input type="number" step="0.01" class="form-input" id="linkCost" placeholder="₱"></div>
                <div style="display:flex;align-items:center;gap:4px;padding-bottom:2px;"><input type="checkbox" id="linkPrimary"><label for="linkPrimary" style="font-size:11px;font-weight:600;">Primary</label></div>
                <button type="submit" class="btn-save" style="height:42px;">Link</button>
            </form>
        </div>
        <div class="detail-section" style="margin-top:16px;">
            <div class="detail-section-title">Record a Purchase</div>
            <form id="purchaseForm" onsubmit="recordPurchase(event)" style="display:flex;gap:8px;flex-wrap:wrap;align-items:flex-end;">
                <div style="flex:1;min-width:140px;"><div class="form-label">Ingredient</div><select class="form-input" id="purchIngredient" required style="height:42px;" onchange="updateUnitLabels()">@foreach ($ingredients as $ing)<option value="{{ $ing->id }}" data-unit="{{ $ing->unit }}">{{ $ing->name }} ({{ $ing->unit }})</option>@endforeach</select></div>
                <div style="width:90px;"><div class="form-label">Unit Price <span id="purchPriceUnit" style="opacity:.5;font-weight:400;"></span></div><input type="number" step="0.01" class="form-input" id="purchPrice" required placeholder="₱"></div>
                <div style="width:80px;"><div class="form-label">Qty <span id="purchQtyUnit" style="opacity:.5;font-weight:400;"></span></div><input type="number" step="0.001" class="form-input" id="purchQty" required placeholder="0"></div>
                <div style="width:130px;"><div class="form-label">Date</div><input type="date" class="form-input" id="purchDate" required value="{{ date('Y-m-d') }}"></div>
                <button type="submit" class="btn-save" style="height:42px;">Save</button>
            </form>
        </div>
    </div>
</div>

<script>
const csrf = document.querySelector('meta[name="csrf-token"]').content;
let currentSupplierId = null;

function openAddModal() {
    document.getElementById('modalTitle').textContent = 'Add Supplier';
    document.getElementById('supplierId').value = '';
    document.getElementById('supplierForm').reset();
    document.getElementById('supplierModal').classList.add('is-open');
}
function editSupplier(id, data) {
    document.getElementById('modalTitle').textContent = 'Edit Supplier';
    document.getElementById('supplierId').value = id;
    document.getElementById('fName').value = data.name || '';
    document.getElementById('fContactPerson').value = data.contact_person || '';
    document.getElementById('fContactNumber').value = data.contact_number || '';
    document.getElementById('fAddress').value = data.address || '';
    document.getElementById('fLandmark').value = data.landmark || '';
    document.getElementById('fNotes').value = data.notes || '';
    document.getElementById('supplierModal').classList.add('is-open');
}
function closeModal() { document.getElementById('supplierModal').classList.remove('is-open'); }

async function saveSupplier(e) {
    e.preventDefault();
    const id = document.getElementById('supplierId').value;
    const body = { name: document.getElementById('fName').value, contact_person: document.getElementById('fContactPerson').value || null, contact_number: document.getElementById('fContactNumber').value || null, address: document.getElementById('fAddress').value || null, landmark: document.getElementById('fLandmark').value || null, notes: document.getElementById('fNotes').value || null };
    const url = id ? `/suppliers/${id}` : '/suppliers';
    const method = id ? 'PUT' : 'POST';
    const res = await fetch(url, { method, headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(body) });
    if (res.ok) location.reload(); else alert('Error saving supplier.');
}
async function deleteSupplier(id, name) {
    if (!confirm(`Delete supplier "${name}"?`)) return;
    const res = await fetch(`/suppliers/${id}`, { method: 'DELETE', headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' } });
    if (res.ok) location.reload();
}
function updateUnitLabels() {
    const linkUnit = document.getElementById('linkIngredientId').selectedOptions[0]?.dataset.unit || '';
    document.getElementById('linkCostUnit').textContent = linkUnit ? `(per ${linkUnit})` : '';

    const purchUnit = document.getElementById('purchIngredient').selectedOptions[0]?.dataset.unit || '';
    document.getElementById('purchPriceUnit').textContent = purchUnit ? `(per ${purchUnit})` : '';
    document.getElementById('purchQtyUnit').textContent = purchUnit ? `(${purchUnit})` : '';
}

async function viewSupplier(id) {
    currentSupplierId = id;
    const res = await fetch(`/suppliers/${id}`, { headers: { 'Accept': 'application/json' } });
    if (!res.ok) return alert('Failed to load supplier.');
    const s = await res.json();
    document.getElementById('detailName').textContent = s.name;
    document.getElementById('detailContact').innerHTML = `<strong>Person:</strong> ${s.contact_person||'—'}<br><strong>Phone:</strong> ${s.contact_number||'—'}`;
    document.getElementById('detailLocation').innerHTML = `${s.address||'—'}${s.landmark?'<br><strong>Landmark:</strong> '+s.landmark:''}`;
    document.getElementById('detailIngredients').innerHTML = s.ingredients&&s.ingredients.length ? s.ingredients.map(i=>`<div class="ingredient-item"><span class="ingredient-item__name">${i.name} (${i.unit})</span><span>${i.pivot.is_primary?'<span class="ingredient-item__primary">PRIMARY</span>':''}<span class="ingredient-item__cost">${i.pivot.unit_cost?'₱'+Number(i.pivot.unit_cost).toFixed(2):'—'}</span></span></div>`).join('') : '<div class="empty-state">No linked ingredients.</div>';
    document.getElementById('detailPurchases').innerHTML = s.purchase_history&&s.purchase_history.length ? s.purchase_history.map(p=>`<div class="purchase-row"><span>${new Date(p.purchased_at).toLocaleDateString('en-PH',{year:'numeric',month:'short',day:'numeric'})}</span><span>₱${Number(p.unit_price).toFixed(2)} × ${p.quantity}</span><span style="font-weight:700;">₱${(p.unit_price*p.quantity).toFixed(2)}</span></div>`).join('') : '<div class="empty-state">No purchases recorded.</div>';
    document.getElementById('detailModal').classList.add('is-open');
    updateUnitLabels();
}
function closeDetail() { document.getElementById('detailModal').classList.remove('is-open'); currentSupplierId = null; }

async function linkIngredient(e) {
    e.preventDefault();
    if (!currentSupplierId) return;
    const body = { ingredient_id: document.getElementById('linkIngredientId').value, unit_cost: document.getElementById('linkCost').value || null, is_primary: document.getElementById('linkPrimary').checked };
    const res = await fetch(`/suppliers/${currentSupplierId}/ingredients`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(body) });
    if (res.ok) viewSupplier(currentSupplierId); else alert('Error linking ingredient.');
}
async function recordPurchase(e) {
    e.preventDefault();
    if (!currentSupplierId) return;
    const body = { ingredient_id: document.getElementById('purchIngredient').value, unit_price: document.getElementById('purchPrice').value, quantity: document.getElementById('purchQty').value, purchased_at: document.getElementById('purchDate').value };
    const res = await fetch(`/suppliers/${currentSupplierId}/purchases`, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify(body) });
    if (res.ok) viewSupplier(currentSupplierId); else alert('Error recording purchase.');
}
document.querySelectorAll('.modal-overlay').forEach(el => { el.addEventListener('click', e => { if (e.target === el) el.classList.remove('is-open'); }); });
</script>
@endsection
