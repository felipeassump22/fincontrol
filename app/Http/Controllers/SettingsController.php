<?php

namespace App\Http\Controllers;

use App\Models\CompanySetting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $ownerId = auth()->user()->dataOwnerId();
        $company = CompanySetting::firstOrNew(['user_id' => $ownerId]);

        return view('settings.index', compact('company'));
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

    public function updateCompany(Request $request)
    {
        if (! auth()->user()->canManageFinances()) {
            abort(403);
        }

        $data = $request->validate([
            'company_name' => 'nullable|string|max:150',
            'trade_name' => 'nullable|string|max:150',
            'document' => 'nullable|string|max:18',
            'email' => 'nullable|email|max:150',
            'phone' => 'nullable|string|max:20',
            'zip_code' => 'nullable|string|max:9',
            'street' => 'nullable|string|max:150',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
        ]);

        $ownerId = auth()->user()->dataOwnerId();
        CompanySetting::updateOrCreate(
            ['user_id' => $ownerId],
            $data
        );

        return redirect()->back()->with('success', __('Dados da empresa atualizados com sucesso.'));
    }
}
