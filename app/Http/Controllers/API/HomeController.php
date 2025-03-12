<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        $users = UserResource::make(User::all());
        return $users;
        return response()->json(['data' => $users, 'error' => ""], 200);
    }
}
