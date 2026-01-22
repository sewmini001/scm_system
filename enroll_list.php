<?php require "header.php";

$student_id = trim($_GET["student_id"] ?? "");
$course_id  = trim($_GET["course_id"] ?? "");

$where = [];
$types = "";
$params = [];

if($student_id !== ""){
  $where[] = "e.student_id = ?";
  $types .= "i";
  $params[] = (int)$student_id;
}
if($course_id !== ""){
  $where[] = "e.course_id = ?";
  $types .= "i";
  $params[] = (int)$course_id;
}

$sql = "
SELECT
  e.enrollment_id,
  e.enrolled_date,
  e.student_id,
  s.student_name,
  s.nic,
  e.course_id,
  c.course_name,
  e.course_fee AS total_fee,
  COALESCE(SUM(p.paid_amount),0) AS total_paid,
  (e.course_fee - COALESCE(SUM(p.paid_amount),0)) AS balance
FROM enrollments e
JOIN students s ON s.student_id = e.student_id
JOIN courses c ON c.course_id = e.course_id
LEFT JOIN payments p ON p.enrollment_id = e.enrollment_id
";

if($where) $sql .= " WHERE ".implode(" AND ", $where);

$sql .= "
GROUP BY e.enrollment_id, e.enrolled_date, e.student_id, s.student_name, s.nic, e.course_id, c.course_name, e.course_fee
ORDER BY e.enrollment_id DESC
";

$stmt = $conn->prepare($sql);
if($types){
  $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<h2>Enrollments</h2>

<div class="form-card">
  <form method="get" class="filters">
    <div style="flex:1;min-width:240px;">
      <label>Filter by Student ID</label>
      <input type="text" name="student_id" value="<?=h($student_id)?>">
    </div>
    <div style="flex:1;min-width:240px;">
      <label>Filter by Course ID</label>
      <input type="text" name="course_id" value="<?=h($course_id)?>">
    </div>
    <div style="align-self:end;">
      <button type="submit">Filter</button>
    </div>
  </form>
</div>

<table>
  <tr>
    <th>Enrollment ID</th>
    <th>Student ID</th>
    <th>Name</th>
    <th>NIC</th>
    <th>Course ID</th>
    <th>Course</th>
    <th>Total Fee</th>
    <th>Paid</th>
    <th>Balance</th>
    <th>Enrolled Date</th>
  </tr>
  <?php foreach($rows as $r): ?>
    <tr>
      <td><?=h($r["enrollment_id"])?></td>
      <td><?=h($r["student_id"])?></td>
      <td><?=h($r["student_name"])?></td>
      <td><?=h($r["nic"])?></td>
      <td><?=h($r["course_id"])?></td>
      <td><?=h($r["course_name"])?></td>

      <td>
        <a class="fee-link" href="payment.php?enrollment_id=<?= (int)$r['enrollment_id'] ?>">
          <?= number_format((float)$r["total_fee"],2) ?>
        </a>
      </td>

      <td><?= number_format((float)$r["total_paid"],2) ?></td>
      <td><?= number_format((float)$r["balance"],2) ?></td>
      <td><?=h($r["enrolled_date"])?></td>
    </tr>
  <?php endforeach; ?>
</table>

<h2 style="margin-top:20px;">Filtered Payment Bar Chart (Paid vs Balance)</h2>
<canvas id="filterChart" height="110"></canvas>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
fetch("api_enrollment_filter_chart.php?student_id=<?=h($student_id)?>&course_id=<?=h($course_id)?>")
  .then(r=>r.json())
  .then(data=>{
    const labels = data.map(x => "E"+x.enrollment_id);
    const paid   = data.map(x => x.total_paid);
    const bal    = data.map(x => x.balance);

    new Chart(document.getElementById("filterChart"),{
      type:"bar",
      data:{
        labels,
        datasets:[
          { label:"Paid", data: paid },
          { label:"Balance", data: bal }
        ]
      },
      options:{ responsive:true, scales:{ y:{ beginAtZero:true } } }
    });
  });
</script>

<?php require "footer.php"; ?>
