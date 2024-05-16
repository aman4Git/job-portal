<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\JobApplication;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function index(){
        //Fetch all job applications
        $applications = JobApplication::orderBy('created_at', 'DESC')->with('user','job','employer')->paginate(5);

        //Return view with job applications
        return view('admin.job-applications.list',[
            'applications' => $applications
        ]);
    }
}
