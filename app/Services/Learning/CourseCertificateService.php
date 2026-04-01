<?php

namespace App\Services\Learning;

use App\Models\Course;
use App\Models\CourseCertificate;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class CourseCertificateService
{
    public function ensureIssued(User $user, Course $course, bool $eligible): ?CourseCertificate
    {
        if (! $eligible) {
            return CourseCertificate::query()
                ->where('user_id', $user->id)
                ->where('course_id', $course->id)
                ->first();
        }

        return CourseCertificate::query()->firstOrCreate(
            [
                'user_id' => $user->id,
                'course_id' => $course->id,
            ],
            [
                'student_name' => $user->name,
                'certificate_number' => $this->generateCertificateNumber($course, $user),
                'verification_code' => $this->generateVerificationCode(),
                'issued_at' => now(),
            ],
        );
    }

    public function download(User $user, Course $course, bool $eligible): Response
    {
        abort_unless($eligible, 403, __('Your certificate is not available yet.'));

        $certificate = $this->ensureIssued($user, $course, true);

        $pdf = Pdf::loadView('certificates.course', [
            'certificate' => $certificate,
            'course' => $course,
            'user' => $user,
        ])->setPaper('a4', 'landscape');

        return $pdf->download("course-certificate-{$course->slug}.pdf");
    }

    public function toPayload(?CourseCertificate $certificate, bool $eligible, Course $course): array
    {
        return [
            'available' => $eligible,
            'issued' => $certificate !== null,
            'student_name' => $certificate?->student_name,
            'certificate_number' => $certificate?->certificate_number,
            'verification_code' => $certificate?->verification_code,
            'issued_at' => optional($certificate?->issued_at)->toIso8601String(),
            'download_url' => $eligible ? route('api.v1.user.certificates.download', ['course' => $course->id], false) : null,
        ];
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
