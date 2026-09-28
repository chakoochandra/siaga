<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_add_sidang_qr_barcode_configs extends CI_Migration
{
	public function up()
	{
		$configs = [
			'ENABLE_SIDANG_QR' => [
				'value' => '0',
				'category' => 4,
				'note' => 'boolean. 1 = lampirkan QR code (dari nomor perkara) ke notifikasi sidang ke pihak, 0 = nonaktif',
			],
			'ENABLE_SIDANG_BARCODE' => [
				'value' => '0',
				'category' => 4,
				'note' => 'boolean. 1 = lampirkan barcode Code39 (dari nomor perkara) ke notifikasi sidang ke pihak, 0 = nonaktif',
			],
		];

		foreach ($configs as $key => $row) {
			if (!$this->db->where('key', $key)->get('tmst_configs')->num_rows()) {
				$this->db->insert('tmst_configs', [
					'key' => $key,
					'value' => $row['value'],
					'category' => $row['category'],
					'note' => $row['note'],
				]);
			}
		}
	}

	public function down()
	{
		$this->db->where_in('key', ['ENABLE_SIDANG_QR', 'ENABLE_SIDANG_BARCODE'])
			->delete('tmst_configs');
	}
}
