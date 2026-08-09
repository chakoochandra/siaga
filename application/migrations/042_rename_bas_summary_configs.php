<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Rename_bas_summary_configs extends CI_Migration
{
	public function up()
	{
		$renames = [
			'ENABLE_BAS_TRIWULAN_SUMMARY' => 'ENABLE_TRIWULAN_SUMMARY',
			'ENABLE_BAS_YEARLY_SUMMARY'   => 'ENABLE_YEARLY_SUMMARY',
		];

		foreach ($renames as $old_key => $new_key) {
			$existing = $this->db->where('key', $old_key)->get('tmst_configs')->row();
			if ($existing) {
				$new_exists = $this->db->where('key', $new_key)->get('tmst_configs')->num_rows();
				if (!$new_exists) {
					$this->db->where('key', $old_key)->update('tmst_configs', ['key' => $new_key]);
				}
			}
		}
	}

	public function down()
	{
		$renames = [
			'ENABLE_TRIWULAN_SUMMARY' => 'ENABLE_BAS_TRIWULAN_SUMMARY',
			'ENABLE_YEARLY_SUMMARY'   => 'ENABLE_BAS_YEARLY_SUMMARY',
		];

		foreach ($renames as $old_key => $new_key) {
			$existing = $this->db->where('key', $old_key)->get('tmst_configs')->row();
			if ($existing) {
				$new_exists = $this->db->where('key', $new_key)->get('tmst_configs')->num_rows();
				if (!$new_exists) {
					$this->db->where('key', $old_key)->update('tmst_configs', ['key' => $new_key]);
				}
			}
		}
	}
}
