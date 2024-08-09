<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$environment = 'production'; // Default to production
$host = $_SERVER['HTTP_HOST'];
if (strpos($host, 'stg-') === 0 || 
    strpos($host, 'localhost') === 0 || 
    strpos($host, '127.0') === 0 || 
    strpos($host, '.local') === strlen($host) - strlen('.local')) {
    $environment = 'staging';
}

$testing = true;
 

$API_URL = $environment === 'staging' ? 'https://staging-wp.builditforme.ai/' : 'https://wp.builditforme.ai/';
    $WIDGET_URL = $API_URL . "widget-page/";