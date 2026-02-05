document.addEventListener('DOMContentLoaded', () => {
    fetch('js/products.json')
        .then(res => res.json())
        .then(data => mostrarProductos(data))
        .catch(err => console.error('Error al cargar productos:', err));
});

function mostrarProductos(productos){
    const container = document.getElementById('productos');
    container.innerHTML = '';
    productos.forEach(p => {
        const div = document.createElement('div');
        div.className = 'card';
        div.innerHTML = `
            <h3>${p.name}</h3>
            <p><strong>Categoría:</strong> ${p.category}</p>
            <p>${p.description}</p>
            <p><strong>Precio:</strong> $${p.price.toFixed(2)}</p>
            <button class="cta" onclick="agregarCarrito('${p.name}', ${p.price})">Agregar al carrito</button>
        `;
        container.appendChild(div);
    });
}

let carrito = [];

function agregarCarrito(nombre, precio){
    carrito.push({nombre, precio});
    actualizarCarrito();
}

function actualizarCarrito(){
    const container = document.getElementById('carrito-items');
    if(carrito.length === 0){
        container.textContent = 'Aún no hay productos en el carrito.';
        return;
    }

    container.innerHTML = '';
    let total = 0;
    carrito.forEach(item => {
        const div = document.createElement('div');
        div.textContent = `${item.nombre} - $${item.precio.toFixed(2)}`;
        container.appendChild(div);
        total += item.precio;
    });

    const totalDiv = document.createElement('p');
    totalDiv.innerHTML = `<strong>Total:</strong> $${total.toFixed(2)}`;
    container.appendChild(totalDiv);
}

// ================= FUNCIONES PARA DONACIONES =================

// Función para registrar donación en el sistema
function registrarDonacion(monto, metodo, donante) {
    const donacion = {
        id: 'DON-' + Date.now(),
        fecha: new Date().toISOString(),
        monto: parseFloat(monto),
        metodo: metodo,
        donante: donante,
        estado: 'Completado',
        facturaRelacionada: localStorage.getItem('lastPedidoId')
    };
    
    // Guardar en localStorage
    let donaciones = JSON.parse(localStorage.getItem('donaciones') || '[]');
    donaciones.push(donacion);
    localStorage.setItem('donaciones', JSON.stringify(donacion));
    
    // Actualizar estadísticas
    actualizarEstadisticasDonaciones(monto);
    
    return donacion;
}

// Función para actualizar estadísticas
function actualizarEstadisticasDonaciones(monto) {
    let estadisticas = JSON.parse(localStorage.getItem('estadisticasDonaciones') || '{}');
    
    if (!estadisticas.totalDonado) {
        estadisticas = {
            totalDonado: 0,
            totalDonaciones: 0,
            ultimaActualizacion: new Date().toISOString()
        };
    }
    
    estadisticas.totalDonado += parseFloat(monto);
    estadisticas.totalDonaciones += 1;
    estadisticas.ultimaActualizacion = new Date().toISOString();
    
    localStorage.setItem('estadisticasDonaciones', JSON.stringify(estadisticas));
    
    console.log('Estadísticas actualizadas:', estadisticas);
    return estadisticas;
}

// Función para obtener impacto de donaciones
function obtenerImpactoDonaciones() {
    const estadisticas = JSON.parse(localStorage.getItem('estadisticasDonaciones') || '{}');
    const total = estadisticas.totalDonado || 0;
    
    return {
        totalDonado: total,
        arbolesPlantados: Math.floor(total / 2), // $2 por árbol
        litrosAguaProtegidos: total * 10, // 10L por peso
        familiasEducadas: Math.floor(total / 20), // $20 por familia
        totalDonaciones: estadisticas.totalDonaciones || 0
    };
}

