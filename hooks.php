<?php
/**
 * WHMCS SDK Sample Provisioning Module Hooks File
 *
 * Hooks allow you to tie into events that occur within the WHMCS application.
 *
 * This allows you to execute your own code in addition to, or sometimes even
 * instead of that which WHMCS executes by default.
 *
 * WHMCS recommends as good practice that all named hook functions are prefixed
 * with the keyword "hook", followed by your module name, followed by the action
 * of the hook function. This helps prevent naming conflicts with other addons
 * and modules.
 *
 * For every hook function you create, you must also register it with WHMCS.
 * There are two ways of registering hooks, both are demonstrated below.
 *
 * @see https://developers.whmcs.com/hooks/
 *
 * @copyright Copyright (c) WHMCS Limited 2017
 * @license https://www.whmcs.com/license/ WHMCS Eula
 */

// Require any libraries needed for the module to function.
// require_once __DIR__ . '/path/to/library/loader.php';
//
// Also, perform any initialization required by the service's library.

/**
 * Client edit sample hook function.
 *
 * This sample demonstrates making a service call whenever a change is made to a
 * client profile within WHMCS.
 *
 * @param array $params Parameters dependant upon hook function
 *
 * @return mixed Return dependant upon hook function
 */
function hook_provisioningmodule_clientedit(array $params)
{
    try {
        // Call the service's function, using the values provided by WHMCS in
        // `$params`.
    } catch (Exception $e) {
        // Consider logging or reporting the error.
    }
}

/**
 * Register a hook with WHMCS.
 *
 * add_hook(string $hookPointName, int $priority, string|array|Closure $function)
 */
add_hook('ClientEdit', 1, 'hook_provisioningmodule_clientedit');

/**
 * Insert a service item to the client area navigation bar.
 *
 * Demonstrates adding an additional link to the Services navbar menu that
 * provides a shortcut to a filtered products/services list showing only the
 * products/services assigned to the module.
 *
 * @param \WHMCS\View\Menu\Item $menu
 */
//add_hook('ClientAreaPrimaryNavbar', 1, function ($menu)
//{
//    // Check whether the services menu exists.
//    if (!is_null($menu->getChild('Services'))) {
//        // Add a link to the module filter.
//        $menu->getChild('Services')
//            ->addChild(
//                'Provisioning Module Products',
//                array(
//                    'uri' => 'clientarea.php?action=services&module=provisioningmodule',
//                    'order' => 15,
//                )
//            );
//    }
//});

/**
 * Render a custom sidebar panel in the secondary sidebar.
 *
 * Demonstrates the creation of an additional sidebar panel on any page where
 * the My Services Actions default panel appears and populates it with a title,
 * icon, body and footer html output and a child link.  Also sets it to be in
 * front of any other panels defined up to this point.
 *
 * @param \WHMCS\View\Menu\Item $secondarySidebar
 */
//add_hook('ClientAreaSecondarySidebar', 1, function ($secondarySidebar)
//{
//    // determine if we are on a page containing My Services Actions
//    if (!is_null($secondarySidebar->getChild('My Services Actions'))) {
//
//        // define new sidebar panel
//        $customPanel = $secondarySidebar->addChild('Provisioning Module Sample Panel');
//
//        // set panel attributes
//        $customPanel->moveToFront()
//            ->setIcon('fa-user')
//            ->setBodyHtml(
//                'Your HTML output goes here...'
//            )
//            ->setFooterHtml(
//                'Footer HTML can go here...'
//            );
//
//        // define link
//        $customPanel->addChild(
//                'Sample Link Menu Item',
//                array(
//                    'uri' => 'clientarea.php?action=services&module=provisioningmodule',
//                    'icon'  => 'fa-list-alt',
//                    'order' => 2,
//                )
//            );
//
//    }
//});



//add_hook('ProductEdit', 1, function($vars) {
//    // Perform hook code here...
//    logModuleCall(
//        'mysqldb',
//        "ProductEdit",
//        "parameters",
//        $vars
//    );
//
//
//});

