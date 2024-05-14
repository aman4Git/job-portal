<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Job;
use Illuminate\Http\Request;

class JobsController extends Controller
{
    public function index(){
        //Fetch all jobs
        $jobs = Job::orderBy('created_at', 'DESC')->with('user','applications')->paginate(5);

        //Return view with jobs
        return view('admin.jobs.list',[
            'jobs' => $jobs
        ]);
    }
}
