<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ClassApiController extends Controller
{
    public function index()
    {
        $classes = \App\Models\SchoolClass::orderBy('name')->pluck('name');
        return response()->json([
            'success' => true,
            'data' => $classes
        ]);
    }
}
