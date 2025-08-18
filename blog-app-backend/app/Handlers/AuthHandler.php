<?php

namespace App\Handlers;

use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthHandler
{
    public function login(string $email, string $password): array
    {
        $user = User::where('email', $email)->first();

        if (!$user || !Hash::check($password, $user->password)) {
            return ['status' => 'invalid_credentials'];
        }

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'status' => 'ok',
            'data' => [
                'token' => $token,
                'role'  => $user->getRoleNames()->first(),
            ],
        ];
    }

    public function register(string $name, string $email, string $password, string $role): array
    {
        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => bcrypt($password),
        ]);

        $user->assignRole($role);

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'status' => 'created',
            'data' => [
                'user'  => $user,
                'role'  => $role,
                'token' => $token,
            ],
        ];
    }

    public function logout($user): void
    {
        $user->currentAccessToken()->delete();
    }

    public function currentUser($user): array
    {
        return [
            'id'    => $user->id,
            'name'  => $user->name,
            'email' => $user->email,
        ];
    }

    public function registerAdmin(string $email, string $password, ?string $adminKey, string $envAdminKey): array
    {
        if (!$adminKey || $adminKey !== $envAdminKey) {
            return ['status' => 'invalid_admin_key'];
        }

        $role = 'admin';
        $name = 'admin user';

        $user = User::create([
            'name'     => $name,
            'email'    => $email,
            'password' => bcrypt($password),
        ]);

        $user->assignRole($role);

        $token = $user->createToken('api-token')->plainTextToken;

        return [
            'status' => 'created',
            'data' => [
                'user'  => $user,
                'role'  => $role,
                'token' => $token,
            ],
        ];
    }

    public function deleteAdmin(string $email, string $adminKey, string $envAdminKey): array
    {
        if ($adminKey !== $envAdminKey) {
            return ['status' => 'invalid_admin_key'];
        }

        $admin = User::where('email', $email)->first();
        if (!$admin || !$admin->hasRole('admin')) {
            return ['status' => 'not_found'];
        }

        $admin->delete();

        return ['status' => 'deleted'];
    }
}
