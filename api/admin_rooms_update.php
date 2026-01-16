<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/_json.php";

$id = (int)($_POST['id'] ?? 0);
$status = trim($_POST['status'] ?? '');

if($id<=0) json_out(["ok"=>false,"error"=>"id inválido"],400);
if(!in_array($status, ['active','maintenance'], true)) json_out(["ok"=>false,"error"=>"status inválido"],400);

$stmt = $mysqli->prepare("UPDATE rooms SET status=? WHERE id=?");
$stmt->bind_param("si",$status,$id);

if(!$stmt->execute()) json_out(["ok"=>false,"error"=>$stmt->error],500);

json_out(["ok"=>true]);
