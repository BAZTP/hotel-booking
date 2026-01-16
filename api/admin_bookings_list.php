<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/_json.php";

$status = trim($_GET['status'] ?? 'all');
$from   = trim($_GET['from'] ?? '');
$to     = trim($_GET['to'] ?? '');
$q      = trim($_GET['q'] ?? '');

$where = " WHERE 1=1 ";
$params = [];
$types = "";

if(in_array($status, ['pending','paid','cancelled'], true)){
  $where .= " AND b.status=? ";
  $params[] = $status; $types .= "s";
}

if($from !== ''){
  $where .= " AND b.check_in >= ? ";
  $params[] = $from; $types .= "s";
}
if($to !== ''){
  $where .= " AND b.check_out <= ? ";
  $params[] = $to; $types .= "s";
}

if($q !== ''){
  $where .= " AND (b.booking_code = ? OR b.customer_email = ? OR b.customer_name LIKE ?) ";
  $params[] = $q; $types .= "s";
  $params[] = $q; $types .= "s";
  $like = "%$q%";
  $params[] = $like; $types .= "s";
}

$sql = "
  SELECT b.booking_code, b.customer_name, b.customer_email, b.check_in, b.check_out, b.guests,
         b.total_cents, b.status, b.created_at, b.paid_at, b.cancelled_at,
         rt.name AS room_type, r.room_number
  FROM bookings b
  JOIN rooms r ON r.id=b.room_id
  JOIN room_types rt ON rt.id=b.room_type_id
  $where
  ORDER BY b.created_at DESC
  LIMIT 200
";

$stmt = $mysqli->prepare($sql);
if($types) $stmt->bind_param($types, ...$params);
$stmt->execute();

$rows=[];
$res = $stmt->get_result();
while($row=$res->fetch_assoc()) $rows[]=$row;

json_out(["ok"=>true,"bookings"=>$rows]);
