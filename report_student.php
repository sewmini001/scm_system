<?php
require "header.php";

$student_id = trim($_GET["student_id"] ?? "");
$search_name = trim($_GET["search_name"] ?? "");
$info = null;
$rows = [];
$err = "";

if ($student_id !== "" || $search_name !== "") {

    // Search by Student ID if given
    if ($student_id !== "" && ctype_digit($student_id)) {
        $st = $conn->prepare("SELECT * FROM students WHERE student_id=?");
        $st->bind_param("i", $student_id);
        $st->execute();
        $info = $st->get_result()->fetch_assoc();

        if (!$info) $err = "Student not found.";
    } 
    // Else search by Student Name
    elseif ($search_name !== "") {
        $st = $conn->prepare("SELECT * FROM students WHERE student_name LIKE ?");
        $like = "%".$search_name."%";
        $st->bind_param("s", $like);
        $st->execute();
        $res = $st->get_result();

        if ($res->num_rows === 1) {
            $info = $res->fetch_assoc();
            $student_id = $info['student_id']; // set ID for enrollment query
        } elseif ($res->num_rows > 1) {
            $err = "Multiple students found. Please use Student ID.";
        } else {
            $err = "Student not found.";
        }
    }

    // Load enrollment data if student found
    if ($info) {
        $st = $conn->prepare("
            SELECT
                e.enrollment_id,
                e.course_id,
                c.course_name,
                e.course_fee AS total_fee,
                e.enrolled_date,
                COALESCE(SUM(p.paid_amount),0) AS total_paid,
                (e.course_fee - COALESCE(SUM(p.paid_amount),0)) AS balance
            FROM enrollments e
            JOIN courses c ON c.course_id = e.course_id
            LEFT JOIN payments p ON p.enrollment_id = e.enrollment_id
            WHERE e.student_id=?
            GROUP BY e.enrollment_id, e.course_id, c.course_name, e.course_fee, e.enrolled_date
            ORDER BY e.enrollment_id DESC
        ");
        $st->bind_param("i", $student_id);
        $st->execute();
        $rows = $st->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
?>

<h2>Student Report</h2>

<div class="form-card">
  <form method="get">
    <label>Student ID</label>
    <input type="text" name="student_id" id="student_id" value="<?=h($student_id)?>" placeholder="Enter Student ID">
    
    <label>Student Name</label>
    <input type="text" name="search_name" id="student_name" value="<?=h($info['student_name'] ?? '')?>" placeholder="Enter Name" >

    <button type="submit">Search</button>
  </form>
</div>

<?php if($err): ?>
  <div class='err'><?=h($err)?></div>
<?php endif; ?>

<?php if($info): ?>
  <h3>
    Student: <?=h($info["student_id"])?> - <?=h($info["student_name"])?> (<?=h($info["nic"])?>)
  </h3>

  <table border="1" cellpadding="5" cellspacing="0">
    <tr>
      <th>Enrollment ID</th>
      <th>Course</th>
      <th>Total Fee</th>
      <th>Paid</th>
      <th>Balance</th>
      <th>Enrolled Date</th>
    </tr>
    <?php
      $tFee = 0; $tPaid = 0; $tBal = 0;
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

<script>
document.getElementById('student_id').addEventListener('input', function() {
    let id = this.value.trim();
    if(id === "" || isNaN(id)){
        document.getElementById('student_name').value = "";
        return;
    }

    fetch('get_student_name.php?student_id=' + id)
    .then(res => res.json())
    .then(data => {
        if(data.success){
            document.getElementById('student_name').value = data.name;
        } else {
            document.getElementById('student_name').value = "";
        }
    })
    .catch(err => console.error(err));
});
</script>

<?php require "footer.php"; ?>

