<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Request extends Model
{
    protected $fillable = [
        'server_id',   // The ID of the related server
        'status',      // Health status ('healthy' or 'unhealthy')
        'latency',     // Response latency
    ];

    public $timestamps = true;

    public function server()
    {
        return $this->belongsTo(Server::class);
    }
}
