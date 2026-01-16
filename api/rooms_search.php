<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/_json.php";

$check_in  = trim($_GET['check_in'] ?? '');
$check_out = trim($_GET['check_out'] ?? '');
$guests    = (int)($_GET['guests'] ?? 1);
$type_id   = (int)($_GET['type_id'] ?? 0);

if($check_in==='' || $check_out==='') json_out(["ok"=>false,"error"=>"Fechas requeridas"],400);
if(strtotime($check_in)===false || strtotime($check_out)===false) json_out(["ok"=>false,"error"=>"Fechas inválidas"],400);
if($check_in >= $check_out) json_out(["ok"=>false,"error"=>"check_out debe ser mayor que check_in"],400);
if($guests<=0) $guests=1;

$params = [$check_in, $check_out, $check_in, $check_out, $guests];
$types  = "ssss i";
$types  = str_replace(" ", "", $types);

$whereType = "";
if($type_id>0){
  $whereType = " AND rt.id = ? ";
  $params[] = $type_id;
  $types .= "i";
}

$sql = "
SELECT
  rt.id AS room_type_id,
  rt.name, rt.description, rt.capacity, rt.base_price_cents,
  COUNT(r.id) AS total_rooms,
  SUM(CASE WHEN r.id IS NULL THEN 0 ELSE 1 END) AS debug
FROM room_types rt
JOIN rooms r ON r.room_type_id = rt.id AND r.status='active'
WHERE rt.capacity >= ?
$whereType
GROUP BY rt.id
";

$stmt = $mysqli->prepare($sql);
$stmt->bind_param($types, ...$params);
$stmt->execute();
$typesRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$out = [];
foreach($typesRows as $t){
  // contar disponibles restando ocupadas
  $sql2 = "
    SELECT COUNT(*) AS occupied
    FROM bookings b
    JOIN rooms r ON r.id=b.room_id
    WHERE b.status IN ('pending','paid')
      AND r.room_type_id=?
      AND (b.check_in < ? AND b.check_out > ?)
  ";
  $st2 = $mysqli->prepare($sql2);
  $st2->bind_param("iss", $t['room_type_id'], $check_out, $check_in);
  $st2->execute();
  $occ = (int)$st2->get_result()->fetch_assoc()['occupied'];

  $available = (int)$t['total_rooms'] - $occ;
  if($available < 0) $available = 0;

  $t['available_rooms'] = $available;
  $out[] = $t;
}

json_out(["ok"=>true,"results"=>$out]);
