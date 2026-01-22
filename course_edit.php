<?php
require "header.php";

$id = intval($_GET["id"] ?? 0);
$msg = $err = "";

/* Load course */
$st = $conn->prepare("
    SELECT * FROM courses 
    WHERE course_id=? AND is_deleted=0
");
$st->bind_param("i", $id);
$st->execute();
$course = $st->get_result()->fetch_assoc();

if (!$course) {
    echo "<div class='err'>Course not found</div>";
    require "footer.php";
    exit;
}

/* Update */
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $name = trim($_POST["course_name"]);
    $dur  = intval($_POST["duration_months"]);
    $fee  = floatval($_POST["course_fee"]);

    if ($name === "" || $dur <= 0 || $fee <= 0) {
        $err = "All fields required.";
    } else {
        $up = $conn->prepare("
            UPDATE courses 
            SET course_name=?, duration_months=?, course_fee=?
            WHERE course_id=?
        ");
        $up->bind_param("sidi", $name, $dur, $fee, $id);
        $up->execute();
        $msg = "Course updated successfully.";
    }
}
?>

<h2>Edit Course</h2>

<?php if ($msg) echo "<div class='msg'>".h($msg)."</div>"; ?>
<?php if ($err) echo "<div class='err'>".h($err)."</div>"; ?>

<form method="post" class="form-card">
  <label>Course Name</label>
  <input type="text" name="course_name" value="<?= h($course["course_name"]) ?>" required>

  <label>Duration (Months)</label>
  <input type="number" name="duration_months" value="<?= $course["duration_months"] ?>" required>

  <label>Course Fee</label>
  <input type="number" step="0.01" name="course_fee" value="<?= $course["course_fee"] ?>" required>

  <button type="submit">Update Course</button>
  <a href="course_list.php">Back</a>
</form>

<?php require "footer.php"; ?>
