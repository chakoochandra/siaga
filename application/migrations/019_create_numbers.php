<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_numbers extends CI_Migration {

	public function up()
	{
		if (!$this->db->table_exists('numbers'))
		{
			$this->db->query("
				CREATE TABLE `numbers` (
					`n` INT(11) NOT NULL,
					PRIMARY KEY (`n`)
				)
				COLLATE='latin1_swedish_ci'
				ENGINE=InnoDB
			");

			for ($i = 0; $i <= 90; $i++) {
				$this->db->insert('numbers', ['n' => $i]);
			}
		}
	}

	public function down()
	{
		// if ($this->db->table_exists('numbers'))
		// {
		// 	$this->dbforge->drop_table('numbers');
		// }
	}
}