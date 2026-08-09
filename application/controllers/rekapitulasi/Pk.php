<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pk extends Core_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!is_local_ip()) {
            show_error('Halaman ini hanya bisa diakses menggunakan IP lokal pada jaringan kantor', $status_code = 404, $heading = 'Halaman ini hanya bisa diakses pada jaringan kantor');
            exit;
        }
        
        $this->load->model('rekapitulasi/Pk_Model', 'pk');
        $this->model = $this->pk;

		$this->indexTitle = 'Rekapitulasi Keadaan Perkara PK';
		$this->indexSubtitle = 'Modul ini menampilkan statistik dan rekapitulasi keadaan perkara peninjauan kembali (PK) secara periodik (bulanan atau tahunan).';
		$this->indexIcon = 'fa-solid fa-rotate-left';
		$this->indexView = 'rekapitulasi/pk/index';
		$this->module_id = 'monitoring_pk';
    }

    function perkara_list()
    {
        $year = $this->uri->segment(5);
        $month = $this->uri->segment(6);

        switch ($this->uri->segment(4)) {
            case 'sisa_bulan_lalu':
                $title = 'Perkara PK PK';
                break;
            case 'terima':
                $title = 'Perkara PK Masuk';
                break;
            case 'diperbaiki':
                $title = 'Perkara PK Diperbaiki';
                break;
            case 'dibatalkan':
                $title = 'Perkara PK Dibatalkan';
                break;
            case 'tidak_diterima':
                $title = 'Perkara PK Tidak Diterima';
                break;
            case 'ditolak':
                $title = 'Perkara PK Ditolak';
                break;
            case 'lain_lain':
                $title = 'Status Lain-Lain';
                break;
            case 'jumlah_putus':
                $title = 'Perkara PK Putus';
                break;
            case 'sisa_bulan_ini':
                $title = 'Perkara PK Belum Putus';
                break;
            case 'syarat_formil':
                $title = 'Tidak Memenuhi Syarat / Cabut';
                break;
        }

        $title .= $month ? " Bulan " . format_date(date('Y')."-$month-01", 'MMMM') : '';
        $title .= $year ? " Tahun {$year}" : '';

        $this->vars['main_body'] = 'layout_content';
        $this->vars['view'] = 'rekapitulasi/pk/list';
        $this->vars['title'] = $title;

        $this->load->vars($this->vars);

        if ($this->input->is_ajax_request()) {
            return $this->viewAjax('rekapitulasi/pk/list', ['size' => 'modal-xl', 'showTitle' => true]);
        }

        $this->load->view('layout');
    }
}
