<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }}">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        {{ __('Resignation Letter') }}
    </title>

    <style>
        @import url('https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700&display=swap');

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Tajawal', 'DejaVu Sans', sans-serif;
            direction: {{ app()->getLocale() == 'ar' ? 'rtl' : 'ltr' }};
            text-align: {{ app()->getLocale() == 'ar' ? 'right' : 'left' }};
            color: #2c3e50;
            background: white;
            line-height: 1.4;
            font-size: 14px;
        }

        .document {
            max-width: 210mm;
            min-height: 297mm;
            margin: 0 auto;
            background: white;
            padding: 0;
            page-break-after: avoid;
        }

        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 20px 25px;
            text-align: center;
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
        }

        .company-name {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 8px;
            position: relative;
            z-index: 1;
        }

        .company-details {
            font-size: 12px;
            opacity: 0.9;
            position: relative;
            z-index: 1;
        }

        .document-title {
            background: #34495e;
            color: white;
            padding: 15px 25px;
            text-align: center;
            font-size: 18px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .content {
            padding: 20px 25px;
            flex: 1;
        }

        .info-section {
            background: #f8f9fa;
            border: 1px solid #e9ecef;
            border-radius: 6px;
            padding: 15px;
            margin-bottom: 15px;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 10px;
            margin-bottom: 0;
        }

        .info-item {
            display: flex;
            align-items: center;
            padding: 8px 12px;
            background: white;
            border-radius: 4px;
            border-left: 3px solid #667eea;
            box-shadow: 0 1px 2px rgba(0,0,0,0.05);
            font-size: 13px;
        }

        .info-label {
            font-weight: 600;
            color: #34495e;
            margin-{{ app()->getLocale() == 'ar' ? 'left' : 'right' }}: 8px;
            min-width: 100px;
            font-size: 12px;
        }

        .info-value {
            color: #2c3e50;
            font-weight: 500;
            font-size: 13px;
        }

        .letter-content {
            background: white;
            padding: 20px;
            border-radius: 6px;
            border: 1px solid #e9ecef;
            margin: 15px 0;
            position: relative;
        }

        .letter-content::before {
            content: '';
            position: absolute;
            top: 0;
        {{ app()->getLocale() == 'ar' ? 'right' : 'left' }}: 0;
            width: 3px;
            height: 100%;
            background: linear-gradient(to bottom, #667eea, #764ba2);
            border-radius: 0 2px 2px 0;
        }

        .salutation {
            font-size: 16px;
            font-weight: 600;
            color: #34495e;
            margin-bottom: 15px;
            padding-bottom: 8px;
            border-bottom: 1px solid #ecf0f1;
        }

        .letter-body {
            font-size: 14px;
            line-height: 1.6;
            color: #2c3e50;
            margin-bottom: 15px;
            text-align: justify;
        }

        .closing-message {
            background: #e8f5e8;
            border: 1px solid #c3e6c3;
            border-radius: 4px;
            padding: 12px;
            margin: 15px 0;
            font-style: italic;
            color: #27ae60;
            text-align: center;
            font-weight: 500;
            font-size: 14px;
        }

        .signature-section {
            margin-top: 25px;
            text-align: {{ app()->getLocale() == 'ar' ? 'left' : 'right' }};
        }

        .signature-title {
            font-weight: 600;
            color: #34495e;
            margin-bottom: 25px;
            font-size: 14px;
        }

        .signature-line {
            border-bottom: 2px solid #34495e;
            width: 150px;
            margin: {{ app()->getLocale() == 'ar' ? '0 0 0 auto' : '0 auto 0 0' }};
            height: 30px;
        }

        .footer {
            background: #2c3e50;
            color: white;
            padding: 12px 25px;
            text-align: center;
            font-size: 10px;
            margin-top: auto;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 15px;
            font-weight: 600;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            margin-left: 10px;
        }

        .status-resignation {
            background: #fff3cd;
            color: #856404;
            border: 1px solid #ffeaa7;
        }

        .status-termination {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .watermark {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            font-size: 60px;
            color: rgba(0,0,0,0.03);
            font-weight: bold;
            z-index: 0;
            pointer-events: none;
        }

        @media print {
            body {
                background: white;
                margin: 0;
                padding: 0;
                font-size: 12px;
            }

            .document {
                box-shadow: none;
                margin: 0;
                border-radius: 0;
                max-width: 100%;
                min-height: auto;
                height: 100vh;
                display: flex;
                flex-direction: column;
            }

            .header {
                padding: 15px 20px;
            }

            .company-name {
                font-size: 20px;
                margin-bottom: 6px;
            }

            .company-details {
                font-size: 11px;
            }

            .document-title {
                padding: 12px 20px;
                font-size: 16px;
            }

            .content {
                padding: 15px 20px;
                flex: 1;
            }

            .info-section {
                padding: 12px;
                margin-bottom: 12px;
            }

            .info-grid {
                gap: 8px;
            }

            .info-item {
                padding: 6px 10px;
                font-size: 12px;
            }

            .info-label {
                font-size: 11px;
                min-width: 80px;
            }

            .info-value {
                font-size: 12px;
            }

            .letter-content {
                padding: 15px;
                margin: 12px 0;
            }

            .salutation {
                font-size: 14px;
                margin-bottom: 12px;
            }

            .letter-body {
                font-size: 13px;
                margin-bottom: 12px;
                line-height: 1.5;
            }

            .closing-message {
                padding: 10px;
                font-size: 13px;
                margin: 12px 0;
            }

            .signature-section {
                margin-top: 20px;
            }

            .signature-title {
                font-size: 13px;
                margin-bottom: 20px;
            }

            .signature-line {
                width: 120px;
                height: 25px;
            }

            .footer {
                padding: 10px 20px;
                font-size: 9px;
            }

            .watermark {
                font-size: 50px;
            }

            .status-badge {
                font-size: 11px;
                padding: 4px 8px;
            }
        }
    </style>
</head>

<body>
<div class="document">
    <!-- Header Section -->
    <div class="header">
        <div class="company-name">{{ settings('app_name') }}</div>
        <div class="company-details">
            <div>{{ __('Date') }}: {{ \Carbon\Carbon::now()->format('d/m/Y') }}</div>
            <div>{{ __('Time') }}: {{ \Carbon\Carbon::now()->format('h:i:s A') }}</div>
        </div>
    </div>

    <!-- Document Title -->
    <div class="document-title">
        {{ __('Employee Resignation') }}
        <span class="status-badge status-resignation">
                {{ __('Resignation') }}
            </span>
    </div>

    <!-- Content Section -->
    <div class="content">
        <!-- Employee Information -->
        <div class="info-section">
            <div class="info-grid">
                <div class="info-item">
                    <span class="info-label">{{ __('Movement No') }}:</span>
                    <span class="info-value">#{{ str_pad($record->id, 6, '0', STR_PAD_LEFT) }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('Movement Date') }}:</span>
                    <span class="info-value">{{ $record->change_date->format('d/m/Y') }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('Employee') }}:</span>
                    <span class="info-value">{{ $record->employee->full_name }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('Functional Number') }}:</span>
                    <span class="info-value">{{ $record->employee->employee_number }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('Functional Title') }}:</span>
                    <span class="info-value">{{ $record->employee->position->name ?? 'N/A' }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">{{ __('Status') }}:</span>
                    <span class="info-value">{{ $record->status->name }}</span>
                </div>
            </div>
        </div>

        <!-- Letter Content -->
        <div class="letter-content">
            <div class="watermark">
                {{ __('Resignation') }}
            </div>

            <div class="salutation">{{ __('After the salutation') }}</div>

            <div class="letter-body">
                {{ __('Based on the resignation request submitted by you, we would like to inform you that the management has approved your resignation from work, effective from the date of :date, so please review the financial department to pay your dues.', ['date' => $record->change_date->format('d/m/Y')]) }}
            </div>

            @if ($record->note)
                <div class="letter-body">
                    <strong>{{ __('Note') }}:</strong> {{ $record->note }}
                </div>
            @endif

            <div class="closing-message">
                {{ __('We wish you all the best in your future work') }}
            </div>

            <!-- Signature Section -->
            <div class="signature-section">
                <div class="signature-title">{{ __('general manager') }}</div>
                <div class="signature-line"></div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer">
        <div>{{ settings('app_name') }} - {{ __('Employee Status Change Document') }}</div>
        <div>{{ __('Generated on') }}: {{ \Carbon\Carbon::now()->format('d/m/Y h:i:s A') }}</div>
    </div>
</div>
</body>

</html>
