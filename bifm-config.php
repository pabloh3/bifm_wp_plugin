<?php
if ( ! defined( 'ABSPATH' ) ) exit;
$environment = 'production'; // Default to production
if(!isset( $_SERVER['HTTP_HOST'] )){
    wp_die(__('Could not get host information','bifm'));
}
$host = sanitize_text_field($_SERVER['HTTP_HOST']); // sanitizing as text since it's not used as a url
if (strpos($host, 'stg-') === 0 || 
    strpos($host, 'localhost') === 0 || 
    strpos($host, '127.0') === 0 || 
    strpos($host, 'staging') === 0 || 
    strpos($host, '.local') === strlen($host) - strlen('.local')) {
    $environment = 'staging';
}

$testing = false;
 

$BIFM_API_URL = $environment === 'staging' ? 'https://staging-wp.builditforme.ai/' : 'https://wp.builditforme.ai/';
$WIDGET_URL = $BIFM_API_URL . "widget-page/";