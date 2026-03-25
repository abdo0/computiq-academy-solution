<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseCategory;
use App\Models\Section;
use App\Models\Sponsor;
use App\Models\Testimonial;
use Illuminate\Support\Facades\Cache;

class HomePageService
{
    /**
     * Get all initial data for the home page.
     */
    public function getInitialData(string $locale): array
    {
        return [
            'stats' => $this->getStats($locale),
            'campaigns' => $this->getCampaigns($locale),
            'sections' => $this->getSections($locale),
            'testimonials' => $this->getTestimonials(),
            'trust_features' => $this->getTrustFeatures(),
            'working_steps' => $this->getWorkingSteps(),
            'course_categories' => $this->getCourseCategories(),
            'courses' => $this->getCourses(),
            'sponsors' => $this->getSponsors(),
        ];
    }

    /**
     * Get platform statistics.
     * Stats labels are stored multi-lingual, no cache-per-locale needed.
     */
    private function getStats(string $locale): array
    {
        // Stats numbers are the same across locales, only labels differ client-side
        return Cache::remember('platform_stats', 300, function () {
            $totalDonations = 0;
            $totalDonors = 0;
            $activeCampaigns = 0;
            $totalOrganizations = 0;

            return [
                [
                    'id' => 1,
                    'label' => __('إجمالي التبرعات'),
                    'labelEn' => 'Total Donations',
                    'labelKu' => 'کۆی پارەدانەکان',
                    'value' => $this->formatNumber($totalDonations),
                    'iconName' => 'HandHeart',
                ],
                [
                    'id' => 2,
                    'label' => __('المتبرعين'),
                    'labelEn' => 'Donors',
                    'labelKu' => 'پارەدەران',
                    'value' => $this->formatNumber($totalDonors),
                    'iconName' => 'Users',
                ],
                [
                    'id' => 3,
                    'label' => __('الحملات النشطة'),
                    'labelEn' => 'Active Campaigns',
                    'labelKu' => 'کەمپەینە چالاکەکان',
                    'value' => $this->formatNumber($activeCampaigns),
                    'iconName' => 'Droplets',
                ],
                [
                    'id' => 4,
                    'label' => __('المنظمات'),
                    'labelEn' => 'Organizations',
                    'labelKu' => 'ڕێکخراوەکان',
                    'value' => $this->formatNumber($totalOrganizations),
                    'iconName' => 'BuildingOffice',
                ],
            ];
        });
    }

    /**
     * Get active campaigns for the home page.
     * Campaign titles/descriptions are locale-sensitive, so we cache per locale.
     */
    private function getCampaigns(string $locale): array
    {
        return [];
    }

    /**
     * Get all active home sections.
     * Sections have multi-lingual fields so we store all translations in one cache entry.
     */
    private function getSections(string $locale): array
    {
        return Cache::remember('home_sections_api', 3600, function () {
            return Section::where('is_active', true)
                ->get()
                ->keyBy('key')
                ->map(fn ($section) => [
                    'id' => (string) $section->id,
                    'key' => $section->key,
                    'title' => $section->getTranslation('title', 'ar'),
                    'titleEn' => $section->getTranslation('title', 'en'),
                    'titleKu' => $section->getTranslation('title', 'ku'),
                    'description' => $section->description
                        ? $section->getTranslation('description', 'ar')
                        : null,
                    'descriptionEn' => $section->description && array_key_exists('en', $section->getTranslations('description'))
                        ? $section->getTranslation('description', 'en')
                        : null,
                    'descriptionKu' => $section->description && array_key_exists('ku', $section->getTranslations('description'))
                        ? $section->getTranslation('description', 'ku')
                        : null,
                    'extra_data' => $section->extra_data,
                ])
                ->toArray();
        });
    }

    /**
     * Format large numbers for display.
     */
    private function formatNumber(float|int $number): string
    {
        if ($number >= 1000000) {
            return number_format($number / 1000000, 1).'M';
        }

        if ($number >= 1000) {
            return number_format($number / 1000, 1).'K';
        }

        return number_format($number);
    }

    /**
     * Get testimonials (reviews section).
     */
    private function getTestimonials(): array
    {
        return Cache::remember('home_testimonials_api', 3600, function () {
            return Testimonial::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($t) => [
                    'id' => (string) $t->id,
                    'name' => $t->getTranslation('name', 'ar'),
                    'nameEn' => $t->getTranslation('name', 'en'),
                    'nameKu' => $t->getTranslation('name', 'ku'),
                    'comment' => $t->getTranslation('comment', 'ar'),
                    'commentEn' => $t->getTranslation('comment', 'en'),
                    'commentKu' => $t->getTranslation('comment', 'ku'),
                    'rating' => $t->rating,
                ])
                ->toArray();
        });
    }

    /**
     * Get trust features.
     */
    private function getTrustFeatures(): array
    {
        return [];
    }

    /**
     * Get working steps.
     */
    private function getWorkingSteps(): array
    {
        return [];
    }

    /**
     * Get active course categories.
     */
    private function getCourseCategories(): array
    {
        return Cache::remember('home_course_categories', 3600, function () {
            return CourseCategory::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($cat) => [
                    'id' => $cat->id,
                    'name' => $cat->getTranslations('name'),
                    'slug' => $cat->slug,
                    'image' => $cat->image,
                    'parent_id' => $cat->parent_id,
                ])
                ->toArray();
        });
    }

    /**
     * Get active courses for the home page.
     */
    private function getCourses(): array
    {
        return Cache::remember('home_courses', 3600, function () {
            return Course::where('is_active', true)
                ->with(['category', 'instructor'])
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($c) => [
                    'id' => $c->id,
                    'title' => $c->getTranslations('title'),
                    'slug' => $c->slug,
                    'short_description' => $c->getTranslations('short_description'),
                    'image' => $c->image,
                    'instructor_name' => $c->instructor_name,
                    'instructor_image' => $c->instructor_image,
                    'rating' => (float) $c->rating,
                    'review_count' => $c->review_count,
                    'duration_hours' => $c->duration_hours,
                    'students_count' => $c->students_count,
                    'price' => (float) $c->price,
                    'old_price' => $c->old_price ? (float) $c->old_price : null,
                    'is_live' => $c->is_live,
                    'is_best_seller' => $c->is_best_seller,
                    'category_id' => $c->course_category_id,
                    'category_slug' => $c->category?->slug,
                    'instructor_slug' => $c->instructor?->slug,
                    'instructor_image' => $c->instructor?->image ?? $c->instructor_image,
                ])
                ->toArray();
        });
    }

    /**
     * Get active sponsors and partners.
     */
    private function getSponsors(): array
    {
        return Cache::remember('home_sponsors_api', 3600, function () {
            $all = Sponsor::active()->orderBy('sort_order')->get();
            return [
                'partners' => $all->where('type', 'partner')->values()->toArray(),
                'employment' => $all->where('type', 'employment')->values()->toArray(),
            ];
        });
    }
}
