<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4f46e5;">{{ __('Registration Approved') }}</h2>
        
        <p>{{ __('Hello') }} {{ $userName }},</p>
        
        <p>{{ __('Great news! Your registration has been approved by an administrator.') }}</p>
        
        <p>{{ __('You can now login to your account using the credentials you provided during registration.') }}</p>
        
        <div style="margin: 30px 0; text-align: center;">
            <a href="{{ $loginUrl }}" style="background-color: #4f46e5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; display: inline-block;">
                {{ __('Login Now') }}
            </a>
        </div>
        
        <p>{{ __('If you have any questions, please contact our support team.') }}</p>
        
        <p style="margin-top: 30px; color: #666;">
            {{ __('Regards') }},<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
