<?php

/**
 * ZohoCrm Integration
 *
 */

namespace BitCode\BITWPFZC\Integration\ZohoCRM;

use WP_Error;
use BitCode\BITWPFZC\Core\Util\HttpHelper;
use BitCode\BITWPFZC\Integration\ZohoCRM\RecordApiHelper;
use BitCode\BITWPFZC\Integration\IntegrationHandler;
use BitCode\BITWPFZC\Core\Util\IpTool;
use BitCode\BITWPFZC\Admin\Log\Handler as Log;

/**
 * Provide functionality for ZohoCrm integration
 */
final class ZohoCRMHandler
{
    private $_formID;
    private $_integrationID;

    public function __construct($integrationID, $fromID)
    {
        $this->_formID = $fromID;
        $this->_integrationID = $integrationID;
    }
    public static function registerAjax()
    {
        \add_action('wp_ajax_bitwpfzc_zcrm_generate_token', array(__CLASS__, 'generateTokens'));
        \add_action('wp_ajax_bitwpfzc_zcrm_refresh_modules', array(__CLASS__, 'refreshModulesAjaxHelper'));
        \add_action('wp_ajax_bitwpfzc_zcrm_refresh_layouts', array(__CLASS__, 'refreshLayoutsAjaxHelper'));
    }


