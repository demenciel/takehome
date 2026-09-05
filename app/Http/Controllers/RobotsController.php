<?php

namespace App\Http\Controllers;

use Illuminate\Http\Response;

class RobotsController extends Controller
{
    public function __invoke(): Response
    {
        return response()
            ->view('seo.robots')
            ->header('Content-Type', 'text/plain');
    }
}
