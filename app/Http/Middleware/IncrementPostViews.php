<?php

namespace App\Http\Middleware;

use App\Models\Post;
use App\Models\PostViewLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class IncrementPostViews
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        if ($response->isSuccessful() && $request->route('post') instanceof Post) {
            $post = $request->route('post');
            $post->increment('views');
            PostViewLog::create([
                'post_id' => $post->id,
                'ip_hash' => hash('sha256', (string) $request->ip()),
                'viewed_at' => now(),
            ]);
        }

        return $response;
    }
}
