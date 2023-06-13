<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use GuzzleHttp\Psr7\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Throwable;

class FolderController extends Controller
{
    
    public function createFolder(Request $request){
        try{
            $val =Validator::make($request->all(),[
                'parent_id' => 'required',
                'name' => 'required|string',
                'is_public' => 'required|boolean',
                'description'=> 'required|string'
            ]);
            if($val->fails()){
                return response([
                    "message" => $val->messages()
                ],400);
            }
            $parent=Folder::find($request->parent_id);
            foreach($parent->getSubFolers as $sub)
            {
                if($sub->name == $request->name)
                {
                    return Response([
                        "message"=>"the folder name already exists"
                    ],400);
                }
            }
            $folder=new Folder();
            $folder->user_id=Auth::user()->id;
            $folder->name=$request->name;
            $folder->parent_id=$request->parent_id;
            $folder->description=$request->description;
            $folder->is_public=$request->is_public;
            $folder->save();
            if($folder)
                return Response([
                    "folder"=>$folder
                ],201);
            else
            return Response([
                "message"=>"unable to create folder"
            ],400);
        }
        catch(Throwable $e){
            echo($e->getMessage());
            return response([
                "message"=>"server error"
            ],500);
        } 
    }

    public function getFolder($name){
        try{
            $myfolders= DB::table('folders')
            ->where('name','like',$name.'%')
            ->where('user_id','=',Auth::user()->id)->get();
            
            $publicfolders= DB::table('folders')
            ->where('name','like',$name.'%')
            ->where('user_id','!=',Auth::user()->id)
            ->where('is_public','=',true)->get();
            return Response([
                "myFolders"=>$myfolders,
                "publicFolder"=>$publicfolders
            ],201);

        }
        catch(Throwable $e){
            echo(["message"=>$e->getMessage()]);
            return response([
                "message"=>"server error"
            ],500);
        }
    }
    public function getFolderById($id){
        try{
            $publicfolder= DB::table('folders')
            ->where('id','=',$id)
            ->Where('is_public','=',true)->get();
            $folder= DB::table('folders')
            ->where('id','=',$id)
            ->where('user_id','=',Auth::user()->id)->get();
            if($folder)
                return Response([
                    "folder"=>$folder
                ],201);
            elseif($publicfolder)
            return Response([
                "folder"=>$publicfolder
            ],201); 
            else
                return Response([
                    "message"=>"Not Found"
                ],404);
        }
        catch(Throwable $e){
            echo($e->getMessage());
            return response([
                "message"=>"server error"
            ],500);
        } 
    }

    public function getSubFolder($id){
        try{
            $publicfolder= Folder::where('id','=',$id)
            ->Where('is_public','=',true)->first();
            $folder= Folder::where('id','=',$id)
            ->where('user_id','=',Auth::user()->id)->first();
            if($folder)
                return Response([
                    "folders"=>$folder->getSubFolers
                ],201);
            elseif($publicfolder)
            return Response([
                "folders"=>$publicfolder
            ],201); 
            else
                return Response([
                    "message"=>"Not Found"
                ],404);
        }
        catch(Throwable $e){
            echo($e->getMessage());
            return response([
                "message"=>"server error"
            ],500);
        } 
    }
    public function updateFolder(Request $request){
         try{
                $folder=Folder::find($request->folder_id);
                $folder->name=$request->name;
                $folder->description=$request->description;
                $folder->is_public=$request->is_public;
                $f=$folder->save();
                if($f)
                    return Response([
                        "folder"=>$folder
                    ],201);
                else
                return Response([
                    "message"=>"unable to edit folder"
                ],400);
            }
            catch(Throwable $e){
                echo($e->getMessage());
                return response([
                    "message"=>"server error"
                ],500);
            } 
        }

        public function changeFolder(Request $request){
            try{
                    $folder=Folder::find($request->folder_id);
                    if ($folder->parent_id==null)
                        return response([
                            "message"=>"cant move root folder"
                        ],401);
                    if($request->dist_id==$request->folder_id){
                        return response([
                            "message"=>"cant move folder to it self"
                        ],201);
                    }
                    $dis=Folder::find($request->dist_id);
                    if(!$this->getparent($dis,$folder->id))
                        return response([
                            "message"=>"cant move to child folder"
                        ],401);
                    $folder->parent_id=$request->dist_id;
                    $folder->save();
                    return response([
                        "message"=>"folder moved successfully"
                    ],201);

            }
            catch(Throwable $e){
                echo($e->getMessage());
                return response([
                    "message"=>"server error"
                ],500);
        }
    }
        
    public function getparent($folder,$dis_id) {
        if($folder->parent_id==null)
            return true;
        elseif($folder->parent_id==$dis_id)
            return false;
        else {
            $par=Folder::find($folder->parent_id);
            return $this->getparent($par,$dis_id);
        }
    }
        public function getFolderDetails($id){
        
            try{
                $folder =Folder::find($id);
                if ($folder)
                return response([
                    "folders"=>$folder->getSubFolers,
                    "files"=>$folder->getFiles
            ],201);
                else return response([
                    "message"=>"Not found"
                ],404);
            }
            catch(Throwable $e){
                echo($e->getMessage());
                return response([
                    "message"=>"server error"
                ],500);
            }
        }
}


