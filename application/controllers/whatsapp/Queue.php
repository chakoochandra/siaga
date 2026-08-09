<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Queue extends Core_Controller
{
	public function __construct()
	{
		parent::__construct();

		$this->load->helper(['app']);

		$this->load->model('whatsapp/Queue_Model', 'queue');
		$this->model = $this->queue;

		$this->indexTitle = 'Antrian Notifikasi';
		$this->indexSubtitle = 'Modul ini menampilkan antrian dan proses pengiriman notifikasi WhatsApp.';
		$this->indexIcon = 'fa-brands fa-whatsapp';
		$this->indexView = 'whatsapp/queue/index';
		$this->indexUrl = base_url('whatsapp/queue');
		$this->module_id = 'whatsapp_queue';
	}

	protected function prepare_index($options = [])
	{
		$this->vars['scriptName'] = $_SERVER['PHP_SELF'];
		parent::prepare_index($this->vars);
	}

	public function process()
	{
		$this->queue->delete_completed();

		$queue_items = $this->queue->get_pending();
		$total_items = count($queue_items);
		$processed_count = 0;
		foreach ($queue_items as $index => $item) {
			$processed_count++;

			if ($this->queue->hasSuccessfulMessageToday($item->target, $item->text, $item->type)) {
				$this->queue->delete($item->id);

				$this->send_stream_data([
					'no' => $processed_count,
					'progress' => min(99, intval(($processed_count / $total_items) * 100)),
					'message' => "Pesan untuk {$item->target} sudah dikirim hari ini, dilewati",
					'status' => true,
				]);
				continue;
			}

			$file_path = null;
			if ($item->file_path) {
				$relative_path = ltrim($item->file_path, './');
				$absolute_path = FCPATH . $relative_path;

				if (is_file($absolute_path)) {
					$file_path = $absolute_path;
				} else {
					$alt_absolute_path = $item->file_path;
					if (substr($item->file_path, 0, 1) !== '/') {
						$alt_absolute_path = FCPATH . $item->file_path;
					}

					if (is_file($alt_absolute_path)) {
						$file_path = $alt_absolute_path;
					}
				}
			}

			$result = send_wa([
				'type' => $item->type,
				'target' => $item->target,
				'text' => $item->text,
				'file_path' => $file_path
			]);

			if ($result['status'] == 'completed') {
				$this->queue->delete($item->id);
			} else {
				$update_data = array(
					'attempts' => $item->attempts + 1,
					'status' => isset($result['status']) && in_array($result['status'], ['pending','processing','completed','failed','invalid_number']) ? $result['status'] : 'failed',
					'sent_response' => isset($result['sent_response']) ? $result['sent_response'] : '',
					'processed_at' => date('Y-m-d H:i:s'),
				);
				$this->queue->update($item->id, $update_data);
			}

			$this->send_stream_data([
				'no' => $processed_count,
				'progress' =>  min(99, intval(($processed_count / $total_items) * 100)),
				'message' => $result['sent_response'],
				'status' => $result['status'] == 'completed'
			]);
		}

		// Send final message when complete
		if ($processed_count == 0) {
			$this->send_stream_data([
				'progress' => 100,
				'status' => true,
				'message' => 'Tidak ada notifikasi untuk dikirim',
			]);
		} else {
			$this->send_stream_data([
				'progress' => 100,
				'status' => true,
				'message' => 'Proses pengiriman antrian selesai',
			]);
		}

		exit;
	}
}
