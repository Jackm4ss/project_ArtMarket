<?php

namespace Tests\Feature\Commerce;

use App\Models\Article;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ArticleContentTest extends TestCase
{
    use RefreshDatabase;

    public function test_published_articles_are_listed_publicly(): void
    {
        $article = Article::factory()->published()->create([
            'title' => 'Kebangkitan Seni Kontemporer',
        ]);
        Article::factory()->create(['title' => 'Draft Tidak Tampil']);

        $this->get(route('articles.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Public/Articles')
                ->has('articles.data', 1)
                ->where('articles.data.0.slug', $article->slug)
            );
    }

    public function test_draft_or_scheduled_article_show_returns_not_found(): void
    {
        $draft = Article::factory()->create();
        $future = Article::factory()->futurePublished()->create();

        $this->get(route('articles.show', $draft))->assertNotFound();
        $this->get(route('articles.show', $future))->assertNotFound();
    }
}
