<?php

namespace GesimaticLoginAttempts\Security;

use GesimaticLoginAttempts\Core\Setup;
use GesimaticLoginAttempts\Core\Config;

/**
 * Class Security.
 * 
 * @package GesimaticLoginAttempts\Security.
*/
class Security extends Setup{

    /**
     * This method gets the ip from client request.
     *       
     * @return string
     */
    public static function get_client_ip()
    {
        return $_SERVER['REMOTE_ADDR'] ?? '';
    }

    /**
     * This method checks if the givenip is blocked.
     *       
     * @return bool
     */
    public static function is_ip_blocked($ip)
    {

        // spotlight to checks if the are queries of the option in process
        $lock_time = 30; // Maximun time of bloqued in seconds
        $retry_delay = 1; // Seconds to retry the query

        // wait until the transient expires or is deleted
        while (get_transient(Config::SPOTLIGHT_QUERING_BLOCKED_IPS) == 'true'){
            sleep($retry_delay);
        }

        // set the spotlight to disabling the access to the option
        set_transient(Config::SPOTLIGHT_QUERING_BLOCKED_IPS,'true',$lock_time);

        // default return value
        $blockedIp = false;
        // get the gsmtc_blocked_ips option
        $blockedIps = get_option(Config::OPTION_BLOCKED_IPS,array());
        // creates an array to store the new blocked ips
        $newBlockedIps = array();
        // boolean to checks if blocked_ips has been changed
        $updated_blocked_ips = false;
        // gets the current time
        $now = time();

        // checks if ip is in blockedIps array 
        foreach ($blockedIps as $blocked){
            // To checks if an ip is blocked, it must check two values, if the ip is in array.
            if ($blocked['ip'] == $ip){
                // and if blocked time hasn`t expired, the current time is minor than "until" blocked time
                if ($now < intval($blocked['until'])){
                    $blockedIp = true;
                    $newBlockedIps[] = $blocked;
                } else {// the current time is higher than "until" blocked time it must unlock the ip
                    self::unlock_ip($blocked['ip']);
                    $updated_blocked_ips = true;
                }
            } else {
                $newBlockedIps[] = $blocked;
            }
        } ;
    
        if ($updated_blocked_ips)
            update_option(Config::OPTION_BLOCKED_IPS,$newBlockedIps);

        // delete the spotlight to enabling the access to the option
        delete_transient(Config::SPOTLIGHT_QUERING_BLOCKED_IPS);


        return $blockedIp;

    }



   /**
     * Method unlock_ip
     *
     * This method update the status of a blocked Ip to unlock.
     *
     * This method is used by both children classes
     *
     * @params ip The ip to unlock
     * @return boolean
     */
    public static function unlock_ip($ip){
        global $wpdb;

        $status = $wpdb->get_row($wpdb->prepare("SELECT * FROM " . $wpdb->prefix . Config::TABLE_NAME_STATUS_IP . " WHERE ip = %s", $ip), ARRAY_A);
        $now = time();
        $result = false;

        if (($status != null) && (intval($status['lockUntil']) < $now)){
            $status['lockUntil'] = 0;
            $status['status'] = 'enabled';

            $update = $wpdb->update($wpdb->prefix . Config::TABLE_NAME_STATUS_IP,$status,array('id' => $status['id']));
            if (false !== $update)
                $result = true;
        } else {
            $result = false;
        }

        return $result;
    }

    /**
     * Method reload_blocked_ips
     *
     * This method updates the option gsmtc_blocked_ips from table login_status_ip and check if all blocked ips are still blocked.
     *
     * @params void
     * @return void
     */
    public static function reload_blocked_ips(){
        global $wpdb;

        // spotlight to checks if the are queries of the option in process
        $lock_time = 30; // Maximun time of blocked in seconds
        $retry_delay = 1; // Seconds to retry the query

        // wait until the transient expires or is deleted
        while (get_transient(Config::SPOTLIGHT_QUERING_BLOCKED_IPS) == 'true'){
            sleep($retry_delay);
        }

        // set the spotlight to disabling the access to the option
        set_transient(Config::SPOTLIGHT_QUERING_BLOCKED_IPS,'true',$lock_time);

        $query = "SELECT * FROM ".$wpdb->prefix . Config::TABLE_NAME_STATUS_IP." WHERE status = 'blocked'";
        $results = $wpdb->get_results($query,ARRAY_A);
        $blockedIps = array();
        $now = time();
        foreach($results as $result){
            if (intval($result['lockUntil']) > $now){
                $blockedIp = array(
                    'ip' => $result['ip'],
                    'until' => $result['lockUntil']
                );
                $blockedIps[] = $blockedIp;
            } else {
                $result['lockUntil'] = 0;
                $result['status'] = 'enabled';
                $wpdb->update($wpdb->prefix . Config::TABLE_NAME_STATUS_IP,$result,array('id' => $result['id']));
            }
        }
        // create the gsmtc_blocked_ips
		update_option(Config::OPTION_BLOCKED_IPS,$blockedIps);

        // delete the spotlight to enabling the access to the option
        delete_transient(Config::SPOTLIGHT_QUERING_BLOCKED_IPS);

    }

    }