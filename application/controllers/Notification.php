<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Notification extends Core_Controller
{
	public function __construct()
	{
		parent::__construct();
		$this->load->model('settings/Holiday_Model', 'holiday');
	}

	/**
	 * Shared body for every broadcast-style notification below: resolves
	 * the target list, loops sending/queueing $text to each target, streams
	 * progress, and streams a final terminal message.
	 *
	 * Extracted from send_notif_presensi/kinerja/mediasi,
	 * which previously each hand-rolled this same ~30-line loop. Two bugs
	 * that divergence had introduced are fixed here for all callers:
	 *   - send_notif_presensi used to have no terminal stream message when
	 *     $targets was empty, leaving the client's progress bar hanging.
	 *   - send_notif_mediasi's terminal message hardcoded status=true
	 *     instead of reflecting whether anything actually sent.
	 *
	 * @param array    $targets           Phone numbers, already cleansed.
	 * @param string   $type              'type' tag passed to queue_wa_message/send_wa.
	 * @param string   $text              Message body (identical for every target).
	 * @param bool     $useQueue          Whether to queue_wa_message() vs send_wa() directly.
	 * @param string   $doneMessageFormat sprintf() format string taking the sent count,
	 *                                    e.g. "Selesai, %d notifikasi terkirim".
	 * @param string   $noTargetsMessage  Message streamed when $targets is empty.
	 * @param int      $streamEveryNth    Only stream per-message progress every Nth
	 *                                    send (plus the last one) - send_notif_presensi
	 *                                    uses 10 since its target list can be large;
	 *                                    everyone else streams every send (1).
	 * @return int Number of targets processed.
	 */
	private function broadcast_notification(
		array $targets,
		$type,
		$text,
		$useQueue,
		$doneMessageFormat,
		$noTargetsMessage,
		$streamEveryNth = 1
	) {
		$total_targets = count($targets);

		if ($total_targets === 0) {
			$this->send_stream_data(['progress' => 100, 'message' => $noTargetsMessage, 'status' => false]);
			return 0;
		}

		$x = 0;
		foreach ($targets as $no) {
			$waData = ['type' => $type, 'target' => $no, 'text' => $text];
			$sendResult = $useQueue ? queue_wa_message($waData) : send_wa($waData);
			if (!$useQueue) {
				sleep(2);
			}

			$isSuccess = is_array($sendResult) && in_array($sendResult['status'], ['queued', 'completed', 'duplicate']);
			$message = (is_array($sendResult) && isset($sendResult['sent_response']))
				? $sendResult['sent_response']
				: 'Notifikasi sedang dalam proses pengiriman';

			$x++;

			if ($streamEveryNth <= 1 || $x % $streamEveryNth === 0 || $x === $total_targets) {
				$this->send_stream_data([
					'progress' => round($x * 100 / $total_targets),
					'no'       => $x,
					'message'  => $message,
					'status'   => $isSuccess,
				]);
			}
		}

		$this->send_stream_data([
			'progress' => 100,
			'message'  => sprintf($doneMessageFormat, $x),
			'status'   => $x > 0,
		]);

		return $x;
	}

	public function send_notif_presensi($is_morning_checkin = 1)
	{
		// Same job triggered from cron or a UI button shares this cooldown -
		// note both weekday schedules (Mon-Thu and Fri) for "hadir" share one
		// label/lock, which is fine since the natural gap between them
		// (~23.5h) is comfortably above this cooldown.
		$cooldown_label = 'send_notif_presensi_' . ($is_morning_checkin ? 'hadir' : 'pulang');
		$cooldown = is_development() ? ['ok' => true, 'elapsed' => null, 'remaining' => null] : job_cooldown_check($cooldown_label);
		if (!$cooldown['ok']) {
			stream_cooldown_countdown([$this, 'send_stream_data'], $cooldown['remaining']);
			return;
		}

		if (!is_development() && $this->holiday->is_today_holiday()) {
			$this->send_stream_data(['progress' => 100, 'message' => "Hari ini libur, reminder tidak dikirim", 'status' => true]);
			return;
		}

		$text = "⚠️ *Reminder Presensi SIKEP*\n\n";
		if ($is_morning_checkin) {
			$text .= "Jangan lupa untuk melakukan presensi *HADIR* sebelum waktunya habis.\n\n";
		} else {
			$text .= "Jangan lupa untuk melakukan presensi *PULANG* sebelum meninggalkan kantor.\n\n";
		}
		$text .= "Silakan lakukan presensi melalui aplikasi SIKEP di tautan sikep.mahkamahagung.go.id/sambung/presensi\n\n";
		$text .= notif_footer();

		$targets = is_development() ? cleanse_phone_number(WA_TEST_TARGET) : cleanse_phone_number(WA_PRESENSI_TARGET);

		$this->broadcast_notification(
			$targets,
			'Reminder Presensi',
			$text,
			true,
			'Selesai, %d notifikasi sudah dalam proses pengiriman',
			'Target WhatsApp (WA_PRESENSI_TARGET) belum diset',
			10
		);
	}

	public function send_notif_kinerja()
	{
		$cooldown = is_development() ? ['ok' => true, 'elapsed' => null, 'remaining' => null] : job_cooldown_check('send_notif_kinerja');
		if (!$cooldown['ok']) {
			stream_cooldown_countdown([$this, 'send_stream_data'], $cooldown['remaining']);
			return;
		}

		if (!is_development() && $this->holiday->is_today_holiday()) {
			$this->send_stream_data(['progress' => 100, 'message' => "Hari ini libur, notifikasi tidak dikirim", 'status' => true]);
			return;
		}

		$useQueue = $this->input->get('use_queue') !== 'false';
		$this->load->model('rekapitulasi/Ratio_Model', 'ratio');

		$ratio = $this->ratio->get_ratio();
		$kinerja_bas = $this->ratio->kinerja_bas();
		$kinerja_minutasi = $this->ratio->kinerja_minutasi();

		if (!$ratio) {
			$this->send_stream_data(['progress' => 100, 'message' => 'Data ratio tidak tersedia', 'status' => false]);
			return;
		}

		$tunggakan_tahun_lalu = isset($ratio->tunggakan_tahun_lalu) ? $ratio->tunggakan_tahun_lalu : 0;
		$masuk_tahun_ini = isset($ratio->masuk_tahun_ini) ? $ratio->masuk_tahun_ini : 0;
		$total = $tunggakan_tahun_lalu + $masuk_tahun_ini;
		$minutasi_tahun_ini = isset($ratio->minutasi_tahun_ini) ? $ratio->minutasi_tahun_ini : 0;
		$tunggakan_total = isset($ratio->tunggakan_total) ? $ratio->tunggakan_total : 0;

		$persentase_perkara = isset($ratio->persentase_perkara) ? $ratio->persentase_perkara : 0;

		$persentase_ecourt = isset($ratio->persentase_ecourt) ? $ratio->persentase_ecourt : 0;
		$ecourt = isset($ratio->ecourt) ? $ratio->ecourt : 0;
		$ecourt_display = number_format_indo($ecourt) . ' / ' . number_format_indo($masuk_tahun_ini);

		$percentage_bas = isset($kinerja_bas->percentage_bas) ? $kinerja_bas->percentage_bas : 0;
		$uploaded_bas = isset($kinerja_bas->uploaded_bas) ? $kinerja_bas->uploaded_bas : 0;
		$jumlah_sidang = isset($kinerja_bas->jumlah_sidang) ? $kinerja_bas->jumlah_sidang : 0;
		$not_uploaded_bas = $jumlah_sidang - $uploaded_bas;

		$percentage_minutasi = isset($kinerja_minutasi->percentage_minutasi) ? $kinerja_minutasi->percentage_minutasi : 0;
		$setor_putus_tahun_ini = isset($kinerja_minutasi->setor_putus_tahun_ini) ? $kinerja_minutasi->setor_putus_tahun_ini : 0;
		$jumlah_putus_tahun_ini = isset($kinerja_minutasi->jumlah_putus_tahun_ini) ? $kinerja_minutasi->jumlah_putus_tahun_ini : 0;
		$belum_setor_putus_tahun_ini = $jumlah_putus_tahun_ini - $setor_putus_tahun_ini;

		$text = "📊 *Laporan Kinerja Penyelesaian Perkara " . date('Y') . "*\n\n";

		$text .= "👉🏼 *Penanganan Perkara: " . $persentase_perkara . "%*\n";
		$text .= "  • Sisa Tahun Lalu: " . number_format_indo($tunggakan_tahun_lalu) . " perkara\n";
		$text .= "  • Masuk Tahun Ini: " . number_format_indo($masuk_tahun_ini) . " perkara\n";
		$text .= "  • Total: " . number_format_indo($total) . " perkara\n";
		$text .= "  • Minutasi Tahun Ini: " . number_format_indo($minutasi_tahun_ini) . " perkara\n";
		$text .= "  • Tunggakan: " . number_format_indo($tunggakan_total) . " perkara\n\n";

		$text .= "👉🏼 *e-Court: " . $persentase_ecourt . "%* ({$ecourt_display})\n\n";

		$text .= "👉🏼 *Unggah BAS: " . $percentage_bas . "%*\n";
		$text .= "  • Jumlah Sidang: " . number_format_indo($jumlah_sidang) . "\n";
		$text .= "  • Belum Unggah: *" . number_format_indo($not_uploaded_bas) . "*\n\n";

		$text .= "👉🏼 *Putus Setor Panmud: " . $percentage_minutasi . "%*\n";
		$text .= "  • Jumlah Putus: " . number_format_indo($jumlah_putus_tahun_ini) . "\n";
		$text .= "  • Belum Setor: *" . number_format_indo($belum_setor_putus_tahun_ini) . "*\n\n";
		$text .= notif_footer();

		$targets = is_development() ? cleanse_phone_number(WA_TEST_TARGET) : cleanse_phone_number(WA_KINERJA_TARGET);

		$this->broadcast_notification(
			$targets,
			'Laporan Kinerja Perkara',
			$text,
			$useQueue,
			'Selesai, %d notifikasi laporan kinerja terkirim',
			'Target WhatsApp (WA_KINERJA_TARGET) belum diset'
		);
	}

	public function send_notif_mediasi()
	{
		$cooldown = is_development() ? ['ok' => true, 'elapsed' => null, 'remaining' => null] : job_cooldown_check('send_notif_mediasi');
		if (!$cooldown['ok']) {
			stream_cooldown_countdown([$this, 'send_stream_data'], $cooldown['remaining']);
			return;
		}

		if (!is_development() && $this->holiday->is_today_holiday()) {
			$this->send_stream_data(['progress' => 100, 'message' => "Hari ini libur, notifikasi tidak dikirim", 'status' => true]);
			return;
		}

		$useQueue = $this->input->get('use_queue') !== 'false';
		$this->load->model('rekapitulasi/Mediasi_Model', 'mediasi');

		$stats = $this->mediasi->get_statistic();
		$currentYear = (int) date('Y');

		$yearRows = [];
		foreach ($stats as $row) {
			if (strpos((string) $row->period, (string) $currentYear) === 0) {
				$yearRows[] = $row;
			}
		}

		if (empty($yearRows)) {
			$this->send_stream_data(['progress' => 100, 'message' => 'Data statistik mediasi tidak tersedia', 'status' => false]);
			return;
		}

		$flowFields = ['perkara_mediasi', 'berhasil_akta', 'berhasil_cabut', 'berhasil_sebagian', 'tidak_berhasil', 'gagal'];

		$yearData = new stdClass();
		foreach ($flowFields as $field) {
			$yearData->$field = 0;
		}
		foreach ($yearRows as $row) {
			foreach ($flowFields as $field) {
				$yearData->$field += (int) (isset($row->$field) ? $row->$field : 0);
			}
		}

		$firstRow = reset($yearRows);
		$lastRow = end($yearRows);
		$yearData->sisa_mediasi_lalu = (int) (isset($firstRow->sisa_mediasi_lalu) ? $firstRow->sisa_mediasi_lalu : 0);
		$yearData->perkara_proses_mediasi = (int) (isset($lastRow->perkara_proses_mediasi) ? $lastRow->perkara_proses_mediasi : 0);

		$text = "📊 *Ringkasan Mediasi Perkara " . format_date(date('Y-01-01'), 'yyyy') . "*\n\n";

		$text .= "👉🏼 *Sisa Mediasi Tahun Lalu: " . number_format_indo($yearData->sisa_mediasi_lalu) . "*\n";
		$text .= "👉🏼 *Perkara Dimediasi: " . number_format_indo($yearData->perkara_mediasi) . "*\n\n";

		$text .= "👉🏼 *Hasil Mediasi:*\n";
		$pct_akta = $yearData->perkara_mediasi > 0 ? round(($yearData->berhasil_akta / $yearData->perkara_mediasi) * 100, 2) : 0;
		$pct_cabut = $yearData->perkara_mediasi > 0 ? round(($yearData->berhasil_cabut / $yearData->perkara_mediasi) * 100, 2) : 0;
		$pct_sebagian = $yearData->perkara_mediasi > 0 ? round(($yearData->berhasil_sebagian / $yearData->perkara_mediasi) * 100, 2) : 0;
		$pct_tidak = $yearData->perkara_mediasi > 0 ? round(($yearData->tidak_berhasil / $yearData->perkara_mediasi) * 100, 2) : 0;
		$pct_gagal = $yearData->perkara_mediasi > 0 ? round(($yearData->gagal / $yearData->perkara_mediasi) * 100, 2) : 0;
		$text .= "  • Berhasil Akta: " . number_format_indo($yearData->berhasil_akta) . " ({$pct_akta}%)\n";
		$text .= "  • Berhasil Cabut: " . number_format_indo($yearData->berhasil_cabut) . " ({$pct_cabut}%)\n";
		$text .= "  • Berhasil Sebagian: " . number_format_indo($yearData->berhasil_sebagian) . " ({$pct_sebagian}%)\n";
		$text .= "  • Tidak Berhasil: " . number_format_indo($yearData->tidak_berhasil) . " ({$pct_tidak}%)\n";
		$text .= "  • Gagal: " . number_format_indo($yearData->gagal) . " ({$pct_gagal}%)\n\n";

		$text .= "👉🏼 *Masih Proses Mediasi: " . number_format_indo($yearData->perkara_proses_mediasi) . "*\n\n";

		$text .= notif_footer();

		$targets = is_development() ? cleanse_phone_number(WA_TEST_TARGET) : cleanse_phone_number(WA_KINERJA_TARGET);

		// NOTE: previously this method's terminal message hardcoded
		// 'status' => true regardless of whether anything actually sent,
		// unlike every sibling method here. broadcast_notification() now
		// reports $sent_count > 0 consistently for all of them.
		$this->broadcast_notification(
			$targets,
			'mediasi_summary',
			$text,
			$useQueue,
			'Selesai, %d notifikasi mediasi summary terkirim',
			'Target WhatsApp (WA_KINERJA_TARGET) belum diset'
		);
	}
}
