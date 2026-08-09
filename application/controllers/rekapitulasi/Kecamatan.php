<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Kecamatan extends Core_Controller
{
	public function __construct()
	{
		parent::__construct();

		if (!is_local_ip()) {
			show_error('Halaman ini hanya bisa diakses menggunakan IP lokal pada jaringan kantor', $status_code = 404, $heading = 'Halaman ini hanya bisa diakses pada jaringan kantor');
			exit;
		}

		$this->load->model('rekapitulasi/Kecamatan_Model', 'kecamatan');
		$this->model = $this->kecamatan;

		$this->indexTitle = 'Rekapitulasi Penerimaan Perkara per Kecamatan';
		$this->indexSubtitle = 'Modul ini menampilkan rekapitulasi penerimaan perkara per kecamatan.';
		$this->indexIcon = 'fa-solid fa-map';
		$this->indexView = 'rekapitulasi/kecamatan/index';
		$this->module_id = 'rekapitulasi_kecamatan';
	}

	protected function prepare_index($options = [])
	{
		$this->load->model('settings/Ref_Model', 'ref');
		$this->load->model('Wilayah_Model', 'wilayah');
		$this->vars['all_jenis_perkara'] = $this->ref->findJenisPerkara();
		$this->vars['all_alur_perkara'] = $this->ref->findAlurPerkara();
		$this->vars['all_kecamatan'] = $this->wilayah->findKecamatanByKabupaten(ID_WILAYAH, false, false);
		$this->vars['all_radius'] = $this->wilayah->findRadiusByKabupaten(ID_WILAYAH);
		$this->vars['kabupaten'] = $this->wilayah->getNamaByKode(ID_WILAYAH);

		parent::prepare_index($this->vars);
	}

	function perkara_list()
	{
		$this->load->model('Wilayah_Model', 'wilayah');

		$title = 'Daftar Penerimaan Perkara';
		$segment4 = $this->uri->segment(4);

		if ($segment4) {
			if ($segment4 == 'kec_luar') {
				$kecamatanNama = 'Luar ' . $this->wilayah->getNamaByKode(ID_WILAYAH);
			} else if ($segment4 == 'total_perkara') {
				$kecamatanNama = null;
			} else if (strpos($segment4, 'kec_') === 0) {
				// segment4 is the "kec_<sanitized-name>" bucket key (matching
				// the SUM column alias / list_query's CASE chain in
				// Kecamatan_Model), not a wilayah code - reverse it back to a
				// display name by re-applying the same sanitization the model
				// uses and matching against it.
				$kecamatanNama = null;
				foreach ($this->wilayah->findKecamatanByKabupaten(ID_WILAYAH, false, false) as $namaKec) {
					$bucketKey = 'kec_' . strtolower(preg_replace('/[^A-Za-z0-9]/', '', $namaKec));
					if ($bucketKey === $segment4) {
						$kecamatanNama = $namaKec;
						break;
					}
				}
				if (!$kecamatanNama) {
					$kecamatanNama = urldecode($segment4);
				}
			} else {
				// Backward-compat path, in case anything still links here
				// with a raw wilayah code rather than the bucket key.
				$kecamatanNama = $this->wilayah->getNamaByKode($segment4);
				if (!$kecamatanNama) {
					$kecamatanNama = urldecode($segment4);
				}
			}
			if ($kecamatanNama) {
				$title .= ' Kecamatan ' . $kecamatanNama;
			}
		}

		if ($this->uri->segment(7)) {
			$title .= $this->uri->segment(7) == 1 ? ' E-Court' : ' Tidak E-Court';
		}

		if ($this->uri->segment(6)) {
			$month = $this->uri->segment(6);
			$title .= ' ' . format_date(date('Y') . "-$month-01", 'MMMM');
			$title .= ' ' . $this->uri->segment(5);
		} else {
			if ($this->uri->segment(5)) {
				$title .= ' Tahun ' . $this->uri->segment(5);
			}
		}

		$this->vars['main_body'] = 'layout_content';
		$this->vars['view'] = 'rekapitulasi/kecamatan/list';
		$this->vars['title'] = $title;

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('rekapitulasi/kecamatan/list', ['size' => 'modal-xl', 'showTitle' => true]);
		}

		$this->load->view('layout');
	}
}
