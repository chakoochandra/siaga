<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Create_tref_wilayah_2020 extends CI_Migration
{
	const BATCH_SIZE = 1000;

	public function up()
	{
		$justCreated = false;

		if (!$this->db->table_exists('tref_wilayah_2020')) {
			$fkClause = '';
			if ($this->db->table_exists('tref_cost_radius')) {
				$fkClause = ",\n\t\t\t\t\tKEY `FK_tref_wilayah_2020_tref_biaya_panggilan` (`biaya_panggilan_id`),\n\t\t\t\t\tCONSTRAINT `FK_ydfui_tref_wilayah_2020_ydfui_tref_biaya_panggilan` FOREIGN KEY (`biaya_panggilan_id`) REFERENCES `tref_cost_radius` (`id`)";
			} else {
				log_message('error', 'Migration 039: tref_cost_radius not found, creating tref_wilayah_2020 without its FK constraint. Run the migration that creates tref_cost_radius, then add the FK manually if needed.');
			}

			$prevDbDebug = $this->db->db_debug;
			$this->db->db_debug = FALSE;
			$created = $this->db->query("
				CREATE TABLE IF NOT EXISTS `tref_wilayah_2020` (
					`id` int(11) NOT NULL AUTO_INCREMENT,
					`kode` varchar(13) CHARACTER SET utf8 NOT NULL,
					`nama` varchar(100) CHARACTER SET utf8 DEFAULT NULL,
					`biaya_panggilan_id` int(11) DEFAULT NULL,
					PRIMARY KEY (`id`),
					UNIQUE KEY `kode` (`kode`){$fkClause}
				) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
			");
			$this->db->db_debug = $prevDbDebug;

			if ($created === FALSE) {
				$dbError = $this->db->error();
				log_message('error', 'Migration 039: CREATE TABLE tref_wilayah_2020 failed — ' . $dbError['message']);
				return;
			}

			// Don't call table_exists() again here — CI caches list_tables() per-request,
			// so it would still report the pre-creation (stale) list. We know creation
			// succeeded because $created wasn't FALSE, so just trust that directly.
			$justCreated = true;
		}

		$isEmpty = true;
		if (!$justCreated) {
			$query = $this->db->query('SELECT COUNT(*) AS cnt FROM `tref_wilayah_2020`');
			if ($query) {
				$row = $query->row();
				$isEmpty = !$row || $row->cnt == 0;
			}
		}

		if ($isEmpty) {
			$this->seed_from_csv();
		}
	}

	protected function seed_from_csv()
	{
		$csvFile = APPPATH . 'migrations/data/tref_wilayah_2020.csv';

		if (!file_exists($csvFile)) {
			log_message('error', 'Migration 039: seed file not found at ' . $csvFile);
			return;
		}

		$handle = fopen($csvFile, 'r');
		if ($handle === FALSE) {
			log_message('error', 'Migration 039: could not open seed file ' . $csvFile);
			return;
		}

		// First row is the header: id,kode,nama,biaya_panggilan_id
		$header = fgetcsv($handle);
		if ($header === FALSE) {
			fclose($handle);
			log_message('error', 'Migration 039: seed file is empty or unreadable');
			return;
		}

		$this->db->trans_start();

		$batch = array();
		while (($fields = fgetcsv($handle)) !== FALSE) {
			list($id, $kode, $nama, $biaya) = $fields;

			$batch[] = array(
				'id'                 => (int) $id,
				'kode'               => $kode,
				'nama'               => ($nama === '') ? NULL : $nama,
				'biaya_panggilan_id' => ($biaya === '') ? NULL : (int) $biaya,
			);

			if (count($batch) >= self::BATCH_SIZE) {
				$this->db->insert_batch('tref_wilayah_2020', $batch);
				$batch = array();
			}
		}

		if (!empty($batch)) {
			$this->db->insert_batch('tref_wilayah_2020', $batch);
		}

		fclose($handle);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE) {
			log_message('error', 'Migration 039: failed populating tref_wilayah_2020, rolled back — table left empty for retry on next run.');
		}
	}

	public function down()
	{
		// if ($this->db->table_exists('tref_wilayah_2020'))
		// {
		// 	$this->dbforge->drop_table('tref_wilayah_2020', true);
		// }
	}
}
