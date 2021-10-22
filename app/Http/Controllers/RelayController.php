<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Support\RelayReceiver;

class RelayController extends Controller
{
    public function __invoke(Request $request)
    {
        (new RelayReceiver($request->id))
            ->log($request->all());
    }
}
