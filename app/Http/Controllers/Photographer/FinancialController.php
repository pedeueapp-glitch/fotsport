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

        $withdrawal_fee = (float) \App\Models\Setting::where('key', 'withdrawal_fee')->value('value') ?: 15.0;

        return Inertia::render('Photographer/Financial', [
            'balance' => (float) $user->balance,
            'pix_key' => $user->pix_key,
            'document' => $user->document,
            'withdrawals' => $withdrawals,
            'sales' => $sales,
            'withdrawal_fee_percentage' => $withdrawal_fee
        ]);
    }

    public function updatePix(Request $request)
    {
        $request->validate([
            'pix_key' => 'required|string|max:255',
            'pix_key_type' => 'required|string|in:cpf,email,phone,evp',
            'document' => ['required', 'string', 'max:20', 'regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$|^\d{11}$|^\d{2}\.\d{3}\.\d{3}\/\d{4}\-\d{2}$|^\d{14}$/'],
        ]);

        $user = auth()->user();
        $user->pix_key = strip_tags($request->pix_key);
        $user->pix_key_type = $request->pix_key_type;
        $user->document = preg_replace('/[^0-9]/', '', $request->document);
        $user->save();

        return back()->with('success', 'Chave PIX atualizada com sucesso.');
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:0.01|max:1000000'
        ]);

        $user = auth()->user();
        $amount = (float) $request->amount;

        if ($user->balance < $amount) {
            return back()->withErrors(['amount' => 'Saldo insuficiente']);
        }

        if (!$user->pix_key || !$user->pix_key_type) {
            return back()->withErrors(['pix_key' => 'Você precisa configurar a sua chave PIX e o tipo antes de sacar.']);
        }

        // Deduct balance from photographer
        $user->balance -= $amount;
        $user->save();

        // Get dynamic fee from DB
        $feePercentage = (float) \App\Models\Setting::where('key', 'withdrawal_fee')->value('value') ?: 15.0;
        
        $fee = $amount * ($feePercentage / 100);
        $net = $amount - $fee;

        Withdrawal::create([
            'user_id' => $user->id,
            'request_amount' => $amount,
            'fee_amount' => $fee,
            'net_amount' => $net,
            'pix_key' => $user->pix_key,
            'pix_key_type' => $user->pix_key_type,
            'status' => 'pending'
        ]);

        return back()->with('success', 'Saque solicitado com sucesso! Aguardando o SuperAdmin aprovar.');
    }
}
