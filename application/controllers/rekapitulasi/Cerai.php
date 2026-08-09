<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Cerai extends Core_Controller
{
	public function __construct()
	{
		parent::__construct();

		if (!is_local_ip()) {
			show_error('Halaman ini hanya bisa diakses menggunakan IP lokal pada jaringan kantor', $status_code = 404, $heading = 'Halaman ini hanya bisa diakses pada jaringan kantor');
			exit;
		}

		$this->load->model('rekapitulasi/Cerai_Model', 'cerai');
		$this->model = $this->cerai;

		$this->indexTitle = 'Data Perkara Cerai';
		$this->indexSubtitle = 'Modul ini menampilkan statistik perkara cerai.';
		$this->indexIcon = 'fa-solid fa-heart-circle-xmark';
		$this->indexView = 'rekapitulasi/cerai/index';
		$this->module_id = 'rekapitulasi_cerai';
	}

	protected function prepare_index($options = [])
	{
		$this->load->model('settings/Ref_Model', 'ref');
		$this->vars['all_hakim_aktif'] = $this->ref->findHakim();

		$agama_list = $this->ref->findAgama(true);
		$all_agama_options = [
			'semua' => 'Semua Agama',
			'tidak_diset' => 'Agama Tidak Diset',
		];
		foreach ($agama_list as $agama) {
			$all_agama_options[$agama->id] = $agama->nama;
		}
		$this->vars['all_agama_options'] = $all_agama_options;

		// NEW: Pendidikan options
		$pendidikan_list = $this->ref->findPendidikan();
		$all_pendidikan_options = [
			'tidak_diset' => 'Pendidikan Tidak Diset',
		];
		foreach ($pendidikan_list as $pendidikan) {
			$all_pendidikan_options[$pendidikan->id] = $pendidikan->nama;
		}
		$this->vars['all_pendidikan_options'] = $all_pendidikan_options;

		// NEW: Warga Negara options
		$negara_list = $this->ref->findNegara();
		$all_warganegara_options = [
			'tidak_diset' => 'Warga Negara Tidak Diset',
		];
		foreach ($negara_list as $negara) {
			$all_warganegara_options[$negara->id] = $negara->nama;
		}
		$this->vars['all_warganegara_options'] = $all_warganegara_options;

		$pekerjaan_list = $this->ref->findPekerjaan();
		$all_pekerjaan_options = [
			'tidak_diset' => 'Pekerjaan Tidak Diset',
		];
		foreach ($pekerjaan_list as $pekerjaan) {
			$all_pekerjaan_options[$pekerjaan->id] = $pekerjaan->nama;
		}
		$this->vars['all_pekerjaan_options'] = $all_pekerjaan_options;

		$this->vars['title'] = $this->indexTitle;

		parent::prepare_index($this->vars);
	}

	public function get_summary()
	{
		$summary = $this->cerai->get_summary();
		$ringkasan = $this->cerai->get_ringkasan();

		$grouped = [
			'Agama' => [],
			'Pendidikan' => [],
			'Warga Negara' => [],
			'Pekerjaan' => [],
		];
		foreach ($summary as $row) {
			$grouped[$row['grup']][] = [
				'label' => $row['label'],
				'jumlah' => (int) $row['jumlah'],
			];
		}

		$this->output
			->set_content_type('application/json')   // NEW
			->set_output(json_encode([
				'status' => true,
				'data' => $grouped,
				'ringkasan' => $ringkasan,
			]));
	}
}
