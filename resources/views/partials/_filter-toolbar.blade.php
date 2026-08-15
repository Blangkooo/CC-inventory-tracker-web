{{--
    Reusable client-side filter toolbar: Search + Branch + Date + Status, each
    independently togglable. Filters rows matching $ft_target by toggling
    display:none — same convention as the Alerts page filters, generalized.

    Required:
      $ft_target — CSS selector for the rows to filter (e.g. '#paymentsTbody tr')

    Optional (pass to enable each control):
      $ft_search            — bool, default true
      $ft_searchPlaceholder — string
      $ft_branches          — Collection of Branch, enables the branch select
      $ft_date              — bool, enables the date-range select
      $ft_statusOptions     — assoc array value => label, enables the status select

    Rows read filter state from data attributes:
      data-search="lowercase searchable text"
      data-branch-id="123"
      data-status="pending"
      data-created="1699999999" (unix timestamp)
--}}
@php
    $ft_id = $ft_id ?? 'filterToolbar';
    $ft_search = $ft_search ?? true;
    $ft_searchPlaceholder = $ft_searchPlaceholder ?? 'Search…';
    $ft_branches = $ft_branches ?? null;
    $ft_date = $ft_date ?? false;
    $ft_statusOptions = $ft_statusOptions ?? null;
@endphp

<div class="flex items-center gap-2.5 flex-wrap mb-4" id="{{ $ft_id }}">
    @if ($ft_search)
        <input type="text" class="form-input" style="width:220px;" id="{{ $ft_id }}-search" placeholder="{{ $ft_searchPlaceholder }}">
    @endif
    @if ($ft_branches && $ft_branches->count() > 1)
        <select class="form-input" style="width:170px;" id="{{ $ft_id }}-branch">
            <option value="">All Branches</option>
            @foreach ($ft_branches as $b)
                <option value="{{ $b->id }}">{{ $b->name }}</option>
            @endforeach
        </select>
    @endif
    @if ($ft_statusOptions)
        <select class="form-input" style="width:160px;" id="{{ $ft_id }}-status">
            <option value="">All Statuses</option>
            @foreach ($ft_statusOptions as $value => $label)
                <option value="{{ $value }}">{{ $label }}</option>
            @endforeach
        </select>
    @endif
    @if ($ft_date)
        <select class="form-input" style="width:150px;" id="{{ $ft_id }}-date">
            <option value="all">All Time</option>
            <option value="today">Today</option>
            <option value="week">This Week</option>
            <option value="month">This Month</option>
            <option value="quarter">This Quarter</option>
        </select>
    @endif
</div>

<script>
(function () {
    var target = {{ Illuminate\Support\Js::from($ft_target) }};
    var searchEl = document.getElementById('{{ $ft_id }}-search');
    var branchEl = document.getElementById('{{ $ft_id }}-branch');
    var statusEl = document.getElementById('{{ $ft_id }}-status');
    var dateEl = document.getElementById('{{ $ft_id }}-date');

    function rangeStart(range) {
        var now = new Date();
        if (range === 'today') return new Date(now.getFullYear(), now.getMonth(), now.getDate()).getTime() / 1000;
        if (range === 'week') { var d = new Date(now); d.setDate(d.getDate() - d.getDay()); d.setHours(0, 0, 0, 0); return d.getTime() / 1000; }
        if (range === 'month') return new Date(now.getFullYear(), now.getMonth(), 1).getTime() / 1000;
        if (range === 'quarter') return new Date(now.getFullYear(), Math.floor(now.getMonth() / 3) * 3, 1).getTime() / 1000;
        return null;
    }

    function applyFilterToolbar() {
        var rows = document.querySelectorAll(target);
        var q = searchEl ? searchEl.value.toLowerCase().trim() : '';
        var branch = branchEl ? branchEl.value : '';
        var status = statusEl ? statusEl.value : '';
        var range = dateEl ? dateEl.value : 'all';
        var start = range === 'all' ? null : rangeStart(range);

        rows.forEach(function (row) {
            var matchesSearch = !q || (row.dataset.search || '').toLowerCase().includes(q);
            var matchesBranch = !branch || row.dataset.branchId === branch;
            var matchesStatus = !status || row.dataset.status === status;
            var matchesDate = start === null || (row.dataset.created && parseInt(row.dataset.created, 10) >= start);
            row.style.display = (matchesSearch && matchesBranch && matchesStatus && matchesDate) ? '' : 'none';
        });
    }

    [searchEl, branchEl, statusEl, dateEl].forEach(function (el) {
        if (el) el.addEventListener(el.tagName === 'SELECT' ? 'change' : 'input', applyFilterToolbar);
    });
})();
</script>
