<?php
require "db.php";

$student_id = intval($_GET['student_id'] ?? 0);

if($student_id > 0){
    $st = $conn->prepare("SELECT student_name FROM students WHERE student_id=?");
    $st->bind_param("i", $student_id);
    $st->execute();
    $res = $st->get_result()->fetch_assoc();

    if($res){
        echo json_encode(['success'=>true,'name'=>$res['student_name']]);
    } else {
        echo json_encode(['success'=>false]);
    }
} else {
    echo json_encode(['success'=>false]);
}
