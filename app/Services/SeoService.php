<?php

namespace App\Services;

use App\Models\Article;
use App\Models\Campaign;
use App\Models\Page;
use App\Models\Setting;
use Illuminate\Support\Str;

class SeoService
{
    /**
     * Generate URL with locale prefix (only for non-English locales).
     */
    private function localeUrl(string $path, string $lang): string
    {
        $path = ltrim($path, '/');
        // English (default) has no prefix
        if ($lang === 'en') {
            return url($path ? "/{$path}" : '/');
        }
        return url("/{$lang}/{$path}");
    }

    /**
     * Get SEO data for a specific page/resource.
     */
    public function getSeoData(string $type, ?string $slug = null, ?string $lang = 'en'): array
    {
        return match ($type) {
            'home' => $this->getHomeSeo($lang),
            'about' => $this->getStaticPageSeo('about', __('About Us', [], $lang), __('Learn more about our mission and vision', [], $lang), $lang),
            'how-it-works' => $this->getStaticPageSeo('how-it-works', __('How It Works', [], $lang), __('Learn how our platform works', [], $lang), $lang),
            'guide' => $this->getStaticPageSeo('guide', __('Guide', [], $lang), __('Comprehensive guide for our users', [], $lang), $lang),
            'success-stories' => $this->getStaticPageSeo('success-stories', __('Success Stories', [], $lang), __('Read inspiring success stories from our community', [], $lang), $lang),
            'page' => $this->getPageSeo($slug, $lang),
            'campaigns' => $this->getCampaignsSeo($lang),
            'campaign' => $this->getCampaignSeo($slug, $lang),
            'volunteer' => $this->getStaticPageSeo('volunteer', __('Volunteer', [], $lang), __('Join us as a volunteer and make a difference', [], $lang), $lang),
            'whitelist' => $this->getStaticPageSeo('whitelist', __('Whitelist', [], $lang), __('Our trusted and verified organizations', [], $lang), $lang),
            'contact' => $this->getContactSeo($lang),
            'blog' => $this->getBlogSeo($lang),
            'article' => $this->getArticleSeo($slug, $lang),
            'faq' => $this->getFaqSeo($lang),
            'zakat' => $this->getStaticPageSeo('zakat', __('Zakat Calculator', [], $lang), __('Calculate your Zakat easily and accurately', [], $lang), $lang),
            
            // Auth / General
            'login' => $this->getStaticPageSeo('login', __('Login', [], $lang), __('Login to your account', [], $lang), $lang),
            'signup' => $this->getStaticPageSeo('signup', __('Sign Up', [], $lang), __('Create a new account', [], $lang), $lang),
            'forgot-password' => $this->getStaticPageSeo('forgot-password', __('Forgot Password', [], $lang), __('Reset your password', [], $lang), $lang),
            'reset-password' => $this->getStaticPageSeo('reset-password', __('Reset Password', [], $lang), __('Set a new password', [], $lang), $lang),
            'verify-email' => $this->getStaticPageSeo('verify-email', __('Verify Email', [], $lang), __('Verify your email address', [], $lang), $lang),
            
            // Dashboards
            'student-dashboard' => $this->getStaticPageSeo('student/dashboard', __('Student Dashboard', [], $lang), __('Manage your courses remotely', [], $lang), $lang),
            'instructor-dashboard' => $this->getStaticPageSeo('instructor/dashboard', __('Instructor Dashboard', [], $lang), __('Manage your courses and profile', [], $lang), $lang),
            
            default => $this->getDefaultSeo($lang),
        };
    }

    /**
     * Get home page SEO.
     */
    private function getHomeSeo(string $lang): array
    {
        $siteName = $this->getSiteName($lang);

        return [
            'title' => Setting::get("seo_title_{$lang}", "{$siteName} | ".__('Computiq Academy', [], $lang)),
            'description' => Setting::get("seo_description_{$lang}", __('The best platform for learning and courses', [], $lang)),
            'keywords' => Setting::get("seo_keywords_{$lang}", __('learning,courses,academy,computiq', [], $lang)),
            'og_image' => Setting::get('seo_og_image', asset('logo.png')),
            'og_type' => 'website',
            'canonical' => $this->localeUrl('/', $lang),
            'structured_data' => $this->getOrganizationStructuredData($lang),
        ];
    }

