<?php

class MY_Controller extends CI_Controller
{   
    private $upload_path='./product_images/';
    public function __construct() {
        parent::__construct();
        $this->load->model('user_model');
        $this->load->model('item_model');
        $this->load->model('Common_Model');
        $this->load->library('session');
        $this->load->library('user_agent');
        $this->load->database('default');
        $this->load->helper('url');

    }
    public function protect_var($var){
        return mysqli_real_escape_string($this->db->conn_id, $var);
    }
    public function getPostData($var, $default = '') {
        if (isset($var) && !empty($var)) {
            return $this->protect_var($var);
        }
        return $default;
    }
    public function get_client_ip() {
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {   //check ip from share internet
            $ip = $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {   //to check ip is pass from proxy
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            $ip = $_SERVER['REMOTE_ADDR'];
        }
        return $ip;
    }
    public function get_user_browser() {
        if ($this->agent->is_browser()){
            $agent = $this->agent->browser().' '.$this->agent->version();
        }
        elseif ($this->agent->is_robot()){
            $agent = $this->agent->robot();
        }
        elseif ($this->agent->is_mobile()){
            $agent = $this->agent->mobile();
        }
        else{
            $agent = 'Unidentified User Agent';
        }
        return $agent;
    }
    public function user_exits(){
        $requestedEmail  = $this->getPostData($_REQUEST['username']);
        $registeredEmail = $this->user_model->user_exits($requestedEmail);
        if($registeredEmail > 0){
            echo 'false';
        }else{
            echo 'true';
        }
    }
    
}
class MY_Auth_Controller extends MY_Controller
{
    function __construct()
    {
        parent::__construct();
        // check if logged_in
   }
public function text(){
    echo "hai";
   }
}