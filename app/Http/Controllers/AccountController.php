<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use App\Models\JobApplication;
use App\Models\JobType;
use App\Models\SavedJob;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Colors\Rgb\Channels\Red;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class AccountController extends Controller
{

    //This method will show user's Registration page
    public function registration()
    {
        return view('front.account.registration');
    }

    //This method will create and save a user
    public function processRegistration(Request $request)
    {
        //validations
        $validator = Validator::make($request->all(),[
            'name'             => 'required',
            'email'            => 'required|email|unique:users,email',
            'password'         => 'required|min:5|same:confirm_password',
            'confirm_password' => 'required',

        ]);

        if( $validator->passes() )
        {
            //create and store User
            $user           = new User();
            $user->name     = $request->name;
            $user->email    = $request->email;
            $user->password = Hash::make($request->password);
            $user->save();

            session()->flash('success', 'You have registered successfully!');

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

    //This method will show user's Login page
    public function login()
    {
        return view('front.account.login');
    }

    //This method will Authenticate user
    public function authenticate(Request $request)
    {
        //validations
        $validator = Validator::make($request->all(),
        [
            'email'            => 'required|email',
            'password'         => 'required',
        ]);

        if( $validator->passes() )
        {
            if(Auth::attempt(['email' => $request->email, 'password' => $request->password]))
            {
                return redirect()->route('account.profile');
            }
            else
            {
                return redirect()->route('account.login')->with('error', 'Login credentials are invalid');
            }
        }
        else
        {
           return redirect()->route('account.login')->withErrors($validator)->withInput($request->only('email'));
        }
    }

    public function profile()
    {
        //Get logged in user's ID
        $id = Auth::user()->id;

        //Get user by id
        $user = User::where('id',$id)->first();

        return view('front.account.profile', [
            'user' => $user
        ]);
    }

    public function updateProfile(Request $request)
    {
        //Get logged in user's ID
        $id = Auth::user()->id;

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
            session()->flash('success', 'Profile updated successfully!');

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

    public function updateProfilePic(Request $request)
    {

        $id= Auth::user()->id;

        //validations
        $validator = Validator::make($request->all(),[
            'image'             => 'required|image'
        ]);

        if( $validator->passes() )
        {
            $image     = $request->image;
            $ext       = $image->getClientOriginalExtension();
            $imageName =  $id.'-'.time().'.'.$ext;
            $image->move(public_path('/profile-pic/'), $imageName);

            //Create small thumbnails for full size images
            // create new image instance (800 x 600)
            $sourcePath = public_path('/profile-pic/'.$imageName);
            $manager    = new ImageManager(Driver::class);
            $image      = $manager->read( $sourcePath );

            // crop the best fitting
            $image->cover(150, 150);
            $image->toPng()->save(public_path('/profile-pic/thumb/'.$imageName));

            //Delete Old profile pic & thumbnail from profile-pic $ thumb directory
            File::delete(public_path('/profile-pic/'.Auth::user()->image));
            File::delete(public_path('/profile-pic/thumb/'.Auth::user()->image));

            User::where('id', $id)->update(['image' => $imageName]);

            //flash message
            session()->flash('success', 'Profile picture updated successfully!');

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

    public function createJob()
    {
        //Get all job categories
        $categories= Category::orderBy('name', 'ASC')->where('status', 1)->get();

        //Get all job types
        $jobTypes= JobType::orderBy('name', 'ASC')->where('status', 1)->get();

        return view('front.account.job.create', [
            'categories' => $categories,
            'jobTypes'   => $jobTypes
        ]);
    }

    public function saveJob(Request $request)
    {
        //Logged in user
        $user  = Auth::user();

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
            //Create and save job
            $job = new Job();

            $job->title            = $request->title;
            $job->category_id      = $request->category;
            $job->job_type_id      = $request->jobType;
            $job->user_id          = $user->id;
            $job->vacancy          = $request->vacancy;
            $job->salary           = $request->salary;
            $job->location         = $request->location;
            $job->description      = $request->description;
            $job->benefits         = $request->benefits;
            $job->responsibilities = $request->responsibilities;
            $job->qualifications   = $request->qualifications;
            $job->keywords         = $request->keywords;
            $job->experience       = $request->experience;
            $job->company_name     = $request->company_name;
            $job->company_location = $request->company_location;
            $job->company_website  = $request->company_website;
            $job->save();

            //Flash message
            session()->flash('success', 'Job added successfully!');

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

    public function myJobs()
    {
        //Logged in user
        $user  = Auth::user();

        //Get jobs
        $jobs = Job::where('user_id', $user->id)->with('jobType')->orderBy('created_at', 'DESC')->paginate(10);

        return view('front.account.job.my-jobs',[
            'jobs' => $jobs
        ]);
    }

    public function editJob(Request $request, $jobId)
    {
        //Logged in user
        $user  = Auth::user();

        //Get all job categories
        $categories= Category::orderBy('name', 'ASC')->where('status', 1)->get();

        //Get all job types
        $jobTypes= JobType::orderBy('name', 'ASC')->where('status', 1)->get();

        //Get job by ID's
        $job = Job::where([
            'user_id' => $user->id,
            'id'      => $jobId
        ])->first();

        if(empty($job))
        {
            abort(404);
        }

        return view('front.account.job.edit', [
            'job'        => $job,
            'categories' => $categories,
            'jobTypes'   => $jobTypes
        ]);
    }

    public function updateJob(Request $request, $jobId)
    {
        //Logged in user
        $user  = Auth::user();

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
            $job->user_id          = $user->id;
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

    public function deleteJob(Request $request)
    {
        //Logged in user
        $user  = Auth::user();

        //Get job by ID's
        $job = Job::where([
            'user_id' => $user->id,
            'id'      => $request->jobId
        ])->first();

        if(empty($job))
        {
            //Flash message
            session()->flash('error', 'Either job deleted or not found.');

            //return response
            return response()->json([
                'status' => true,
            ]);
        }

        //Delete selected Job
        Job::where('id', $request->jobId )->delete();

        //Flash message
        session()->flash('success', 'Job deleted successfully!');

        //return response
        return response()->json([
            'status' => true,
        ]);
    }

    public function logout()
    {
        Auth::logout();
        return redirect()->route('account.login');
    }

    public function myJobApplication()
    {
        //Get applied jobs
        $jobApplications = JobApplication::where('user_id', Auth::user()->id)
        ->with(['job', 'job.jobType', 'job.applications'])
        ->orderBy('created_at', 'DESC')
        ->paginate(10);

       return view('front.account.job.my-job-application',[
        'jobApplications' => $jobApplications
       ]);
    }

    public function removeJobs(Request $request)
    {
        //Get applied jobs
        $jobApplication = JobApplication::where(['id' =>  $request->id, 'user_id' => Auth::user()->id]
        )->first();

        if($jobApplication == null){

            //flash message
            session()->flash('error', 'Job application not found.');

            return response()->json([
               'status' => false,
            ]);
        }

        JobApplication::find($request->id)->delete();

        //flash message
        session()->flash('success', 'Job application removed successfully.');

        return response()->json([
           'status' => true,
        ]);
    }

    public function savedJobs() {

        //Get all saved jobs
        $savedJobs = SavedJob::where('user_id', Auth::user()->id)
        ->with(['job', 'job.jobType', 'job.applications'])
        ->orderBy('created_at', 'DESC')
        ->paginate(10);

       return view('front.account.job.saved-jobs',[
        'savedJobs' => $savedJobs
       ]);
    }

    public function removeSavedJob(Request $request)
    {
        //Get saved jobs
        $savedJob = SavedJob::where(['id' =>  $request->id, 'user_id' => Auth::user()->id]
        )->first();

        if($savedJob == null){

            //flash message
            session()->flash('error', 'Job not found.');

            return response()->json([
               'status' => false,
            ]);
        }

        SavedJob::find($request->id)->delete();

        //flash message
        session()->flash('success', 'Job removed successfully.');

        return response()->json([
           'status' => true,
        ]);
    }

    public function updatePassword(Request $request)
    {
        //Validations
        $validator = Validator::make($request->all(),
        [
            'old_password'     => 'required',
            'new_password'     => 'required|min:5',
            'confirm_password' => 'required|same:new_password',
        ]);

        //Check validations
        if( $validator->fails() ){

            return response()->json([
               'status' => false,
               'error'  => $validator->errors()
            ]);
        }

        //Check old password
        if( Hash::check($request->old_password, Auth::user()->password ) === false )
        {
            //flash message
            session()->flash('error', 'Your old password is incorrect.');

            //return response
            return response()->json([
                'status' => true,
            ]);
        }

        //Update password
        $user = User::find(Auth::user()->id);
        $user->password =  Hash::make($request->new_password);
        $user->save();

        //flash message
        session()->flash('success', 'Password updated successfully.');

        //return response
        return response()->json([
           'status' => true,
        ]);


    }
}
