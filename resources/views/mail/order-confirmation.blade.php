<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Confirmed – {{ $orderNumber }}</title>
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
                            <h1 style="margin:0 0 12px; font-size:22px; color:#18181b;">Thank you, {{ $customerName }}! 🎉</h1>
                            <p style="margin:0 0 6px; font-size:15px; color:#52525b; line-height:1.6;">
                                Your order <strong style="color:#18181b;">{{ $orderNumber }}</strong> has been placed successfully.
                            </p>
                            <p style="margin:0 0 24px; font-size:15px; color:#52525b; line-height:1.6;">
                                Payment: <strong style="color:#18181b;">{{ $paymentMethod }}</strong>
                            </p>

                            <!-- Items -->
                            @foreach($items as $item)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #f0f0f0; padding:12px 0;">
                                    <tr>
                                        <td width="72" valign="top" style="padding:12px 0;">
                                            <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" width="64" height="64" style="width:64px; height:64px; object-fit:cover; border-radius:8px; background:#f4f4f5;">
                                        </td>
                                        <td valign="middle" style="padding:12px 12px;">
                                            <div style="font-size:15px; font-weight:600; color:#18181b;">{{ $item->product_name }}</div>
                                            <div style="font-size:13px; color:#71717a; margin-top:2px;">
                                                {{ $item->size ?? '' }}{{ !empty($item->size) && !empty($item->color) ? ' · ' : '' }}{{ $item->color ?? '' }} × {{ $item->quantity }}
                                            </div>
                                        </td>
                                        <td valign="middle" align="right" style="padding:12px 0; font-size:15px; font-weight:600; color:#18181b; white-space:nowrap;">
                                            ₹{{ number_format((float) $item->total, 0) }}
                                        </td>
                                    </tr>
                                </table>
                            @endforeach

                            <!-- Totals -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:2px solid #18181b; margin-top:8px;">
                                <tr>
                                    <td style="padding:14px 0 4px; font-size:14px; color:#52525b;">Subtotal</td>
                                    <td align="right" style="padding:14px 0 4px; font-size:14px; color:#18181b;">₹{{ number_format($subtotal, 0) }}</td>
                                </tr>
                                @if($discount > 0)
                                <tr>
                                    <td style="padding:4px 0; font-size:14px; color:#52525b;">Coupon Discount</td>
                                    <td align="right" style="padding:4px 0; font-size:14px; color:#16a34a;">-₹{{ number_format($discount, 0) }}</td>
                                </tr>
                                @endif
                                <tr>
                                    <td style="padding:4px 0; font-size:14px; color:#52525b;">GST &amp; Shipping</td>
                                    <td align="right" style="padding:4px 0; font-size:14px; color:#18181b;">₹{{ number_format($tax, 0) }}</td>
                                </tr>
                                <tr>
                                    <td style="padding:10px 0 4px; font-size:16px; font-weight:700; color:#18181b;">Total</td>
                                    <td align="right" style="padding:10px 0 4px; font-size:18px; font-weight:800; color:#18181b;">₹{{ number_format($total, 0) }}</td>
                                </tr>
                            </table>

                            <!-- Delivery Address -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fafafa; border:1px solid #f0f0f0; border-radius:10px; padding:16px 20px; margin-top:20px;">
                                <tr>
                                    <td style="font-size:12px; color:#a1a1aa; text-transform:uppercase; letter-spacing:1px; padding-bottom:4px;">Delivering To</td>
                                </tr>
                                <tr>
                                    <td style="font-size:14px; color:#18181b; line-height:1.6;">{{ $shippingAddress }}</td>
                                </tr>
                            </table>

                            <!-- CTA -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                <tr>
                                    <td align="center" style="padding:24px 0 0;">
                                        <a href="{{ $trackUrl }}" style="display:inline-block; background:#18181b; color:#ffffff; text-decoration:none; font-size:15px; font-weight:700; padding:14px 40px; border-radius:8px;">
                                            Track Your Order
                                        </a>
                                    </td>
                                </tr>
                            </table>

                            <p style="margin:20px 0 0; font-size:13px; color:#a1a1aa; text-align:center; line-height:1.6;">
                                For any questions, reply to this email or contact our care team anytime. 💛
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#fafafa; padding:20px 32px; text-align:center; border-top:1px solid #f0f0f0;">
                            <p style="margin:0; font-size:12px; color:#a1a1aa;">
                                ZYRA Lifestyle · <a href="https://zyralifestyle.in" style="color:#71717a; text-decoration:none;">zyralifestyle.in</a> · +91 9884125555
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>