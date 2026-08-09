<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Set_users_active_default_to_1 extends CI_Migration {

	public function up()
	{
		if ($this->db->query("SHOW TABLES LIKE 'users'")->num_rows() === 0) {
			return;
		}

		$prevDbDebug = $this->db->db_debug;
		$this->db->db_debug = FALSE;

		// 1. Set the DEFAULT value of active to 1 for future inserts
		$alter_sql = "ALTER TABLE `users` ALTER COLUMN `active` SET DEFAULT 1";

		$result = $this->db->query($alter_sql);

		if ($result === FALSE) {
			$dbError = $this->db->error();
			log_message('error', 'Migration 056: ALTER TABLE users ALTER active SET DEFAULT failed — ' . $dbError['message']);
		}

		// 2. Backfill any remaining users with NULL active
		$update_sql = "UPDATE `users` SET `active` = 1 WHERE `active` IS NULL";
		$this->db->query($update_sql);
		$affected = $this->db->affected_rows();

		$this->db->db_debug = $prevDbDebug;

		log_message('info', 'Migration 056: Set active default to 1 and backfilled ' . $affected . ' users with NULL active.');
	}

	public function down()
	{
		// Restore the original DEFAULT NULL
		$prevDbDebug = $this->db->db_debug;
		$this->db->db_debug = FALSE;

		$this->db->query("ALTER TABLE `users` ALTER COLUMN `active` SET DEFAULT NULL");

		$this->db->db_debug = $prevDbDebug;

		log_message('info', 'Migration 056: Reverted active DEFAULT to NULL.');
	}
}
