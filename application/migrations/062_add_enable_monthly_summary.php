<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Add_enable_monthly_summary extends CI_Migration
{
	public function up()
	{
		$configs = [
			'ENABLE_MONTHLY_SUMMARY' => [
				'value' => '1',
				'category' => 4,
				'note' => 'boolean. 1 = aktifkan rekap bulanan, 0 = nonaktif',
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
		// $this->db->where('key', 'ENABLE_MONTHLY_SUMMARY')
		// 	->delete('tmst_configs');
	}
}
