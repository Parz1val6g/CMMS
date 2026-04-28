<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4f46e5;">{{ __('Reset Your Password') }}</h2>
        
        <p>{{ __('Hello') }} {{ $userName }},</p>
        
        <p>{{ __('You are receiving this email because we received a password reset request for your account.') }}</p>
        
        <div style="margin: 30px 0; text-align: center;">
            <a href="{{ $resetUrl }}" style="background-color: #4f46e5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; display: inline-block;">
                {{ __('Reset Password') }}
            </a>
        </div>
        
        <p><small>{{ __('This password reset link will expire in') }} {{ $expiryHours }} {{ __('hour(s).') }}</small></p>
        
        <p>{{ __('If you did not request a password reset, no further action is required.') }}</p>
        
        <p style="margin-top: 30px; color: #666;">
            {{ __('Regards') }},<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
