<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class CurrencyController extends Controller
{
    public function currency()
    {
        $url = "https://openexchangerates.org/api/currencies.json?prettyprint=false&show_alternative=false&show_inactive=false&app_id=1";

        $result = Http::get($url)->timeout(10)->json();

        return response()->json(['status' => true, 'data' => $result]);
    }
}
