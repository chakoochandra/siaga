<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pegawai extends Auth_Controller
{
	protected $public_methods = ['save', 'delete', 'change_password'];

	public function __construct()
	{
		parent::__construct();

		$this->setFrom('admin');

		if (!$this->ion_auth->logged_in()) {
			if ($this->input->is_ajax_request()) {
				return $this->set_content_type(['not_logged_in' => true]);
			}
			if (!$this->session->userdata('redirect')) {
				$this->session->set_userdata('redirect', current_url());
			}
			redirect('site/login');
		}

		$this->load->model('ck/Pegawai_Model', 'pegawai');
		$this->model = $this->pegawai;

		$this->indexTitle = 'Daftar Pegawai';
		$this->indexUrl = base_url('ck/pegawai');
		$this->indexSubtitle = 'Modul ini mengelola dan menampilkan daftar pegawai.';
		$this->indexIcon = 'fa-solid fa-id-card';
		$this->indexView = 'ck/pegawai/index';
		$this->module_id = 'pegawai_nip';
	}

	function save($id = null)
	{
		$this->form_validation->set_rules('nip', 'NIP', 'required|trim|max_length[18]|regex_match[/^[0-9]{18}$/]|callback_check_nip_unique[' . (int) $id . ']');
		$this->form_validation->set_rules('phone', 'Phone', 'required|trim|max_length[20]|regex_match[/^[0-9+\-\s()]*$/]');

		$this->vars['title'] = $id ? 'Perbarui Pegawai' : 'Tambah Pegawai';
		$this->vars['message'] = '';

		$existingRecord = null;
		if ($id) {
			$existingRecord = $this->pegawai->findOne($id, true);
		}

		if (isset($_POST) && !empty($_POST)) {
			if ($this->form_validation->run() === TRUE) {
				$nip = preg_replace('/\D/', '', $this->input->post('nip', TRUE));
				$phone = $this->input->post('phone', TRUE);

				if ($this->pegawai->save($nip, $phone, $id)) {
					if (!$id) {
						$newRecord = $this->pegawai->findOneByNip($nip);
						if ($newRecord) {
							$this->ion_auth->update($newRecord->id, ['password' => $nip]);
						}
					}
					return $this->redirectAjax([
						'redirect' => base_url('ck/pegawai'),
						'status' => true,
						'message' => 'Berhasil menyimpan data pegawai',
					]);
				} else {
					$this->vars['message'] = 'Gagal menyimpan data';
				}
			}

			$this->vars['message'] = $this->vars['message'] ?: my_validation_errors();
		}

		$this->vars['form']['nip'] = [
			'type' => 'form_input',
			'name' => 'nip',
			'placeholder' => 'NIP Pegawai',
			'value' => $this->form_validation->set_value('nip', $existingRecord ? $existingRecord->nip : null),
			'required' => true,
			'maxlength' => 18,
			'pattern' => '[0-9]{18}',
			'help' => 'Isikan 18 digit NIP tanpa spasi atau karakter lain.',
		];

		$this->vars['form']['phone'] = [
			'type' => 'form_input',
			'name' => 'phone',
			'placeholder' => 'Nomor Telepon',
			'value' => $this->form_validation->set_value('phone', $existingRecord ? $existingRecord->phone : null),
			'required' => true,
			'help' => 'Isikan nomor telepon.',
		];

		$this->load->vars($this->vars);

		return $this->viewAjax('widgets/form', [
			'status' => false,
			'message' => $this->vars['message'],
		]);
	}

	/**
	 * form_validation callback: rejects an NIP that already belongs to another
	 * employee. $id is the record being edited (0 when adding a new employee),
	 * excluded from the uniqueness check so saving an unchanged NIP still passes.
	 */
	public function check_nip_unique($nip, $id)
	{
		$excludeId = $id ? $id : null;
		if ($this->pegawai->nipExists($nip, $excludeId)) {
			$this->form_validation->set_message('check_nip_unique', 'NIP {field} sudah digunakan pegawai lain.');
			return false;
		}
		return true;
	}

	/**
	 * Override: after password change in admin context, redirect to pegawai list
	 * instead of loading a profile view — closes the modal and refreshes the DataTable.
	 */
	protected function prepareRedirectView($id_user, $data)
	{
		return $this->redirectAjax(array_merge($data, [
			'redirect' => base_url('ck/pegawai'),
		]));
	}
}
