<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Admin - Reservas</title>
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
      <h1 class="h4 mb-0">📄 Admin - Reservas</h1>
      <div class="muted small">Filtros por estado y fechas</div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-light" href="dashboard.php">Dashboard</a>
      <a class="btn btn-outline-light" href="rooms.php">Habitaciones</a>
      <a class="btn btn-outline-light" href="../index.php">Web</a>
    </div>
  </div>

  <div class="card rounded-4 shadow-sm mb-3">
    <div class="card-body">
      <form id="formFilter" class="row g-2 align-items-end">
        <div class="col-12 col-md-3">
          <label class="form-label">Estado</label>
          <select class="form-select" id="status">
            <option value="all" selected>Todos</option>
            <option value="pending">Pending</option>
            <option value="paid">Paid</option>
            <option value="cancelled">Cancelled</option>
          </select>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Desde (check-in)</label>
          <input type="date" class="form-control" id="from">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Hasta (check-out)</label>
          <input type="date" class="form-control" id="to">
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Buscar</label>
          <input class="form-control" id="q" placeholder="código / email / nombre">
        </div>
        <div class="col-12 col-md-12 d-grid mt-1">
          <button class="btn btn-primary">Aplicar filtros</button>
        </div>
      </form>
      <div class="text-secondary small mt-2" id="msg"></div>
    </div>
  </div>

  <div class="card rounded-4 shadow-sm">
    <div class="card-body">
      <div class="d-flex justify-content-between align-items-center mb-2">
        <h2 class="h5 mb-0">Listado</h2>
        <button class="btn btn-outline-light btn-sm" id="btnReload" type="button">Actualizar</button>
      </div>

      <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Código</th>
              <th>Cliente</th>
              <th>Tipo/Hab</th>
              <th>Fechas</th>
              <th>Estado</th>
              <th>Total</th>
              <th>Creado</th>
            </tr>
          </thead>
          <tbody id="tb"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="../assets/app.js"></script>
<script>
  function money(c){ return "$" + (Number(c||0)/100).toFixed(2); }

  function badge(status){
    if(status==="paid") return "success";
    if(status==="pending") return "warning";
    return "danger";
  }

  async function loadBookings(){
    const st = document.querySelector("#status").value;
    const from = document.querySelector("#from").value;
    const to = document.querySelector("#to").value;
    const q = document.querySelector("#q").value.trim();

    const url = `../../api/admin_bookings_list.php?status=${encodeURIComponent(st)}&from=${encodeURIComponent(from)}&to=${encodeURIComponent(to)}&q=${encodeURIComponent(q)}`;
    const d = await apiGet(url);

    document.querySelector("#msg").textContent = d.ok ? "OK ✅" : (d.error || "Error");
    const tb = document.querySelector("#tb");
    tb.innerHTML = "";

    if(!d.ok){
      tb.innerHTML = `<tr><td colspan="7">${esc(d.error||"Error")}</td></tr>`;
      return;
    }

    (d.bookings||[]).forEach(b=>{
      tb.insertAdjacentHTML("beforeend", `
        <tr>
          <td><b>${esc(b.booking_code)}</b></td>
          <td>
            ${esc(b.customer_name)}
            <div class="text-secondary small">${esc(b.customer_email||"—")}</div>
          </td>
          <td>${esc(b.room_type)} <span class="text-secondary">#${esc(b.room_number)}</span></td>
          <td>${esc(b.check_in)} → ${esc(b.check_out)} <div class="text-secondary small">${esc(b.guests)} huésped(es)</div></td>
          <td><span class="badge text-bg-${badge(b.status)}">${esc(b.status)}</span></td>
          <td>${money(b.total_cents)}</td>
          <td class="text-secondary">${esc(b.created_at)}</td>
        </tr>
      `);
    });

    if((d.bookings||[]).length===0){
      tb.innerHTML = `<tr><td colspan="7" class="text-secondary">Sin resultados.</td></tr>`;
    }
  }

  document.querySelector("#formFilter").addEventListener("submit", async (e)=>{
    e.preventDefault();
    await loadBookings();
  });

  document.querySelector("#btnReload").addEventListener("click", loadBookings);

  window.addEventListener("load", loadBookings);
</script>
</body>
</html>
