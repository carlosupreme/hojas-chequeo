<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Spatie\Permission\Exceptions\RoleDoesNotExist;

class LoginSelectionController extends Controller
{
    public function index()
    {
        if (! Auth::check()) {
            try {
                $operadores = User::role('Operador')->get();
            } catch (RoleDoesNotExist $e) {
                $operadores = collect();
            }

            return view('auth.login-selection', compact('operadores'));
        }

        $user = Auth::user();

        if ($user->isAdmin()) {
            return redirect('/admin');
        }

        if ($user->isSupervisor()) {
            return redirect('/supervisor');
        }

        return redirect('/operador');
    }

    public function login(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'password' => 'required|string',
        ]);

        $user = User::with(['roles'])->find($request->user_id);

        if (Auth::attempt(['email' => $user->email, 'password' => $request->password], true)) {
            $request->session()->regenerate();

            $redirectUrl = '/operador';
            if ($user->isAdmin()) {
                $redirectUrl = '/admin';
            } elseif ($user->isSupervisor()) {
                $redirectUrl = '/supervisor';
            }

            if ($request->wantsJson()) {
                return response()->json(['redirect' => $redirectUrl]);
            }

            return redirect()->intended($redirectUrl);
        }

        throw ValidationException::withMessages([
            'password' => ['La contraseña es incorrecta.'],
        ]);
    }
}
