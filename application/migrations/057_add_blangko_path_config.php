<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_blangko_path_config extends CI_Migration {

	public function up()
	{
		$this->db->query("INSERT INTO `tmst_configs` (`key`, `value`, `category`, `note`) VALUES ('BLANGKO_PATH', '', NULL, 'Path Relative ke folder blangko, contoh. aps_badilag/_blangko_abt') ON DUPLICATE KEY UPDATE `value` = VALUES(`value`)");
	}

	public function down()
	{
		// $this->db->where('key', 'BLANGKO_PATH')->delete('tmst_configs');
	}
}
