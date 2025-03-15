<?php

namespace App\Http\Controllers;

use App\Models\Schedule;
use Illuminate\Http\Request;

class RecapController extends Controller
{
    public function getUserRecap(Request $request){

        $recap = Schedule::where('date', '>=', $request->start)
        ->where('date', "<=", $request->end)
        ->get();

        return response()->json($recap);

    }

    

}
