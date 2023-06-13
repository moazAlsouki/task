<?php

namespace App\Http\Middleware;

use App\Models\File;
use App\Models\Folder;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

use function League\Flysystem\Local\read;

class CheckFile
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try{
            $file=File::find($request->file_id);
            $folder=Folder::find($file->folder_id);
            if($folder->user_id==Auth::user()->id || $file->is_public==true){
                return $next($request);
            }
            else
                return response([
                    "message"=>"cant edit this file"
                ],400);

        }
        catch(Throwable $e){
            echo($e->getMessage());
            return response([
                "message"=>"server error"
            ],500);
        } 
    }
}
