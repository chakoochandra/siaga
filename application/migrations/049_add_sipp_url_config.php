<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_sipp_url_config extends CI_Migration {

	public function up()
	{
		$this->db->insert('tmst_configs', [
			'key' => 'SIPP_URL',
			'value' => NULL,
			'category' => 5,
			'note' => 'string. contoh: http://192.168.1.14/sipp'
		]);
	}

	public function down()
	{
		$this->db->where('key', 'SIPP_URL')->delete('tmst_configs');
	}
}