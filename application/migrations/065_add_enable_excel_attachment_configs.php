<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_enable_excel_attachment_configs extends CI_Migration
{
	public function up()
	{
		$configs = [
			'ENABLE_EXCEL_PUTUS_BELUM_SETOR_PERSONAL' => [
				'value' => '1',
				'category' => 4,
				'note' => 'boolean. 1 = kirim Excel attachment untuk notifikasi personal Putus Belum Setor, 0 = nonaktif',
			],
			'ENABLE_EXCEL_BAS_BELUM_UNGGAH_PERSONAL' => [
				'value' => '1',
				'category' => 4,
				'note' => 'boolean. 1 = kirim Excel attachment untuk notifikasi personal BAS Belum Unggah, 0 = nonaktif',
			],
			'ENABLE_EXCEL_RELAAS_BELUM_INPUT_UNGGAH_PERSONAL' => [
				'value' => '1',
				'category' => 4,
				'note' => 'boolean. 1 = kirim Excel attachment untuk notifikasi personal Relaas Belum Input/Unggah, 0 = nonaktif',
			],
			'ENABLE_EXCEL_RENCANA_BHT_PERSONAL' => [
				'value' => '1',
				'category' => 4,
				'note' => 'boolean. 1 = kirim Excel attachment untuk notifikasi personal Rencana BHT, 0 = nonaktif',
			],
			'ENABLE_EXCEL_SIDANG_TODAY_PERSONAL' => [
				'value' => '1',
				'category' => 4,
				'note' => 'boolean. 1 = kirim Excel attachment untuk notifikasi personal Jadwal Sidang, 0 = nonaktif',
			],
		];

		foreach ($configs as $key => $row) {
			if (!$this->db->where('key', $key)->get('tmst_configs')->num_rows()) {
				$this->db->insert('tmst_configs', array(
					'key' => $key,
					'value' => $row['value'],
					'category' => $row['category'],
					'note' => $row['note'],
				));
			}
		}
	}

	public function down()
	{
		$keys = [
			'ENABLE_EXCEL_PUTUS_BELUM_SETOR_PERSONAL',
			'ENABLE_EXCEL_BAS_BELUM_UNGGAH_PERSONAL',
			'ENABLE_EXCEL_RELAAS_BELUM_INPUT_UNGGAH_PERSONAL',
			'ENABLE_EXCEL_RENCANA_BHT_PERSONAL',
			'ENABLE_EXCEL_SIDANG_TODAY_PERSONAL',
		];
		$this->db->where_in('key', $keys)
			->delete('tmst_configs');
	}
}