<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ArticleResource;
use App\Models\Article;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ArticleController extends Controller
{
    /**
     * Get list of published articles.
     */
    public function index(Request $request): JsonResponse
    {
        $query = Article::query()
            ->published()
            ->with(['category', 'author'])
            ->orderBy('sort_order')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        // Filter by category
        if ($request->filled('category')) {
            $query->where('article_category_id', $request->category);
        }

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title->ar', 'like', "%{$search}%")
                    ->orWhere('title->en', 'like', "%{$search}%")
                    ->orWhere('title->ku', 'like', "%{$search}%")
                    ->orWhere('excerpt->ar', 'like', "%{$search}%")
                    ->orWhere('excerpt->en', 'like', "%{$search}%")
                    ->orWhere('content->ar', 'like', "%{$search}%")
                    ->orWhere('content->en', 'like', "%{$search}%");
            });
        }

        // Pagination
        $perPage = min($request->integer('per_page', 12), 50);
        $articles = $query->paginate($perPage);

        // Set SEO tags using seo() helper for blog listing page
        seo()->setTitle(__('Blog'));
        seo()->setDescription(__('Browse all our blog articles and stay updated with the latest news and updates'));
        seo()->setKeywords(__('blog,articles,news,updates'));
        seo()->setTag('og:image', 'https://picsum.photos/seed/article/1200/630');

        $resourceCollection = ArticleResource::collection($articles);

        return response()->success(
            $resourceCollection->response()->getData(true),
            __('Articles retrieved successfully')
        );
    }

    /**
     * Get a single article by slug.
     */
    public function show(string $slug): JsonResponse
    {
        $article = Article::query()
            ->published()
            ->with(['category', 'author'])
            ->where('slug', $slug)
            ->firstOrFail();

        // Set SEO tags using seo() helper
        seo()->setTitle($article->title);
        seo()->setDescription($article->excerpt ?? $article->title);

        if ($article->category) {
            seo()->setKeywords($article->category->name);
        }

        // Use featured image or default image for SEO
        $ogImage = $article->featured_image
            ? asset('storage/'.$article->featured_image)
            : 'https://picsum.photos/seed/article/1200/630';
        seo()->setTag('og:image', $ogImage);

        return response()->success(
            ['article' => new ArticleResource($article)],
            __('Article retrieved successfully')
        );
    }
}
