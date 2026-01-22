<?php
require "db.php";

$id = intval($_GET["id"] ?? 0);

if ($id > 0) {
    $stmt = $conn->prepare("
        UPDATE students 
        SET is_deleted = 1 
        WHERE student_id = ?
    ");
    $stmt->bind_param("i", $id);
    $stmt->execute();
}

/* Redirect back to list */
header("Location: student_list.php");
exit;
