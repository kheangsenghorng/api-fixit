<h2>Owner Payout Paid</h2>

<p>Hello {{ $owner->user->name ?? 'Owner' }},</p>

<p>Your payout has been marked as paid.</p>

<p><strong>Total Amount:</strong> {{ number_format($totalAmount, 2) }}</p>
<p><strong>Transaction Reference:</strong> {{ $transactionReference }}</p>

<h3>Payment Details</h3>

<table border="1" cellpadding="8" cellspacing="0" width="100%">
    <thead>
        <tr>
            <th>Payout ID</th>
            <th>Payment ID</th>
            <th>User ID</th>
            <th>Booking ID</th>
            <th>Amount</th>
            <th>Method</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($payouts as $payout)
            @php
                $payment = $payout->split->payment ?? null;
            @endphp

            <tr>
                <td>{{ $payout->id }}</td>
                <td>{{ $payment->id ?? '-' }}</td>
                <td>{{ $payment->user_id ?? '-' }}</td>
                <td>{{ $payment->service_booking_id ?? '-' }}</td>
                <td>{{ number_format($payout->amount, 2) }}</td>
                <td>{{ $payout->method }}</td>
            </tr>
        @endforeach
    </tbody>
</table>

<p>Thank you.</p>