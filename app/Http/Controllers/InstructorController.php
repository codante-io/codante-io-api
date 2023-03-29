<?php

namespace App\Http\Controllers;

use App\Http\Resources\InstructorCollection;
use App\Http\Resources\InstructorResource;
use App\Models\Instructor;
use Illuminate\Http\Request;

class InstructorController extends Controller
{
    public function index()
    {
        return new InstructorCollection(Instructor::all());
    }

    public function show($slug)
    {
        return new InstructorResource(
            Instructor::where('slug', $slug)->with('workshops')->firstOrFail()
        );
    }
}
