<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Update_enable_configs_category extends CI_Migration
{
	public function up()
	{
		$this->db->like('key', 'ENABLE_', 'after')
			->update('tmst_configs', ['category' => 4]);
	}

	public function down()
	{
		// $this->db->like('key', 'ENABLE_', 'after')
		// 	->update('tmst_configs', ['category' => 5]);
	}
}
