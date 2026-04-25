<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Article;
use Inertia\Inertia;
use Inertia\Response;

class ArticleController extends Controller
{
    public function index(): Response
    {
        $articles = Article::query()
            ->published()
            ->with('author:id,name')
            ->orderByDesc('published_at')
            ->orderByDesc('id')
            ->cursorPaginate(12);

        return Inertia::render('Public/Articles', [
            'articles' => $articles,
        ]);
    }

    public function show(Article $article): Response
    {
        abort_unless($article->isPublished(), 404);

        return Inertia::render('Public/ArticleShow', [
            'article' => $article->load('author:id,name'),
        ]);
    }
}
