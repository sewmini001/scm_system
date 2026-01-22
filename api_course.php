<?php require "db.php";
header("Content-Type: application/json");
$id=intval($_GET["course_id"]??0);
if($id<=0){ echo json_encode(["found"=>false]); exit; }
$st=$conn->prepare("SELECT course_id,course_name,duration_months,course_fee FROM courses WHERE course_id=?");
$st->bind_param("i",$id);
$st->execute();
$row=$st->get_result()->fetch_assoc();
echo json_encode(["found"=>!!$row, "course"=>$row]);