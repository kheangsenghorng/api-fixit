<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Upload Verification Document</title>
</head>
<body style="margin:0; padding:0; background-color:#f8fafc; font-family:Arial, Helvetica, sans-serif;">
    <table width="100%" cellpadding="0" cellspacing="0" style="background-color:#f8fafc; padding:40px 0;">
        <tr>
            <td align="center">
                <table width="600" cellpadding="0" cellspacing="0" style="background:#ffffff; border-radius:12px; overflow:hidden; box-shadow:0 4px 20px rgba(0,0,0,0.05);">
                    
                    <tr>
                        <td style="background:#4f46e5; padding:24px; text-align:center; color:#ffffff; font-size:22px; font-weight:bold;">
                            Upload Verification Document
                        </td>
                    </tr>

                    <tr>
                        <td style="padding:32px; color:#334155; font-size:15px; line-height:1.8;">
                            <p style="margin:0 0 16px;">Dear {{ $user->name ?? 'Owner' }},</p>

                            <p style="margin:0 0 16px;">
                                {{ $messageText }}
                            </p>

                            <p style="margin:0 0 16px;">
                                Please log in to your account and upload the required verification documents as soon as possible.
                            </p>

                            <p style="margin:0;">
                                Thank you,<br>
                                <strong>Fixit Team</strong>
                            </p>
                        </td>
                    </tr>

                    <tr>
                        <td style="background:#f1f5f9; padding:16px; text-align:center; font-size:12px; color:#64748b;">
                            This is an automated email. Please do not reply directly to this message.
                        </td>
                    </tr>

                </table>
            </td>
        </tr>
    </table>
</body>
</html>