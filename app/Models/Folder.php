<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    use HasFactory;


    public function getSubFolers(){
        return $this->hasMany('App\Models\Folder','parent_id');
    }

    public function getFiles(){
        return $this->hasMany('App\Models\File','folder_id');
    }

}
