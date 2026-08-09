<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_Trans_minutation extends CI_Migration {

	public function up()
	{
		if (!$this->db->table_exists('trans_minutation'))
		{
			$this->db->query("
				CREATE TABLE `trans_minutation` (
					`perkara_id` BIGINT(11) NOT NULL,
					`tanggal_pp_setor` DATE NULL DEFAULT NULL COMMENT 'Tanggal PP setor instrumen',
					`tanggal_jsp_terima` DATE NULL DEFAULT NULL COMMENT 'Tanggal JS/JSP terima instrumen',
					`tanggal_panmudg_terima` DATE NULL DEFAULT NULL,
					`tanggal_serah_ke_minut` DATE NULL DEFAULT NULL,
					`tanggal_serah_ke_ac` DATE NULL DEFAULT NULL,
					`tanggal_serah_ke_arsip` DATE NULL DEFAULT NULL,
					`tanggal_upload_ecourt` DATE NULL DEFAULT NULL,
					`tanggal_tte_ecourt` DATE NULL DEFAULT NULL,
					`tanggal_upload_ecourt_verzet` DATE NULL DEFAULT NULL,
					`tanggal_tte_ecourt_verzet` DATE NULL DEFAULT NULL,
					`tanggal_rencana_bht` DATE NULL DEFAULT NULL,
					PRIMARY KEY (`perkara_id`)
				)
				COLLATE='latin1_swedish_ci'
				ENGINE=InnoDB
				ROW_FORMAT=DYNAMIC
			");
		}
	}

	public function down()
	{
		// $this->dbforge->drop_table('trans_minutation', TRUE);
	}
}
