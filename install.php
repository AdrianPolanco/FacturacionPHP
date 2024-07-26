<?php
$host = 'localhost';
$db = 'factura_db';
$user = 'root';
$pass = '';

try {
    // Primero, conectar sin seleccionar una base de datos
    $pdo = new PDO("mysql:host=$host", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Crear base de datos si no existe
    $pdo->exec("CREATE DATABASE IF NOT EXISTS $db");
    $pdo->exec("USE $db");

    // Crear tablas
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS clientes (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            email VARCHAR(255) NOT NULL
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS productos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            nombre VARCHAR(255) NOT NULL,
            precio DECIMAL(10, 2) NOT NULL
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS facturas (
            id INT AUTO_INCREMENT PRIMARY KEY,
            codigo VARCHAR(255) NOT NULL,
            cliente_id INT NOT NULL,
            fecha DATE NOT NULL,
            total DECIMAL(10, 2) NOT NULL,
            FOREIGN KEY (cliente_id) REFERENCES clientes(id)
        );
    ");

    $pdo->exec("
        CREATE TABLE IF NOT EXISTS factura_productos (
            id INT AUTO_INCREMENT PRIMARY KEY,
            factura_id INT NOT NULL,
            producto_id INT NOT NULL,
            cantidad INT NOT NULL,
            FOREIGN KEY (factura_id) REFERENCES facturas(id),
            FOREIGN KEY (producto_id) REFERENCES productos(id)
        );
    ");

    // Crear tabla para registrar el estado de la instalación
    $pdo->exec("
        CREATE TABLE IF NOT EXISTS instalacion (
            id INT PRIMARY KEY,
            completada BOOLEAN NOT NULL
        );
    ");

    // Marcar la instalación como completada
    $pdo->exec("INSERT INTO instalacion (id, completada) VALUES (1, TRUE) ON DUPLICATE KEY UPDATE completada = TRUE");

    // Mostrar mensaje de éxito y redirigir a index.php
    echo '<script>alert("Instalación completada con éxito."); window.location.href = "index.php";</script>';
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}
