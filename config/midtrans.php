<?php
return [
    // Sandbox credentials (replace with your own keys from Midtrans dashboard)
    'server_key' => env('MIDTRANS_SERVER_KEY'), // ex: 'SB-Mid-server-xxxxxxxxxxxx'
    'client_key' => env('MIDTRANS_CLIENT_KEY'), // ex: 'SB-Mid-client-xxxxxxxxxxxx'
    'is_production' => false, // false = sandbox, true = production
    'is_sanitized' => true,
    'is_3ds' => true,
];
?>
