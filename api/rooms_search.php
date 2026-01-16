<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__."/../config/db.php";
require_once __DIR__."/_json.php";

$check_in  = trim($_GET['check_in'] ?? '');
$check_out = trim($_GET['check_out'] ?? '');
$guests    = (int)($_GET['guests'] ?? 1);
$type_id   = (int)($_GET['type_id'] ?? 0);

if($check_in==='' || $check_out==='') json_out(["ok"=>false,"error"=>"Fechas requeridas"],400);

// Esperamos YYYY-MM-DD
if(!preg_match('/^\d{4}-\d{2}-\d{2}$/', $check_in) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $check_out)){
  json_out(["ok"=>false,"error"=>"Formato de fecha inválido. Usa YYYY-MM-DD (ej: 2026-01-09)"],400);
}

if($check_in >= $check_out) json_out(["ok"=>false,"error"=>"check_out debe ser mayor que check_in"],400);
if($guests <= 0) $guests = 1;

// 1) Traer tipos que soportan la capacidad (y opcionalmente filtrar por tipo)
$sql = "
  SELECT id, name, description, capacity, base_price_cents
  FROM room_types
  WHERE capacity >= ?
";
$params = [$guests];
$types = "i";

if($type_id > 0){
  $sql .= " AND id = ? ";
  $params[] = $type_id;
  $types .= "i";
}

$sql .= " ORDER BY base_price_cents ASC ";

$stmt = $mysqli->prepare($sql);
if(!$stmt) json_out(["ok"=>false,"error"=>$mysqli->error],500);

$stmt->bind_param($types, ...$params);
$stmt->execute();
$res = $stmt->get_result();

$results = [];

while($rt = $res->fetch_assoc()){
  $rt_id = (int)$rt['id'];

  // 2) Contar habitaciones activas de ese tipo
  $st1 = $mysqli->prepare("SELECT COUNT(*) AS total FROM rooms WHERE room_type_id=? AND status='active'");
  $st1->bind_param("i", $rt_id);
  $st1->execute();
  $total = (int)$st1->get_result()->fetch_assoc()['total'];

  // 3) Contar ocupadas por reservas que se cruzan con el rango
  $st2 = $mysqli->prepare("
    SELECT COUNT(*) AS occupied
    FROM bookings b
    JOIN rooms r ON r.id=b.room_id
    WHERE b.status IN ('pending','paid')
      AND r.room_type_id=?
      AND (b.check_in < ? AND b.check_out > ?)
  ");
  $st2->bind_param("iss", $rt_id, $check_out, $check_in);
  $st2->execute();
  $occupied = (int)$st2->get_result()->fetch_assoc()['occupied'];

  $available = $total - $occupied;
  if($available < 0) $available = 0;

  $results[] = [
    "room_type_id" => $rt_id,
    "name" => $rt['name'],
    "description" => $rt['description'],
    "capacity" => (int)$rt['capacity'],
    "base_price_cents" => (int)$rt['base_price_cents'],
    "available_rooms" => $available,
  ];
}

json_out(["ok"=>true, "results"=>$results]);
