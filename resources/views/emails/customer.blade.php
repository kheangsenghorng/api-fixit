<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Security Protocol: Verification Code</title>
</head>
<body style="margin: 0; padding: 0; background-color: #fdfdfd; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; -webkit-font-smoothing: antialiased;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #fdfdfd; padding: 60px 20px;">
        <tr>
            <td align="center">
                <!-- Main Vault Container -->
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 500px; background-color: #ffffff; border-radius: 32px; overflow: hidden; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08); border: 1px solid #e5e7eb;">
                    
                    <!-- Top Security Bar -->
                    <tr>
                        <td style="padding: 40px 40px 0 40px; text-align: center;">
                            <div style="margin-bottom: 32px;">
                                <div style="display: inline-block; background-color: #6366f1; padding: 16px; border-radius: 20px; box-shadow: 0 10px 15px -3px rgba(99, 102, 241, 0.3);">
                                    <svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="#ffffff" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path>
                                        <circle cx="12" cy="11" r="3" stroke="white" fill="none"></circle>
                                        <path d="M12 14v4" stroke="white"></path>
                                    </svg>
                                </div>
                            </div>
                            
                            <p style="margin: 0; text-transform: uppercase; letter-spacing: 0.2em; font-size: 10px; font-weight: 900; color: #6366f1; margin-bottom: 12px;">
                                Authentication Protocol
                            </p>
                            <h1 style="margin: 0; color: #0f172a; font-size: 32px; font-weight: 900; letter-spacing: -0.04em; line-height: 1;">
                                Verify Identity
                            </h1>
                            <p style="margin: 20px 0 0 0; color: #64748b; font-size: 16px; line-height: 1.6; font-weight: 500;">
                                A request has been made to authorize a transaction on <strong style="color: #0f172a;">Fixit</strong>. Please use the secure token below.
                            </p>
                        </td>
                    </tr>

                    <!-- OTP Token Box -->
                    <tr>
                        <td style="padding: 40px; text-align: center;">
                            <div style="background-color: #0f172a; border-radius: 24px; padding: 32px; border: 1px solid #1e293b; box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);">
                                <p style="margin: 0 0 12px 0; font-size: 10px; font-weight: 900; color: #94a3b8; uppercase; letter-spacing: 0.3em;">
                                    SECURE TOKEN
                                </p>
                                <div style="font-family: 'SF Mono', 'Fira Code', monospace; font-size: 48px; font-weight: 800; color: #ffffff; letter-spacing: 12px; text-indent: 12px;">
                                    {{ $otp }}
                                </div>
                            </div>
                            <div style="margin-top: 24px; display: inline-flex; align-items: center; background-color: #fef2f2; padding: 8px 16px; border-radius: 12px; border: 1px solid #fee2e2;">
                                <span style="font-size: 12px; color: #ef4444; font-weight: 700;">
                                    Expires in 5 minutes
                                </span>
                            </div>
                        </td>
                    </tr>

                    <!-- Instructions -->
                    <tr>
                        <td style="padding: 0 40px 40px 40px; text-align: center;">
                            <p style="margin: 0; font-size: 14px; color: #94a3b8; line-height: 1.5; font-weight: 500;">
                                Tip: Never share this code with anyone. Our security team will never ask for this token via phone or chat.
                            </p>
                        </td>
                    </tr>

                    <!-- Security Footer -->
                    <tr>
                        <td style="background-color: #f8fafc; padding: 32px; text-align: center; border-top: 1px solid #f1f5f9;">
                            <p style="margin: 0; font-size: 12px; color: #94a3b8; line-height: 1.6; font-weight: 600;">
                                This is an automated security notification.<br>
                                If you did not initiate this request, please contact our <a href="#" style="color: #6366f1; text-decoration: none;">Security Response Team</a> immediately.
                            </p>
                        </td>
                    </tr>
                </table>

                <!-- Company Footer -->
                <table width="100%" border="0" cellspacing="0" cellpadding="0" style="max-width: 500px; margin-top: 32px;">
                    <tr>
                        <td align="center">
                            <p style="margin: 0; font-size: 12px; color: #cbd5e1; font-weight: 700; letter-spacing: 0.05em; text-transform: uppercase;">
                                &copy; {{ date('Y') }} FIXIT COMPLIANCE ENGINE
                            </p>
                            <p style="margin: 8px 0 0 0; font-size: 11px; color: #94a3b8;">
                                123 Security Way, San Francisco, CA 94103
                            </p>
                        </td>
                    </tr>
                </table>

            </td>
        </tr>
    </table>
</body>
</html>