<?php 
        
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;

class Api extends RestController {

    protected $headers = [];
    protected $options = [];
    protected $services = [];
    protected $access_key='ACCESS_KEY';
    protected $refresh_key='REFRESH_KEY';
    protected $access_token;
    protected $host = '';

	function __construct()
    {
        parent::__construct();
        $this->load->library('PHPRequests');
        $this->load->library('user_agent');
        $this->load->helper('app');
        $this->load->model('rest_model');
        
        if (isset($_SERVER['HTTP_ACCESS_TOKEN'])) {
            $this->access_token = $_SERVER['HTTP_ACCESS_TOKEN'];
        }
    }

    public function welcome_get()
    {
        $this->response([
            'status' => TRUE,
            'respons' => "Welcome...!"
        ], RestController::HTTP_OK);
    }
    public function index_get()
    {
        $response = Requests::get($this->request_url(),$this->headers,$this->options);

        $this->response([
            'status' => TRUE,
            'respons' => json_decode($response->body)
        ], RestController::HTTP_OK); 
        
    }

    public function index_post()
    {
        $this->options = $this->input->post();

        if (!empty($this->input->post())) {
            $this->options = array(
                'userlogin' => $this->input->post('userlogin'),
                'usersecret' => $this->input->post('usersecret'),
            );
        }

        $response = Requests::post($this->request_url(),$this->headers,$this->options);

        $this->response([
            'status' => TRUE,
            'respons' => json_decode($response->body)
        ], RestController::HTTP_OK); 
    }

    private function services($srv='')
    {
        
        $serviceslist = $this->db->get_where('services',['name'=>$srv,'isActive'=>1]);
        $services = $serviceslist->row_array();
        if ($serviceslist->num_rows()==0) {
            $this->response([
                'status' => FALSE
            ], RestController::HTTP_NOT_FOUND); 
        }
        
        $headers_data = $this->db
                        ->select('key,value')
                        ->get_where(
                            'header',
                            ['services_id'=> $services['id']]
                        )
                        ->result_array();
        // dd($services['zona']);
        
        switch ($services['zona']) {
            case 'public':
                # code...
                break;

            case 'private':
                if ($this->access_token) {
                    $this->verification_token($this->access_token);
                }else {
                    $this->response( [
                        'status' => false,
                        'message' => 'Access denied',
                        'error' => 'token is null'
                    ], 401 );
                }
                break;

            case 'protected':
                $this->rest_model->key_verify();
                break;
            
            default:
                # code...
                break;
        }

        if (!empty($headers_data)) {
            foreach ($headers_data as $row) {
                $this->headers = [ $row['key'] => $row['value'] ];
            }
        }
        
        $this->services = $services;
    }
    
    private function request_url()
    {
        $request_url ='';
        $host='';

        $_url = str_replace('api/','',$this->uri->uri_string());
        $segment = explode('/',$_url);

        for ($i=0; $i < count($segment); $i++) { 
            if ($i == (count($segment)-1)) {
                $request_url .= $segment[$i];
            }else {
                $request_url .= $segment[$i].'/';
            }
        }

        if ($segment[0]=='api') {
            $this->response([
                'status' => FALSE
            ], RestController::HTTP_BAD_REQUEST); 
        }
        // dd($request_url);
        $this->services($segment[0]);
        $host = $this->services['host'];
        // echo json_encode($list_host);
        // echo json_encode($segment[0]);
        // exit();
        $path = $host.$request_url;
        // dd($path);
        return $path;
    }

    private function verification_token($access_token)
    {
        $access_key = $this->access_key;
        try {
            // $bearer_token = explode(' ', $access_token);
            // $decoded = JWT::decode($bearer_token[1], $access_key, array('HS256'));
            $decoded = JWT::decode($access_token, $access_key, array('HS256'));

            // $tes = array(
            //     'iat'=> date('Y-m-d H:i:s', $decoded->iat),
            //     'exp'=> date('Y-m-d H:i:s', $decoded->exp)
            // );
            // dd($tes);


            return $decoded->userlogin;
        } catch (Exception $e){
            $this->response( [
                'status' => false,
                'message' => 'Access denied',
                'error' => $e->getMessage()
            ], 401 );
        }
    }

}
        
    /* End of file  api.php */
