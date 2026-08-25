<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Drop_icon_dimensions_from_tmst_web extends CI_Migration
{
	public function up()
	{
		$this->db->query("ALTER TABLE `tmst_web` DROP COLUMN `icon_width`, DROP COLUMN `icon_height`");
	}

	public function down()
	{
		// $this->db->query("ALTER TABLE `tmst_web` ADD COLUMN `icon_width` INT(11) NULL DEFAULT NULL AFTER `description`, ADD COLUMN `icon_height` INT(11) NULL DEFAULT NULL AFTER `icon_width`");
	}
}
