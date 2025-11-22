<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthLoginCustomController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login'); 
    }

    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|email', 
            'password' => 'required',
            'remember' => 'nullable|boolean',
        ]);

        $user = User::where('email', $request->email)->first(); 

        if ($user && Hash::check($request->password, $user->password)) {
            Auth::login($user, $request->remember);
            return redirect()->intended(route('home')); 
        }

        return back()->withErrors([
            'email' => 'These credentials do not match our records.', 
        ])->onlyInput('email');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/');
    }
}
