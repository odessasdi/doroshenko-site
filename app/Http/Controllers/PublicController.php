<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class PublicController extends Controller
{
    public function home(): View
    {
        return view('public.home');
    }

    public function gallery(): View
    {
        return view('public.gallery');
    }

    public function contacts(): View
    {
        return view('public.contacts');
    }
}
