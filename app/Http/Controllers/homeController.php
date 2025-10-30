<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;

class homeController extends Controller
{
    public function index(){
        if(!Auth::check()){
            return view('welcome');
        }
        $dashboardData = []; // TODO: Replace with actual dashboard data
        $userPermissions = []; // TODO: Replace with actual user permissions
        return view('panel.index', [
            'dashboardData' => $dashboardData,
            'userPermissions' => $userPermissions,
        ]);
    }

}
