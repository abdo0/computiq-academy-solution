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
            color: #0f172a;
        }
        .page {
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            padding: 22px;
            background: #f8fafc;
        }
        .canvas {
            position: relative;
            width: 100%;
            height: 100%;
            box-sizing: border-box;
            overflow: hidden;
            border-radius: 20px;
            background: #ffffff;
            border: 1px solid #dbe4f2;
        }
        .template-image {
            width: 100%;
            display: block;
        }
        .student-name {
            position: absolute;
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            overflow: hidden;
            white-space: nowrap;
            font-weight: 700;
            line-height: 1.2;
        }
        .meta-strip {
            position: absolute;
            left: 32px;
            right: 32px;
            bottom: 24px;
            display: table;
            table-layout: fixed;
            width: calc(100% - 64px);
            border-collapse: separate;
            border-spacing: 14px 0;
        }
        .meta-box {
            display: table-cell;
            padding: 12px 16px;
            background: rgba(255, 255, 255, 0.9);
            border: 1px solid #dbe4f2;
            border-radius: 14px;
            vertical-align: middle;
        }
        .meta-label {
            font-size: 11px;
            color: #64748b;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: .08em;
        }
        .meta-value {
            font-size: 14px;
            color: #0f172a;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="page">
        <div class="canvas">
            @if ($templateImageDataUri)
                <img class="template-image" src="{{ $templateImageDataUri }}" alt="Certificate Template">
            @endif

            <div
                class="student-name"
                style="
                    left: {{ $nameAreaStyle['left'] }}%;
                    top: {{ $nameAreaStyle['top'] }}%;
                    width: {{ $nameAreaStyle['width'] }}%;
                    height: {{ $nameAreaStyle['height'] }}%;
                    color: {{ $nameAreaStyle['text_color'] }};
                    font-size: {{ $nameAreaStyle['font_size'] }}px;
                    font-family: '{{ $nameAreaStyle['font_family'] }}', DejaVu Sans, sans-serif;
                    text-align: {{ $nameAreaStyle['text_align'] }};
                "
            >
                {{ $resolvedStudentName }}
            </div>

            <div class="meta-strip">
                <div class="meta-box">
                    <div class="meta-label">Course</div>
                    <div class="meta-value">{{ $resolvedCourseTitle }}</div>
                </div>
                <div class="meta-box">
                    <div class="meta-label">Issued At</div>
                    <div class="meta-value">{{ optional($certificate->issued_at)->format('Y-m-d') }}</div>
                </div>
                <div class="meta-box">
                    <div class="meta-label">Verification</div>
                    <div class="meta-value">{{ $certificate->verification_code }}</div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
