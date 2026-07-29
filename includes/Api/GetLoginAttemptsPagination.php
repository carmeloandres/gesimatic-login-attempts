<?php

namespace GesimaticLoginAttempts\Api;

use Gesimatic\Api\Controllers\ActionInterface;
use Gesimatic\Api\Controllers\AdminController;
use Gesimatic\Api\Base\CommonResponse;

use GesimaticLoginAttempts\Core\Setup;
use GesimaticLoginAttempts\Core\Config;

/**
 * Class Setup
 *
 * This class contains the code necessary to manage the data from get_login_attempts_pagination api request.
 *
 * @package gesimatic-login-attempts
 */
class GetLoginAttemptsPagination extends Setup implements ActionInterface{

    /**
     * To validate 
     * 
     * This method perfoms the necesaria actions to validate data.
     * 
     */
    public static function validate($params){

        error_log ('GetLoginAttemptsPagination validate, $params: '.var_export($params,true));

        // sets the default value
        $sanitized_params = array();

        // check if acction is as expected
        if(isset($params['action']) && ($params['action'] === 'get_login_attempts_pagination')){
                // validate FilterStatus
                if(isset($params['filterStatus']) ){
                    $sanitized_params['filterStatus'] = sanitize_text_field($params['filterStatus']);
                    if ( ! in_array($sanitized_params['filterStatus'],Config::VALID_FILTERS)) return false;
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

        $results = array(
			'items' => 0,
			'pages' => 0
		);

        error_log ('GetLoginAttemptsPagination handle, $validated: '.var_export($validated,true));

        if (is_array($validated)){

        	$filterStatus = '';
			if ($validated['filterStatus'] != '')
				if ($validated['filterStatus'] == 'enabled')
					$filterStatus = " WHERE status = 'enabled' ";
				else $filterStatus = " WHERE status <> 'enabled' ";
        
    $items = $wpdb->get_var( "SELECT COUNT(*) FROM " . self::$table_name_status_ip . " " . $filterStatus );

	        $pages = 0;
			if (($items != NULL)){
				$pages = intval($items) / intval(self::$per_page);
				if ((intval($items) % intval(self::$per_page)) > 0)
					$pages = intval($pages + 1); 
				else $pages = intval($pages);
			}

			$results = array(
				'items' => $items,
				'pages' => $pages
			);		


        } else return CommonResponse::error();

        return new \WP_REST_Response($results, 200);
    }


}