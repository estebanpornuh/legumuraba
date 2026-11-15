<div class="card"><b>🧾 Caja</b> <span class="mu">· toca un producto para agregarlo y luego cobra</span></div>
<div class="caja">
  <section class="card">
    <h3>Productos</h3>
    <input id="buscar" placeholder="Buscar producto…">
    <div id="grid" class="g" style="margin-top:10px"></div>
  </section>
  <section class="card">
    <h3>Cuenta actual</h3>
    <div id="cuenta"></div>
    <div class="tot">Total: <span id="total">$0</span></div>
    <label>Método de pago
      <select id="pago"><option value="EFECTIVO">Efectivo</option><option value="NEQUI">Nequi</option><option value="TRANSFERENCIA">Transferencia</option></select>
    </label>
    <label id="lrec">Efectivo recibido<input id="recibido" type="number" min="0" step="any"></label>
    <div id="cambio" class="tot"></div>
    <div id="msg" class="aviso" style="display:none"></div>
    <button id="cobrar" class="b">Cobrar</button>
  </section>
</div>
<!-- Datos para caja.js. JSON_HEX_* evita que el contenido rompa el HTML (XSS) -->
<script id="datos" type="application/json"><?= json_encode(['productos' => $productos, 'csrf' => Csrf::token(), 'url' => url('ventas/guardar')], JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?></script>
<script src="assets/caja.js"></script>
