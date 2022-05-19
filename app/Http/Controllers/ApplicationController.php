<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Lookups;

class ApplicationController extends Controller
{

    public function step_1(Request $request){

        $lookups = Lookups::get();

        return view('application.step_1', ["lookups" => $lookups]);
    }
}
