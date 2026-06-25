<?php

namespace GesimaticLoginAttempts\Core;


/**
 * Class Setup.
 * 
 * @package GesimaticLoginAttempts\Core.
*/
class Setup {

    /**
     * Part of the table name used to store the states of each IP address 
     * would be the WordPress prefix to complete it
     * @var string
     * @since 1
     */
    protected const TABLE_NAME_STATUS_IP = 'gesimatic_login_attempts_status_ip';

     /**
     * Option key in the database to store the bloqued IPs data.
     * @var string
     * @since 1
     */
    protected const OPTION_BLOCKED_IPS = 'gesimatic_login_attempts_bloqued_ips';
   
     /**
     * Option key in the database to store the bloqued IPs data.
     * @var string
     * @since 1
     */
    protected const OPTION_SETTINGS = 'gesimatic_login_attempts_settings';

     /**
     * Default settings value.
     * @var array
     * @since 1
     */
    protected const DEFAULT_SETTINGS = [  
		'attempts' => 4,        // Set the fail access before start a lock period [1-100]
		'initialLock' => 20,    // Set the initial period of lock in minutes    [1-100]
		'multiplier' => 2,       // Set the multiplier to calculate the actual period of lock
        'logedInAlert' => true,  // Set the loged in alert in true
        'triggerRoles' => array('administrator') // set the role of alerts to administrators only
    ];

     /**
     * Spotlight to use when quering option blocked ips
     * @var string
     * @since 1
     */
    protected const SPOTLIGHT_QUERING_BLOCKED_IPS = 'gesimatic_login_attempts_quering_bloqued_ips_spotlight';

    /**
     * Order valid values. Used in Api request validations
     * @var array
     * @since 1
     */
    protected const VALID_FILTERS = ['','enabled','disabled'];


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
            self::$table_name_status_ip = $wpdb->prefix.self::TABLE_NAME_STATUS_IP;
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

        // create the bloqued_ips option
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

        delete_option(self::OPTION_SETTINGS);

        delete_option(self::OPTION_BLOCKED_IPS); 
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
     * This method creates the wp option 'gsmtc_access_settings' to store the ips that are currently bloqued.
     * 
     */
    private static function create_option_settings(): void{    

        $option = array( 	'enabled' => true,      // Enables the access limit attempts funcionality 
							'attempts' => 4,        // Set the fail access before start a lock period [1-100]
							'initialLock' => 20,    // Set the initial period of lock in minutes    [1-100]
							'multiplier' => 2,       // Set the multiplier to calculate the actual period of lock
                            'logedInAlert' => true,  // Set the loged in alert in true
                            'triggerRoles' => array('administrator') // set the role of alerts to administrators only
                          );    

        update_option(self::OPTION_SETTINGS, $option, true);
    }


   /**
     * Method unlock_ip
     * 
     * This method update the status of a bloqued Ip to unlock.
     * 
     * This method is used by both children classes
     * 
     * @params ip The ip to unlock
     * @return boolean 
     */
    public static function unlock_ip($ip){
        global $wpdb;

        // spotlight to checks if the are queries of the option in process
        $lock_time = 30; // Maximun time of bloqued in seconds
        $retry_delay = 1; // Seconds to retry the query

        // wait until the transient expires or is deleted
        while (get_transient(self::SPOTLIGHT_QUERING_BLOCKED_IPS) == 'true'){
            sleep($retry_delay);
        }

        // set the spotlight to disabling the access to the option
        set_transient(self::SPOTLIGHT_QUERING_BLOCKED_IPS,'true',$lock_time);

        $status = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . self::$table_name_status_ip . " WHERE ip = %s", $ip), ARRAY_A);
        $now = time();
        $result = false;

        if (($status != null) && (intval($status['lockUntil']) < $now)){
            $status['lockUntil'] = 0;
            $status['status'] = 'enabled';

            $update = $wpdb->update(self::$table_name_status_ip,$status,array('id' => $status['id']));
            if (false !== $update)
                $result = true;
        } else {
            $result = false;
        }

        // delete the spotlight to enabling the access to the option
        delete_transient(self::SPOTLIGHT_QUERING_BLOCKED_IPS);

        return $result;
    }

    /**
     * Method reload_bloqued_ips
     * 
     * This method updates the option gsmtc_bloqued_ips from table login_status_ip and check if all bloqued ips are still blocked.
     * 
     * @params void
     * @return void
     */
    public static function reload_blocked_ips(){
        global $wpdb;

        // spotlight to checks if the are queries of the option in process
        $lock_time = 30; // Maximun time of bloqued in seconds
        $retry_delay = 1; // Seconds to retry the query

        // wait until the transient expires or is deleted
        while (get_transient(self::SPOTLIGHT_QUERING_BLOCKED_IPS) == 'true'){
            sleep($retry_delay);
        }

        // set the spotlight to disabling the access to the option
        set_transient(self::SPOTLIGHT_QUERING_BLOCKED_IPS,'true',$lock_time);

        $query = "SELECT * FROM ".self::$table_name_status_ip." WHERE status = 'bloqued'";
        $results = $wpdb->get_results($query,ARRAY_A);
        $bloquedIps = array();
        $now = time();
        foreach($results as $result){
            if (intval($result['lockUntil']) > $now){
                $bloquedIp = array(
                    'ip' => $result['ip'],
                    'until' => $result['lockUntil']
                );
                $bloquedIps[] = $bloquedIp;
            } else {
                $result['lockUntil'] = 0;
                $result['status'] = 'enabled';
                $wpdb->update(self::$table_name_status_ip,$result,array('id' => $result['id']));
            }
        }
        // create the gsmtc_bloqued_ips
		update_option(self::OPTION_BLOCKED_IPS,$bloquedIps);

        // delete the spotlight to enabling the access to the option
        delete_transient(self::SPOTLIGHT_QUERING_BLOCKED_IPS);
    
    }

}