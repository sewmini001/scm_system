<?php
require "header.php";

$id = intval($_GET["id"] ?? 0);
$msg = $err = "";

/* Load student */
$stmt = $conn->prepare("
  SELECT * FROM students 
  WHERE student_id = ? AND is_deleted = 0
");
$stmt->bind_param("i", $id);
$stmt->execute();
$student = $stmt->get_result()->fetch_assoc();

if (!$student) {
    echo "<div class='err'>Student not found.</div>";
    require "footer.php";
    exit;
}

/* Update */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["student_name"]);
    $email = trim($_POST["email"]);
    $address = trim($_POST["address"]);
    $contact = trim($_POST["contact_no"]);

    if ($name === "" || $email === "") {
        $err = "Name and Email required.";
    } else {
        $stmt = $conn->prepare("
          UPDATE students 
          SET student_name=?, email=?, address=?, contact_no=?
          WHERE student_id=?
        ");
        $stmt->bind_param("ssssi",
            $name, $email, $address, $contact, $id
        );
        $stmt->execute();
        $msg = "Student updated successfully.";
    }
}
?>

<h2>Edit Student</h2>

<?php if ($msg) echo "<div class='msg'>".h($msg)."</div>"; ?>
<?php if ($err) echo "<div class='err'>".h($err)."</div>"; ?>

<form method="post" class="form-card">
  <label>Name</label>
  <input type="text" name="student_name" value="<?= h($student["student_name"]) ?>" required>

  <label>Email</label>
  <input type="email" name="email" value="<?= h($student["email"]) ?>" required>

  <label>Address</label>
  <input type="text" name="address" value="<?= h($student["address"]) ?>">

  <label>Contact No</label>
  <input type="text" name="contact_no" value="<?= h($student["contact_no"]) ?>">

  <button type="submit">Update Student</button>
</form>

<?php require "footer.php"; ?>
