<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Banding extends Core_Controller
{
    public function __construct()
    {
        parent::__construct();

        if (!is_local_ip()) {
            show_error('Halaman ini hanya bisa diakses menggunakan IP lokal pada jaringan kantor', $status_code = 404, $heading = 'Halaman ini hanya bisa diakses pada jaringan kantor');
            exit;
        }

        $this->load->model('rekapitulasi/Banding_Model', 'banding');
        $this->model = $this->banding;

		$this->indexTitle = 'Rekapitulasi Keadaan Perkara Banding';
		$this->indexSubtitle = 'Modul ini menampilkan statistik dan rekapitulasi keadaan perkara banding secara periodik (bulanan atau tahunan).';
		$this->indexIcon = 'fa-solid fa-scale-balanced';
		$this->indexView = 'rekapitulasi/banding/index';
		$this->module_id = 'monitoring_banding';
    }

    function perkara_list()
    {
        $year = $this->uri->segment(5);
        $month = $this->uri->segment(6);

        switch ($this->uri->segment(4)) {
            case 'sisa_bulan_lalu':
                $title = 'Perkara Banding Banding';
                break;
            case 'terima':
                $title = 'Perkara Banding Masuk';
                break;
            case 'diperbaiki':
                $title = 'Perkara Banding Diperbaiki';
                break;
            case 'dibatalkan':
                $title = 'Perkara Banding Dibatalkan';
                break;
            case 'tidak_diterima':
                $title = 'Perkara Banding Tidak Diterima';
                break;
            case 'ditolak':
                $title = 'Perkara Banding Ditolak';
                break;
            case 'lain_lain':
                $title = 'Status Lain-Lain';
                break;
            case 'jumlah_putus':
                $title = 'Perkara Banding Putus';
                break;
            case 'sisa_bulan_ini':
                $title = 'Perkara Banding Belum Putus';
                break;
            case 'syarat_formil':
                $title = 'Tidak Memenuhi Syarat / Cabut';
                break;
        }

        $title .= $month ? " Bulan " . format_date(date('Y')."-$month-01", 'MMMM') : '';
        $title .= $year ? " Tahun {$year}" : '';

        $this->vars['main_body'] = 'layout_content';
        $this->vars['view'] = 'rekapitulasi/banding/list';
        $this->vars['title'] = $title;

        $this->load->vars($this->vars);

        if ($this->input->is_ajax_request()) {
            return $this->viewAjax('rekapitulasi/banding/list', ['size' => 'modal-xl', 'showTitle' => true]);
        }

        $this->load->view('layout');
    }
}