<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Welcome to ZYRA Lifestyle</title>
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
                            <h1 style="margin:0 0 12px; font-size:22px; color:#18181b;">Namaste, {{ $name }}! 💛</h1>
                            <p style="margin:0 0 16px; font-size:15px; color:#52525b; line-height:1.6;">
                                Your ZYRA Lifestyle account has been created successfully. We're so happy to have you!
                            </p>
                            <p style="margin:0 0 24px; font-size:15px; color:#52525b; line-height:1.6;">
                                You can now shop our latest kurtis, tops, leggings, maxi dresses, and nightwear with
                                <strong>free pan-India express delivery</strong> — plus track your orders and save your favourites.
                            </p>

                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fafafa; border:1px solid #f0f0f0; border-radius:10px; padding:16px 20px; margin-bottom:24px;">
                                <tr>
                                    <td style="padding:4px 8px 4px 0; font-size:13px; color:#71717a; white-space:nowrap;">Account</td>
                                    <td style="padding:4px 0; font-size:14px; color:#18181b; font-weight:600;">{{ $user->email }}</td>
                                </tr>
                                @if(!empty($user->phone_number))
                                <tr>
                                    <td style="padding:4px 8px 4px 0; font-size:13px; color:#71717a; white-space:nowrap;">Phone</td>
                                    <td style="padding:4px 0; font-size:14px; color:#18181b;">+91 {{ $user->phone_number }}</td>
                                </tr>
                                @endif
                            </table>

                            <!-- CTA -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:8px 0;">
                                        <a href="{{ $shopUrl }}" style="display:inline-block; background:#18181b; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; padding:14px 40px; border-radius:8px;">
                                            Start Shopping
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0; font-size:13px; color:#a1a1aa; text-align:center; line-height:1.6;">
                                Psst… use code <strong style="color:#71717a;">WELCOME10</strong> at checkout for 10% off your first order.<br>
                                <a href="{{ url('/shop') }}" style="color:#71717a; text-decoration:underline;">Explore the collection</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#fafafa; padding:20px 32px; text-align:center; border-top:1px solid #f0f0f0;">
                            <p style="margin:0; font-size:12px; color:#a1a1aa;">
                                ZYRA Lifestyle · <a href="https://zyralifestyle.in" style="color:#71717a; text-decoration:none;">zyralifestyle.in</a><br>
                                You're receiving this because you created an account on ZYRA Lifestyle.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>