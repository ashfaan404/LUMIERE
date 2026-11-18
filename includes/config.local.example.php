<?php
// Copy to includes/config.local.php and set YOUR OWN values.
// Never commit includes/config.local.php to a public repository.
return [
    'DB_HOST' => 'localhost',
    'DB_USER' => 'root',
    'DB_PASS' => '',
    'DB_NAME' => 'perfume_shop',
    'APP_BASE_URL' => 'http://localhost/LUMIERE',
    'STRIPE_SECRET_KEY' => '', // Use a NEW test secret from your own Stripe account
    'STRIPE_PUBLISHABLE_KEY' => '', // Corresponding NEW test publishable key
    'SMTP_HOST' => 'smtp.gmail.com',
    'SMTP_USER' => '',
    'SMTP_PASS' => '', // Use a NEW application-specific password
    'SMTP_PORT' => 587,
    'SMTP_FROM' => '',
    'SMTP_FROM_NAME' => 'LUMIÈRE',
];
