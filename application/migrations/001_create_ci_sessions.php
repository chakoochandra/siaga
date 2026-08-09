<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_ci_sessions extends CI_Migration {

	public function up()
	{
		if (!$this->db->table_exists('ci_sessions'))
		{
			$this->db->query("
				CREATE TABLE `ci_sessions` (
				  `id` VARCHAR(128) NOT NULL,
				  `ip_address` VARCHAR(45) NOT NULL,
				  `timestamp` INT(10) UNSIGNED NOT NULL DEFAULT '0',
				  `data` BLOB NOT NULL,
				  PRIMARY KEY (`id`, `ip_address`),
				  INDEX `ci_sessions_timestamp` (`timestamp`)
				)
				COLLATE='latin1_swedish_ci'
				ENGINE=InnoDB
			");
		}
	}

	public function down()
	{
		// if ($this->db->table_exists('ci_sessions'))
		// {
		// 	$this->dbforge->drop_table('ci_sessions');
		// }
	}
}
