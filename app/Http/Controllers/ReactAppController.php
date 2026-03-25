<?php

namespace App\Http\Controllers;

use App\Models\Article;

use App\Models\Page;

class ReactAppController extends Controller
{
    /**
     * Home page
     */
    public function home(): \Illuminate\Http\Response
    {
        $locale = app()->getLocale();

        seo()->setTitle(__('Home'));
        seo()->setDescription(settings('seo_description_'.$locale) ?? settings('seo_description_ar') ?? __('A trusted charitable donation platform'));
        seo()->setKeywords(settings('seo_keywords_'.$locale) ?? settings('seo_keywords_ar') ?? __('donations,charity,nakhwaa'));

        return $this->renderReactApp();
    }

    /**
     * About page
     */
    public function about(): \Illuminate\Http\Response
    {
        $locale = app()->getLocale();

        seo()->setTitle(__('About Us'));
        seo()->setDescription(__('Learn more about our mission and values'));
        seo()->setKeywords(__('about,mission,values'));

        return $this->renderReactApp();
    }



    /**
     * Contact page
     */
    public function contact(): \Illuminate\Http\Response
    {
        seo()->setTitle(__('Contact Us'));
        seo()->setDescription(__('Get in touch with us'));
        seo()->setKeywords(__('contact,reach out,help'));

        return $this->renderReactApp();
    }

    /**
     * Blog listing page
     */
    public function blog(): \Illuminate\Http\Response
    {
        seo()->setTitle(__('Blog'));
        seo()->setDescription(__('Read our latest news and updates'));
        seo()->setKeywords(__('blog,news,updates'));

        return $this->renderReactApp();
    }

    /**
     * Blog post detail page
     */
    public function blogShow(string $slug): \Illuminate\Http\Response
    {
        $article = Article::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $locale = app()->getLocale();
        $title = $article->getTranslation('title', $locale) ?? $article->getTranslation('title', 'ar');
        $description = $article->getTranslation('excerpt', $locale) ?? $article->getTranslation('excerpt', 'ar');

        seo()->setTitle($title);
        seo()->setDescription($description);
        seo()->setKeywords($article->category?->getTranslation('name', $locale) ?? '');
        seo()->setTag('og:image', $article->image ? asset('storage/'.$article->image) : null);

        return $this->renderReactApp();
    }

    /**
     * FAQ page
     */
    public function faq(): \Illuminate\Http\Response
    {
        seo()->setTitle(__('FAQ'));
        seo()->setDescription(__('Frequently asked questions'));
        seo()->setKeywords(__('faq,questions,help'));

        return $this->renderReactApp();
    }



    /**
     * Dynamic page
     */
    public function page(string $slug): \Illuminate\Http\Response
    {
        $page = Page::where('slug', $slug)
            ->where('is_published', true)
            ->firstOrFail();

        $locale = app()->getLocale();
        $title = $page->getTranslation('title', $locale) ?? $page->getTranslation('title', 'ar');
        $description = $page->getTranslation('content', $locale) ?? $page->getTranslation('content', 'ar');
        $description = strip_tags($description);
        $description = mb_substr($description, 0, 160);

        seo()->setTitle($title);
        seo()->setDescription($description);

        return $this->renderReactApp();
    }

    /**
     * Render React app with SEO data and translations
     */
    protected function renderReactApp(): \Illuminate\Http\Response
    {
        $locale = app()->getLocale();
        $translationPath = lang_path("{$locale}.json");
        $translations = [];

        if (file_exists($translationPath)) {
            $translations = json_decode(file_get_contents($translationPath), true) ?: [];
        } else {
            // Fallback
            $fallbackPath = lang_path('ar.json');
            if (file_exists($fallbackPath)) {
                $translations = json_decode(file_get_contents($fallbackPath), true) ?: [];
            }
        }

        return response()->view('react-app', [
            'locale' => $locale,
            'translations' => $translations,
            'seo' => [
                'title' => \App\Helpers\SEO::getTag('title'),
                'description' => \App\Helpers\SEO::getTag('description'),
                'keywords' => \App\Helpers\SEO::getTag('keywords'),
                'og_image' => \App\Helpers\SEO::getTag('og:image'),
            ],
        ]);
    }
}
