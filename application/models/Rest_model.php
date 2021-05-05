<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Rest_model extends CI_Model
{
  public function __construct()
  {
    
    $this->get_local_config('rest');
  }

  private function get_local_config($config_file)
  {
      if (!$this->load->config($config_file, false)) {
          $config = [];
          include __DIR__.'/'.$config_file.'.php';
          foreach ($config as $key => $value) {
              $this->config->set_item($key, $value);
          }
      }
  }


  public function key_verify()
  {
    $key_name = 'HTTP_'.strtoupper(str_replace('-', '_', $this->config->item('rest_key_name')));
    $raw = $this->db->where($this->config->item('rest_key_column'), $this->input->server($key_name))
                    ->get($this->config->item('rest_keys_table'));

    // return $this->db->last_query();
    return $_SERVER;
  }
}
