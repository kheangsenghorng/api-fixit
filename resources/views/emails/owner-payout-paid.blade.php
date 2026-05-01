<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payout Successful</title>
    <style>
        /* Base styles for email clients */
        body { font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; background-color: #f3f4f6; color: #1f2937; margin: 0; padding: 0; line-height: 1.6; }
        .container { max-width: 600px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1); }
        .header { background-color: #059669; background-image: linear-gradient(to right, #059669, #10b981); padding: 40px 20px; text-align: center; color: white; }
        .content { padding: 30px; }
        
        /* Summary Box */
        .summary-card { background-color: #f9fafb; border: 1px solid #e5e7eb; border-radius: 8px; padding: 20px; margin-bottom: 30px; display: table; width: 100%; box-sizing: border-box; }
        .summary-col { display: table-cell; width: 50%; vertical-align: top; }
        .label { font-size: 11px; text-transform: uppercase; letter-spacing: 0.05em; color: #6b7280; font-weight: 700; margin-bottom: 4px; }
        .value { font-size: 18px; font-weight: 800; color: #111827; }
        .amount-text { color: #059669; }

        /* Table Styles */
        .table-title { font-size: 14px; font-weight: 700; color: #374151; margin-bottom: 12px; padding-left: 4px; border-left: 4px solid #10b981; }
        table { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        th { text-align: left; font-size: 12px; color: #6b7280; padding: 12px 8px; border-bottom: 2px solid #f3f4f6; }
        td { padding: 16px 8px; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        
        /* Typography */
        .service-title { font-size: 14px; font-weight: 600; color: #111827; margin: 0; }
        .booking-ref { font-size: 12px; color: #6b7280; margin: 0; }
        .method-badge { font-size: 11px; background: #eff6ff; color: #1d4ed8; padding: 2px 8px; border-radius: 4px; text-transform: uppercase; }
        
        .footer { background: #f9fafb; padding: 20px; text-align: center; font-size: 12px; color: #9ca3af; }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div style="font-size: 14px; text-transform: uppercase; letter-spacing: 2px; opacity: 0.9; margin-bottom: 8px;">Payment Sent</div>
            <h1 style="margin: 0; font-size: 28px; font-weight: 800;">Payout Confirmed</h1>
        </div>

        <div class="content">
            <p style="margin-top: 0; font-size: 16px;">Hello <strong>{{ $owner->user->name ?? 'Owner' }}</strong>,</p>
            <p style="color: #4b5563;">Great news! We have processed your payout. The funds have been transferred to your account via your selected payment method.</p>

            <!-- Summary Section -->
            <div class="summary-card">
                <div class="summary-col">
                    <div class="label">Total Paid</div>
                    <div class="value amount-text">${{ number_format($totalAmount, 2) }}</div>
                </div>
                <div class="summary-col">
                    <div class="label">Reference ID</div>
                    <div class="value" style="font-size: 14px;">#{{ $transactionReference }}</div>
                </div>
            </div>

            <div class="table-title">Breakdown of Earnings</div>
            <table>
                <thead>
                    <tr>
                        <th>Service / Booking</th>
                        <th>Method</th>
                        <th style="text-align: right;">Amount</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($payouts as $payout)
                        @php
                            $payment = $payout->split->payment ?? null;
                            $booking = $payment->serviceBooking ?? null;
                            $service = $booking->service ?? null;
                            
                            // Human-readable method names
                            $methodName = str_replace('_', ' ', $payout->method);
                        @endphp
                        <tr>
                            <td>
                                <p class="service-title">{{ $service->title ?? 'Service Payment' }}</p>
                                <p class="booking-ref">Booking #{{ $payment->service_booking_id ?? $payout->id }}</p>
                            </td>
                            <td>
                                <span class="method-badge">{{ $methodName }}</span>
                            </td>
                            <td style="text-align: right; font-weight: 700; color: #111827;">
                                ${{ number_format($payout->amount, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div style="margin-top: 30px; border-top: 1px solid #f3f4f6; padding-top: 20px;">
                <p style="font-size: 14px; color: #6b7280;">
                    <strong>Note:</strong> Depending on your bank or payment provider, it may take 1-3 business days for the funds to reflect in your account.
                </p>
            </div>
        </div>

        <div class="footer">
            <p style="margin-bottom: 5px;">&copy; {{ date('Y') }} {{ config('app.name') }}. All rights reserved.</p>
            <p>If you have any questions, please reply to this email or contact support.</p>
        </div>
    </div>
</body>
</html>