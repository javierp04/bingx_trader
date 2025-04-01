<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * BingxApi Library
 * Biblioteca para conectar con la API de BingX
 */
class BingxApi {
    
    // URLs de la API de BingX
    private $api_urls = [
        'sandbox' => [
            'futures' => 'https://open-api-sandbox.bingx.com',
            'spot' => 'https://open-api-sandbox.bingx.com'
        ],
        'production' => [
            'futures' => 'https://open-api.bingx.com',
            'spot' => 'https://open-api.bingx.com'
        ]
    ];
    
    // Credenciales y configuración
    private $api_key;
    private $api_secret;
    private $base_url;
    private $market_type;
    private $environment;
    
    // Instancia de CodeIgniter
    private $CI;
    
    public function __construct() {
        $this->CI =& get_instance();
        $this->CI->load->model('Config_model');
        $this->CI->load->model('Log_model');
    }
    
    /**
     * Inicializa la biblioteca con las credenciales adecuadas
     */
    public function initialize($environment, $market_type) {
        $this->environment = $environment;
        $this->market_type = $market_type;
        $this->base_url = $this->api_urls[$environment][$market_type];
        
        // Cargar las credenciales del ambiente seleccionado
        $credentials = $this->CI->Config_model->get_api_credentials($environment);
        
        if (!$credentials) {
            throw new Exception("No se encontraron credenciales para el entorno: {$environment}");
        }
        
        $this->api_key = $credentials['api_key'];
        $this->api_secret = $credentials['api_secret'];
    }
    
    /**
     * Genera la firma para las solicitudes a la API
     */
    private function generate_signature($params) {
        $query_string = http_build_query($params);
        return hash_hmac('sha256', $query_string, $this->api_secret);
    }
    
    /**
     * Realiza una solicitud a la API de BingX
     */
    private function request($method, $endpoint, $params = [], $is_signed = true) {
        // Añadir parámetros comunes
        $params['timestamp'] = round(microtime(true) * 1000);
        $params['recvWindow'] = 5000;
        
        if ($is_signed) {
            $params['signature'] = $this->generate_signature($params);
        }
        
        // Construir la URL
        $url = $this->base_url . $endpoint;
        
        // Configurar cURL
        $ch = curl_init();
        
        if ($method === 'GET') {
            $url .= '?' . http_build_query($params);
        } else {
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($params));
        }
        
        // Configurar opciones de cURL
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'X-BX-APIKEY: ' . $this->api_key,
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        
        // Ejecutar la solicitud
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        
        // Registrar la solicitud
        $this->CI->Log_model->add_api_request(
            $this->environment,
            $this->market_type,
            $method,
            $endpoint,
            json_encode($params),
            $response,
            $http_code
        );
        
        // Manejar errores de cURL
        if (curl_errno($ch)) {
            throw new Exception('Error de cURL: ' . curl_error($ch));
        }
        
        curl_close($ch);
        
        // Decodificar la respuesta
        $result = json_decode($response, true);
        
        // Verificar errores en la respuesta
        if (isset($result['code']) && $result['code'] !== 0) {
            throw new Exception('Error de API: ' . $result['msg'] . ' (Código: ' . $result['code'] . ')');
        }
        
