// ============================================================================
// caja.js: lógica de la pantalla de CAJA en el navegador.
// Arma la cuenta (carrito), calcula totales y cambio, y envía la venta al servidor.
// IMPORTANTE: el servidor vuelve a calcular todo con los precios de la base de datos
// (VentaService); lo que se muestra aquí es solo una ayuda visual.
// ============================================================================
(function () {
  // Datos enviados por PHP (productos, token CSRF y URL para guardar la venta)
  const D = JSON.parse(document.getElementById('datos').textContent);
  const $ = id => document.getElementById(id);
  const m = n => '$' + Math.round(n).toLocaleString('es-CO');           // formato de pesos
  let carrito = [];                                                      // cada línea: {id, modo: 'q'|'v'|'g', valor}

  const prod = id => D.productos.find(p => p.id === id);
  // Cantidad real según el modo: q = cantidad, v = valor en $ (valor / precio), g = gramos (/1000)
  const cantidad = l => { const p = prod(l.id); return l.modo === 'v' ? (p.precio_venta > 0 ? l.valor / p.precio_venta : 0) : l.modo === 'g' ? l.valor / 1000 : l.valor; };
  const subtotal = l => { const p = prod(l.id); return l.modo === 'v' ? l.valor : cantidad(l) * p.precio_venta; };
  const total = () => carrito.reduce((t, l) => t + subtotal(l), 0);

  // el(): crea un elemento HTML (se usa textContent, así el texto nunca se interpreta como HTML)
  const el = (tag, props = {}, ...hijos) => { const e = document.createElement(tag); Object.assign(e, props); hijos.forEach(h => e.append(h)); return e; };

  function mostrar(tipo, texto) { const a = $('msg'); a.className = 'aviso ' + tipo; a.textContent = texto; a.style.display = 'block'; }

  // Dibuja la cuadrícula de productos (filtrada por el buscador)
  function dibujarGrid() {
    const q = $('buscar').value.toLowerCase(), g = $('grid');
    g.replaceChildren();
    D.productos.filter(p => p.nombre.toLowerCase().includes(q)).forEach(p => {
      const b = el('button', { className: 's prod', type: 'button', disabled: p.stock <= 0, onclick: () => agregar(p.id) });
      if (p.imagen) b.append(el('img', { className: 'th', src: 'uploads/' + p.imagen, alt: '' }));
      b.append(el('span', {}, el('b', { textContent: p.nombre }), el('br'),
        el('span', { className: 'mu', textContent: m(p.precio_venta) + '/' + p.unidad + ' · ' + p.stock + ' disp.' })));
      g.append(b);
    });
    if (!g.children.length) g.textContent = 'Sin productos';
  }

  // Agrega un producto a la cuenta (si ya está, suma 1)
  function agregar(id) {
    const l = carrito.find(x => x.id === id && x.modo === 'q');
    l ? l.valor++ : carrito.push({ id, modo: 'q', valor: 1 });
    dibujarCuenta();
  }

  // Dibuja la cuenta actual: cada línea con su modo (unidad / $ valor / gramos)
  function dibujarCuenta() {
    const c = $('cuenta');
    c.replaceChildren();
    if (!carrito.length) c.append(el('p', { className: 'mu', textContent: 'Toca un producto para agregarlo.' }));
    carrito.forEach((l, i) => {
      const p = prod(l.id);
      const sel = el('select', { onchange: () => { l.modo = sel.value; l.valor = 1; dibujarCuenta(); } });
      [['q', p.unidad], ['v', '$ valor']].concat(p.unidad === 'kg' ? [['g', 'gramos']] : []).forEach(([v, t]) => {
        const o = el('option', { value: v, textContent: t }); if (l.modo === v) o.selected = true; sel.append(o);
      });
      const inp = el('input', { type: 'number', min: 0, step: 'any', value: l.valor, oninput: () => { l.valor = parseFloat(inp.value) || 0; actualizar(); } });
      c.append(el('div', { className: 'linea' },
        el('b', { textContent: p.nombre }),
        el('button', { className: 's', type: 'button', textContent: '✕', onclick: () => { carrito.splice(i, 1); dibujarCuenta(); } }),
        el('div', { className: 'fila' }, sel, inp),
        el('div', { className: 'mu' }, '= ', el('span', { id: 'eq' + i }), ' · ', el('b', { id: 'st' + i }))));
    });
    actualizar();
  }

  // Recalcula cantidades, totales y cambio sin redibujar (así no se pierde lo que se escribe)
  function actualizar() {
    carrito.forEach((l, i) => { const p = prod(l.id); $('eq' + i).textContent = (+cantidad(l).toFixed(3)) + ' ' + p.unidad; $('st' + i).textContent = m(subtotal(l)); });
    $('total').textContent = m(total());
    const ef = $('pago').value === 'EFECTIVO';
    $('lrec').style.display = ef ? 'flex' : 'none';
    const r = parseFloat($('recibido').value);
    $('cambio').textContent = ef && !isNaN(r) ? (r >= total() ? 'Cambio: ' + m(r - total()) : 'Faltan ' + m(total() - r)) : '';
  }

  // Cobrar: envía la cuenta al servidor
  $('cobrar').onclick = async () => {
    $('msg').style.display = 'none';
    if (!carrito.length) return mostrar('error', 'La cuenta está vacía.');
    const r = parseFloat($('recibido').value), ef = $('pago').value === 'EFECTIVO';
    if (ef && !isNaN(r) && r < total()) return mostrar('error', 'El efectivo recibido no alcanza.');
    $('cobrar').disabled = true;
    try {
      const resp = await fetch(D.url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': D.csrf },     // el token CSRF viaja en la cabecera
        body: JSON.stringify({ pago: $('pago').value, lineas: carrito.map(l => ({ id: l.id, modo: l.modo, valor: l.valor })) })
      });
      const j = await resp.json().catch(() => ({ ok: false, error: 'La sesión expiró. Recarga la página.' }));
      if (!j.ok) throw new Error(j.error || 'No se pudo registrar la venta.');
      const cambio = ef && !isNaN(r) ? ' · Cambio: ' + m(r - j.total) : '';
      carrito.forEach(l => { const p = prod(l.id); p.stock = +(p.stock - cantidad(l)).toFixed(3); });   // actualiza el stock mostrado
      carrito = []; $('recibido').value = '';
      dibujarGrid(); dibujarCuenta();
      mostrar('ok', '✅ Venta #' + j.ticket + ' registrada · Total ' + m(j.total) + cambio);
    } catch (e) { mostrar('error', e.message); }
    $('cobrar').disabled = false;
  };

  $('buscar').oninput = dibujarGrid;
  $('pago').onchange = actualizar;
  $('recibido').oninput = actualizar;
  dibujarGrid(); dibujarCuenta();
})();
