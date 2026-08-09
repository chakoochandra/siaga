<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Set_app_version_210 extends CI_Migration
{
	public function up()
	{
		$this->db->where('key', 'APP_VERSION')->update('tmst_configs', array('value' => '2.10'));
	}

	public function down()
	{
		// $this->db->where('key', 'APP_VERSION')->update('tmst_configs', array('value' => '2.0'));
	}
}