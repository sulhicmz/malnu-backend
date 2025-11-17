<?php

namespace App\Http\Controllers\frontend;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class FeController extends Controller
{
    public function indexFe()
    {
        return view('frontend.landingpage');
    }
}
