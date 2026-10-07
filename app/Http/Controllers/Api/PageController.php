<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Page;

/**
 * Static content pages (terms, privacy policy, ...). Public — the app shows
 * these before login too.
 */
class PageController extends Controller
{
    /** GET /api/pages — list of active pages (title + slug, no heavy content). */
    public function index()
    {
        $pages = Page::where('status', 1)
            ->orderBy('title')
            ->get()
            ->map(fn(Page $p) => [
                'id'         => $p->id,
                'title'      => $p->title,
                'slug'       => $p->slug,
                'updated_at' => $p->updated_at,
            ]);

        return response()->json(['status' => true, 'data' => $pages]);
    }

    /** GET /api/pages/{slug} — one page with its content. Accepts id too. */
    public function show(string $slug)
    {
        $page = Page::where('status', 1)
            ->where(fn($q) => $q->where('slug', $slug)->orWhere('id', $slug))
            ->first();

        if (!$page) {
            return response()->json(['status' => false, 'message' => 'Page not found.'], 404);
        }

        return response()->json([
            'status' => true,
            'data'   => [
                'id'         => $page->id,
                'title'      => $page->title,
                'slug'       => $page->slug,
                'content'    => $page->content,     // HTML from the editor
                'updated_at' => $page->updated_at,
            ],
        ]);
    }
}