    /**
     * Process ajax request for generate_token
     *
     * @return JSON zoho crm api response and status
     */
    public static function generateTokens()
    {
        if (isset($_REQUEST['_ajax_nonce']) && wp_verify_nonce(sanitize_text_field($_REQUEST['_ajax_nonce']), 'bitwpfzc_nonce')) {
            $inputJSON = file_get_contents('php://input');
            $requestsParams = json_decode($inputJSON);
            if (
                empty($requestsParams->{'accounts-server'})
                || empty($requestsParams->dataCenter)
                || empty($requestsParams->clientId)
                || empty($requestsParams->clientSecret)
                || empty($requestsParams->redirectURI)
                || empty($requestsParams->code)
            ) {
                wp_send_json_error(
                    __(
                        'Requested parameter is empty',
                        'bitforms'
                    ),
                    400
                );
            }
            $apiEndpoint = \urldecode($requestsParams->{'accounts-server'}) . '/oauth/v2/token';
            $requestParams = array(
                "grant_type" => "authorization_code",
                "client_id" => $requestsParams->clientId,
                "client_secret" => $requestsParams->clientSecret,
                "redirect_uri" => \urldecode($requestsParams->redirectURI),
                "code" => $requestsParams->code
            );
            $apiResponse = HttpHelper::post($apiEndpoint, $requestParams);
            if (is_wp_error($apiResponse) || !empty($apiResponse->error)) {
                wp_send_json_error(
                    empty($apiResponse->error) ? 'Unknown' : $apiResponse->error,
                    400
                );
            }
            $apiResponse->generates_on = \time();
            wp_send_json_success($apiResponse, 200);
        } else {
            wp_send_json_error(
                __(
                    'Token expired',
                    'bitwpfzc'
                ),
                401
            );
        }
    }
    /**
     * Process ajax request for refresh crm modules
     *
     * @return JSON crm module data
     */
    public static function refreshModulesAjaxHelper()
    {
        if (isset($_REQUEST['_ajax_nonce']) && wp_verify_nonce(sanitize_text_field($_REQUEST['_ajax_nonce']), 'bitwpfzc_nonce')) {
            $inputJSON = file_get_contents('php://input');
            $queryParams = json_decode($inputJSON);
            if (
                empty($queryParams->tokenDetails)
                || empty($queryParams->dataCenter)
                || empty($queryParams->clientId)
                || empty($queryParams->clientSecret)
            ) {
                wp_send_json_error(
                    __(
                        'Requested parameter is empty',
                        'bitforms'
                    ),
                    400
                );
            }
            $response = [];
            if ((intval($queryParams->tokenDetails->generates_on) + (55 * 60)) < time()) {
                $response['tokenDetails'] = ZohoCRMHandler::_refreshAccessToken($queryParams);
            }


            $allModules['Leads'] = (object) array(
                'plural_label' => 'Leads',
                'triggers_supported' => true,
                'quick_create' => true,
            );
            uksort($allModules, 'strnatcasecmp');
            $response["modules"] = $allModules;

            if (!empty($response['tokenDetails']) && !empty($queryParams->id)) {
                // var_dump($queryParams->formID, $queryParams->id);
                ZohoCRMHandler::_saveRefreshedToken($queryParams->formID, $queryParams->id, $response['tokenDetails'], $response['modules']);
            }
            wp_send_json_success($response, 200);
        } else {
            wp_send_json_error(
                __(
                    'Token expired',
                    'bitwpfzc'
                ),
                401
            );
        }
    }
    /**
     * Process ajax request for refesh crm layouts
     *
     * @return JSON crm layout data
     */
    public static function refreshLayoutsAjaxHelper()
    {
        if (isset($_REQUEST['_ajax_nonce']) && wp_verify_nonce(sanitize_text_field($_REQUEST['_ajax_nonce']), 'bitwpfzc_nonce')) {
            $inputJSON = file_get_contents('php://input');
            $queryParams = json_decode($inputJSON);
            if (
                empty($queryParams->module)
                || empty($queryParams->tokenDetails)
                || empty($queryParams->dataCenter)
                || empty($queryParams->clientId)
                || empty($queryParams->clientSecret)
            ) {
                wp_send_json_error(
                    __(
                        'Requested parameter is empty',
                        'bitforms'
                    ),
                    400
                );
            }
            $response = [];
            if ((intval($queryParams->tokenDetails->generates_on) + (55 * 60)) < time()) {
                $response['tokenDetails'] = ZohoCRMHandler::_refreshAccessToken($queryParams);
            }
            $layoutsMetaApiEndpoint = "{$queryParams->tokenDetails->api_domain}/crm/v2/settings/layouts";
            $authorizationHeader["Authorization"] = "Zoho-oauthtoken {$queryParams->tokenDetails->access_token}";
            $requiredParams['module'] = $queryParams->module;
            $layoutsMetaResponse = HttpHelper::get($layoutsMetaApiEndpoint, $requiredParams, $authorizationHeader);
            if (!is_wp_error($layoutsMetaResponse) && (empty($layoutsMetaResponse->status) || (!empty($layoutsMetaResponse->status) && $layoutsMetaResponse->status !== 'error'))) {
                $retriveLayoutsData = $layoutsMetaResponse->layouts;
                $fieldToShow = ['Email','First_Name','Last_Name','Phone'];
                $layouts = [];
                foreach ($retriveLayoutsData as $layoutKey => $layoutValue) {
                    if ($layoutValue->name !== 'Standard') {
                        continue;
                    }
                    $fields = [];
                    $fileUploadFields = [];
                    $requiredFields = [];
                    $requiredFileUploadFiles = [];
                    $uniqueFields = [];
                    foreach ($layoutValue->sections as $sectionKey => $sectionValue) {
                        foreach ($sectionValue->fields as $fieldKey => $fieldDetails) {
                            if (
                                empty($fieldDetails->subform)
                                && !empty($fieldDetails->api_name)
                                && in_array($fieldDetails->api_name, $fieldToShow)
                                && !empty($fieldDetails->view_type->create)
                                && $fieldDetails->view_type->create
                                && $fieldDetails->data_type !== 'ownerlookup'
                                && $fieldDetails->data_type !== 'fileupload'
                                && empty($fieldDetails->custom_field)
                            ) {
                                $fields[$fieldDetails->api_name] = (object) array(
                                    'display_label' => $fieldDetails->display_label,
                                    'length' => $fieldDetails->length,
                                    'visible' => $fieldDetails->visible,
                                    'json_type' => !empty($fieldDetails->json_type) ? $fieldDetails->json_type : null,
                                    'data_type' => $fieldDetails->data_type,
                                    'required' => $fieldDetails->required
                                );

                                if (!empty($fieldDetails->required) && $fieldDetails->required) {
                                    if ($fieldDetails->data_type === 'fileupload') {
                                        $requiredFileUploadFiles[] = $fieldDetails->api_name;
                                    } elseif ($fieldDetails->api_name !== 'Parent_Id') {
                                        $requiredFields[] = $fieldDetails->api_name;
                                    }
                                }
                                if (!empty($fieldDetails->unique) && count((array)$fieldDetails->unique)) {
                                    $uniqueFields[] = $fieldDetails->api_name;
                                }
                            }
                        }
                    }
                    uksort($fields, 'strnatcasecmp');
                    uksort($fileUploadFields, 'strnatcasecmp');
                    usort($requiredFields, 'strnatcasecmp');
                    usort($requiredFileUploadFiles, 'strnatcasecmp');

                    $layouts[$layoutValue->name] = (object) array(
                        'visible' => $layoutValue->visible,
                        'fields' => $fields,
                        'required' => $requiredFields,
                        'unique' => $uniqueFields,
                        'id' => $layoutValue->id,
                        'fileUploadFields' => $fileUploadFields,
                        'requiredFileUploadFields' => $requiredFileUploadFiles
                    );
                }
                uksort($layouts, 'strnatcasecmp');
                $response["layouts"] = $layouts;
            } else {
                wp_send_json_error(
                    $layoutsMetaResponse->status === 'error' ? $layoutsMetaResponse->message : 'Unknown',
                    400
                );
            }
            if (!empty($response['tokenDetails']) && $response['tokenDetails'] && !empty($queryParams->id)) {
                // var_dump($queryParams->formID, $queryParams->id);
                $response["queryModule"] = $queryParams->module;
                ZohoCRMHandler::_saveRefreshedToken($queryParams->formID, $queryParams->id, $response['tokenDetails'], $response);
            }
            wp_send_json_success($response, 200);
        } else {
            wp_send_json_error(
                __(
                    'Token expired',
                    'bitwpfzc'
                ),
                401
            );
        }
    }

