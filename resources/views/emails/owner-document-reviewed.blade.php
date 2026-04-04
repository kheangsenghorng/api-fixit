<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Document Review Status</title>
</head>
<body style="margin:0; padding:0; background-color:#f1f5f9; font-family:Arial, Helvetica, sans-serif;">

    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9; padding:40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:16px; overflow:hidden; box-shadow:0 8px 30px rgba(0,0,0,0.08);">

                    <!-- Header -->
                    <tr>
                        <td style="background:{{ $ownerDocument->status === 'approved' ? '#16a34a' : '#dc2626' }}; padding:28px; text-align:center;">
                            <h1 style="margin:0; color:#ffffff; font-size:24px; font-weight:700;">
                                {{ $ownerDocument->status === 'approved' ? 'Document Approved' : 'Document Rejected' }}
                            </h1>
                        </td>
                    </tr>

                    <!-- Body -->
                    <tr>
                        <td style="padding:40px 32px;">
                            <p style="margin:0 0 20px; font-size:16px; color:#0f172a;">
                                Hello <strong>{{ $user->name }}</strong>,
                            </p>

                            <p style="margin:0 0 24px; font-size:15px; line-height:1.8; color:#475569;">
                                {{ $messageText }}
                            </p>

                            <!-- Info Card -->
                            <table width="100%" cellpadding="0" cellspacing="0" style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; margin-bottom:24px;">
                                <tr>
                                    <td style="padding:20px;">
                                        <p style="margin:0 0 12px; font-size:14px; color:#64748b;">
                                            <strong style="color:#0f172a;">Document Type:</strong>
                                            {{ $ownerDocument->document_type }}
                                        </p>

                                        <p style="margin:0; font-size:14px; color:#64748b;">
                                            <strong style="color:#0f172a;">Status:</strong>
                                            <span style="
                                                display:inline-block;
                                                padding:6px 12px;
                                                border-radius:999px;
                                                font-size:12px;
                                                font-weight:700;
                                                color:#ffffff;
                                                background:{{ $ownerDocument->status === 'approved' ? '#16a34a' : '#dc2626' }};
                                            ">
                                                {{ ucfirst($ownerDocument->status) }}
                                            </span>
                                        </p>
                                    </td>
                                </tr>
                            </table>

                            @if($ownerDocument->status === 'rejected' && $ownerDocument->rejection_reason)
                                <table width="100%" cellpadding="0" cellspacing="0" style="background:#fef2f2; border:1px solid #fecaca; border-radius:12px; margin-bottom:24px;">
                                    <tr>
                                        <td style="padding:20px;">
                                            <p style="margin:0 0 8px; font-size:14px; font-weight:700; color:#b91c1c;">
                                                Rejection Reason
                                            </p>
                                            <p style="margin:0; font-size:14px; line-height:1.7; color:#7f1d1d;">
                                                {{ $ownerDocument->rejection_reason }}
                                            </p>
                                        </td>
                                    </tr>
                                </table>
                            @endif

                            <p style="margin:0; font-size:14px; line-height:1.8; color:#475569;">
                                Thank you,<br>
                                <strong style="color:#0f172a;">Fixit Team</strong>
                            </p>
                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="padding:20px; background:#f8fafc; text-align:center; border-top:1px solid #e2e8f0;">
                            <p style="margin:0; font-size:12px; color:#94a3b8;">
                                This is an automated email. Please do not reply to this message.
                            </p>
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>

</body>
</html>