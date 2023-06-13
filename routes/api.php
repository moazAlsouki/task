<?php

use App\Http\Controllers\FileController;
use App\Http\Controllers\FolderController;
use App\Http\Controllers\UserController;
use App\Models\File;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/
//Auth
route::post("login",[UserController::class,'login']);  
route::post("signup",[UserController::class,'signUp']);
route::group(['middleware'=>['auth:sanctum']],function () {
    //logout
    route::post("logout",[UserController::class,'logout']); 
    
    route::post("folder/create",[FolderController::class,'createFolder']);
    route::get("folder/getbyname/{name}",[FolderController::class,'getFolder']);
    route::get("folder/getbyid/{id}",[FolderController::class,'getFolderById']);
    route::get("folder/getsubfolder/{id}",[FolderController::class,'getSubFolder']);
    route::get("folder/getfolderdetails/{id}",[FolderController::class,'getFolderDetails']);
    route::put("folder/update",[FolderController::class,'updateFolder'])->middleware('checkfolder');
    route::put("folder/change",[FolderController::class,'changeFolder'])->middleware('checkfolder');

    route::post("file/add",[FileController::class,'uploadFile'])->middleware('checkfolder');
    route::put("file/update",[FileController::class,'changefolder']);
    route::get("file/get/{id}",[FileController::class,'download'])->middleware('checkfile');
    route::get("file/getbyname/{name}",[FileController::class,'getFile']);
   
    
    

});
