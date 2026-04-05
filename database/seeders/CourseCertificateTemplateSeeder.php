<?php

namespace Database\Seeders;

use App\Models\Course;
use App\Models\CourseCertificateTemplate;
use Illuminate\Database\Seeder;

class CourseCertificateTemplateSeeder extends Seeder
{
    public function run(): void
    {
        Course::query()->get()->each(function (Course $course): void {
            $template = CourseCertificateTemplate::query()->updateOrCreate(
                [
                    'course_id' => $course->id,
                ],
                [
                    'x1' => 0.22,
                    'y1' => 0.46,
                    'x2' => 0.78,
                    'y2' => 0.58,
                    'text_color' => '#10243f',
                    'font_size' => 44,
                    'font_family' => 'DejaVu Sans',
                    'text_align' => 'center',
                    'is_active' => true,
                ],
            );

            if ($template->hasMedia('template_image')) {
                return;
            }

            $template
                ->addMediaFromString($this->buildTemplateSvg($course))
                ->usingFileName("course-certificate-template-{$course->slug}.svg")
                ->toMediaCollection('template_image');
        });
    }

    protected function buildTemplateSvg(Course $course): string
    {
        $title = htmlspecialchars(
            data_get($course->getTranslations('title'), 'en')
                ?: data_get($course->getTranslations('title'), 'ar')
                ?: $course->slug,
            ENT_QUOTES,
            'UTF-8'
        );

        return <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" width="1600" height="900" viewBox="0 0 1600 900">
  <defs>
    <linearGradient id="bg" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" stop-color="#f8fbff"/>
      <stop offset="100%" stop-color="#eef4ff"/>
    </linearGradient>
    <linearGradient id="accent" x1="0%" y1="0%" x2="100%" y2="0%">
      <stop offset="0%" stop-color="#1d4ed8"/>
      <stop offset="100%" stop-color="#ef4444"/>
    </linearGradient>
  </defs>

  <rect width="1600" height="900" rx="28" fill="url(#bg)"/>
  <rect x="28" y="28" width="1544" height="844" rx="24" fill="none" stroke="#d7e3f5" stroke-width="2"/>

  <circle cx="1450" cy="120" r="110" fill="#dbeafe" opacity="0.8"/>
  <circle cx="180" cy="760" r="150" fill="#fee2e2" opacity="0.75"/>

  <text x="800" y="170" text-anchor="middle" fill="#1f2937" font-size="30" font-family="Arial, sans-serif" font-weight="700">
    COMPUTIQ ACADEMY
  </text>
  <text x="800" y="250" text-anchor="middle" fill="#0f172a" font-size="64" font-family="Arial, sans-serif" font-weight="800">
    Certificate of Completion
  </text>
  <text x="800" y="310" text-anchor="middle" fill="#475569" font-size="26" font-family="Arial, sans-serif">
    This certifies the learner has successfully completed the course
  </text>
  <text x="800" y="352" text-anchor="middle" fill="#1d4ed8" font-size="28" font-family="Arial, sans-serif" font-weight="700">
    {$title}
  </text>

  <text x="800" y="420" text-anchor="middle" fill="#64748b" font-size="20" font-family="Arial, sans-serif" letter-spacing="2">
    STUDENT NAME
  </text>

  <rect x="352" y="414" width="896" height="124" rx="18" fill="#ffffff" fill-opacity="0.55" stroke="#cbd5e1" stroke-width="2" stroke-dasharray="10 10"/>
  <line x1="392" y1="572" x2="1208" y2="572" stroke="url(#accent)" stroke-width="3" stroke-dasharray="10 10"/>

  <text x="800" y="670" text-anchor="middle" fill="#475569" font-size="22" font-family="Arial, sans-serif">
    Achievement • Excellence • Verified Learning
  </text>

  <rect x="1120" y="720" width="300" height="94" rx="20" fill="#ffffff" stroke="#dbe4f2" />
  <text x="1270" y="762" text-anchor="middle" fill="#64748b" font-size="18" font-family="Arial, sans-serif">Authorized by</text>
  <text x="1270" y="792" text-anchor="middle" fill="#0f172a" font-size="22" font-family="Arial, sans-serif" font-weight="700">Computiq Academy</text>
</svg>
SVG;
    }
}
