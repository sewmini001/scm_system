<?php require "header.php";

$msg=$err="";

if($_SERVER["REQUEST_METHOD"]==="POST"){
  $student_id=intval($_POST["student_id"]??0);
  $course_id=intval($_POST["course_id"]??0);
  $fee=floatval($_POST["course_fee"]??0);
  $date=$_POST["enrolled_date"] ?? date("Y-m-d");

  if($student_id<=0||$course_id<=0||$fee<=0) $err="Select valid student & course.";
  else{
    try{
      $st=$conn->prepare("INSERT INTO enrollments(student_id,course_id,course_fee,enrolled_date) VALUES(?,?,?,?)");
      $st->bind_param("iids",$student_id,$course_id,$fee,$date);
      $st->execute();
      $msg="Enrollment saved. Enrollment ID: ".$conn->insert_id;
    }catch(Exception $e){ $err=$e->getMessage(); }
  }
}
?>
<h2>Enroll Student</h2>
<?php if($msg) echo "<div class='msg'>".h($msg)."</div>"; ?>
<?php if($err) echo "<div class='err'>".h($err)."</div>"; ?>

<form class="form-card" method="post">
  <label>Student ID</label>
  <input type="number" name="student_id" id="student_id" required>
  <div class="small" id="student_info">Type Student ID to load details...</div>

  <label>Course ID</label>
  <input type="number" name="course_id" id="course_id" required>
  <div class="small" id="course_info">Type Course ID to load details...</div>

  <label>Course Fee (auto)</label>
  <input type="number" step="0.01" name="course_fee" id="course_fee" readonly required>

  <label>Enrolled Date</label>
  <input type="date" name="enrolled_date" value="<?=h(date("Y-m-d"))?>">

  <button type="submit">Save Enrollment</button>
</form>

<script>
async function fetchJSON(url){
  const r = await fetch(url);
  return await r.json();
}

document.getElementById("student_id").addEventListener("input", async (e)=>{
  const id = e.target.value;
  if(!id){ document.getElementById("student_info").innerText="Type Student ID to load details..."; return; }
  const data = await fetchJSON("api_student.php?student_id="+id);
  if(data.found){
    document.getElementById("student_info").innerText =
      `${data.student.student_name} | NIC: ${data.student.nic} | ${data.student.email}`;
  } else {
    document.getElementById("student_info").innerText = "Student not found.";
  }
});

document.getElementById("course_id").addEventListener("input", async (e)=>{
  const id = e.target.value;
  if(!id){ document.getElementById("course_info").innerText="Type Course ID to load details..."; document.getElementById("course_fee").value=""; return; }
  const data = await fetchJSON("api_course.php?course_id="+id);
  if(data.found){
    document.getElementById("course_info").innerText =
      `${data.course.course_name} | Duration: ${data.course.duration_months} months`;
    document.getElementById("course_fee").value = Number(data.course.course_fee).toFixed(2);
  } else {
    document.getElementById("course_info").innerText = "Course not found.";
    document.getElementById("course_fee").value="";
  }
});
</script>

<?php require "footer.php"; ?>


