<?php
/**
 * Dump all HTTP REQUESTs to disk along with uploaded files
 */

require_once '../vendor/autoload.php';

$app = new Slim\App([
    'displayErrorDetails'    => true,
    'addContentLengthHeader' => false,
]);

// Container settings
$container           = $app->getContainer();
$container['logger'] = function (\Psr\Container\ContainerInterface $c) {
    $logger       = new \Monolog\Logger('my_logger');
    $file_handler = new \Monolog\Handler\StreamHandler("../logs/app.log");
    $logger->pushHandler($file_handler);
    return $logger;
};

// Register settings
$container['settings_yml'] = function (\Psr\Container\ContainerInterface $c) {
    return new \davebarnwell\Model\SettingsModel();
};

// Register Twig View helper
$container['view'] = function (\Psr\Container\ContainerInterface $c) {
    $view    = new \Slim\Views\Twig(
        $c->get('settings_yml')->getDirectorySetting('viewTemplateDir'),
        [
            'cache' => $c->get('settings_yml')->getDirectorySetting('viewTemplateCacheDir')
        ]
    );

    // Instantiate and add Slim specific extension
    $basePath = rtrim(str_ireplace('index.php', '', $c['request']->getUri()->getBasePath()), '/');
    $view->addExtension(new Slim\Views\TwigExtension($c['router'], $basePath));

    return $view;
};

// Routes
$app->any('/', \davebarnwell\Controller\DumpRequestController::class . ':execute');
$app->any('/{parts:.+}', \davebarnwell\Controller\DumpRequestController::class . ':execute');

// Start App
$app->run();