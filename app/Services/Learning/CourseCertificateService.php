<?php

namespace App\Services\Learning;

use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\CourseCertificateTemplate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CourseCertificateService
{
    public function findIssued(User $user, Course $course): ?CourseCertificate
    {
        return CourseCertificate::query()
            ->where('user_id', $user->id)
            ->where('course_id', $course->id)
            ->first();
    }

    public function download(User $user, Course $course, bool $eligible): Response
    {
        $certificate = $this->issueCertificate($user, $course, $eligible);
        $template = $this->resolveTemplate($course);

        abort_unless($template, 403, __('No certificate template has been configured for this course yet.'));

        $pdf = Pdf::loadView('certificates.course', [
            'certificate' => $certificate,
            'course' => $course,
            'user' => $user,
            'template' => $template,
            'templateImageDataUri' => $this->mediaToDataUri($template),
            'resolvedStudentName' => $certificate->student_name,
            'resolvedCourseTitle' => $this->resolveCourseTitle($course, $user->locale ?? app()->getLocale()),
            'nameAreaStyle' => $this->buildNameAreaStyle($template),
        ])->setPaper('a4', 'landscape');

        return $pdf->download("course-certificate-{$course->slug}.pdf");
    }

    public function toPayload(User $user, Course $course, bool $eligible): array
    {
        $template = $this->resolveTemplate($course);
        $issued = $this->findIssued($user, $course);
        $hasTemplate = $template !== null;
        $hasRealName = filled(trim((string) $user->real_name));
        $downloadAllowed = false;

        if (! $hasTemplate) {
            $status = 'unavailable_template';
            $lockedReason = __('Certificate template is not configured yet.');
        } elseif ($issued) {
            $status = 'ready';
            $lockedReason = null;
            $downloadAllowed = true;
        } elseif (! $eligible) {
            $status = 'locked_progress';
            $lockedReason = __('Complete the course lessons and pass the required assessments to unlock this certificate.');
        } elseif (! $hasRealName) {
            $status = 'locked_real_name';
            $lockedReason = __('Add your real name in your profile before downloading this certificate.');
        } else {
            $status = 'ready';
            $lockedReason = null;
            $downloadAllowed = true;
        }

        return [
            'visible' => true,
            'status' => $status,
            'available' => $downloadAllowed,
            'is_locked' => ! $downloadAllowed,
            'issued' => $issued !== null,
            'student_name' => $issued?->student_name ?: (filled($user->real_name) ? $user->real_name : null),
            'certificate_number' => $issued?->certificate_number,
            'verification_code' => $issued?->verification_code,
            'issued_at' => optional($issued?->issued_at)->toIso8601String(),
            'locked_reason' => $lockedReason,
            'requires_real_name' => $status === 'locked_real_name',
            'profile_url' => '/dashboard?tab=profile',
            'download_url' => $downloadAllowed
                ? route('api.v1.user.certificates.download', ['course' => $course->id], false)
                : null,
            'template' => $template ? [
                'image_url' => $template->getFirstMediaUrl('template_image'),
                'preview_url' => $template->getFirstMediaUrl('template_image', 'preview') ?: $template->getFirstMediaUrl('template_image'),
                'name_area' => [
                    'x1' => $template->x1,
                    'y1' => $template->y1,
                    'x2' => $template->x2,
                    'y2' => $template->y2,
                ],
                'style' => [
                    'text_color' => $template->text_color,
                    'font_size' => $template->font_size,
                    'font_family' => $template->font_family,
                    'text_align' => $template->text_align,
                ],
            ] : null,
        ];
    }

    public function issueCertificate(User $user, Course $course, bool $eligible): CourseCertificate
    {
        abort_unless($eligible, 403, __('Your certificate is not available yet.'));

        $template = $this->resolveTemplate($course);
        abort_unless($template, 403, __('No certificate template has been configured for this course yet.'));

        $existing = $this->findIssued($user, $course);

        if ($existing) {
            return $existing;
        }

        $realName = trim((string) $user->real_name);
        abort_if($realName === '', 403, __('Please add your real name in your profile before downloading your certificate.'));

        return CourseCertificate::query()->create([
            'user_id' => $user->id,
            'course_id' => $course->id,
            'student_name' => $realName,
            'certificate_number' => $this->generateCertificateNumber($course, $user),
            'verification_code' => $this->generateVerificationCode(),
            'issued_at' => now(),
        ]);
    }

    protected function resolveTemplate(Course $course): ?CourseCertificateTemplate
    {
        $template = $course->relationLoaded('certificateTemplate')
            ? $course->certificateTemplate
            : $course->certificateTemplate()->first();

        if (! $template || ! $template->is_active || ! $template->hasUsableImage()) {
            return null;
        }

        return $template;
    }

    protected function resolveCourseTitle(Course $course, ?string $locale = null): string
    {
        $locale ??= app()->getLocale();

        return data_get($course->getTranslations('title'), $locale)
            ?: data_get($course->getTranslations('title'), 'ar')
            ?: data_get($course->getTranslations('title'), 'en')
            ?: $course->slug;
    }

    protected function buildNameAreaStyle(CourseCertificateTemplate $template): array
    {
        $left = min($template->x1, $template->x2) * 100;
        $top = min($template->y1, $template->y2) * 100;
        $width = max(abs($template->x2 - $template->x1) * 100, 1);
        $height = max(abs($template->y2 - $template->y1) * 100, 1);

        return [
            'left' => $left,
            'top' => $top,
            'width' => $width,
            'height' => $height,
            'text_color' => $template->text_color,
            'font_size' => $template->font_size,
            'font_family' => $template->font_family,
            'text_align' => $template->text_align,
        ];
    }

    protected function mediaToDataUri(CourseCertificateTemplate $template): ?string
    {
        $media = $template->getFirstMedia('template_image');

        if (! $media || ! is_file($media->getPath())) {
            return null;
        }

        $mime = $media->mime_type ?: 'image/png';
        $contents = file_get_contents($media->getPath());

        if ($contents === false) {
            return null;
        }

        return sprintf('data:%s;base64,%s', $mime, base64_encode($contents));
    }

    protected function generateCertificateNumber(Course $course, User $user): string
    {
        return sprintf(
            'CMP-%s-%s-%s',
            str_pad((string) $course->id, 4, '0', STR_PAD_LEFT),
            str_pad((string) $user->id, 5, '0', STR_PAD_LEFT),
            now()->format('YmdHis')
        );
    }

    protected function generateVerificationCode(): string
    {
        return Str::upper(Str::random(12));
    }
}
