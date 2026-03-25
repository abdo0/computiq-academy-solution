@component('emails.layouts.main', ['title' => emailTranslation('Email Verification'), 'subtitle' => emailTranslation('Verify Your Email Address')])
    <h2>{{ emailTranslation('Thank you for registering!') }}</h2>
    
    <p>{{ emailTranslation('Hello') }} {{ $name ?? '' }},</p>
    
    <p>{{ emailTranslation('Please click the button below to verify your email address.') }}</p>
    
    <div class="text-center">
        <a href="{{ $verificationUrl }}" class="btn">{{ emailTranslation('Verify Email Address') }}</a>
    </div>
    
    <p style="margin-top: 30px;">{{ emailTranslation('If you did not create an account, no further action is required.') }}</p>
    
    <p style="margin-top: 25px; font-size: 0.9em; color: #777;">
        {{ emailTranslation("If you're having trouble clicking the button, copy and paste the URL below into your web browser:") }}
        <br>
        <a href="{{ $verificationUrl }}" style="word-break: break-all;">{{ $verificationUrl }}</a>
    </p>
@endcomponent 