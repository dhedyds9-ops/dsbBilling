<?php
$pdo = new PDO('sqlite:D:/dsBilling/database/database.sqlite');
$stmt = $pdo->query("SELECT DISTINCT service_type FROM service_profiles");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $row) {
    echo $row['service_type'] . "\n";
}
?>
