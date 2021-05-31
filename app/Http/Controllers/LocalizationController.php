<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\App;
use Illuminate\Http\Request;

class LocalizationController extends Controller
{
    public function index(Request $request){
        session(['lng' => $request->lng]);
        return redirect()->back();
    }
}
