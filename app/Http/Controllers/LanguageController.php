<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;

class LanguageController extends Controller
{
    /**
     * Altera o idioma da sessão.
     */
    public function switch(string $locale): RedirectResponse
    {
        if (in_array($locale, ['en', 'pt_BR'])) {
            session()->put('locale', $locale);
        }

        return redirect()->back();
    }
}
