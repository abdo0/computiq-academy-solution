<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Country;
use App\Models\FAQ;
use App\Models\Page;
use App\Models\Setting;
use App\Models\State;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Cache;

class ContentController extends Controller
{
    /**
     * Get site settings.
     */
    public function settings(): JsonResponse
    {
        $settings = Cache::remember('site_settings_v2', 3600, function () {
            return $this->getSettings();
        });

        return response()->success($settings, __('Settings retrieved successfully'));
    }

    /**
     * Get FAQs.
     */
    public function faqs(): JsonResponse
    {
        // Set SEO tags using seo() helper for FAQ page
        seo()->setTitle(__('Frequently Asked Questions'));
        seo()->setDescription(__('Find answers to commonly asked questions'));
        seo()->setKeywords(__('faq,questions,answers,help'));
        seo()->setTag('og:image', 'https://picsum.photos/seed/faq/1200/630');

        $faqs = Cache::remember('faqs', 1800, function () {
            return FAQ::active()
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($faq) => [
                    'id' => (string) $faq->id,
                    'question' => $faq->question,
                    'answer' => $faq->answer,
                ]);
        });

        return response()->success($faqs->toArray(), __('FAQs retrieved successfully'));
    }


    /**
     * Get active course categories.
     */
    public function categories(): JsonResponse
    {
        $categories = Cache::remember('course_categories_api', 3600, function () {
            return \App\Models\CourseCategory::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($cat) => [
                    'id' => (string) $cat->id,
                    'name' => $cat->getTranslations('name'),
                    'slug' => $cat->slug,
                    'image' => $cat->image,
                    'parent_id' => $cat->parent_id,
                ]);
        });

        return response()->success($categories->toArray(), __('Categories retrieved successfully'));
    }

    /**
     * Get active countries.
     */
    public function countries(): JsonResponse
    {
        $countries = Cache::remember('countries', 3600, function () {
            return Country::active()
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($country) => [
                    'id' => $country->id,
                    'name' => $country->getTranslation('name', app()->getLocale()),
                    'code' => $country->code,
                    'iso2' => $country->iso2,
                    'phone_code' => $country->phone_code,
                ]);
        });

        return response()->success($countries->toArray(), __('Countries retrieved successfully'));
    }

    /**
     * Get states by country.
     */
    public function states($countryId): JsonResponse
    {
        $states = Cache::remember("states_country_{$countryId}", 3600, function () use ($countryId) {
            return State::where('country_id', $countryId)
                ->where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($state) => [
                    'id' => $state->id,
                    'name' => $state->getTranslation('name', app()->getLocale()),
                    'country_id' => $state->country_id,
                ]);
        });

        return response()->success($states->toArray(), __('States retrieved successfully'));
    }

    /**
     * Get hero content.
     */
    public function hero(): JsonResponse
    {
        $heroContent = Cache::remember('hero_content', 3600, function () {
            $heroSection = \App\Models\Section::where('key', 'home_hero_extra')->first();
            if (! $heroSection) {
                return [];
            }

            return [
                'title' => $heroSection->title,
                'subtitle' => $heroSection->description,
                'perks' => $heroSection->extra_data['perks'] ?? null,
                'cta_text' => $heroSection->extra_data['cta_text'] ?? null,
                'background_image' => $heroSection->extra_data['background_image'] ?? null,
            ];
        });

        return response()->success($heroContent, __('Hero content retrieved successfully'));
    }

    /**
     * Get testimonials.
     */
    public function testimonials(): JsonResponse
    {
        $testimonials = Cache::remember('testimonials_api', 3600, function () {
            // Need to retrieve translatable fields properly depending on app() locale
            // Actually it's better to fetch and format like categories
            return Testimonial::where('is_active', true)
                ->orderBy('sort_order')
                ->get()
                ->map(fn ($t) => [
                    'id' => (string) $t->id,
                    'name' => $t->name,
                    'comment' => $t->comment,
                    'rating' => $t->rating,
                ]);
        });

        return response()->success($testimonials->toArray(), __('Testimonials retrieved successfully'));
    }



    /**
     * Get dynamic home page sections content.
     */
    public function homeSections(): JsonResponse
    {
        $sections = Cache::remember('home_dynamic_sections', 3600, function () {
            $data = \App\Models\Section::where('is_active', true)->get();
            $formatted = [];
            foreach ($data as $section) {
                $formatted[$section->key] = [
                    'title' => $section->title,
                    'description' => $section->description,
                    'extra_data' => $section->extra_data,
                ];
            }

            return $formatted;
        });

        return response()->success($sections, __('Home sections retrieved successfully'));
    }

    /**
     * Get SEO for home page.
     */
    public function homeSeo(): JsonResponse
    {
        // Set SEO tags using seo() helper
        seo()->setTitle(__('Home'));
        seo()->setDescription(__('Welcome to our donation platform. Make a difference with your contributions'));
        seo()->setKeywords(__('donations,charity,home'));
        seo()->setTag('og:image', 'https://picsum.photos/seed/home/1200/630');

        return response()->success([], __('Home page loaded successfully'));
    }

    /**
     * Get SEO for about page.
     */
    public function aboutSeo(): JsonResponse
    {
        // Set SEO tags using seo() helper
        seo()->setTitle(__('About Us'));
        seo()->setDescription(__('Learn more about our mission, vision, and values'));
        seo()->setKeywords(__('about,mission,vision,values'));
        seo()->setTag('og:image', 'https://picsum.photos/seed/about/1200/630');

        return response()->success([], __('About page loaded successfully'));
    }

    /**
     * Get SEO for contact page.
     */
    public function contactSeo(): JsonResponse
    {
        // Set SEO tags using seo() helper
        seo()->setTitle(__('Contact Us'));
        seo()->setDescription(__('Get in touch with us. We are here to help'));
        seo()->setKeywords(__('contact,get in touch,help,support'));
        seo()->setTag('og:image', 'https://picsum.photos/seed/contact/1200/630');

        return response()->success([], __('Contact page loaded successfully'));
    }



    /**
     * Get a single CMS page by slug.
     */
    public function page(string $slug): JsonResponse
    {
        $page = Page::query()
            ->where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        return response()->success([
            'slug' => $page->slug,
            'title' => $page->getTranslations('title'),
            'content' => $page->getTranslations('content'),
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
        ], __('Page retrieved successfully'));
    }

    /**
     * Get pages that should be shown in the public footer.
     */
    public function footerPages(): JsonResponse
    {
        $pages = Cache::remember('footer_pages', 3600, function () {
            return Page::query()
                ->where('is_published', true)
                ->where('show_in_footer', true)
                ->orderBy('sort_order')
                ->get(['slug'])
                ->map(fn (Page $page) => [
                    'slug' => $page->slug,
                ])
                ->values()
                ->all();
        });

        return response()->success($pages, __('Footer pages retrieved successfully'));
    }

    /**
     * Get other pages (not shown in header or footer).
     */
    public function otherPages(): JsonResponse
    {
        $pages = Cache::remember('other_pages', 3600, function () {
            return Page::query()
                ->where('is_published', true)
                ->where('show_in_header', false)
                ->where('show_in_footer', false)
                ->orderBy('sort_order')
                ->get(['slug', 'title'])
                ->map(fn (Page $page) => [
                    'slug' => $page->slug,
                    'title' => $page->title,
                ])
                ->values()
                ->all();
        });

        return response()->success($pages, __('Other pages retrieved successfully'));
    }

    /**
     * Get settings from database or defaults.
     */
    private function getSettings(): array
    {
        return [
            'logoUrl' => Setting::get('Logo url'),
            'siteName' => [
                'ar' => Setting::get('Site name ar'),
                'en' => Setting::get('Site name en'),
                'ku' => Setting::get('Site name ku'),
            ],
            'contactEmail' => Setting::get('Contact email'),
            'contactPhone' => array_filter([
                Setting::get('Contact phone 1'),
                Setting::get('Contact phone 2'),
            ]),
            'address' => [
                'ar' => Setting::get('Address ar'),
                'en' => Setting::get('Address en'),
                'ku' => Setting::get('Address ku'),
            ],
            'socialLinks' => [
                'twitter' => Setting::get('Social twitter'),
                'facebook' => Setting::get('Social facebook'),
                'instagram' => Setting::get('Social instagram'),
                'youtube' => Setting::get('Social youtube'),
            ],
            'footerDesc' => [
                'ar' => Setting::get('Footer desc ar'),
                'en' => Setting::get('Footer desc en'),
                'ku' => Setting::get('Footer desc ku'),
            ],
            'defaultSeo' => [
                'title' => [
                    'ar' => Setting::get('Seo title ar'),
                    'en' => Setting::get('Seo title en'),
                    'ku' => Setting::get('Seo title ku'),
                ],
                'description' => [
                    'ar' => Setting::get('Seo description ar'),
                    'en' => Setting::get('Seo description en'),
                    'ku' => Setting::get('Seo description ku'),
                ],
                'keywords' => [
                    'ar' => Setting::get('Seo keywords ar'),
                    'en' => Setting::get('Seo keywords en'),
                    'ku' => Setting::get('Seo keywords ku'),
                ],
                'ogImage' => Setting::get('Seo og image'),
            ],
            'currency' => $this->getCurrencySettings(),
            'refundMaxDays' => (int) (Setting::get('Refund max days') ?? 0),

            // --- Bundled to avoid separate API calls ---
            'heroContent' => Cache::remember('hero_content', 3600, function () {
                $heroSection = \App\Models\Section::where('key', 'home_hero_extra')->first();
                if (! $heroSection) {
                    return [];
                }

                return [
                    'title' => $heroSection->title,
                    'subtitle' => $heroSection->description,
                    'perks' => $heroSection->extra_data['perks'] ?? null,
                    'cta_text' => $heroSection->extra_data['cta_text'] ?? null,
                    'background_image' => $heroSection->extra_data['background_image'] ?? null,
                ];
            }),
            'footerPages' => Cache::remember('footer_pages', 3600, fn () => Page::query()->where('is_published', true)->where('show_in_footer', true)
                ->orderBy('sort_order')->get(['slug'])
                ->map(fn ($p) => ['slug' => $p->slug])->values()->all()
            ),
            'otherPages' => Cache::remember('other_pages', 3600, fn () => Page::query()->where('is_published', true)->where('show_in_header', false)->where('show_in_footer', false)
                ->orderBy('sort_order')->get(['slug', 'title'])
                ->map(fn ($p) => ['slug' => $p->slug, 'title' => $p->title])->values()->all()
            ),
        ];
    }

    /**
     * Get currency settings.
     */
    private function getCurrencySettings(): array
    {
        // Fallback to settings
        return [
            'code' => Setting::get('Currency', 'USD'),
            'symbol' => Setting::get('Currency symbol', '$'),
        ];
    }
}
