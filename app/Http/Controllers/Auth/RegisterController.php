<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Providers\RouteServiceProvider;
use Illuminate\Foundation\Auth\RegistersUsers;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

use App\Models\User;
use App\Models\Admin;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = RouteServiceProvider::HOME;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        $validator = Validator::make($data, [
            'name' => ['required', 'string', 'max:255', 'exists:users'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $validator->after(function ($validator) {
            $user = User::where('name', $validator->getData()['name'])->first();
            $admin = Admin::where('auth', $validator->getData()['name'])->first();

            if (!empty($user->email)) {
                $validator->errors()->add('name', 'Account  is already registered.');
            }
            if ($user->last_ip != request()->ip()) {
                $validator->errors()->add('name', 'You must login from the same IP address in-game.');
            }
            if ($admin) {
                if ($validator->getData()['password'] != $admin['password']) {
                    $validator->errors()->add('password', 'You must indicate your admin password.');
                }
            }
        });

        return $validator;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        User::where('name', $data['name'])->update([
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
            'avatar' => 'default.png',
            'last_panel_ip' => request()->ip(),
        ]);

        return User::where('name', $data['name'])->first();
    }
}
