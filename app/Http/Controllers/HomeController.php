<?php

namespace App\Http\Controllers;

use App\Models\SubscriptionPlan;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Display landing page
     */
    public function index()
    {
        $plans = SubscriptionPlan::where('is_active', true)
            ->orderBy('price', 'asc')
            ->get();
        
        return view('home.index', compact('plans'));
    }
    
    /**
     * Display credits page
     */
    public function credits()
    {
        return view('home.credits');
    }
}
