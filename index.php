<?php

include 'db.php';
if (!isset($pdo)) {
   echo '<form action="install.php" method="post">
            <button type="submit">Instalar</button>
          </form>';
    exit; 
}

 try {
        $pdo = new PDO('mysql:host=localhost;dbname=factura_db', 'root', '');
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        
        $stmt = $pdo->query("SHOW TABLES LIKE 'instalacion'");
        $tableExists = $stmt->fetchColumn();

        if (!$tableExists) {
                      
             echo '<form action="install.php" method="post">
                <button type="submit">Instalar</button>
              </form>';
            exit; 
        }

                       
        $stmt = $pdo->query("SELECT completada FROM instalacion WHERE id = 1");
        $instalacionCompletada = $stmt->fetchColumn();

         if (!$instalacionCompletada or $instalacionCompletada == null) {
            echo '<form action="install.php" method="post">
                <button type="submit">Instalar</button>
              </form>';
            exit;
        }
    } catch (PDOException $e) {
        die("Error de conexión: " . $e->getMessage());
   }
// CRUD de Clientes
if (isset($_POST['add_cliente'])) {
    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $stmt = $pdo->prepare("INSERT INTO clientes (nombre, email) VALUES (?, ?)");
    $stmt->execute([$nombre, $email]);
}
if (isset($_GET['delete_cliente'])) {
    $id = $_GET['delete_cliente'];
    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = ?");
    $stmt->execute([$id]);
}

// CRUD de Productos
if (isset($_POST['add_producto'])) {
    $nombre = $_POST['nombre'];
    $precio = $_POST['precio'];
    $stmt = $pdo->prepare("INSERT INTO productos (nombre, precio) VALUES (?, ?)");
    $stmt->execute([$nombre, $precio]);
}
if (isset($_GET['delete_producto'])) {
    $id = $_GET['delete_producto'];
    $stmt = $pdo->prepare("DELETE FROM productos WHERE id = ?");
    $stmt->execute([$id]);
}

// Crear Factura
if (isset($_POST['add_factura'])) {
    $cliente_id = $_POST['cliente_id'];
    $fecha = $_POST['fecha'];
    $productos = $_POST['producto_id'];
    $cantidades = $_POST['cantidad'];

    $codigo = uniqid('FAC-'); // Generar código único

    $stmt = $pdo->prepare("INSERT INTO facturas (codigo, cliente_id, fecha, total) VALUES (?, ?, ?, 0)");
    $stmt->execute([$codigo, $cliente_id, $fecha]);
    $factura_id = $pdo->lastInsertId();

    $total = 0;
    for ($i = 0; $i < count($productos); $i++) {
        $producto_id = $productos[$i];
        $cantidad = $cantidades[$i];
        $stmt = $pdo->prepare("SELECT precio FROM productos WHERE id = ?");
        $stmt->execute([$producto_id]);
        $precio = $stmt->fetchColumn();

        $total += $precio * $cantidad;

        $stmt = $pdo->prepare("INSERT INTO factura_productos (factura_id, producto_id, cantidad) VALUES (?, ?, ?)");
        $stmt->execute([$factura_id, $producto_id, $cantidad]);
    }

    $stmt = $pdo->prepare("UPDATE facturas SET total = ? WHERE id = ?");
    $stmt->execute([$total, $factura_id]);
}

