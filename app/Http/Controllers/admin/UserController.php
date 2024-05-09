<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(){
        //Fetch all users
        $users = User::orderBy('created_at', 'DESC')->paginate(10);

        //Return view with users
        return view('admin.users.list',[
            'users' => $users
        ]);
    }
}
