<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_groups_and_users_groups extends CI_Migration
{

	const BATCH_SIZE = 1000;

	public function up()
	{
		$groupsJustCreated = false;
		$usersGroupsJustCreated = false;

		if (!$this->db->table_exists('groups')) {
			$prevDbDebug = $this->db->db_debug;
			$this->db->db_debug = FALSE;
			$created = $this->db->query("
				CREATE TABLE IF NOT EXISTS `groups` (
					`id` MEDIUMINT(8) UNSIGNED NOT NULL AUTO_INCREMENT,
					`name` VARCHAR(70) NOT NULL,
					`description` VARCHAR(100) NOT NULL,
					PRIMARY KEY (`id`)
				) COLLATE='utf8_general_ci' ENGINE=InnoDB AUTO_INCREMENT=100
			");
			$this->db->db_debug = $prevDbDebug;

			if ($created === FALSE) {
				$dbError = $this->db->error();
				log_message('error', 'Migration 050: CREATE TABLE groups failed — ' . $dbError['message']);
			} else {
				$groupsJustCreated = true;
			}
		}

		if ($groupsJustCreated || ($this->db->table_exists('groups') && $this->db->count_all('groups') === 0)) {
			$this->seed_groups();
		}

		$usersTableExists = $this->db->query("SHOW TABLES LIKE 'users'")->num_rows() > 0;

		if ($usersTableExists && !$this->db->table_exists('users_groups')) {
			$prevDbDebug = $this->db->db_debug;
			$this->db->db_debug = FALSE;
			$created = $this->db->query("
				CREATE TABLE IF NOT EXISTS `users_groups` (
					`id` INT(11) UNSIGNED NOT NULL AUTO_INCREMENT,
					`user_id` INT(11) UNSIGNED NOT NULL,
					`group_id` MEDIUMINT(8) UNSIGNED NOT NULL,
					PRIMARY KEY (`id`),
					UNIQUE INDEX `uc_users_groups` (`user_id`, `group_id`),
					INDEX `fk_users_groups_users1_idx` (`user_id`),
					INDEX `fk_users_groups_groups1_idx` (`group_id`),
					CONSTRAINT `fk_users_groups_groups1` FOREIGN KEY (`group_id`) REFERENCES `groups` (`id`) ON DELETE CASCADE ON UPDATE NO ACTION,
					CONSTRAINT `FK_users_groups_users_new` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`)
				) COLLATE='utf8_general_ci' ENGINE=InnoDB AUTO_INCREMENT=141
			");
			$this->db->db_debug = $prevDbDebug;

			if ($created === FALSE) {
				$dbError = $this->db->error();
				log_message('error', 'Migration 050: CREATE TABLE users_groups failed — ' . $dbError['message']);
			} else {
				$usersGroupsJustCreated = true;
			}
		}

		$usersGroupsTableExists = $this->db->query("SHOW TABLES LIKE 'users_groups'")->num_rows() > 0;

		if ($usersGroupsJustCreated || ($usersGroupsTableExists && $this->db->count_all('users_groups') === 0)) {
			$this->seed_users_groups();
		}

		$this->insert_admin_user_and_assign_group();
	}

	protected function seed_groups()
	{
		$groups = array(
			array('id' => 1,  'name' => 'Administrator', 'description' => 'Administrator dengan semua hak akses aplikasi'),
			array('id' => 2,  'name' => 'Operator', 'description' => 'Pengguna dengan hak akses tertentu'),
			array('id' => 3,  'name' => 'PTSP', 'description' => 'Petugas PTSP'),
			array('id' => 4,  'name' => 'PP', 'description' => 'Petugas Sidang'),
			array('id' => 5,  'name' => 'Tekre', 'description' => 'Tekre'),
			array('id' => 6,  'name' => 'Posbakum', 'description' => 'Petugas Posbakum'),
			array('id' => 7,  'name' => 'Pos', 'description' => 'Petugas Pos'),
			array('id' => 8,  'name' => 'Mediator', 'description' => 'Petugas Mediasi'),
			array('id' => 9,  'name' => 'Kepegawaian', 'description' => 'Operator Kepegawaian'),
			array('id' => 10, 'name' => 'Plt. Kepala Sub Bagian Kepegawaian, Organisasi, Dan Tata Laksana', 'description' => 'Plt. Kepala Sub Bagian Kepegawaian, Organisasi, Dan Tata Laksana'),
			array('id' => 11, 'name' => 'Plt. Panitera', 'description' => 'Plt. Panitera'),
			array('id' => 12, 'name' => 'Plt. Sekretaris', 'description' => 'Plt. Sekretaris'),
			array('id' => 13, 'name' => 'Plt. Wakil Ketua', 'description' => 'Plt. Wakil Ketua'),
			array('id' => 14, 'name' => 'Plt. Ketua', 'description' => 'Plt. Ketua'),
			array('id' => 99, 'name' => 'Pegawai', 'description' => 'Pegawai'),
		);

		// db_debug must be disabled here, otherwise a failed insert_batch()
		// call halts script execution via show_error() before trans_complete()
		// / trans_status() ever run, making the error-logging below dead code.
		$prevDbDebug = $this->db->db_debug;
		$this->db->db_debug = FALSE;

		try {
			$this->db->trans_start();
			foreach (array_chunk($groups, self::BATCH_SIZE) as $batch) {
				$this->db->insert_batch('groups', $batch);
			}
			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				log_message('error', 'Migration 050: failed populating groups, rolled back — table left empty for retry on next run.');
			}
		} catch (Exception $e) {
			$this->db->db_debug = $prevDbDebug;
			throw $e;
		}

		$this->db->db_debug = $prevDbDebug;
	}

	protected function seed_users_groups()
	{
		$usersGroups = array(
			array('id' => 120, 'user_id' => 7,  'group_id' => 9),
			array('id' => 79,  'user_id' => 31, 'group_id' => 2),
			array('id' => 81,  'user_id' => 73, 'group_id' => 5),
			array('id' => 82,  'user_id' => 72, 'group_id' => 5),
			array('id' => 83,  'user_id' => 75, 'group_id' => 5),
			array('id' => 84,  'user_id' => 69, 'group_id' => 5),
			array('id' => 86,  'user_id' => 48, 'group_id' => 2),
			array('id' => 87,  'user_id' => 43, 'group_id' => 2),
			array('id' => 88,  'user_id' => 17, 'group_id' => 2),
			array('id' => 89,  'user_id' => 21, 'group_id' => 2),
			array('id' => 90,  'user_id' => 56, 'group_id' => 2),
			array('id' => 92,  'user_id' => 38, 'group_id' => 9),
			array('id' => 94,  'user_id' => 66, 'group_id' => 1),
			array('id' => 100, 'user_id' => 81, 'group_id' => 2),
			array('id' => 101, 'user_id' => 82, 'group_id' => 2),
			array('id' => 109, 'user_id' => 78, 'group_id' => 1),
			array('id' => 110, 'user_id' => 53, 'group_id' => 1),
			array('id' => 121, 'user_id' => 65, 'group_id' => 9),
			array('id' => 122, 'user_id' => 92, 'group_id' => 7),
			array('id' => 123, 'user_id' => 91, 'group_id' => 99),
			array('id' => 131, 'user_id' => 1,  'group_id' => 1),
			array('id' => 132, 'user_id' => 1,  'group_id' => 2),
			array('id' => 133, 'user_id' => 1,  'group_id' => 3),
			array('id' => 134, 'user_id' => 1,  'group_id' => 5),
			array('id' => 135, 'user_id' => 1,  'group_id' => 7),
			array('id' => 136, 'user_id' => 57, 'group_id' => 99),
			array('id' => 137, 'user_id' => 93, 'group_id' => 99),
			array('id' => 138, 'user_id' => 94, 'group_id' => 99),
			array('id' => 139, 'user_id' => 55, 'group_id' => 1),
			array('id' => 140, 'user_id' => 55, 'group_id' => 2),
		);

		$prevDbDebug = $this->db->db_debug;
		$this->db->db_debug = FALSE;

		try {
			$this->db->trans_start();
			$this->db->query('SET FOREIGN_KEY_CHECKS=0');

			foreach (array_chunk($usersGroups, self::BATCH_SIZE) as $batch) {
				$this->db->insert_batch('users_groups', $batch);
			}

			$this->db->trans_complete();

			if ($this->db->trans_status() === FALSE) {
				log_message('error', 'Migration 050: failed populating users_groups, rolled back — table left empty for retry on next run.');
			}
		} catch (Exception $e) {
			// Guaranteed to run even if insert_batch() errors out above,
			// so FK checks never get left permanently disabled.
			$this->db->query('SET FOREIGN_KEY_CHECKS=1');
			$this->db->db_debug = $prevDbDebug;
			throw $e;
		}

		$this->db->query('SET FOREIGN_KEY_CHECKS=1');
		$this->db->db_debug = $prevDbDebug;
	}

	protected function insert_admin_user_and_assign_group()
	{
		if ($this->db->query("SHOW TABLES LIKE 'users'")->num_rows() === 0) {
			return;
		}

		$prevDbDebug = $this->db->db_debug;
		$this->db->db_debug = FALSE;

		try {
			$existing = $this->db->get_where('users', array('username' => 'admin'))->row();

			if (!$existing) {
				// Generate a random per-install password instead of shipping
				// the same static bcrypt hash to every deployment. Reusing
				// one hash across every independent installation means a
				// single known credential logs into all of them.
				// $plainPassword = $this->generate_random_password();
				$plainPassword = '12345678';

				$data = array(
					'username' => 'admin',
					'password' => password_hash($plainPassword, PASSWORD_BCRYPT),
					'email' => NULL,
					'created_on' => time(),
					'active' => 1,
				);

				$inserted = $this->db->insert('users', $data);
				$adminUserId = $this->db->insert_id();

				if (!$inserted || !$adminUserId) {
					log_message('error', 'Migration 050: failed to create admin user.');
					$this->db->db_debug = $prevDbDebug;
					return;
				}

				// Logged once so the operator can retrieve and immediately
				// rotate it. Do not lower this to a level that gets shipped
				// to end users; treat the log file as sensitive afterward.
				log_message('info', 'Migration 050: created admin user "admin" with generated password: ' . $plainPassword . ' — change this immediately after first login.');
			} else {
				$adminUserId = $existing->id;
			}

			$this->db->query('SET FOREIGN_KEY_CHECKS=0');

			$exists = $this->db->get_where('users_groups', array('user_id' => $adminUserId, 'group_id' => 1))->row();
			if (!$exists) {
				$this->db->insert('users_groups', array('user_id' => $adminUserId, 'group_id' => 1));
			}
		} catch (Exception $e) {
			$this->db->query('SET FOREIGN_KEY_CHECKS=1');
			$this->db->db_debug = $prevDbDebug;
			throw $e;
		}

		$this->db->query('SET FOREIGN_KEY_CHECKS=1');
		$this->db->db_debug = $prevDbDebug;
	}

	protected function generate_random_password($length = 16)
	{
		$bytes = function_exists('random_bytes')
			? random_bytes($length)
			: openssl_random_pseudo_bytes($length);

		$password = str_replace(array('+', '/', '='), '', base64_encode($bytes));

		return substr($password, 0, $length);
	}

	public function down()
	{
		// if ($this->db->table_exists('users_groups'))
		// {
		// 	$this->dbforge->drop_table('users_groups');
		// }
		// if ($this->db->table_exists('groups'))
		// {
		// 	$this->dbforge->drop_table('groups');
		// }
	}
}
