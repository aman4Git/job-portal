<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

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

    public function edit($id){

        //Retrieve user information
        $user = User::findOrFail($id);

        //Return view
        return view('admin.users.edit',[
            'user' => $user
        ]);

    }

    public function update(Request $request, $id)
    {
        //validations
        $validator = Validator::make($request->all(),[
            'name'             => 'required|min:5',
            'email'            => 'required|email|unique:users,email, '.$id.',id'
        ]);

        if( $validator->passes() )
        {
            //Get user by id
            $user = User::where('id',$id)->first();

            //update the user
            $user->name        = $request->name;
            $user->email       = $request->email;
            $user->designation = $request->designation;
            $user->mobile      = $request->mobile;
            $user->save();

            //flash message
            session()->flash('success', 'User information updated successfully!');

            //Return response
            return response()->json([
                'status' => true,
                'error'  => []
            ]);
        }
        else
        {
            //Return response
            return response()->json([
                'status' => false,
                'error'  => $validator->errors()
            ]);
        }
    }

}
