<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

use \Firebase\JWT\JWT;

if ( ! function_exists('verification_token'))
{
    function verification_token($access_token)
    {
        $CI =& get_instance();
        $CI->load->library('PHPRequests');
        
        $access_key = $_SERVER['ACCESS_KEY'];

        try {
            $bearer_token = explode(' ', $access_token);
            $decoded = JWT::decode($bearer_token[1], $access_key, array('HS256'));
    
            return $decoded->email;
        } catch (Exception $e){
            if($e->getMessage() == "Expired token"){
                $CI->response( [
                    'status' => false,
                    'message' => 'expired token'
                ], 401 );
            } else {
                $CI->response( [
                    'status' => false,
                    'message' => 'Access denied',
                    'error' => $e->getMessage()
                ], 401 );
            }
        }
    }
}
