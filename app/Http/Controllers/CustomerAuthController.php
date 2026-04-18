<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CustomerAuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'cpf' => ['required', 'string', 'regex:/^\d{3}\.\d{3}\.\d{3}\-\d{2}$|^\d{11}$/'],
            'name' => 'nullable|string|max:255',
            'phone' => ['nullable', 'string', 'max:20', 'regex:/^\(?\d{2}\)?[\s-]?\d{4,5}-?\d{4}$/'],
        ]);

        $cpf = preg_replace('/[^0-9]/', '', $request->cpf);

        $customer = Customer::where('cpf', $cpf)->first();

        if (!$customer) {
            if (!$request->filled('name') || !$request->filled('phone')) {
                return back()->with('needs_registration', true);
            }

            $customer = Customer::create([
                'cpf' => $cpf,
                'name' => strip_tags($request->name),
                'phone' => preg_replace('/[^0-9]/', '', $request->phone),
            ]);
        }

        Auth::guard('customer')->login($customer);

        return redirect()->intended(route('customer.dashboard'))->with('success', 'Bem-vindo de volta!');
    }
}