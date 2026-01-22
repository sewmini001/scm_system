<?php
require "db.php";

$course_id = intval($_GET['course_id'] ?? 0);

if($course_id > 0){
    $st = $conn->prepare("SELECT course_name FROM courses WHERE course_id=?");
    $st->bind_param("i", $course_id);
    $st->execute();
    $res = $st->get_result()->fetch_assoc();

    if($res){
        echo json_encode(['success'=>true,'name'=>$res['course_name']]);
    } else {
        echo json_encode(['success'=>false]);
    }
} else {
    echo json_encode(['success'=>false]);
}
