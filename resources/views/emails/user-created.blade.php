<div style="background-color: #f4f7ff; padding: 50px 0; font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; color: #4a5568; line-height: 1.6;">
    <div style="max-width: 600px; margin: 0 auto; background-color: #ffffff; border-radius: 8px; overflow: hidden; box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);">
        
        <!-- Header -->
        <div style="background-color: #4f46e5; padding: 20px; text-align: center;">
            <h1 style="color: #ffffff; margin: 0; font-size: 24px;">Welcome Aboard!</h1>
        </div>

        <!-- Body -->
        <div style="padding: 40px;">
            <h2 style="color: #1a202c; margin-top: 0;">Hello, {{ $user->name }}!</h2>
            <p>Your account has been created successfully. You can now access your dashboard using the credentials below:</p>
            
            <!-- Credentials Box -->
            <div style="background-color: #f8fafc; border: 1px solid #e2e8f0; padding: 20px; border-radius: 6px; margin: 25px 0;">
                <p style="margin: 0;"><strong>Email:</strong> <span style="color: #4f46e5;">{{ $user->email }}</span></p>
                
                <!-- Added Phone Number -->
                <p style="margin: 10px 0 0 0;"><strong>Phone:</strong> <span style="color: #4f46e5;">{{ $user->phone ?? 'N/A' }}</span></p>
                
                <p style="margin: 10px 0 0 0;"><strong>Password:</strong> <span style="color: #4f46e5;">{{ $plainPassword }}</span></p>
            </div>

            <div style="text-align: center; margin-top: 30px;">
                <a href="{{ config('app.frontend_url') }}/auth/login"
                   style="background-color: #4f46e5; color: white; padding: 12px 25px; text-decoration: none; border-radius: 5px; font-weight: bold; display: inline-block;">
                    Login to Your Account
                </a>
            </div>

            <p style="font-size: 14px; color: #718096; margin-top: 30px; border-top: 1px solid #e2e8f0; padding-top: 20px;">
                <strong>Note:</strong> For security reasons, please change your password immediately after your first login.
            </p>
        </div>

        <!-- Footer -->
        <div style="background-color: #f7fafc; padding: 20px; text-align: center; font-size: 12px; color: #a0aec0;">
            © {{ date('Y') }} {{ config('app.name') }}. All rights reserved.
        </div>
    </div>
</div>