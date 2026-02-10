<?php

declare(strict_types=1);

// CORS must be handled FIRST, before anything else
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, PATCH, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Access-Control-Max-Age: 86400');

// Handle preflight immediately
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

// Now load everything else
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;
use App\Controllers\AuthController;
use App\Controllers\CategoryController;
use App\Controllers\ExpenseController;
use App\Controllers\RecurringExpenseController;
use App\Controllers\ForecastController;
use App\Controllers\MonthStateController;
use App\Controllers\DashboardController;
use App\Controllers\VendorMappingController;
use App\Controllers\ImportController;
use App\Utils\Response;

// Error handling
set_error_handler(function ($severity, $message, $file, $line) {
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (Throwable $e) {
    error_log("Uncaught exception: " . $e->getMessage() . "\n" . $e->getTraceAsString());

    if (!headers_sent()) {
        header('Content-Type: application/json; charset=utf-8');
    }

    $debug = ($_ENV['DEBUG'] ?? getenv('DEBUG') ?: 'false') === 'true';

    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $debug ? $e->getMessage() : 'Une erreur est survenue',
        'trace' => $debug ? $e->getTraceAsString() : null
    ]);
    exit;
});

// Load environment variables
try {
    $dotenv = Dotenv::createImmutable(__DIR__ . '/..');
    $dotenv->load();
} catch (Exception $e) {
    // In Docker, env vars come from docker-compose, .env file may not exist
}

// Parse request
$method = $_SERVER['REQUEST_METHOD'];
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Remove base path if needed
$basePath = '/api';
if (strpos($uri, $basePath) === 0) {
    $uri = substr($uri, strlen($basePath));
}

// Remove trailing slash
$uri = rtrim($uri, '/') ?: '/';

