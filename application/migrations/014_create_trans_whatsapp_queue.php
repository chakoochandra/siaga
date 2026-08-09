<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_Trans_whatsapp_queue extends CI_Migration {

	public function up()
	{
		if (!$this->db->table_exists('trans_whatsapp_queue'))
		{
			$this->db->query("
				CREATE TABLE `trans_whatsapp_queue` (
					`id` INT(11) NOT NULL AUTO_INCREMENT,
					`type` VARCHAR(50) NOT NULL,
					`job_key` VARCHAR(255) NOT NULL,
					`target` VARCHAR(50) NOT NULL,
					`text` TEXT NOT NULL,
					`file_path` TEXT NULL,
					`priority` TINYINT(4) NOT NULL DEFAULT '1',
					`status` ENUM('pending','processing','completed','failed','invalid_number') NOT NULL DEFAULT 'pending',
					`attempts` TINYINT(4) NOT NULL DEFAULT '0',
					`created_at` DATETIME NOT NULL,
					`processed_at` DATETIME NULL DEFAULT NULL,
					`sent_response` VARCHAR(250) NULL DEFAULT NULL,
					PRIMARY KEY (`id`),
					INDEX `idx_status` (`status`),
					INDEX `idx_job_key` (`job_key`(191)),
					INDEX `idx_created_at` (`created_at`)
				)
				COLLATE='utf8mb4_general_ci'
				ENGINE=InnoDB
				AUTO_INCREMENT=133
			");
		}
	}
}
