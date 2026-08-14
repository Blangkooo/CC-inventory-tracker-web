<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial, sans-serif; color: #1f2937; line-height: 1.5; }
        .container { max-width: 480px; margin: 0 auto; padding: 24px; }
        .badge { display: inline-block; padding: 4px 10px; border-radius: 4px; font-size: 12px; font-weight: bold; color: #fff; }
        .badge-high { background-color: #dc2626; }
        .badge-medium { background-color: #d97706; }
        .badge-low { background-color: #6b7280; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        td { padding: 6px 0; border-bottom: 1px solid #e5e7eb; }
        td:first-child { color: #6b7280; width: 40%; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Discrepancy Alert</h2>
        <span class="badge badge-{{ $alert->severity }}">{{ strtoupper($alert->severity) }} SEVERITY</span>

        <table>
            <tr><td>Branch</td><td>{{ $alert->branch->name }}</td></tr>
            <tr><td>Type</td><td>{{ str_replace('_', ' ', ucfirst($alert->type)) }}</td></tr>
            @if ($alert->ingredient)
                <tr><td>Ingredient</td><td>{{ $alert->ingredient->name }}</td></tr>
            @endif
            <tr><td>Expected</td><td>{{ $alert->expected_value }}</td></tr>
            <tr><td>Actual</td><td>{{ $alert->actual_value }}</td></tr>
            <tr><td>Variance</td><td>{{ $alert->variance }}</td></tr>
        </table>

        <p style="margin-top: 16px;">{{ $alert->details }}</p>

        @if ($alert->ingredient && ($supplier = $alert->ingredient->primarySupplier()))
            <div style="margin-top: 16px; padding: 12px 14px; background: #f9fafb; border-radius: 6px; border: 1px solid #e5e7eb;">
                <strong style="font-size: 13px;">Reorder from:</strong>
                <div style="font-size: 13px; margin-top: 4px;">
                    {{ $supplier->name }}<br>
                    @if ($supplier->contact_person) {{ $supplier->contact_person }}<br> @endif
                    @if ($supplier->contact_number) {{ $supplier->contact_number }}<br> @endif
                    @if ($supplier->address) {{ $supplier->address }}{{ $supplier->landmark ? ' ('.$supplier->landmark.')' : '' }} @endif
                </div>
                <a href="{{ url('/suppliers') }}" style="font-size: 13px; display: inline-block; margin-top: 8px;">View Supplier Directory &rarr;</a>
            </div>
        @endif

        <p style="color: #6b7280; font-size: 13px; margin-top: 24px;">
            Review this alert in the owner dashboard to mark it as reviewed or dismissed.
        </p>
    </div>
</body>
</html>