    /**
     * Helps to refresh zoho crm access_token
     *
     * @param  Array $apiData Contains required data for refresh access token
     * @return JSON  $tokenDetails API token details
     */
    protected static function _refreshAccessToken($apiData)
    {
        if (
            empty($apiData->dataCenter)
            || empty($apiData->clientId)
            || empty($apiData->clientSecret)
            || empty($apiData->tokenDetails)
        ) {
            return false;
        }
        $tokenDetails = $apiData->tokenDetails;

        $dataCenter = $apiData->dataCenter;
        $apiEndpoint = "https://accounts.zoho.{$dataCenter}/oauth/v2/token";
        $requestParams = array(
            "grant_type" => "refresh_token",
            "client_id" => $apiData->clientId,
            "client_secret" => $apiData->clientSecret,
            "refresh_token" => $tokenDetails->refresh_token,
        );

        $apiResponse = HttpHelper::post($apiEndpoint, $requestParams);
        if (is_wp_error($apiResponse) || !empty($apiResponse->error)) {
            return false;
        }
        $tokenDetails->generates_on = \time();
        $tokenDetails->access_token = $apiResponse->access_token;
        return $tokenDetails;
    }

    /**
     * Save updated access_token to avoid unnecessary token generation
     *
     * @param Integer $fromID        ID of Integration related form
     * @param Integer $integrationID ID of Zoho crm Integration
     * @param Obeject $tokenDetails  refreshed token info
     *
     * @return null
     */
    protected static function _saveRefreshedToken($formID, $integrationID, $tokenDetails, $others = null)
    {
        if (empty($formID) || empty($integrationID)) {
            return;
        }

        $integrationHandler = new IntegrationHandler($formID, IpTool::getUserDetail());
        $zcrmDetails = $integrationHandler->getAIntegration($integrationID);

        // var_dump($zcrmDetails, $formID, $integrationID, $tokenDetails, $others);
        if (is_wp_error($zcrmDetails)) {
            return;
        }
        $newDetails = json_decode($zcrmDetails[0]->integration_details);

        $newDetails->tokenDetails = $tokenDetails;
        if (!empty($others['modules'])) {
            $newDetails->default->modules = $others['modules'];
        }
        if (!empty($others['layouts']) && !empty($others['queryModule'])) {
            $newDetails->default->layouts->{$others['queryModule']} = $others['layouts'];
        }

        $integrationHandler->updateIntegration($integrationID, $zcrmDetails[0]->integration_name, 'Zoho CRM', \json_encode($newDetails), 'integration');
    }

