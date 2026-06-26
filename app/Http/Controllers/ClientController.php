<?php

namespace App\Http\Controllers;

use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $ownerId = $request->user()->dataOwnerId();
        $clients = Client::where('user_id', $ownerId)
            ->withCount('transactions')
            ->orderBy('name')
            ->get();

        return view('clients.index', compact('clients'));
    }

    public function store(Request $request)
    {
        $this->authorize('create', Client::class);

        $data = $this->validateClient($request);
        $data['user_id'] = $request->user()->dataOwnerId();
        Client::create($data);

        return redirect()->route('clients.index')
            ->with('success', 'Cliente cadastrado com sucesso!');
    }

    public function update(Request $request, Client $client)
    {
        $this->authorize('update', $client);

        $client->update($this->validateClient($request));

        return redirect()->route('clients.index')
            ->with('success', 'Cliente atualizado com sucesso!');
    }

    public function destroy(Request $request, Client $client)
    {
        $this->authorize('delete', $client);

        if ($client->transactions()->exists()) {
            return back()->with('error', 'Não é possível excluir um cliente com lançamentos vinculados.');
        }

        $client->delete();

        return redirect()->route('clients.index')
            ->with('success', 'Cliente excluído com sucesso!');
    }

    private function validateClient(Request $request): array
    {
        $data = $request->validate([
            'name' => 'required|string|max:150',
            'document_type' => 'nullable|in:CPF,CNPJ',
            'document' => 'nullable|string|max:18',
            'zip_code' => 'nullable|string|max:9',
            'street' => 'nullable|string|max:150',
            'number' => 'nullable|string|max:20',
            'complement' => 'nullable|string|max:100',
            'neighborhood' => 'nullable|string|max:100',
            'city' => 'nullable|string|max:100',
            'state' => 'nullable|string|max:2',
        ], [
            'name.required' => 'O nome do cliente é obrigatório.',
        ]);

        if (! empty($data['document']) && ! empty($data['document_type'])) {
            $digits = preg_replace('/\D/', '', $data['document']);
            $expected = $data['document_type'] === 'CPF' ? 11 : 14;
            if (strlen($digits) !== $expected) {
                throw \Illuminate\Validation\ValidationException::withMessages([
                    'document' => $data['document_type'] === 'CPF'
                        ? 'Informe um CPF válido com 11 dígitos.'
                        : 'Informe um CNPJ válido com 14 dígitos.',
                ]);
            }
            $data['document'] = $digits;
        }

        if (! empty($data['zip_code'])) {
            $data['zip_code'] = preg_replace('/\D/', '', $data['zip_code']);
        }

        return $data;
    }
}
