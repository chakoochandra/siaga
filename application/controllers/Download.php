<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Download extends Core_Controller
{
	private $upload_base_path;

	public function __construct()
	{
		parent::__construct();
		$this->upload_base_path = rtrim(dirname(APPPATH), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR . 'uploads' . DIRECTORY_SEPARATOR;
	}

	public function debug()
	{
		if (ENVIRONMENT !== 'development') {
			show_error('Not Found', 404);
			return;
		}

		echo '<pre>';
		echo 'ENVIRONMENT: '        . ENVIRONMENT . "\n";
		echo 'APPPATH: '            . APPPATH . "\n";
		echo 'FCPATH: '             . FCPATH . "\n";
		echo 'Upload base path: '   . $this->upload_base_path . "\n";
		echo 'Uploads dir exists: ' . (is_dir($this->upload_base_path) ? 'YES' : 'NO') . "\n\n";

		if (is_dir($this->upload_base_path)) {
			$dirs = scandir($this->upload_base_path);
			foreach ($dirs as $dir) {
				if ($dir === '.' || $dir === '..') continue;
				$full = $this->upload_base_path . $dir;
				if (is_dir($full)) {
					echo "[$dir/]\n";
					$files = scandir($full);
					foreach ($files as $f) {
						if ($f === '.' || $f === '..') continue;
						echo "    $f (" . filesize($full . DIRECTORY_SEPARATOR . $f) . " bytes)\n";
					}
				}
			}
		}
		echo '</pre>';
		exit;
	}

	public function index($type = '', $subpath = '', $filename = '')
	{
		if (empty($type) || empty($subpath)) {
			show_error('File not found', 404);
			return;
		}

		if ($type === 'file') {
			$ci = get_instance();
			$ci->load->database();
			$record = $ci->db->where('token', $subpath)->get('file_tokens')->row();
			if (!$record) {
				show_error('File not found', 404);
				return;
			}
			$actual_folder = $record->folder;
			$fullFilename = $record->filename;
			$real_file = realpath($this->upload_base_path . $actual_folder . DIRECTORY_SEPARATOR . $fullFilename);
		} else {
			$type     = preg_replace('/[^a-z0-9_]/i', '', $type);
			$subpath  = preg_replace('/[^a-z0-9_\-\.\/()]/i', '', $subpath);
			$filename = $filename ? preg_replace('/[^a-z0-9_\-\.\/()]/i', '', $filename) : '';

			if (empty($type) || empty($subpath)) {
				show_error('File not found', 404);
				return;
			}

			if (!$this->_is_allowed_type($type)) {
				log_message('error', 'Download blocked — disallowed type: ' . $type);
				show_error('File not found', 404);
				return;
			}

			$fullFilename = $filename ? $subpath . '/' . $filename : $subpath;
			$actual_folder = $type;

			$filepath = $this->upload_base_path . $type . DIRECTORY_SEPARATOR . $fullFilename;
			$real_base = realpath($this->upload_base_path);
			$real_file = realpath($filepath);
		}

		if (!isset($real_file) || $real_file === false) {
			show_error('File not found', 404);
			return;
		}

		$real_base = realpath($this->upload_base_path);
		if ($real_base === false || strpos($real_file, rtrim($real_base, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR) !== 0) {
			log_message('error', 'Download blocked — path escape attempt: ' . $real_file);
			show_error('File not found', 404);
			return;
		}

		log_message('info', 'File download: ' . $actual_folder . '/' . $fullFilename . ' | IP: ' . $this->input->ip_address());

		if (file_exists($real_file) && is_readable($real_file)) {
			$extension = strtolower(pathinfo($fullFilename, PATHINFO_EXTENSION));
			$mime      = $this->_get_mime_type($extension);
			$download_name = basename($fullFilename);

			header('Content-Type: ' . $mime);
			header('Content-Disposition: inline; filename="' . $download_name . '"');
			header('Content-Length: ' . filesize($real_file));
			header('Cache-Control: private, max-age=3600');

			readfile($real_file);
			exit;
		} else {
			log_message('error', 'File not found or not readable: ' . $real_file);
			show_error('File tidak ditemukan', 404);
		}
	}

	private function _is_allowed_type($type)
	{
		$allowed_types = [
			'bas_documents',
			'file_audio',
			'file_name',
			'file_pihak',
			'foto_tamu',
			'gdrive_temp',
			'incoming_letters',
			'laporan',
			'laporan_doc',
			'misc_pegawai',
			'outgoing_letters',
			'pbt_ghaib',
			'photo',
			'python_temp',
			'relaas_ghaib',
			'scan_berkas',
			'sk_documents',
			'sop_documents',
			'web',
		];

		return in_array($type, $allowed_types);
	}

	private function _get_mime_type($extension)
	{
		$mime_types = [
			'pdf'  => 'application/pdf',
			'doc'  => 'application/msword',
			'docx' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
			'xls'  => 'application/vnd.ms-excel',
			'xlsx' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
			'ppt'  => 'application/vnd.ms-powerpoint',
			'pptx' => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
			'jpg'  => 'image/jpeg',
			'jpeg' => 'image/jpeg',
			'png'  => 'image/png',
			'gif'  => 'image/gif',
			'txt'  => 'text/plain',
			'zip'  => 'application/zip',
			'rar'  => 'application/x-rar-compressed',
		];

		return isset($mime_types[$extension]) ? $mime_types[$extension] : 'application/octet-stream';
	}
}
