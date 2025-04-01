<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * Trading Helper
 * 
 * Funciones auxiliares para el sistema de trading
 */

/**
 * Formatea un precio con el número de decimales adecuado
 * 
 * @param float $price Precio a formatear
 * @param int $decimals Número de decimales (por defecto 5)
 * @return string Precio formateado
 */
if (!function_exists('format_price')) {
    function format_price($price, $decimals = 5) {
        return number_format($price, $decimals, '.', '');
    }
}

/**
 * Formatea un valor de PNL
 * 
 * @param float $pnl Valor de PNL
 * @return string PNL formateado
 */
if (!function_exists('format_pnl')) {
    function format_pnl($pnl) {
        return number_format($pnl, 2, '.', '');
    }
}

/**
 * Formatea un porcentaje
 * 
 * @param float $percentage Porcentaje
 * @return string Porcentaje formateado
 */
if (!function_exists('format_percentage')) {
    function format_percentage($percentage) {
        return number_format($percentage, 2, '.', '') . '%';
    }
}

/**
 * Genera una clase CSS basada en el valor de PNL
 * 
 * @param float $pnl Valor de PNL
 * @return string Clase CSS ('text-success' o 'text-danger')
 */
if (!function_exists('pnl_class')) {
    function pnl_class($pnl) {
        return $pnl >= 0 ? 'text-success' : 'text-danger';
    }
}

/**
 * Calcula el PNL para una posición
 * 
 * @param string $direction Dirección ('long' o 'short')
 * @param float $entry_price Precio de entrada
 * @param float $current_price Precio actual
 * @param float $quantity Cantidad
 * @param float $leverage Apalancamiento
 * @return array Array con 'amount' y 'percentage'
 */
if (!function_exists('calculate_pnl')) {
    function calculate_pnl($direction, $entry_price, $current_price, $quantity, $leverage = 1) {
        if ($direction === 'long') {
            $price_diff = $current_price - $entry_price;
        } else {
            $price_diff = $entry_price - $current_price;
        }
        
        $pnl_amount = $price_diff * $quantity * $leverage;
        $pnl_percentage = ($price_diff / $entry_price) * 100 * $leverage;
        
        return [
            'amount' => $pnl_amount,
            'percentage' => $pnl_percentage
        ];
    }
}

/**
 * Calcula el tamaño de la posición basado en el riesgo
 * 
 * @param float $balance Saldo disponible
 * @param float $risk_percentage Porcentaje de riesgo
 * @param float $price Precio del activo
 * @param float $leverage Apalancamiento
 * @return float Tamaño de la posición
 */
if (!function_exists('calculate_position_size')) {
    function calculate_position_size($balance, $risk_percentage, $price, $leverage = 1) {
        // Calcular el monto a arriesgar
        $risk_amount = $balance * ($risk_percentage / 100);
        
        // Calcular el tamaño de la posición
        $position_size = ($risk_amount * $leverage) / $price;
        
        return $position_size;
    }
}

/**
 * Genera un nombre de cliente para las órdenes
 * 
 * @param string $strategy_id ID de la estrategia
 * @param string $action Acción ('buy' o 'sell')
 * @return string ID de cliente único
 */
if (!function_exists('generate_client_order_id')) {
    function generate_client_order_id($strategy_id, $action) {
        $timestamp = time();
        $random = mt_rand(1000, 9999);
        return strtoupper(substr($strategy_id, 0, 5)) . '-' . $action[0] . '-' . $timestamp . '-' . $random;
    }
}

/**
 * Valida un par de trading
 * 
 * @param string $ticker Símbolo del par
 * @return bool Verdadero si es válido
 */
if (!function_exists('is_valid_ticker')) {
    function is_valid_ticker($ticker) {
        // Validación básica (personalizar según las reglas de BingX)
        return (bool) preg_match('/^[A-Z0-9]+$/', $ticker);
    }
}

/**
 * Obtiene el nombre de la clase CSS para un tipo de mercado
 * 
 * @param string $market_type Tipo de mercado ('spot' o 'futures')
 * @return string Nombre de la clase
 */
if (!function_exists('market_type_class')) {
    function market_type_class($market_type) {
        switch ($market_type) {
            case 'spot':
                return 'info';
            case 'futures':
                return 'warning';
            default:
                return 'secondary';
        }
    }
}

