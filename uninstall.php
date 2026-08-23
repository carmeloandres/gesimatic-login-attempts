<?php

/**
 * Gesimatic Login Attempts uninstall handler.
 *
 * @package GesimaticLoginAttempts
 */

defined('WP_UNINSTALL_PLUGIN') || exit;

$autoload_file = __DIR__ . '/vendor/autoload.php';

if (!is_readable($autoload_file)) {
    return;
}

require_once $autoload_file;

GesimaticLoginAttempts\Core\Setup::delete();
