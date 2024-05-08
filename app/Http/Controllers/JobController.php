<?php

namespace App\Http\Controllers;

use App\Mail\JobNotificationEmail;
use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use App\Providers\OTPServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class JobController extends Controller
{
    public function index(Request $request){

        //Get Categories
        $categories = Category::where('status', 1)->get();

        //Get Job types
        $jobTypes = JobType::where('status', 1)->get();

        //Get Jobs
        $jobs = Job::where('status', 1);

        //Search using keywords
        if(!empty($request->keyword))
        {
            $jobs = $jobs->where(function($query) use($request){

                $query->orWhere('title', 'like', '%'.$request->keyword.'%');
                $query->orWhere('keywords', 'like', '%'.$request->keyword.'%');

            });

        }

        //Search using location
        if(!empty($request->location))
        {
            $jobs = $jobs->where('location', $request->location );
        }

        //Search using categories
        if(!empty($request->category))
        {
            $jobs = $jobs->where('category_id', $request->category );
        }

        //Search using Job Type
        $jobTypeArray = [];

        if(!empty($request->jobType))
        {
            $jobTypeArray = explode(',', $request->jobType);
            $jobs         = $jobs->whereIn('job_type_id', $jobTypeArray );
        }

        //Search using experience
        if(!empty($request->experience))
        {
            $jobs = $jobs->where('experience', $request->experience );
        }

        $jobs = $jobs->with(['jobType', 'category']);

        if($request->sort == '0')
        {

            $jobs = $jobs->orderBy('created_at', 'ASC');

        } else
        {

            $jobs = $jobs->orderBy('created_at', 'DESC');

        }


        $jobs = $jobs->paginate(8);

        return view('front.jobs',[
            'categories'   => $categories,
            'jobTypes'     => $jobTypes,
            'jobs'         => $jobs,
            'jobTypeArray' => $jobTypeArray

        ]);
    }

    public function detail($id){

        //Get job details
        $job = Job::where([ 'id' => $id , 'status' => 1])
        ->with(['jobType','category'])
        ->first();

        if( $job == null ){

            abort(404);

        }

        $count = 0;
        if( Auth::user())
        {
            //check if user is already saved the job
            $count = SavedJob::where([
                'user_id' => Auth::user()->id,
                'job_id'  => $id
            ])->count();
        }

        //Fetch applicants information
        $applications = JobApplication::where('job_id', $id)->with('user')->get();

        //Return job details
        return view('front.jobDetails',[
            'job'          => $job,
            'count'        => $count,
            'applications' => $applications
        ]);

    }

    public function applyJob( Request $request ) {
        $id = $request->id;

        $job = Job::where('id' , $id )->first();

        //If the job not found
        if( $job == null ){

            //Flash message
            session()->flash('error', 'Job not found');

            //Return response
            return response()->json([
                'status'  => false,
                'message' => 'Job not found'
            ]);

        }

        //Employer can not apply their own jobs
        //Get employer id
        $employerId = $job->user_id;

        if($employerId == Auth::user()->id)
        {
            //Flash message
            session()->flash('error', 'You can not apply your own job');

            //Return response
            return response()->json([
                'status'  => false,
                'message' => 'You can not apply your own job'
            ]);
        }

        //Check if the user already applied for this job
        $jobApplicationCount = JobApplication::where([
                'user_id' => Auth::user()->id,
                'job_id'  => $id
            ])->count();

        if($jobApplicationCount > 0){

            //Flash message
            session()->flash('error', 'You have already applied for this job');

            //Return response
            return response()->json([
               'status'  => false,
               'message' => 'You have already applied for this job'
            ]);
        }

        //Apply for job and store in database
        $application              = new JobApplication();
        $application->user_id     = Auth::user()->id;
        $application->job_id      = $id;
        $application->employer_id = $employerId;
        $application->save();

        //Send notification email to employer
        $employer = User::where('id', $employerId)->first();

        $mailData = [
            'employer' => $employer,
            'user'     => Auth::user(),
            'job'      => $job
        ];

        //Send email to employer by using MailTrap.io account
        Mail::to($employer->email)->send(new JobNotificationEmail($mailData));

        $message = 'You have successfully submit your application for the job role of '.$job->title .'.';

        //Flash message
        session()->flash('success', $message);

        //Return response
        return response()->json([
            'status'  => false,
            'message' => $message
        ]);

    }

    public function saveJob(Request $request){

       $id = $request->id;

        $job = Job::find($id);

        //If the job not found
        if( $job == null ){

            //Flash message
            session()->flash('error', 'Job not found');

           return response()->json([
            'status'  => false,
            ]);

        }

        //check if user is already saved the job
        $count = SavedJob::where([
            'user_id' => Auth::user()->id,
            'job_id'  => $id
        ])->count();

        if ($count > 0){

            //Flash message
            session()->flash('error', 'You already saved this job.');

           return response()->json([
            'status'  => false,
            ]);
        }

        //Save job
        $savedJob = new SavedJob;
        $savedJob->job_id = $id;
        $savedJob->user_id = Auth::user()->id;
        $savedJob->save();

        //Flash message
        session()->flash('success', 'Job saved successfully.');

        return response()->json([
         'status'  => true,
         ]);
    }

}
