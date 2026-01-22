<?php require "header.php";

$msg=$err="";
if($_SERVER["REQUEST_METHOD"]==="POST"){
  $id=intval($_POST["course_id"]??0);
  $name=trim($_POST["course_name"]??"");
  $dur=intval($_POST["duration_months"]??0);
  $fee=floatval($_POST["course_fee"]??0);

  if($id<=0||$name===""||$dur<=0||$fee<=0) $err="All fields required.";
  else{
    try{
      $st=$conn->prepare("INSERT INTO courses(course_id,course_name,duration_months,course_fee) VALUES(?,?,?,?)");
      $st->bind_param("isid",$id,$name,$dur,$fee);
      $st->execute();
      $msg="Course added.";
    }catch(Exception $e){ $err=$e->getMessage(); }
  }
}
?>
<h2>Add Course</h2>
<?php if($msg) echo "<div class='msg'>".h($msg)."</div>"; ?>
<?php if($err) echo "<div class='err'>".h($err)."</div>"; ?>

<form class="form-card" method="post">
  <label>Course ID (manual)</label>
  <input type="number" name="course_id" required>

  <label>Course Name (SE/IT...)</label>
  <input type="text" name="course_name" required>

  <label>Duration (months)</label>
  <input type="number" name="duration_months" required>

  <label>Course Fee</label>
  <input type="number" step="0.01" name="course_fee" required>

  <button type="submit">Save Course</button>
</form>

<?php require "footer.php"; ?>
