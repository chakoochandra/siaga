<?php if (!defined('BASEPATH')) exit('No direct script access allowed');

class Migration_Add_performance_indexes extends CI_Migration
{
	private $indexes = [
		'arsip' => [
			'idx_arsip_perkara_id' => ['perkara_id'],
		],
		'dirput_antrian' => [
			'idx_dirput_antrian_perkara_id' => ['perkara_id'],
		],
		'perkara_pelaksanaan_relaas' => [
			'idx_perkara_pelaksanaan_relaas_sidang_id' => ['sidang_id'],
		],
		'perkara_mempelai_dk' => [
			'idx_perkara_mempelai_dk_perkara_id' => ['perkara_id'],
			'idx_perkara_mempelai_dk_nama' => ['nama'],
			'idx_perkara_mempelai_dk_nik' => ['nik'],
		],
	];

	public function up()
	{
		$sipp_db = $this->_get_sipp_db();
		if (!$sipp_db) {
			return;
		}

		foreach ($this->indexes as $table => $indexList) {
			foreach ($indexList as $name => $columns) {
				if ($this->_index_exists($sipp_db, $table, $name)) {
					continue;
				}

				if ($this->_column_has_index($sipp_db, $table, $columns[0])) {
					continue;
				}

				$quoted_columns = array_map(function ($col) {
					return '`' . $col . '`';
				}, $columns);

				$sipp_db->query(
					'CREATE INDEX `' . $name . '` ON `' . $table . '` (' . implode(', ', $quoted_columns) . ')'
				);
			}
		}
	}

	public function down()
	{
		// $sipp_db = $this->_get_sipp_db();
		// if (!$sipp_db) {
		// 	return;
		// }

		// foreach ($this->indexes as $table => $indexList) {
		// 	foreach ($indexList as $name => $columns) {
		// 		if ($this->_index_exists($sipp_db, $table, $name)) {
		// 			$sipp_db->query('DROP INDEX `' . $name . '` ON `' . $table . '`');
		// 		}
		// 	}
		// }
	}

	private function _get_sipp_db()
	{
		$ci = &get_instance();

		$db_file = APPPATH . 'config/database.php';
		if (!file_exists($db_file)) {
			return null;
		}

		include($db_file);
		if (!isset($db['db_sipp']) || empty($db['db_sipp']['hostname'])) {
			return null;
		}

		return $ci->load->database('db_sipp', true);
	}

	private function _index_exists($sipp_db, $table, $index_name)
	{
		$sql = "SELECT COUNT(*) AS cnt FROM information_schema.statistics
			WHERE table_schema = " . $sipp_db->escape($sipp_db->database) . "
			AND table_name = " . $sipp_db->escape($table) . "
			AND index_name = " . $sipp_db->escape($index_name);

		$row = $sipp_db->query($sql)->row();

		return $row ? ((int) $row->cnt > 0) : false;
	}

	private function _column_has_index($sipp_db, $table, $column)
	{
		$sql = "SELECT COUNT(*) AS cnt FROM information_schema.statistics
			WHERE table_schema = " . $sipp_db->escape($sipp_db->database) . "
			AND table_name = " . $sipp_db->escape($table) . "
			AND seq_in_index = 1
			AND column_name = " . $sipp_db->escape($column);

		$row = $sipp_db->query($sql)->row();

		return $row ? ((int) $row->cnt > 0) : false;
	}
}
