<?php
include 'db.php';

if (!isset($_GET['id'])) {
    die('Factura no encontrada.');
}

$id = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT f.codigo, f.fecha, f.total, c.nombre AS cliente
    FROM facturas f
    JOIN clientes c ON f.cliente_id = c.id
    WHERE f.id = ?
");
$stmt->execute([$id]);
$factura = $stmt->fetch(PDO::FETCH_ASSOC);

$stmt = $pdo->prepare("
    SELECT p.nombre, p.precio, fp.cantidad
    FROM factura_productos fp
    JOIN productos p ON fp.producto_id = p.id
    WHERE fp.factura_id = ?
");
$stmt->execute([$id]);
$productos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (!$factura) {
    die('Factura no encontrada.');
}
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura <?php echo htmlspecialchars($factura['codigo']); ?></title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.0/css/bootstrap.min.css" rel="stylesheet">
    <style>
        @media print {
            .no-print {
                display: none;
            }
        }
    </style>
</head>

<body>
    <div class="container mt-4">
        <h2>Factura <?php echo htmlspecialchars($factura['codigo']); ?></h2>
        <p><strong>Cliente:</strong> <?php echo htmlspecialchars($factura['cliente']); ?></p>
        <p><strong>Fecha:</strong> <?php echo htmlspecialchars($factura['fecha']); ?></p>
        <h3>Productos</h3>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Nombre</th>
                    <th>Precio</th>
                    <th>Cantidad</th>
                    <th>Total</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($productos as $producto) : ?>
                    <tr>
                        <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                        <td>$<?php echo htmlspecialchars($producto['precio']); ?></td>
                        <td><?php echo htmlspecialchars($producto['cantidad']); ?></td>
                        <td>$<?php echo htmlspecialchars($producto['precio'] * $producto['cantidad']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <p><strong>Total Factura:</strong> $<?php echo htmlspecialchars($factura['total']); ?></p>
        <button class="btn btn-secondary no-print" onclick="window.print()">Imprimir</button>
        <a href="index.php" class="btn btn-primary no-print">Volver</a>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" integrity="sha384-oBqDVmMz9ATKxIep9tiCxS/Z9fNfEXiDAYTujMAeBAsjFuCZSmKbSSUnQlmh/jp3" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.min.js" integrity="sha384-cuYeSxntonz0PPNlHhBs68uyIAVpIIOZZ5JqeqvYYIcEL727kskC66kF92t6Xl2V" crossorigin="anonymous"></script>
</body>

</html>