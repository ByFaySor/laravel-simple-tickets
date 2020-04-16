<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\User;
use App\Role;
use App\RoleUser;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function register(Request $request)
    {
        $this->validator($request->all())->validate();
        $user = $this->create($request->all());
        return response()->json([
            'user' => $user,
            'message' => 'Cuenta creada exitosamente'
        ], 200);
    }
    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'string', 'min:4', 'confirmed'],
            'password_confirmation' => ['required', 'string', 'min:4'],
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\User
     */
    protected function create(array $data)
    {
        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        $user
            ->roles()
            ->attach(Role::where('name', 'user')->first());

        return response()->json(['success' => true, 'message' => 'Registro exitoso!', 'user' => $user], 201);
    }

    protected function guard()
    {
        return Auth::guard();
    }

    public function login(Request $request) {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
    
        $user = User::where('email', $request->email)->first();
    
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => 'Datos incorrectos, intente nuevamente.'
            ], 404);
        }
    
        $token = $user->createToken('fs-tickets')->plainTextToken;

        $user = $user->only(['id', 'name', 'email']);
        $user['rol'] = RoleUser::where('user_id', $user['id'])->first()->role_id;
    
        $response = [
            'user' => $user,
            'token' => $token
        ];
    
        return response($response, 201);
    }

    public function logout()
    {
        Auth::logout();
        return response()->json(['message' => 'Sesión cerrada'], 200);
    }
}