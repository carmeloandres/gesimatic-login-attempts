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
     * Suffix used to build the table name that stores each IP login status.
     *
     * @var string
     * @since 1
     */
    public const TABLE_NAME_STATUS_IP = 'gesimatic_login_attempts_status_ip';

    /**
     * Option key in the database to store blocked IP data.
     *
     * @var string
     * @since 1
     */
    public const OPTION_BLOCKED_IPS = 'gesimatic_login_attempts_blocked_ips';

    /**
     * Transient key used to synchronize access to blocked IP data.
     *
     * @var string
     * @since 1
     */
    public const SPOTLIGHT_QUERING_BLOCKED_IPS = 'gesimatic_login_attempts_quering_blocked_ips_spotlight';

    /**
     * Valid status filters used by the API requests.
     *
     * @var array
     * @since 1
     */
    public const VALID_FILTERS = ['', 'enabled', 'disabled'];

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

    /**
     * Number of items per page in a paged query.
     *
     * @var int
     * @since 1
     */
    public const PER_PAGE = 15;

}