<?php

namespace App\Http\Controllers;

use App\Models\Genre;
use App\Models\Surface;
use App\Models\Technique;
use App\Models\Work;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        return view('public.home');
    }

    public function gallery(Request $request): View
    {
        $query = Work::query()
            ->with(['technique', 'genre', 'surface'])
            ->where('is_published', true);

        $techniqueId = $request->query('technique');
        $genreId = $request->query('genre');
        $surfaceId = $request->query('surface');
        $year = $request->query('year');

        if ($techniqueId) {
            $query->where('technique_id', $techniqueId);
        }

        if ($genreId) {
            $query->where('genre_id', $genreId);
        }

        if ($surfaceId) {
            $query->where('surface_id', $surfaceId);
        }

        if ($year) {
            $query->where('year', $year);
        }

        $query->orderBy('sort_order')->orderByDesc('created_at')->orderByDesc('id');

        $works = $query->paginate(18)->withQueryString();

        $techniques = Technique::orderBy('name_en')->get();
        $genres = Genre::orderBy('name_en')->get();
        $surfaces = Surface::orderBy('name_en')->get();
        $years = Work::where('is_published', true)
            ->whereNotNull('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        $viewData = [
            'works' => $works,
            'techniques' => $techniques,
            'genres' => $genres,
            'surfaces' => $surfaces,
            'years' => $years,
            'locale' => app()->getLocale(),
            'filters' => [
                'technique' => $techniqueId,
                'genre' => $genreId,
                'surface' => $surfaceId,
                'year' => $year,
            ],
        ];

        if ($request->ajax()) {
            return view('public.partials.gallery-results', $viewData);
        }

        return view('public.gallery', $viewData);
    }

    public function galleryShow(string $locale, Request $request, Work $work): View
    {
        if (! $work->is_published) {
            abort(404);
        }

        $techniqueId = $request->query('technique');
        $genreId = $request->query('genre');
        $surfaceId = $request->query('surface');
        $year = $request->query('year');

        $listQuery = Work::query()->where('is_published', true);
        if ($techniqueId) {
            $listQuery->where('technique_id', $techniqueId);
        }
        if ($genreId) {
            $listQuery->where('genre_id', $genreId);
        }
        if ($surfaceId) {
            $listQuery->where('surface_id', $surfaceId);
        }
        if ($year) {
            $listQuery->where('year', $year);
        }

        $orderedIds = $listQuery
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->pluck('id')
            ->values();

        $currentIndex = $orderedIds->search($work->id);
        $prevId = null;
        $nextId = null;
        if ($currentIndex !== false) {
            $prevId = $orderedIds[$currentIndex - 1] ?? null;
            $nextId = $orderedIds[$currentIndex + 1] ?? null;
        }

        $work->load(['technique', 'genre', 'surface', 'images']);

        $imageUrls = $work->imageUrls();
        $hasRealImages = $work->hasRealImages();
        $moreWorksQuery = Work::query()
            ->with(['technique', 'genre', 'surface'])
            ->where('is_published', true)
            ->where('id', '!=', $work->id);

        if ($work->genre_id) {
            $moreWorksQuery->where('genre_id', $work->genre_id);
        }

        $moreWorks = $moreWorksQuery
            ->orderBy('sort_order')
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->limit(6)
            ->get();

        return view('public.work', [
            'work' => $work,
            'imageUrls' => $imageUrls,
            'hasRealImages' => $hasRealImages,
            'moreWorks' => $moreWorks,
            'prevId' => $prevId,
            'nextId' => $nextId,
            'filters' => [
                'technique' => $techniqueId,
                'genre' => $genreId,
                'surface' => $surfaceId,
                'year' => $year,
            ],
        ]);
    }

    public function contacts(): View
    {
        return view('public.contacts');
    }
}
