<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Models\Recipe;

class SQLController extends Controller
{
    public function index(){
        $done = Recipe::truncate();

        if($done){
            return response()->json(['code' => 200, 'message' => 'all done']);
        }
        return response()->json(['code' => 500, 'message' => 'not done']);
    }
}
