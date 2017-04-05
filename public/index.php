<?php
/**
 * Dump all HTTP REQUESTs
 */

require_once '../vendor/autoload.php';

$settings = new \davebarnwell\Model\SettingsModel();
$dumper   = new \davebarnwell\Controller\DumpRequestController();
$dumper->execute(
    $settings->getDirectorySetting('storeRequests') . '/' . date('Y-m-d-H-i-s') . uniqid('-request-') . '.txt'
);
