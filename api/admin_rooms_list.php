<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/_json.php";

$sql = "
  SELECT r.id, r.room_number, r.status, r.created_at,
         rt.id AS room_type_id, rt.name AS type_name, rt.capacity, rt.base_price_cents
  FROM rooms r
  JOIN room_types rt ON rt.id=r.room_type_id
  ORDER BY rt.base_price_cents ASC, r.room_number ASC
";
$res = $mysqli->query($sql);

$rows=[];
while($row=$res->fetch_assoc()) $rows[]=$row;

json_out(["ok"=>true,"rooms"=>$rows]);
