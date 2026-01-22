<?php require "db.php";
header("Content-Type: application/json");

$student_id = trim($_GET["student_id"] ?? "");
$course_id  = trim($_GET["course_id"] ?? "");

$where=[]; $types=""; $params=[];
if($student_id!==""){ $where[]="e.student_id=?"; $types.="i"; $params[]=(int)$student_id; }
if($course_id!==""){  $where[]="e.course_id=?";  $types.="i"; $params[]=(int)$course_id; }

$sql="
SELECT
  e.enrollment_id,
  e.course_fee AS total_fee,
  COALESCE(SUM(p.paid_amount),0) AS total_paid,
  (e.course_fee - COALESCE(SUM(p.paid_amount),0)) AS balance
FROM enrollments e
LEFT JOIN payments p ON p.enrollment_id = e.enrollment_id
";
if($where) $sql.=" WHERE ".implode(" AND ",$where);
$sql.=" GROUP BY e.enrollment_id, e.course_fee ORDER BY e.enrollment_id DESC";

$stmt=$conn->prepare($sql);
if($types) $stmt->bind_param($types, ...$params);
$stmt->execute();
$res=$stmt->get_result();

$out=[];
while($row=$res->fetch_assoc()){
  $out[]=[
    "enrollment_id"=>(int)$row["enrollment_id"],
    "total_fee"=>(float)$row["total_fee"],
    "total_paid"=>(float)$row["total_paid"],
    "balance"=>(float)$row["balance"]
  ];
}
echo json_encode($out);