<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_login_attempts extends CI_Migration {

	public function up()
	{
		if (!$this->db->table_exists('login_attempts'))
		{
			$prevDbDebug = $this->db->db_debug;
			$this->db->db_debug = FALSE;
			$created = $this->db->query("
				CREATE TABLE IF NOT EXISTS `login_attempts` (
					`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`ip_address` VARCHAR(45) NOT NULL,
					`login` VARCHAR(100) NOT NULL,
					`time` INT(11) UNSIGNED NULL DEFAULT NULL,
					PRIMARY KEY (`id`)
				) COLLATE='utf8_general_ci' ENGINE=InnoDB AUTO_INCREMENT=3
			");
			$this->db->db_debug = $prevDbDebug;

			if ($created === FALSE)
			{
				$dbError = $this->db->error();
				log_message('error', 'Migration 053: CREATE TABLE login_attempts failed — ' . $dbError['message']);
			}
		}
	}

	public function down()
	{
		// if ($this->db->table_exists('login_attempts'))
		// {
		// 	$this->dbforge->drop_table('login_attempts');
		// }
	}
}
