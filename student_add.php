<?php require "header.php";

$msg=$err="";
if($_SERVER["REQUEST_METHOD"]==="POST"){
  $id=intval($_POST["student_id"]??0);
  $name=trim($_POST["student_name"]??"");
  $nic=trim($_POST["nic"]??"");
  $email=trim($_POST["email"]??"");
  $address=trim($_POST["address"]??"");
  $contact=trim($_POST["contact_no"]??"");

  if($id<=0||$name===""||$nic===""||$email==="") $err="Student ID, Name, NIC, Email required.";
  else{
    try{
      $st=$conn->prepare("INSERT INTO students(student_id,student_name,nic,email,address,contact_no) VALUES(?,?,?,?,?,?)");
      $st->bind_param("isssss",$id,$name,$nic,$email,$address,$contact);
      $st->execute();
      $msg="Student added.";
    }catch(Exception $e){ $err=$e->getMessage(); }
  }
}
?>
<h2>Add Student</h2>
<?php if($msg) echo "<div class='msg'>".h($msg)."</div>"; ?>
<?php if($err) echo "<div class='err'>".h($err)."</div>"; ?>

<form class="form-card" method="post">
  <label>Student ID (manual)</label>
  <input type="number" name="student_id" required>

  <label>Student Name</label>
  <input type="text" name="student_name" required>

  <label>NIC</label>
  <input type="text" name="nic" required>

  <label>Email</label>
  <input type="email" name="email" required>

  <label>Address</label>
  <input type="text" name="address">

  <label>Contact No</label>
  <input type="text" name="contact_no">

  <button type="submit">Save Student</button>
</form>

<?php require "footer.php"; ?>
