<?php

use App\Kernel;
use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;
use Symfony\Component\Dotenv\Dotenv;
use Symfony\Component\ErrorHandler\Debug;
use Symfony\Component\HttpFoundation\Request;
if (!empty($_GET['phpinfo'])) {
    phpinfo();
    var_dump(getenv());
    exit;
}

require dirname(__DIR__).'/vendor/autoload.php';

(new Dotenv())->bootEnv(dirname(__DIR__).'/.env');

if ($_SERVER['APP_DEBUG']) {
    umask(0000);
    Debug::enable();
}

$trustedProxies = $_SERVER['TRUSTED_PROXIES'] ?? $_ENV['TRUSTED_PROXIES'] ?? false;
$trustedProxies = $trustedProxies ? explode(',', $trustedProxies) : [];

$kernel = new Kernel($_SERVER['APP_ENV'], (bool)$_SERVER['APP_DEBUG']);
if ($_SERVER['APP_ENV'] === 'prod') {
    $trustedProxies[] = $_SERVER['REMOTE_ADDR'];
    $kernel = new HttpCache($kernel);
}

if($trustedProxies) {
    Request::setTrustedProxies($trustedProxies, Request::HEADER_X_FORWARDED_AWS_ELB);
}
if ($trustedHosts = $_SERVER['TRUSTED_HOSTS'] ?? false) {
    Request::setTrustedHosts([$trustedHosts]);
}

$request = Request::createFromGlobals();
$response = $kernel->handle($request);
$response->send();
$kernel->terminate($request, $response);
