<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Payment Confirmation</title>
</head>
<body style="font-family: Arial, sans-serif; background: #f5f5f5; padding: 30px;">
    <div style="max-width: 600px; margin: auto; background: #ffffff; padding: 20px; border-radius: 8px; box-shadow: 0 0 10px rgba(0,0,0,0.05);">
        <h2 style="color: #4CAF50;">✅ Payment Confirmed!</h2>

        <p>Hi {{ $user->display_name }},</p>

        <p>Thank you for your payment! We're happy to let you know that your order <strong>#{{ $order->order_id }}</strong> has been successfully processed.</p>

        <h3>🧾 Order Summary</h3>
        <ul>
            <li><strong>Order ID:</strong> {{ $order->order_id }}</li>
            <li><strong>Amount Paid:</strong> ${{ number_format($order->amount_paid, 2) }}</li>
            <li><strong>Payment Status:</strong> {{ $order->payment_status }}</li>
            <li><strong>Date:</strong> {{ $order->created_at->format('F j, Y g:i A') }}</li>
        </ul>

        <p>If you have any questions or need help, feel free to contact our support team.</p>

        <p style="margin-top: 30px;">Best regards,  
        <br><strong>The {{ config('app.name') }} Team</strong></p>
    </div>
</body>
</html>
