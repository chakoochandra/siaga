<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Set_admin_nama_lengkap extends CI_Migration {

	public function up()
	{
		$this->db->where('username', 'admin')->update('users', array('nama_lengkap' => 'Administrator'));
	}

	public function down()
	{
		// $this->db->where('username', 'admin')->update('users', array('nama_lengkap' => null));
	}
}
