<?php

require 'vendor/autoload.php';

use Illuminate\Database\Capsule\Manager as Capsule;

error_reporting(E_ALL);
ini_set('display_errors', 1);

$capsule = new Capsule;

$capsule->addConnection([
    'driver' => 'mysql',
    'host' => 'localhost',
    'database' => 'academico',
    'username' => 'root',
    'password' => '123',
    'charset' => 'utf8',
    'collation' => 'utf8_unicode_ci',
    'prefix' => '',
]);

// Set the event dispatcher used by Eloquent models... (optional)
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;

$capsule->setEventDispatcher(new Dispatcher(new Container));

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();

use LdapTools\Configuration;
use LdapTools\LdapManager;
use LdapTools\Object\LdapObjectType;
use Models\sfguard;
use Models\sf_guard_user_group;

$config = (new Configuration())->load('config.yml');
$ldap = new LdapManager($config);


//$guard = Models\sfguard::query()->where('id','in','(select user_id from sf_guard_user_group )');
//$guard = Models\sf_guard_user_group::query()->where('group_id', '=','2');
$guard = Models\sf_guard_user_group::query()
//        ->where('is_active','=','1')
//        ->has('sfguard.is_active','=','1',true)
//        ->has('sfguard.is_active','=','1',true)
        ->where('group_id', '=', '2')
;
$result = $guard->get();

//var_dump($result);
use LdapTools\Ldif\Ldif;

//
$ldif = new Ldif();
$ldif->setVersion(1);

foreach ($result as $row) {
//    echo $row->user_id;
    $user = Models\sfguard::query()->find($row->user_id);
    if ($user->is_active == 1) {
        $mat = Models\matriculation::query()->where('user_id', '=', $row->user_id)->get();
//        echo $mat->first()->student_id . ' --- ' . count($mat);
        $mat =$mat->first();
        if (count($mat) == 1) {
        $student= Models\student::query()->where('id','=',$mat->student_id)->get();
//        echo $mat->student_id."----- count student".count($student);
        $student = $student->first();
//        var_dump($student   );
//        echo "________";
        $cn = str_replace('.','',str_replace('-', '', $student->cpf));
        $sn = explode(' ', $student->name);
        $sn = $sn[count($sn)-1];
            $entry = $ldif->entry()->add('cn=' . $cn . ',ou=alunos,dc=uerr,dc=edu,dc=br')
                    ->addAttribute('cn', $cn)
                    ->addAttribute('gidnumber', $user->username)
                    ->addAttribute('givenname', $student->name)
                    ->addAttribute('objectclass', array('inetOrgPerson', 'posixAccount', 'top'))
                    ->addAttribute('sn',$sn )
                    ->addAttribute('uid', $user->username)
                    ->addAttribute('uidnumber', $user->username)
                    ->addAttribute('userpassword', '123')
                    ->addAttribute('homedirectory', '/home/aluno')
                    ->addAttribute('mail', $mat->email);



// Add the created entry to the LDIF. This method is variadic, so add as many entries at a time as you like.
            $ldif->addEntry($entry);
        }
    }
}
// where generate the ldif file to import to our server
//// Add a few comments to appear at the top of the LDIF...
////$ldif->addComment('This is just a test LDIF file', 'Created on '.date("m.d.y"));
//
//
//// Output the LDIF object as a string to a file.
////file_put_contents('/path/to/ldif.txt', $ldif->toString());
//// Output the LDIF to a string and do whatever you need with it...
$ldifData = $ldif->toString();
echo $ldifData;
// cn=00387046259,ou=dti,dc=uerr,dc=edu,dc=br