<?php
namespace BitCode\BITWPFZC\Admin\Gclid;

use BitCode\BITWPFZC\Core\Database\GclidModel;
use BitCode\BITWPFZC\Core\Util\IpTool;

final class Handler{
    private $userDetails;
    private $gclidModel;
    public function __construct()
    {
        $this->gclidModel = new GclidModel();
        $this->userDetails = IpTool::getUserDetail();
    }
    
    
    public function enable($data)
    {
        if (!isset($data->id)) {
            wp_send_json_error('Form Id cann\'t be empty');
        }
        $gclids = $this->gclidModel->get('status', ['form_id' => intval($data->id)]);
        if (is_wp_error($gclids)) {
            $insertStatus = $this->gclidModel->insert([
                'form_id' => intval($data->id),
                'status' => 1,
                'user_id' => $this->userDetails['id'],
                'user_ip' => $this->userDetails['ip'],
                'created_at' => $this->userDetails['time'],
                'updated_at' => $this->userDetails['time'],
            ]);
        } else if (isset($gclids[0]->status) && $gclids[0]->status == 1){
            wp_send_json_success('Gclid is already enabled');
        } else {
            $insertStatus = $this->gclidModel->update([
                'status' => 1,
                'user_id' => $this->userDetails['id'],
                'user_ip' => $this->userDetails['ip'],
                'created_at' => $this->userDetails['time'],
                'updated_at' => $this->userDetails['time'],
            ],
            ['form_id' => intval($data->id)]
            );
        }
        if (is_wp_error($insertStatus)) {
            wp_send_json_error($insertStatus->get_error_message());
        }
        wp_send_json_success('Gclid enabled successfully');
    }
    
    public function disable($data)
    {
        if (!isset($data->id)) {
            wp_send_json_error('Form Id cann\'t be empty');
        }
        $gclids = $this->gclidModel->get('status', ['form_id' => intval($data->id)]);
        if (is_wp_error($gclids)) {
            $insertStatus = $this->gclidModel->insert([
                'form_id' => intval($data->id),
                'status' => 0,
                'user_id' => $this->userDetails['id'],
                'user_ip' => $this->userDetails['ip'],
                'created_at' => $this->userDetails['time'],
                'updated_at' => $this->userDetails['time'],
            ]);
        } else if (isset($gclids[0]->status) && $gclids[0]->status == 0){
            wp_send_json_success('Gclid is already disabled');
        } else {
            $insertStatus = $this->gclidModel->update([
                'status' => 0,
                'user_id' => $this->userDetails['id'],
                'user_ip' => $this->userDetails['ip'],
                'created_at' => $this->userDetails['time'],
                'updated_at' => $this->userDetails['time'],
            ],
            ['form_id' => intval($data->id)]
            );
        }
        if (is_wp_error($insertStatus)) {
            wp_send_json_error($insertStatus->get_error_message());
        }
        wp_send_json_success('Gclid disabled successfully');
    }

    public function get_enabled_form_lsit()
    {
        $gclids = $this->gclidModel->get('form_id', ['status' => 1]);
        if (is_wp_error($gclids)) {
            return [];
        }
        $form = [];
        foreach ($gclids as $gclid) {
            $form[] = intval($gclid->form_id);
        }
        return $form;
    }
} 