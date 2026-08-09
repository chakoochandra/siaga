<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_tref_holiday_type extends CI_Migration
{

	public function up()
	{
		if (!$this->db->table_exists('tref_holiday_type')) {
			$this->db->query("
				CREATE TABLE `tref_holiday_type` (
					`id` int(4) NOT NULL AUTO_INCREMENT,
					`jenis_libur` varchar(50) NOT NULL,
					`pengurang_cuti_tahunan` tinyint(1) NOT NULL DEFAULT '0',
					`keterangan` varchar(100) DEFAULT NULL,
					`aktif` tinyint(1) NOT NULL DEFAULT '1',
					PRIMARY KEY (`id`) USING BTREE
				) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=latin1
			");

			$this->db->query("
				INSERT INTO `tref_holiday_type` (`id`, `jenis_libur`, `pengurang_cuti_tahunan`, `keterangan`, `aktif`) VALUES
					(1, 'Libur Nasional Tanggal Tetap', 0, 'Tanggal merah tetap tiap tahun', 1),
					(2, 'Libur Nasional Tanggal Berubah', 0, 'Tanggal merah berubah tiap tahun', 1),
					(3, 'Cuti Bersama Mengurangi Cuti Tahunan', 1, 'Cuti bersama yang mengurangi cuti tahunan', 1),
					(4, 'Cuti Bersama Tidak Mengurangi Cuti Tahunan', 0, 'Cuti bersama yang tidak mengurangi cuti tahunan', 0)
			");
		}
	}

	public function down()
	{
		// if ($this->db->table_exists('tref_holiday_type'))
		// {
		// 	$this->dbforge->drop_table('tref_holiday_type');
		// }
	}
}
