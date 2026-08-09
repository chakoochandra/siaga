<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_dialogwa_api_url extends CI_Migration {

	public function up()
	{
		$this->db->where('key', 'DIALOGWA_API_URL')->update('tmst_configs', array('value' => 'https://dialogwa.com/api'));
	}

	public function down()
	{
		// $this->db->where('key', 'DIALOGWA_API_URL')->update('tmst_configs', array('value' => 'https://dialogwa.web.id/api'));
	}
}
