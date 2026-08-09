<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Set_username_and_password_from_nip extends CI_Migration {

	public function up()
	{
		if ($this->db->query("SHOW TABLES LIKE 'users'")->num_rows() === 0) {
			return;
		}

		$prevDbDebug = $this->db->db_debug;
		$this->db->db_debug = FALSE;

		// 1. Fill empty username with nip + activate
		$sql = "UPDATE `users` SET `username` = `nip`, `active` = 1 " .
			"WHERE (`username` IS NULL OR `username` = '') " .
			"AND `nip` IS NOT NULL AND `nip` != ''";;

		$result = $this->db->query($sql);

		if ($result === FALSE) {
			$dbError = $this->db->error();
			log_message('error', 'Migration 055: UPDATE username from nip failed — ' . $dbError['message']);
			$this->db->db_debug = $prevDbDebug;
			return;
		}

		// 2. Fill empty password with bcrypt hash of nip
		$users = $this->db->select('id, nip')
			->from('users')
			->where("(`password` IS NULL OR `password` = '') AND `nip` IS NOT NULL AND `nip` != ''")
			->get()
			->result();

		$cost = $this->config->item('bcrypt_default_cost', 'ion_auth') ?: 10;
		$updated = 0;

		foreach ($users as $user) {
			$hashed = password_hash($user->nip, PASSWORD_BCRYPT, ['cost' => $cost]);
			if ($hashed) {
				$this->db->where('id', $user->id)->update('users', ['password' => $hashed, 'active' => 1]);
				$updated++;
			}
		}

		$this->db->db_debug = $prevDbDebug;

		log_message('info', 'Migration 055: Filled username+password from nip for ' . $updated . ' users.');
	}

	public function down()
	{
		// No rollback — one-way data fix
	}
}
