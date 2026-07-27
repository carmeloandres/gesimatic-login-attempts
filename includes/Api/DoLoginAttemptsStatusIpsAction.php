<?php

namespace GesimaticLoginAttempts\Api;

use Gesimatic\Api\Controllers\ActionInterface;
use Gesimatic\Api\Controllers\AdminController;
use Gesimatic\Api\Base\CommonResponse;

use GesimaticLoginAttempts\Core\Setup;

/**
 * Class DoLoginAttemptsStatusIpsAction
 *
 * This class contains the code necessary to manage the data from do_login_attemtps_status_ips_action api request.
 *
 * @package gesimatic-login-attempts
 */
class DoLoginAttemptsStatusIpsAction extends Setup implements ActionInterface{

    /**
     * valid action values.
     * @var array
     * @since 1
     */
    protected const VALID_ACTIONS = ['reset','unlock'];


    /**
     * To validate 
     * 
     * This method perfoms the necesaria actions to validate data.
     * 
     */
    public static function validate($params){

        // sets the default value
        $sanitized_params = array();

        // check if acction is as expected
        if(isset($params['action']) && ($params['action'] === 'do_login_attempts_status_ips_action')){
                // validate doAction
                if(isset($params['doAction']) ){
                    $sanitized_params['doAction'] = sanitize_text_field($params['doAction']);
                    if ( ! in_array($sanitized_params['doAction'],self::VALID_ACTIONS)) return false;
                }else return false;
                // validate ids
                if(isset($params['ids']) ){
                    $ids = json_decode($params['ids'], true);
                    error_log ('DoLoginAttemptsStatusIpsAction validate, $ids: '.var_export($ids,true));

                    if ( json_last_error() !== JSON_ERROR_NONE) return false;
                    foreach($ids as $index => $id){
                        $sanitized_params['ids'][$index] = sanitize_text_field($id);
                        if ( (filter_var($sanitized_params['ids'][$index], FILTER_VALIDATE_INT) === false) || ( 0 >= (int) $sanitized_params['ids'][$index])) return false;
                    }
                }else return false;

        } else return false;

        return $sanitized_params;
    }

    /**
     * To handle 
     * 
     * This method perfoms the necesaria actions to handle data, to perform the request.
     * 
     */
    public static function handle($validated){
        global $wpdb;

        error_log ('DoLoginAttemptsStatusIpsAction handle, $validated: '.var_export($validated,true));

        if (is_array($validated)){
            foreach($validated['ids'] as $id){
                if ($validated['doAction'] == 'reset'){
                    $wpdb->delete(self::$table_name_status_ip,array('id' => $id));
                } else {
                    $status = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . self::$table_name_status_ip . " WHERE id = %d", (int) $id ), ARRAY_A );

                    if ( is_array( $status ) ) {
                        $status['lockUntil'] = 0;
                        $status['status'] = 'enabled';
                        $wpdb->update(self::$table_name_status_ip,$status,array('id' => $id));
                    }
                }
            }
            self::reload_blocked_ips();

        } else return CommonResponse::error();

        return new \WP_REST_Response(true, 200);
    }


}