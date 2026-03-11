<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PageController extends Controller
{
    public function about()
    {
        return view('pages.about');
    }

    public function privacy()
    {
        return view('pages.privacy');
    }

    public function terms()
    {
        return view('pages.terms');
    }

    public function advertise()
    {
        return view('pages.advertise');
    }

    public function contact(Request $request)
    {
        if ($request->isMethod('POST')) {
            // In a real app, send an email here
            return back()->with('success', 'Thank you! Your message has been received. We\'ll respond within 24 hours.');
        }
        return view('pages.contact');
    }
}
