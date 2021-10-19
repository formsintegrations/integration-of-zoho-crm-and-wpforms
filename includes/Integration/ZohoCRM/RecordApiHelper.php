<?php

/**
 * ZohoCrm Record Api
 *
 */

namespace BitCode\BITWPFZC\Integration\ZohoCRM;

use WP_Error;
use BitCode\BITWPFZC\Core\Util\HttpHelper;
use BitCode\BITWPFZC\Core\Util\DateTimeHelper;
use BitCode\BITWPFZC\Admin\Log\Handler as Log;

/**
 * Provide functionality for Record insert,upsert
 */
class RecordApiHelper
{
    protected $_defaultHeader;
    protected $_apiDomain;
    protected $_tokenDetails;

    public function __construct($tokenDetails)
    {
        $this->_defaultHeader['Authorization'] = "Zoho-oauthtoken {$tokenDetails->access_token}";
        $this->_apiDomain = \urldecode($tokenDetails->api_domain);
        $this->_tokenDetails = $tokenDetails;
    }

    public function upsertRecord($module, $data)
    {
        $insertRecordEndpoint = "{$this->_apiDomain}/crm/v2/{$module}/upsert";
        $data = \is_string($data) ? $data : \json_encode($data);
        return HttpHelper::post($insertRecordEndpoint, $data, $this->_defaultHeader);
    }

    public function insertRecord($module, $data)
    {
        $insertRecordEndpoint = "{$this->_apiDomain}/crm/v2/{$module}";
        $data = \is_string($data) ? $data : \json_encode($data);
        return HttpHelper::post($insertRecordEndpoint, $data, $this->_defaultHeader);
    }

    public function serachRecord($module, $searchCriteria)
    {
        $searchRecordEndpoint = "{$this->_apiDomain}/crm/v2/{$module}/search";
        return HttpHelper::get($searchRecordEndpoint, ["criteria" => "({$searchCriteria})"], $this->_defaultHeader);
    }

    public function executeRecordApi($formID, $integId, $defaultConf, $module, $layout, $fieldValues, $fieldMap, $actions, $required, $fileMap = [], $isRelated = false)
    {
        $fieldData = [];
        foreach ($fieldMap as $fieldKey => $fieldPair) {
            if (!empty($fieldPair->zohoFormField)) {
                if (empty($defaultConf->layouts->{$module}->{$layout}->fields->{$fieldPair->zohoFormField})) {
                    continue;
                }
                if ($fieldPair->formField === 'custom' && isset($fieldPair->customValue)) {
                    $fieldData[$fieldPair->zohoFormField] = $this->formatFieldValue($fieldPair->customValue, $defaultConf->layouts->{$module}->{$layout}->fields->{$fieldPair->zohoFormField});
                } elseif (strpos($fieldPair->formField, '=>') !== false) {
                    $fids = explode('=>', $fieldPair->formField);
                    
                    if (isset($fieldValues[$fids[0]][$fids[1]])) {
                        $formFieldValue = $fieldValues[$fids[0]][$fids[1]];
                    }
                    if (!is_null($formFieldValue)) {
                        $fieldData[$fieldPair->zohoFormField] = $this->formatFieldValue($formFieldValue, $defaultConf->layouts->{$module}->{$layout}->fields->{$fieldPair->zohoFormField});
                    }
                } else {
                    $fieldData[$fieldPair->zohoFormField] = $this->formatFieldValue($fieldValues[$fieldPair->formField], $defaultConf->layouts->{$module}->{$layout}->fields->{$fieldPair->zohoFormField});
                }
                if (empty($fieldData[$fieldPair->zohoFormField]) && \in_array($fieldPair->zohoFormField, $required)) {
                    $error = new WP_Error('REQ_FIELD_EMPTY', wp_sprintf(__('%s is required for zoho crm, %s module', 'bitwpfzc'), $fieldPair->zohoFormField, $module));
                    Log::save($formID, $integId, wp_json_encode(['type' => 'record', 'type_name' => 'field']), 'validation', wp_json_encode($error));
                    return $error;
                }
                if (!empty($fieldData[$fieldPair->zohoFormField])) {
                    $requiredLength = $defaultConf->layouts->{$module}->{$layout}->fields->{$fieldPair->zohoFormField}->length;
                    $currentLength = is_array($fieldData[$fieldPair->zohoFormField]) || is_object($fieldData[$fieldPair->zohoFormField]) ?
                        @count($fieldData[$fieldPair->zohoFormField])
                        : strlen($fieldData[$fieldPair->zohoFormField]);
                    if ($currentLength > $requiredLength) {
                        $error = new WP_Error('REQ_FIELD_LENGTH_EXCEEDED', wp_sprintf(__('zoho crm field %s\'s maximum length is %s, Given %s', 'bitwpfzc'), $fieldPair->zohoFormField, $module));
                        Log::save($formID, $integId, wp_json_encode(['type' => 'length', 'type_name' => 'field']), 'validation', wp_json_encode($error));
                        return $error;
                    }
                }
            }
        }
        if (!empty($defaultConf->layouts->{$module}->{$layout}->id)) {
            $fieldData['Layout']['id'] = $defaultConf->layouts->{$module}->{$layout}->id;
        }
        
        $requestParams['data'][] = (object) $fieldData;

        $recordApiResponse = '';
        $recordApiResponse = $this->insertRecord($module, (object) $requestParams);
        
        if (!empty($recordApiResponse->data)
            && !empty($recordApiResponse->data[0]->code)
            && $recordApiResponse->data[0]->code === 'SUCCESS'
        ) {
            Log::save($formID, $integId, wp_json_encode(['type' => 'record', 'type_name' => $module]), 'success', wp_json_encode($recordApiResponse));
        } else {
            Log::save($formID, $integId, wp_json_encode(['type' => 'record', 'type_name' => $module]), 'error', wp_json_encode($recordApiResponse));
        }

        return $recordApiResponse;
    }


