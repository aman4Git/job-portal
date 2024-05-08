<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Job;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    public function index()
    {
        //Get Categories
        $categories = Category::where('status', 1)->orderBy('name', 'ASC')->take(8)->get();

        $newCategories = Category::where('status', 1)->orderBy('name', 'ASC')->get();


        //Get featured jobs
        $featuredJobs = Job::where('status', 1)
        ->where('isFeatured',1)
        ->with('jobType')
        ->orderBy('created_at', 'ASC')
        ->take(8)->get();

        //Get latest jobs
        $latestJobs = Job::where('status', 1)
        ->with('jobType')
        ->orderBy('created_at', 'DESC')
        ->take(8)->get();

        return view('front.home', [
            'categories'    => $categories,
            'featuredJobs'  => $featuredJobs,
            'latestJobs'    => $latestJobs,
            'newCategories' => $newCategories,
        ]);
    }
}