    /**
     * Get static page SEO.
     */
    private function getStaticPageSeo(string $path, string $title, string $description, string $lang): array
    {
        $siteName = $this->getSiteName($lang);

        return [
            'title' => "{$title} | {$siteName}",
            'description' => $description,
            'keywords' => $this->extractKeywords("{$title} {$description}"),
            'og_image' => Setting::get('seo_og_image', asset('logo.png')),
            'og_type' => 'website',
            'canonical' => $this->localeUrl($path, $lang),
        ];
    }

    /**
     * Get article SEO.
     */
    private function getArticleSeo(?string $slug, string $lang): array
    {
        if (! $slug) {
            return $this->getBlogSeo($lang);
        }

        $article = Article::where('slug', $slug)
            ->where('is_published', true)
            ->with(['category'])
            ->first();

        if (! $article) {
            return $this->getDefaultSeo($lang);
        }

        $siteName = $this->getSiteName($lang);
        $title = $article->getTranslation('title', $lang);
        
        // Use an excerpt or crop from content
        $description = Str::limit(strip_tags($article->getTranslation('content', $lang)), 160);
        $image = $article->getFirstMediaUrl('featured_image') ?: Setting::get('seo_og_image', asset('logo.png'));

        return [
            'title' => "{$title} | {$siteName}",
            'description' => $description,
            'keywords' => $this->extractKeywords($title.' '.$description),
            'og_image' => $image,
            'og_type' => 'article',
            'canonical' => $this->localeUrl("blog/{$slug}", $lang),
            'article_published_time' => $article->created_at?->toIso8601String(),
            'article_modified_time' => $article->updated_at->toIso8601String(),
            'article_author' => 'Computiq Team',
            'article_section' => $article->category?->getTranslation('name', $lang) ?? 'General',
            'structured_data' => $this->getArticleStructuredData($article, $lang),
        ];
    }

    /**
     * Get Campaigns Listing SEO.
     */
    private function getCampaignsSeo(string $lang): array
    {
        $siteName = $this->getSiteName($lang);

        return [
            'title' => __('Explore Courses', [], $lang)." | {$siteName}",
            'description' => __('Discover and learn from verified courses and make a difference.', [], $lang),
            'keywords' => __('courses,learning,education,academy', [], $lang),
            'og_image' => Setting::get('seo_og_image', asset('logo.png')),
            'og_type' => 'website',
            'canonical' => $this->localeUrl('campaigns', $lang),
        ];
    }

    /**
     * Get single Campaign SEO.
     */
    private function getCampaignSeo(?string $slug, string $lang): array
    {
        if (! $slug) {
            return $this->getCampaignsSeo($lang);
        }

        $campaign = Campaign::where('slug', $slug)
            ->whereIn('status', ['active', 'completed'])
            ->with('organization')
            ->first();

        if (! $campaign) {
            return $this->getDefaultSeo($lang);
        }

        $siteName = $this->getSiteName($lang);
        $title = $campaign->getTranslation('title', $lang);
        $description = Str::limit(strip_tags($campaign->getTranslation('description', $lang) ?: $campaign->getTranslation('story', $lang)), 160);
        $image = $campaign->getFirstMediaUrl('cover_image') ?: Setting::get('seo_og_image', asset('logo.png'));

        return [
            'title' => "{$title} | {$siteName}",
            'description' => $description,
            'keywords' => $this->extractKeywords($title.' '.$description),
            'og_image' => $image,
            'og_type' => 'website',
            'canonical' => $this->localeUrl("campaigns/{$slug}", $lang),
            'structured_data' => [
                '@context' => 'https://schema.org',
                '@type' => 'Project', // or CharitableOrganization / specific schema
                'name' => $title,
                'description' => $description,
                'image' => url($image),
                'organizer' => [
                    '@type' => 'Organization',
                    'name' => $campaign->organization?->getTranslation('name', $lang) ?? $siteName,
                ]
            ],
        ];
    }

