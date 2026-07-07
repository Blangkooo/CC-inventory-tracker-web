<div class="dash-main">

    <div class="widget-head" style="margin-bottom: 0;">
        <span class="detail-sub">Global catalog &mdash; recipes apply to all branches.</span>
    </div>

    @forelse ($products as $product)
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
                            <th>Ingredient</th>
                            <th>Quantity Required (per unit sold)</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($product->recipes as $recipe)
                            <tr>
                                <td class="cell-primary">{{ $recipe->ingredient->name ?? '—' }}</td>
                                <td>{{ rtrim(rtrim(number_format($recipe->quantity_required, 3), '0'), '.') }} {{ $recipe->ingredient->unit ?? '' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>
    @empty
        <div class="widget">
            <div class="empty-state">No products in the catalog yet.</div>
        </div>
    @endforelse

</div>
