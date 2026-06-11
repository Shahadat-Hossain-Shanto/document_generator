<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Password Reset OTP</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; background-color:#f4f6f8; font-family: Arial, sans-serif;">
<table width="100%" cellpadding="0" cellspacing="0" style="padding:20px 10px;">
    <tr>
        <td align="center">
            <table cellpadding="0" cellspacing="0"
                   style="background:#ffffff; border-radius:8px; overflow:hidden; box-shadow:0 4px 10px rgba(0,0,0,0.05); max-width:420px; width:100%;">

                <!-- Header -->
                <tr>
                    <td style="background:#2563eb; padding:18px; text-align:center;">
                        <h1 style="color:#ffffff; margin:0; font-size:18px;">TrustHaus Inc</h1>
                        <p style="color:#e0e7ff; margin:4px 0 0; font-size:12px;">Password Reset Verification</p>
                    </td>
                </tr>

                <!-- Body -->
                <tr>
                    <td style="padding:20px;">
                        <p style="font-size:13px; color:#374151; margin:0 0 10px;">Hello,</p>

                        <p style="font-size:13px; color:#374151; margin:0 0 16px;">
                            Use the following One-Time Password (OTP) to reset your password:
                        </p>

                        <div style="text-align:center; margin:20px 0;">
                            <span style="
                                display:inline-block;
                                background:#f1f5f9;
                                padding:12px 24px;
                                font-size:22px;
                                letter-spacing:4px;
                                font-weight:bold;
                                color:#111827;
                                border-radius:6px;
                            ">
                                {{ $otp }}
                            </span>
                        </div>

                        <p style="font-size:12px; color:#6b7280; margin:0 0 10px;">
                            This OTP expires in <strong>10 minutes</strong>.
                        </p>

                        <p style="font-size:12px; color:#6b7280; margin:0;">
                            If you didn’t request this, please ignore this email.
                        </p>
                    </td>
                </tr>

                <!-- Footer -->
                <tr>
                    <td style="background:#f9fafb; padding:14px; text-align:center;">
                        <p style="font-size:11px; color:#4b4b4b; margin:0;">
                            © {{ date('Y') }} TrustHaus Inc
                        </p>
                    </td>
                </tr>

            </table>
        </td>
    </tr>
</table>
</body>
</html>
