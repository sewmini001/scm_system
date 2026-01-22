<?php require "db.php";
header("Content-Type: application/json");
$id=intval($_GET["student_id"]??0);
if($id<=0){ echo json_encode(["found"=>false]); exit; }
$st=$conn->prepare("SELECT student_id,student_name,nic,email FROM students WHERE student_id=?");
$st->bind_param("i",$id);
$st->execute();
$row=$st->get_result()->fetch_assoc();
echo json_encode(["found"=>!!$row, "student"=>$row]);
