<?php
global $wpdb;
use Illuminate\Database\Capsule\Manager as Capsule;

$capsule = new Capsule;

$capsule->addConnection([
	'driver'    => 'mysql',
	'host'      => DB_HOST,
	'database'  => DB_NAME,
	'username'  => DB_USER,
	'password'  => DB_PASSWORD,
	'charset'   => 'utf8',
	'collation' => 'utf8_unicode_ci',
	'prefix'    => $wpdb->prefix,
]);

// Set the event dispatcher used by Eloquent models... (optional)
use Illuminate\Events\Dispatcher;
use Illuminate\Container\Container;
$capsule->setEventDispatcher( new Dispatcher( new Container ) );

// Make this Capsule instance available globally via static methods... (optional)
$capsule->setAsGlobal();

// Setup the Eloquent ORM... (optional; unless you've used setEventDispatcher())
$capsule->bootEloquent();
<<<<<<< HEAD
=======

$users = Capsule::table( 'users' )->get();
print_r( $users );
>>>>>>> 1e7091fb6b661c5ff8035f4e3f590e192127eb22
