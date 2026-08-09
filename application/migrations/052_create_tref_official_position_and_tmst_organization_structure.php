<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_tref_official_position_and_tmst_organization_structure extends CI_Migration {

	const BATCH_SIZE = 1000;

	public function up()
	{
		$orgStructureJustCreated = false;
		$positionJustCreated = false;

		if (!$this->db->table_exists('tmst_organization_structure'))
		{
			$prevDbDebug = $this->db->db_debug;
			$this->db->db_debug = FALSE;
			$created = $this->db->query("
				CREATE TABLE IF NOT EXISTS `tmst_organization_structure` (
					`id` INT(4) NOT NULL AUTO_INCREMENT,
					`title` VARCHAR(100) NULL DEFAULT NULL,
					`parent` TINYINT(2) NULL DEFAULT NULL,
					`item_type` TINYINT(1) NULL DEFAULT NULL COMMENT '0 : default. 1 : assistant. 2: adviser',
					`adviser_placement` TINYINT(1) NULL DEFAULT NULL COMMENT '2 : left. 3 : right.',
					`child_placement` TINYINT(1) NULL DEFAULT NULL COMMENT '0 : auto. 1: vertical. 2: horizontal. 3: matrix',
					`level_offset` TINYINT(1) NULL DEFAULT NULL COMMENT 'kedalaman level node',
					`color` CHAR(7) NULL DEFAULT NULL,
					`has_structural` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '0 : no. 1 : yes.',
					`is_visible` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '0 : no. 1 : yes.',
					`is_active` TINYINT(1) NOT NULL DEFAULT '1' COMMENT '0 : no. 1 : yes.',
					PRIMARY KEY (`id`) USING BTREE
				) COLLATE='latin1_swedish_ci' ENGINE=InnoDB
			");
			$this->db->db_debug = $prevDbDebug;

			if ($created === FALSE)
			{
				$dbError = $this->db->error();
				log_message('error', 'Migration 051: CREATE TABLE tmst_organization_structure failed — ' . $dbError['message']);
			}
			else
			{
				// Don't call table_exists() again for this table later in this
				// request — CI caches list_tables() per-request, so it would
				// still report the pre-creation (stale) result. See migration
				// 039 for the same issue.
				$orgStructureJustCreated = true;
			}
		}

		if (!$this->db->table_exists('tref_official_position'))
		{
			$prevDbDebug = $this->db->db_debug;
			$this->db->db_debug = FALSE;
			$created = $this->db->query("
				CREATE TABLE IF NOT EXISTS `tref_official_position` (
					`id` INT(4) NOT NULL AUTO_INCREMENT,
					`nama_jabatan` VARCHAR(100) NOT NULL,
					`urutan` TINYINT(4) NOT NULL,
					`struktur_organisasi_id` INT(11) NULL DEFAULT NULL,
					`is_struktural` TINYINT(1) NOT NULL COMMENT '0 : bukan struktural, 1 : struktural',
					`has_staff` TINYINT(1) NULL DEFAULT NULL COMMENT '0 : tidak punya staff, 1 : punya staff',
					`is_honorer` TINYINT(1) NULL DEFAULT NULL COMMENT '0 : bukan honorer, 1 : honorer',
					`group` TINYINT(1) NULL DEFAULT NULL COMMENT '1 : hakim, 2 : panmud, 3 : kasubbag, 4 : jafung, 5 : tenaga teknis, 6 : pelaksana, 7: honorer',
					PRIMARY KEY (`id`),
					INDEX `FK_tref_jabatan_tmst_struktur_organisasi` (`struktur_organisasi_id`),
					CONSTRAINT `FK_tref_jabatan_tmst_struktur_organisasi` FOREIGN KEY (`struktur_organisasi_id`) REFERENCES `tmst_organization_structure` (`id`)
				) COLLATE='latin1_swedish_ci' ENGINE=InnoDB
			");
			$this->db->db_debug = $prevDbDebug;

			if ($created === FALSE)
			{
				$dbError = $this->db->error();
				log_message('error', 'Migration 051: CREATE TABLE tref_official_position failed — ' . $dbError['message']);
			}
			else
			{
				$positionJustCreated = true;
			}
		}

		// tref_official_position.struktur_organisasi_id has an FK into
		// tmst_organization_structure, so it must be seeded second.
		if ($orgStructureJustCreated || ($this->db->table_exists('tmst_organization_structure') && $this->db->count_all('tmst_organization_structure') === 0))
		{
			$this->seed_org_structure_from_csv();
		}

		if ($positionJustCreated || ($this->db->table_exists('tref_official_position') && $this->db->count_all('tref_official_position') === 0))
		{
			$this->seed_official_position_from_csv();
		}
	}

	protected function seed_org_structure_from_csv()
	{
		$this->seed_from_csv(
			'tmst_organization_structure',
			'tmst_organization_structure.csv',
			function ($row) {
				return array(
					'id'                 => (int) $row['id'],
					'title'              => ($row['title'] === '') ? NULL : $row['title'],
					'parent'             => ($row['parent'] === '') ? NULL : (int) $row['parent'],
					'item_type'          => ($row['item_type'] === '') ? NULL : (int) $row['item_type'],
					'adviser_placement'  => ($row['adviser_placement'] === '') ? NULL : (int) $row['adviser_placement'],
					'child_placement'    => ($row['child_placement'] === '') ? NULL : (int) $row['child_placement'],
					'level_offset'       => ($row['level_offset'] === '') ? NULL : (int) $row['level_offset'],
					'color'              => ($row['color'] === '') ? NULL : $row['color'],
					'has_structural'     => (int) $row['has_structural'],
					'is_visible'         => (int) $row['is_visible'],
					'is_active'          => (int) $row['is_active'],
				);
			}
		);
	}

	protected function seed_official_position_from_csv()
	{
		$this->seed_from_csv(
			'tref_official_position',
			'tref_official_position.csv',
			function ($row) {
				return array(
					'id'                      => (int) $row['id'],
					'nama_jabatan'            => $row['nama_jabatan'],
					'urutan'                  => (int) $row['urutan'],
					'struktur_organisasi_id'  => ($row['struktur_organisasi_id'] === '') ? NULL : (int) $row['struktur_organisasi_id'],
					'is_struktural'           => (int) $row['is_struktural'],
					'has_staff'               => ($row['has_staff'] === '') ? NULL : (int) $row['has_staff'],
					'is_honorer'              => ($row['is_honorer'] === '') ? NULL : (int) $row['is_honorer'],
					'group'                   => ($row['group'] === '') ? NULL : (int) $row['group'],
				);
			}
		);
	}

	/**
	 * Shared CSV-batch-insert helper (same shape as migration 039's
	 * seed_from_csv()). $rowMapper receives the row as [header => value]
	 * (via array_combine against the CSV's own header row) and must return
	 * the associative array to hand to insert_batch().
	 */
	protected function seed_from_csv($table, $csvFilename, $rowMapper)
	{
		$csvFile = APPPATH . 'migrations/data/' . $csvFilename;

		if (!file_exists($csvFile))
		{
			log_message('error', 'Migration 051: seed file not found at ' . $csvFile);
			return;
		}

		$handle = fopen($csvFile, 'r');
		if ($handle === FALSE)
		{
			log_message('error', 'Migration 051: could not open seed file ' . $csvFile);
			return;
		}

		$header = fgetcsv($handle);
		if ($header === FALSE)
		{
			fclose($handle);
			log_message('error', 'Migration 051: seed file ' . $csvFilename . ' is empty or unreadable');
			return;
		}

		$this->db->trans_start();

		$batch = array();
		$lineNumber = 1; // header was line 1

		while (($fields = fgetcsv($handle)) !== FALSE)
		{
			$lineNumber++;

			if (count($fields) !== count($header))
			{
				log_message('error', 'Migration 051: skipping malformed row ' . $lineNumber . ' in ' . $csvFilename
					. ' (expected ' . count($header) . ' columns, got ' . count($fields) . ')');
				continue;
			}

			$row = array_combine($header, $fields);
			$batch[] = call_user_func($rowMapper, $row);

			if (count($batch) >= self::BATCH_SIZE)
			{
				$this->db->insert_batch($table, $batch);
				$batch = array();
			}
		}

		if (!empty($batch))
		{
			$this->db->insert_batch($table, $batch);
		}

		fclose($handle);

		$this->db->trans_complete();

		if ($this->db->trans_status() === FALSE)
		{
			log_message('error', 'Migration 051: failed populating ' . $table . ' from ' . $csvFilename . ', rolled back — table left empty for retry on next run.');
		}
	}

	public function down()
	{
		// if ($this->db->table_exists('tref_official_position'))
		// {
		// 	$this->dbforge->drop_table('tref_official_position');
		// }
		// if ($this->db->table_exists('tmst_organization_structure'))
		// {
		// 	$this->dbforge->drop_table('tmst_organization_structure');
		// }
	}
}