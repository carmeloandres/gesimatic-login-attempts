<?php

namespace GesimaticLoginAttempta\Core;


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
    protected const OPTION_BLOQUED_IPS = 'gesimatic_login_attempts_bloqued_ips';
   
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
    public static string $table_name_login_status_ip = '';

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
        if (self::$table_name_login_status_ip === ''){
            global $wpdb;
            self::$table_name_login_status_ip = $wpdb->prefix.self::TABLE_NAME_STATUS_IP;
        }
    }

    
    /**
     * Initialize plugin components.
     *
     * @return void
     */
    public static function activate($blog_id): void {
        self::init_static();
        self::create_table_login_status_ip();
        self::create_option_gsmtc_access_settings();
        $main_blog_id = get_main_site_id();
        // If it's not the main site
        if (function_exists('is_multisite') && is_multisite() && $main_blog_id !== $blog_id){
            // copiaremos la opción 'gsmtc_api_token' por si ya se ha registrado el multisite
            // read the main site option
            $option = get_blog_option($main_blog_id,'gsmtc_api_token',array());
            // write the main site options in the current site
		    update_option(OPTION_BLOQUED_IPS,$option);
        } else {
		    update_option(OPTION_BLOQUED_IPS,array());
        }

        // create the bloqued_ips option
		update_option(OPTION_BLOQUED_IPS,array());

        // create the gsmtc_registered_plugins
		update_option('gsmtc_registered_plugins',array());
     }
    
    /**
     * Initialize plugin components.
     *
     * @return void
     */
    public static function delete(): void {
        
       global $wpdb;
        
        $query = "DROP TABLE ".self::$table_name_login_status_ip;
        
        $wpdb->query($query);

        delete_option('gsmtc_access_settings');

        delete_option('gsmtc_bloqued_ips');

        delete_option('gsmtc_api_token');

		delete_option('gsmtc_registered_plugins');
 
     }


     /**
     * Creation the table login status ip
     * 
     * This method creates the table 'gsmtc_login_status_ip' to store the login status information.
     * 
     */
    private static function create_table_login_status_ip(): void{
        
        global $wpdb;

        // Charset and Collate (to compatibility)
        $charset_collate = $wpdb->get_charset_collate();

        $mysql_query = "CREATE TABLE IF NOT EXISTS ".self::$table_name_login_status_ip." (
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
    private static function create_option_gsmtc_access_settings(): void{    

        $option = array( 	'enabled' => true,      // Enables the access limit attempts funcionality 
							'attempts' => 4,        // Set the fail access before start a lock period [1-100]
							'initialLock' => 20,    // Set the initial period of lock in minutes    [1-100]
							'multiplier' => 2,       // Set the multiplier to calculate the actual period of lock
                            'logedInAlert' => true,  // Set the loged in alert in true
                            'blocksEnumerationAccess' => true, // Set to true, blocks the enumeration accesses to  ?author or /author/username
                            'hideUsersEndpoints' => true, // Set to true hide the access to users endpoint throught the REST API
                            'disablePostAuthorInfo' => true, // Set to true disables the author info in posts 
                            'triggerRoles' => array('administrator') // set the role of alerts to administrators only
                          );    

        update_option('gsmtc_access_settings', $option, true);
    }

    /**
     * Creation of the gsmtc_smtp_settings
     * 
     * This method creates the wp option 'gsmtc_access_settings' to store the ips that are currently bloqued.
     * 
     */
    private static function create_option_gsmtc_smtp_settings(): void{  

        $option = array (   'enabled' => false, // Enables or Disables the smtp functionality
                            'host' => '', // The smtp server url
                            'port' => '587', // the smtp port, 587, 465 or 25 it depends of your server 
                            'userName' => '', // The smtp username
                            'password' =>  '', //  the smtp password
                            'secure' => 'tls', // tls or ssl
                            'from' => '',  // from email address
                            'fromName' => '', // replace with the from name
        );

        update_option('gsmtc_smtp_settings', $option, true);
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
    function unlock_ip($ip){
        global $wpdb;

        $query = "SELECT * FROM ".self::$table_name_login_status_ip." WHERE ip = '".$ip."'";

        $status = $wpdb->get_row($query,ARRAY_A);
        $now = time();

        if (($status != null) && (intval($status['lockUntil']) < $now)){
            $status['lockUntil'] = 0;
            $status['status'] = 'enabled';

            $result = $wpdb->update(self::$table_name_login_status_ip,$status,array('id' => $status['id']));
            if (false !== $result)
                $result = true;
        } else $result = false;

        return $result;
    }

    /**
     * Method reload_gsmtc_bloqued_ips
     * 
     * This method updates the option gsmtc_bloqued_ips from table login_status_ip and check if all bloqued ips are still bloked.
     * 
     * @params void
     * @return void
     */
    function reload_gsmtc_bloqued_ips(){
        global $wpdb;

        // spotlight to checks if the are queries of the option in process
        $spotlight = 'gsmtc_login_failed_spotlight';
        $lock_time = 30; // Maximun time of bloqued in seconds
        $retry_delay = 1; // Seconds to retry the query

        // wait until the transient expires or is deleted
        while (get_transient($spotlight) == 'true'){
            sleep($retry_delay);
        }

        // set the spotlight to disabling the access to the option
        set_transient($spotlight,'true',$lock_time);

        $query = "SELECT * FROM ".self::$table_name_login_status_ip." WHERE status = 'bloqued'";
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
                $wpdb->update(self::$table_name_login_status_ip,$result,array('id' => $result['id']));
            }
        }
        // create the gsmtc_bloqued_ips
		update_option('gsmtc_bloqued_ips',$bloquedIps);

        // delete the spotlight to enabling the access to the option
        delete_transient($spotlight);
    
    }

    /**
     * Send a formated html email to user with an advanced HTML template to a user_email.
     *
     * This function sends a fotmated email in an html template to the user_email with the $body_title and $body_content
     *
     * @param string $user_email The email to send informatios.
     * @param string $subject The subject of the email.
     * @param string $body_title The main title of the email body. 
     * @param string $body_content The main content of the email body.
     * 
     * @return void
     */
    function send_formated_html_email_to_user($user_email,$subject, $body_title, $body_content) {

        // Email headers
        $headers = array('Content-Type: text/html; charset=UTF-8');

        // HTML Email template
        $email_template = '
            <html>
            <head>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        background-color: #f9f9f9;
                        margin: 0;
                        padding: 20px;
                    }
                    .email-container {
                        background-color: #ffffff;
                        max-width: 600px;
                        margin: 20px auto;
                        padding: 20px;
                        border-radius: 8px;
                        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
                    }
                    .email-header {
                        background-color: #2f4f80;
                        color: #ffffff;
                        padding: 10px;
                        text-align: center;
                        border-radius: 8px 8px 0 0;
                    }
                    .email-body {
                        padding: 20px;
                        color: #000000;
                        line-height: 1.6;
                    }
                    .email-footer {
                        margin-top: 20px;
                        font-size: 12px;
                        color: #777777;
                        text-align: center;
                    }
                    .button {
                        display: inline-block;
                        padding: 10px 20px;
                        margin-top: 20px;
                        background-color: #2f4f80;
                        color: #ffffff;
                        text-decoration: none;
                        border-radius: 5px;
                    }
                    .email-body a {
                        display: inline-block;
                        padding: 10px 20px;
                        margin-top: 20px;
                        background-color: #2f4f80;
                        color: #ffffff;
                        text-decoration: none;
                        border-radius: 5px;
                    }
                    .button:hover {
                        background-color: #005a87;
                    }
                    .gsmtc-footer a {
                        text-decoration: none;
                    }
                </style>
            </head>
            <body>
                <div class="email-container">
                    <div class="email-header">
                        <h1>'.$body_title.'</h1>
                    </div>
                    <div class="email-body">
                        <p>' . nl2br($body_content) . '</p>
                        <a href="' . home_url() . '">Visit Site</a>
                    </div>
                    <div class="email-footer">
                        <p>This email was sent from your WordPress site: ' . get_bloginfo('name') . '</p>
                        <p class="gsmtc-footer">' . get_bloginfo('url') . '</p>
                    </div>
                </div>
            </body>
            </html>
        ';

        return wp_mail($user_email, $subject, $email_template, $headers);
    }


}