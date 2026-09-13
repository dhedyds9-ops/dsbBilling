<?php
$pdo = new PDO('sqlite:D:/dsBilling/database/database.sqlite');
$stmt = $pdo->query("SELECT DISTINCT status FROM members");
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach($rows as $row) {
    echo $row['status'] . "\n";
}
?>
