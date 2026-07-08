<div class="dash-main">

    <div class="widget-head" style="margin-bottom: 0;">
        <span class="detail-sub">Global catalog &mdash; recipes apply to all branches.</span>
    </div>

    @forelse ($products as $product)
        @php
            $regularRecipes = $product->recipes->where('size', 'regular')->values();
            $largeRecipes = $product->recipes->where('size', 'large')->values();
            $maxRows = max($regularRecipes->count(), $largeRecipes->count());
        @endphp
        <div class="widget">
            <div class="widget-head">
                <h2>{{ $product->name }}</h2>
                <div>
                    @if ($product->category)
                        <span class="badge blue">{{ $product->category }}</span>
                    @endif
                    <span class="badge green">&#8369;{{ number_format($product->price, 2) }}</span>
                </div>
            </div>

            @if ($product->recipes->isEmpty())
                <div class="empty-state">No ingredients assigned to this product yet.</div>
            @else
                <table class="data-table">
                    <thead>
                        <tr>
                            <th colspan="2" style="text-align: center;">Regular</th>
                            <th colspan="2" style="text-align: center;">Large</th>
                        </tr>
                        <tr>
                            <th>Measurement</th>
                            <th>Ingredient</th>
                            <th>Measurement</th>
                            <th>Ingredient</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for ($i = 0; $i < $maxRows; $i++)
                            <tr>
                                @if ($regularRecipes->has($i))
                                    <td>{{ rtrim(rtrim(number_format($regularRecipes[$i]->quantity_required, 3), '0'), '.') }}{{ $regularRecipes[$i]->ingredient->unit ?? '' }}</td>
                                    <td class="cell-primary">{{ $regularRecipes[$i]->ingredient->name ?? '—' }}</td>
                                @else
                                    <td>&mdash;</td>
                                    <td>&mdash;</td>
                                @endif
                                @if ($largeRecipes->has($i))
                                    <td>{{ rtrim(rtrim(number_format($largeRecipes[$i]->quantity_required, 3), '0'), '.') }}{{ $largeRecipes[$i]->ingredient->unit ?? '' }}</td>
                                    <td class="cell-primary">{{ $largeRecipes[$i]->ingredient->name ?? '—' }}</td>
                                @else
                                    <td>&mdash;</td>
                                    <td>&mdash;</td>
                                @endif
                            </tr>
                        @endfor
                    </tbody>
                </table>

                @if ($largeRecipes->isEmpty())
                    <div class="detail-sub" style="margin-top: 8px;">No Large-size formula configured yet &mdash; only Regular is sold via POS.</div>
                @endif
            @endif

            @if ($product->procedure)
                <div style="margin-top: 16px; padding-top: 16px; border-top: 1px solid rgba(92, 45, 27, 0.15);">
                    <div class="stat-label" style="margin-bottom: 8px;">Procedure</div>
                    @foreach (explode("\n", $product->procedure) as $line)
                        @continue(trim($line) === '')
                        <p style="font-size: 13px; margin-bottom: 6px;">{{ $line }}</p>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="widget">
            <div class="empty-state">No products in the catalog yet.</div>
        </div>
    @endforelse

</div>
