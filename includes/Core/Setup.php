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
     * Number of items per page in a paged query.
     *
     * @var string
     * @since 1
     */
    public static int $per_page = 15;

    
    /**
     * Initialize plugin components.
     *
     * @return void
     */
    public static function activate($blog_id): void {
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
        
        $query = "DROP TABLE " . $wpdb->prefix . Config::TABLE_NAME_STATUS_IP;
        
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

        $mysql_query = "CREATE TABLE IF NOT EXISTS " . $wpdb->prefix . Config::TABLE_NAME_STATUS_IP . " (
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