<?php

namespace App\Http\Controllers;

use App\Models\File;
use App\Models\Folder;
use Illuminate\Contracts\Cache\Store;
use Illuminate\Http\Request;
use Illuminate\Http\Response as HttpResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator as FacadesValidator;
use Throwable;

use function League\Flysystem\Local\read;

class FileController extends Controller
{
    //


    public function uploadFile(Request $request){

        $val =FacadesValidator::make($request->all(),[
            'folder_id' => 'required',
            'name' => 'required|string',
            'file'=> 'required|file'
        ]);
        if($val->fails()){
            return response([
                "message" => $val->messages()
            ],400);
        }
        try{
            $f = $request->file('file');
            $file=new File();
            $file->folder_id=$request->folder_id;
            $file->name=$request->name.'.' . $f->getClientOriginalExtension();
            $folder=Folder::find($request->folder_id);
            foreach($folder->getFiles as $fff)
            {
                if($fff->name == $file->name)
                {
                    return Response([
                        "message"=>"the file name already exists"
                    ],400);
                }
            }
            $fileName = time() . '.' . $f->getClientOriginalExtension();
            $filePath = $f->storePubliclyAs('uploads', $fileName, 'public');
            $file->is_public=$folder->is_public;
            $file->path=$filePath;
            $file->save();
            return Response([
                "File"=>$file,
                "message"=>"File Uploaded"
            ],201);
        }
        catch(Throwable $e){
            echo($e->getMessage());
            return response([
                "message"=>"server error"
            ],500);
        } 

    }


    public function download($id)
    {
        try{
            $fi=File::find($id);
            if(!$fi){
                return response([
                    "message"=>"not found"
                  ],404);
            }
            $filename=$fi->path;

            $disk = Storage::disk('public');
    
            if (! $disk->exists($filename)) {
              return response([
                "message"=>"not found"
              ],404);
            }
    
            $pathToFile = $disk->path($filename);
            $mime = $disk->mimeType($filename);
    
            return response()->download($pathToFile, $fi->name, [
                'Content-Type' => $mime,
            ]);
        }
        catch(Throwable $e){
            echo($e->getMessage());
            return response([
                "message"=>"server error"
            ],500);
        } 
    }


    public function getFile($name){
        try{
            $files= DB::table('files')
            ->where('name','like',$name.'%')
            ->where('user_id','=',Auth::user()->id)->get();
            
            $publicfiles= DB::table('files')
            ->where('name','like',$name.'%')
            ->where('user_id','!=',Auth::user()->id)
            ->where('is_public','=',true)->get();
            return Response([
                "Files"=>$files,
                "publicFiles"=>$publicfiles
            ],201);

        }
        catch(Throwable $e){
            echo(["message"=>$e->getMessage()]);
            return response([
                "message"=>"server error"
            ],500);
        }
    }

    public function changefolder(Request $request){
        try{
            $file=File::find($request->file_id);
            $folder=Folder::find($request->folder_id);
            
            foreach($folder->getFiles as $fff)
            {                
                if($fff->name == $file->name)
                {
                    return Response([
                        "message"=>"the file name already exists in distination folder"
                    ],400);
                }
            }
            $file->folder_id=$folder->id;
            $file->save();
            return response([
                "message"=>"file moved successfully",
                "file"=>$file
            ]);
        }
        catch(Throwable $e){
            echo($e->getMessage());
            return response([
                "message"=>"server error"
            ],500);
        } 
    }
}
