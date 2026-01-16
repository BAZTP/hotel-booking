<?php
function esc($s){ return htmlspecialchars($s ?? '', ENT_QUOTES, 'UTF-8'); }

$type_id  = (int)($_GET['room_type_id'] ?? 0);
$check_in = trim($_GET['check_in'] ?? '');
$check_out= trim($_GET['check_out'] ?? '');
$guests   = (int)($_GET['guests'] ?? 1);

$errors = [];
if($type_id <= 0) $errors[] = "Falta room_type_id (tipo de habitación). Vuelve y elige un tipo con el botón Reservar.";
if($check_in === '' || $check_out === '') $errors[] = "Faltan fechas (check-in/check-out).";
if($check_in !== '' && $check_out !== '' && $check_in >= $check_out) $errors[] = "Rango de fechas inválido.";
if($guests <= 0) $guests = 1;
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
    .muted{ color:#9ca3af; }
  </style>
</head>
<body class="text-light">
<div class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <div>
      <h1 class="h4 mb-0">🧾 Checkout</h1>
      <div class="muted small">Crear reserva y pagar (simulado)</div>
    </div>
    <a class="btn btn-outline-light" href="index.php">← Volver</a>
  </div>

  <?php if(!empty($errors)): ?>
    <div class="alert alert-danger">
      <b>No se puede continuar:</b>
      <ul class="mb-0">
        <?php foreach($errors as $e): ?>
          <li><?= esc($e) ?></li>
        <?php endforeach; ?>
      </ul>
    </div>

    <div class="d-flex gap-2">
      <a class="btn btn-primary" href="index.php">Volver a buscar disponibilidad</a>
    </div>

  <?php else: ?>

  <div class="row g-3">
    <div class="col-12 col-lg-6">
      <div class="card rounded-4 shadow-sm">
        <div class="card-body">
          <h2 class="h5 mb-2">Datos</h2>

          <form id="formCreate" class="row g-2">
            <!-- IMPORTANTÍSIMO: estos hidden deben ir bien -->
            <input type="hidden" name="room_type_id" value="<?= (int)$type_id ?>">
            <input type="hidden" name="check_in" value="<?= esc($check_in) ?>">
            <input type="hidden" name="check_out" value="<?= esc($check_out) ?>">
            <input type="hidden" name="guests" value="<?= (int)$guests ?>">

            <div class="col-12">
              <label class="form-label">Nombre *</label>
              <input class="form-control" name="customer_name" required maxlength="120" autocomplete="name">
            </div>
            <div class="col-12">
              <label class="form-label">Email (opcional)</label>
              <input class="form-control" name="customer_email" maxlength="120" autocomplete="email">
              <div class="muted small">Usa el email para ver “Mis reservas”.</div>
            </div>

            <div class="col-12 d-grid mt-2">
              <button class="btn btn-primary btn-lg">Crear reserva</button>
            </div>

            <div class="muted small mt-2" id="msg"></div>
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
          <div class="muted small">
            Tipo ID: <?= (int)$type_id ?><br>
            Check-in: <?= esc($check_in) ?><br>
            Check-out: <?= esc($check_out) ?><br>
            Huéspedes: <?= (int)$guests ?>
          </div>
        </div>
      </div>
    </div>
  </div>

  <?php endif; ?>
</div>

<script src="assets/app.js"></script>
<script>
  let bookingCode = "";

  // si hay errores, no ejecutamos JS de reserva
  <?php if(empty($errors)): ?>

  const msg = document.querySelector("#msg");

  document.querySelector("#formCreate").addEventListener("submit", async (e)=>{
    e.preventDefault();
    msg.textContent = "Procesando...";

    const fd = new FormData(e.target);
    // DEBUG: puedes ver lo que envía
    // console.log([...fd.entries()]);

    const d = await apiPost("../api/booking_create.php", fd);
    if(!d.ok){
      msg.textContent = d.error || "Error";
      return;
    }

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

  <?php endif; ?>
</script>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
