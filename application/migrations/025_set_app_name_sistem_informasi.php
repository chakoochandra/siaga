<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Set_app_name_sistem_informasi extends CI_Migration
{
	public function up()
	{
		// Update APP_NAME only if it still has the default value
		$this->db
			->where('key', 'APP_NAME')
			->where('value', 'Aplikasiku')
			->update('tmst_configs', array(
				'value' => "Sistem\nInformasi Alert & Monitoring Kinerja Perkara"
			));

		// Update APP_SHORT_NAME only if it still has the default value
		$this->db
			->where('key', 'APP_SHORT_NAME')
			->where('value', 'MY APP')
			->update('tmst_configs', array(
				'value' => 'SIAGA'
			));

		// Update APP_VERSION
		$this->db
			->where('key', 'APP_VERSION')
			->update('tmst_configs', array(
				'value' => '2.12'
			));
	}

	public function down()
	{
		// // Revert APP_NAME
		// $this->db
		// 	->where('key', 'APP_NAME')
		// 	->where('value', "Sistem\nInformasi Alert & Monitoring Kinerja Perkara")
		// 	->update('tmst_configs', array(
		// 		'value' => 'Aplikasiku'
		// 	));

		// // Revert APP_SHORT_NAME
		// $this->db
		// 	->where('key', 'APP_SHORT_NAME')
		// 	->where('value', 'SIAGA')
		// 	->update('tmst_configs', array(
		// 		'value' => 'MY APP'
		// 	));

		// // Revert APP_VERSION
		// $this->db
		// 	->where('key', 'APP_VERSION')
		// 	->where('value', '2.12')
		// 	->update('tmst_configs', array(
		// 		'value' => '2.11'
		// 	));
	}
}
