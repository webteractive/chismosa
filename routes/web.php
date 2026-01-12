<?php

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\RelayController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| contains the "web" middleware group. Now create something great!
|
*/

Route::any('/relay/{id}/{key}', RelayController::class)
    ->middleware(['relay.checkpoint', 'throttle:30,1'])
    ->name('relay');

Route::fallback(function () {
    $request = request();
    $path = $request->path();
    $ip = $request->ip();
    $userAgent = $request->userAgent();

    // Exclude specific paths that should return 404
    if (str_starts_with($path, 'relay') ||
        str_starts_with($path, config('chismosa.admin_path')) ||
        str_starts_with($path, 'livewire') ||
        str_starts_with($path, '_')) {
        abort(404);
    }

    // Track requests per IP for abuse detection
    $cacheKey = "fallback-requests:{$ip}";
    $requestCount = Cache::get($cacheKey, 0);
    $requestCount++;
    Cache::put($cacheKey, $requestCount, now()->addMinutes(15));

    // Log fallback route usage
    Log::info('Fallback route accessed', [
        'ip' => $ip,
        'path' => $path,
        'user_agent' => $userAgent,
        'request_count_15min' => $requestCount,
        'method' => $request->method(),
    ]);

    // Detect potential abuse (more than 30 requests in 15 minutes)
    if ($requestCount > 30) {
        Log::warning('Potential abuse detected on fallback route', [
            'ip' => $ip,
            'path' => $path,
            'request_count_15min' => $requestCount,
            'user_agent' => $userAgent,
        ]);
    }

    $quotes = [
        'The only way to do great work is to love what you do.',
        'Innovation distinguishes between a leader and a follower.',
        'Life is what happens to you while you\'re busy making other plans.',
        'The future belongs to those who believe in the beauty of their dreams.',
        'It is during our darkest moments that we must focus to see the light.',
        'The way to get started is to quit talking and begin doing.',
        'Don\'t let yesterday take up too much of today.',
        'You learn more from failure than from success.',
        'If you are working on something exciting that you really care about, you don\'t have to be pushed. The vision pulls you.',
        'People who are crazy enough to think they can change the world, are the ones who do.',
        'We may encounter many defeats but we must not be defeated.',
        'The only impossible journey is the one you never begin.',
        'In this life we cannot do great things. We can only do small things with great love.',
        'The person who says it cannot be done should not interrupt the person who is doing it.',
        'It is our choices that show what we truly are, far more than our abilities.',
    ];

    $quote = Cache::remember('inspire-quote', now()->addHour(), function () use ($quotes) {
        return $quotes[array_rand($quotes)];
    });

    return response($quote, 200)
        ->header('Content-Type', 'text/plain');
})->middleware('throttle:60,1'); // 60 requests per minute
