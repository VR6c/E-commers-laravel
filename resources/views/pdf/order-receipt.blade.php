<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Order Receipt #{{ $order->id }}</title>
    <style>
        @page {
            size: a5 portrait;
            margin: 8mm 10mm;
        }

        * {
            box-sizing: border-box;
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        }

        body {
            font-size: 8pt;
            line-height: 1.35;
            color: #1e293b;
            margin: 0;
            padding: 0;
            background: #ffffff;
        }

        .header-table {
            width: 100%;
            border-bottom: 2px solid #0f172a;
            padding-bottom: 8px;
            margin-bottom: 10px;
        }

        .brand-name {
            font-size: 14pt;
            font-weight: 800;
            color: #0f172a;
            letter-spacing: -0.02em;
        }

        .brand-sub {
            font-size: 7.5pt;
            color: #64748b;
        }

        .receipt-badge {
            font-size: 8pt;
            font-weight: bold;
            padding: 4px 10px;
            border-radius: 4px;
            background: #0f172a;
            color: #ffffff;
            text-align: right;
            display: inline-block;
        }

        .meta-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 7.5pt;
        }

        .meta-card {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            padding: 6px 10px;
        }

        .meta-title {
            font-size: 6.5pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.06em;
            color: #64748b;
            margin-bottom: 3px;
        }

        /* Items Table */
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 10px;
        }

        .items-table th {
            font-size: 7pt;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #64748b;
            border-bottom: 1.5px solid #cbd5e1;
            padding: 4px 6px;
            text-align: left;
            background: #f8fafc;
        }

        .items-table td {
            font-size: 8pt;
            padding: 5px 6px;
            border-bottom: 1px solid #f1f5f9;
            color: #334155;
        }

        .items-table .text-right {
            text-align: right;
        }

        /* Total calculation block */
        .totals-table {
            width: 50%;
            margin-left: auto;
            border-collapse: collapse;
            font-size: 8pt;
            margin-bottom: 10px;
        }

        .totals-table td {
            padding: 3px 6px;
        }

        .totals-table .total-row {
            border-top: 1.5px solid #0f172a;
            font-weight: 800;
            font-size: 9pt;
            color: #0f172a;
        }

        /* Unlocked Recipes Highlight */
        .recipes-block {
            background: #f0fdf4;
            border: 1px solid #bbf7d0;
            border-radius: 6px;
            padding: 8px 10px;
            margin-top: 8px;
            font-size: 7.5pt;
        }

        .recipes-title {
            font-weight: 800;
            color: #15803d;
            font-size: 8pt;
            margin-bottom: 3px;
        }

        .footer {
            margin-top: 12px;
            padding-top: 6px;
            border-top: 1px solid #e2e8f0;
            font-size: 6.5pt;
            color: #94a3b8;
            text-align: center;
        }
    </style>
</head>
<body>

    <table class="header-table" cellpadding="0" cellspacing="0">
        <tr>
            <td>
                <div class="brand-name">{{ $appName }}</div>
                <div class="brand-sub">Official Purchase Receipt &bull;</div>
            </td>
            <td align="right">
                <div class="receipt-badge">RECEIPT #{{ $order->id }}</div>
            </td>
        </tr>
    </table>

    <table class="meta-table" cellpadding="0" cellspacing="6">
        <tr>
            <td width="50%" valign="top">
                <div class="meta-card">
                    <div class="meta-title">Order Information</div>
                    <div><strong>Date:</strong> {{ $order->created_at ? $order->created_at->format('M d, Y h:i A') : 'N/A' }}</div>
                    <div><strong>Payment:</strong> {{ strtoupper($order->payment_method ?? 'Card/COD') }}</div>
                    <div><strong>Status:</strong> <span style="color: #15803d; font-weight: bold;">{{ ucfirst($order->status) }}</span></div>
                </div>
            </td>
            <td width="50%" valign="top">
                <div class="meta-card">
                    <div class="meta-title">Customer / Delivery</div>
                    @if($order->shippingAddress)
                        <div><strong>{{ $order->shippingAddress->name }}</strong></div>
                        <div>{{ $order->shippingAddress->address }} {{ $order->shippingAddress->city }}</div>
                        <div>{{ $order->shippingAddress->phone }}</div>
                    @else
                        <div><strong>{{ $order->customer->name ?? 'Customer' }}</strong></div>
                        <div>{{ $order->guest_email ?? ($order->customer->email ?? '') }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="items-table" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th>Item Description</th>
                <th class="text-right" width="15%">Qty</th>
                <th class="text-right" width="20%">Price</th>
                <th class="text-right" width="20%">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->details as $detail)
                <tr>
                    <td>
                        <strong>{{ $detail->product->name ?? 'Product' }}</strong>
                        @if($detail->product && $detail->product->recipes->isNotEmpty())
                            <div style="font-size: 6.5pt; color: #16a34a;">
                                &bull; Includes bonus downloadable recipe!
                            </div>
                        @endif
                    </td>
                    <td class="text-right">{{ $detail->quantity }}</td>
                    <td class="text-right">{{ $currency->symbol }}{{ number_format($detail->price, 2) }}</td>
                    <td class="text-right">{{ $currency->symbol }}{{ number_format($detail->price * $detail->quantity, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    @php
        $subtotal = $order->details->sum(fn($d) => $d->price * $d->quantity);
        $discount = round($subtotal - $order->total_amount, 2);
    @endphp

    <table class="totals-table" cellpadding="0" cellspacing="0">
        <tr>
            <td>Subtotal</td>
            <td class="text-right">{{ $currency->symbol }}{{ number_format($subtotal, 2) }}</td>
        </tr>
        @if($discount > 0)
            <tr>
                <td style="color: #dc2626;">Coupon Discount</td>
                <td class="text-right" style="color: #dc2626;">&minus;{{ $currency->symbol }}{{ number_format($discount, 2) }}</td>
            </tr>
        @endif
        <tr>
            <td>Shipping</td>
            <td class="text-right">Free</td>
        </tr>
        <tr class="total-row">
            <td>Total Paid</td>
            <td class="text-right">{{ $currency->symbol }}{{ number_format($order->total_amount, 2) }}</td>
        </tr>
    </table>

    {{-- Unlocked Recipes Notice --}}
    @if(isset($unlockedRecipes) && $unlockedRecipes->isNotEmpty())
        <div class="recipes-block">
            <div class="recipes-title">🎉 Unlocked Chef Recipes Included With This Order:</div>
            @foreach($unlockedRecipes as $recipe)
                <div>&bull; <strong>{{ $recipe->title }}</strong> ({{ $recipe->total_time }} min &bull; {{ ucfirst($recipe->difficulty) }}) &mdash; Available in your account & thank you page for PDF download.</div>
            @endforeach
        </div>
    @endif

    <div class="footer">
        Thank you for your purchase! &bull; Generated from {{ $appName }} &bull; Standard Receipt
    </div>

</body>
</html>
