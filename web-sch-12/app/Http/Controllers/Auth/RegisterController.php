<?php
namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class RegisterController extends Controller
{

    use RegistersUsers;

    protected $redirectTo = '/home';

    public function __construct()
    {
        $this->middleware('guest');
    }

    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);
    }

    protected function create(array $data)
    {
        $username = $data['username'] ?? $this->generateUsername($data['name']);

        return User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'username' => $username,
            'password' => Hash::make($data['password']),
        ]);
    }

    protected function generateUsername($name)
    {
        $words           = explode(' ', trim($name));
        $firstFieveWords = array_slice($words, 0, 5);
        $base            = strtolower(implode('', $firstFieveWords));
        $randomNumber    = rand(1000, 9999);

        return $base . $randomNumber;
    }
}
