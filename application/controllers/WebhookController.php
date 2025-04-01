<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * WebhookController
 * Maneja las señales entrantes desde TradingView
 */
class WebhookController extends CI_Controller {
    
    public function __construct() {
        parent::__construct();
        $this->load->model('Trading_model');
        $this->load->model('Strategy_model');
        $this->load->model('Log_model');
        $this->load->library('BingxApi');
    }
    
    /**
     * Endpoint principal para recibir webhooks de TradingView
     */
    public function receive() {
        // Verificar que sea una solicitud POST
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->output->set_status_header(405);
            echo json_encode(['error' => 'Method not allowed']);
            return;
        }
        
        // Obtener el contenido JSON del webhook
        $payload = file_get_contents('php://input');
        $signal = json_decode($payload, true);
        
        // Validar que el JSON sea válido
        if (json_last_error() !== JSON_ERROR_NONE) {
            $this->Log_model->add_log('error', 'WebhookController', 'JSON inválido recibido: ' . $payload);
            $this->output->set_status_header(400);
            echo json_encode(['error' => 'Invalid JSON']);
            return;
        }
        
        // Guardar la señal recibida
        $signal_id = $this->Trading_model->save_signal($signal);
        
        // Validar campos requeridos en la señal
        $required_fields = ['strategyId', 'ticker', 'timeframe', 'action'];
        foreach ($required_fields as $field) {
            if (!isset($signal[$field])) {
                $this->Log_model->add_log('error', 'WebhookController', 'Campo requerido faltante: ' . $field);
                $this->output->set_status_header(400);
                echo json_encode(['error' => 'Missing required field: ' . $field]);
                return;
            }
        }
        
        // Obtener información de la estrategia
        $strategy = $this->Strategy_model->get_strategy($signal['strategyId']);
        if (!$strategy) {
            $this->Log_model->add_log('error', 'WebhookController', 'Estrategia no encontrada: ' . $signal['strategyId']);
            $this->output->set_status_header(404);
            echo json_encode(['error' => 'Strategy not found']);
            return;
        }
        
        // Determinar el entorno (sandbox o producción)
        $environment = $this->Trading_model->get_active_environment();
        
        try {
            // Inicializar la API de BingX con las credenciales adecuadas
            $this->bingxapi->initialize($environment, $strategy['market_type']);
            
            // Ejecutar la operación según el tipo de mercado
            if ($strategy['market_type'] == 'futures') {
                $leverage = isset($signal['leverage']) ? $signal['leverage'] : $strategy['default_leverage'];
                $result = $this->execute_futures_order($signal, $strategy, $leverage);
            } else {
                $result = $this->execute_spot_order($signal, $strategy);
            }
            
            // Guardar la operación en la base de datos
            $order_id = $this->Trading_model->save_order($signal_id, $result);
            
            // Enviar respuesta exitosa
            $this->output->set_status_header(200);
            echo json_encode(['success' => true, 'orderId' => $order_id]);
            
        } catch (Exception $e) {
            $this->Log_model->add_log('error', 'WebhookController', 'Error al ejecutar operación: ' . $e->getMessage());
            $this->output->set_status_header(500);
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
    
    /**
     * Ejecuta una orden en el mercado de futuros
     */
    private function execute_futures_order($signal, $strategy, $leverage) {
        // Configurar el apalancamiento
        $this->bingxapi->set_leverage($signal['ticker'], $leverage);
        
        // Determinar el tamaño de la posición
        $position_size = isset($signal['positionSize']) ? 
            $signal['positionSize'] : $this->calculate_position_size($strategy, $signal['ticker']);
        
        // Determinar la dirección de la operación
        $side = strtolower($signal['action']);
        if (!in_array($side, ['buy', 'sell'])) {
            throw new Exception('Acción no válida: ' . $side);
        }
        
        // Ejecutar la orden
        $order_params = [
            'symbol' => $signal['ticker'],
            'side' => $side,
            'positionSide' => 'BOTH',
            'type' => 'MARKET',
            'quantity' => $position_size,
            'newOrderRespType' => 'RESULT'
        ];
        
        return $this->bingxapi->create_futures_order($order_params);
    }
    
    /**
     * Ejecuta una orden en el mercado spot
     */
    private function execute_spot_order($signal, $strategy) {
        // Determinar el tamaño de la posición
        $position_size = isset($signal['positionSize']) ? 
            $signal['positionSize'] : $this->calculate_position_size($strategy, $signal['ticker']);
        
        // Determinar la dirección de la operación
        $side = strtolower($signal['action']);
        if (!in_array($side, ['buy', 'sell'])) {
            throw new Exception('Acción no válida: ' . $side);
        }
        
        // Ejecutar la orden
        $order_params = [
            'symbol' => $signal['ticker'],
            'side' => $side,
            'type' => 'MARKET',
            'quantity' => $position_size
        ];
        
        return $this->bingxapi->create_spot_order($order_params);
    }
    
    /**
     * Calcula el tamaño de la posición según la configuración de la estrategia
     */
    private function calculate_position_size($strategy, $ticker) {
        // Obtener el balance disponible
        $balance = $this->bingxapi->get_account_balance();
        
        // Calcular el tamaño de la posición según la configuración de riesgo
        $position_size = $balance * ($strategy['risk_percentage'] / 100);
        
        // Obtener el precio actual del activo
        $ticker_price = $this->bingxapi->get_ticker_price($ticker);
        
        // Convertir el tamaño en dinero a unidades del activo
        $quantity = $position_size / $ticker_price;
        
        // Redondear según la precisión del activo
        $market_info = $this->bingxapi->get_market_info($ticker);
        $precision = $market_info['quantityPrecision'];
        
        return round($quantity, $precision);
    }
}