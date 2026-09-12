<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Your ZYRA cart is waiting</title>
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
                            <h1 style="margin:0 0 12px; font-size:22px; color:#18181b;">Your cart is waiting for you 💕</h1>
                            <p style="margin:0 0 6px; font-size:15px; color:#52525b; line-height:1.6;">
                                Hi there, we saved {{ count($items) }} item(s) in your ZYRA bag so nothing gets lost.
                            </p>
                            <p style="margin:0 0 24px; font-size:15px; color:#52525b; line-height:1.6;">
                                Complete your checkout and we'll ship them out right away.
                            </p>

                            <!-- Items -->
                            @foreach($items as $index => $item)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #f0f0f0; padding:12px 0;">
                                    <tr>
                                        <td width="72" valign="top" style="padding:12px 0;">
                                            <img src="{{ $item['image'] ?? '' }}" alt="{{ $item['name'] ?? 'Item' }}" width="64" height="64" style="width:64px; height:64px; object-fit:cover; border-radius:8px; background:#f4f4f5;">
                                        </td>
                                        <td valign="middle" style="padding:12px 12px;">
                                            <div style="font-size:15px; font-weight:600; color:#18181b;">{{ $item['name'] ?? 'Fashion Item' }}</div>
                                            <div style="font-size:13px; color:#71717a; margin-top:2px;">
                                                {{ $item['size'] ?? '' }}{{ !empty($item['size']) && !empty($item['color']) ? ' · ' : '' }}{{ $item['color'] ?? '' }} × {{ $item['quantity'] ?? 1 }}
                                            </div>
                                        </td>
                                        <td valign="middle" align="right" style="padding:12px 0; font-size:15px; font-weight:600; color:#18181b; white-space:nowrap;">
                                            ₹{{ number_format(((float)($item['price'] ?? 0)) * ((int)($item['quantity'] ?? 1)), 0) }}
                                        </td>
                                    </tr>
                                </table>
                            @endforeach

                            <!-- Subtotal -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:2px solid #18181b; margin-top:8px;">
                                <tr>
                                    <td style="padding:16px 0 4px; font-size:14px; color:#52525b;">Bag Subtotal</td>
                                    <td align="right" style="padding:16px 0 4px; font-size:18px; font-weight:800; color:#18181b;">₹{{ number_format($subtotal, 0) }}</td>
                                </tr>
                                <tr>
                                    <td colspan="2" style="padding:0 0 24px; font-size:13px; color:#a1a1aa;">
                                        GST &amp; shipping calculated at checkout.
                                    </td>
                                </tr>
                            </table>

                            <!-- CTA -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:8px 0;">
                                        <a href="{{ $checkoutUrl }}" style="display:inline-block; background:#18181b; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; padding:14px 40px; border-radius:8px;">
                                            Complete Your Order
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:20px 0 0; font-size:13px; color:#a1a1aa; text-align:center; line-height:1.6;">
                                Loved items sell out fast — don't let these get away!<br>
                                <a href="{{ url('/shop') }}" style="color:#71717a; text-decoration:underline;">Continue shopping</a>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#fafafa; padding:20px 32px; text-align:center; border-top:1px solid #f0f0f0;">
                            <p style="margin:0; font-size:12px; color:#a1a1aa;">
                                ZYRA Lifestyle · <a href="https://zyralifestyle.in" style="color:#71717a; text-decoration:none;">zyralifestyle.in</a><br>
                                You're receiving this because you left items in your shopping bag.
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>