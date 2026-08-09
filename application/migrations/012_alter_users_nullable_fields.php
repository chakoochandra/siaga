<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Alter_users_nullable_fields extends CI_Migration
{

	public function up()
	{
		if ($this->db->table_exists('users')) {
			$prevDbDebug = $this->db->db_debug;
			$this->db->db_debug = FALSE;

			$result = $this->db->query("
				ALTER TABLE `users`
				MODIFY `password` VARCHAR(255) NULL DEFAULT NULL,
				MODIFY `email` VARCHAR(254) NULL DEFAULT NULL,
				MODIFY `created_on` INT(11) UNSIGNED NULL DEFAULT NULL
			");

			$this->db->db_debug = $prevDbDebug;

			if ($result === FALSE) {
				$dbError = $this->db->error();
				log_message('error', 'Migration 012: ALTER TABLE users (nullable fields) failed — ' . $dbError['message']);
			}
		}
	}

	public function down()
	{
		// if ($this->db->table_exists('users'))
		// {
		// 	$this->db->query("
		// 		ALTER TABLE `users`
		// 		MODIFY `password` VARCHAR(255) NOT NULL,
		// 		MODIFY `email` VARCHAR(254) NOT NULL,
		// 		MODIFY `created_on` INT(11) UNSIGNED NOT NULL
		// 	");
		// }
	}
}
