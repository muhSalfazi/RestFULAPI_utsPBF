<?php

namespace App\Http\Middleware;

use Closure;

class ConvertPutToPost
{
    public function handle($request, Closure $next)
    {
        if ($request->isMethod('put') || $request->isMethod('patch')) {
            if ($request->header('Content-Type') === 'multipart/form-data') {
                $request->setMethod('POST');
                $request->merge(['_method' => $request->getMethod()]);
            }
        }

        return $next($request);
    }
}