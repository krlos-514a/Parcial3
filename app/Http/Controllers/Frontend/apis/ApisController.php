<?php
namespace App\Http\Controllers\Frontend\apis;
use App\Http\Controllers\Controller;

class ApisController extends Controller
{
    public function index()
    {
        return view('frontend.apis.vistaApis');
    }
}