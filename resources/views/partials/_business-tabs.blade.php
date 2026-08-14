{{--
    Sub-navigation shared by the four Business pages.
    Pass the active tab: @include('partials._business-tabs', ['active' => 'verification'])
--}}
@php
    $tabs = [
        'summary'      => ['Summary',      route('business.summary'),      '<line x1="18" y1="20" x2="18" y2="10"/><line x1="12" y1="20" x2="12" y2="4"/><line x1="6" y1="20" x2="6" y2="14"/>'],
        'recipes'      => ['Recipe',       route('business.recipes'),      '<path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"/><path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"/>'],
        'workers'      => ['Staff',        route('business.workers'),      '<path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>'],
        'verification' => ['Verification', route('business.verification'), '<polyline points="20 6 9 17 4 12"/>'],
    ];
@endphp

<div class="mb-6 flex flex-wrap gap-1.5">
    @foreach ($tabs as $key => [$label, $url, $icon])
        <a href="{{ $url }}"
           class="flex items-center gap-1.5 rounded-full border-[1.5px] px-4 py-[7px] text-xs font-semibold transition-colors
                  {{ $key === $active
                        ? 'border-accent bg-accent text-white'
                        : 'border-line bg-card text-ink-2 hover:border-accent hover:text-accent' }}">
            <svg width="13" height="13" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">{!! $icon !!}</svg>
            {{ $label }}
        </a>
    @endforeach
</div>
