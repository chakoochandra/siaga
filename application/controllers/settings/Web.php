<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Web extends Admin_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->model('app/Web_Model', 'web');
		$this->model = $this->web;

		$this->indexTitle = 'Daftar Web';
		$this->indexSubtitle = 'Modul pengelolaan data web.';
		$this->indexIcon = 'fa-solid fa-globe';
		$this->indexView = 'settings/web/index';
		$this->indexUrl = base_url('settings/web');
		$this->module_id = 'web';
	}

	function save($id = null)
	{
		$web = $id ? $this->model->findOne($id) : null;
		$actionText = $id ? 'memperbarui' : 'menambah';

		$this->form_validation->set_rules('name', 'Name', 'required|max_length[100]');
		$this->form_validation->set_rules('url', 'URL', 'required|max_length[250]');
		$this->form_validation->set_rules('category', 'Category', 'required');
		$this->form_validation->set_rules('order', 'Order', 'trim');
		$this->form_validation->set_rules('description', 'Description', 'max_length[500]');
		$this->form_validation->set_rules('icon_width', 'Icon Width', 'trim');
		$this->form_validation->set_rules('icon_height', 'Icon Height', 'trim');
		$this->form_validation->set_rules('is_active', 'Is Active', 'required');

		$this->vars['title'] = ($id ? 'Update' : 'Tambah') . ' Web';
		$this->vars['message'] = '';

		if (isset($_POST) && !empty($_POST)) {
			if ($this->form_validation->run() === TRUE) {
				$data = [
					'name' => $this->input->post('name'),
					'url' => $this->input->post('url'),
					'tag' => $this->input->post('tag'),
					'category' => $this->input->post('category'),
					'icon' => $this->input->post('icon'),
					'order' => $this->input->post('order'),
					'description' => $this->input->post('description'),
					'icon_width' => $this->input->post('icon_width'),
					'icon_height' => $this->input->post('icon_height'),
					'is_active' => $this->input->post('is_active'),
				];

				if ($id ? $this->model->update($id, $data) : $this->model->insert($data)) {
					return $this->redirectAjax([
						'redirect' => base_url('settings/web'),
						'status' => true,
						'message' => "Berhasil {$actionText} {$data['name']}",
					]);
				}
			}

			$this->vars['message'] = my_validation_errors();
		}

		$this->vars['form']['name'] = [
			'type' => 'form_input',
			'name' => 'name',
			'placeholder' => 'Name',
			'value' => $this->form_validation->set_value('name', $web ? $web->name : null),
			'required' => true,
		];

		$this->vars['form']['url'] = [
			'type' => 'form_input',
			'name' => 'url',
			'placeholder' => 'URL',
			'value' => $this->form_validation->set_value('url', $web ? $web->url : null),
			'required' => true,
		];

		$this->vars['form']['tag'] = [
			'type' => 'form_textarea',
			'name' => 'tag',
			'placeholder' => 'Tag',
			'value' => $this->form_validation->set_value('tag', $web ? $web->tag : null),
		];

		$category_options = [
			'' => 'Pilih Kategori',
			'Socmed' => 'Socmed',
			'Lokal' => 'Lokal',
			'Web' => 'Web',
			'MA' => 'MA',
			SATKER_ESELON_1 => SATKER_ESELON_1,
			SATKER_BANDING => SATKER_BANDING,
			'Lain-lain' => 'Lain-lain',
		];

		$this->vars['form']['category'] = [
			'type' => 'form_dropdown',
			'name' => 'category',
			'options' => $category_options,
			'selected' => $this->form_validation->set_value('category', $web ? $web->category : null),
			'required' => true,
		];

		$this->vars['form']['icon'] = [
			'type' => 'form_input',
			'name' => 'icon',
			'placeholder' => 'Icon',
			'value' => $this->form_validation->set_value('icon', $web ? $web->icon : null),
		];

		$this->vars['form']['order'] = [
			'name' => 'order',
			'placeholder' => 'Order',
			'type' => 'number',
			'value' => $this->form_validation->set_value('order', $web ? $web->order : null),
		];

		$this->vars['form']['description'] = [
			'type' => 'form_input',
			'name' => 'description',
			'placeholder' => 'Description',
			'value' => $this->form_validation->set_value('description', $web ? $web->description : null),
		];

		$this->vars['form']['icon_width'] = [
			'name' => 'icon_width',
			'placeholder' => 'Icon Width',
			'type' => 'number',
			'value' => $this->form_validation->set_value('icon_width', $web ? $web->icon_width : null),
		];

		$this->vars['form']['icon_height'] = [
			'name' => 'icon_height',
			'placeholder' => 'Icon Height',
			'type' => 'number',
			'value' => $this->form_validation->set_value('icon_height', $web ? $web->icon_height : null),
		];

		$is_active_options = [
			'1' => 'Aktif',
			'0' => 'Tidak Aktif',
		];

		$this->vars['form']['is_active'] = [
			'type' => 'form_dropdown',
			'name' => 'is_active',
			'options' => $is_active_options,
			'selected' => $this->form_validation->set_value('is_active', $web ? $web->is_active : 1),
			'required' => true,
		];

		$this->load->vars($this->vars);

		return $this->viewAjax('widgets/form', [
			'status' => false,
			'message' => $this->vars['message'],
		]);
	}
}
