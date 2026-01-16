<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Admin - Habitaciones</title>
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
      <h1 class="h4 mb-0">🏨 Admin - Habitaciones</h1>
      <div class="muted small">Crear y gestionar stock de habitaciones</div>
    </div>
    <div class="d-flex gap-2">
      <a class="btn btn-outline-light" href="dashboard.php">Dashboard</a>
      <a class="btn btn-outline-light" href="bookings.php">Reservas</a>
      <a class="btn btn-outline-light" href="../index.php">Web</a>
    </div>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-4">
      <div class="card rounded-4 shadow-sm">
        <div class="card-body">
          <h2 class="h5 mb-2">Crear habitación</h2>
          <form id="formCreate" class="row g-2">
            <div class="col-12">
              <label class="form-label">Tipo</label>
              <select class="form-select" name="room_type_id" id="typeSel" required>
                <option value="">Cargando...</option>
              </select>
            </div>

            <div class="col-12">
              <label class="form-label">Número</label>
              <input class="form-control" name="room_number" placeholder="Ej: 101" required maxlength="10">
            </div>

            <div class="col-12">
              <label class="form-label">Estado</label>
              <select class="form-select" name="status">
                <option value="active" selected>Activa</option>
                <option value="maintenance">Mantenimiento</option>
              </select>
            </div>

            <div class="col-12 d-grid mt-1">
              <button class="btn btn-primary">Crear</button>
            </div>

            <div class="text-secondary small mt-2" id="msg"></div>
          </form>
        </div>
      </div>
    </div>

    <div class="col-12 col-lg-8">
      <div class="card rounded-4 shadow-sm">
        <div class="card-body">
          <div class="d-flex flex-wrap gap-2 justify-content-between align-items-center mb-2">
            <h2 class="h5 mb-0">Listado</h2>
            <button class="btn btn-outline-light btn-sm" id="btnReload">Actualizar</button>
          </div>

          <div class="table-responsive">
            <table class="table table-dark table-hover align-middle mb-0">
              <thead>
                <tr>
                  <th>Número</th>
                  <th>Tipo</th>
                  <th>Capacidad</th>
                  <th>Precio/noche</th>
                  <th>Estado</th>
                  <th class="text-end">Acciones</th>
                </tr>
              </thead>
              <tbody id="tb"></tbody>
            </table>
          </div>

          <div class="muted small mt-2">Tip: “mantenimiento” excluye la habitación de disponibilidad.</div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="../assets/app.js"></script>
<script>
  function money(c){ return "$" + (Number(c||0)/100).toFixed(2); }

  async function loadTypes(){
    const d = await apiGet("../../api/rooms_types_list.php");
    const sel = document.querySelector("#typeSel");
    sel.innerHTML = `<option value="">Selecciona...</option>`;
    if(!d.ok) return;

    (d.types||[]).forEach(t=>{
      sel.insertAdjacentHTML("beforeend", `<option value="${t.id}">${esc(t.name)} (cap ${t.capacity})</option>`);
    });
  }

  async function loadRooms(){
    const d = await apiGet("../../api/admin_rooms_list.php");
    const tb = document.querySelector("#tb");
    tb.innerHTML = "";
    if(!d.ok){
      tb.innerHTML = `<tr><td colspan="6">${esc(d.error||"Error")}</td></tr>`;
      return;
    }

    (d.rooms||[]).forEach(r=>{
      const badge = r.status==='active' ? 'success' : 'warning';
      tb.insertAdjacentHTML("beforeend", `
        <tr>
          <td><b>${esc(r.room_number)}</b></td>
          <td>${esc(r.type_name)}</td>
          <td class="text-secondary">${esc(r.capacity)}</td>
          <td>${money(r.base_price_cents)}</td>
          <td><span class="badge text-bg-${badge}">${esc(r.status)}</span></td>
          <td class="text-end">
            <button class="btn btn-sm btn-outline-light" onclick="toggleStatus(${r.id}, '${r.status}')">
              Cambiar estado
            </button>
          </td>
        </tr>
      `);
    });

    if((d.rooms||[]).length===0){
      tb.innerHTML = `<tr><td colspan="6" class="text-secondary">Sin habitaciones.</td></tr>`;
    }
  }

  async function toggleStatus(id, current){
    const next = current==='active' ? 'maintenance' : 'active';
    if(!confirm(`¿Cambiar estado a "${next}"?`)) return;

    const fd = new FormData();
    fd.append("id", id);
    fd.append("status", next);

    const d = await apiPost("../../api/admin_rooms_update.php", fd);
    if(!d.ok) return alert(d.error || "Error");
    await loadRooms();
  }

  document.querySelector("#formCreate").addEventListener("submit", async (e)=>{
    e.preventDefault();
    const fd = new FormData(e.target);

    const d = await apiPost("../../api/admin_rooms_create.php", fd);
    document.querySelector("#msg").textContent = d.ok ? "Creado ✅" : (d.error || "Error");
    if(d.ok){
      e.target.reset();
      await loadRooms();
    }
  });

  document.querySelector("#btnReload").addEventListener("click", loadRooms);

  window.addEventListener("load", async ()=>{
    await loadTypes();
    await loadRooms();
  });
</script>
</body>
</html>
