<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_trans_whatsapp_message extends CI_Migration {

	public function up()
	{
		if (!$this->db->table_exists('trans_whatsapp_message'))
		{
			$this->db->query("
				CREATE TABLE `trans_whatsapp_message` (
					`id` INT(11) NOT NULL AUTO_INCREMENT,
					`sent_time` DATETIME NOT NULL,
					`sent_by` VARCHAR(50) NOT NULL,
					`phone_number` VARCHAR(20) NOT NULL,
					`type` VARCHAR(50) NOT NULL,
					`reference` VARCHAR(50) NOT NULL,
					`perkara_id` VARCHAR(50) NOT NULL,
					`callback` VARCHAR(50) NOT NULL,
					`text` TEXT NOT NULL,
					`success` TINYINT(1) NOT NULL DEFAULT '0',
					`note` VARCHAR(100) NOT NULL,
					PRIMARY KEY (`id`) USING BTREE
				)
				COLLATE='utf8mb4_general_ci'
				ENGINE=InnoDB
				AUTO_INCREMENT=3052
			");
		}
	}

	public function down()
	{
		// if ($this->db->table_exists('trans_whatsapp_message'))
		// {
		// 	$this->dbforge->drop_table('trans_whatsapp_message');
		// }
	}
}