// Obtener datos
$clientes = $pdo->query("SELECT * FROM clientes")->fetchAll(PDO::FETCH_ASSOC);
$productos = $pdo->query("SELECT * FROM productos")->fetchAll(PDO::FETCH_ASSOC);
$facturas = $pdo->query("
    SELECT f.id, f.codigo, f.fecha, f.total, c.nombre AS cliente
    FROM facturas f
    JOIN clientes c ON f.cliente_id = c.id
")->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Facturación</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
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
        <!-- CRUD de Clientes -->
        <h2 class="mb-4">Clientes</h2>
        <form method="POST" class="mb-4">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="nombre_cliente" class="form-label">Nombre</label>
                    <input type="text" id="nombre_cliente" name="nombre" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="email_cliente" class="form-label">Email</label>
                    <input type="email" id="email_cliente" name="email" class="form-control" required>
                </div>
            </div>
            <button type="submit" name="add_cliente" class="btn btn-primary">Agregar Cliente</button>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Email</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($clientes as $cliente) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cliente['id']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['nombre']); ?></td>
                            <td><?php echo htmlspecialchars($cliente['email']); ?></td>
                            <td><a href="?delete_cliente=<?php echo $cliente['id']; ?>" class="btn btn-danger">Eliminar</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- CRUD de Productos -->
        <h2 class="mt-4 mb-4">Productos</h2>
        <form method="POST" class="mb-4">
            <div class="row mb-3">
                <div class="col-md-6">
                    <label for="nombre_producto" class="form-label">Nombre</label>
                    <input type="text" id="nombre_producto" name="nombre" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="precio_producto" class="form-label">Precio</label>
                    <input type="number" id="precio_producto" name="precio" class="form-control" step="0.01" required>
                </div>
            </div>
            <button type="submit" name="add_producto" class="btn btn-primary">Agregar Producto</button>
        </form>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($productos as $producto) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($producto['id']); ?></td>
                            <td><?php echo htmlspecialchars($producto['nombre']); ?></td>
                            <td>$<?php echo htmlspecialchars($producto['precio']); ?></td>
                            <td><a href="?delete_producto=<?php echo $producto['id']; ?>" class="btn btn-danger">Eliminar</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Crear Factura -->
        <h2 class="mt-4 mb-4">Crear Factura</h2>
        <form method="POST" class="mb-4">
            <div class="mb-3">
                <label for="cliente_id" class="form-label">Cliente</label>
                <select id="cliente_id" name="cliente_id" class="form-select" required>
                    <?php foreach ($clientes as $cliente) : ?>
                        <option value="<?php echo $cliente['id']; ?>"><?php echo htmlspecialchars($cliente['nombre']); ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="mb-3">
                <label for="fecha" class="form-label">Fecha</label>
                <input type="date" id="fecha" name="fecha" class="form-control" required>
            </div>
            <div id="productos-container">
                <div class="mb-3">
                    <label class="form-label">Productos</label>
                    <div class="input-group mb-3">
                        <select name="producto_id[]" class="form-select" required>
                            <?php foreach ($productos as $producto) : ?>
                                <option value="<?php echo $producto['id']; ?>"><?php echo htmlspecialchars($producto['nombre']); ?> - $<?php echo htmlspecialchars($producto['precio']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <input type="number" name="cantidad[]" class="form-control" placeholder="Cantidad" min="1" required>
                    </div>
                </div>
            </div>
            <button type="button" class="btn btn-secondary" onclick="addProduct()">Añadir Producto</button>
            <button type="submit" name="add_factura" class="btn btn-primary">Crear Factura</button>
        </form>

        <!-- Historial de Facturas -->
        <h2 class="mt-4 mb-4">Historial de Facturas</h2>
        <div class="table-responsive">
            <table class="table table-striped table-bordered">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Código</th>
                        <th>Cliente</th>
                        <th>Fecha</th>
                        <th>Total</th>
                        <th>Acción</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($facturas as $factura) : ?>
                        <tr>
                            <td><?php echo htmlspecialchars($factura['id']); ?></td>
                            <td><?php echo htmlspecialchars($factura['codigo']); ?></td>
                            <td><?php echo htmlspecialchars($factura['cliente']); ?></td>
                            <td><?php echo htmlspecialchars($factura['fecha']); ?></td>
                            <td>$<?php echo htmlspecialchars($factura['total']); ?></td>
                            <td><a href="print_invoice.php?id=<?php echo $factura['id']; ?>" class="btn btn-info no-print">Imprimir</a></td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script>
        function addProduct() {
            const container = document.getElementById('productos-container');
            const newProduct = document.createElement('div');
            newProduct.classList.add('mb-3');
            newProduct.innerHTML = `
                <div class="input-group mb-3">
                    <select name="producto_id[]" class="form-select" required>
                        <?php foreach ($productos as $producto) : ?>
                        <option value="<?php echo $producto['id']; ?>"><?php echo htmlspecialchars($producto['nombre']); ?> - $<?php echo htmlspecialchars($producto['precio']); ?></option>
                        <?php endforeach; ?>
                    </select>
                    <input type="number" name="cantidad[]" class="form-control" placeholder="Cantidad" min="1" required>
                </div>
            `;
            container.appendChild(newProduct);
        }
    </script>
</body>

</html>