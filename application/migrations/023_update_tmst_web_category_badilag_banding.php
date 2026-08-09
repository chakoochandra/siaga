<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_tmst_web_category_badilag_banding extends CI_Migration {

	public function up()
	{
		// Get SATKER_BANDING value from tmst_configs
		$row = $this->db->where('key', 'SATKER_BANDING')->get('tmst_configs')->row();
		$satker_banding = $row ? $row->value : 'SATKER_BANDING';

		// Update tmst_web records where category = 'Eselon1' to 'Badilag'
		$this->db->where('category', 'Eselon1');
		$this->db->update('tmst_web', array('category' => 'Badilag'));

		// Update tmst_web records where category = 'Banding' to SATKER_BANDING constant value
		$this->db->where('category', 'Banding');
		$this->db->update('tmst_web', array('category' => $satker_banding));
	}

	public function down()
	{
		// // Get SATKER_BANDING value from tmst_configs
		// $row = $this->db->where('key', 'SATKER_BANDING')->get('tmst_configs')->row();
		// $satker_banding = $row ? $row->value : 'SATKER_BANDING';

		// // Revert 'Badilag' back to 'Eselon1'
		// $this->db->where('category', 'Badilag');
		// $this->db->update('tmst_web', array('category' => 'Eselon1'));

		// // Revert SATKER_BANDING back to 'Banding'
		// $this->db->where('category', $satker_banding);
		// $this->db->update('tmst_web', array('category' => 'Banding'));
	}
}