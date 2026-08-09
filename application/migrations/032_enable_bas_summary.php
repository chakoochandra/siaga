<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Enable_bas_summary extends CI_Migration
{
	public function up()
	{
		$configs = [
			'ENABLE_BAS_TRIWULAN_SUMMARY' => [
				'value' => '1',
				'category' => 5,
				'note' => 'boolean. 1 = aktifkan rekap BAS triwulan, 0 = nonaktif',
			],
			'ENABLE_BAS_YEARLY_SUMMARY' => [
				'value' => '0',
				'category' => 5,
				'note' => 'boolean. 1 = aktifkan rekap BAS tahunan, 0 = nonaktif',
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
		// $this->db->where_in('key', ['ENABLE_BAS_TRIWULAN_SUMMARY', 'ENABLE_BAS_YEARLY_SUMMARY'])
		// 	->delete('tmst_configs');
	}
}
