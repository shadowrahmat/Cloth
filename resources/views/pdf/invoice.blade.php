<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Invoice #{{ $order->order_number }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #171C1A;
            margin: 0;
            padding: 30px;
            font-size: 12px;
            line-height: 1.5;
        }
        .header {
            border-bottom: 2px solid #14382A;
            padding-bottom: 20px;
            margin-bottom: 25px;
        }
        .brand-title {
            font-size: 24px;
            font-weight: 700;
            letter-spacing: 2px;
            text-transform: uppercase;
            color: #14382A;
            margin: 0;
        }
        .brand-subtitle {
            font-size: 10px;
            letter-spacing: 1.5px;
            text-transform: uppercase;
            color: #69736E;
            margin-top: 3px;
        }
        .invoice-title {
            text-align: right;
        }
        .invoice-title h2 {
            margin: 0;
            font-size: 20px;
            color: #14382A;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .invoice-meta {
            margin-top: 5px;
            color: #69736E;
            font-size: 11px;
        }
        .two-col {
            width: 100%;
            margin-bottom: 25px;
        }
        .col {
            vertical-align: top;
            width: 50%;
        }
        .section-label {
            font-size: 10px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #14382A;
            margin-bottom: 6px;
            border-bottom: 1px solid #E7EBE8;
            padding-bottom: 3px;
            display: inline-block;
        }
        .address-box {
            font-size: 11px;
            color: #2D3748;
            line-height: 1.6;
        }
        .table-items {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .table-items th {
            background-color: #14382A;
            color: #ffffff;
            font-size: 10px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 8px 10px;
            text-align: left;
        }
        .table-items td {
            padding: 10px;
            border-bottom: 1px solid #E7EBE8;
            font-size: 11px;
        }
        .table-items tr:nth-child(even) td {
            background-color: #F7F8FA;
        }
        .text-right {
            text-align: right;
        }
        .text-center {
            text-align: center;
        }
        .totals-table {
            width: 45%;
            margin-left: auto;
            border-collapse: collapse;
            margin-bottom: 30px;
        }
        .totals-table td {
            padding: 6px 10px;
            font-size: 11px;
        }
        .totals-table .grand-total td {
            border-top: 2px solid #14382A;
            border-bottom: 2px solid #14382A;
            font-size: 14px;
            font-weight: bold;
            color: #14382A;
        }
        .status-badge {
            display: inline-block;
            padding: 3px 8px;
            font-size: 9px;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            border-radius: 3px;
        }
        .badge-paid {
            background-color: #DCFCE7;
            color: #15803D;
        }
        .badge-unpaid {
            background-color: #FEF3C7;
            color: #B45309;
        }
        .footer {
            border-top: 1px solid #E7EBE8;
            padding-top: 15px;
            font-size: 10px;
            color: #69736E;
            text-align: center;
            margin-top: 40px;
        }
    </style>
</head>
<body>
    <div class="header">
        <table style="width: 100%;">
            <tr>
                <td style="vertical-align: top;">
                    <img src="{{ public_path('images/logo.png') }}" style="height: 56px; width: auto; margin-bottom: 6px;" alt="DRAWER CLOTHING" />
                    <div style="font-size: 10px; color: #69736E; margin-top: 2px;">
                        Gulshan Avenue, Dhaka, Bangladesh<br/>
                        concierge@drawerclothing.com | +880 1700 000000
                    </div>
                </td>
                <td class="invoice-title" style="vertical-align: top;">
                    <h2>TAX INVOICE</h2>
                    <div class="invoice-meta">
                        <strong>Invoice No:</strong> INV-{{ $order->order_number }}<br/>
                        <strong>Date:</strong> {{ $order->created_at->format('M d, Y') }}<br/>
                        <strong>Payment Status:</strong> 
                        <span class="status-badge {{ $order->payment_status === 'paid' ? 'badge-paid' : 'badge-unpaid' }}">
                            {{ strtoupper($order->payment_status ?? 'UNPAID') }}
                        </span>
                    </div>
                </td>
            </tr>
        </table>
    </div>

    <table class="two-col">
        <tr>
            <td class="col">
                <div class="section-label">Billed & Shipped To</div>
                <div class="address-box">
                    <strong>{{ $order->customer_name }}</strong><br/>
                    Email: {{ $order->customer_email }}<br/>
                    Phone: {{ $order->customer_phone ?? 'N/A' }}<br/>
                    @if(is_array($order->shipping_address))
                        {{ $order->shipping_address['address_line_1'] ?? '' }}<br/>
                        {{ $order->shipping_address['city'] ?? '' }}, {{ $order->shipping_address['state'] ?? '' }} {{ $order->shipping_address['postal_code'] ?? '' }}<br/>
                        {{ $order->shipping_address['country'] ?? 'Bangladesh' }}
                    @else
                        {{ $order->shipping_address }}
                    @endif
                </div>
            </td>
            <td class="col" style="text-align: right;">
                <div class="section-label">Fulfillment Summary</div>
                <div class="address-box">
                    <strong>Order Status:</strong> {{ ucfirst($order->order_status) }}<br/>
                    <strong>Payment Method:</strong> {{ strtoupper($order->payment_method ?? 'COD') }}<br/>
                    @if($order->tracking_number)
                        <strong>Tracking / Waybill:</strong> {{ $order->tracking_number }}<br/>
                    @endif
                    @if($order->shipping_method)
                        <strong>Courier:</strong> {{ $order->shipping_method }}<br/>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table class="table-items">
        <thead>
            <tr>
                <th style="width: 5%;">#</th>
                <th style="width: 45%;">Garment Description</th>
                <th style="width: 15%;" class="text-center">SKU / Attributes</th>
                <th style="width: 10%;" class="text-center">Qty</th>
                <th style="width: 12%;" class="text-right">Unit Price</th>
                <th style="width: 13%;" class="text-right">Line Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $index => $item)
                <tr>
                    <td>{{ $index + 1 }}</td>
                    <td>
                        <strong>{{ $item->product_name }}</strong>
                    </td>
                    <td class="text-center" style="font-size: 10px; color: #69736E;">
                        {{ $item->sku ?? 'N/A' }}<br/>
                        @if($item->size || $item->color)
                            ({{ $item->size ?? '' }} {{ $item->color ? '/ ' . $item->color : '' }})
                        @endif
                    </td>
                    <td class="text-center">{{ $item->quantity }}</td>
                    <td class="text-right">${{ number_format($item->price, 2) }}</td>
                    <td class="text-right">${{ number_format($item->subtotal ?? ($item->price * $item->quantity), 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="totals-table">
        <tr>
            <td class="text-right"><strong>Subtotal:</strong></td>
            <td class="text-right">${{ number_format($order->subtotal ?? $order->total, 2) }}</td>
        </tr>
        @if(($order->discount_amount ?? 0) > 0)
        <tr>
            <td class="text-right" style="color: #15803D;"><strong>Discount ({{ $order->coupon_code ?? 'PROMO' }}):</strong></td>
            <td class="text-right" style="color: #15803D;">-${{ number_format($order->discount_amount, 2) }}</td>
        </tr>
        @endif
        <tr>
            <td class="text-right"><strong>Shipping / Delivery:</strong></td>
            <td class="text-right">${{ number_format($order->shipping_fee ?? 0, 2) }}</td>
        </tr>
        @if(($order->tax_amount ?? 0) > 0)
        <tr>
            <td class="text-right"><strong>VAT / Tax:</strong></td>
            <td class="text-right">${{ number_format($order->tax_amount, 2) }}</td>
        </tr>
        @endif
        <tr class="grand-total">
            <td class="text-right">Total Payable:</td>
            <td class="text-right">${{ number_format($order->total, 2) }}</td>
        </tr>
    </table>

    @if($order->order_notes)
        <div style="background-color: #F7F8FA; border: 1px solid #E7EBE8; padding: 10px; margin-bottom: 25px; font-size: 11px;">
            <strong>Special Instructions / Notes:</strong><br/>
            {{ $order->order_notes }}
        </div>
    @endif

    <div class="footer">
        <p>Thank you for shopping with DRAWER CLOTHING. For alterations, sizing assistance or returns within 14 days, contact concierge@drawerclothing.com.</p>
        <p style="font-size: 9px; color: #A89B8A;">Computer generated invoice. No signature required.</p>
    </div>
</body>
</html>