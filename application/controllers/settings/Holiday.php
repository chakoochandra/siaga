<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Holiday extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();

		// $this->isAuthorized = check_auth($this->ion_auth->is_admin(), is_operator(), is_kepegawaian());
		$this->load->model('settings/Holiday_Model', 'holiday');
		$this->model = $this->holiday;
		$this->redirectUrl = base_url('settings/holiday');

		$this->indexTitle = 'Daftar Hari Libur';
		$this->indexSubtitle = 'Modul ini mengelola data hari libur nasional dan cuti bersama untuk keperluan penjadwalan.';
		$this->indexIcon = 'fa-solid fa-calendar-xmark';
		$this->indexView = 'settings/holiday/index';
		$this->module_id = 'settings_holiday';
	}

	function save($id = null)
	{
		$isNewRecord = !$id;
		$holiday = !$isNewRecord ? $this->model->findOne($id) : null;
		$actionText = !$isNewRecord ? 'memperbarui' : 'menambah';

		$this->form_validation->set_rules('jenis_libur_id', 'Jenis Libur', 'required');
		$this->form_validation->set_rules('nama', 'Nama Hari Libur', 'trim|required|max_length[100]');
		$this->form_validation->set_rules('tanggal', 'Tanggal', 'required');
		$this->form_validation->set_rules('tanggal', 'Tanggal', 'required|callback_unique_tanggal');

		$this->vars['title'] = ($id ? 'Update' : 'Tambah') . ' Hari Libur';
		$this->vars['isNewRecord'] = $isNewRecord;
		$this->vars['message'] = '';

		if (isset($_POST) && !empty($_POST)) {
			if ($this->form_validation->run() === TRUE) {
				$date = $this->input->post('tanggal');
				if ($this->input->post('jenis_libur_id') == 1 && !empty($date)) {
					$parts = explode('-', $date);
					if (count($parts) === 3) {
						$date = '0000-' . $parts[1] . '-' . $parts[2];
					}
				}

				$data = [
					'tanggal' => $date,
					'nama' => $this->input->post('nama'),
					'jenis_libur_id' => $this->input->post('jenis_libur_id'),
				];

				if (!$isNewRecord ? $this->model->update($id, $data) : $this->model->insert($data)) {
					return $this->redirectAjax([
						'redirect' => base_url('settings/holiday'),
						'status' => true,
						'message' => "Berhasil {$actionText} {$data['nama']}",
					]);
				}

				$this->vars['message'] = 'Terjadi Kesalahan';
			} else {
				$this->vars['message'] = my_validation_errors();
			}
		}

		$this->vars['form']['hiddenfield'] = [
			'type' => 'form_hidden',
			'id' => $id,
		];

		$this->load->model('settings/Ref_Model', 'ref');
		$this->vars['form']['jenis_libur_id'] = [
			'type' => 'form_dropdown',
			'name' => 'jenis_libur_id',
			'options' => $this->ref->findJenisLibur(),
			'selected' => $this->form_validation->set_value('jenis_libur_id', $holiday ? $holiday->jenis_libur_id : 2),
			'label' => 'Jenis Libur',
		];

		$this->vars['form']['nama'] = [
			'type' => 'form_input',
			'name' => 'nama',
			'placeholder' => 'Nama Hari Libur',
			'value' => $this->form_validation->set_value('nama', $holiday ? $holiday->nama : null),
		];

		$this->vars['form']['tanggal'] = [
			'type' => 'form_datepicker',
			'name' => 'tanggal',
			'value' => $this->form_validation->set_value('tanggal', $holiday ? $holiday->tanggal : null),
			'placeholder' => 'Tanggal Libur',
			'disableWeekend' => false,
		];

		$this->load->vars($this->vars);

		return $this->viewAjax('settings/holiday/form', [
			'status' => false,
			'message' => $this->vars['message'],
		]);
	}

	public function unique_tanggal($tanggal)
	{
		$jenis_libur_id = $this->input->post('jenis_libur_id');
		$id = $this->input->post('id'); // for update

		if ($this->model->check_unique_combination($jenis_libur_id, $tanggal, $id)) {
			$this->form_validation->set_message('unique_tanggal', 'Tanggal libur sudah ada.');
			return false;
		}
		return true;
	}
}
