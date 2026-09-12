<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your ZYRA payment needs attention</title>
</head>
<body style="margin:0; padding:0; background-color:#f6f6f6; font-family:'Segoe UI', Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f6f6; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.06);">
                    <!-- Header -->
                    <tr>
                        <td style="background:#18181b; padding:24px 32px; text-align:center;">
                            <span style="font-size:26px; font-weight:800; letter-spacing:2px; color:#ffffff;">ZYRA</span>
                            <span style="display:block; font-size:12px; color:#a1a1aa; letter-spacing:4px; margin-top:4px;">L I F E S T Y L E</span>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:36px 32px;">
                            <h1 style="margin:0 0 12px; font-size:22px; color:#18181b;">Heads up, {{ $customerName }} 🙁</h1>
                            <p style="margin:0 0 8px; font-size:15px; color:#52525b; line-height:1.6;">
                                Your payment for the ZYRA order could not be completed, so the order was
                                <strong style="color:#18181b;">not placed</strong> and no money was charged.
                            </p>
                            @if(!empty($orderId))
                            <p style="margin:0 0 8px; font-size:13px; color:#71717a; line-height:1.6;">
                                Payment reference: <strong>{{ $orderId }}</strong>
                            </p>
                            @endif
                            @if($total > 0)
                            <p style="margin:0 0 24px; font-size:15px; color:#52525b; line-height:1.6;">
                                Amount attempted: <strong style="color:#18181b;">₹{{ number_format($total, 0) }}</strong>
                            </p>
                            @else
                            <p style="margin:0 0 24px; font-size:15px; color:#52525b; line-height:1.6;">
                                No amount was charged from your account or card.
                            </p>
                            @endif

                            <p style="margin:0 0 24px; font-size:15px; color:#52525b; line-height:1.6;">
                                Your bag is still saved — simply try again whenever you're ready.
                            </p>

                            <!-- CTA -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:8px 0;">
                                        <a href="{{ $shoppingUrl }}" style="display:inline-block; background:#18181b; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; padding:14px 40px; border-radius:8px;">
                                            Retry Checkout
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0; font-size:13px; color:#a1a1aa; text-align:center; line-height:1.6;">
                                Still having trouble? Reply to this email and our team will help you out.<br>
                                <a href="{{ $shopUrl }}" style="color:#71717a; text-decoration:underline;">Browse the sale</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#fafafa; padding:20px 32px; text-align:center; border-top:1px solid #f0f0f0;">
                            <p style="margin:0; font-size:12px; color:#a1a1aa;">
                                ZYRA Lifestyle · <a href="https://zyralifestyle.in" style="color:#71717a; text-decoration:none;">zyralifestyle.in</a><br>
                                You're receiving this because you attempted a checkout on ZYRA Lifestyle.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>