<?php

namespace GesimaticLoginAttempts\Security;

use GesimaticLoginAttempts\Core\Setup;

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
        while (get_transient(self::SPOTLIGHT_QUERING_BLOCKED_IPS) == 'true'){
            sleep($retry_delay);
        }

        // set the spotlight to disabling the access to the option
        set_transient(self::SPOTLIGHT_QUERING_BLOCKED_IPS,'true',$lock_time);

        // default return value
        $blockedIp = false;
        // get the gsmtc_blocked_ips option
        $blockedIps = get_option(self::OPTION_BLOCKED_IPS,array());
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
            update_option(self::OPTION_BLOCKED_IPS,$newBlockedIps);

        // delete the spotlight to enabling the access to the option
        delete_transient(self::SPOTLIGHT_QUERING_BLOCKED_IPS);


        return $blockedIp;

    }


    }