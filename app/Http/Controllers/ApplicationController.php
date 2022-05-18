<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ApplicationController extends Controller
{

    public function step_1(Request $request){

        return view('application.step_1');
    }
}
