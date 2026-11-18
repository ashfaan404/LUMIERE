<?php
/**
 * Get application configuration from server environment variables or an
 * untracked local PHP configuration file (see config.local.example.php).
 */
function appConfig($key, $default = '')
{
    $env = getenv($key);
    if ($env !== false && $env !== '') {
        return $env;
    }

    static $local = null;
    if ($local === null) {
        $path = __DIR__ . '/config.local.php';
        $loaded = is_file($path) ? require $path : [];
        $local = is_array($loaded) ? $loaded : [];
    }
    return array_key_exists($key, $local) ? $local[$key] : $default;
}
