<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Hotel Reservas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{ background:#0b1220; }
    .card{ background: rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08); }
  </style>
</head>
<body class="text-light">
<div class="container py-4">
  <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h3 mb-0">🏨 Reservas de Hotel</h1>
      <div class="text-secondary small">Busca disponibilidad por fechas</div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-light" href="my_bookings.php">Mis reservas</a>
      <a class="btn btn-outline-light" href="admin/dashboard.php">Admin</a>
    </div>
  </div>

  <div class="card rounded-4 shadow-sm mb-3">
    <div class="card-body">
      <form id="formSearch" class="row g-2 align-items-end">
        <div class="col-12 col-md-3">
          <label class="form-label">Check-in</label>
          <input type="date" class="form-control" id="check_in" required>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Check-out</label>
          <input type="date" class="form-control" id="check_out" required>
        </div>
        <div class="col-12 col-md-2">
          <label class="form-label">Huéspedes</label>
          <input type="number" class="form-control" id="guests" min="1" value="2" required>
        </div>
        <div class="col-12 col-md-3">
          <label class="form-label">Tipo (opcional)</label>
          <select class="form-select" id="type_id">
            <option value="0">Todos</option>
          </select>
        </div>
        <div class="col-12 col-md-1 d-grid">
          <button class="btn btn-primary">Buscar</button>
        </div>
      </form>
      <div class="text-secondary small mt-2" id="msg"></div>
    </div>
  </div>

  <div class="card rounded-4 shadow-sm">
    <div class="card-body">
      <h2 class="h5 mb-2">Resultados</h2>
      <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Tipo</th>
              <th>Capacidad</th>
              <th>Precio/noche</th>
              <th>Disponibles</th>
              <th class="text-end">Acción</th>
            </tr>
          </thead>
          <tbody id="tb"></tbody>
        </table>
      </div>
    </div>
  </div>
</div>

<script src="assets/app.js"></script>
<script>
  async function loadTypes(){
    const d = await apiGet("../api/rooms_types_list.php");
    if(!d.ok) return;
    const sel = document.querySelector("#type_id");
    (d.types||[]).forEach(t=>{
      sel.insertAdjacentHTML("beforeend", `<option value="${t.id}">${esc(t.name)}</option>`);
    });
  }

  document.querySelector("#formSearch").addEventListener("submit", async (e)=>{
    e.preventDefault();
    const ci = document.querySelector("#check_in").value;
    const co = document.querySelector("#check_out").value;
    const g  = document.querySelector("#guests").value;
    const typeId = document.querySelector("#type_id").value;

    const url = `../api/rooms_search.php?check_in=${encodeURIComponent(ci)}&check_out=${encodeURIComponent(co)}&guests=${encodeURIComponent(g)}&type_id=${encodeURIComponent(typeId)}`;
    const d = await apiGet(url);

    const tb = document.querySelector("#tb");
    tb.innerHTML = "";
    document.querySelector("#msg").textContent = d.ok ? "OK ✅" : (d.error || "Error");

    if(!d.ok) return;

    (d.results||[]).forEach(r=>{
      tb.insertAdjacentHTML("beforeend", `
        <tr>
          <td>
            <b>${esc(r.name)}</b>
            <div class="text-secondary small">${esc(r.description||"")}</div>
          </td>
          <td>${esc(r.capacity)}</td>
          <td>${money(r.base_price_cents)}</td>
          <td><span class="badge text-bg-info">${esc(r.available_rooms)}</span></td>
          <td class="text-end">
            <a class="btn btn-sm btn-success ${Number(r.available_rooms)===0?'disabled':''}"
              href="checkout.php?room_type_id=${r.room_type_id}&check_in=${encodeURIComponent(ci)}&check_out=${encodeURIComponent(co)}&guests=${encodeURIComponent(g)}">
              Reservar
            </a>
          </td>
        </tr>
      `);
    });

    if((d.results||[]).length===0){
      tb.innerHTML = `<tr><td colspan="5" class="text-secondary">Sin resultados.</td></tr>`;
    }
  });

  window.addEventListener("load", loadTypes);
</script>
</body>
</html>
