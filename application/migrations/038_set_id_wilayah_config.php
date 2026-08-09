<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Set_id_wilayah_config extends CI_Migration
{
	public function up()
	{
		$this->db->insert('tmst_configs', array(
			'key' => 'ID_WILAYAH',
			'value' => '',
			'category' => '5',
			'note' => 'string. kode kabupaten'
		));
	}

	public function down()
	{
		// $this->db->where('key', 'ID_WILAYAH')->delete('tmst_configs');
	}
}
