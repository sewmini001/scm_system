<?php
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

$host = "localhost";
$user = "root";
$pass = "";
$db   = "scm_system";
$port = 4306;


$conn = new mysqli($host, $user, $pass, $db, $port);
$conn->set_charset("utf8mb4");

function h($s){ return htmlspecialchars($s ?? "", ENT_QUOTES, "UTF-8"); }