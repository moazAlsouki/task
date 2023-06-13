<?php

namespace App\Http\Middleware;

use App\Models\Folder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

class CheckFolder
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        try{
            $id=$request->folder_id;
            $folder=Folder::find($id);
            if(Auth::user()->id==$folder->user_id)
                return $next($request);
            else
                return Response([
                    "message"=>"unable to edit this folder"
                ],401);
        }
        catch(Throwable $e){
            echo($e->getMessage());
            return response([
                "message"=>"server error"
            ],500);
        } 
    }
}
