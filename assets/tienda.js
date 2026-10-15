// ================= Carrito de la Microtienda =================
// El carrito vive en localStorage para no perderlo al recargar la página.
// Al enviar el pedido solo se mandan ids y cantidades: el servidor pone los precios.

const CLAVE = 'microtienda-carrito';
const $ = (selector) => document.querySelector(selector);
const euros = (n) => n.toLocaleString('es-ES', { style: 'currency', currency: 'EUR' });

let carrito = cargar();

function cargar() {
  try {
    return JSON.parse(localStorage.getItem(CLAVE)) || [];
  } catch {
    return [];
  }
}

function guardar() {
  try {
    localStorage.setItem(CLAVE, JSON.stringify(carrito));
  } catch {
    // Sin localStorage (modo privado estricto) el carrito sigue funcionando en memoria
  }
}

function anadir({ id, nombre, precio, icono }) {
  const linea = carrito.find((l) => l.id === id);
  if (linea) linea.cantidad = Math.min(99, linea.cantidad + 1);
  else carrito.push({ id, nombre, precio, icono, cantidad: 1 });
  guardar();
  pintar();
  avisar(`${icono} ${nombre} al carrito`);
  const boton = $('#abrir-carrito');
  boton.classList.remove('rebote');
  void boton.offsetWidth; // reinicia la animación
  boton.classList.add('rebote');
}

function cambiarCantidad(id, delta) {
  const linea = carrito.find((l) => l.id === id);
  if (!linea) return;
  linea.cantidad += delta;
  if (linea.cantidad <= 0) carrito = carrito.filter((l) => l.id !== id);
  guardar();
  pintar();
}

function pintar() {
  const lista = $('#lineas');
  lista.innerHTML = '';
  for (const l of carrito) {
    const li = document.createElement('li');
    li.className = 'linea';
    li.innerHTML = `
      <span class="ico"></span>
      <div><span class="nombre"></span><small>${euros(l.precio)} / ud.</small></div>
      <div class="cantidad">
        <button type="button" aria-label="Quitar uno">−</button>
        <span>${l.cantidad}</span>
        <button type="button" aria-label="Añadir uno">+</button>
      </div>`;
    // Textos con textContent: nunca se inyecta HTML con datos
    li.querySelector('.ico').textContent = l.icono;
    li.querySelector('.nombre').textContent = l.nombre;
    const [menos, mas] = li.querySelectorAll('button');
    menos.addEventListener('click', () => cambiarCantidad(l.id, -1));
    mas.addEventListener('click', () => cambiarCantidad(l.id, 1));
    lista.appendChild(li);
  }
  const unidades = carrito.reduce((s, l) => s + l.cantidad, 0);
  $('#contador-carrito').textContent = unidades;
  $('#total').textContent = euros(carrito.reduce((s, l) => s + l.precio * l.cantidad, 0));
  $('#vacio').style.display = carrito.length ? 'none' : 'block';
}

function avisar(texto) {
  const toast = $('#toast');
  toast.textContent = texto;
  toast.classList.add('visible');
  clearTimeout(avisar.t);
  avisar.t = setTimeout(() => toast.classList.remove('visible'), 1800);
}

const abrirCarrito = (abrir) => document.body.classList.toggle('carrito-abierto', abrir);

// ---------- Envío del pedido ----------
async function enviarPedido(e) {
  e.preventDefault();
  const formulario = e.target;
  const datos = Object.fromEntries(new FormData(formulario));
  document.querySelectorAll('[data-error]').forEach((el) => (el.textContent = ''));
  formulario.querySelectorAll('input').forEach((i) => i.classList.remove('invalido'));

  const boton = $('#enviar');
  boton.disabled = true;
  boton.textContent = 'Enviando…';
  try {
    const respuesta = await fetch('api/pedido.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        cliente: datos,
        productos: carrito.map(({ id, cantidad }) => ({ id, cantidad })),
      }),
    });
    const resultado = await respuesta.json();
    if (!respuesta.ok) {
      for (const [campo, mensaje] of Object.entries(resultado.errores || { general: 'Error inesperado.' })) {
        const destino = document.querySelector(`[data-error="${campo}"]`);
        if (destino) destino.textContent = mensaje;
        formulario.elements[campo]?.classList.add('invalido');
      }
      return;
    }
    carrito = [];
    guardar();
    window.location.href = `pedido.php?numero=${encodeURIComponent(resultado.numero)}`;
  } catch {
    document.querySelector('[data-error="general"]').textContent = 'Sin conexión con la tienda. Inténtalo de nuevo.';
  } finally {
    boton.disabled = false;
    boton.textContent = 'Enviar pedido 🚀';
  }
}

// ---------- Eventos ----------
document.querySelectorAll('.anadir').forEach((boton) =>
  boton.addEventListener('click', () =>
    anadir({
      id: +boton.dataset.id,
      nombre: boton.dataset.nombre,
      precio: +boton.dataset.precio,
      icono: boton.dataset.icono,
    }),
  ),
);
$('#abrir-carrito').addEventListener('click', () => abrirCarrito(true));
$('#cerrar-carrito').addEventListener('click', () => abrirCarrito(false));
$('#velo').addEventListener('click', () => abrirCarrito(false));
document.addEventListener('keydown', (e) => e.key === 'Escape' && abrirCarrito(false));
$('#formulario').addEventListener('submit', enviarPedido);
// Al corregir un campo desaparece su error
$('#formulario').addEventListener('input', (e) => {
  e.target.classList.remove('invalido');
  const error = document.querySelector(`[data-error="${e.target.name}"]`);
  if (error) error.textContent = '';
});

pintar();
