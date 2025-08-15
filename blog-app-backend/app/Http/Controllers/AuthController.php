<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Spatie\Permission\Models\Role;
use Exception;

class AuthController extends Controller
{
        public function login(Request $request)
        {
            try {
                $user = User::where('email', $request->email)->first();

                if (! $user || ! Hash::check($request->password, $user->password)) {
                    return response()->json(['message' => 'Invalid Email or Password'], 401);
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
                    'role' => $request->role,
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

        public function getCurrentUser()
        {
            try {
                $user = Auth::user();

                return response()->json([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ]);
            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Unable to fetch user info',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

        public function registerAdmin(Request $request)
        {
            try {
                $request->validate([
                    'email'=> 'required|email|unique:users',
                    'password'=> 'required|string|min:6',
                    'admin_key' => 'nullable|string'
                ]);

                if ($request->admin_key && $request->admin_key === env('ADMIN_CREATION_KEY')) {
                    $role = 'admin';
                    $name = 'admin user';
                } else {
                    return response()->json([
                        'message' => 'Invalid admin key'
                    ], 403);
                }

                $user = User::create([
                    'name' => $name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                ]);

                $user->assignRole($role);

                $token = $user->createToken('api-token')->plainTextToken;

                return response()->json([
                    'user' => $user,
                    'role' => $role,
                    'token' => $token
                ], 201);

            } catch (Exception $e) {
                return response()->json([
                    'message' => 'Failed to register user',
                    'error' => $e->getMessage()
                ], 500);
            }
        }

            public function deleteAdmin(Request $request)
            {
                try {
                    $request->validate([
                        'email' => 'required|email',
                        'admin_key' => 'required|string'
                    ]);

                    if ($request->admin_key !== env('ADMIN_CREATION_KEY')) {
                        return response()->json(['message' => 'Invalid admin key'], 403);
                    }

                    $admin = User::where('email', $request->email)->first();
                    if (!$admin || !$admin->hasRole('admin')) {
                        return response()->json(['message' => 'Admin not found'], 404);
                    }

                    $admin->delete();

                    return response()->json(['message' => 'Admin deleted successfully'], 200);

                } catch (Exception $e) {
                    return response()->json(['message' => 'Failed to delete admin', 'error' => $e->getMessage()], 500);
                }
            }

}
