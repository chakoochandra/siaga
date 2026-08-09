<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Ensure_users_nullable_fields extends CI_Migration
{

	public function up()
	{
		if ($this->db->query("SHOW TABLES LIKE 'users'")->num_rows() === 0) {
			return;
		}

		$prevDbDebug = $this->db->db_debug;
		$this->db->db_debug = FALSE;

		$sql = "
			ALTER TABLE `users`
			MODIFY `password` VARCHAR(255) NULL DEFAULT NULL,
			MODIFY `email` VARCHAR(254) NULL DEFAULT NULL,
			MODIFY `created_on` INT(11) UNSIGNED NULL DEFAULT NULL,
			MODIFY `username` VARCHAR(100) NULL DEFAULT NULL,
			MODIFY `activation_selector` VARCHAR(255) NULL DEFAULT NULL,
			MODIFY `forgotten_password_selector` VARCHAR(255) NULL DEFAULT NULL,
			MODIFY `remember_selector` VARCHAR(255) NULL DEFAULT NULL
		";

		$result = $this->db->query($sql);

		$this->db->db_debug = $prevDbDebug;

		if ($result === FALSE) {
			$dbError = $this->db->error();
			log_message('error', 'Migration 050: ALTER TABLE users (ensure nullable fields) failed — ' . $dbError['message']);
			return;
		}

		$column = $this->db->query("SHOW COLUMNS FROM `users` LIKE 'email'")->row();

		if ($column && strtoupper($column->Null) !== 'YES') {
			log_message('error', 'Migration 050: users.email still NOT NULL after ALTER — please check manually.');
		}
	}

	public function down()
	{
		// Deliberately left empty. This is a repair migration for a bug in
		// 012/041; reverting it would just reintroduce the original bug's
		// symptom, and 012/041's own down() methods are already commented
		// out for the same reason.
	}
}