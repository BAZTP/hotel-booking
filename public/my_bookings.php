<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Mis Reservas</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body{ background:#0b1220; }
    .card{ background: rgba(255,255,255,.03); border:1px solid rgba(255,255,255,.08); }
  </style>
</head>
<body class="text-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h4 mb-0">📌 Mis reservas</h1>
      <div class="text-secondary small">Busca por email o código</div>
    </div>
    <a class="btn btn-outline-light" href="index.php">← Volver</a>
  </div>

  <div class="card rounded-4 shadow-sm mb-3">
    <div class="card-body">
      <div class="row g-2 align-items-end">
        <div class="col-12 col-md-9">
          <label class="form-label">Email o Código</label>
          <input id="q" class="form-control" placeholder="ej: correo@dominio.com o ABC123..." />
        </div>
        <div class="col-12 col-md-3 d-grid">
          <button class="btn btn-primary" id="btn">Buscar</button>
        </div>
      </div>
      <div class="text-secondary small mt-2" id="msg"></div>
    </div>
  </div>

  <div class="card rounded-4 shadow-sm">
    <div class="card-body">
      <div class="table-responsive">
        <table class="table table-dark table-hover align-middle mb-0">
          <thead>
            <tr>
              <th>Código</th>
              <th>Tipo</th>
              <th>Hab</th>
              <th>Fechas</th>
              <th>Estado</th>
              <th>Total</th>
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
  document.querySelector("#btn").addEventListener("click", async ()=>{
    const q = document.querySelector("#q").value.trim();
    if(!q) return;

    const d = await apiGet(`../api/bookings_my_list.php?q=${encodeURIComponent(q)}`);
    const tb = document.querySelector("#tb");
    tb.innerHTML = "";
    document.querySelector("#msg").textContent = d.ok ? "OK ✅" : (d.error || "Error");

    if(!d.ok) return;

    (d.bookings||[]).forEach(b=>{
      const badge =
        b.status==='paid' ? 'success' :
        b.status==='pending' ? 'warning' : 'danger';

      tb.insertAdjacentHTML("beforeend", `
        <tr>
          <td><b>${esc(b.booking_code)}</b></td>
          <td>${esc(b.room_type)}</td>
          <td class="text-secondary">${esc(b.room_number)}</td>
          <td>${esc(b.check_in)} → ${esc(b.check_out)}</td>
          <td><span class="badge text-bg-${badge}">${esc(b.status)}</span></td>
          <td>${money(b.total_cents)}</td>
        </tr>
      `);
    });

    if((d.bookings||[]).length===0){
      tb.innerHTML = `<tr><td colspan="6" class="text-secondary">Sin resultados.</td></tr>`;
    }
  });
</script>
</body>
</html>
