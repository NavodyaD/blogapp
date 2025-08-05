<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
        public function login(Request $request)
        {
            try {
                $user = User::where('email', $request->email)->first();

                if (! $user || ! Hash::check($request->password, $user->password)) {
                    return response()->json(['message' => 'Invalid credentials'], 401);
                }

                $token = $user->createToken('api-token')->plainTextToken;

                return response()->json([
                    'token' => $token,
                    'role' => $user->getRoleNames()->first(),
                ]);

            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Failed to login user',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        public function register(Request $request)
        {
            try {
                $request->validate([
                    'name'=> 'required|string|max:255',
                    'email'=> 'required|email|unique:users',
                    'password'=> 'required|string|min:6',
                    'role' => 'required|string|exists:roles,name'
                ]);

                $user = User::create([
                    'name' => $request->name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                ]);

                $user->assignRole($request->role);

                $token = $user->createToken('api-token')->plainTextToken;

                return response()->json([
                    'user' => $user,
                    'token' => $token
                ], 201);

            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Failed to register user',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        public function logout (Request $request)
        {
            try {
                $request->user()->currentAccessToken()->delete();

                return response()->json(['message'=>'Logged Out!'], 200);

            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Failed to logout the user',
                    'error' => $e->getMessage()
                ], 500);
            }
        }
}
