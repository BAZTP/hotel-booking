<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/_json.php";

$q = trim($_GET['q'] ?? '');
if($q==='') json_out(["ok"=>false,"error"=>"q requerido (email o código)"],400);

$sql = "
  SELECT b.booking_code, b.customer_name, b.customer_email, b.check_in, b.check_out, b.guests,
         b.total_cents, b.status, b.created_at, b.paid_at, b.cancelled_at,
         rt.name AS room_type, r.room_number
  FROM bookings b
  JOIN rooms r ON r.id=b.room_id
  JOIN room_types rt ON rt.id=b.room_type_id
  WHERE b.booking_code=? OR b.customer_email=?
  ORDER BY b.created_at DESC
  LIMIT 50
";
$stmt = $mysqli->prepare($sql);
$stmt->bind_param("ss",$q,$q);
$stmt->execute();
$res = $stmt->get_result();

$rows=[];
while($row=$res->fetch_assoc()) $rows[]=$row;

json_out(["ok"=>true,"bookings"=>$rows]);