// Router
$routes = [
    // ============================================
    // AUTH ROUTES (Magic Link)
    // ============================================
    'POST /auth/request-magic-link' => ['controller' => AuthController::class, 'method' => 'requestMagicLink'],
    'GET /auth/verify/([a-f0-9]+)' => ['controller' => AuthController::class, 'method' => 'verifyMagicLink'],
    'POST /auth/refresh' => ['controller' => AuthController::class, 'method' => 'refresh'],
    'POST /auth/logout' => ['controller' => AuthController::class, 'method' => 'logout'],
    'GET /auth/me' => ['controller' => AuthController::class, 'method' => 'me'],

    // ============================================
    // DASHBOARD ROUTES
    // ============================================
    'GET /dashboard' => ['controller' => DashboardController::class, 'method' => 'index'],
    'GET /dashboard/year' => ['controller' => DashboardController::class, 'method' => 'year'],
    'GET /dashboard/daily' => ['controller' => DashboardController::class, 'method' => 'daily'],
    'GET /dashboard/health' => ['controller' => DashboardController::class, 'method' => 'health'],

    // ============================================
    // CATEGORY ROUTES
    // ============================================
    'GET /categories' => ['controller' => CategoryController::class, 'method' => 'index'],
    'GET /categories/([a-f0-9-]+)' => ['controller' => CategoryController::class, 'method' => 'show'],
    'POST /categories' => ['controller' => CategoryController::class, 'method' => 'store'],
    'PUT /categories/([a-f0-9-]+)' => ['controller' => CategoryController::class, 'method' => 'update'],
    'DELETE /categories/([a-f0-9-]+)' => ['controller' => CategoryController::class, 'method' => 'destroy'],

    // ============================================
    // EXPENSE ROUTES
    // ============================================
    'GET /expenses' => ['controller' => ExpenseController::class, 'method' => 'index'],
    'GET /expenses/by-category' => ['controller' => ExpenseController::class, 'method' => 'byCategory'],
    'GET /expenses/monthly-totals' => ['controller' => ExpenseController::class, 'method' => 'monthlyTotals'],
    'GET /expenses/([a-f0-9-]+)' => ['controller' => ExpenseController::class, 'method' => 'show'],
    'POST /expenses' => ['controller' => ExpenseController::class, 'method' => 'store'],
    'PUT /expenses/([a-f0-9-]+)' => ['controller' => ExpenseController::class, 'method' => 'update'],
    'DELETE /expenses/([a-f0-9-]+)' => ['controller' => ExpenseController::class, 'method' => 'destroy'],

    // ============================================
    // RECURRING EXPENSE ROUTES
    // ============================================
    'GET /recurring-expenses' => ['controller' => RecurringExpenseController::class, 'method' => 'index'],
    'GET /recurring-expenses/monthly-total' => ['controller' => RecurringExpenseController::class, 'method' => 'getMonthlyTotal'],
    'GET /recurring-expenses/([a-f0-9-]+)' => ['controller' => RecurringExpenseController::class, 'method' => 'show'],
    'POST /recurring-expenses' => ['controller' => RecurringExpenseController::class, 'method' => 'store'],
    'POST /recurring-expenses/generate' => ['controller' => RecurringExpenseController::class, 'method' => 'generate'],
    'POST /recurring-expenses/generate-year' => ['controller' => RecurringExpenseController::class, 'method' => 'generateYear'],
    'PUT /recurring-expenses/([a-f0-9-]+)' => ['controller' => RecurringExpenseController::class, 'method' => 'update'],
    'DELETE /recurring-expenses/([a-f0-9-]+)' => ['controller' => RecurringExpenseController::class, 'method' => 'destroy'],

    // ============================================
    // FORECAST ROUTES
    // ============================================
    'GET /forecasts' => ['controller' => ForecastController::class, 'method' => 'index'],
    'GET /forecasts/annual-total' => ['controller' => ForecastController::class, 'method' => 'getAnnualTotal'],
    'GET /forecasts/(\d+)/(\d+)' => ['controller' => ForecastController::class, 'method' => 'show'],
    'POST /forecasts' => ['controller' => ForecastController::class, 'method' => 'store'],
    'PUT /forecasts/(\d+)/(\d+)' => ['controller' => ForecastController::class, 'method' => 'update'],

    // ============================================
    // MONTH STATE ROUTES
    // ============================================
    'GET /month-states' => ['controller' => MonthStateController::class, 'method' => 'index'],
    'GET /month-states/(\d+)/(\d+)' => ['controller' => MonthStateController::class, 'method' => 'show'],
    'POST /month-states/(\d+)/(\d+)/actual' => ['controller' => MonthStateController::class, 'method' => 'setActual'],
    'POST /month-states/(\d+)/(\d+)/estimated' => ['controller' => MonthStateController::class, 'method' => 'setEstimated'],
    'POST /month-states/(\d+)/(\d+)/clear' => ['controller' => MonthStateController::class, 'method' => 'clearMonth'],

    // ============================================
    // VENDOR MAPPING ROUTES
    // ============================================
    'GET /vendor-mappings' => ['controller' => VendorMappingController::class, 'method' => 'index'],
    'GET /vendor-mappings/suggest' => ['controller' => VendorMappingController::class, 'method' => 'suggest'],
    'GET /vendor-mappings/search' => ['controller' => VendorMappingController::class, 'method' => 'search'],
    'GET /vendor-mappings/([a-f0-9-]+)' => ['controller' => VendorMappingController::class, 'method' => 'show'],
    'POST /vendor-mappings' => ['controller' => VendorMappingController::class, 'method' => 'store'],
    'PUT /vendor-mappings/([a-f0-9-]+)' => ['controller' => VendorMappingController::class, 'method' => 'update'],
    'DELETE /vendor-mappings/([a-f0-9-]+)' => ['controller' => VendorMappingController::class, 'method' => 'destroy'],

    // ============================================
    // IMPORT ROUTES
    // ============================================
    'GET /imports' => ['controller' => ImportController::class, 'method' => 'history'],
    'POST /imports/preview' => ['controller' => ImportController::class, 'method' => 'preview'],
    'POST /imports' => ['controller' => ImportController::class, 'method' => 'import'],

    // ============================================
    // HEALTH CHECK
    // ============================================
    'GET /' => ['handler' => fn() => Response::success(['status' => 'ok', 'version' => '1.0.0'], 'OPS API')],
    'GET /health' => ['handler' => fn() => Response::success(['status' => 'ok'], 'Service opérationnel')],
];

// Find matching route
$matched = false;

foreach ($routes as $route => $config) {
    list($routeMethod, $routePath) = explode(' ', $route, 2);

    if ($method !== $routeMethod) {
        continue;
    }

    // Convert route to regex
    $pattern = '#^' . $routePath . '$#';

    if (preg_match($pattern, $uri, $matches)) {
        $matched = true;

        // Execute handler or controller
        if (isset($config['handler'])) {
            $config['handler']();
        } else {
            $controller = new $config['controller']();
            $methodName = $config['method'];

            // Extract route parameters
            array_shift($matches); // Remove full match

            call_user_func_array([$controller, $methodName], $matches);
        }

        break;
    }
}

if (!$matched) {
    Response::notFound('Route non trouvée');
}
