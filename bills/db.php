<?php
$host = 'sql202.infinityfree.com'; 
$db = 'if0_37015989_bills';
$user = 'if0_37015989'; 
$pass = 'mDPCquln0hmJ5'; 

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    if ($e->getCode() == 1049) {

    } else {
        die("Error de conexión: " . $e->getMessage());
    }
}