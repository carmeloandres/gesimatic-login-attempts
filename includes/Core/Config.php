<?php

namespace GesimaticLoginAttempts\Core;

/**
 * Class Config.
 *  
 *  To store the configuration constants of the plugin.
 *  
 * @package Gesimatic
*/
class Config {

     /**
     * Option key in the database to store the blocked IPs data.
     * @var string
     * @since 1
     */
    public const OPTION_SETTINGS = 'gesimatic_login_attempts_settings';


     /**
     * Default settings value.
     * @var array
     * @since 1
     */
    public const DEFAULT_SETTINGS = [  
		'attempts' => 4,        // Set the fail access before start a lock period [1-100]
		'initialLock' => 20,    // Set the initial period of lock in minutes    [1-100]
		'multiplier' => 2,       // Set the multiplier to calculate the actual period of lock
        'logedInAlert' => true,  // Set the loged in alert in true
        'triggerRoles' => array('administrator') // set the role of alerts to administrators only
    ];

}