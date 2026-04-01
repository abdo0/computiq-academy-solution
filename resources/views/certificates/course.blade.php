<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Course Certificate</title>
    <style>
        @page { margin: 0; }
        body {
            margin: 0;
            font-family: DejaVu Sans, sans-serif;
            background: #0f172a;
            color: #f8fafc;
        }
        .page {
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            padding: 48px;
            background:
                radial-gradient(circle at top right, rgba(37, 99, 235, .28), transparent 28%),
                radial-gradient(circle at bottom left, rgba(220, 38, 38, .20), transparent 32%),
                linear-gradient(135deg, #0f172a 0%, #111827 45%, #172033 100%);
        }
        .frame {
            height: 100%;
            border: 2px solid rgba(248, 250, 252, .16);
            border-radius: 28px;
            padding: 34px 42px;
            box-sizing: border-box;
            position: relative;
            background: rgba(15, 23, 42, .55);
        }
        .badge {
            display: inline-block;
            padding: 8px 14px;
            border-radius: 999px;
            color: #fecaca;
            background: rgba(220, 38, 38, .15);
            font-size: 14px;
            letter-spacing: .08em;
            text-transform: uppercase;
        }
        .title {
            margin: 28px 0 6px;
            font-size: 42px;
            font-weight: 700;
        }
        .subtitle {
            font-size: 18px;
            color: #cbd5e1;
            margin-bottom: 34px;
        }
        .student {
            font-size: 40px;
            font-weight: 700;
            margin: 24px 0 14px;
            color: #fff7ed;
        }
        .course {
            font-size: 30px;
            font-weight: 700;
            color: #93c5fd;
            margin: 12px 0 18px;
        }
        .copy {
            max-width: 760px;
            line-height: 1.8;
            font-size: 17px;
            color: #cbd5e1;
        }
        .meta {
            position: absolute;
            left: 42px;
            right: 42px;
            bottom: 34px;
            overflow: hidden;
        }
        .meta-box {
            width: 32%;
            float: left;
            margin-right: 2%;
            padding: 14px 16px;
            border-radius: 18px;
            background: rgba(255, 255, 255, .05);
            border: 1px solid rgba(255, 255, 255, .08);
            box-sizing: border-box;
        }
        .meta-box:last-child {
            margin-right: 0;
        }
        .meta-label {
            font-size: 12px;
            color: #94a3b8;
            text-transform: uppercase;
            letter-spacing: .08em;
            margin-bottom: 6px;
        }
        .meta-value {
            font-size: 16px;
            color: #f8fafc;
            font-weight: 600;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="frame">
            <div class="badge">Computiq Academy</div>
            <div class="title">Certificate of Completion</div>
            <div class="subtitle">This document certifies that the learner below has successfully completed the course and passed its required assessments.</div>

            <div style="font-size: 16px; color: #94a3b8;">Presented to</div>
            <div class="student">{{ $certificate->student_name }}</div>

            <div style="font-size: 16px; color: #94a3b8;">For successfully completing</div>
            <div class="course">{{ data_get($course->getTranslations('title'), $user->locale) ?: data_get($course->getTranslations('title'), 'ar') ?: data_get($course->getTranslations('title'), 'en') ?: $course->slug }}</div>

            <div class="copy">
                This certificate was issued by Computiq Academy as a record of successful course completion and assessment achievement.
            </div>

            <div class="meta">
                <div class="meta-box">
                    <div class="meta-label">Issued At</div>
                    <div class="meta-value">{{ optional($certificate->issued_at)->format('F j, Y') }}</div>
                </div>
                <div class="meta-box">
                    <div class="meta-label">Certificate Number</div>
                    <div class="meta-value">{{ $certificate->certificate_number }}</div>
                </div>
                <div class="meta-box">
                    <div class="meta-label">Verification Code</div>
                    <div class="meta-value">{{ $certificate->verification_code }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
