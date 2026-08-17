<?php
/**
* @package gesimatic-login-attempts
*/

/**
 *  Sentence to ensure it is procesed as a wordpress request
*/
if(! defined('WP_UNINSTALL_PLUGIN')){exit;}

require_once __DIR__ . '/includes/Core/Setup.php';

\GesimaticServer\Core\Setup::delete();