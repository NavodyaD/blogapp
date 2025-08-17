<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Helpers\ApiResponse;
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

                $data = [
                    'token' => $token,
                    'role' => $user->getRoleNames()->first(),
                ];

                return ApiResponse::success($data, 'Login successful');

            } catch (Exception $e) {
                return ApiResponse::error('Failed to login user', $e->getMessage(), 500);
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

                $data = [
                    'user' => $user,
                    'role' => $request->role,
                    'token' => $token
                ];

                return ApiResponse::success($data, 'User registered successfully', 201);

            } catch (Exception $e) {
                return ApiResponse::error('Failed to register user', $e->getMessage(), 500);
            }
        }

        public function logout (Request $request)
        {
            try {
                $request->user()->currentAccessToken()->delete();

                return ApiResponse::success(null, 'Logged out successfully');

            } catch (Exception $e) {
                return ApiResponse::error('Failed to logout the user', $e->getMessage(), 500);
            }
        }

        public function getCurrentUser()
        {
            try {
                $user = Auth::user();

                return ApiResponse::success([
                    'id' => $user->id,
                    'name' => $user->name,
                    'email' => $user->email,
                ], 'Fetched current user');
            } catch (Exception $e) {
                return ApiResponse::error('Unable to fetch user info', $e->getMessage(), 500);
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
                    return ApiResponse::error('Invalid admin key', null, 403);
                }

                $user = User::create([
                    'name' => $name,
                    'email' => $request->email,
                    'password' => bcrypt($request->password),
                ]);

                $user->assignRole($role);

                $token = $user->createToken('api-token')->plainTextToken;

                return ApiResponse::success([
                    'user' => $user,
                    'role' => $role,
                    'token' => $token
                ], 'Admin registered successfully', 201);

            } catch (Exception $e) {
                return ApiResponse::error('Failed to register user', $e->getMessage(), 500);
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
                    return ApiResponse::error('Invalid admin key', null, 403);
                }

                $admin = User::where('email', $request->email)->first();
                if (!$admin || !$admin->hasRole('admin')) {
                    return ApiResponse::error('Admin not found', null, 404);
                }

                $admin->delete();

                return ApiResponse::success(null, 'Admin deleted successfully', 200);

            } catch (Exception $e) {
                return ApiResponse::error('Failed to delete admin', $e->getMessage(), 500);
            }
        }

}
