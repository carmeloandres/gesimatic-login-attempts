<?php

namespace GesimaticLoginAttempts\Core;

use GesimaticLoginAttempts\Core\Config;


/**
 * Class Setup.
 * 
 * @package GesimaticLoginAttempts\Core.
*/
class Setup {

     /**
     * Option key in the database to store the blocked IPs data.
     * @var string
     * @since 1
     */
//    protected const OPTION_SETTINGS = 'gesimatic_login_attempts_settings';

     /**
     * Default settings value.
     * @var array
     * @since 1
     */
/*    protected const DEFAULT_SETTINGS = [  
		'attempts' => 4,        // Set the fail access before start a lock period [1-100]
		'initialLock' => 20,    // Set the initial period of lock in minutes    [1-100]
		'multiplier' => 2,       // Set the multiplier to calculate the actual period of lock
        'logedInAlert' => true,  // Set the loged in alert in true
        'triggerRoles' => array('administrator') // set the role of alerts to administrators only
    ];
*/


    /**
     * Number of items per page in a paged query.
     *
     * @var string
     * @since 1
     */
    public static int $per_page = 15;

    /**
     * Table name of table to store the login ip status.
     *
     * @var string
     */
    public static string $table_name_status_ip = '';

    /**
     * Constructor method.
     *
     * @return void
     */
    public function __construct(){
        self::init_static();
    }

    /**
     * Initialize static attributes.
     *
     * @return void
     */
    protected static function init_static(): void {
        if (self::$table_name_status_ip === ''){
            global $wpdb;
            self::$table_name_status_ip = $wpdb->prefix . Config::TABLE_NAME_STATUS_IP;
        }
    }

    
    /**
     * Initialize plugin components.
     *
     * @return void
     */
    public static function activate($blog_id): void {
        self::init_static();
        self::create_table_status_ip();
        self::create_option_settings();

        // create the blocked_ips option
//		update_option(OPTION_BLOCKED_IPS,array());

     }
    
    /**
     * Initialize plugin components.
     *
     * @return void
     */
    public static function delete(): void {
        
       global $wpdb;
        
        $query = "DROP TABLE ".self::$table_name_status_ip;
        
        $wpdb->query($query);

        delete_option(Config::OPTION_SETTINGS);

        delete_option(Config::OPTION_BLOCKED_IPS);
     }


     /**
     * Creation the table login status ip
     * 
     * This method creates the table to store the login status information.
     * 
     */
    private static function create_table_status_ip(): void{
        
        global $wpdb;

        // Charset and Collate (to compatibility)
        $charset_collate = $wpdb->get_charset_collate();

        $mysql_query = "CREATE TABLE IF NOT EXISTS ".self::$table_name_status_ip." (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            userLogin varchar(255),  
            ip varchar(40),
            attempts bigint(20) unsigned,
            currentPeriod bigint(20) unsigned,
            lockUntil bigint(20) unsigned,
            lastAttempt bigint(20) unsigned,
            status varchar(10),

         PRIMARY KEY (id)
        ) ".$charset_collate;

        // Include the necessary file to use dbDelta
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Execute dbDelta
        dbDelta($mysql_query);       

    }

    /**
     * Creation of the gsmtc_access_settings
     * 
     * This method creates the wp option 'gsmtc_access_settings' to store the ips that are currently blocked.
     * 
     */
    private static function create_option_settings(): void{    

        // Check if the option already exists, if not create it with default settings
        $settings = get_option(Config::OPTION_SETTINGS);
        if ($settings === false)
            update_option(Config::OPTION_SETTINGS, Config::DEFAULT_SETTINGS, true);
    }



}