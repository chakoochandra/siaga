<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Set_app_version_200 extends CI_Migration
{
	public function up()
	{
		$this->db->where('key', 'APP_VERSION')->update('tmst_configs', array('value' => '2.0'));
		
		// Add configs if not exist (check by key)
		$configs = [
			'WA_BAS_TARGET' => 'string. no whatsapp untuk penerima notifikasi monitoring BAS. Multiple pisahkan dengan koma.',
			'WA_PRESENSI_TARGET' => 'string. no whatsapp untuk penerima notifikasi presensi sikep. Multiple pisahkan dengan koma.',
			'WA_SIDANG_TARGET' => 'string. no whatsapp untuk penerima notifikasi summary jadwal sidang. Multiple pisahkan dengan koma.',
		];
		
		foreach ($configs as $key => $note) {
			if (!$this->db->where('key', $key)->get('tmst_configs')->num_rows()) {
				$this->db->insert('tmst_configs', array(
					'key' => $key,
					'value' => '',
					'category' => 5,
					'note' => $note,
				));
			}
		}
	}
}