    /**
     * Get page SEO.
     */
    private function getPageSeo(?string $slug, string $lang): array
    {
        if (! $slug) {
            return $this->getDefaultSeo($lang);
        }

        $page = Page::where('slug', $slug)
            ->where('is_active', true)
            ->first();

        if (! $page) {
            return $this->getDefaultSeo($lang);
        }

        $siteName = $this->getSiteName($lang);
        $title = $page->getTranslation('title', $lang);
        $description = Str::limit(strip_tags($page->getTranslation('content', $lang)), 160);

        return [
            'title' => "{$title} | {$siteName}",
            'description' => $description,
            'keywords' => $this->extractKeywords($title.' '.$description),
            'og_image' => Setting::get('seo_og_image', asset('logo.png')),
            'og_type' => 'website',
            'canonical' => $this->localeUrl("page/{$slug}", $lang),
            'structured_data' => $this->getWebPageStructuredData($page, $lang),
        ];
    }

    /**
     * Get blog listing SEO.
     */
    private function getBlogSeo(string $lang): array
    {
        $siteName = $this->getSiteName($lang);

        return [
            'title' => __('News & Updates', [], $lang)." | {$siteName}",
            'description' => __('Stay updated with our latest news and educational activities.', [], $lang),
            'keywords' => __('blog,articles,news,education,updates', [], $lang),
            'og_image' => Setting::get('seo_og_image', asset('logo.png')),
            'og_type' => 'website',
            'canonical' => $this->localeUrl('blog', $lang),
        ];
    }

    /**
     * Get contact page SEO.
     */
    private function getContactSeo(string $lang): array
    {
        $siteName = $this->getSiteName($lang);

        return [
            'title' => __('Contact Us', [], $lang)." | {$siteName}",
            'description' => __('Get in touch with us for inquiries and support.', [], $lang),
            'keywords' => __('contact,support,help,inquiries', [], $lang),
            'og_image' => Setting::get('seo_og_image', asset('logo.png')),
            'og_type' => 'website',
            'canonical' => $this->localeUrl('contact', $lang),
        ];
    }

    /**
     * Get FAQ page SEO.
     */
    private function getFaqSeo(string $lang): array
    {
        $siteName = $this->getSiteName($lang);

        return [
            'title' => __('Frequently Asked Questions', [], $lang)." | {$siteName}",
            'description' => __('Find answers to common questions about our platform and courses.', [], $lang),
            'keywords' => __('faq,questions,answers,help,support', [], $lang),
            'og_image' => Setting::get('seo_og_image', asset('logo.png')),
            'og_type' => 'website',
            'canonical' => $this->localeUrl('faq', $lang),
            'structured_data' => $this->getFaqStructuredData($lang),
        ];
    }

