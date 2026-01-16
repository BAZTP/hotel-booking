<?php
// FORZAR ERRORES (solo en local)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../config/db.php";
require_once __DIR__ . "/_json.php";

if (!isset($mysqli)) {
  json_out(["ok"=>false,"error"=>"DB no inicializada"],500);
}

$res = $mysqli->query("
  SELECT id, name, description, capacity, base_price_cents
  FROM room_types
  ORDER BY base_price_cents ASC
");

if (!$res) {
  json_out(["ok"=>false,"error"=>$mysqli->error],500);
}

$types = [];
while ($row = $res->fetch_assoc()) {
  $types[] = $row;
}

json_out([
  "ok" => true,
  "types" => $types
]);
