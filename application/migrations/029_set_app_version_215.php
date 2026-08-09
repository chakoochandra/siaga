<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Set_app_version_215 extends CI_Migration
{
	public function up()
	{
		$this->db->where('key', 'APP_VERSION')->update('tmst_configs', array('value' => '2.15'));
	}

	public function down()
	{
		// $this->db->where('key', 'APP_VERSION')->update('tmst_configs', array('value' => '2.13'));
	}
}