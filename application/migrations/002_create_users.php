<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_users extends CI_Migration {

	public function up()
	{
		if (!$this->db->table_exists('users'))
		{
			$this->db->query("
				CREATE TABLE `users` (
					`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`username` VARCHAR(100) NULL DEFAULT NULL,
					`password` VARCHAR(255) NULL DEFAULT NULL,
					`email` VARCHAR(254) NULL DEFAULT NULL,
					`created_on` INT(11) UNSIGNED NULL DEFAULT NULL,
					`active` TINYINT(1) UNSIGNED NULL DEFAULT NULL,
					`nama_lengkap` VARCHAR(100) NULL DEFAULT NULL,
					`gelar_depan` VARCHAR(50) NULL DEFAULT NULL,
					`gelar_belakang` VARCHAR(50) NULL DEFAULT NULL,
					`nip` VARCHAR(18) NULL DEFAULT NULL,
					`nik` VARCHAR(16) NULL DEFAULT NULL,
					`birth_place_code` VARCHAR(13) NULL DEFAULT NULL,
					`birth_date` DATE NULL DEFAULT NULL,
					`jenis_kelamin` ENUM('L','P') NULL DEFAULT NULL,
					`tingkat_pendidikan_id` INT(4) NULL DEFAULT NULL,
					`jabatan_id` INT(4) NULL DEFAULT NULL,
					`golongan_ruang_id` INT(4) NULL DEFAULT NULL,
					`phone` VARCHAR(20) NULL DEFAULT NULL,
					`photo` VARCHAR(50) NULL DEFAULT NULL,
					`start_date` DATE NULL DEFAULT NULL,
					`ttd` VARCHAR(50) NULL DEFAULT NULL,
					`struktur_organisasi_id` INT(4) NULL DEFAULT NULL,
					`ip_address` VARCHAR(45) NULL DEFAULT NULL,
					`activation_selector` VARCHAR(255) NULL DEFAULT NULL,
					`activation_code` VARCHAR(255) NULL DEFAULT NULL,
					`forgotten_password_selector` VARCHAR(255) NULL DEFAULT NULL,
					`forgotten_password_code` VARCHAR(255) NULL DEFAULT NULL,
					`forgotten_password_time` INT(11) UNSIGNED NULL DEFAULT NULL,
					`remember_selector` VARCHAR(255) NULL DEFAULT NULL,
					`remember_code` VARCHAR(255) NULL DEFAULT NULL,
					`last_login` INT(11) UNSIGNED NULL DEFAULT NULL,
					PRIMARY KEY (`id`) USING BTREE,
					UNIQUE INDEX `uc_email` (`email`) USING BTREE,
					UNIQUE INDEX `uc_activation_selector` (`activation_selector`) USING BTREE,
					UNIQUE INDEX `uc_forgotten_password_selector` (`forgotten_password_selector`) USING BTREE,
					UNIQUE INDEX `uc_remember_selector` (`remember_selector`) USING BTREE,
					UNIQUE INDEX `uc_username` (`username`) USING BTREE,
					INDEX `FK_users_tref_jabatan` (`jabatan_id`) USING BTREE,
					INDEX `FK_users_tref_tingkat_pendidikan` (`tingkat_pendidikan_id`) USING BTREE,
					INDEX `FK_users_tref_golongan_ruang` (`golongan_ruang_id`) USING BTREE,
					INDEX `FK_users_tmst_struktur_organisasi` (`struktur_organisasi_id`)
				)
				COLLATE='utf8_general_ci'
				ENGINE=InnoDB
			");
		}
	}

	public function down()
	{
		// if ($this->db->table_exists('users'))
		// {
		// 	$this->dbforge->drop_table('users');
		// }
	}
}
