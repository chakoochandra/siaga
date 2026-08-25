<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Add_show_online_to_tmst_web extends CI_Migration
{
	public function up()
	{
		$this->db->query("ALTER TABLE `tmst_web` ADD COLUMN `show_online` TINYINT(1) NOT NULL DEFAULT '0' AFTER `is_active`");
	}

	public function down()
	{
		// $this->db->query("ALTER TABLE `tmst_web` DROP COLUMN `show_online`");
	}
}
