<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Alter_users_unique_fields_nullable extends CI_Migration
{

	public function up()
	{
		if ($this->db->table_exists('users')) {
			$prevDbDebug = $this->db->db_debug;
			$this->db->db_debug = FALSE;

			$result = $this->db->query("
				ALTER TABLE `users`
				MODIFY `email` VARCHAR(254) NULL DEFAULT NULL,
				MODIFY `username` VARCHAR(100) NULL DEFAULT NULL,
				MODIFY `activation_selector` VARCHAR(255) NULL DEFAULT NULL,
				MODIFY `forgotten_password_selector` VARCHAR(255) NULL DEFAULT NULL,
				MODIFY `remember_selector` VARCHAR(255) NULL DEFAULT NULL
			");

			$this->db->db_debug = $prevDbDebug;

			if ($result === FALSE) {
				$dbError = $this->db->error();
				log_message('error', 'Migration 041: ALTER TABLE users (unique fields nullable) failed — ' . $dbError['message']);
			}
		}
	}

	public function down()
	{
		// if ($this->db->table_exists('users'))
		// {
		// 	$this->db->query("
		// 		ALTER TABLE `users`
		// 		MODIFY `email` VARCHAR(254) NOT NULL,
		// 		MODIFY `username` VARCHAR(100) NOT NULL,
		// 		MODIFY `activation_selector` VARCHAR(255) NOT NULL,
		// 		MODIFY `forgotten_password_selector` VARCHAR(255) NOT NULL,
		// 		MODIFY `remember_selector` VARCHAR(255) NOT NULL
		// 	");
		// }
	}
}
