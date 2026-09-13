<?php
$pdo = new PDO('sqlite:D:/dsBilling/database/database.sqlite');
$stmt = $pdo->query("PRAGMA table_info('members')");
$columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($columns as $col) {
    echo $col['name'] . " - NotNull: " . $col['notnull'] . " - Default: " . $col['dflt_value'] . "\n";
}
?>
