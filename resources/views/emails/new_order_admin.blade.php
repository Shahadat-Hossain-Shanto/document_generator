<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <title>New Order Received</title>
</head>

<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, sans-serif;">

    <table align="center" width="100%" cellpadding="0" cellspacing="0" style="padding:30px 0;">
        <tr>
            <td align="center">

                <table width="600" cellpadding="0" cellspacing="0"
                    style="background:#ffffff; border-radius:10px; box-shadow:0 4px 12px rgba(0,0,0,0.08); overflow:hidden;">

                    <!-- Header -->
                    <tr>
                        <td style="background:#4CAF50; color:#ffffff; text-align:center; padding:25px;">
                            <h2 style="margin:0;">🎉 New Order Received</h2>
                            <p style="margin:5px 0 0; font-size:14px;">You have received a new order</p>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:30px;">

                            <table width="100%" cellpadding="8" cellspacing="0" style="font-size:15px; color:#333;">
                                <tr>
                                    <td style="font-weight:bold;">Order Number:</td>
                                    <td>{{ $order->order_number }}</td>
                                </tr>

                                <tr style="background:#f9f9f9;">
                                    <td style="font-weight:bold;">Customer Name:</td>
                                    <td>{{ $order->customer_name }}</td>
                                </tr>

                                <tr>
                                    <td style="font-weight:bold;">Email:</td>
                                    <td>{{ $order->customer_email }}</td>
                                </tr>

                                <tr style="background:#f9f9f9;">
                                    <td style="font-weight:bold;">State:</td>
                                    <td>{{ $order->state }}</td>
                                </tr>

                                <tr style="background:#f9f9f9;">
                                    <td style="font-weight:bold;">Service:</td>
                                    <td>{{ $order->service?->title }}</td>
                                </tr>

                                <tr>
                                    <td style="font-weight:bold;">Amount:</td>
                                    <td style="color:#4CAF50; font-weight:bold;">${{ $order->amount }}</td>
                                </tr>

                                <tr style="background:#f9f9f9;">
                                    <td style="font-weight:bold;">Transaction ID:</td>
                                    <td>{{ $order->stripe_transaction_id }}</td>
                                </tr>

                                <tr style="background:#f9f9f9;">
                                    <td style="font-weight:bold;">Status:</td>
                                    <td>
                                        <span
                                            style="background:#e3f2fd; color:#1976d2; padding:5px 10px; border-radius:20px; font-size:13px;">
                                            {{ $order->status }}
                                        </span>
                                    </td>
                                </tr>
                            </table>

                        </td>
                    </tr>

                </table>

            </td>
        </tr>
    </table>

</body>

</html>
