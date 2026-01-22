<?php 
require "header.php";

/* UI DELETE ACTION */
if (isset($_GET["delete_id"])) {
    $did = intval($_GET["delete_id"]);
    if ($did > 0) {
        $st = $conn->prepare("UPDATE courses SET is_deleted=1 WHERE course_id=?");
        $st->bind_param("i", $did);
        $st->execute();
    }
}

/* Load only ACTIVE courses */
$res = $conn->query("
    SELECT * FROM courses 
    WHERE is_deleted=0 
    ORDER BY course_id ASC
");
?>

<h2>Course List</h2>

<table>
  <tr>
    <th>Course ID</th>
    <th>Course Name</th>
    <th>Duration (Months)</th>
    <th>Fee</th>
    <th>Actions</th>
  </tr>

<?php while ($r = $res->fetch_assoc()): ?>
<tr>
  <td><?= h($r["course_id"]) ?></td>
  <td><?= h($r["course_name"]) ?></td>
  <td><?= h($r["duration_months"]) ?></td>
  <td><?= number_format($r["course_fee"],2) ?></td>
  <td>
    <a href="course_edit.php?id=<?= $r["course_id"] ?>">Edit</a> |
    <a href="course_list.php?delete_id=<?= $r["course_id"] ?>"
       onclick="return confirm('Are you sure?')">
       Delete
    </a>
  </td>
</tr>
<?php endwhile; ?>
</table>

<?php require "footer.php"; ?>

