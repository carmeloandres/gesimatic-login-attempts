<?php

namespace GesimaticLoginAttempts\Core;

use Gesimatic\Core\OptionManager;
use GesimaticLoginAttempts\Core\Config;
use GesimaticLoginAttempts\Repositories\LoginAttemptsRepository;


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
     * @param bool $network_wide Whether the plugin is activated for the network.
     * @return void
     */
    public static function activate($network_wide): void
    {
        self::create_table_status_ip();
        self::create_option_settings();
    }
    
    /**
     * Initialize plugin components.
     *
     * @return void
     */
    public static function delete(): void {
        
       global $wpdb;
        
        $query = "DROP TABLE " . LoginAttemptsRepository::table_name();
        
        $wpdb->query($query);

        OptionManager::delete(Config::OPTION_SETTINGS);

     }


     /**
     * Creation the table login status ip
     * 
     * This method creates the table to store the login status information.
     * 
     */
    private static function create_table_status_ip(): void
    {
        
        global $wpdb;

        // Charset and Collate (to compatibility)
        $charset_collate = $wpdb->get_charset_collate();

        $mysql_query = "CREATE TABLE IF NOT EXISTS " . LoginAttemptsRepository::table_name() . " (
            id bigint(20) unsigned NOT NULL AUTO_INCREMENT,
            userLogin varchar(255),  
            ip varchar(45) NOT NULL,
            attempts bigint(20) unsigned,
            currentPeriod bigint(20) unsigned,
            lockUntil bigint(20) unsigned,
            lastAttempt bigint(20) unsigned,
            status varchar(10),

            PRIMARY KEY  (id),
            UNIQUE KEY ip (ip)
        ) ENGINE=InnoDB " . $charset_collate;

        // Include the necessary file to use dbDelta
        require_once ABSPATH . 'wp-admin/includes/upgrade.php';

        // Execute dbDelta
        dbDelta($mysql_query);

    }

    /**
     * Creation of the login attempts settings
     * 
     * This method creates the shared limiter settings when they do not exist.
     * 
     */
    private static function create_option_settings(): void{    

        // Check if the option already exists, if not create it with default settings
        $settings = OptionManager::get(Config::OPTION_SETTINGS);
        if ($settings === false)
            OptionManager::update(Config::OPTION_SETTINGS, Config::DEFAULT_SETTINGS);
    }



}