    public function formatFieldValue($value, $formatSpecs)
    {
        if (empty($value)) {
            return '';
        }

        switch ($formatSpecs->json_type) {
        case 'jsonarray':
            $apiFormat = 'array';
            break;
        case 'jsonobject':
            $apiFormat = 'object';
            break;

        default:
            $apiFormat = $formatSpecs->json_type;
            break;
        }
        $formatedValue = '';
        $fieldFormat = gettype($value);
        if ($fieldFormat === $apiFormat && $formatSpecs->data_type !== 'datetime') {
            $formatedValue = $fieldFormat === 'string' ? html_entity_decode($value) : $value;
        } else {
            if ($apiFormat === 'array' || $apiFormat === 'object') {
                if ($fieldFormat === 'string') {
                    if (strpos($value, ',') === -1) {
                        $formatedValue = json_decode($value);
                    } else {
                        $formatedValue = explode(',', $value);
                    }
                    $formatedValue = is_null($formatedValue) && !is_null($value) ? [$value] : $formatedValue;
                } else {
                    $formatedValue = $value;
                }

                if ($apiFormat === 'object') {
                    $formatedValue = (object) $formatedValue;
                }
            } elseif ($apiFormat === 'string' && $formatSpecs->data_type !== 'datetime' && $formatSpecs->data_type !== 'date') {
                $formatedValue = !is_string($value) ? json_encode($value) : html_entity_decode($value);
            } elseif ($formatSpecs->data_type === 'datetime') {
                if (is_array($value)) {
                    if (isset($value['date'])) {
                        $value = $value['date'];
                        $date_format = 'm/d/Y';
                    } elseif (isset($value['time'])) {
                        $value = $value['time'];
                        $date_format = 'H:i A';
                    } elseif (isset($value['time']) && isset($value['date'])) {
                        $value = isset($value['date']) . ' ' . $value['time'];
                        $date_format = 'm/d/Y H:i A';
                    } else {
                        $value = '0000-00-00T00:00';
                        $date_format = 'Y-m-d\TH:i';
                    }
                }
                $dateTimeHelper = new DateTimeHelper();
                $formatedValue = $dateTimeHelper->getFormated($value, $date_format, wp_timezone(), 'Y-m-d\TH:i:sP', null);
            } elseif ($formatSpecs->data_type === 'date') {
                if (is_array($value)) {
                    if (isset($value['date'])) {
                        $value = $value['date'];
                        $date_format = 'm/d/Y';
                    } elseif (isset($value['time'])) {
                        $value = $value['time'];
                        $date_format = 'H:i A';
                    } elseif (isset($value['time']) && isset($value['date'])) {
                        $value = isset($value['date']) . ' ' . $value['time'];
                        $date_format = 'm/d/Y H:i A';
                    } else {
                        $value = '0000-00-00T00:00';
                        $date_format = 'Y-m-d\TH:i';
                    }
                }
                $dateTimeHelper = new DateTimeHelper();
                $formatedValue = $dateTimeHelper->getFormated($value, $date_format, wp_timezone(), 'Y-m-d', null);
            } else {
                $stringyfieldValue = !is_string($value) ? json_encode($value) : $value;

                switch ($apiFormat) {
                case 'double':
                    $formatedValue = (float) $stringyfieldValue;
                    break;

                case 'boolean':
                    $formatedValue = (bool) $stringyfieldValue;
                    break;

                case 'integer':
                    $formatedValue = (int) $stringyfieldValue;
                    break;
                default:
                    $formatedValue = $stringyfieldValue;
                    break;
                }
            }
        }
        if ($apiFormat === 'array' || $apiFormat === 'object') {
            $formatedValueLenght = is_countable($formatedValue) ? \count($formatedValue) : count(get_object_vars($formatedValue));
        } else {
            $formatedValueLenght =  \strlen($formatedValue);
        }
        if ($formatedValueLenght > $formatSpecs->length) {
            $formatedValue = $apiFormat === 'array' || $apiFormat === 'object' ? array_slice($formatedValue, 0, $formatSpecs->length) : substr($formatedValue, 0, $formatSpecs->length);
        }

        return $formatedValue;
    }
}
