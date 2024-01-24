<?php
$environment = 'production'; // Default to production
$host = $_SERVER['HTTP_HOST'];
if (strpos($host, 'stg-') === 0 || 
    strpos($host, 'localhost') === 0 || 
    strpos($host, '127.0') === 0 || 
    strpos($host, '.local') === strlen($host) - strlen('.local')) {
    $environment = 'staging';
}

// Define the API URL based on the environment
$API_URL = $environment === 'staging' ? 'https://staging-wp.builditforme.ai/' : 'https://wp.builditforme.ai/';
//$API_URL = 'http://localhost:5001';

// Use $API_URL wherever needed in your script
