document.addEventListener("DOMContentLoaded", () => {
  const container = document.getElementById("catalog-container");

  // Cargar productos desde JSON
  fetch("../data/products.json") 
    .then(response => response.json())
    .then(products => {
      products.forEach(product => {
        const card = document.createElement("div");
        card.classList.add("product-card");

        card.innerHTML = `
          <img src="${product.image}" alt="${product.name}" class="product-image">
          <div class="product-name">${product.name}</div>
          <div class="product-category">${product.category}</div>
          <div class="product-description">${product.description}</div>
          <div class="product-price">$${product.price.toFixed(2)}</div>
          <button class="btn btn-warning text-dark agregar" 
                  data-nombre="${product.name}" 
                  data-precio="${product.price}">
            Agregar al carrito
          </button>
        `;

        container.appendChild(card);

        // Agregar evento al botón
        const button = card.querySelector('button');
        button.addEventListener('click', () => {
          const nombre = button.getAttribute('data-nombre');
          const precio = parseFloat(button.getAttribute('data-precio'));

          let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
          const productoExistente = carrito.find(p => p.nombre === nombre);

          if (productoExistente) {
            productoExistente.cantidad += 1;
          } else {
            carrito.push({ nombre, precio, cantidad: 1 });
          }

          localStorage.setItem('carrito', JSON.stringify(carrito));
          alert(`${nombre} se agregó al carrito 🛒`);
        });
      });
    })
    .catch(error => {
      console.error("Error al cargar productos:", error);
      container.innerHTML = '<p class="text-center">Error al cargar los productos. Por favor, intente más tarde.</p>';
    });
});