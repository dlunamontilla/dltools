<?php

include __DIR__ . "/vendor/autoload.php";

use DLTools\Controllers\DLConfig;
use DLTools\Controllers\DLRequest;
use DLTools\Models\DLDatabase;

$db = new DLDatabase;

// $stmt = $pdo->prepare("INSERT INTO `products` (`name`, `lastname`) VALUES(:name, :lastname)");

// $pdo->beginTransaction();

// foreach($datos as $register) {
//     $stmt->execute($register);
// }

// $pdo->commit();


// $stmt = $pdo->prepare("INSERT INTO `products` (`name`, `lastname`) VALUES(:name, :lastname)");

// $pdo->beginTransaction();

// $stmt->execute($datos);

// $pdo->commit();

// Probando la inserción de los datos | Registro simple:
// $db->to('products')->insert([
//     "name" => "David Eduardo",
//     "lastname" => "Luna Montilla"
// ]);

// $db->to('products')->insert([
//     [
//         "name" => "Ciencia",
//         "lastname" => "De datos"
//     ],

//     [
//         "name" => "Una computadora",
//         "lastname" => "Otra computadora"
//     ],

//     [
//         "name" => "David Eduardo",
//         "lastname" => "Luna Montilla"
//     ]
// ]);


// Devolver datos de una tabla:

// print_r($db->from('products')->select()->get());
// print_r(
//     $db->from('products')->first()
// );


// Devolver el último registro:
// print_r(
//     $db->from('products')->last()
// );

// $data = $db->select('name')->from('products')->limit(2)->get();
// $data = $db->select('name')->from('products')->first();
$data = [];

// $data = $db->query("SELECT * FROM products WHERE name LIKE :name")->get([
//     ":name" => '%eduardo%'
// ]);

// $data = $db->from('products')->select('lastname')->where('name', 'like', '%eduardo%')->where('lastname', 'like', '%luna%')->get();

// $data = $db->from('products')->last('id');

// $data = $db->select('id')->from('products')->orderBy('id')->desc()->limit(3)->get();


$db->from('products')->update([
    "name" => 'Walter Quimey',
    "lastname" => 'Galtieri'
]);

$data = $db->from('products')->where('id', 100)->first();

// $data = $db->from('products')->where('id', 8)->get();
// // $data = $db->from('products')->select('lastname')->get();
// $queryString = $db->from('products')->where('id', 7)->getQuery();
// $db->clean();

echo json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
// echo $queryString . PHP_EOL;entorno

// // $db->from('products')->delete();
// $db->from('products')->where('name', 'like', '%eduardo%')->delete();

use DLTools\Controllers\DLAuth;

$auth = new DLAuth;

echo $auth->getToken() . PHP_EOL;