<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document Ready</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, Helvetica, sans-serif;">
    @php
        $appName = config('app.name', 'TrustHaus');
        $serviceTitle = $order->service?->title ?? $order->service_name;
        $customerName = $order->customer_name ?: 'there';
    @endphp

    <!-- Preheader (hidden preview text) -->
    <div style="display:none; max-height:0; overflow:hidden; opacity:0; color:transparent;">
        Your document is ready. Download it securely using the link inside.
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="padding:28px 12px;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0"
                    style="width:100%; max-width:600px; background:#ffffff; border-radius:10px; overflow:hidden; box-shadow:0 6px 16px rgba(0,0,0,0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background:#22c55e; padding:22px 24px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:20px; font-weight:700; letter-spacing:0.2px;">
                                {{ $appName }}
                            </h1>
                            <p style="margin:6px 0 0; color:#dcfce7; font-size:12px;">
                                Secure document delivery
                            </p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:26px 24px; color:#111827;">
                            <h2 style="margin:0 0 10px; font-size:18px; font-weight:700; color:#111827;">
                                Your document is ready
                            </h2>

                            <p style="margin:0 0 14px; font-size:14px; line-height:1.6; color:#374151;">
                                Hello <strong style="color:#111827;">{{ $customerName }}</strong>,
                                thank you for your purchase. You can download your document using the button below.
                            </p>

                            <!-- Summary card -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0"
                                style="background:#f8fafc; border:1px solid #e5e7eb; border-radius:10px; margin:16px 0 18px;">
                                <tr>
                                    <td style="padding:14px 14px 6px;">
                                        <p style="margin:0; font-size:12px; font-weight:700; letter-spacing:0.4px; color:#6b7280; text-transform:uppercase;">
                                            Order Summary
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:4px 14px 14px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size:14px;">
                                            <tr>
                                                <td style="padding:8px 0; color:#6b7280;">Order number</td>
                                                <td style="padding:8px 0; color:#111827; font-weight:700;" align="right">#{{ $order->order_number }}</td>
                                            </tr>
                                            <tr>
                                                <td style="padding:8px 0; color:#6b7280;">Service</td>
                                                <td style="padding:8px 0; color:#111827; font-weight:600;" align="right">{{ $serviceTitle }}</td>
                                            </tr>
                                            @if(!empty($order->state))
                                                <tr>
                                                    <td style="padding:8px 0; color:#6b7280;">State</td>
                                                    <td style="padding:8px 0; color:#111827; font-weight:600;" align="right">{{ $order->state }}</td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td style="padding:8px 0; color:#6b7280;">Amount paid</td>
                                                <td style="padding:8px 0; color:#111827; font-weight:700;" align="right">${{ number_format((float) $order->amount, 2) }}</td>
                                            </tr>
                                            @if(!empty($order->stripe_transaction_id))
                                                <tr>
                                                    <td style="padding:8px 0; color:#6b7280;">Transaction ID</td>
                                                    <td style="padding:8px 0; color:#111827; font-weight:600;" align="right">{{ $order->stripe_transaction_id }}</td>
                                                </tr>
                                            @endif
                                            @if(!empty($order->card_brand) && !empty($order->card_last4))
                                                <tr>
                                                    <td style="padding:8px 0; color:#6b7280;">Payment method</td>
                                                    <td style="padding:8px 0; color:#111827; font-weight:600;" align="right">
                                                        {{ strtoupper($order->card_brand) }} •••• {{ $order->card_last4 }}
                                                    </td>
                                                </tr>
                                            @endif
                                            <tr>
                                                <td style="padding:8px 0; color:#6b7280;">Status</td>
                                                <td style="padding:8px 0;" align="right">
                                                    <span style="display:inline-block; background:#dcfce7; color:#166534; padding:4px 10px; border-radius:999px; font-size:12px; font-weight:700;">
                                                        Completed
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA Button -->
                            <table role="presentation" cellpadding="0" cellspacing="0" align="center" style="margin:0 auto;">
                                <tr>
                                    <td align="center" style="padding:6px 0 0;">
                                        <a href="{{ $downloadUrl }}"
                                            style="background:#2563eb; color:#ffffff; text-decoration:none; display:inline-block; padding:12px 18px; border-radius:8px; font-size:14px; font-weight:700;">
                                            Download document
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:14px 0 0; font-size:12px; line-height:1.6; color:#6b7280;">
                                If the button doesn't work, copy and paste this link into your browser:
                                <br>
                                <a href="{{ $downloadUrl }}" style="color:#2563eb; word-break:break-all;">{{ $downloadUrl }}</a>
                            </p>

                            <p style="margin:14px 0 0; font-size:12px; line-height:1.6; color:#6b7280;">
                                For your security, please do not share this email or link with anyone.
                            </p>

                            <hr style="border:0; border-top:1px solid #e5e7eb; margin:18px 0;">

                            <p style="margin:0; font-size:12px; line-height:1.6; color:#6b7280;">
                                Need help? Reply to this email and our team will assist you.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#f9fafb; padding:14px 18px; text-align:center;">
                            <p style="margin:0; font-size:11px; line-height:1.5; color:#6b7280;">
                                © {{ date('Y') }} {{ $appName }}. All rights reserved.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>

</html>
