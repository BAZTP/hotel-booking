<?php
require_once __DIR__."/../config/db.php";
require_once __DIR__."/_json.php";

function one($mysqli, $sql){
  $r = $mysqli->query($sql);
  return $r ? (int)$r->fetch_row()[0] : 0;
}

$rooms_total = one($mysqli, "SELECT COUNT(*) FROM rooms");
$rooms_active = one($mysqli, "SELECT COUNT(*) FROM rooms WHERE status='active'");
$rooms_maint  = one($mysqli, "SELECT COUNT(*) FROM rooms WHERE status='maintenance'");

$bk_total = one($mysqli, "SELECT COUNT(*) FROM bookings");
$bk_pending = one($mysqli, "SELECT COUNT(*) FROM bookings WHERE status='pending'");
$bk_paid = one($mysqli, "SELECT COUNT(*) FROM bookings WHERE status='paid'");
$bk_cancelled = one($mysqli, "SELECT COUNT(*) FROM bookings WHERE status='cancelled'");

$rev_paid = one($mysqli, "SELECT COALESCE(SUM(total_cents),0) FROM bookings WHERE status='paid'");
$rev_today = one($mysqli, "SELECT COALESCE(SUM(total_cents),0) FROM bookings WHERE status='paid' AND DATE(paid_at)=CURDATE()");

json_out([
  "ok"=>true,
  "stats"=>[
    "rooms_total"=>$rooms_total,
    "rooms_active"=>$rooms_active,
    "rooms_maint"=>$rooms_maint,
    "bookings_total"=>$bk_total,
    "bookings_pending"=>$bk_pending,
    "bookings_paid"=>$bk_paid,
    "bookings_cancelled"=>$bk_cancelled,
    "revenue_paid_cents"=>$rev_paid,
    "revenue_today_cents"=>$rev_today
  ]
]);
