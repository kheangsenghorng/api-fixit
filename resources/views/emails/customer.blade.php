<div style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; max-width: 600px; margin: 20px auto; border: 1px solid #e0e0e0; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
    <div style="background-color: #4f46e5; padding: 20px; color: #ffffff;">
        <h2 style="margin: 0; font-size: 20px; font-weight: 600;">Service Notification</h2>
    </div>
    
    <div style="padding: 30px; background-color: #ffffff;">
        <p style="color: #374151; font-size: 16px; line-height: 1.6; margin-bottom: 25px;">
            {{ $messageText }}
        </p>
        
        <div style="border-top: 1px solid #f3f4f6; padding-top: 20px; margin-top: 20px;">
            <p style="color: #6b7280; font-size: 14px; margin-bottom: 20px;">Please login to the admin dashboard to review the service details.</p>
            
            <a href="{{ url('/admin/dashboard') }}" style="display: inline-block; background-color: #4f46e5; color: white; padding: 12px 24px; text-decoration: none; border-radius: 6px; font-weight: 500; font-size: 14px;">
                Review Service →
            </a>
        </div>
    </div>
    
    <div style="background-color: #f9fafb; padding: 15px; text-align: center; color: #9ca3af; font-size: 12px;">
        This is an automated system message.
    </div>
</div>