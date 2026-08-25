<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Config extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->model('settings/Config_Model', 'configs');
		$this->load->model('Wilayah_Model', 'wilayah');
		$this->model = $this->configs;
		$this->kota_options = $this->wilayah->findKota();

		$this->indexTitle = 'Konfigurasi Aplikasi';
		$this->indexSubtitle = 'Modul ini mengelola konfigurasi aplikasi dan pengaturan sistem.';
		$this->indexIcon = 'fa-solid fa-gears';
		$this->indexView = 'settings/config/index';
		$this->module_id = 'settings_config';
	}

	function save($id = null)
	{
		$config = $id ? $this->model->findOne($id) : null;

		if ($id && !$config) {
			return $this->redirectAjax([
				'redirect' => base_url('settings/config'),
				'status' => false,
				'message' => 'Konfigurasi yang ingin diubah tidak ditemukan.',
			]);
		}

		$actionText = $id ? 'memperbarui' : 'menambah';

		$this->form_validation->set_rules('key', 'Key', 'required|max_length[100]');
		$this->form_validation->set_rules('value', 'Value', 'required');
		$this->form_validation->set_rules('note', 'Note', 'max_length[250]');
		$this->form_validation->set_rules('category', 'Category', 'required|in_list[1,2,3,4,8]');

		$this->vars['title'] = ($id ? 'Update' : 'Tambah') . ' Konfigurasi';
		$this->vars['message'] = '';

		if (isset($_POST) && !empty($_POST)) {
			if ($this->form_validation->run() === TRUE) {
				$data = [
					'key' => $this->input->post('key'),
					'value' => $this->input->post('value'),
					'note' => $this->input->post('note'),
					'category' => $this->input->post('category'),
				];

				if ($id ? $this->model->update($id, $data) : $this->model->insert($data)) {
					return $this->redirectAjax([
						'redirect' => base_url('settings/config'),
						'status' => true,
						'message' => "Berhasil {$actionText} {$data['key']}",
					]);
				}

				$this->vars['message'] = "Gagal {$actionText} konfigurasi. Silakan periksa kembali data yang dimasukkan.";
			} else {
				$this->vars['message'] = my_validation_errors();
			}
		}

		$this->vars['form']['key'] = [
			'type' => 'form_input',
			'name' => 'key',
			'placeholder' => 'Key',
			'value' => $this->form_validation->set_value('key', $config ? $config->key : null),
			'required' => true,
		];

		$this->vars['form']['value'] = [
			'type' => 'form_input',
			'name' => 'value',
			'placeholder' => 'Value',
			'value' => $this->form_validation->set_value('value', $config ? $config->value : null),
			'required' => true,
		];

		$this->vars['form']['note'] = [
			'type' => 'form_input',
			'name' => 'note',
			'placeholder' => 'Note',
			'value' => $this->form_validation->set_value('note', $config ? $config->note : null),
		];

		$category_options = [
			'' => 'Pilih Kategori',
			'1' => 'Umum',
			'2' => 'Database',
			'3' => 'Email',
			'4' => 'Lainnya',
			'8' => 'Tanggal',
		];

		$this->vars['form']['category'] = [
			'type' => 'form_dropdown',
			'name' => 'category',
			'options' => $category_options,
			'selected' => $this->form_validation->set_value('category', $config ? $config->category : null),
			'required' => true,
		];

		$this->load->vars($this->vars);

		return $this->viewAjax('widgets/form', [
			'status' => false,
			'message' => $this->vars['message'],
		]);
	}
}
