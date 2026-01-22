<?php require "db.php";
header("Location: index.php");

$enrollment_id = intval($_POST["enrollment_id"] ?? 0);
$method = trim($_POST["payment_method"] ?? "");
$sub    = trim($_POST["payment_sub_method"] ?? "");
$type   = trim($_POST["payment_type"] ?? "");
$amount = floatval($_POST["paid_amount"] ?? 0);

if($enrollment_id<=0 || $method==="" || $sub==="" || $type==="" || $amount<=0){
  die("Invalid payment data.");
}

$st=$conn->prepare("SELECT course_fee FROM enrollments WHERE enrollment_id=?");
$st->bind_param("i",$enrollment_id);
$st->execute();
$enr=$st->get_result()->fetch_assoc();
if(!$enr) die("Enrollment not found.");

$total_fee=(float)$enr["course_fee"];

$st=$conn->prepare("SELECT COALESCE(SUM(paid_amount),0) AS paid FROM payments WHERE enrollment_id=?");
$st->bind_param("i",$enrollment_id);
$st->execute();
$paidRow=$st->get_result()->fetch_assoc();
$already=(float)$paidRow["paid"];

$balance = $total_fee - $already;
if($amount > $balance){
  die("Payment exceeds remaining balance.");
}

$ins=$conn->prepare("INSERT INTO payments(enrollment_id,payment_method,payment_sub_method,payment_type,paid_amount) VALUES(?,?,?,?,?)");
$ins->bind_param("isssd",$enrollment_id,$method,$sub,$type,$amount);
$ins->execute();

header("Location: payment.php?enrollment_id=".$enrollment_id);
exit;