<?php
require_once __DIR__ . '/app_config.php';

define('SMTP_HOST', appConfig('SMTP_HOST', 'smtp.gmail.com'));
define('SMTP_USER', appConfig('SMTP_USER', ''));
define('SMTP_PASS', appConfig('SMTP_PASS', ''));
define('SMTP_PORT', (int) appConfig('SMTP_PORT', 587));
define('SMTP_FROM', appConfig('SMTP_FROM', ''));
define('SMTP_FROM_NAME', appConfig('SMTP_FROM_NAME', 'LUMIÈRE'));
