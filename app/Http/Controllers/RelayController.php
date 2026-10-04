<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Support\RelayReceiver;

class RelayController extends Controller
{
    public function __invoke(Request $request): Response
    {
        (new RelayReceiver($request->route('id')))
            ->handle($request->all());

        return response()->noContent(200);
    }
}
