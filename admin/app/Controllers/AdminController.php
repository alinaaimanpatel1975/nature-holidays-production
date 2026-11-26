<?php

namespace App\Controllers;

use App\Core\Auth;
use App\Core\Controller;

class AdminController extends Controller
{
    public function index(): void
    {
        Auth::requireLogin();

        $this->view('admin/dashboard', [
            'user' => Auth::user(),
        ]);
    }
}