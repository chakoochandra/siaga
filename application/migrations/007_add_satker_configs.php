<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_satker_configs extends CI_Migration {

	public function up()
	{
		// Insert SATKER_BANDING config
		$this->db->query("
			INSERT INTO `tmst_configs` (`key`, `value`, `category`, `note`) 
			VALUES ('SATKER_BANDING', 'PTA ...', '1', 'string. satker tingkat banding')
		");
		
		// Insert SATKER_ESELON_1 config
		$this->db->query("
			INSERT INTO `tmst_configs` (`key`, `value`, `category`, `note`) 
			VALUES ('SATKER_ESELON_1', 'Badilag', '1', 'string. satker eselon 1')
		");
	}

	public function down()
	{
		// $this->db->where('key', 'SATKER_BANDING')->delete('tmst_configs');
		// $this->db->where('key', 'SATKER_ESELON_1')->delete('tmst_configs');
	}
}