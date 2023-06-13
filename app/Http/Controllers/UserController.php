<?php

namespace App\Http\Controllers;

use App\Models\Folder;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Date;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Throwable;

use function League\Flysystem\Local\read;

class UserController extends Controller
{
    //


    public function signUp(Request $request)
    {
        try{
            $val =Validator::make($request->all(),[
                'name' => 'required|string',
                'email' => 'required|email|unique:users',
                'password'=> 'required|string'
            ]);
            if($val->fails()){
                return response([
                    "message" => $val->messages()
                ],400);
            }
            $user = new User();
            $user->name=$request->name;
            $user->email=$request->email;
            $user->password=bcrypt($request->password);
            $user->save();
            $folder=new Folder();
            $folder->user_id=$user->id;
            $folder->name='root';
            $folder->description='root folder for user :'.$user->name;
            $folder->is_public=false;
            $folder->save();
            if ($user){
                
                return response([
                    "user"=>$user,
                    "root folder"=>$folder
                ],201);
            }
            else{
                return response([
                    'message'=>'Wronge In Register'
                ],400);
            }
        }
        catch(Throwable $e){
            echo($e->getMessage());
            return response([
                "message"=>"server error"
            ],500);
        } 
    }

    public function logout(Request $request){
        try{  
            $request->user()->currentAccessToken()->delete();
            return response([
                'message'=>'Logged out'
            ],201);
        }
        catch(Throwable $e){
            echo($e->getMessage());
            return response([
                "message"=>"server error"
            ],500);
        }  
    }
    public function login(Request $request)
    {
        try{
            $val =Validator::make($request->all(),[
                'email' => 'required|email',
                'password'=> 'required|string'
            ]);
            if($val->fails()){
                return response([
                    "message" => $val->messages()
                ],400);
            }
            $user = User::where('email',$request->email)->first();
            
            if(!$user )
            {
                return response([
                    'message'=>'email is not exist'
                ],401);
            }
            if(!Hash::check($request->password,$user->password)){
                return response([
                    'message'=>'password is wrong'
                ],401);
            }
            $token =$user->createToken('task')->plainTextToken;

            $response= [
                'user' => $user,
                'token' => $token
            ];

            return response($response,201);
        }
        catch(Throwable $e){
            echo($e->getMessage());
            return response([
                "message"=>"server error"
            ],500);
        }    
    }
}
