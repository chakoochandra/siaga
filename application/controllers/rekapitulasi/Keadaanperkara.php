<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Keadaanperkara extends Core_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->helper('app');

		if (!is_local_ip()) {
			show_error('Halaman ini hanya bisa diakses menggunakan IP lokal pada jaringan kantor', $status_code = 404, $heading = 'Halaman ini hanya bisa diakses pada jaringan kantor');
			exit;
		}

		$this->load->model('rekapitulasi/Keadaan_Perkara_Model', 'rekapitulasi');
		$this->model = $this->rekapitulasi;

		$this->indexTitle = 'Rekapitulasi Keadaan Perkara';
		$this->indexSubtitle = 'Modul ini menampilkan rekapitulasi keadaan perkara.';
		$this->indexView = 'rekapitulasi/keadaan-perkara/index';
		$this->module_id = 'rekapitulasi_keadaan_perkara';
	}

	protected function prepare_index($options = [])
	{
		$this->load->model('settings/Ref_Model', 'ref');
		$this->vars['all_jenis_perkara'] = $this->ref->findJenisPerkara();
		$this->vars['all_alur_perkara'] = $this->ref->findAlurPerkara();
		$this->vars['view_type'] = 'monthly';
		$this->vars['assets'] = [
			'datatables' => true,
			'datepicker' => true,
			'select2' => true,
			'moment' => true
		];

		parent::prepare_index($this->vars);
	}

	function yearly()
	{
		$this->load->model('settings/Ref_Model', 'ref');
		$this->vars['main_body'] = 'layout_content';
		$this->vars['view'] = 'rekapitulasi/keadaan-perkara/index';
		$this->vars['title'] = 'Rekapitulasi Perkara Tahunan';
		$this->vars['all_jenis_perkara'] = $this->ref->findJenisPerkara();
		$this->vars['all_alur_perkara'] = $this->ref->findAlurPerkara();
		$this->vars['view_type'] = 'yearly';
		$this->vars['assets'] = [
			'datatables' => true,
			'select2' => true
		];

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('layout_content');
		}

		$this->load->view('layout');
	}

	function details()
	{
		$this->load->model('settings/Ref_Model', 'ref');
		$this->vars['main_body'] = 'layout_content';
		$this->vars['view'] = 'rekapitulasi/keadaan-perkara/details';
		$this->vars['title'] = 'Rekapitulasi Details';
		$this->vars['all_jenis_perkara'] = $this->ref->findJenisPerkara();
		$this->vars['all_alur_perkara'] = $this->ref->findAlurPerkara();
		$this->vars['assets'] = [
			'datatables' => true,
			'datepicker' => true,
			'select2' => true,
			'moment' => true,
		];

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('layout_content');
		}

		$this->load->view('layout');
	}

	function ecourt()
	{
		$this->load->model('settings/Ref_Model', 'ref');
		$this->vars['main_body'] = 'layout_content';
		$this->vars['view'] = 'rekapitulasi/keadaan-perkara/ecourt';
		$this->vars['title'] = 'Rekapitulasi Perkara e-Court';
		$this->vars['subtitle'] = 'Modul ini menampilkan rekapitulasi perkara e-Court.';
		$this->vars['all_jenis_perkara'] = $this->ref->findJenisPerkara();
		$this->vars['all_alur_perkara'] = $this->ref->findAlurPerkara();
		$this->vars['assets'] = [
			'datatables' => true,
			'datepicker' => true,
			'select2' => true,
			'moment' => true,
		];

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('layout_content');
		}

		$this->load->view('layout');
	}

	function dk()
	{
		$this->vars['main_body'] = 'layout_content';
		$this->vars['view'] = 'rekapitulasi/keadaan-perkara/dk';
		$this->vars['title'] = 'Rekapitulasi Dispensasi Kawin';
		$this->vars['subtitle'] = 'Modul ini menampilkan rekapitulasi dispensasi kawin.';
		$this->vars['assets'] = [
			'datatables' => true,
			'datepicker' => true,
			'select2' => true,
			'moment' => true,
		];

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('layout_content');
		}

		$this->load->view('layout');
	}

	function get_yearly()
	{
		$data = $this->model->get_statistic(null, null, 'yearly');
		return $this->set_content_type([
			"draw" => $this->input->post('draw'),
			"recordsTotal" => count($data),
			"recordsTotal" => $this->model->count_statistic_all(null, null, 'yearly'),
			"recordsFiltered" => $this->model->count_statistic_filtered(null, null, 'yearly'),
			"data" => $data,
		]);
	}

	function perkara_list()
	{
		if (substr($this->uri->segment(4), 0, strlen('dk_umur_')) === 'dk_umur_') {
			$title = 'Klasifikasi Umur Mempelai Dispensasi Kawin';
		} else if (substr($this->uri->segment(4), 0, strlen('dk_jenis_')) === 'dk_jenis_') {
			$title = 'Klasifikasi Jenis Kelamin Dispensasi Kawin';
		} else if (substr($this->uri->segment(4), 0, strlen('dk_pendidikan_')) === 'dk_pendidikan_') {
			$title = 'Tingkat Pendidikan Mempelai Dispensasi Kawin';
		} else if (substr($this->uri->segment(4), 0, strlen('dk_pekerjaan_')) === 'dk_pekerjaan_') {
			$title = 'Pekerjaan Mempelai Dispensasi Kawin';
		} else if (substr($this->uri->segment(4), 0, strlen('alasan_')) === 'alasan_') {
			$title = 'Alasan Dispensasi Kawin';
		} else {
			$titles = [
				'sisa' => 'Sisa Perkara',
				'sisa_bulan_lalu' => 'Sisa Perkara',
				'terima' => 'Diterima',
				'cabut' => 'Dicabut',
				'dikabulkan' => 'Dikabulkan',
				'ditolak' => 'Ditolak',
				'tidak_diterima' => 'Tidak Diterima',
				'digugurkan' => 'Digugurkan',
				'dicoret' => 'Dicoret Dari Register',
				'damai' => 'Perdamaian',
				'jumlah_putus' => 'Putus',
				'jumlah_putus_minus_cabut' => 'Putus',
				'jumlah_bulan_ini' => 'Jumlah Perkara',
				'sisa_bulan_ini' => 'Sisa Perkara',
				'tunggakan' => 'Tunggakan Perkara',
				'tunggakan_bulan_lalu' => 'Tunggakan Perkara',
				'minutasi' => 'Minutasi',
				'masuk_hari_ini' => 'Masuk Hari Ini',
				'ecourt_hari_ini' => 'e-Court Hari Ini',
				'putus_hari_ini' => 'Putus Hari Ini',
				'redaksi_hari_ini' => 'Redaksi Hari Ini',
				'belum_input_putus' => 'Belum Input Putus',
				'putus_belum_redaksi' => 'Belum Input Redaksi',
				'selisih_redaksi_putus' => 'Tanggal Putus-Redaksi Berbeda',
				'belum_minutasi' => 'Belum Minutasi',
				'published' => 'Putusan Terpublish',
				'not_published' => 'Putusan Tidak Terpublish',
				'sudah_ada_edoc' => 'e-Doc Putusan',
				'belum_ada_edoc' => 'Belum Ada e-Doc',
				'dirput_antrian' => 'Antrian Upload Dirput',
				'dirput_error' => 'Antrian Dirput Error',
				'belum_ada_gugatan' => 'Perkara Belum Ada Gugatan',
				'relaas_belum_input' => 'Relaas Belum Diinput',
				'belum_anonimasi' => 'Putusan Belum Anonimisasi',
				'belum_pertimbangan_hukum' => 'Belum Ada Pertimbangan Hukum',
				'jumlah_semua' => 'Putus',
				'lain_lain' => 'Lain-Lain',
				'belum_ada_pmh' => 'Belum Ada PMH',
				'belum_ada_phs' => 'Belum Ada PHS',
			];
			$title = isset($titles[$this->uri->segment(4)]) ? $titles[$this->uri->segment(4)] : 'Daftar Perkara';
		}

		$showPeriod = true;
		if (in_array($this->uri->segment(4), [
			'tunggakan',
			'tunggakan_bulan_lalu',
			'masuk_hari_ini',
			'ecourt_hari_ini',
			'putus_hari_ini',
			'redaksi_hari_ini',
			'putus_belum_redaksi',
			'selisih_redaksi_putus',
			'belum_minutasi',
			'published',
			'not_published',
			'sudah_ada_edoc',
			'belum_ada_edoc',
			'dirput_antrian',
			'dirput_error',
			'belum_ada_gugatan',
			'relaas_belum_input',
			'belum_anonimasi',
		])) {
			$showPeriod = false;
		}

		// if ($this->uri->segment(7)) {
		// 	$title .= $this->uri->segment(7) == 1 ? ' e-Court' : ' Tidak e-Court';
		// }

		if ($showPeriod) {
			$month = $this->uri->segment(6);
			if ($this->uri->segment(6)) {
				if (in_array($this->uri->segment(4), ['sisa', 'sisa_bulan_lalu'])) {
					$month = $this->uri->segment(6) - 1;
					if ($month > 0) {
						$title .= " Bulan " . format_date(date('Y') . "-$month-01", 'MMMM');
					}
				} else {
					$title .= " Bulan " . format_date(date('Y') . "-$month-01", 'MMMM');
				}
			}
			if ($this->uri->segment(5)) {
				$year = $this->uri->segment(5);
				if ($this->uri->segment(4) == 'sisa') {
					$month = $this->uri->segment(6) - 1;
					if ($month == 0) {
						$year -= 1;
						$title .= " Tahun {$year}";
					} else {
						$title .= (!$this->uri->segment(6) ? ' Tahun' : '') . " {$year}";
					}
				} else {
					$title .= (!$this->uri->segment(6) ? ' Tahun' : '') . " {$year}";
				}
			}
		}

		$this->vars['main_body'] = 'layout_content';
		$this->vars['view'] = 'rekapitulasi/keadaan-perkara/list';
		$this->vars['title'] = $title;
		
		$this->load->model('settings/Holiday_Model', 'holiday');
		$this->vars['excludedDates'] = $this->holiday->get_holidays(null, null);

		$this->vars['assets'] = [
			'datatables' => true
		];

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('rekapitulasi/keadaan-perkara/list', ['size' => 'modal-xl', 'showTitle' => true]);
		}

		$this->load->view('layout');
	}
}
