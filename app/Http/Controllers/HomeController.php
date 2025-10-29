<?php
namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    // ... (Your constructor/other methods)

    public function showPersonalization()
    {
        // 🚨 IMPORTANT: Add logic to check if personalization is already complete
        // For now, we assume it's the first time.
        
        return view('personalization');
    }
    
    public function index()
    {
        // Your dashboard logic
        return view('dashboard');
    }
}