/**
 * Genera un color basado en un texto para gráficos
 * 
 * @param string $text Texto base
 * @param int $lightness Luminosidad (0-100)
 * @return string Color en formato hex
 */
if (!function_exists('text_to_color')) {
    function text_to_color($text, $lightness = 50) {
        // Generar un hash del texto
        $hash = md5($text);
        
        // Convertir los primeros 3 bytes en valores HSL
        $h = hexdec(substr($hash, 0, 2)) % 360; // Hue 0-359
        $s = hexdec(substr($hash, 2, 2)) % 30 + 70; // Saturation 70-100
        $l = $lightness; // Lightness fijo para mejor visibilidad
        
        // Convertir HSL a RGB
        $c = (1 - abs(2 * $l / 100 - 1)) * $s / 100;
        $x = $c * (1 - abs(fmod(($h / 60), 2) - 1));
        $m = $l / 100 - $c / 2;
        
        if ($h < 60) {
            $r = $c; $g = $x; $b = 0;
        } else if ($h < 120) {
            $r = $x; $g = $c; $b = 0;
        } else if ($h < 180) {
            $r = 0; $g = $c; $b = $x;
        } else if ($h < 240) {
            $r = 0; $g = $x; $b = $c;
        } else if ($h < 300) {
            $r = $x; $g = 0; $b = $c;
        } else {
            $r = $c; $g = 0; $b = $x;
        }
        
        $r = round(($r + $m) * 255);
        $g = round(($g + $m) * 255);
        $b = round(($b + $m) * 255);
        
        return sprintf("#%02x%02x%02x", $r, $g, $b);
    }
}

/**
 * Convierte un error de la API en un mensaje legible
 * 
 * @param string $error_message Mensaje de error de la API
 * @return string Mensaje legible
 */
if (!function_exists('friendly_api_error')) {
    function friendly_api_error($error_message) {
        $common_errors = [
            'Invalid API-key' => 'API Key inválida. Verifica tus credenciales.',
            'Signature for this request is not valid' => 'Firma inválida. Verifica tu API Secret.',
            'Timestamp for this request is outside' => 'Error de sincronización de tiempo. Verifica la hora de tu servidor.',
            'Invalid symbol' => 'Símbolo de trading inválido.',
            'Market is closed' => 'El mercado está cerrado.',
            'Balance insufficient' => 'Saldo insuficiente para ejecutar la operación.',
            'Too many requests' => 'Demasiadas solicitudes. Espera unos segundos e intenta nuevamente.',
            'System busy' => 'Sistema ocupado. Intenta más tarde.',
            'MIN_NOTIONAL' => 'El valor de la orden es demasiado pequeño.',
            'MAX_NUM_ORDERS' => 'Has alcanzado el número máximo de órdenes.'
        ];
        
        // Buscar coincidencias en los errores comunes
        foreach ($common_errors as $key => $message) {
            if (stripos($error_message, $key) !== false) {
                return $message;
            }
        }
        
        // Si no hay coincidencias, devolver el mensaje original
        return $error_message;
    }
}

/**
 * Formatea un timestamp en formato legible
 * 
 * @param int $timestamp Timestamp Unix
 * @return string Fecha formateada
 */
if (!function_exists('format_timestamp')) {
    function format_timestamp($timestamp) {
        return date('d/m/Y H:i:s', $timestamp);
    }
}

/**
 * Calcula la diferencia entre dos timestamps en formato legible
 * 
 * @param int $start_time Timestamp de inicio
 * @param int $end_time Timestamp de fin (por defecto, tiempo actual)
 * @return string Diferencia de tiempo en formato legible
 */
if (!function_exists('time_elapsed')) {
    function time_elapsed($start_time, $end_time = null) {
        if ($end_time === null) {
            $end_time = time();
        }
        
        $diff = $end_time - $start_time;
        
        if ($diff < 60) {
            return $diff . ' segundos';
        }
        
        if ($diff < 3600) {
            $minutes = floor($diff / 60);
            $seconds = $diff % 60;
            return $minutes . ' minutos, ' . $seconds . ' segundos';
        }
        
        if ($diff < 86400) {
            $hours = floor($diff / 3600);
            $minutes = floor(($diff % 3600) / 60);
            return $hours . ' horas, ' . $minutes . ' minutos';
        }
        
        $days = floor($diff / 86400);
        $hours = floor(($diff % 86400) / 3600);
        return $days . ' días, ' . $hours . ' horas';
    }
}