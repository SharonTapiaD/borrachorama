// Cargar carrito desde localStorage o crear vacío
let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

// Guardar carrito en localStorage
function guardarCarrito() {
  localStorage.setItem('carrito', JSON.stringify(carrito));
}

// Renderizar carrito
function renderCarrito() {
  const tbody = document.getElementById("cart-body");
  const totalEl = document.getElementById("cart-total");
  const buyBtn = document.getElementById("buy-btn");

  tbody.innerHTML = "";
  let total = 0;

  carrito.forEach((item, index) => {
    const subtotal = item.precio * item.cantidad;
    total += subtotal;

    const row = `
      <tr>
        <td>${item.nombre}</td>
        <td>$${item.precio.toFixed(2)}</td>
        <td>
          <input type="number" min="1" value="${item.cantidad}" data-index="${index}" class="qty-input">
        </td>
        <td>$${subtotal.toFixed(2)}</td>
        <td><button class="remove-btn" data-index="${index}">Eliminar</button></td>
      </tr>
    `;
    tbody.insertAdjacentHTML("beforeend", row);
  });

  totalEl.textContent = total.toFixed(2);
  buyBtn.disabled = carrito.length === 0;

  guardarCarrito();
}

// Cambiar cantidad
document.addEventListener("input", e => {
  if (e.target.classList.contains("qty-input")) {
    const index = e.target.dataset.index;
    carrito[index].cantidad = parseInt(e.target.value) || 1;
    renderCarrito();
  }
});

// Eliminar producto
document.addEventListener("click", e => {
  if (e.target.classList.contains("remove-btn")) {
    const index = e.target.dataset.index;
    carrito.splice(index, 1);
    renderCarrito();
  }
});

// Botón Comprar
document.addEventListener("DOMContentLoaded", () => {
  renderCarrito();

  const buyBtn = document.getElementById("buy-btn");
  if (buyBtn) {
    buyBtn.addEventListener("click", () => {
      if(carrito.length === 0) return;
      alert(`Compra realizada por $${document.getElementById("cart-total").textContent} MXN 🛒`);
      carrito = [];
      renderCarrito();
    });
  }
});
