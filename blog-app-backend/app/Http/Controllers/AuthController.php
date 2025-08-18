<?php

namespace App\Http\Controllers;

use App\Handlers\AuthHandler;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Helpers\ApiResponse;
use Exception;

class AuthController extends Controller
{
    protected AuthHandler $handler;

    public function __construct(AuthHandler $handler)
    {
        $this->handler = $handler;
    }

    public function login(Request $request)
    {
        try {
            $result = $this->handler->login($request->email, $request->password);

            if ($result['status'] === 'invalid_credentials') {
                return ApiResponse::error('Invalid Email or Password', null, 401);
            }

            return ApiResponse::success($result['data'], 'Login successful');
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

            $result = $this->handler->register(
                $request->name,
                $request->email,
                $request->password,
                $request->role
            );

            return ApiResponse::success($result['data'], 'User registered successfully', 201);
        } catch (Exception $e) {
            return ApiResponse::error('Failed to register user', $e->getMessage(), 500);
        }
    }

    public function logout(Request $request)
    {
        try {
            $this->handler->logout($request->user());
            return ApiResponse::success(null, 'Logged out successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to logout the user', $e->getMessage(), 500);
        }
    }

    public function getCurrentUser()
    {
        try {
            $data = $this->handler->currentUser(Auth::user());
            return ApiResponse::success($data, 'Fetched current user');
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

            $result = $this->handler->registerAdmin(
                $request->email,
                $request->password,
                $request->admin_key,
                env('ADMIN_CREATION_KEY')
            );

            if ($result['status'] === 'invalid_admin_key') {
                return ApiResponse::error('Invalid admin key', null, 403);
            }

            return ApiResponse::success($result['data'], 'Admin registered successfully', 201);
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

            $result = $this->handler->deleteAdmin(
                $request->email,
                $request->admin_key,
                env('ADMIN_CREATION_KEY')
            );

            if ($result['status'] === 'invalid_admin_key') {
                return ApiResponse::error('Invalid admin key', null, 403);
            }
            if ($result['status'] === 'not_found') {
                return ApiResponse::error('Admin not found', null, 404);
            }

            return ApiResponse::success(null, 'Admin deleted successfully', 200);
        } catch (Exception $e) {
            return ApiResponse::error('Failed to delete admin', $e->getMessage(), 500);
        }
    }
}
