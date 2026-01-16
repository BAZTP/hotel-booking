<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/_json.php";

$type_id   = (int)($_POST['room_type_id'] ?? 0);
$check_in  = trim($_POST['check_in'] ?? '');
$check_out = trim($_POST['check_out'] ?? '');
$guests    = (int)($_POST['guests'] ?? 1);
$name      = trim($_POST['customer_name'] ?? '');
$email     = trim($_POST['customer_email'] ?? '');

if($type_id<=0) json_out(["ok"=>false,"error"=>"room_type_id requerido"],400);
if($check_in==='' || $check_out==='') json_out(["ok"=>false,"error"=>"Fechas requeridas"],400);
if($check_in >= $check_out) json_out(["ok"=>false,"error"=>"Rango de fechas inválido"],400);
if($name==='') json_out(["ok"=>false,"error"=>"Nombre requerido"],400);
if($guests<=0) $guests=1;

$mysqli->begin_transaction();
try{
  // lock tipo
  $st = $mysqli->prepare("SELECT id, capacity, base_price_cents FROM room_types WHERE id=? FOR UPDATE");
  $st->bind_param("i",$type_id);
  $st->execute();
  $rt = $st->get_result()->fetch_assoc();
  if(!$rt) throw new Exception("Tipo no existe");
  if($guests > (int)$rt['capacity']) throw new Exception("Excede capacidad del tipo");

  // buscar 1 habitación libre de ese tipo
  $sql = "
    SELECT r.id, r.room_number
    FROM rooms r
    WHERE r.room_type_id=? AND r.status='active'
      AND NOT EXISTS (
        SELECT 1 FROM bookings b
        WHERE b.room_id = r.id
          AND b.status IN ('pending','paid')
          AND (b.check_in < ? AND b.check_out > ?)
      )
    ORDER BY r.room_number ASC
    LIMIT 1
    FOR UPDATE
  ";
  $stmt = $mysqli->prepare($sql);
  $stmt->bind_param("iss", $type_id, $check_out, $check_in);
  $stmt->execute();
  $room = $stmt->get_result()->fetch_assoc();
  if(!$room) throw new Exception("No hay habitaciones disponibles para esas fechas");

  // total = noches * precio base
  $nights = (int)((strtotime($check_out) - strtotime($check_in)) / 86400);
  if($nights <= 0) $nights = 1;
  $total = $nights * (int)$rt['base_price_cents'];

  $code = strtoupper(bin2hex(random_bytes(6)));

  $ins = $mysqli->prepare("
    INSERT INTO bookings(booking_code, room_id, room_type_id, customer_name, customer_email, check_in, check_out, guests, total_cents, status)
    VALUES(?,?,?,?,?,?,?,?,?, 'pending')
  ");
  $ins->bind_param("siissssii", $code, $room['id'], $type_id, $name, $email, $check_in, $check_out, $guests, $total);
  if(!$ins->execute()) throw new Exception($ins->error);

  $mysqli->commit();
  json_out([
    "ok"=>true,
    "booking_code"=>$code,
    "room_number"=>$room['room_number'],
    "nights"=>$nights,
    "total_cents"=>$total
  ]);
}catch(Throwable $e){
  $mysqli->rollback();
  json_out(["ok"=>false,"error"=>$e->getMessage()],400);
}
