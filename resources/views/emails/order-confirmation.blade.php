<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmation</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
        }
        .order-number {
            font-size: 18px;
            font-weight: bold;
            color: #444;
            margin-bottom: 20px;
        }
        .section {
            margin-bottom: 30px;
        }
        .section-title {
            font-size: 16px;
            font-weight: bold;
            border-bottom: 1px solid #ddd;
            padding-bottom: 5px;
            margin-bottom: 15px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
        }
        table th, table td {
            padding: 8px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 12px;
            color: #777;
        }
        .total-row {
            font-weight: bold;
        }
        .user-guide {
            background-color: #f5f5f5;
            padding: 15px;
            border-radius: 5px;
            margin-top: 20px;
        }
        .user-guide h3 {
            margin-top: 0;
        }
        .user-guide-links a {
            display: block;
            margin-bottom: 5px;
            color: #0066cc;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Thank You for Your Order!</h1>
    </div>

    <div class="order-number">
        Order #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
    </div>

    <div class="section">
        <p>Dear {{ $order->first_name }} {{ $order->last_name }},</p>
        <p>We're pleased to confirm that your order has been received and is now being processed. Below you'll find your order details.</p>
    </div>

    <div class="section">
        <div class="section-title">Order Summary</div>
        <p><strong>Order Date:</strong> {{ $order->created_at->format('M d, Y') }}</p>
        <p><strong>Payment Method:</strong> {{ $order->payment_method }}</p>
        <p><strong>Order Status:</strong> {{ ucfirst($order->status) }}</p>
    </div>

    <div class="section">
        <div class="section-title">Shipping Address</div>
        <p>
            {{ $order->first_name }} {{ $order->last_name }}<br>
            {{ $order->company ? $order->company . '<br>' : '' }}
            {{ $order->address }}<br>
            {{ $order->apartment ? $order->apartment . '<br>' : '' }}
            {{ $order->city }}, {{ $order->state ? $order->state . ', ' : '' }}{{ $order->postal_code }}<br>
            {{ $order->country }}<br>
            {{ $order->phone }}
        </p>
    </div>

    <div class="section">
        <div class="section-title">Order Details</div>
        <table>
            <thead>
                <tr>
                    <th>Product</th>
                    <th>Quantity</th>
                    <th>Price</th>
                </tr>
            </thead>
            <tbody>
                @foreach($order->orderItems as $item)
                <tr>
                    <td>{{ $item->product->name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>LKR {{ number_format($item->total, 2) }}</td>
                </tr>
                @endforeach
                <tr>
                    <td colspan="2" style="text-align: right;">Subtotal:</td>
                    <td>LKR {{ number_format($order->total - $order->tax - $order->shipping_rate + $order->discount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: right;">Discount:</td>
                    <td>LKR {{ number_format($order->discount, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: right;">Tax:</td>
                    <td>LKR {{ number_format($order->tax, 2) }}</td>
                </tr>
                <tr>
                    <td colspan="2" style="text-align: right;">Shipping:</td>
                    <td>LKR {{ number_format($order->shipping_rate, 2) }}</td>
                </tr>
                <tr class="total-row">
                    <td colspan="2" style="text-align: right;">Total:</td>
                    <td>LKR {{ number_format($order->total, 2) }}</td>
                </tr>
            </tbody>
        </table>
    </div>

    @php
    $productsWithGuides = [];
    foreach($order->orderItems as $item) {
        if(isset($item->product->user_guide_pdf) && !empty($item->product->user_guide_pdf)) {
            $productsWithGuides[] = [
                'name' => $item->product->name,
                'pdf' => $item->product->user_guide_pdf
            ];
        }
    }
    @endphp

    @if(count($productsWithGuides) > 0)
    <div class="user-guide">
        <h3>Product User Guides</h3>
        <p>Click the links below to download user guides for your purchased products:</p>
        <div class="user-guide-links">
            @foreach($productsWithGuides as $guide)
            <a href="{{ env('APP_ASSET_URL') }}/storage/{{ $guide['pdf'] }}" target="_blank">{{ $guide['name'] }} - User Guide</a>
            @endforeach
        </div>
    </div>
    @endif

    <div class="section">
        <p>If you have any questions about your order, please contact our customer service at <a href="mailto:support@yourstore.com">support@yourstore.com</a>.</p>
        <p>Thank you for shopping with us!</p>
    </div>

    <div class="footer">
        <p>&copy; {{ date('Y') }} Your Store. All rights reserved.</p>
    </div>
</body>
</html>