//add_hook('InvoicePaid', 1, function($vars) {
//    // Perform hook code here...
//    logModuleCall(
//        'mysqldb',
//        "InvoicePaid",
//        Session,
//        $vars
//    );
//
//});


//add_hook('OrderPaid', 1, function($vars) {
//    // Perform hook code here...
//    logModuleCall(
//        'mysqldb',
//        'OrderPaid',
//        $vars
//    );
//
//    // Get User Name
//    $params = array(
//        'action'=>'GetClientsDetails',
//        'clientid' => $vars['userId'],
//        'stats' => true,
//        );
//    $response = request_whmcs_api($params);
//
//    $username = $response['firstname'];
//    $db_name = $username."_db";
//
//    // Get Order details
//    $order_details = request_whmcs_api(array('action'=>'GetOrders', 'id'=>$vars['orderId']));
//    if(!$order_details){
//        return;
//    }
//
//    if(count($order_details['orders']['order']) == 0){
//        return;
//    }
//
//    $product = $order_details['orders']['order'][0]['lineitems']['lineitem'][0];
//    logModuleCall('mysqldb', 'OrderPaid', 'product', $product);
//
//    $products = request_whmcs_api(array('action'=>'GetProducts'));
////    $db_server_ip = $vars
//
//    // Perform DB actions
//    $query = "Mysql/get_server_information";
//    $result = request_cpanel_api($query, array());
//
//});

//function request_whmcs_api($params){
//    $whmcs_api_identifier = "ypwzLE0J9gdEvbEuYLYCED41pm23URKf";
//    $whmcs_api_secret = "W7R6t38soBBDe8XDCSbFGFiPAmazgObd";
//    $whmcs_api_access_key = "vasilieva";
//
//    $params['username'] = $whmcs_api_identifier;
//    $params['password'] = $whmcs_api_secret;
//    $params['accesskey'] = $whmcs_api_access_key;
//    $params['responsetype'] = 'json';
//
//    $ch = curl_init();
//    curl_setopt($ch, CURLOPT_URL, 'https://test.openrma.com/includes/api.php');
//    curl_setopt($ch, CURLOPT_POST, 1);
//    curl_setopt($ch, CURLOPT_POSTFIELDS,
//        http_build_query($params)
//    );
//    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//    $response = curl_exec($ch);
//    curl_close($ch);
//
//    logModuleCall(
//        'mysqldb',
//        'OrderPaid',
//        $params,
//        $response
//    );
//    return $response;
//}
//
//function request_cpanel_api($query, $params){
//    $cpusername = "openrmaprojects";
//    $cppassword = "IGc7W^v]anCo";
//
//    $query = "https://127.0.0.1:2083/execute/$query";
//
//    $curl = curl_init();                                // Create Curl Object
//    curl_setopt($curl, CURLOPT_SSL_VERIFYPEER,0);       // Allow self-signed certs
//    curl_setopt($curl, CURLOPT_SSL_VERIFYHOST,0);       // Allow certs that do not match the hostname
//    curl_setopt($curl, CURLOPT_HEADER,0);               // Do not include header in output
//    curl_setopt($curl, CURLOPT_RETURNTRANSFER,1);       // Return contents of transfer on curl_exec
//    $header[0] = "Authorization: Basic " . base64_encode($cpusername.":".$cppassword) . "\n\r";
//    curl_setopt($curl, CURLOPT_HTTPHEADER, $header);    // set the username and password
//    curl_setopt($curl, CURLOPT_URL, $query);            // execute the query
//    $result = curl_exec($curl);
//    if ($result == false) {
//        logModuleCall(
//            'mysqldb',
//            "OrderPaid",
//            curl_error($curl),
//            $query
//        );
//        error_log("curl_exec threw error \"" . curl_error($curl) . "\" for $query");
//        // log error if curl exec fails
//    }
//    curl_close($curl);
//    logModuleCall(
//        'mysqldb',
//        "OrderPaid",
//        $query,
//        $result
//    );
//    return $result;
//}