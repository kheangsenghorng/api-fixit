<div style="font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; max-width: 600px; margin: 0 auto; padding: 20px; color: #333; line-height: 1.6;">
    <h2 style="color: #1a1a1a; font-size: 24px; font-weight: 700;">Hello, {{ $user->name }}! 👋</h2>
    
    <p style="font-size: 16px;">We’re excited to see you’re ready to list a new service on our platform. You’re just <strong>one step away</strong> from going live.</p>
    
    <div style="background-color: #f8fafc; border-left: 4px solid #3b82f6; padding: 15px; margin: 20px 0;">
        <p style="margin: 0; font-weight: 600; color: #1e40af;">Account Verification Required</p>
        <p style="margin: 5px 0 0 0; font-size: 14px; color: #64748b;">To ensure the safety of our community, all service providers must verify their identity before publishing.</p>
    </div>

    <p>Please upload your verification documents to activate your owner account and start growing your business.</p>

    <div style="text-align: center; margin: 30px 0;">
        <a href="{{ url('/account/verify') }}" style="background-color: #3b82f6; color: white; padding: 12px 25px; text-decoration: none; border-radius: 6px; font-weight: bold; display: inline-block;">Verify My Account</a>
    </div>

    <p style="font-size: 14px; color: #94a3b8;">Thank you for choosing us,<br>The Team</p>
</div>