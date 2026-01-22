<?php require "header.php";

$enrollment_id = intval($_GET["enrollment_id"] ?? 0);
if($enrollment_id<=0){ echo "<div class='err'>Invalid enrollment.</div>"; require "footer.php"; exit; }

$stmt=$conn->prepare("
SELECT e.enrollment_id, e.course_fee, e.enrolled_date,
       s.student_id, s.student_name, s.nic,
       c.course_id, c.course_name
FROM enrollments e
JOIN students s ON s.student_id=e.student_id
JOIN courses c ON c.course_id=e.course_id
WHERE e.enrollment_id=?
");
$stmt->bind_param("i",$enrollment_id);
$stmt->execute();
$enr=$stmt->get_result()->fetch_assoc();
if(!$enr){ echo "<div class='err'>Enrollment not found.</div>"; require "footer.php"; exit; }

$paidStmt=$conn->prepare("SELECT COALESCE(SUM(paid_amount),0) AS paid FROM payments WHERE enrollment_id=?");
$paidStmt->bind_param("i",$enrollment_id);
$paidStmt->execute();
$paidRow=$paidStmt->get_result()->fetch_assoc();

$total_fee=(float)$enr["course_fee"];
$total_paid=(float)$paidRow["paid"];
$balance=$total_fee-$total_paid;
?>

<h2>Payment</h2>

<div class="form-card" style="max-width:760px;">
  <h3>Enrollment: <?=h($enr["enrollment_id"])?> | Student <?=h($enr["student_id"])?> | Course <?=h($enr["course_id"])?> (<?=h($enr["course_name"])?>)</h3>
  <p class="small">Total Fee: <b><?=number_format($total_fee,2)?></b> | Paid: <b><?=number_format($total_paid,2)?></b> | Balance: <b><?=number_format($balance,2)?></b></p>

  <form method="post" action="process_payment.php">
    <input type="hidden" name="enrollment_id" value="<?= (int)$enrollment_id ?>">
    <input type="hidden" id="max_balance" value="<?= h($balance) ?>">

    <label>Payment Method</label>
    <select name="payment_method" id="payment_method" required>
      <option value="">-- Select --</option>
      <option value="Cash">Cash</option>
      <option value="Online Transfer">Online Transfer</option>
      <option value="Bank Deposit">Bank Deposit</option>
      <option value="Koko Pay">Koko Pay</option>
      <option value="Other">Other</option>
    </select>

    <label>Sub Method</label>
    <select name="payment_sub_method" id="payment_sub_method" required>
      <option value="">-- Select method first --</option>
    </select>

    <label>Payment Type</label>
    <select name="payment_type" id="payment_type" required>
      <option value="">-- Select --</option>
      <option value="Full">Full Payment</option>
      <option value="Half">Half Payment</option>
      <option value="Installment">Installment</option>
    </select>

    <label>Amount</label>
    <input type="number" step="0.01" name="paid_amount" id="paid_amount" required>

    <button type="submit">Confirm Payment</button>
  </form>
</div>

<h3 style="margin-top:18px;">Payment Summary</h3>
<table>
  <tr><th>Enrollment ID</th><th>Total Fee</th><th>Total Paid</th><th>Balance</th></tr>
  <tr>
    <td><?=h($enrollment_id)?></td>
    <td><?=number_format($total_fee,2)?></td>
    <td><?=number_format($total_paid,2)?></td>
    <td><?=number_format($balance,2)?></td>
  </tr>
</table>

<script>
const methodSelect = document.getElementById("payment_method");
const subSelect = document.getElementById("payment_sub_method");
const typeSelect = document.getElementById("payment_type");
const amountInput = document.getElementById("paid_amount");
const maxBalance = parseFloat(document.getElementById("max_balance").value);

function setSubOptions(method){
  subSelect.innerHTML = "";
  let options = [];

  if(method==="Cash") options = ["N/A"];
  if(method==="Online Transfer") options = ["Visa", "Master"];
  if(method==="Bank Deposit") options = ["HNB", "BOC", "Commercial"];
  if(method==="Koko Pay") options = ["N/A"];
  if(method==="Other") options = ["N/A"];

  options.forEach(o=>{
    const op=document.createElement("option");
    op.value=o; op.textContent=o;
    subSelect.appendChild(op);
  });
}

methodSelect.addEventListener("change", ()=>{
  setSubOptions(methodSelect.value);
});

typeSelect.addEventListener("change", ()=>{
  const t = typeSelect.value;
  if(!t) return;

  if(t==="Full") amountInput.value = maxBalance.toFixed(2);
  else if(t==="Half") amountInput.value = (maxBalance/2).toFixed(2);
  else amountInput.value = "";
});
</script>

<?php require "footer.php"; ?>
