<?php require "db.php";
header("Content-Type: application/json");

$course_id = intval($_GET["course_id"] ?? 0);
if($course_id<=0){ echo json_encode([]); exit; }

$st=$conn->prepare("
  SELECT DATE(p.paid_at) AS day, COALESCE(SUM(p.paid_amount),0) AS total
  FROM payments p
  JOIN enrollments e ON e.enrollment_id = p.enrollment_id
  WHERE e.course_id=?
  GROUP BY DATE(p.paid_at)
  ORDER BY day ASC
");
$st->bind_param("i",$course_id);
$st->execute();
$res=$st->get_result();

$out=[];
while($r=$res->fetch_assoc()){
  $out[]=["day"=>$r["day"], "total"=>(float)$r["total"]];
}
echo json_encode($out);