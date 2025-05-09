<?php

namespace App\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Throwable;

class Handler extends ExceptionHandler
{

  protected function unauthenticated($request, AuthenticationException $exception)
  {
    if ($request->expectsJson()) {
      return response()->json(['message' => 'Unauthorized.'], 401);
    }

    return redirect()->guest(route('login'));
  }
}
