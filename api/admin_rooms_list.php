<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/_json.php";

$r = $mysqli->query("SELECT id,name,description,capacity,base_price_cents FROM room_types ORDER BY base_price_cents ASC");
$rows=[];
while($row=$r->fetch_assoc()) $rows[]=$row;

json_out(["ok"=>true,"types"=>$rows]);