    public static function registerHooks()
    {
        add_filter('bitwpfzc_addRelatedList', array(__CLASS__, 'addRelatedList'), 10, 6);
    }

    public function execute(IntegrationHandler $integrationHandler, $integrationData, $fieldValues)
    {
        $integrationDetails = is_string($integrationData->integration_details) ? json_decode($integrationData->integration_details) : $integrationData->integration_details;

        $tokenDetails = $integrationDetails->tokenDetails;
        $module = $integrationDetails->module;
        $layout = $integrationDetails->layout;
        $fieldMap = $integrationDetails->field_map;
        $fileMap = $integrationDetails->upload_field_map;
        $actions = $integrationDetails->actions;
        $defaultDataConf = $integrationDetails->default;

        if (
            empty($tokenDetails)
            || empty($module)
            || empty($layout)
            || empty($fieldMap)
        ) {
            $error = new WP_Error('REQ_FIELD_EMPTY', __('module, layout, fields are required for zoho crm api', 'bitwpfzc'));
            Log::save($this->_formID, $this->_integrationID, 'record', 'validation', $error);
            return $error;
        }
        if (empty($defaultDataConf->layouts->{$module}->{$layout}->fields) || empty($defaultDataConf->modules->{$module})) {
            $error = new WP_Error('REQ_FIELD_EMPTY', __('module, layout, fields are required for zoho crm api', 'bitwpfzc'));
            Log::save($this->_formID, $this->_integrationID, 'record', 'validation', $error);
            return $error;
        }
        if ((intval($tokenDetails->generates_on) + (55 * 60)) < time()) {
            $requiredParams['clientId'] = $integrationDetails->clientId;
            $requiredParams['clientSecret'] = $integrationDetails->clientSecret;
            $requiredParams['dataCenter'] = $integrationDetails->dataCenter;
            $requiredParams['tokenDetails'] = $tokenDetails;
            $newTokenDetails = ZohoCRMHandler::_refreshAccessToken((object)$requiredParams);
            if ($newTokenDetails) {
                ZohoCRMHandler::_saveRefreshedToken($this->_formID, $this->_integrationID, $newTokenDetails);
                $tokenDetails = $newTokenDetails;
            }
        }

        $required = !empty($defaultDataConf->layouts->{$module}->{$layout}->required) ?
            $defaultDataConf->layouts->{$module}->{$layout}->required : [];
        $actions = $integrationDetails->actions;
        $recordApiHelper = new RecordApiHelper($tokenDetails);
        $zcrmApiResponse = $recordApiHelper->executeRecordApi(
            $this->_formID,
            $this->_integrationID,
            $defaultDataConf,
            $module,
            $layout,
            $fieldValues,
            $fieldMap,
            $actions,
            $required,
            $fileMap
        );
        if (is_wp_error($zcrmApiResponse)) {
            return $zcrmApiResponse;
        }
        return $zcrmApiResponse;
    }
}
