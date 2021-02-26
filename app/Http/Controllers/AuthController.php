<?php

namespace App\Http\Controllers;

use JWTAuth;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\User;

class AuthController extends Controller
{
    public $loginAfterSignUp = true;

    // login
    public function login(Request $request)
    {
        $credentials = $request->only('email', 'password');
        $token = null;

        if (!$token = JWTAuth::attempt($credentials)) {
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized'
            ]);
        }

        return response()->json([
            'status' => true,
            'token' => $token,
            'message' => 'Logged in successfully'
        ]);

    }

    // register
    public function register(Request $request)
    {
        // validate
        $this->validate($request, [
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string|min:6'
        ]);

        // save user
        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = \bcrypt($request->password);
        $user->save();

        if ($this->loginAfterSignUp) {
            return $this->login($request);
        }

        return response()->json([
            'status' => true,
            'user' => $user,
            'message' => 'User registered successfully'
        ]);
    }

    // logout
    public function logout(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);

        try {
            JWTAuth::invalidate($request->token);
            return response()->json([
                'status' => true,
                'message' => 'User logged out successfully'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'status' => false,
                'message' => 'Something went wrong. User can not be logged out.'
            ]);
        }
    }

}
