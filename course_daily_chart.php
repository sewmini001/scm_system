<?php require "header.php";
$course_id = intval($_GET["course_id"] ?? 0);

$course=null;
if($course_id>0){
  $st=$conn->prepare("SELECT course_id,course_name FROM courses WHERE course_id=?");
  $st->bind_param("i",$course_id);
  $st->execute();
  $course=$st->get_result()->fetch_assoc();
}
?>

<h2>Course Daily Payments Chart</h2>

<div class="form-card">
  <form method="get">
    <label>Course ID</label>
    <input type="number" name="course_id" value="<?=h($course_id)?>" required>
    <button type="submit">Load</button>
  </form>
</div>

<?php if($course): ?>
  <h3><?=h($course["course_id"])?> - <?=h($course["course_name"])?></h3>
  <canvas id="dailyChart" height="120"></canvas>

  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
  fetch("api_course_daily_payments.php?course_id=<?= (int)$course_id ?>")
    .then(r=>r.json())
    .then(data=>{
      const labels=data.map(x=>x.day);
      const values=data.map(x=>x.total);

      new Chart(document.getElementById("dailyChart"),{
        type:"bar",
        data:{ labels, datasets:[{ label:"Daily Payments", data: values }] },
        options:{ responsive:true, scales:{ y:{ beginAtZero:true } } }
      });
    });
  </script>
<?php endif; ?>

<?php require "footer.php"; ?>