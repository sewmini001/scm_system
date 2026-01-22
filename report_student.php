<?php require "header.php";

$student_id = trim($_GET["student_id"] ?? "");
$info=null; $rows=[]; $err="";

if($student_id!==""){
  if(!ctype_digit($student_id)) $err="Student ID must be numeric.";
  else{
    $st=$conn->prepare("SELECT * FROM students WHERE student_id=?");
    $st->bind_param("i",$student_id);
    $st->execute();
    $info=$st->get_result()->fetch_assoc();
    if(!$info) $err="Student not found.";
    else{
      $st=$conn->prepare("
        SELECT
          e.enrollment_id, e.course_id, c.course_name, e.course_fee AS total_fee, e.enrolled_date,
          COALESCE(SUM(p.paid_amount),0) AS total_paid,
          (e.course_fee - COALESCE(SUM(p.paid_amount),0)) AS balance
        FROM enrollments e
        JOIN courses c ON c.course_id = e.course_id
        LEFT JOIN payments p ON p.enrollment_id = e.enrollment_id
        WHERE e.student_id=?
        GROUP BY e.enrollment_id, e.course_id, c.course_name, e.course_fee, e.enrolled_date
        ORDER BY e.enrollment_id DESC
      ");
      $st->bind_param("i",$student_id);
      $st->execute();
      $rows=$st->get_result()->fetch_all(MYSQLI_ASSOC);
    }
  }
}
?>

<h2>Student Report</h2>

<div class="form-card">
  <form method="get">
    <label>Student ID</label>
    <input type="text" name="student_id" value="<?=h($student_id)?>" required>
    <button type="submit">Search</button>
  </form>
</div>

<?php if($err) echo "<div class='err'>".h($err)."</div>"; ?>

<?php if($info): ?>
  <h3>Student: <?=h($info["student_id"])?> - <?=h($info["student_name"])?> (<?=h($info["nic"])?>)</h3>

  <table>
    <tr>
      <th>Enrollment ID</th><th>Course</th><th>Total Fee</th><th>Paid</th><th>Balance</th><th>Enrolled Date</th>
    </tr>
    <?php
      $tFee=0; $tPaid=0; $tBal=0;
      foreach($rows as $r):
        $tFee += (float)$r["total_fee"];
        $tPaid += (float)$r["total_paid"];
        $tBal += (float)$r["balance"];
    ?>
      <tr>
        <td><?=h($r["enrollment_id"])?></td>
        <td><?=h($r["course_id"])?> - <?=h($r["course_name"])?></td>
        <td><?=number_format((float)$r["total_fee"],2)?></td>
        <td><?=number_format((float)$r["total_paid"],2)?></td>
        <td><?=number_format((float)$r["balance"],2)?></td>
        <td><?=h($r["enrolled_date"])?></td>
      </tr>
    <?php endforeach; ?>

    <tr style="font-weight:900;">
      <td colspan="2">TOTAL</td>
      <td><?=number_format($tFee,2)?></td>
      <td><?=number_format($tPaid,2)?></td>
      <td><?=number_format($tBal,2)?></td>
      <td></td>
    </tr>
  </table>
<?php endif; ?>

<?php require "footer.php"; ?>