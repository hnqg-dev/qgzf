<?php
namespace app\middleware;

use think\facade\Session;
use think\Response;
use app\index\model\User;

class AuthCheck
{
    public function handle($request, \Closure $next)
    {
        $userId = Session::get('user_id');
        if (!$userId) {
            return redirect('/');
        }

        $user = User::getUserById($userId);
        if (!$user) {
            Session::delete('user_id');
            return redirect('/');
        }

        $referer = $request->header('referer');
        $host = $request->host();
        $allowedPaths = ['/login', '/register']; 
        
        if (!$referer && !in_array($request->path(), $allowedPaths)) {
            return redirect('/');
        }
        
        if ($referer && !str_contains($referer, $host)) {
            return redirect('/');
        }

        return $next($request);
    }
}
