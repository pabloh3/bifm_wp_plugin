<?php
$environment = 'production'; // Default to production

if (strpos($_SERVER['HTTP_HOST'], 'stg-') === 0 || strpos($_SERVER['HTTP_HOST'], 'localhost') === 0 || strpos($_SERVER['HTTP_HOST'], '127.0') === 0) {
    $environment = 'staging';
}

// Define the API URL based on the environment
$API_URL = $environment === 'staging' ? 'https://staging-wp.builditforme.ai' : 'https://wp.builditforme.ai';

// Use $API_URL wherever needed in your script
