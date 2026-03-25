<!DOCTYPE html>
<html dir="{{ context('Email direction') }}" lang="{{ context('Email language') }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? settings('app_name') }}</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #333;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 0;
            background-color: #ffffff;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
            border-radius: 8px;
            overflow: hidden;
        }
        .header {
            text-align: center;
            padding: 30px 20px;
            border-bottom: 2px solid rgba(255, 255, 255, 0.2);
            background: linear-gradient(135deg, #e5a523 0%, #d4941f 100%);
            color: white;
            position: relative;
        }
        .header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="40" r="1.5" fill="rgba(255,255,255,0.1)"/><circle cx="40" cy="80" r="2.5" fill="rgba(255,255,255,0.1)"/></svg>');
            opacity: 0.3;
        }
        .header-content {
            position: relative;
            z-index: 1;
        }
        .header img {
            max-width: 120px;
            height: auto;
            margin-bottom: 15px;
            border-radius: 8px;
            background: white;
            padding: 8px;
        }
        .header h2 {
            margin: 0;
            font-size: 24px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .header p {
            margin: 10px 0 0 0;
            font-size: 14px;
            opacity: 0.95;
        }
        .content {
            padding: 40px 30px;
            background-color: #ffffff;
            color: #333;
            line-height: 1.8;
        }
        .content h1, .content h2, .content h3 {
            color: #2c3e50;
            margin-top: 0;
        }
        .content h2 {
            font-size: 22px;
            margin-bottom: 20px;
            border-bottom: 2px solid #e5a523;
            padding-bottom: 10px;
        }
        .content p {
            margin-bottom: 15px;
        }
        .content a {
            color: #e5a523;
            text-decoration: none;
        }
        .content a:hover {
            text-decoration: underline;
        }
        .footer {
            text-align: center;
            padding: 30px 20px;
            font-size: 0.9em;
            color: #666;
            border-top: 1px solid #e0e0e0;
            background: linear-gradient(to bottom, #f9f9f9 0%, #f5f5f5 100%);
        }
        .footer p {
            margin: 8px 0;
            line-height: 1.6;
        }
        .btn {
            display: inline-block;
            background: linear-gradient(135deg, #e5a523 0%, #d4941f 100%);
            color: #ffffff !important;
            text-decoration: none;
            padding: 14px 30px;
            border-radius: 6px;
            margin: 20px 0;
            font-weight: 600;
            box-shadow: 0 4px 6px rgba(229, 165, 35, 0.3);
            transition: all 0.3s ease;
        }
        .btn:hover {
            box-shadow: 0 6px 12px rgba(229, 165, 35, 0.4);
            transform: translateY(-2px);
        }
        .text-center {
            text-align: center;
        }
        .highlight {
            color: #e5a523;
            font-weight: bold;
        }
        .social-icons {
            margin-top: 20px;
            padding-top: 20px;
            border-top: 1px solid #e0e0e0;
        }
        .social-icons a {
            display: inline-block;
            margin: 0 8px;
            color: #666;
            text-decoration: none;
            font-size: 14px;
            transition: color 0.3s ease;
        }
        .social-icons a:hover {
            color: #e5a523;
        }
        @media only screen and (max-width: 600px) {
            .container {
                margin: 0;
                border-radius: 0;
            }
            .header {
                padding: 25px 15px;
            }
            .content {
                padding: 30px 20px;
            }
            .footer {
                padding: 25px 15px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <div class="header-content">
            @if(settings('logo'))
                <img src="{{ settings('logo') }}" alt="{{ settings('app_name') }}" class="logo">
            @endif
            <h2>{{ settings('app_name') }}</h2>
            @if(isset($subtitle))
                <p>{{ $subtitle }}</p>
            @endif
            </div>
        </div>
        
        <div class="content">
            {{ $slot }}
        </div>
        
        <div class="footer">
            <p>
                {{ settings('app_name') }}<br>
                {{ settings('address') }}<br>
                @if(settings('working_time'))
                    {{ settings('working_time') }}<br>
                @endif
                {{ emailTranslation('Phone:') }} {{ settings('phone', '+1 234 567 890') }}<br>
                {{ emailTranslation('Email:') }} {{ settings('email', 'contact@example.com') }}
            </p>
            
            <div class="social-icons">
                @if(settings('facebook'))
                    <a href="{{ settings('facebook') }}" target="_blank">{{ __('Facebook') }}</a>
                @endif
                @if(settings('instagram'))
                    <a href="{{ settings('instagram') }}" target="_blank">{{ __('Instagram') }}</a>
                @endif
                @if(settings('twitter'))
                    <a href="{{ settings('twitter') }}" target="_blank">{{ __('Twitter') }}</a>
                @endif
                @if(settings('linkedin'))
                    <a href="{{ settings('linkedin') }}" target="_blank">{{ __('LinkedIn') }}</a>
                @endif
                @if(settings('youtube'))
                    <a href="{{ settings('youtube') }}" target="_blank">{{ __('YouTube') }}</a>
                @endif
                @if(settings('whatsapp'))
                    <a href="{{ settings('whatsapp') }}" target="_blank">{{ __('WhatsApp') }}</a>
                @endif
            </div>
            
            <p>{{ emailTranslation('This is an automated email, please do not reply to this message.') }}</p>
            <p>
                &copy; {{ date('Y') }} {{ settings('app_name') }}. <br> {{ emailTranslation('All rights reserved.') }}
            </p>
        </div>
    </div>
</body>
</html> 