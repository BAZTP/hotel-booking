<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/_json.php";

$code = trim($_POST['booking_code'] ?? '');
if($code==='') json_out(["ok"=>false,"error"=>"booking_code requerido"],400);

$stmt = $mysqli->prepare("UPDATE bookings SET status='cancelled', cancelled_at=NOW() WHERE booking_code=? AND status IN ('pending','paid')");
$stmt->bind_param("s",$code);
$stmt->execute();

if($stmt->affected_rows<=0){
  $st = $mysqli->prepare("SELECT status FROM bookings WHERE booking_code=? LIMIT 1");
  $st->bind_param("s",$code);
  $st->execute();
  $b = $st->get_result()->fetch_assoc();
  if(!$b) json_out(["ok"=>false,"error"=>"Reserva no existe"],404);
  json_out(["ok"=>false,"error"=>"No se puede cancelar (estado: ".$b['status'].")"],400);
}

json_out(["ok"=>true,"status"=>"cancelled"]);
