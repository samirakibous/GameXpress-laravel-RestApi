<?php

// namespace App\Http\Controllers\Api\V1\Admin;

// use Illuminate\Support\Facades\Auth;
// use Illuminate\Http\Request;

// class DashboardController extends controller
// {
//     public function index()
//     {
//         $user=Auth::user();
//         if (Auth::user()->hasRole('super_admin')) {
//         return response()->json(['message' => 'Welcome to the dashboard']);}
//         else{
//             return response()->json(['message' => 'You are not allowed to access the dashboard']);
//         }
//     }
// }