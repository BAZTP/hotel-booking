<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/_json.php";

$room_type_id = (int)($_POST['room_type_id'] ?? 0);
$room_number  = trim($_POST['room_number'] ?? '');
$status       = trim($_POST['status'] ?? 'active');

if($room_type_id<=0) json_out(["ok"=>false,"error"=>"room_type_id requerido"],400);
if($room_number==='') json_out(["ok"=>false,"error"=>"room_number requerido"],400);
if(!in_array($status, ['active','maintenance'], true)) $status='active';

$stmt = $mysqli->prepare("INSERT INTO rooms(room_type_id, room_number, status) VALUES(?,?,?)");
$stmt->bind_param("iss", $room_type_id, $room_number, $status);

if(!$stmt->execute()){
  json_out(["ok"=>false,"error"=>$stmt->error],500);
}

json_out(["ok"=>true,"id"=>$mysqli->insert_id]);
