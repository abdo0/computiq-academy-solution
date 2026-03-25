<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;

class ArticleResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => (string) $this->id,
            'slug' => $this->slug,
            'title' => $this->title,
            'excerpt' => $this->excerpt,
            'content' => $this->content,
            'categoryId' => $this->article_category_id ? (string) $this->article_category_id : null,
            'category' => $this->category?->name ?? '',
            'authorId' => $this->author_id ? (string) $this->author_id : null,
            'authorName' => $this->author?->name ?? '',
            'featuredImageUrl' => $this->getFirstMediaUrl('featured_image')
                ?: ($this->featured_image ? Storage::url($this->featured_image) : 'https://picsum.photos/seed/article/1200/630'),
            'contentImageUrl' => 'https://picsum.photos/seed/article-content/800/600',
            'publishedAt' => $this->published_at?->toIso8601String(),
            'isPublished' => (bool) $this->is_published,
            'createdAt' => $this->created_at?->toIso8601String(),
            'updatedAt' => $this->updated_at?->toIso8601String(),
        ];
    }
}
