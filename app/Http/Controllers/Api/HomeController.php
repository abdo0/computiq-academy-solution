<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\HomePageService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Get all home page data in a single request.
     */
    public function index(Request $request): JsonResponse
    {
        $locale = $request->header('Accept-Language', 'ar');

        $homePageService = app(HomePageService::class);
        $data = $homePageService->getInitialData($locale);

        // Include hero content and testimonials for backward compatibility if needed,
        // or just let frontend rely on sections, but since Home.tsx might need them structured:
        
        // We can also fetch the exact things Home.tsx expects via the service.
        return response()->success($data, 'Home page data retrieved successfully');
    }
}
