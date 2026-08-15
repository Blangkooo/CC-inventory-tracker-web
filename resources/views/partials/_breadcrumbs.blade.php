{{--
    Minimal breadcrumb trail. Only for pages with real navigational depth
    (Branch detail tabs, Employees profile view) — not rolled out app-wide.

    $bc_items — ordered array of ['label' => string, 'url' => string|null].
    The last item (or any item with url === null) renders as plain text,
    the current page.
--}}
@php $bc_items = $bc_items ?? []; @endphp
@if (count($bc_items) > 1)
<nav class="text-[12px] mb-3 flex items-center gap-1.5 flex-wrap" aria-label="Breadcrumb">
    @foreach ($bc_items as $i => $item)
        @if ($i > 0)
            <span class="opacity-35">/</span>
        @endif
        @if (!empty($item['url']))
            <a href="{{ $item['url'] }}" class="no-underline text-ink-2 hover:underline hover:text-accent">{{ $item['label'] }}</a>
        @else
            <span class="font-semibold">{{ $item['label'] }}</span>
        @endif
    @endforeach
</nav>
@endif
