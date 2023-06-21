<?php

namespace BitCode\BITWPFZC\Admin\WPF;

use BitCode\BITWPFZC\Integration\IntegrationHandler;
use BitCode\BITWPFZC\Integration\Integrations;

final class Handler
{
    public function __construct()
    {
        //
    }

    public function get_a_form($data)
    {
        if (empty($data->formId)) {
            wp_send_json_error(__('Form doesn\'t exists', 'bitwpfzc'));
        }
        $form = \wpforms()->form->get($data->formId, ['content_only' => true]);
        $fieldDetails = $form['fields'];

        if (empty($fieldDetails)) {
            wp_send_json_error(__('Form doesn\'t exists', 'bitwpfzc'));
        }

        $fields = [];
        $fieldToExclude = ['divider','address','html','page-break', 'pagebreak', 'file-upload','payment-single','payment-multiple','payment-checkbox','payment-dropdown','payment-credit-card','payment-total'];
        foreach ($fieldDetails as  $id => $field) {
            if (in_array($field['type'], $fieldToExclude)) {
                continue;
            }
            if ($field['type'] == 'name' && $field['format'] != 'simple') {
                if ($field['format'] == 'first-last') {
                    $names = ['first' => 'First', 'last' => 'Last'];
                } else {
                    $names = ['first' => 'First', 'last' => 'Last', 'middle' => 'Middle'];
                }

                foreach ($names as $key => $value) {
                    $fields[] = [
                        'name' => "$id=>$key",
                        'type' => "text",
                        'label' => "$value " . $field['label'],
                    ];
                }

            } else {
                $fields[] = [
                        'name' => $id,
                        'type' => $field['type'],
                        'label' => $field['label'],
                    ];
            }
        }
        if (empty($fields)) {
            wp_send_json_error(__('Form doesn\'t exists any field', 'bitwpfzc'));
        }

        $responseData['fields'] = $fields;
        $integrationHandler = new IntegrationHandler($data->formId);
        $formIntegrations = $integrationHandler->getAllIntegration();
        if (!is_wp_error($formIntegrations)) {
            $integrations = [];
            foreach ($formIntegrations as $integrationkey => $integrationValue) {
                $integrationData = array(
                    'id' => $integrationValue->id,
                    'name' => $integrationValue->integration_name,
                    'type' => $integrationValue->integration_type,
                    'status' => $integrationValue->status,
                );
                $integrations[] = array_merge(
                    $integrationData,
                    is_string($integrationValue->integration_details) ?
                        (array) json_decode($integrationValue->integration_details) :
                        $integrationValue->integration_details
                );
            }
            $responseData['integrations'] = $integrations;
        }
        wp_send_json_success($responseData);
    }

    public static function wpforms_process_complete($fields, $entry, $form_data, $entry_id)
    {
        $form_id = $form_data['id'];
        if (!empty($form_id)) {
            Integrations::executeIntegrations($form_id, $entry['fields']);
        }
    }
}
