# Sistema de Trading Automático - BingX & TradingView

Un sistema completo para ejecutar operaciones automáticas en BingX basadas en señales de TradingView mediante webhooks.

## Características

- Integración con BingX a través de su API REST
- Recepción de señales de TradingView mediante webhooks
- Soporte para operaciones en spot y futuros perpetuos
- Panel de control en tiempo real para monitorear posiciones
- Gestión múltiple de estrategias
- Operación en entornos de sandbox y producción
- Estadísticas de rendimiento y reportes

## Requisitos del Sistema

- PHP 7.3+
- MySQL 5.7+
- Servidor web con soporte para PHP (Apache, Nginx)
- Extensiones PHP: curl, json, mbstring, mysqli, openssl
- Cuenta en BingX (Sandbox y/o Producción)
- TradingView con soporte para alertas webhook

## Instalación

### 1. Preparación del Servidor

```bash
# Clonar el repositorio
git clone https://github.com/tu-usuario/trading-bot.git
cd trading-bot

# Configurar permisos
chmod 755 -R ./
chmod 777 -R ./application/logs/
chmod 777 -R ./application/cache/
chmod 777 -R ./assets/temp/
```

### 2. Configuración de la Base de Datos

1. Crear una base de datos MySQL
2. Importar el esquema desde `application/sql/schema.sql`
3. Configurar la conexión en `application/config/database.php`

```php
$db['default'] = array(
    'dsn'   => '',
    'hostname' => 'localhost',
    'username' => 'tu_usuario',
    'password' => 'tu_contraseña',
    'database' => 'trading_bot',
    'dbdriver' => 'mysqli',
    'dbprefix' => '',
    'pconnect' => FALSE,
    'db_debug' => TRUE,
    'cache_on' => FALSE,
    'cachedir' => '',
    'char_set' => 'utf8mb4',
    'dbcollat' => 'utf8mb4_general_ci',
    'swap_pre' => '',
    'encrypt' => FALSE,
    'compress' => FALSE,
    'stricton' => FALSE,
    'failover' => array(),
    'save_queries' => TRUE
);
```

### 3. Configuración del Sistema

1. Modificar `application/config/config.php` con la URL base de tu instalación:

```php
$config['base_url'] = 'https://tu-dominio.com/';
```

2. Configurar una clave de encriptación segura:

```php
$config['encryption_key'] = 'tu_clave_segura_aleatoria';
```

3. Crear usuario administrador:

```sql
INSERT INTO users (username, password, email, role, is_active, created_at) 
VALUES ('admin', '$2y$10$yDIAZbBtMHsUxnNMdjmPqetrPKwGX.LU3K7XV7G.UWOj6Wb7/IKOO', 'admin@example.com', 'admin', 1, NOW());
```
La contraseña predeterminada es `admin123` - ¡Cámbiala después del primer inicio de sesión!

### 4. Configuración del Webhook en TradingView

1. En TradingView, crea una alerta con la siguiente URL de webhook:
   ```
   https://tu-dominio.com/webhook/receive
   ```

2. Configura el payload JSON de la alerta con este formato:
   ```json
   {
       "strategyId": "NOMBRE_ESTRATEGIA",
       "ticker": "{{ticker}}",
       "timeframe": "{{interval}}",
       "action": "{{strategy.order.action}}",
       "price": {{close}},
       "leverage": 5,
       "positionSize": 0.1
   }
   ```

### 5. Configuración de la API de BingX

1. Inicia sesión en el sistema
2. Ve a "Configuración" -> "API Keys"
3. Agrega las API Keys tanto para el entorno sandbox como para producción

## Estructura de Archivos

```
project_root/
├── application/                      # Carpeta principal de CodeIgniter
│   ├── config/                       # Configuraciones
│   ├── controllers/                  # Controladores
│   │   ├── Auth.php                  # Autenticación
│   │   ├── Dashboard.php             # Panel principal
│   │   ├── Dashboard.php   # Funcionalidades del dashboard
│   │   ├── Orders.php                # Gestión de órdenes
│   │   ├── Positions.php             # Gestión de posiciones
│   │   ├── WebhookController.php     # Recepción de webhooks
│   │   └── ...
│   ├── libraries/                    # Bibliotecas
│   │   └── BingxApi.php              # Biblioteca para API de BingX
│   ├── models/                       # Modelos
│   │   ├── Trading_model.php         # Operaciones de trading
│   │   ├── Position_model.php        # Gestión de posiciones
│   │   ├── Strategy_model.php        # Gestión de estrategias
│   │   ├── Config_model.php          # Configuración
│   │   ├── User_model.php            # Usuarios
│   │   └── Log_model.php             # Logs del sistema
│   ├── helpers/                      # Helpers
│   │   └── trading_helper.php        # Funciones auxiliares
│   ├── views/                        # Vistas
│   │   ├── templates/                # Plantillas
│   │   ├── dashboard/                # Vistas del dashboard
│   │   ├── positions/                # Vistas de posiciones
│   │   ├── orders/                   # Vistas de órdenes
│   │   └── auth/                     # Vistas de autenticación
│   └── sql/                          # Scripts SQL
├── assets/                           # Recursos estáticos
│   ├── css/                          # Hojas de estilo
│   ├── js/                           # JavaScript
│   └── img/                          # Imágenes
├── system/                           # Core de CodeIgniter
├── .htaccess                         # Configuración de Apache
└── index.php                         # Punto de entrada
```

## Uso del Sistema

### 1. Ingreso al Sistema

1. Accede a `https://tu-dominio.com/`
2. Inicia sesión con las credenciales configuradas

### 2. Configuración Inicial

1. Configura las API Keys de BingX
2. Crea estrategias y configura parámetros
3. Selecciona el entorno de operación (sandbox/producción)

### 3. Monitoreo

- El panel principal muestra posiciones abiertas
- Los precios y PNL se actualizan automáticamente
- Puedes cerrar posiciones manualmente

## Seguridad

- **Protección de API Keys**: Las claves se almacenan encriptadas
- **CSRF Protection**: Protección contra ataques Cross-Site Request Forgery
- **XSS Prevention**: Sanitización de entradas para prevenir Cross-Site Scripting
- **IP Restriction**: Considera configurar restricciones de IP para el webhook

## Mantenimiento

- Revisa regularmente los logs en `application/logs/`
- Realiza copias de seguridad de la base de datos
- Mantén actualizadas las dependencias

## Licencia

Este proyecto está licenciado bajo la Licencia MIT - ver el archivo LICENSE para más detalles.

## Soporte

Para soporte, contacta a [tu-email@ejemplo.com](mailto:tu-email@ejemplo.com)