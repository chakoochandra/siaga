<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Hakim extends Core_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->model('kinerja/Hakim_Model', 'rekapitulasi');
		$this->model = $this->rekapitulasi;

		$this->indexTitle = 'Laporan Kegiatan Hakim (LIPA 6)';
		$this->indexSubtitle = 'Modul ini menampilkan statistik dan rekapitulasi keadaan perkara di pengadilan secara periodik (bulanan atau tahunan).';
		$this->indexIcon = 'fa-solid fa-gavel';
		$this->indexView = 'kinerja/hakim/index';
		$this->module_id = 'kinerja_hakim';
	}

	/**
	 * AJAX source for the monthly "Rekap Perkara Diterima Hakim" table (Jan-Dec of
	 * a chosen year), feeding the second DataTable rendered below the main recap.
	 */
	function get_statistic_monthly()
	{
		$year = $this->input->post('year') ?: date('Y');
		$category = $this->input->post('category') ?: 'terima';
		$result = $this->rekapitulasi->get_statistic_monthly($year, null, $category);

		echo json_encode(['data' => $result]);
	}

	function perkara_list()
	{
		$column = $this->uri->segment(4);
		$alur_id = $this->uri->segment(5);
		$date_type = $this->uri->segment(6); // 'range', 'year', 'month', etc.
		$date_value = $this->uri->segment(7); // Date range string or year/month
		$hakim_id = $this->uri->segment(8); // hakim_id parameter
		$jabatan_hakim_id = $this->uri->segment(9); // jabatan_hakim_id: 1=Majelis, 3=Hakim Tunggal

		$title = 'Daftar Perkara';

		// Add column information to the title
		$column_titles = [
			'sisa_bulan_lalu' => 'Sisa Sebelumnya',
			'terima' => 'Diterima',
			'jumlah_bulan_ini' => 'Jumlah',
			'jumlah_putus' => 'Jumlah Putus',
			'sisa_bulan_ini' => 'Sisa',
			'minutasi' => 'Minutasi',
			// table 2 (monthly recap) drills down with its own column keys for
			// the same Diterima/Putus/Sisa categories — see
			// Hakim_Model::monthly_category_condition() / categoryMap in
			// index.php's createMonthlyUrl()/createSisaSebelumnyaUrl().
			'putus' => 'Diputus',
			'sisa_bulan_kohort' => 'Sisa',
			'sisa_sebelumnya_tahun' => 'Sisa Sebelumnya',
		];

		if (isset($column_titles[$column])) {
			$title = 'Perkara ' . $column_titles[$column];
		}

		// Add hakim information to the title if available
		// If hakim_id is 0, null, or not provided, don't add specific hakim to title
		if ($hakim_id && $hakim_id != 0 && $hakim_id !== 'null' && $hakim_id !== null) {
			$this->load->model('settings/Ref_Model', 'ref');
			$hakim = $this->ref->findHakimById($hakim_id);
			if ($hakim) {
				$title .= " | {$hakim->nama_gelar}";
			}
		} else {
			// Add "Semua Hakim" to the title when showing all hakims
			$title .= " | Semua Hakim";
		}

		// Add jabatan hakim information to the title if available
		$jabatan_labels = [1 => 'Majelis', 3 => 'Hakim Tunggal'];
		if ($jabatan_hakim_id && isset($jabatan_labels[$jabatan_hakim_id])) {
			$title .= " | " . $jabatan_labels[$jabatan_hakim_id];
		}

		// Format title based on date type - only handle date ranges
		switch ($date_type) {
			case 'range':
				$decoded_range = urldecode($date_value);
				$parts = explode('|', $decoded_range);
				if (count($parts) === 2) {
					$startDate = date('d/m/Y', strtotime(trim($parts[0])));
					$endDate   = date('d/m/Y', strtotime(trim($parts[1])));
					$title .= " | {$startDate} - {$endDate}";
				} else {
					$title .= " | Tanggal Tidak Valid";
				}
				break;
			default:
				// Default to current year until today if no valid range
				$startDate = date('Y-01-01');
				$endDate = date('Y-m-d');
				$startDateFormatted = format_date($startDate, "d MMMM");
				$endDateFormatted = format_date($endDate, "d MMMM yyyy");
				$title .= " | {$startDateFormatted} - {$endDateFormatted}";
				break;
		}

		$this->vars['main_body'] = 'layout_content';
		$this->vars['view'] = 'kinerja/hakim/list';
		$this->vars['title'] = $title;

		$this->load->model('settings/Holiday_Model', 'holiday');
		$this->vars['excludedDates'] = $this->holiday->get_holidays(null, null);

		$this->load->vars($this->vars);

		if ($this->input->is_ajax_request()) {
			return $this->viewAjax('kinerja/hakim/list', ['size' => 'modal-xl', 'showTitle' => true]);
		}

		$this->load->view('layout');
	}
}
