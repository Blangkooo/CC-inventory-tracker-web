@extends('layouts.sidebar')

@section('title', 'Branches')

@section('content')
<style>
    /* ══ BUSINESS TABS ════════════════════════════════════════════════════ */
    .business-tabs {
        display: flex;
        gap: 5px;
        margin-bottom: 24px;
        flex-wrap: wrap;
        align-items: center;
        border-bottom: 1px solid var(--border);
    }

    .business-tab {
        padding: 10px 14px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        border-bottom: 2px solid transparent;
        background: transparent;
        color: var(--brown);
        opacity: 0.5;
        transition: all .15s ease;
        display: flex;
        align-items: center;
        gap: 6px;
    }

    .business-tab:hover {
        opacity: 0.8;
    }

    .business-tab.active {
        opacity: 1;
        border-bottom-color: var(--terra);
    }

    .business-tab__icon {
        font-size: 12px;
    }

    .add-business-btn {
        margin-left: auto;
        padding: 8px 14px;
        border-radius: 999px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        border: 1.5px solid var(--terra);
        background: var(--terra);
        color: #fff;
        display: flex;
        align-items: center;
        gap: 6px;
        transition: all .15s ease;
    }

    .add-business-btn:hover {
        background: var(--terra-dk);
    }

    /* ══ MAIN CONTENT GRID ════════════════════════════════════════════════ */
    .business-content {
        display: grid;
        grid-template-columns: 1fr 1.3fr;
        gap: 24px;
    }

    @media (max-width: 1100px) {
        .business-content { grid-template-columns: 1fr; }
    }

    /* ══ BUSINESS INFO CARD ════════════════════════════════════════════════ */
    .business-info {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 16px;
        padding: 28px;
    }

    .business-info__header {
        display: flex;
        align-items: center;
        gap: 10px;
        margin-bottom: 20px;
    }

    .business-info__icon {
        font-size: 20px;
    }

    .business-info__name {
        font-size: 18px;
        font-weight: 700;
        color: var(--brown);
    }

    .business-info__details {
        border: 1px dashed var(--terra);
        border-radius: 12px;
        padding: 20px;
        font-size: 13px;
        line-height: 1.7;
        color: var(--brown);
    }

    .business-info__details p {
        margin-bottom: 10px;
    }

    .business-info__details strong {
        color: var(--terra);
        font-weight: 600;
    }

    .business-info__details ul {
        margin-left: 20px;
        margin-top: 6px;
    }

    .business-info__details li {
        margin-bottom: 4px;
    }

    .business-info__actions {
        display: flex;
        gap: 12px;
        justify-content: center;
        margin-top: 24px;
    }

    .btn-outline {
        padding: 10px 24px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid var(--border);
        background: #fff;
        color: var(--brown);
        transition: all .15s ease;
    }

    .btn-outline:hover {
        background: var(--cream);
    }

    .btn-danger {
        padding: 10px 24px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        background: #dc2626;
        color: #fff;
        transition: all .15s ease;
    }

    .btn-danger:hover {
        background: #b91c1c;
    }

    /* ══ RECIPES PANEL ════════════════════════════════════════════════════ */
    .recipes-panel {
        background: #fff;
        border: 1.5px solid var(--border);
        border-radius: 16px;
        padding: 28px;
    }

    .recipes-panel__header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 16px;
    }

    .recipes-panel__title {
        font-size: 18px;
        font-weight: 700;
        color: var(--brown);
    }

    .recipes-search {
        display: flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border: 1px solid var(--border);
        border-radius: 8px;
        background: var(--cream);
    }

    .recipes-search input {
        border: none;
        background: transparent;
        outline: none;
        font-size: 12px;
        font-family: var(--font);
        width: 180px;
    }

    .recipes-categories {
        display: flex;
        gap: 16px;
        margin-bottom: 20px;
        border-bottom: 1px solid var(--border);
        padding-bottom: 12px;
    }

    .recipes-category {
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        color: var(--brown);
        opacity: .5;
        transition: all .15s ease;
    }

    .recipes-category:hover,
    .recipes-category.active {
        opacity: 1;
        color: var(--terra);
    }

    .recipe-card {
        margin-bottom: 24px;
    }

    .recipe-card__name {
        font-size: 16px;
        font-weight: 700;
        color: var(--terra);
        margin-bottom: 16px;
    }

    .recipe-section {
        margin-bottom: 16px;
    }

    .recipe-section__badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        background: var(--cream);
        color: var(--brown);
        margin-bottom: 12px;
    }

    .recipe-ingredients {
        display: grid;
        grid-template-columns: auto 1fr;
        gap: 6px 20px;
        font-size: 13px;
    }

    .recipe-ingredients dt {
        color: var(--terra);
        font-weight: 500;
    }

    .recipe-ingredients dd {
        color: var(--brown);
    }

    .recipe-procedure {
        font-size: 13px;
        line-height: 1.7;
        color: var(--brown);
    }

    .recipe-procedure h4 {
        font-size: 14px;
        font-weight: 700;
        color: var(--terra);
        margin: 14px 0 6px;
    }

    .recipe-procedure h4:first-child {
        margin-top: 0;
    }

    .recipe-procedure p {
        margin-bottom: 8px;
    }

    .recipes-footer {
        text-align: right;
        margin-top: 16px;
        padding-top: 16px;
        border-top: 1px solid var(--border);
    }

    .btn-edit-recipe {
        padding: 8px 20px;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 600;
        cursor: pointer;
        border: 1px solid var(--border);
        background: #fff;
        color: var(--brown);
        transition: all .15s ease;
        text-decoration: none;
        display: inline-block;
    }

    .btn-edit-recipe:hover {
        background: var(--cream);
    }

    .empty-state {
        text-align: center;
        color: var(--brown);
        opacity: .5;
        font-size: 14px;
        padding: 40px 20px;
    }

    /* ══ ADD BUSINESS MODAL ════════════════════════════════════════════════ */
    .modal-overlay {
        display: none;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(0,0,0,.5);
        z-index: 1000;
        align-items: center;
        justify-content: center;
    }

    .modal-overlay.active {
        display: flex;
    }

    .modal-content {
        background: #fff;
        border-radius: 16px;
        padding: 32px;
        width: 90%;
        max-width: 650px;
        max-height: 90vh;
        overflow-y: auto;
    }

    .modal-badge {
        display: inline-block;
        padding: 4px 12px;
        border-radius: 6px;
        font-size: 11px;
        font-weight: 600;
        background: var(--cream);
        color: var(--terra);
        margin-bottom: 16px;
    }

    .modal-title {
        font-size: 20px;
        font-weight: 700;
        color: var(--brown);
        margin-bottom: 4px;
    }

    .modal-subtitle {
        font-size: 13px;
        color: var(--brown);
        opacity: .6;
        margin-bottom: 24px;
    }

    .form-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 20px;
        margin-bottom: 20px;
    }

    .form-group {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .form-group.full-width {
        grid-column: span 2;
    }

    .form-label {
        font-size: 13px;
        font-weight: 600;
        color: var(--terra);
    }

    .form-input {
        padding: 10px 14px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 13px;
        font-family: var(--font);
        transition: border-color .15s ease;
    }

    .form-input:focus {
        outline: none;
        border-color: var(--terra);
    }

    .form-textarea {
        min-height: 100px;
        resize: vertical;
    }

    .file-upload {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 14px;
        border: 1px solid var(--border);
        border-radius: 8px;
        font-size: 12px;
        color: var(--brown);
        opacity: .6;
        cursor: pointer;
        transition: border-color .15s ease;
    }

    .file-upload:hover {
        border-color: var(--terra);
    }

    .file-upload__icon {
        font-size: 16px;
    }

    .btn-submit {
        margin-top: 24px;
        padding: 12px 28px;
        border-radius: 8px;
        font-size: 14px;
        font-weight: 600;
        cursor: pointer;
        border: none;
        background: #16a34a;
        color: #fff;
        transition: background .15s ease;
    }

    .btn-submit:hover {
        background: #15803d;
    }

    /* ══ FLASH MESSAGES ════════════════════════════════════════════════════ */
    .flash-success {
        background: #dcfce7;
        border: 1px solid #16a34a;
        color: #166534;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 13px;
    }

    .flash-error {
        background: #fee2e2;
        border: 1px solid #dc2626;
        color: #991b1b;
        padding: 12px 16px;
        border-radius: 8px;
        margin-bottom: 16px;
        font-size: 13px;
    }
</style>

{{-- ═══ PAGE HEADER ═══ --}}
<div class="mb-6">
    <div class="text-[22px] font-extrabold tracking-tight">Branches</div>
    <div class="text-[13px] text-ink-2 mt-0.5">Manage your registered businesses and their recipes</div>
</div>

{{-- ═══ FLASH MESSAGES ═══ --}}
@if(session('success'))
    <div class="flash-success">{{ session('success') }}</div>
@endif

@if($errors->any())
    <div class="flash-error">
        @foreach($errors->all() as $error)
            <div>{{ $error }}</div>
        @endforeach
    </div>
@endif

{{-- ═══ BUSINESS TABS ═══ --}}
<div class="business-tabs">
    @foreach($branches as $branch)
        <button class="business-tab {{ $loop->first ? 'active' : '' }}" 
                data-branch-id="{{ $branch->id }}" 
                onclick="switchBranch({{ $branch->id }}, this)">
            {{ $branch->name }}
        </button>
    @endforeach
    <button class="add-business-btn" onclick="openAddBusinessModal()">
        <span>＋</span>
        Add Business
    </button>
</div>

{{-- ═══ MAIN CONTENT ═══ --}}
<div class="business-content">
    {{-- LEFT: Business Info --}}
    <div class="business-info" id="businessInfo">
        @if($branches->isEmpty())
            <div class="empty-state-icon">
                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M3 21h18"/><path d="M5 21V7l8-4v18"/><path d="M19 21V11l-6-4"/><path d="M9 9v.01"/><path d="M9 12v.01"/><path d="M9 15v.01"/><path d="M9 18v.01"/></svg>
                <span class="empty-state-text">No businesses yet. Add your first business!</span>
            </div>
        @else
            @php $branch = $branches->first(); @endphp
            <div class="business-info__header">
                <span class="business-info__icon">B</span>
                <span class="business-info__name">{{ $branch->name }}</span>
            </div>
            <div class="business-info__details">
                <p><strong>Location:</strong> {{ $branch->location ?? 'Not specified' }}</p>
                <p><strong>Date of Operation:</strong> Established {{ $branch->created_at->format('F j, Y') }}</p>
                <p><strong>About:</strong> {{ $branch->description ?: 'No description set yet.' }}</p>
                @php
                    $serviceLines = $branch->services
                        ? array_values(array_filter(array_map('trim', preg_split('/\r\n|\r|\n/', $branch->services))))
                        : [];
                @endphp
                @if(count($serviceLines) > 0)
                    <p><strong>Services Offered:</strong></p>
                    <ul>
                        @foreach($serviceLines as $service)
                            <li>{{ $service }}</li>
                        @endforeach
                    </ul>
                @else
                    <p><strong>Services Offered:</strong> Not listed yet.</p>
                @endif
            </div>
            <div class="business-info__actions">
                <button class="btn-outline" onclick="editDescription()">Edit Business Info</button>
                @if(auth()->user()->isSuperAdmin())
                    <button class="btn-danger" onclick="disownBusiness()">Disown Business</button>
                @endif
            </div>
        @endif
    </div>

    {{-- RIGHT: Recipes --}}
    <div class="recipes-panel">
        <div class="recipes-panel__header">
            <span class="recipes-panel__title">Recipes</span>
            <div class="recipes-search">
                <span>S</span>
                <input type="text" placeholder="Regular Classic Bubble Tea" id="recipeSearchInput">
            </div>
        </div>

        <div class="recipes-categories">
            <span class="recipes-category active" onclick="filterByCategory('all', this)">Drinks</span>
            <span class="recipes-category" onclick="filterByCategory('Goods', this)">Goods</span>
            <span class="recipes-category" onclick="filterByCategory('Sets', this)">Set</span>
        </div>

        <div id="recipesContainer">
            @if($products->isNotEmpty())
                @foreach($products->take(3) as $product)
                    <div class="recipe-card" data-category="{{ $product->category ?? 'Drinks' }}">
                        <div class="recipe-card__name">{{ $product->name }}</div>
                        
                        <div class="recipe-section">
                            <span class="recipe-section__badge">Ingredients</span>
                            <dl class="recipe-ingredients">
                                @forelse($product->recipes as $recipe)
                                    <dt>{{ $recipe->ingredient->name ?? 'Unknown' }}</dt>
                                    <dd>{{ $recipe->size_regular ?? $recipe->quantity ?? '-' }} {{ $recipe->ingredient->unit ?? '' }}</dd>
                                @empty
                                    <dt>No ingredients</dt>
                                    <dd>-</dd>
                                @endforelse
                            </dl>
                        </div>

                        @if($product->procedure)
                            <div class="recipe-section">
                                <span class="recipe-section__badge">Procedure</span>
                                <div class="recipe-procedure">
                                    {!! nl2br(e($product->procedure)) !!}
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            @else
                <div class="empty-state-icon">
                    <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg>
                    <span class="empty-state-text">No recipes found.</span>
                </div>
            @endif
        </div>

        <div class="recipes-footer">
            <a href="{{ route('recipes') }}" class="btn-edit-recipe">Edit</a>
        </div>
    </div>
</div>

{{-- ═══ EDIT BUSINESS INFO MODAL ═══ --}}
<div class="modal-overlay" id="editDescriptionModal">
    <div class="modal-content" style="max-width: 480px;">
        <span class="modal-badge">Edit Business Info</span>
        <h2 class="modal-title">Update business info</h2>
        <p class="modal-subtitle">This appears on the business info card.</p>

        <form onsubmit="submitDescription(event)">
            <div class="form-group">
                <label class="form-label">Description</label>
                <textarea class="form-input form-textarea" id="editDescriptionInput" placeholder="Enter a short description of your business"></textarea>
            </div>
            <div class="form-group">
                <label class="form-label">Services Offered</label>
                <textarea class="form-input form-textarea" id="editServicesInput" placeholder="One service per line, e.g. Artisanal espresso bar"></textarea>
            </div>
            <button type="submit" class="btn-submit" id="editDescriptionSubmitBtn">Save</button>
        </form>
    </div>
</div>

{{-- ═══ ADD BUSINESS MODAL ═══ --}}
<div class="modal-overlay" id="addBusinessModal">
    <div class="modal-content">
        <span class="modal-badge">Add Business</span>
        <h2 class="modal-title">Register a new business</h2>
        <p class="modal-subtitle">Please provide the needed paperwork for the new business.</p>
        
        <form action="{{ route('branches.store') }}" method="POST">
            @csrf
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Business Name</label>
                    <input type="text" name="name" class="form-input" placeholder="Enter your business name" required>
                </div>
                <div class="form-group">
                    <label class="form-label">DTI Registration</label>
                    <div class="file-upload">
                        <span>Upload a PDF of your DTI Business Name Registration</span>
                        <span class="file-upload__icon">U</span>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Business Description</label>
                    <textarea name="description" class="form-input form-textarea" placeholder="Enter a short description of your business"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">SEC Registration</label>
                    <div class="file-upload">
                        <span>Upload a PDF of your Certificate of Registration</span>
                        <span class="file-upload__icon">U</span>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Location</label>
                    <input type="text" name="location" class="form-input" placeholder="Enter business location">
                </div>
                <div class="form-group">
                    <label class="form-label">BIR Registration</label>
                    <div class="file-upload">
                        <span>Upload a PDF of your Certificate of Registration (COR)</span>
                        <span class="file-upload__icon">U</span>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group full-width">
                    <label class="form-label">LGU Permit</label>
                    <div class="file-upload">
                        <span>Upload a PDF of your Mayor's Permit</span>
                        <span class="file-upload__icon">U</span>
                    </div>
                </div>
            </div>
            
            <button type="submit" class="btn-submit">Add new business</button>
        </form>
    </div>
</div>

<script>
    var currentBranchId = {{ $branches->first()->id ?? 'null' }};
    var currentBranchDescription = @json($branches->first()->description ?? null);
    var currentBranchServices = @json($branches->first()->services ?? null);
    var csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    var isSuperAdmin = @json(auth()->user()->isSuperAdmin());

    // ═══ Branch Switching ═══
    function switchBranch(branchId, el) {
        currentBranchId = branchId;
        // Update URL without reload
        var url = new URL(window.location.href);
        url.searchParams.set('branch_id', branchId);
        history.pushState({}, '', url.toString());

        // Reset all tabs
        document.querySelectorAll('.business-tab').forEach(tab => {
            tab.classList.remove('active');
        });
        // Activate clicked tab
        el.classList.add('active');

        // Show loading state
        document.getElementById('businessInfo').style.opacity = '0.4';
        document.getElementById('recipesContainer').style.opacity = '0.4';

        // Fetch new data via AJAX
        fetch('/ajax/branches?branch_id=' + branchId, {
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            renderBranchData(data);
            document.getElementById('businessInfo').style.opacity = '1';
            document.getElementById('recipesContainer').style.opacity = '1';
        })
        .catch(function() {
            document.getElementById('businessInfo').style.opacity = '1';
            document.getElementById('recipesContainer').style.opacity = '1';
        });
    }

    function renderBranchData(data) {
        if (!data.branch) return;
        var branch = data.branch;
        currentBranchDescription = branch.description || null;
        currentBranchServices = branch.services || null;

        // Update business info card
        var infoCard = document.getElementById('businessInfo');
        if (infoCard) {
            var html = '<div class="business-info__header">';
            html += '<span class="business-info__icon">B</span>';
            html += '<span class="business-info__name">' + branch.name + '</span>';
            html += '</div>';
            html += '<div class="business-info__details">';
            html += '<p><strong>Location:</strong> ' + (branch.location || 'Not specified') + '</p>';
            html += '<p><strong>Date of Operation:</strong> Established ' + new Date(branch.created_at).toLocaleDateString('en-US', {year:'numeric', month:'long', day:'numeric'}) + '</p>';
            html += '<p><strong>About:</strong> ' + (branch.description || 'No description set yet.') + '</p>';
            var serviceLines = (branch.services || '').split(/\r\n|\r|\n/).map(function(s) { return s.trim(); }).filter(function(s) { return s.length > 0; });
            if (serviceLines.length > 0) {
                html += '<p><strong>Services Offered:</strong></p>';
                html += '<ul>';
                serviceLines.forEach(function(s) { html += '<li>' + s + '</li>'; });
                html += '</ul>';
            } else {
                html += '<p><strong>Services Offered:</strong> Not listed yet.</p>';
            }
            html += '</div>';
            html += '<div class="business-info__actions">';
            html += '<button class="btn-outline" onclick="editDescription()">Edit Business Info</button>';
            if (isSuperAdmin) {
                html += '<button class="btn-danger" onclick="disownBusiness()">Disown Business</button>';
            }
            html += '</div>';
            infoCard.innerHTML = html;
        }

        // Update recipes
        var recipesContainer = document.getElementById('recipesContainer');
        if (recipesContainer && data.products) {
            if (data.products.length === 0) {
                recipesContainer.innerHTML = '<div class="empty-state-icon"><svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/></svg><span class="empty-state-text">No recipes found.</span></div>';
            } else {
                var recipesHtml = '';
                data.products.forEach(function(product) {
                    recipesHtml += '<div class="recipe-card" data-category="' + (product.category || 'Drinks') + '">';
                    recipesHtml += '<div class="recipe-card__name">' + product.name + '</div>';
                    if (product.recipes && product.recipes.length > 0) {
                        recipesHtml += '<div class="recipe-section">';
                        recipesHtml += '<span class="recipe-section__badge">Ingredients</span>';
                        recipesHtml += '<dl class="recipe-ingredients">';
                        product.recipes.forEach(function(recipe) {
                            recipesHtml += '<dt>' + recipe.ingredient_name + '</dt>';
                            recipesHtml += '<dd>' + recipe.quantity + ' ' + recipe.unit + '</dd>';
                        });
                        recipesHtml += '</dl>';
                        recipesHtml += '</div>';
                    }
                    if (product.procedure) {
                        recipesHtml += '<div class="recipe-section">';
                        recipesHtml += '<span class="recipe-section__badge">Procedure</span>';
                        recipesHtml += '<div class="recipe-procedure">' + product.procedure.replace(/\n/g, '<br>') + '</div>';
                        recipesHtml += '</div>';
                    }
                    recipesHtml += '</div>';
                });
                recipesContainer.innerHTML = recipesHtml;
            }
        }
    }

    // ═══ Add Business Modal ═══
    function openAddBusinessModal() {
        document.getElementById('addBusinessModal').classList.add('active');
    }

    function closeAddBusinessModal() {
        document.getElementById('addBusinessModal').classList.remove('active');
    }

    // Close modal on overlay click
    document.getElementById('addBusinessModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeAddBusinessModal();
        }
    });

    document.getElementById('editDescriptionModal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeEditDescriptionModal();
        }
    });

    // Close modal on Escape key
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAddBusinessModal();
            closeEditDescriptionModal();
        }
    });

    // ═══ Recipe Filtering ═══
    function filterByCategory(category, el) {
        // Update active category
        document.querySelectorAll('.recipes-category').forEach(cat => cat.classList.remove('active'));
        el.classList.add('active');
        
        // Filter recipe cards
        document.querySelectorAll('.recipe-card').forEach(card => {
            if (category === 'all' || card.dataset.category === category) {
                card.style.display = 'block';
            } else {
                card.style.display = 'none';
            }
        });
    }

    // ═══ Recipe Search ═══
    document.getElementById('recipeSearchInput').addEventListener('input', function(e) {
        const query = e.target.value.toLowerCase();
        document.querySelectorAll('.recipe-card').forEach(card => {
            const name = card.querySelector('.recipe-card__name').textContent.toLowerCase();
            card.style.display = name.includes(query) ? 'block' : 'none';
        });
    });

    // ═══ Business Actions ═══
    function editDescription() {
        document.getElementById('editDescriptionInput').value = currentBranchDescription || '';
        document.getElementById('editServicesInput').value = currentBranchServices || '';
        document.getElementById('editDescriptionModal').classList.add('active');
    }

    function closeEditDescriptionModal() {
        document.getElementById('editDescriptionModal').classList.remove('active');
    }

    async function submitDescription(e) {
        e.preventDefault();
        var btn = document.getElementById('editDescriptionSubmitBtn');
        var description = document.getElementById('editDescriptionInput').value;
        var services = document.getElementById('editServicesInput').value;
        btn.disabled = true;
        btn.textContent = 'Saving…';

        try {
            var res = await fetch('/branches/' + currentBranchId + '/description', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
                body: JSON.stringify({ description: description, services: services }),
            });
            var data = await res.json();
            if (res.ok) {
                closeEditDescriptionModal();
                window.location.reload();
            } else {
                alert(data.message || 'Error updating business info.');
            }
        } catch (err) {
            alert('Error updating business info.');
        } finally {
            btn.disabled = false;
            btn.textContent = 'Save';
        }
    }

    async function disownBusiness() {
        if (!currentBranchId) return;
        if (!confirm('Are you sure you want to disown this business? It will be deactivated and hidden from active views. This can be reversed later by a super admin.')) {
            return;
        }

        try {
            var res = await fetch('/branches/' + currentBranchId + '/disown', {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            var data = await res.json();
            if (res.ok) {
                window.location.reload();
            } else {
                alert(data.message || 'Error disowning business.');
            }
        } catch (err) {
            alert('Error disowning business.');
        }
    }
</script>
@endsection
