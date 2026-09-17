<?php

namespace App\Http\Controllers;

use Inertia\Inertia;

class SuperAdminController extends Controller
{
    public function index()
    {
        return Inertia::render('SuperAdmin/Panel');
    }
}