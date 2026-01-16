<?php
$type_id = (int)($_GET['room_type_id'] ?? 0);
$check_in = $_GET['check_in'] ?? '';
$check_out = $_GET['check_out'] ?? '';
$guests = (int)($_GET['guests'] ?? 1);
?>
<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8"/>
  <meta name="viewport" content="width=device-width, initial-scale=1"/>
  <title>Checkout Reserva</title>
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
      <h1 class="h4 mb-0">🧾 Checkout</h1>
      <div class="text-secondary small">Crear reserva y pagar (simulado)</div>
    </div>
    <a class="btn btn-outline-light" href="index.php">← Volver</a>
  </div>

  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card rounded-4 shadow-sm">
        <div class="card-body">
          <h2 class="h5 mb-2">Datos</h2>

          <form id="formCreate" class="row g-2">
            <input type="hidden" name="room_type_id" value="<?= (int)$type_id ?>">
            <input type="hidden" name="check_in" value="<?= htmlspecialchars($check_in, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="check_out" value="<?= htmlspecialchars($check_out, ENT_QUOTES, 'UTF-8') ?>">
            <input type="hidden" name="guests" value="<?= (int)$guests ?>">

            <div class="col-12">
              <label class="form-label">Nombre *</label>
              <input class="form-control" name="customer_name" required maxlength="120">
            </div>
            <div class="col-12">
              <label class="form-label">Email (opcional)</label>
              <input class="form-control" name="customer_email" maxlength="120">
              <div class="text-secondary small">Usa el email para ver “Mis reservas”.</div>
            </div>

            <div class="col-12 d-grid mt-2">
              <button class="btn btn-primary">Crear reserva</button>
            </div>

            <div class="text-secondary small mt-2" id="msg"></div>
          </form>

          <div id="actions" style="display:none;" class="mt-3">
            <div class="alert alert-secondary">
              <div><b>Código:</b> <span id="code">—</span></div>
              <div><b>Habitación:</b> <span id="room">—</span></div>
              <div><b>Noches:</b> <span id="nights">—</span></div>
              <div><b>Total:</b> <span id="total">—</span></div>
            </div>

            <div class="d-flex gap-2">
              <button class="btn btn-success w-100" id="btnPay" type="button">Pagar</button>
              <button class="btn btn-outline-danger w-100" id="btnCancel" type="button">Cancelar</button>
            </div>
          </div>

        </div>
      </div>
    </div>

    <div class="col-12 col-lg-6">
      <div class="card rounded-4 shadow-sm">
        <div class="card-body">
          <h2 class="h5 mb-2">Resumen</h2>
          <div class="text-secondary small">
            Tipo ID: <?= (int)$type_id ?> <br>
            Check-in: <?= htmlspecialchars($check_in, ENT_QUOTES, 'UTF-8') ?> <br>
            Check-out: <?= htmlspecialchars($check_out, ENT_QUOTES, 'UTF-8') ?> <br>
            Huéspedes: <?= (int)$guests ?>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

<script src="assets/app.js"></script>
<script>
  let bookingCode = "";

  document.querySelector("#formCreate").addEventListener("submit", async (e)=>{
    e.preventDefault();
    const fd = new FormData(e.target);

    const d = await apiPost("../api/booking_create.php", fd);
    const msg = document.querySelector("#msg");
    if(!d.ok){ msg.textContent = d.error || "Error"; return; }

    bookingCode = d.booking_code;
    msg.textContent = "Reserva creada ✅";

    document.querySelector("#actions").style.display = "block";
    document.querySelector("#code").textContent = bookingCode;
    document.querySelector("#room").textContent = d.room_number;
    document.querySelector("#nights").textContent = d.nights;
    document.querySelector("#total").textContent = money(d.total_cents);
  });

  document.querySelector("#btnPay").addEventListener("click", async ()=>{
    const fd = new FormData();
    fd.append("booking_code", bookingCode);
    const d = await apiPost("../api/booking_pay.php", fd);
    if(!d.ok) return alert(d.error || "Error");
    alert("✅ Pagado. Estado: " + d.status);
  });

  document.querySelector("#btnCancel").addEventListener("click", async ()=>{
    if(!confirm("¿Cancelar la reserva?")) return;
    const fd = new FormData();
    fd.append("booking_code", bookingCode);
    const d = await apiPost("../api/booking_cancel.php", fd);
    if(!d.ok) return alert(d.error || "Error");
    alert("✅ Cancelado. Estado: " + d.status);
  });
</script>
</body>
</html>
