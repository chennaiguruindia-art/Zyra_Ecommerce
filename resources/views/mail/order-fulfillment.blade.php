<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Update – {{ $orderNumber }}</title>
</head>
<body style="margin:0; padding:0; background-color:#f6f6f6; font-family:'Segoe UI', Helvetica, Arial, sans-serif;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f6f6f6; padding:24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="600" cellpadding="0" cellspacing="0" style="max-width:600px; width:100%; background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 2px 12px rgba(0,0,0,0.06);">
                    <!-- Header -->
                    <tr>
                        <td style="background:#7D1D32; padding:26px 32px; text-align:center;">
                            <span style="font-size:26px; font-weight:800; letter-spacing:3px; color:#ffffff; font-family:Georgia, serif;">ZYRA</span>
                            <span style="display:block; font-size:11px; color:#fce7eb; letter-spacing:4px; margin-top:4px;">L I F E S T Y L E</span>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:32px 32px;">
                            <h1 style="margin:0 0 10px; font-size:22px; color:#18181b;">Hello, {{ $customerName }}! 👋</h1>

                            @if(strtolower($status) === 'shipped')
                                <p style="margin:0 0 18px; font-size:15px; color:#52525b; line-height:1.6;">
                                    Great news! Your order <strong style="color:#18181b;">{{ $orderNumber }}</strong> has been dispatched and is on its way to you.
                                </p>
                            @elseif(strtolower($status) === 'delivered')
                                <p style="margin:0 0 18px; font-size:15px; color:#52525b; line-height:1.6;">
                                    Your order <strong style="color:#18181b;">{{ $orderNumber }}</strong> has been delivered. We hope you love your new ZYRA pieces! ✨
                                </p>
                            @elseif(strtolower($status) === 'cancelled')
                                <p style="margin:0 0 18px; font-size:15px; color:#52525b; line-height:1.6;">
                                    Your order <strong style="color:#18181b;">{{ $orderNumber }}</strong> has been cancelled. We're sorry for the inconvenience — if you have already made a payment, it will be refunded shortly. 🙏
                                </p>
                            @else
                                <p style="margin:0 0 18px; font-size:15px; color:#52525b; line-height:1.6;">
                                    There is an update on your order <strong style="color:#18181b;">{{ $orderNumber }}</strong>.
                                </p>
                            @endif

                            <!-- Status & Tracking Box -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fdf6f7; border:1.5px solid #f2d1d7; border-radius:10px; padding:20px; margin:16px 0 24px;">
                                <tr>
                                    <td>
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                                            <tr>
                                                <td style="font-size:12px; color:#7D1D32; text-transform:uppercase; font-weight:700; letter-spacing:1px; padding-bottom:6px;">
                                                    Fulfillment Status
                                                </td>
                                                <td align="right" style="padding-bottom:6px;">
                                                    <span style="display:inline-block; background:#7D1D32; color:#ffffff; font-size:12px; font-weight:700; padding:4px 12px; border-radius:999px; text-transform:uppercase; letter-spacing:0.5px;">
                                                        {{ $status }}
                                                    </span>
                                                </td>
                                            </tr>

                                            @if(!empty($awbCode))
                                            <tr>
                                                <td colspan="2" style="border-top:1px dashed #f2d1d7; padding-top:12px; margin-top:8px;">
                                                    <div style="font-size:12px; color:#71717a; text-transform:uppercase; letter-spacing:0.8px;">Courier Partner</div>
                                                    <div style="font-size:15px; font-weight:700; color:#18181b; margin-top:2px;">{{ $courierName }}</div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td colspan="2" style="padding-top:10px;">
                                                    <div style="font-size:12px; color:#71717a; text-transform:uppercase; letter-spacing:0.8px;">Tracking / AWB Number</div>
                                                    <div style="font-size:17px; font-weight:800; color:#7D1D32; letter-spacing:1px; margin-top:2px;">{{ $awbCode }}</div>
                                                </td>
                                            </tr>
                                            @endif
                                        </table>

                                        @if(!empty($trackingUrl) && strtolower($status) !== 'cancelled')
                                        <div style="text-align:center; padding-top:18px;">
                                            <a href="{{ $trackingUrl }}" style="display:inline-block; background:#7D1D32; color:#ffffff; text-decoration:none; font-size:14px; font-weight:700; padding:12px 28px; border-radius:8px; letter-spacing:0.4px;">
                                                Track Your Package &rarr;
                                            </a>
                                        </div>
                                        @endif
                                    </td>
                                </tr>
                            </table>

                            <!-- Ordered Items -->
                            <h3 style="font-size:15px; font-weight:700; color:#18181b; margin:20px 0 10px; text-transform:uppercase; letter-spacing:0.5px;">Items in this Shipment</h3>
                            @foreach($items as $item)
                                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-top:1px solid #f0f0f0; padding:12px 0;">
                                    <tr>
                                        <td width="64" valign="top" style="padding:10px 0;">
                                            <img src="{{ $item->image_url }}" alt="{{ $item->product_name }}" width="56" height="56" style="width:56px; height:56px; object-fit:cover; border-radius:8px; background:#f4f4f5; display:block;">
                                        </td>
                                        <td valign="middle" style="padding:10px 12px;">
                                            <div style="font-size:14px; font-weight:600; color:#18181b;">{{ $item->product_name }}</div>
                                            <div style="font-size:12px; color:#71717a; margin-top:2px;">
                                                Size: {{ $item->size ?? 'M' }}{{ !empty($item->color) ? ' · Color: ' . $item->color : '' }}{{ $item->dupatta ? ' · ' . $item->dupatta : '' }} × {{ $item->quantity }}
                                            </div>
                                        </td>
                                        <td valign="middle" align="right" style="padding:10px 0; font-size:14px; font-weight:700; color:#18181b; white-space:nowrap;">
                                            ₹{{ number_format((float) $item->total, 0) }}
                                        </td>
                                    </tr>
                                </table>
                            @endforeach

                            <!-- Delivery Address -->
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background:#fafafa; border:1px solid #f0f0f0; border-radius:10px; padding:14px 18px; margin-top:20px;">
                                <tr>
                                    <td style="font-size:11px; color:#a1a1aa; text-transform:uppercase; letter-spacing:1px; padding-bottom:4px; font-weight:700;">Delivery Address</td>
                                </tr>
                                <tr>
                                    <td style="font-size:13px; color:#18181b; line-height:1.5;">{{ $shippingAddress }}</td>
                                </tr>
                            </table>

                            <p style="margin:24px 0 0; font-size:13px; color:#71717a; text-align:center; line-height:1.6;">
                                Need help with your order? Reach out to us at <a href="mailto:order@shopwithzyra.in" style="color:#7D1D32; font-weight:600; text-decoration:none;">order@shopwithzyra.in</a> or +91 9884875555.
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background:#fafafa; padding:20px 32px; text-align:center; border-top:1px solid #f0f0f0;">
                            <p style="margin:0; font-size:12px; color:#a1a1aa;">
                                ZYRA Lifestyle · <a href="https://zyralifestyle.in" style="color:#71717a; text-decoration:none;">zyralifestyle.in</a>
                            </p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
