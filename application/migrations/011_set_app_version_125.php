<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Set_app_version_125 extends CI_Migration {

	public function up()
	{
		$this->db->where('key', 'APP_VERSION')->update('tmst_configs', array('value' => '1.25'));
	}

	public function down()
	{
		// $this->db->where('key', 'APP_VERSION')->update('tmst_configs', array('value' => '1.24'));
	}
}
