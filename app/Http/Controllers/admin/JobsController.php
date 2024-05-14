<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

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

    public function edit($id){

        //Retrieve job information
        $job = Job::findOrFail($id);

        //Get all job categories
        $categories= Category::orderBy('name', 'ASC')->where('status', 1)->get();

        $jobTypes= JobType::orderBy('name', 'ASC')->where('status', 1)->get();

        //Return view
        return view('admin.jobs.edit',[
            'job'        => $job,
            'categories' => $categories,
            'jobTypes'   => $jobTypes
        ]);
    }

    public function update(Request $request, $jobId)
    {
        //Validation rules
        $rules = [
            'title'        => 'required|min:4|max:200',
            'category'     => 'required',
            'jobType'      => 'required',
            'vacancy'      => 'required|integer',
            'location'     => 'required|max:50',
            'description'  => 'required',
            'company_name' => 'required|min:3|max:75',
        ];

        //validations
        $validator = Validator::make($request->all(),$rules);

        if( $validator->passes() )
        {
            //Update job
            $job = Job::find($jobId);

            $job->title            = $request->title;
            $job->category_id      = $request->category;
            $job->job_type_id      = $request->jobType;
            $job->vacancy          = $request->vacancy;
            $job->salary           = $request->salary;
            $job->location         = $request->location;
            $job->description      = $request->description;
            $job->benefits         = $request->benefits;
            $job->responsibilities = $request->responsibilities	;
            $job->qualifications   = $request->qualifications;
            $job->keywords         = $request->keywords;
            $job->experience       = $request->experience;
            $job->company_name     = $request->company_name;
            $job->company_location = $request->company_location;
            $job->company_website  = $request->company_website;
            $job->save();

            //Flash message
            session()->flash('success', 'Job updated successfully!');

           //return response
           return response()->json([
            'status' => true,
            'error'  => []
            ]);

        }
        else
        {
            return response()->json([
                'status' => false,
                'error'  => $validator->errors()
            ]);
        }

    }
}
