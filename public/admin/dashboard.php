<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Admin - Dashboard</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{ background:#0b1220; }
    .card{ background: rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08); }
    .muted{ color:#9ca3af; }
  </style>
</head>
<body class="text-light">
<div class="container py-4">
  <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h4 mb-0">🛠️ Admin - Dashboard</h1>
      <div class="muted small">Métricas del sistema</div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-light" href="../index.php">Web</a>
      <a class="btn btn-outline-light" href="rooms.php">Habitaciones</a>
      <a class="btn btn-outline-light" href="bookings.php">Reservas</a>
    </div>
  </div>

  <div class="row g-3" id="cards">
    <div class="col-6 col-lg-3"><div class="card rounded-4 shadow-sm h-100"><div class="card-body">
      <div class="text-secondary small">Habitaciones</div>
      <div class="h2 mb-0" id="rooms_total">0</div>
      <div class="muted small"><span id="rooms_active">0</span> activas · <span id="rooms_maint">0</span> mantenimiento</div>
    </div></div></div>

    <div class="col-6 col-lg-3"><div class="card rounded-4 shadow-sm h-100"><div class="card-body">
      <div class="text-secondary small">Reservas</div>
      <div class="h2 mb-0" id="bk_total">0</div>
      <div class="muted small">
        <span id="bk_pending">0</span> pending ·
        <span id="bk_paid">0</span> paid ·
        <span id="bk_cancelled">0</span> cancelled
      </div>
    </div></div></div>

    <div class="col-12 col-lg-3"><div class="card rounded-4 shadow-sm h-100"><div class="card-body">
      <div class="text-secondary small">Ingresos (pagados)</div>
      <div class="h2 mb-0" id="rev_paid">$0.00</div>
      <div class="muted small">Total histórico</div>
    </div></div></div>

    <div class="col-12 col-lg-3"><div class="card rounded-4 shadow-sm h-100"><div class="card-body">
      <div class="text-secondary small">Ingresos hoy</div>
      <div class="h2 mb-0" id="rev_today">$0.00</div>
      <div class="muted small">Pagos de hoy</div>
    </div></div></div>
  </div>

  <div class="card rounded-4 shadow-sm mt-3">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center">
        <div>
          <h2 class="h5 mb-0">Acciones rápidas</h2>
          <div class="muted small">Admin básico (sin login por ahora)</div>
        </div>
        <button class="btn btn-outline-light" id="btnReload">Actualizar</button>
      </div>
    </div>
  </div>
</div>

<script src="../assets/app.js"></script>
<script>
  function money(c){ return "$" + (Number(c||0)/100).toFixed(2); }

  async function loadStats(){
    const d = await apiGet("../../api/admin_dashboard_stats.php");
    if(!d.ok) return alert(d.error || "Error");

    const s = d.stats || {};
    document.querySelector("#rooms_total").textContent = s.rooms_total ?? 0;
    document.querySelector("#rooms_active").textContent = s.rooms_active ?? 0;
    document.querySelector("#rooms_maint").textContent = s.rooms_maint ?? 0;

    document.querySelector("#bk_total").textContent = s.bookings_total ?? 0;
    document.querySelector("#bk_pending").textContent = s.bookings_pending ?? 0;
    document.querySelector("#bk_paid").textContent = s.bookings_paid ?? 0;
    document.querySelector("#bk_cancelled").textContent = s.bookings_cancelled ?? 0;

    document.querySelector("#rev_paid").textContent = money(s.revenue_paid_cents ?? 0);
    document.querySelector("#rev_today").textContent = money(s.revenue_today_cents ?? 0);
  }

  document.querySelector("#btnReload").addEventListener("click", loadStats);
  window.addEventListener("load", loadStats);
</script>
</body>
</html>
