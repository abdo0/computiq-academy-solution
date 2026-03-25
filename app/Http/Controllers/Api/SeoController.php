<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\SeoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SeoController extends Controller
{
    public function __construct(
        protected SeoService $seoService
    ) {}

    /**
     * Get SEO data for a specific page/resource.
     */
    public function getSeo(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', 'string'],
            'slug' => ['nullable', 'string'],
        ]);

        $lang = $request->header('Accept-Language', 'en');
        $type = $validated['type'];
        $slug = $validated['slug'] ?? null;

        $seoData = $this->seoService->getSeoData($type, $slug, $lang);

        return response()->json([
            'success' => true,
            'data' => $seoData,
            'message' => __('SEO data retrieved successfully')
        ]);
    }

    // Specific endpoints (optional if we just use the ?type= & slug= endpoint)
    // We can just keep the main one or add wrappers like spc if they prefer RESTful endpoints.
    
    public function home(Request $request): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'en');
        $seoData = $this->seoService->getSeoData('home', null, $lang);
        return response()->json(['success' => true, 'data' => $seoData, 'message' => __('SEO retrieved')]);
    }

    public function article(Request $request, string $slug): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'en');
        $seoData = $this->seoService->getSeoData('article', $slug, $lang);
        return response()->json(['success' => true, 'data' => $seoData, 'message' => __('SEO retrieved')]);
    }

    public function campaign(Request $request, string $slug): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'en');
        $seoData = $this->seoService->getSeoData('campaign', $slug, $lang);
        return response()->json(['success' => true, 'data' => $seoData, 'message' => __('SEO retrieved')]);
    }

    public function page(Request $request, string $slug): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'en');
        $seoData = $this->seoService->getSeoData('page', $slug, $lang);
        return response()->json(['success' => true, 'data' => $seoData, 'message' => __('SEO retrieved')]);
    }

    public function handleType(Request $request, string $type): JsonResponse
    {
        $lang = $request->header('Accept-Language', 'en');
        $seoData = $this->seoService->getSeoData($type, null, $lang);
        return response()->json(['success' => true, 'data' => $seoData, 'message' => __('SEO retrieved')]);
    }
}
