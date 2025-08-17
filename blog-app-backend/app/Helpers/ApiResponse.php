<?php

namespace App\Helpers;

class ApiResponse
{
    public static function success($data = null, $message = 'Success', $status = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $status);
    }

    public static function error($message = 'Error', $error = null, $status = 500)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'error' => $error
        ], $status);
    }
}
