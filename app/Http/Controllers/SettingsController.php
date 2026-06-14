<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        return view('settings.index');
    }

    public function updateCurrency(Request $request)
    {
        $request->validate([
            'currency' => 'required|in:BRL,USD,EUR,GBP',
        ]);

        $user = auth()->user();
        $user->currency = $request->currency;
        $user->save();

        return redirect()->back()->with('success', __('Moeda atualizada com sucesso.'));
    }
}
