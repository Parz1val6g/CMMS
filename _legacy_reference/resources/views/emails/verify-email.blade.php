<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4f46e5;">{{ __('Verify Your Email Address') }}</h2>
        
        <p>{{ __('Hello') }} {{ $userName }},</p>
        
        <p>{{ __('Please click the button below to verify your email address.') }}</p>
        
        <div style="margin: 30px 0; text-align: center;">
            <a href="{{ $verificationUrl }}" style="background-color: #4f46e5; color: white; padding: 12px 30px; text-decoration: none; border-radius: 4px; display: inline-block;">
                {{ __('Verify Email') }}
            </a>
        </div>
        
        <p>{{ __('If you did not create an account, no further action is required.') }}</p>
        
        <p style="margin-top: 30px; color: #666;">
            {{ __('Regards') }},<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