    /**
     * Get FAQ structured data (JSON-LD) with QAPage schema.
     */
    private function getFaqStructuredData(string $lang): array
    {
        $siteName = $this->getSiteName($lang);
        $faqs = \App\Models\FAQ::where('is_active', true)
            ->orderBy('sort_order', 'asc')
            ->get();

        $mainEntity = [
            '@type' => 'Question',
            'name' => __('Frequently Asked Questions', [], $lang),
            'acceptedAnswer' => [
                '@type' => 'Answer',
                'text' => __('Find answers to common questions about our platform and courses.', [], $lang),
            ],
        ];

        // Add individual Q&A pairs
        $qaPairs = $faqs->map(function ($faq) use ($lang) {
            return [
                '@type' => 'Question',
                'name' => strip_tags($faq->getTranslation('question', $lang)),
                'acceptedAnswer' => [
                    '@type' => 'Answer',
                    'text' => strip_tags($faq->getTranslation('answer', $lang)),
                ],
            ];
        })->toArray();

        return [
            '@context' => 'https://schema.org',
            '@type' => 'FAQPage',
            'mainEntity' => array_merge([$mainEntity], $qaPairs),
            'name' => __('Frequently Asked Questions', [], $lang),
            'description' => __('Find answers to common questions about our platform and courses.', [], $lang),
            'url' => $this->localeUrl('faq', $lang),
            'inLanguage' => $lang,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $siteName,
                'url' => $this->localeUrl('/', $lang),
            ],
        ];
    }

    /**
     * Get default SEO.
     */
    private function getDefaultSeo(string $lang): array
    {
        $siteName = $this->getSiteName($lang);

        return [
            'title' => $siteName.' | '.__('Computiq Academy', [], $lang),
            'description' => __('The best platform for learning and courses', [], $lang),
            'keywords' => __('learning,courses,academy', [], $lang),
            'og_image' => Setting::get('seo_og_image', asset('logo.png')),
            'og_type' => 'website',
            'canonical' => $this->localeUrl('/', $lang),
        ];
    }

    /**
     * Get site name in specific language.
     */
    private function getSiteName(string $lang): string
    {
        $companyName = Setting::get('site_name');

        if (is_string($companyName)) {
            $decoded = json_decode($companyName, true);
            if (is_array($decoded)) {
                return $decoded[$lang] ?? $decoded['en'] ?? 'Computiq';
            }

            return $companyName;
        }

        if (is_array($companyName)) {
            return $companyName[$lang] ?? $companyName['en'] ?? 'Computiq';
        }

        return 'Computiq';
    }

    /**
     * Extract keywords from text.
     */
    private function extractKeywords(string $text, int $limit = 10): string
    {
        // Remove HTML tags
        $text = strip_tags($text);

        // Get common words to exclude
        $stopWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by', 'is', 'are', 'was', 'were', 'be', 'been', 'being', 'have', 'has', 'had', 'do', 'does', 'did', 'will', 'would', 'should', 'could', 'may', 'might', 'must', 'can'];

        // Split into words
        $words = str_word_count(strtolower($text), 1);

        // Filter out stop words and short words
        $keywords = array_filter($words, function ($word) use ($stopWords) {
            return strlen($word) > 3 && ! in_array($word, $stopWords);
        });

        // Count frequency
        $wordCount = array_count_values($keywords);
        arsort($wordCount);

        // Get top keywords
        $topKeywords = array_slice(array_keys($wordCount), 0, $limit);

        return implode(',', $topKeywords);
    }

    /**
     * Get Organization structured data (JSON-LD).
     */
    private function getOrganizationStructuredData(string $lang): array
    {
        $siteName = $this->getSiteName($lang);
        $siteUrl = $this->localeUrl('/', $lang);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Organization',
            'name' => $siteName,
            'url' => $siteUrl,
            'logo' => url(Setting::get('seo_og_image', 'logo.png')),
            'description' => Setting::get("seo_description_{$lang}", __('The best platform for learning', [], $lang)),
            'contactPoint' => [
                '@type' => 'ContactPoint',
                'telephone' => Setting::get('contact_phone', ''),
                'contactType' => 'customer service',
                'email' => Setting::get('contact_email', ''),
            ],
            'sameAs' => array_filter([
                Setting::get('social_facebook'),
                Setting::get('social_twitter'),
                Setting::get('social_instagram'),
                Setting::get('social_youtube'),
                Setting::get('social_linkedin'),
                Setting::get('social_tiktok'),
                Setting::get('social_snapchat'),
            ]),
        ];
    }

    /**
     * Get Article structured data (JSON-LD).
     */
    private function getArticleStructuredData(Article $article, string $lang): array
    {
        $siteName = $this->getSiteName($lang);
        $image = $article->getFirstMediaUrl('featured_image') ?: Setting::get('seo_og_image', asset('logo.png'));

        return [
            '@context' => 'https://schema.org',
            '@type' => 'Article',
            'headline' => $article->getTranslation('title', $lang),
            'description' => Str::limit(strip_tags($article->getTranslation('content', $lang)), 200),
            'image' => url($image),
            'datePublished' => $article->created_at?->toIso8601String(),
            'dateModified' => $article->updated_at->toIso8601String(),
            'author' => [
                '@type' => 'Organization',
                'name' => 'Computiq Team',
            ],
            'publisher' => [
                '@type' => 'Organization',
                'name' => $siteName,
                'logo' => [
                    '@type' => 'ImageObject',
                    'url' => url(Setting::get('seo_og_image', 'logo.png')),
                ],
            ],
            'mainEntityOfPage' => [
                '@type' => 'WebPage',
                '@id' => $this->localeUrl("blog/{$article->slug}", $lang),
            ],
        ];
    }

    /**
     * Get WebPage structured data (JSON-LD).
     */
    private function getWebPageStructuredData(Page $page, string $lang): array
    {
        $siteName = $this->getSiteName($lang);

        return [
            '@context' => 'https://schema.org',
            '@type' => 'WebPage',
            'name' => $page->getTranslation('title', $lang),
            'description' => Str::limit(strip_tags($page->getTranslation('content', $lang)), 200),
            'url' => $this->localeUrl("page/{$page->slug}", $lang),
            'inLanguage' => $lang,
            'isPartOf' => [
                '@type' => 'WebSite',
                'name' => $siteName,
                'url' => $this->localeUrl('/', $lang),
            ],
        ];
    }
}
