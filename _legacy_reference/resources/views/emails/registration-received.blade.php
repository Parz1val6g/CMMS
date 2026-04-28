<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4f46e5;">{{ __('Registration Received') }}</h2>
        
        <p>{{ __('Hello') }} {{ $userName }},</p>
        
        <p>{{ __('Thank you for registering with us!') }}</p>
        
        <p>{{ __('Your registration has been received and is currently pending approval from our administrator.') }}</p>
        
        <p>{{ __('You will receive an email once your account has been approved. This typically happens within 24 hours.') }}</p>
        
        <p style="margin-top: 30px; color: #666;">
            {{ __('Regards') }},<br>
            {{ config('app.name') }}
        </p>
    </div>
</body>
</html>
