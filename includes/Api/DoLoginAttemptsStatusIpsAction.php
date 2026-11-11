<?php

namespace GesimaticLoginAttempts\Api;

use Gesimatic\Api\ActionInterface;
use Gesimatic\Api\Controllers\AdminController;
use Gesimatic\Api\Base\CommonResponse;

use GesimaticLoginAttempts\Repositories\LoginAttemptsRepository;
use GesimaticLoginAttempts\Core\Setup;
use GesimaticLoginAttempts\Security\Security;

/**
 * Class DoLoginAttemptsStatusIpsAction
 *
 * This class contains the code necessary to manage the data from do_login_attemtps_status_ips_action api request.
 *
 * @package gesimatic-login-attempts
 */
class DoLoginAttemptsStatusIpsAction extends Setup implements ActionInterface{

    /**
     * Administrative action identifier.
     */
    public const ACTION = 'do-login-attempts-status-ips';

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
        if(isset($params['action']) && ($params['action'] === self::ACTION)){
                // validate doAction
                if(isset($params['doAction']) && is_string($params['doAction'])){
                    $sanitized_params['doAction'] = sanitize_text_field($params['doAction']);
                    if ( ! in_array($sanitized_params['doAction'],self::VALID_ACTIONS)) return false;
                }else return false;
                // validate ids
                if(isset($params['ids']) && is_string($params['ids'])){
                    $ids = json_decode($params['ids']);
                    error_log ('DoLoginAttemptsStatusIpsAction validate, $ids: '.var_export($ids,true));

                    if (json_last_error() !== JSON_ERROR_NONE || !is_array($ids) || $ids === []) {
                        return false;
                    }

                    $sanitized_params['ids'] = [];

                    foreach ($ids as $id) {
                        if (!is_int($id) && !is_string($id)) {
                            return false;
                        }

                        $id = filter_var($id, FILTER_VALIDATE_INT, ['options' => ['min_range' => 1]]);

                        if ($id === false) {
                            return false;
                        }

                        $sanitized_params['ids'][] = $id;
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
            $success = true;

            foreach($validated['ids'] as $id){
                if ($validated['doAction'] == 'reset'){
                    if ($wpdb->delete(LoginAttemptsRepository::table_name(),array('id' => $id)) === false) {
                        $success = false;
                    }
                } else {
                    $status = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . LoginAttemptsRepository::table_name() . " WHERE id = %d", (int) $id ), ARRAY_A );

                    if ( is_array( $status ) ) {
                        if ($wpdb->update(
                            LoginAttemptsRepository::table_name(),
                            ['lockUntil' => 0, 'status' => 'enabled'],
                            ['id' => $id],
                            ['%d', '%s'],
                            ['%d']
                        ) === false) {
                            $success = false;
                        }
                    } elseif ($wpdb->last_error !== '') {
                        $success = false;
                    }
                }
            }
            Security::clear_request_cache();

        } else return ['success' => false];

        return ['success' => $success];
    }


}
