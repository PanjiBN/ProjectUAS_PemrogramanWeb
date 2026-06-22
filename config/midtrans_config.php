<?php
// MIDTRANS API KEYS (SANDBOX)
define('MIDTRANS_SERVER_KEY', 'Mid-server-ejI4Xu3TKPxIDZQ0bJh9Ymj-');
define('MIDTRANS_CLIENT_KEY', 'Mid-client-nEKeXodC6Z_4C3p8');

// MODE: false = Sandbox, true = Production
define('MIDTRANS_IS_PRODUCTION', false);

// URL (otomatis berdasarkan mode)
define('MIDTRANS_SNAP_URL', MIDTRANS_IS_PRODUCTION 
    ? 'https://app.midtrans.com/snap/snap.js' 
    : 'https://app.sandbox.midtrans.com/snap/snap.js'
);

define('MIDTRANS_API_URL', MIDTRANS_IS_PRODUCTION 
    ? 'https://app.midtrans.com/snap/v1/transactions' 
    : 'https://app.sandbox.midtrans.com/snap/v1/transactions'
);
?>
