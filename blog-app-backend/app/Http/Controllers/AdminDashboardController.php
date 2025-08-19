<?php

namespace App\Http\Controllers;

use App\Helpers\ApiResponse;
use App\Handlers\AdminDashboardHandler;
use Exception;

class AdminDashboardController extends Controller
{
    protected AdminDashboardHandler $handler;

    public function __construct(AdminDashboardHandler $handler)
    {
        $this->handler = $handler;
    }

    public function getInsights()
    {
        try {
            $data = $this->handler->insights();
            return ApiResponse::success($data, 'Admin insights fetched successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch admin insights', $e->getMessage(), 500);
        }
    }

    public function listAdmins()
    {
        try {
            $admins = $this->handler->listAdmins();
            return ApiResponse::success($admins, 'Admins fetched successfully');
        } catch (Exception $e) {
            return ApiResponse::error('Failed to fetch admins', $e->getMessage(), 500);
        }
    }
}
