<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class HomeController extends Controller
{
    public function index()
    {
        return view('welcome');
    }

    public function changeLanguage(string $locale): RedirectResponse
    {
        if (in_array($locale, appLocales())) {
            session()->put('Locale', $locale);
            app()->setLocale($locale);
        }

        return redirect()->back();
    }
}
