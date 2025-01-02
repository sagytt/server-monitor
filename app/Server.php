<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Server extends Model
{
    protected $fillable = [
        'name',
        'url',
        'protocol',
        'ftp_username',
        'ftp_password',
    ];


    public function requests()
    {
        return $this->hasMany(Request::class);
    }
}
