<?php
// Aplica automáticamente los cambios de esquema a bases de datos creadas con la versión del TFG entregada.
// Equivale a ejecutar database/migracion_portfolio.sql; en instalaciones nuevas no hace nada.

function aplicaMigraciones(PDO $pdo)
{
    $existe = $pdo->query("SELECT COUNT(*) FROM INFORMATION_SCHEMA.TABLES
        WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'metodos_pago'")->fetchColumn();
    if ($existe) return;

    $sql = file_get_contents(__DIR__ . '/../database/migracion_portfolio.sql');
    // Quitar comentarios y ejecutar sentencia a sentencia
    $sql = preg_replace('/^--.*$/m', '', $sql);
    foreach (array_filter(array_map('trim', explode(';', $sql))) as $sentencia) {
        $pdo->exec($sentencia);
    }
}
