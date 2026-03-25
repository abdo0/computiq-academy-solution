@component('emails.layouts.main', ['title' => emailTranslation('Email Verification Code'), 'subtitle' => emailTranslation('Your OTP Code')])
    <h2>{{ emailTranslation('Email Verification Code') }}</h2>
    
    <p>{{ emailTranslation('Hello') }} {{ $name ?? '' }},</p>
    
    <p>{{ emailTranslation('Your email verification code is:') }}</p>
    
    <div class="text-center" style="margin: 30px 0;">
        <div style="display: inline-block; background: #f3f4f6; border: 2px solid #3b82f6; border-radius: 8px; padding: 20px 40px; font-size: 32px; font-weight: bold; letter-spacing: 8px; color: #3b82f6;">
            {{ $code }}
        </div>
    </div>
    
    <p>{{ emailTranslation('This code will expire in') }} {{ $expiryMinutes ?? 10 }} {{ emailTranslation('minutes') }}.</p>
    
    <p style="margin-top: 30px; font-size: 0.9em; color: #777;">
        {{ emailTranslation('If you did not request this code, please ignore this email.') }}
    </p>
@endcomponent

