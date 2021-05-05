<?php 
        
defined('BASEPATH') OR exit('No direct script access allowed');

use chriskacerguis\RestServer\RestController;
use Firebase\JWT\JWT;

class Welcome extends RestController {

	function __construct()
    {
        parent::__construct();
        $this->load->library('PHPRequests');
    }

    public function index_get()
    {
        $this->response([
            'status' => TRUE,
            'respons' => "Welcome...!"
        ], RestController::HTTP_OK);
    }
}
        
    /* End of file  api.php */
