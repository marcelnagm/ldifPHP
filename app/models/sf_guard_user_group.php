<?php
namespace Models;
 
use \Illuminate\Database\Eloquent\Model;
use Models\sfguard;

class sf_guard_user_group extends Model {
     
    protected $table = 'sf_guard_user_group';
     
    public function user()
    {
        return $this->belongsTo('Models\sfguard','id');
    }
}