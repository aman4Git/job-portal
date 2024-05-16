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

    public function delete(Request $request){

        //Get job id
        $applicationId = $request->id;

        //Get application by id
        $application = JobApplication::find($applicationId);

        if( $application === null )
        {
            //flash message
            session()->flash('error', 'Application not found.');

            //Return response
            return response()->json([
                'status' => false,
            ]);
        }

        //Delete application
        $application->delete();

        //flash message
        session()->flash('success', 'Application deleted successfully.');

        //Return response
        return response()->json([
           'status' => true,
        ]);

    }
}