        return $result;
    }
    
    /**
     * Obtiene el balance de la cuenta
     */
    public function get_account_balance() {
        if ($this->market_type === 'futures') {
            $response = $this->request('GET', '/openApi/swap/v2/user/balance');
            return $response['data']['balance']['total'];
        } else {
            $response = $this->request('GET', '/openApi/spot/v1/account/balance');
            // Encuentra el saldo de USDT
            foreach ($response['balances'] as $balance) {
                if ($balance['asset'] === 'USDT') {
                    return $balance['free'];
                }
            }
            return 0;
        }
    }
    
    /**
     * Configura el apalancamiento para un par en futuros
     */
    public function set_leverage($symbol, $leverage) {
        if ($this->market_type !== 'futures') {
            throw new Exception('Esta función solo está disponible para futuros');
        }
        
        $params = [
            'symbol' => $symbol,
            'leverage' => $leverage
        ];
        
        return $this->request('POST', '/openApi/swap/v2/trade/leverage', $params);
    }
    
    /**
     * Crea una orden en el mercado de futuros
     */
    public function create_futures_order($params) {
        if ($this->market_type !== 'futures') {
            throw new Exception('Esta función solo está disponible para futuros');
        }
        
        return $this->request('POST', '/openApi/swap/v2/trade/order', $params);
    }
    
    /**
     * Crea una orden en el mercado spot
     */
    public function create_spot_order($params) {
        if ($this->market_type !== 'spot') {
            throw new Exception('Esta función solo está disponible para spot');
        }
        
        return $this->request('POST', '/openApi/spot/v1/trade/order', $params);
    }
    
    /**
     * Obtiene el precio actual de un activo
     */
    public function get_ticker_price($symbol) {
        $endpoint = ($this->market_type === 'futures') ? 
            '/openApi/swap/v2/quote/price' : 
            '/openApi/spot/v1/ticker/price';
        
        $params = ['symbol' => $symbol];
        $response = $this->request('GET', $endpoint, $params, false);
        
        return $response['price'];
    }
    
    /**
     * Obtiene información del mercado para un símbolo
     */
    public function get_market_info($symbol) {
        $endpoint = ($this->market_type === 'futures') ? 
            '/openApi/swap/v2/quote/contracts' : 
            '/openApi/spot/v1/common/symbols';
        
        $response = $this->request('GET', $endpoint, [], false);
        
        // Buscar el símbolo en la respuesta
        if ($this->market_type === 'futures') {
            foreach ($response['data'] as $contract) {
                if ($contract['symbol'] === $symbol) {
                    return $contract;
                }
            }
        } else {
            foreach ($response['symbols'] as $market) {
                if ($market['symbol'] === $symbol) {
                    return $market;
                }
            }
        }
        
        throw new Exception('Símbolo no encontrado: ' . $symbol);
    }
    
    /**
     * Obtiene las posiciones abiertas en futuros
     */
    public function get_open_positions() {
        if ($this->market_type !== 'futures') {
            throw new Exception('Esta función solo está disponible para futuros');
        }
        
        $response = $this->request('GET', '/openApi/swap/v2/user/positions');
        return $response['data'];
    }
    
    /**
     * Obtiene las órdenes abiertas en spot
     */
    public function get_open_orders($symbol = null) {
        $params = [];
        if ($symbol) {
            $params['symbol'] = $symbol;
        }
        
        $endpoint = ($this->market_type === 'futures') ? 
            '/openApi/swap/v2/trade/openOrders' : 
            '/openApi/spot/v1/trade/openOrders';
        
        return $this->request('GET', $endpoint, $params);
    }
    
    /**
     * Cierra una posición en futuros
     */
    public function close_futures_position($symbol, $positionSide = 'BOTH') {
        if ($this->market_type !== 'futures') {
            throw new Exception('Esta función solo está disponible para futuros');
        }
        
        // Obtener la posición actual
        $positions = $this->get_open_positions();
        $position = null;
        
        foreach ($positions as $pos) {
            if ($pos['symbol'] === $symbol && $pos['positionSide'] === $positionSide) {
                $position = $pos;
                break;
            }
        }
        
        if (!$position) {
            throw new Exception('No se encontró posición abierta para: ' . $symbol);
        }
        
        // Crear orden opuesta para cerrar
        $side = ($position['positionSide'] === 'LONG') ? 'SELL' : 'BUY';
        $params = [
            'symbol' => $symbol,
            'side' => $side,
            'positionSide' => $positionSide,
            'type' => 'MARKET',
            'quantity' => abs($position['positionAmt']),
            'newOrderRespType' => 'RESULT'
        ];
        
        return $this->create_futures_order($params);
    }
    
    /**
     * Cancela todas las órdenes abiertas para un símbolo
     */
    public function cancel_all_orders($symbol) {
        $params = ['symbol' => $symbol];
        
        $endpoint = ($this->market_type === 'futures') ? 
            '/openApi/swap/v2/trade/allOpenOrders' : 
            '/openApi/spot/v1/trade/openOrders';
        
        return $this->request('DELETE', $endpoint, $params);
    }
}