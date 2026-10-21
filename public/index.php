<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/../storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
require __DIR__.'/../vendor/autoload.php';

// Bootstrap Laravel and handle the request...
/** @var Application $app */
$app = require_once __DIR__.'/../bootstrap/app.php';

class CustomRequest extends Request
{
    public function getBaseUrl(): string
    {
        $baseUrl = parent::getBaseUrl();
        if ($baseUrl === '') {
            $scriptDir = str_replace('\\', '/', dirname($this->server->get('SCRIPT_NAME', '')));
            if ($scriptDir !== '/' && $scriptDir !== '.') {
                return rtrim($scriptDir, '/');
            }
        }
        return $baseUrl;
    }

    protected function preparePathInfo(): string
    {
        $requestUri = $this->getRequestUri();
        if (false !== $pos = strpos($requestUri, '?')) {
            $requestUri = substr($requestUri, 0, $pos);
        }
        
        $baseUrl = $this->getBaseUrl();
        if ($baseUrl !== '' && str_starts_with($requestUri, $baseUrl)) {
            $pathInfo = substr($requestUri, strlen($baseUrl));
            if ($pathInfo === '' || $pathInfo[0] !== '/') {
                $pathInfo = '/'.$pathInfo;
            }
            return $pathInfo;
        }
        
        return parent::preparePathInfo();
    }
}

$app->handleRequest(CustomRequest::capture());

