<?php require "header.php";
$res=$conn->query("SELECT * FROM courses ORDER BY course_id ASC");
?>
<h2>Course List</h2>
<table>
  <tr><th>CourseID</th><th>CourseName</th><th>Duration(M)</th><th>Fee</th></tr>
  <?php while($r=$res->fetch_assoc()): ?>
    <tr>
      <td><?=h($r["course_id"])?></td>
      <td><?=h($r["course_name"])?></td>
      <td><?=h($r["duration_months"])?></td>
      <td><?=number_format((float)$r["course_fee"],2)?></td>
    </tr>
  <?php endwhile; ?>
</table>
<?php require "footer.php"; ?>