// Función para mostrar notificación de donación
function mostrarNotificacionDonacion() {
    const impacto = obtenerImpactoDonaciones();
    
    // Crear notificación
    const notificacion = document.createElement('div');
    notificacion.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: linear-gradient(135deg, #4CAF50 0%, #2E7D32 100%);
        color: white;
        padding: 15px 20px;
        border-radius: 10px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.3);
        z-index: 10000;
        animation: slideIn 0.5s ease;
        max-width: 300px;
    `;
    
    notificacion.innerHTML = `
        <strong>🌱 ¡Gracias por donar!</strong>
        <p style="margin: 5px 0 0 0; font-size: 0.9em;">
            Has ayudado a plantar aproximadamente ${impacto.arbolesPlantados} árboles.
        </p>
    `;
    
    document.body.appendChild(notificacion);
    
    // Remover después de 5 segundos
    setTimeout(() => {
        notificacion.style.animation = 'slideOut 0.5s ease';
        setTimeout(() => notificacion.remove(), 500);
    }, 5000);
    
    // Agregar estilos de animación
    if (!document.querySelector('#notificacion-estilos')) {
        const estilos = document.createElement('style');
        estilos.id = 'notificacion-estilos';
        estilos.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(estilos);
    }
}

// ================= SIMULACIÓN DE FALLOS DE PAGO =================
const ConfiguracionPago = {
    probabilidadError: 0.3, // 30% de probabilidad de fallo
    motivosError: [
        "Fondos insuficientes",
        "Tarjeta rechazada por el banco", 
        "Excedió el límite de compra",
        "Tiempo de espera agotado",
        "Error de conexión con el procesador",
        "Código de seguridad incorrecto",
        "Tarjeta vencida",
        "Transacción marcada como sospechosa"
    ],
    reintentosPermitidos: 2
};

// Función para simular procesamiento de pago con posibilidad de fallo
function simularProcesamientoPago(metodoPago, monto) {
    console.log(`Simulando pago con ${metodoPago} por $${monto}`);
    
    // Determinar si hay fallo (30% de probabilidad)
    const tieneFallo = Math.random() < ConfiguracionPago.probabilidadError;
    
    if (tieneFallo) {
        // Seleccionar motivo aleatorio
        const motivoIndex = Math.floor(Math.random() * ConfiguracionPago.motivosError.length);
        const motivo = ConfiguracionPago.motivosError[motivoIndex];
        
        return {
            exito: false,
            error: motivo,
            codigoError: `ERR-${Date.now().toString().slice(-6)}`,
            recomendacion: obtenerRecomendacionPorError(motivo),
            tiempoSimulado: Math.floor(Math.random() * 3000) + 2000 // 2-5 segundos
        };
    } else {
        // Pago exitoso
        return {
            exito: true,
            transaccionId: `TRX-${Date.now().toString().slice(-8)}`,
            referencia: `REF-${Math.floor(Math.random() * 1000000).toString().padStart(6, '0')}`,
            tiempoSimulado: Math.floor(Math.random() * 2000) + 1000 // 1-3 segundos
        };
    }
}

// Función para obtener recomendación según el error
function obtenerRecomendacionPorError(motivo) {
    const recomendaciones = {
        "Fondos insuficientes": "Verifica el saldo de tu cuenta o intenta con otra tarjeta.",
        "Tarjeta rechazada por el banco": "Contacta a tu banco para autorizar la transacción.",
        "Excedió el límite de compra": "Divide tu compra en pagos más pequeños o contacta a tu banco.",
        "Tiempo de espera agotado": "Intenta nuevamente en unos minutos.",
        "Error de conexión con el procesador": "Verifica tu conexión a internet e intenta de nuevo.",
        "Código de seguridad incorrecto": "Asegúrate de ingresar correctamente el CVV de tu tarjeta.",
        "Tarjeta vencida": "Verifica la fecha de expiración de tu tarjeta.",
        "Transacción marcada como sospechosa": "Contacta a tu banco para autorizar esta compra."
    };
    
    return recomendaciones[motivo] || "Intenta nuevamente o contacta a servicio al cliente.";
}

// Función para mostrar spinner de procesamiento
function mostrarSpinnerProcesamiento(mensaje = "Procesando pago...") {
    const spinnerHTML = `
        <div id="spinner-pago" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; display: flex; justify-content: center; align-items: center;">
            <div style="background: white; padding: 30px; border-radius: 15px; text-align: center; max-width: 400px; width: 90%;">
                <div style="border: 5px solid #f3f3f3; border-top: 5px solid #ffb300; border-radius: 50%; width: 50px; height: 50px; animation: spin 1s linear infinite; margin: 0 auto 20px;"></div>
                <h3 style="color: #333; margin-bottom: 10px;">${mensaje}</h3>
                <p style="color: #666; font-size: 0.9em;">Simulando procesamiento de pago...</p>
                <div style="margin-top: 20px; font-size: 0.8em; color: #999;">
                    <div id="tiempo-restante">Tiempo estimado: 3 segundos</div>
                </div>
            </div>
        </div>
        <style>
            @keyframes spin {
                0% { transform: rotate(0deg); }
                100% { transform: rotate(360deg); }
            }
        </style>
    `;
    
    document.body.insertAdjacentHTML('beforeend', spinnerHTML);
    
    // Actualizar contador de tiempo
    let segundos = 3;
    const intervalo = setInterval(() => {
        segundos--;
        const elemento = document.getElementById('tiempo-restante');
        if (elemento && segundos > 0) {
            elemento.textContent = `Tiempo estimado: ${segundos} segundo${segundos !== 1 ? 's' : ''}`;
        }
    }, 1000);
    
    return {
        eliminar: () => {
            const spinner = document.getElementById('spinner-pago');
            if (spinner) spinner.remove();
            clearInterval(intervalo);
        },
        intervalo: intervalo
    };
}