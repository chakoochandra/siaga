<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Update_tmst_web_category extends CI_Migration {

	public function up()
	{
		// Update tmst_web records where category = 'Badilag' to 'Eselon1'
		$this->db->where('category', 'Badilag');
		$this->db->update('tmst_web', array('category' => 'Eselon1'));
	}

	public function down()
	{
		// Note: This downgrade is not perfectly reversible as it would affect
		// all records with category 'Eselon1', not just the ones we changed.
		// For a precise rollback, we would need to track which specific records were updated.
		// $this->db->where('category', 'Eselon1');
		// $this->db->update('tmst_web', array('category' => 'Badilag'));
	}
}