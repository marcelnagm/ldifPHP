<?php

require 'vendor/autoload.php';
require './vendor/marcel/LdapSincronyze.php';
use Illuminate\Database\Capsule\Manager as Capsule;

error_reporting(E_ALL);
ini_set('display_errors', 1);

$capsule = new Capsule;

$capsule->addConnection([
    'driver' => 'pgsql',
    'host' => 'database.uerr.edu.br',
    'database' => 'uerr',
    'username' => 'postgres',
    'password' => '',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

// Set the event dispatcher used by Eloquent models... (optional)
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
use marcel\LdapSincronyze;

$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

use Models\usuario;


//$guard = Models\sfguard::query()->where('id','in','(select user_id from sf_guard_user_group )');
//$guard = Models\sf_guard_user_group::query()->where('group_id', '=','2');
$guard = Models\usuario::query()
//        ->where('cpf','=','67284400382')
//        ->has('sfguard.is_active','=','1',true)
//        ->has('sfguard.is_active','=','1',true)
;
$result = $guard->get();

//var_dump($result);

foreach ($result as $row) {
    echo $row->cpf.' - '.$row->contato_email_principal."\n";
    LdapSincronyze::sincron($row->cpf, $row->contato_email_principal);
    
}
