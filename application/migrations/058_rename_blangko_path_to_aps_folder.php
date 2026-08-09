<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Migration_Rename_blangko_path_to_aps_folder extends CI_Migration
{

	/**
	 * Fixed subfolder that now gets appended in code (Blangko.php /
	 * connector.minimal.php) instead of being stored in config.
	 */
	const BLANGKO_SUBFOLDER = '_blangko_abt';

	public function up()
	{
		$row = $this->db->where('key', 'BLANGKO_PATH')->get('tmst_configs')->row();

		if ($row) {
			// Existing install: migrate the row in place — rename the key
			// and strip a trailing "_blangko_abt" segment from the value,
			// if present, since callers append it themselves now.
			$value = trim((string) $row->value, "/ \t\n\r\0\x0B");

			if ($value !== '' && basename($value) === self::BLANGKO_SUBFOLDER) {
				$value = rtrim(dirname($value), '/');
				if ($value === '.') {
					$value = '';
				}
			}

			$this->db->where('id', $row->id)->update('tmst_configs', [
				'key'   => 'APS_FOLDER',
				'value' => $value,
				'note'  => 'Nama folder aplikasi APS Badilag, contoh: aps_badilag. Bisa juga diisi full path mount point, contoh: /mnt/blangko_share',
			]);
		} else {
			// Fresh install, or migration already run before (idempotent):
			// make sure APS_FOLDER exists without touching an existing value.
			$this->db->query("INSERT INTO `tmst_configs` (`key`, `value`, `category`, `note`) VALUES ('APS_FOLDER', '', NULL, 'Nama folder induk tempat blangko berada (bukan path lengkap ke _blangko_abt), contoh: aps_badilag. Bisa juga diisi full path mount point, contoh: /mnt/blangko_share. Subfolder _blangko_abt otomatis ditambahkan di kode.') ON DUPLICATE KEY UPDATE `note` = VALUES(`note`)");
		}
	}

	public function down()
	{
		// $row = $this->db->where('key', 'APS_FOLDER')->get('tmst_configs')->row();

		// if ($row) {
		// 	$value = trim((string) $row->value, "/ \t\n\r\0\x0B");

		// 	// Re-append the subfolder suffix that up() stripped, so the
		// 	// value round-trips back to its original form.
		// 	if ($value !== '') {
		// 		$value = $value . '/' . self::BLANGKO_SUBFOLDER;
		// 	}

		// 	$this->db->where('id', $row->id)->update('tmst_configs', [
		// 		'key'   => 'BLANGKO_PATH',
		// 		'value' => $value,
		// 		'note'  => 'Path Relative ke folder blangko, contoh. aps_badilag/_blangko_abt',
		// 	]);
		// }
	}
}
