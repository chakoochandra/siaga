<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Alter_trans_whatsapp_message_charset extends CI_Migration {

	public function up()
	{
		if ($this->db->table_exists('trans_whatsapp_message'))
		{
			$this->db->query("
				ALTER TABLE `trans_whatsapp_message`
				CONVERT TO CHARACTER SET utf8mb4
				COLLATE utf8mb4_general_ci
			");
		}
	}
}
