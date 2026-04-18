<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Providers\RouteServiceProvider;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Inertia\Response;

class AuthenticatedSessionController extends Controller
{
    /**
     * Display the login view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/Login', [
            'canResetPassword' => Route::has('password.request'),
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        $request->session()->regenerate();

        // Rastrear IP e Localização do Fotógrafo
        $user = Auth::user();
        if ($user) {
            $ip = $request->ip();
            // Ignorar localhost para testes de localização
            if ($ip === '127.0.0.1' || $ip === '::1') {
                $ip = '8.8.8.8'; // IP de exemplo para teste local
            }
            
            $user->last_login_ip = $request->ip();
            
            try {
                $response = \Illuminate\Support\Facades\Http::get("http://ip-api.com/json/{$ip}?fields=status,city");
                if ($response->successful() && $response->json('status') === 'success') {
                    $user->last_login_city = $response->json('city');
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Erro ao obter localização por IP: ' . $e->getMessage());
            }
            
            $user->save();
        }

        return redirect()->intended(RouteServiceProvider::HOME);
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return redirect('/');
    }
}
