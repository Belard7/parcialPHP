<?php
$pdo = null;
for ($i = 0; $i < 20; $i++) {
    try {
        $dsn = "mysql:host=".getenv('DB_HOST').";port=".getenv('DB_PORT').";dbname=".getenv('DB_DATABASE').";charset=utf8mb4";
        $pdo = new PDO($dsn, getenv('DB_USERNAME'), getenv('DB_PASSWORD'), [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
        break;
    } catch (Exception $e) { sleep(2); }
}
if (!$pdo) { http_response_code(500); die("Sin conexion a la base de datos"); }
$pdo->exec("CREATE TABLE IF NOT EXISTS items (id INT AUTO_INCREMENT PRIMARY KEY, nombre VARCHAR(100) NOT NULL, descripcion VARCHAR(255))");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $a = $_POST['accion'] ?? '';
    if ($a === 'crear') {
        $pdo->prepare("INSERT INTO items (nombre, descripcion) VALUES (?, ?)")->execute([$_POST['nombre'], $_POST['descripcion']]);
    } elseif ($a === 'actualizar') {
        $pdo->prepare("UPDATE items SET nombre=?, descripcion=? WHERE id=?")->execute([$_POST['nombre'], $_POST['descripcion'], $_POST['id']]);
    } elseif ($a === 'eliminar') {
        $pdo->prepare("DELETE FROM items WHERE id=?")->execute([$_POST['id']]);
    }
    header('Location: /'); exit;
}
$editar = null;
if (isset($_GET['editar'])) {
    $st = $pdo->prepare("SELECT * FROM items WHERE id=?");
    $st->execute([$_GET['editar']]);
    $editar = $st->fetch(PDO::FETCH_ASSOC);
}
$items = $pdo->query("SELECT * FROM items ORDER BY id DESC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!doctype html>
<html lang="es"><head><meta charset="utf-8"><title>CRUD PHP + MySQL</title>
<style>body{font-family:sans-serif;max-width:700px;margin:30px auto}table{width:100%;border-collapse:collapse}td,th{border:1px solid #ccc;padding:6px}input{padding:6px;margin:3px}</style></head>
<body>
<h1>App A - PHP + MySQL (paas-uno)</h1>
<form method="post">
  <input type="hidden" name="accion" value="<?= $editar ? 'actualizar' : 'crear' ?>">
  <?php if ($editar): ?><input type="hidden" name="id" value="<?= $editar['id'] ?>"><?php endif; ?>
  <input name="nombre" placeholder="Nombre" required value="<?= htmlspecialchars($editar['nombre'] ?? '') ?>">
  <input name="descripcion" placeholder="Descripcion" value="<?= htmlspecialchars($editar['descripcion'] ?? '') ?>">
  <button><?= $editar ? 'Actualizar' : 'Crear' ?></button>
</form>
<table><tr><th>ID</th><th>Nombre</th><th>Descripcion</th><th>Acciones</th></tr>
<?php foreach ($items as $it): ?>
<tr><td><?= $it['id'] ?></td><td><?= htmlspecialchars($it['nombre']) ?></td><td><?= htmlspecialchars($it['descripcion']) ?></td>
<td><a href="/?editar=<?= $it['id'] ?>">Editar</a>
<form method="post" style="display:inline"><input type="hidden" name="accion" value="eliminar"><input type="hidden" name="id" value="<?= $it['id'] ?>"><button>Eliminar</button></form></td></tr>
<?php endforeach; ?>
</table></body></html>
