<?php require "header.php";

$course_id = trim($_GET["course_id"] ?? "");
$info=null; $rows=[]; $err="";

if($course_id!==""){
  if(!ctype_digit($course_id)) $err="Course ID must be numeric.";
  else{
    $st=$conn->prepare("SELECT * FROM courses WHERE course_id=?");
    $st->bind_param("i",$course_id);
    $st->execute();
    $info=$st->get_result()->fetch_assoc();
    if(!$info) $err="Course not found.";
    else{
      $st=$conn->prepare("
        SELECT
          e.enrollment_id, e.student_id, s.student_name, s.nic, e.course_fee AS total_fee, e.enrolled_date,
          COALESCE(SUM(p.paid_amount),0) AS total_paid,
          (e.course_fee - COALESCE(SUM(p.paid_amount),0)) AS balance
        FROM enrollments e
        JOIN students s ON s.student_id = e.student_id
        LEFT JOIN payments p ON p.enrollment_id = e.enrollment_id
        WHERE e.course_id=?
        GROUP BY e.enrollment_id, e.student_id, s.student_name, s.nic, e.course_fee, e.enrolled_date
        ORDER BY e.enrollment_id DESC
      ");
      $st->bind_param("i",$course_id);
      $st->execute();
      $rows=$st->get_result()->fetch_all(MYSQLI_ASSOC);
    }
  }
}
?>

<h2>Course Report</h2>

<div class="form-card">
  <form method="get">
    <label>Course ID</label>
    <input type="text" name="course_id" value="<?=h($course_id)?>" required>
    <button type="submit">Search</button>
  </form>
</div>

<?php if($err) echo "<div class='err'>".h($err)."</div>"; ?>

<?php if($info): ?>
  <h3>Course: <?=h($info["course_id"])?> - <?=h($info["course_name"])?> | Fee: <?=number_format((float)$info["course_fee"],2)?></h3>

  <table>
    <tr>
      <th>Enrollment ID</th><th>Student</th><th>Total Fee</th><th>Paid</th><th>Balance</th><th>Enrolled Date</th>
    </tr>
    <?php
      $tFee=0; $tPaid=0; $tBal=0; $count=0;
      foreach($rows as $r):
        $count++;
        $tFee += (float)$r["total_fee"];
        $tPaid += (float)$r["total_paid"];
        $tBal += (float)$r["balance"];
    ?>
      <tr>
        <td><?=h($r["enrollment_id"])?></td>
        <td><?=h($r["student_id"])?> - <?=h($r["student_name"])?> (<?=h($r["nic"])?>)</td>
        <td><?=number_format((float)$r["total_fee"],2)?></td>
        <td><?=number_format((float)$r["total_paid"],2)?></td>
        <td><?=number_format((float)$r["balance"],2)?></td>
        <td><?=h($r["enrolled_date"])?></td>
      </tr>
    <?php endforeach; ?>

    <tr style="font-weight:900;">
      <td colspan="2">TOTAL (Students: <?= (int)$count ?>)</td>
      <td><?=number_format($tFee,2)?></td>
      <td><?=number_format($tPaid,2)?></td>
      <td><?=number_format($tBal,2)?></td>
      <td></td>
    </tr>
  </table>

  <p style="margin-top:12px;">
    <a class="btn" href="course_daily_chart.php?course_id=<?= (int)$info["course_id"] ?>">Daily Payments Chart</a>
  </p>
<?php endif; ?>

<?php require "footer.php"; ?>