<?php
/**
 * Utilidades para manejar promociones en Borrachorama
 */

class PromocionesManager {
    private $conn;

    public function __construct($conn) {
        $this->conn = $conn;
    }

    /**
     * Calcula el precio final de un producto aplicando descuentos
     */
    public function calcularPrecioConDescuento($producto) {
        $precioOriginal = $producto['price'];
        $descuento = 0;

        // Verificar si el producto tiene promoción activa
        if ($producto['promotion_active'] && $this->estaEnFechaPromocion($producto)) {
            if ($producto['discount_percentage'] > 0) {
                $descuento = $precioOriginal * ($producto['discount_percentage'] / 100);
            } elseif ($producto['discount_fixed'] > 0) {
                $descuento = min($producto['discount_fixed'], $precioOriginal);
            }
        }

        return max(0, $precioOriginal - $descuento);
    }

    /**
     * Verifica si una promoción está dentro de las fechas válidas
     */
    private function estaEnFechaPromocion($producto) {
        if (!$producto['promotion_start'] || !$producto['promotion_end']) {
            return true; // Si no hay fechas, se considera siempre válida
        }

        $ahora = new DateTime();
        $inicio = new DateTime($producto['promotion_start']);
        $fin = new DateTime($producto['promotion_end']);

        return $ahora >= $inicio && $ahora <= $fin;
    }

    /**
     * Obtiene promociones activas de categoría
     */
    public function obtenerPromocionesCategoria() {
        $sql = "SELECT * FROM promociones_categorias WHERE activa = 1 AND NOW() BETWEEN fecha_inicio AND fecha_fin";
        $result = $this->conn->query($sql);

        $promociones = [];
        while ($row = $result->fetch_assoc()) {
            $promociones[$row['categoria_id']] = $row;
        }

        return $promociones;
    }

    /**
     * Obtiene promociones de pago activas
     */
    public function obtenerPromocionesPago() {
        $sql = "SELECT * FROM promociones_pago WHERE activa = 1 AND NOW() BETWEEN fecha_inicio AND fecha_fin";
        $result = $this->conn->query($sql);

        return $result->fetch_all(MYSQLI_ASSOC);
    }

    /**
     * Aplica descuentos de categoría a un carrito
     */
    public function aplicarDescuentosCategoria($carrito, $promocionesCategoria) {
        foreach ($carrito as &$item) {
            if (isset($promocionesCategoria[$item['categoria_id']])) {
                $promo = $promocionesCategoria[$item['categoria_id']];
                $descuento = 0;

                if ($promo['descuento_porcentaje'] > 0) {
                    $descuento = $item['precio'] * ($promo['descuento_porcentaje'] / 100);
                } elseif ($promo['descuento_fijo'] > 0) {
                    $descuento = min($promo['descuento_fijo'], $item['precio']);
                }

                $item['descuento_categoria'] = $descuento;
                $item['precio_final'] = max(0, $item['precio'] - $descuento);
            } else {
                $item['descuento_categoria'] = 0;
                $item['precio_final'] = $item['precio'];
            }
        }

        return $carrito;
    }

    /**
     * Aplica descuentos de pago al total
     */
    public function aplicarDescuentosPago($total, $metodoPago, $promocionesPago) {
        $descuentoPago = 0;

        foreach ($promocionesPago as $promo) {
            if ($promo['metodo_pago'] === $metodoPago || $promo['metodo_pago'] === 'todos') {
                if ($total >= $promo['minimo_compra']) {
                    if ($promo['descuento_porcentaje'] > 0) {
                        $descuentoPago = $total * ($promo['descuento_porcentaje'] / 100);
                    } elseif ($promo['descuento_fijo'] > 0) {
                        $descuentoPago = min($promo['descuento_fijo'], $total);
                    }
                    break; // Aplicar solo la primera promoción que coincida
                }
            }
        }

        return $descuentoPago;
    }

    /**
     * Calcula el total del carrito con todos los descuentos aplicados
     */
    public function calcularTotalCarrito($carrito, $metodoPago = null) {
        $subtotal = 0;
        $totalDescuentos = 0;

        // Aplicar descuentos de productos y categorías
        $promocionesCategoria = $this->obtenerPromocionesCategoria();
        $carritoConDescuentos = $this->aplicarDescuentosCategoria($carrito, $promocionesCategoria);

        foreach ($carritoConDescuentos as $item) {
            $precioFinal = $item['precio_final'] ?? $item['precio'];
            $subtotal += $precioFinal * $item['cantidad'];
            $totalDescuentos += ($item['precio'] - $precioFinal) * $item['cantidad'];
        }

        // Aplicar descuentos de pago si se especifica método
        $descuentoPago = 0;
        if ($metodoPago) {
            $promocionesPago = $this->obtenerPromocionesPago();
            $descuentoPago = $this->aplicarDescuentosPago($subtotal, $metodoPago, $promocionesPago);
        }

        $totalFinal = $subtotal - $descuentoPago;

        return [
            'subtotal' => $subtotal,
            'total_descuentos' => $totalDescuentos + $descuentoPago,
            'descuento_pago' => $descuentoPago,
            'total_final' => $totalFinal,
            'carrito' => $carritoConDescuentos
        ];
    }
}
?>