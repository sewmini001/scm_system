<?php
require "header.php";

/* Show ONLY active students */
$res = $conn->query("
  SELECT * FROM students 
  WHERE is_deleted = 0
  ORDER BY student_id ASC
");
?>

<h2>Student List</h2>

<table>
  <tr>
    <th>ID</th>
    <th>Name</th>
    <th>Email</th>
    <th>NIC</th>
    <th>Address</th>
    <th>Contact</th>
    <th>Actions</th>
  </tr>

  <?php while ($r = $res->fetch_assoc()): ?>
  <tr>
    <td><?= h($r["student_id"]) ?></td>
    <td><?= h($r["student_name"]) ?></td>
    <td><?= h($r["email"]) ?></td>
    <td><?= h($r["nic"]) ?></td>
    <td><?= h($r["address"]) ?></td>
    <td><?= h($r["contact_no"]) ?></td>
    <td>
      <a href="student_edit.php?id=<?= $r["student_id"] ?>">Edit</a> |
      <a href="student_delete.php?id=<?= $r["student_id"] ?>"
         onclick="return confirm('Are you sure you want to delete this student?')">
         Delete
      </a>
    </td>
  </tr>
  <?php endwhile; ?>
</table>

<?php require "footer.php"; ?>
