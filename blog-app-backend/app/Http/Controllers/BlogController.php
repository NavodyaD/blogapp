<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class BlogController extends Controller
{
    public function index()
    {
        return response()->json([
            'message' => 'Accessed by admin',
        ]);
    }
}
