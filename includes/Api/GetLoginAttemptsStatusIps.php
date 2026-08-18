<?php

namespace GesimaticLoginAttempts\Api;

use Gesimatic\Api\ActionInterface;
use Gesimatic\Api\Controllers\AdminController;
use Gesimatic\Api\Base\CommonResponse;

use GesimaticLoginAttempts\Core\Setup;
use GesimaticLoginAttempts\Security\Security;
use GesimaticLoginAttempts\Core\Config;

/**
 * Class Setup
 *
 * This class contains the code necessary to manage the data from get_login_attempts_settings api request.
 *
 * @package gesimatic-login-attempts
 */
class GetLoginAttemptsStatusIps extends Setup implements ActionInterface{

     /**
     * Order valid values.
     * @var array
     * @since 1
     */
    protected const VALID_ORDERS = ['','desc','asc'];

    /**
     * To validate 
     * 
     * This method perfoms the necesaria actions to validate data.
     * 
     */
    public static function validate($params){

//        error_log ('GetLoginAttemptsStatusIps validate, $params: '.var_export($params,true));

        // sets the default value
        $sanitized_params = array();

        // check if acction is as expected
        if(isset($params['action']) && ($params['action'] === 'get-login-attempts-status-ips')){
            if(isset($params['query'])){
                
                $query = json_decode($params['query']);
                if ( json_last_error() !== JSON_ERROR_NONE) return false;

                $query = (array) $query; // convert object $settings to array

                error_log ('GetLoginAttemptsStatusIps validate, $query: '.var_export($query,true));

                // validate page
                if(isset($query['page']) ){
                    $sanitized_params['page'] = sanitize_text_field($query['page']);
                    if ( (filter_var($sanitized_params['page'], FILTER_VALIDATE_INT) === false) || ( 0 >= (int) $sanitized_params['page'])) return false;
                }else return false;

                error_log ('GetLoginAttemptsStatusIps validate, $sanitized_params: '.var_export($sanitized_params,true));

                // validate orderAttempts
                if(isset($query['orderAttempts']) ){
                    $sanitized_params['orderAttempts'] = sanitize_text_field($query['orderAttempts']);
                    if ( ! in_array($sanitized_params['orderAttempts'],self::VALID_ORDERS)) return false;
                }else return false;
                error_log ('GetLoginAttemptsStatusIps validate, $sanitized_params: '.var_export($sanitized_params,true));


                // validate orderLockPeriod
                if(isset($query['orderLockPeriod']) ){
                    $sanitized_params['orderLockPeriod'] = sanitize_text_field($query['orderLockPeriod']);
                    if ( ! in_array($sanitized_params['orderLockPeriod'],self::VALID_ORDERS)) return false;
                }else return false;
//                error_log ('GetLoginAttemptsStatusIps validate, $sanitized_params: '.var_export($sanitized_params,true));

                // validate orderLastAttempt
                if(isset($query['orderLastAttempt']) ){
                    $sanitized_params['orderLastAttempt'] = sanitize_text_field($query['orderLastAttempt']);
                    if ( ! in_array($sanitized_params['orderLastAttempt'],self::VALID_ORDERS)) return false;
                }else return false;
//                error_log ('GetLoginAttemptsStatusIps validate, $sanitized_params: '.var_export($sanitized_params,true));

                // validate FilterStatus
                if(isset($query['filterStatus']) ){
                    $sanitized_params['filterStatus'] = sanitize_text_field($query['filterStatus']);
                    if ( ! in_array($sanitized_params['filterStatus'],Config::VALID_FILTERS)) return false;
                }else return false;

            } else return false;
        } else return false;

        return $sanitized_params;
    }

    /**
     * To handle 
     * 
     * This method perfoms the necesaria actions to handle data, to perform the request.
     * 
     */
    public static function handle($params){

    	global $wpdb;

        error_log ('GetLoginAttemptsStatusIps handle, $params: '.var_export($params,true));

        if (is_array($params)){

            $result = array();

//            Security::reload_blocked_ips();

            $page = intval($params['page']);

            $orderQuery = '';
            if($params['orderLastAttempt'] != ''){
                if($params['orderLastAttempt'] == 'asc')
                    $orderQuery = ' ORDER BY lastAttempt ASC ';
                else $orderQuery = ' ORDER BY lastAttempt DESC ';
            } else if($params['orderAttempts'] != ''){
                        if($params['orderAttempts'] == 'asc')
                            $orderQuery = ' ORDER BY attempts ASC ';
                        else $orderQuery = ' ORDER BY attempts DESC ';
                    } else if($params['orderLockPeriod'] != ''){
                                if($params['orderLockPeriod'] == 'asc')
                                    $orderQuery = ' ORDER BY currentPeriod ASC ';
                                else $orderQuery = ' ORDER BY currentPeriod DESC ';
                            };

			$filterQuery = '';
			if($params['filterStatus'] != ''){
				if($params['filterStatus'] == 'enabled')
					$filterQuery = " WHERE status = 'enabled' ";
				else $filterQuery = " WHERE status <> 'enabled' ";
			};

	        $offset = ($page - 1) * intval(self::$per_page);

            // get status ips
            $sql = "SELECT * FROM " . $wpdb->prefix . Config::TABLE_NAME_STATUS_IP . " " . $filterQuery . $orderQuery . " LIMIT %d OFFSET %d";
            $query = $wpdb->prepare($sql, self::$per_page, $offset);
            error_log ('GetLoginAttemptsStatusIps handle, $sql: '.var_export($sql,true));
            error_log ('GetLoginAttemptsStatusIps handle, $query: '.var_export($query,true));

            $results = $wpdb->get_results($query, ARRAY_A);

        error_log ('GetLoginAttemptsStatusIps handle, $results: '.var_export($results,true));

            //sending the rest time to unblock
			$now = time();
			$new_results = array();
			foreach($results as $result){
                if (intval($result['lockUntil']) > intval($now)){
					$result['lockUntil'] = intval($result['lockUntil']) - intval($now);
					
				} else if ($result['status'] != 'enabled'){
						$result['lockUntil'] = 0;
						$result['status'] = 'enabled';
                        Security::unlock_ip($result['ip']);
					}
				// send the seconds from lastAttempt to syncronize with client clock
				$result['lastAttempt'] = $now - intval($result['lastAttempt']);
				$new_results[] = $result;
			}
			$results = $new_results;

            error_log ('GetLoginAttemptsStatusIps handle, $new_results: '.var_export($new_results,true));

             return new \WP_REST_Response($results, 200);

        } else return CommonResponse::error();

    }


}