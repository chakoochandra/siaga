<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Copy_tanggal_pp_setor_to_tanggal_panmudg_terima extends CI_Migration {

	public function up()
	{
		if ($this->db->table_exists('trans_minutation'))
		{
			$this->db->query("
				UPDATE `trans_minutation`
				SET `tanggal_panmudg_terima` = `tanggal_pp_setor`
				WHERE `tanggal_panmudg_terima` IS NULL
				  AND `tanggal_pp_setor` IS NOT NULL
			");
		}
	}

	public function down()
	{
		// This migration copies data, so down() would clear tanggal_panmudg_terima
		// Uncomment the following if you want to revert:
		/*
		if ($this->db->table_exists('trans_minutation'))
		{
			$this->db->query("
				UPDATE `trans_minutation`
				SET `tanggal_panmudg_terima` = NULL
				WHERE `tanggal_panmudg_terima` = `tanggal_pp_setor`
			");
		}
		*/
	}
}