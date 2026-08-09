<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_tmst_configs extends CI_Migration {

	public function up()
	{
		if (!$this->db->table_exists('tmst_configs'))
		{
			$this->db->query("
				CREATE TABLE `tmst_configs` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`key` varchar(50) DEFAULT NULL,
					`value` varchar(250) DEFAULT NULL,
					`category` tinyint(4) DEFAULT NULL,
					`note` varchar(250) DEFAULT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `uk_key` (`key`)
				) ENGINE=InnoDB DEFAULT CHARSET=latin1
			");

			// Seed default config data
			$data = array(
				array('id' => 1, 'key' => 'APP_VERSION', 'value' => '1.21', 'category' => 5, 'note' => 'string. versi aplikasi'),
				array('id' => 2, 'key' => 'APP_NAME', 'value' => 'Aplikasiku', 'category' => 5, 'note' => 'string. nama aplikasi'),
				array('id' => 3, 'key' => 'APP_SHORT_NAME', 'value' => 'MY APP', 'category' => 5, 'note' => 'string. nama pendek aplikasi'),
				array('id' => 4, 'key' => 'SATKER_NAME', 'value' => 'Pengadilan ...', 'category' => 1, 'note' => 'string. nama satker'),
				array('id' => 5, 'key' => 'SATKER_ADDRESS', 'value' => 'Jl. ...', 'category' => 5, 'note' => 'string. alamat kantor'),
				array('id' => 6, 'key' => 'DIALOGWA_API_URL', 'value' => 'https://dialogwa.web.id/api', 'category' => 5, 'note' => 'string. url api dialogwa.id'),
				array('id' => 7, 'key' => 'DIALOGWA_TOKEN', 'value' => 'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpZCI6IjY1ZjNiMjIyZWY1MmJjMzc4MDYxM2U1OSIsInVzZXJuYW1lIjoiY2hhbmRyYSIsImlhdCI6MTcxNzc0Nzc4NywiZXhwIjo0ODczNTA3Nzg3fQ.KIqEs7rELJzVj2hk6WJqCiYy0T0Mz7G5vbiy4gFLRQ0', 'category' => 5, 'note' => 'string. token dialogwa.id'),
				array('id' => 8, 'key' => 'DIALOGWA_SESSION', 'value' => 'demo', 'category' => 5, 'note' => 'string. sesi dialogwa.id'),
				array('id' => 9, 'key' => 'START_TANGGAL_PUTUS_SETOR', 'value' => '', 'category' => 8, 'note' => 'date. tanggal awal hitung setor panmud (yyyy-mm-dd)'),
				array('id' => 10, 'key' => 'WA_TEST_TARGET', 'value' => '', 'category' => 5, 'note' => 'string. no whatsapp untuk tes menerima notifikasi. Multiple pisahkan dengan koma.'),
				array('id' => 11, 'key' => 'WA_BHT_TARGET', 'value' => '', 'category' => 5, 'note' => 'string. no whatsapp untuk menerima notifikasi reminder BHT. Multiple pisahkan dengan koma.'),
				array('id' => 12, 'key' => 'WA_KINERJA_TARGET', 'value' => '', 'category' => 5, 'note' => 'string. no whatsapp untuk penerima notifikasi kinerja perkara. Multiple pisahkan dengan koma.'),
			);
			$this->db->insert_batch('tmst_configs', $data);
		}
	}

	public function down()
	{
		// if ($this->db->table_exists('tmst_configs'))
		// {
		// 	$this->dbforge->drop_table('tmst_configs');
		// }
	}
}
