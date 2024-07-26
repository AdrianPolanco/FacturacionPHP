<?php
$host = 'localhost'; 
$db = 'factura_db';
$user = 'root'; 
$pass = ''; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    if ($e->getCode() == 1049) {

    } else {
        die("Error de conexión: " . $e->getMessage());
    }
}