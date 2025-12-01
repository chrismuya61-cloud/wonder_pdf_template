<?php defined('BASEPATH') or exit('No direct script access allowed');

class Wonder_pdf_template extends AdminController {
    
    public function __construct(){
        parent::__construct();
        $this->load->helper('wonder_pdf_template');
    }

    public function settings() {
        if($this->input->post()){
            if(!empty($_FILES['sec_logo']['name'])){
                $config['upload_path'] = FCPATH . 'modules/wonder_pdf_template/assets/images/';
                $config['allowed_types'] = 'jpg|png|jpeg';
                $config['overwrite'] = true;
                $config['file_name'] = 'sec_logo';
                
                $this->load->library('upload', $config);
                
                if($this->upload->do_upload('sec_logo')) {
                    $data = $this->upload->data();
                    update_option('wonder_pdf_sec_logo', $data['file_name']);
                }
            }
            set_alert('success', 'Settings Saved');
            redirect(admin_url('wonder_pdf_template/settings'));
        }
        
        $this->load->view('wonder_pdf_template/manage_settings', ['title'=>'PDF Settings']);
    }
}
