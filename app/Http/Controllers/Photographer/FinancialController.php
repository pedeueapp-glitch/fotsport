<?php

namespace App\Http\Controllers\Photographer;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Withdrawal;

class FinancialController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $withdrawals = $user->withdrawals()->latest()->get();

        $sales = \App\Models\Purchase::where('status', 'approved')
            ->whereHas('photo.event', function ($q) use ($user) {
                $q->where('user_id', $user->id);
            })
            ->with('photo')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Photographer/Financial', [
            'balance' => (float) $user->balance,
            'pix_key' => $user->pix_key,
            'document' => $user->document,
            'withdrawals' => $withdrawals,
            'sales' => $sales
        ]);
    }

    public function updatePix(Request $request)
    {
        $request->validate([
            'pix_key' => 'required|string|max:255',
            'document' => ['required', 'string', 'max:20', 'regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$|^\d{11}$|^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$|^\d{14}$/'], // Valid CPF or CNPJ format
        ]);

        $user = auth()->user();
        $user->pix_key = strip_tags($request->pix_key);
        $user->document = preg_replace('/[^0-9]/', '', $request->document);
        $user->save();

        return back()->with('success', 'Chave PIX atualizada com sucesso.');
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:10|max:1000000'
        ]);

        $user = auth()->user();
        $amount = (float) $request->amount;

        if ($user->balance < $amount) {
            return back()->withErrors(['amount' => 'Saldo insuficiente']);
        }

        if (!$user->pix_key || !$user->document) {
            return back()->withErrors(['pix_key' => 'Você precisa configurar a sua chave PIX antes de sacar.']);
        }

        // Deduct balance from photographer
        $user->balance -= $amount;
        $user->save();

        // 15% Platform fee
        $fee = $amount * 0.15;
        $net = $amount - $fee;

        Withdrawal::create([
            'user_id' => $user->id,
            'request_amount' => $amount,
            'fee_amount' => $fee,
            'net_amount' => $net,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Saque solicitado com sucesso! Aguardando o SuperAdmin aprovar.');
    }
}
