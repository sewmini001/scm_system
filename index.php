<?php require "header.php"; ?>
<div class="page-title">Student & Course Management System</div>
<div class="subtitle">Welcome to the Student & Course Management System. Use the sections below to manage students, courses, and enrollments easily.</div>

<div class="card-row">
  <div class="card">
    <h3>Students</h3>
    <p>Add, edit, view, and manage student details.</p>
    <a class="btn" href="student_list.php">Go to Students</a>
  </div>

  <div class="card">
    <h3>Courses</h3>
    <p>Create courses, update fees, and manage course data.</p>
    <a class="btn" href="course_list.php">Go to Courses</a>
  </div>

  <div class="card">
    <h3>Enrollments</h3>
    <p>Enroll students into courses with fee management.</p>
    <a class="btn" href="enroll_list.php">Go to Enrollments</a>
  </div>

  <div class="card">
    <h3>Search / Reports</h3>
    <p>Search enrollments by student or course.</p>
    <a class="btn" href="report_student.php">Go to Reports</a>
  </div>
</div>

<?php require "footer.php"; ?>
