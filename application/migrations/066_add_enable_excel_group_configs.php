<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_enable_excel_group_configs extends CI_Migration
{
	public function up()
	{
		$configs = [
			'ENABLE_EXCEL_PUTUS_BELUM_SETOR_GROUP' => [
				'value' => '1',
				'category' => 4,
				'note' => 'boolean. 1 = kirim Excel attachment untuk notifikasi group Putus Belum Setor, 0 = nonaktif',
			],
			'ENABLE_EXCEL_BAS_BELUM_UNGGAH_GROUP' => [
				'value' => '1',
				'category' => 4,
				'note' => 'boolean. 1 = kirim Excel attachment untuk notifikasi group BAS Belum Unggah, 0 = nonaktif',
			],
			'ENABLE_EXCEL_RELAAS_BELUM_INPUT_UNGGAH_GROUP' => [
				'value' => '1',
				'category' => 4,
				'note' => 'boolean. 1 = kirim Excel attachment untuk notifikasi group Relaas Belum Input/Unggah, 0 = nonaktif',
			],
			'ENABLE_EXCEL_RENCANA_BHT_GROUP' => [
				'value' => '1',
				'category' => 4,
				'note' => 'boolean. 1 = kirim Excel attachment untuk notifikasi group Rencana BHT, 0 = nonaktif',
			],
			'ENABLE_EXCEL_SIDANG_TODAY_GROUP' => [
				'value' => '1',
				'category' => 4,
				'note' => 'boolean. 1 = kirim Excel attachment untuk notifikasi group Jadwal Sidang, 0 = nonaktif',
			],
			'ENABLE_EXCEL_EDOC_PUTUS_TODAY_GROUP' => [
				'value' => '1',
				'category' => 4,
				'note' => 'boolean. 1 = kirim Excel attachment untuk notifikasi group Perkara Akan Putus, 0 = nonaktif',
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
			'ENABLE_EXCEL_PUTUS_BELUM_SETOR_GROUP',
			'ENABLE_EXCEL_BAS_BELUM_UNGGAH_GROUP',
			'ENABLE_EXCEL_RELAAS_BELUM_INPUT_UNGGAH_GROUP',
			'ENABLE_EXCEL_RENCANA_BHT_GROUP',
			'ENABLE_EXCEL_SIDANG_TODAY_GROUP',
			'ENABLE_EXCEL_EDOC_PUTUS_TODAY_GROUP',
		];
		$this->db->where_in('key', $keys)
			->delete('tmst_configs');
	}
}