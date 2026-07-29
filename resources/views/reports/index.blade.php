@extends('layouts.sidebar')

@section('title', 'Reports')

@section('content')
<div class="mb-6">
    <div class="text-[22px] font-extrabold tracking-tight">Reports</div>
    <div class="text-[13px] text-ink-2 mt-0.5">Canned tabular reports you can view or export as CSV</div>
</div>

<div class="grid grid-cols-[repeat(auto-fit,minmax(240px,1fr))] gap-4">
    @foreach ($types as $key => $meta)
        <a href="{{ route('reports.show', $key) }}" class="tile no-underline text-inherit block transition-transform hover:-translate-y-0.5">
            <div class="tile__title">{{ $meta['label'] }}</div>
            <div class="text-[12.5px] text-ink-2 mt-2 leading-relaxed">{{ $meta['description'] }}</div>
            <div class="card__link mt-3 inline-block">View report &rarr;</div>
        </a>
    @endforeach
</div>
@endsection
