<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_tmst_holiday extends CI_Migration {

	public function up()
	{
		if (!$this->db->table_exists('tmst_holiday'))
		{
			$this->db->query("
				CREATE TABLE `tmst_holiday` (
					`id` INT(4) NOT NULL AUTO_INCREMENT,
					`jenis_libur_id` INT(4) NOT NULL,
					`tanggal` DATE NOT NULL,
					`nama` VARCHAR(100) NOT NULL,
					PRIMARY KEY (`id`) USING BTREE,
					UNIQUE INDEX `Index_2` (`tanggal`),
					INDEX `FK_tmst_hari_libur_manual_tref_jenis_libur` (`jenis_libur_id`),
					CONSTRAINT `FK_tmst_hari_libur_manual_tref_jenis_libur` FOREIGN KEY (`jenis_libur_id`) REFERENCES `tref_holiday_type` (`id`)
				)
				COLLATE='latin1_swedish_ci'
				ENGINE=InnoDB
				AUTO_INCREMENT=51
			");
		}
	}

	public function down()
	{
		// if ($this->db->table_exists('tmst_holiday'))
		// {
		// 	$this->dbforge->drop_table('tmst_holiday');
		// }
	}
}
