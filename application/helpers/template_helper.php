<?php
defined('BASEPATH') or exit('No direct script access allowed');

if (!function_exists('collect_hakim_names')) {
	function collect_hakim_names(array $items)
	{
		$names = [];
		foreach ($items as $item) {
			if (empty($item->hakim_nama)) continue;
			foreach (explode('|', $item->hakim_nama) as $n) {
				$n = trim($n);
				if ($n !== '') $names[$n] = true;
			}
		}
		return $names ? implode(', ', array_keys($names)) : '-';
	}
}

if (!function_exists('collect_pp_names')) {
	function collect_pp_names(array $items)
	{
		$names = [];
		foreach ($items as $item) {
			if (empty($item->nama_pp)) continue;
			$names[trim($item->nama_pp)] = true;
		}
		return $names ? implode(', ', array_keys($names)) : '-';
	}
}

if (!function_exists('sidang_personal_message')) {
	function sidang_personal_message($sidang_label, $role, $recipient_name, $ruang, array $items)
	{
		$hakim_names = collect_hakim_names($items);
		$pp_names = collect_pp_names($items);

		$text  = "📋 *{$sidang_label}*\n\n";
		if ($role === 'Hakim') {
			$text .= "Hakim: {$recipient_name}\n";
			$text .= "PP: {$pp_names}\n";
		} else {
			$text .= "Hakim: {$hakim_names}\n";
			$text .= "PP: {$recipient_name}\n";
		}
		$text .= "Ruang Sidang: {$ruang}\n";
		$text .= "Jumlah Sidang: " . convert_number_to_symbol(count($items)) . "\n";
		$no = 1;
		foreach ($items as $sidang) {
			$text .= $no . ". " . (!empty($sidang->nomor_perkara) ? $sidang->nomor_perkara : '-') . "\n";
			$no++;
		}
		return rtrim($text, "\n") . "\n\n" . notif_footer();
	}
}

/**
 * WA group-notification headline, e.g. "BAS belum diunggah (12)" or
 * "Jadwal Sidang Hari Ini 10/09/2026 (5)". $target_date only matters for
 * the sidang_today_detail/edoc_putus_today report keys, whose label
 * depends on whether the (model-resolved) target date is today; pass the
 * already-resolved date string ('Y-m-d') for those, null otherwise.
 */
if (!function_exists('build_group_headline_text')) {
	function build_group_headline_text($report_key, $total_rows, $target_date = null)
	{
		$label = '';
		if (strpos($report_key, 'putus_belum_setor_detail') === 0) {
			$label = 'Putusan belum disetor';
		} elseif (strpos($report_key, 'bas_belum_unggah_detail') === 0) {
			$label = 'BAS belum diunggah';
		} elseif (strpos($report_key, 'relaas_belum_input_unggah_detail') === 0) {
			$label = 'Relaas belum diinput/unggah';
		} elseif (strpos($report_key, 'rencana_bht_detail') === 0) {
			$label = 'Rencana BHT ' . date('d/m/Y');
		} elseif (strpos($report_key, 'sidang_today_detail') === 0) {
			$is_today = ($target_date === date('Y-m-d'));
			$label = 'Jadwal Sidang' . ($is_today ? ' Hari Ini' : '') . ' ' . date('d/m/Y', strtotime($target_date));
		} elseif (strpos($report_key, 'edoc_putus_today') === 0) {
			$is_today = ($target_date === date('Y-m-d'));
			$label = 'Perkara Akan Putus' . ($is_today ? ' Hari Ini' : '') . ' ' . date('d/m/Y', strtotime($target_date));
		}

		return $label . ' (' . convert_number_to_symbol($total_rows) . ')';
	}
}

/**
 * Fallback WA group-summary text (TOTAL block + one "Jumlah" line per
 * group) used by build_group_summary() for any report_key that doesn't
 * have its own dedicated summary builder below.
 */
if (!function_exists('build_generic_group_summary')) {
	function build_generic_group_summary(array $grouped)
	{
		$globalTotal = 0;
		$groups = [];
		foreach ($grouped as $group_name => $items) {
			$total = count($items);
			$groups[] = [
				'name' => $group_name,
				'total' => $total,
			];
			$globalTotal += $total;
		}

		$summary = "====================\n";
		$summary .= "Total: " . convert_number_to_symbol($globalTotal) . "\n";
		$summary .= "====================\n\n";

		foreach ($groups as $group) {
			$summary .= "*" . $group['name'] . "*\n";
			$summary .= "Jumlah: " . convert_number_to_symbol($group['total']) . "\n\n";
		}

		return $summary;
	}
}

/**
 * Aggregate the WA caption by ruang sidang - one line per ruang with its
 * sidang count, using the same buckets $grouped already carries from
 * group_rows_for_report() (and therefore already in the same order as
 * the Excel tabs built by build_group_sheets()).
 */
if (!function_exists('build_sidang_today_group_summary')) {
	function build_sidang_today_group_summary(array $grouped)
	{
		$total = 0;
		foreach ($grouped as $items) {
			$total += count($items);
		}

		$summary = "====================\n";
		$summary .= "*TOTAL*\n";
		$summary .= "Jumlah Sidang: " . convert_number_to_symbol($total) . "\n";
		$summary .= "====================\n\n";

		foreach ($grouped as $ruang_sidang => $items) {
			$summary .= "*{$ruang_sidang}*\n";
			$summary .= "Jumlah Sidang: " . convert_number_to_symbol(count($items)) . "\n\n";
		}

		return $summary;
	}
}

if (!function_exists('build_edoc_putus_today_group_summary')) {
	function build_edoc_putus_today_group_summary(array $grouped)
	{
		if (empty($grouped)) {
			return '';
		}

		$globalTotal = 0;
		$groups = [];
		foreach ($grouped as $group_name => $items) {
			$total = count($items);
			$groups[] = [
				'name' => $group_name,
				'total' => $total,
				'items' => $items,
			];
			$globalTotal += $total;
		}

		$summary = "====================\n";
		$summary .= "Total: " . convert_number_to_symbol($globalTotal) . "\n";
		$summary .= "====================\n\n";

		foreach ($groups as $group) {
			$summary .= "*" . $group['name'] . "* (" . convert_number_to_symbol($group['total']) . ")\n";
			$no = 1;
			foreach ($group['items'] as $item) {
				$nomor_perkara = !empty($item->nomor_perkara) ? $item->nomor_perkara : '-';
				$summary .= "$no. $nomor_perkara\n";
				$no++;
			}
			$summary .= "\n";
		}

		return $summary;
	}
}

if (!function_exists('build_rencana_bht_group_summary')) {
	function build_rencana_bht_group_summary(array $grouped)
	{
		if (empty($grouped)) {
			return '';
		}

		$globalTotal = 0;
		$groups = [];
		foreach ($grouped as $group_name => $items) {
			$total = count($items);
			$groups[] = [
				'name' => $group_name,
				'total' => $total,
				'items' => $items,
			];
			$globalTotal += $total;
		}

		$summary = "====================\n";
		$summary .= "Total: " . convert_number_to_symbol($globalTotal) . "\n";
		$summary .= "====================\n";

		foreach ($groups as $group) {
			$summary .= "\n" . $group['name'] . " " . convert_number_to_symbol($group['total']) . "\n";
			$no = 1;
			foreach ($group['items'] as $item) {
				$nomor_perkara = !empty($item->nomor_perkara) ? $item->nomor_perkara : '-';
				$summary .= "$no. $nomor_perkara\n";
				$no++;
			}
		}

		return $summary . "\n";
	}
}

/**
 * Compute the structured per-PP completion summary data shared by
 * build_completion_summary_block() (WA caption text) and
 * build_summary_sheet() (Excel Ringkasan tab), for both BAS and
 * putus_belum_setor_detail. Returns:
 *   [
 *     'total_pending' => int,
 *     'total'         => int,
 *     'percentage'    => float,
 *     'groups'        => [
 *       [
 *         'nama_pp'       => string,
 *         'total'         => int,
 *         'pending_count' => int,
 *         'percentage'    => float,
 *         'aktif'         => 'Y'|'N',
 *       ],
 *       ...
 *     ],
 *   ]
 *
 * Starts from every PP with a total record for the period, not just
 * ones with pending rows, so a PP that's fully caught up (zero pending)
 * still shows up with its crown instead of silently vanishing. Groups
 * are sorted by percentage descending, then total descending.
 *
 * Generic across report types: BAS passes a $totals_per_pp map shaped
 * ['nama_pp' => ['total_sidang' => int, 'aktif' => 'Y'|'N']] with
 * $total_key = 'total_sidang'; putus_belum_setor_detail passes one
 * shaped ['nama_pp' => ['total_putus' => int, 'aktif' => 'Y'|'N']]
 * with $total_key = 'total_putus'; relaas passes one shaped
 * ['kecamatan_nama' => ['total' => int]] with $total_key = 'total'
 * and $group_field = 'kecamatan_nama'.
 */
if (!function_exists('get_completion_summary_data')) {
	function get_completion_summary_data(array $pending_rows, array $totals_per_pp, $total_key = 'total_sidang', $group_field = 'nama_pp')
	{
		$grouped = [];
		foreach ($pending_rows as $row) {
			$group_name = !empty($row->$group_field) ? $row->$group_field : 'Tanpa nama';
			if (!isset($grouped[$group_name])) {
				$grouped[$group_name] = ['pending_count' => 0];
			}
			$grouped[$group_name]['pending_count']++;
		}

		foreach ($totals_per_pp as $group_name => $info) {
			if (!isset($grouped[$group_name])) {
				$grouped[$group_name] = ['pending_count' => 0];
			}
			$grouped[$group_name]['aktif'] = isset($info['aktif']) ? $info['aktif'] : 'Y';
		}

		foreach ($grouped as $group_name => &$data) {
			$total = isset($totals_per_pp[$group_name]) ? $totals_per_pp[$group_name][$total_key] : 0;
			$data['total'] = $total;
			$data['aktif'] = isset($data['aktif']) ? $data['aktif'] : 'Y';
			$data['sudah'] = $total - $data['pending_count'];
			$data['percentage'] = $total > 0 ? round((($total - $data['pending_count']) / $total) * 100, 2) : 0;
		}
		unset($data);

		uasort($grouped, function ($a, $b) {
			if ($a['percentage'] == $b['percentage']) {
				if ($a['total'] == $b['total']) return 0;
				return ($a['total'] > $b['total']) ? -1 : 1;
			}
			return ($a['percentage'] > $b['percentage']) ? -1 : 1;
		});

		$total_pending = array_sum(array_column($grouped, 'pending_count'));
		$total_sum = array_sum(array_column($totals_per_pp, $total_key));
		$total_sudah = $total_sum - $total_pending;
		$percentage = $total_sum > 0 ? round((($total_sum - $total_pending) / $total_sum) * 100, 2) : 0;

		return [
			'total_pending' => $total_pending,
			'total_sudah'   => $total_sudah,
			'total'         => $total_sum,
			'percentage'    => $percentage,
			'groups'        => $grouped,
		];
	}
}

/**
 * Shared TOTAL-block-plus-top-10-groups WA caption formatter behind
 * build_bas_group_summary()/build_putus_group_summary()/
 * build_relaas_group_summary() in Generate.php. $total_label and
 * $pending_label drive the two count lines (e.g. "Jumlah Sidang"/
 * "Belum Unggah" for BAS, "Jumlah Putus"/"Belum Setor" for putus), and
 * $done_label is shown in place of the pending line once a group hits
 * 100%.
 */
if (!function_exists('build_completion_summary_block')) {
	function build_completion_summary_block(array $pending_rows, array $totals_per_pp, $total_key, $total_label, $pending_label, $done_label, $group_field = 'nama_pp')
	{
		$summary_data = get_completion_summary_data($pending_rows, $totals_per_pp, $total_key, $group_field);
		$groups = $summary_data['groups'];
		$total_pending = $summary_data['total_pending'];
		$total_sum = $summary_data['total'];
		$percentage = $summary_data['percentage'];

		$summary = "====================\n";
		$summary .= "*TOTAL* ({$percentage}%)\n";
		$summary .= "$total_label: " . convert_number_to_symbol($total_sum) . "\n";
		$summary .= "$pending_label: " . convert_number_to_symbol($total_pending) . "\n";
		$summary .= "====================\n\n";

		$i = 0;
		foreach ($groups as $group_name => $data) {
			if ($data['aktif'] != 'Y' && $data['pending_count'] == 0) continue; // skip inactive group with nothing pending
			if ($i >= 10) break;
			$group_percentage = $data['percentage'];
			$summary .= ($group_percentage >= 100 ? '👑 ' : '') .
				"*$group_name*" .
				($group_percentage ? " ({$group_percentage}%)" : '') . "\n";
			$summary .= "$total_label: " . convert_number_to_symbol($data['total']) . "\n";
			$summary .= ($group_percentage < 100 ? "$pending_label: " . convert_number_to_symbol($data['pending_count']) . "\n\n" : "$done_label\n\n");
			$i++;
		}

		return $summary;
	}
}

/**
 * WA caption for a personal Excel attachment that shows monthly/quarterly/
 * yearly completion percentages (bas_belum_unggah_detail,
 * putus_belum_setor_detail, relaas_belum_input_unggah_detail personal
 * notifications). Totals/pending counts are passed in already resolved -
 * this function only turns them into text.
 */
if (!function_exists('build_periodic_completion_caption')) {
	function build_periodic_completion_caption(
		$excel_caption_title,
		$nama,
		$current_year,
		$current_month_text,
		$current_quarter_text,
		$monthly_total,
		$monthly_pending,
		$quarterly_total,
		$quarterly_pending,
		$yearly_total,
		$yearly_pending,
		$total_label,
		$pending_label
	) {
		$caption = "📎 *{$excel_caption_title} - {$nama}*\n\n";

		$periods = [
			[$current_month_text, $monthly_total, $monthly_pending],
			[$current_quarter_text, $quarterly_total, $quarterly_pending],
			["Tahun {$current_year}", $yearly_total, $yearly_pending],
		];

		foreach ($periods as $period) {
			list($label, $total, $pending) = $period;
			$pct = $total > 0 ? round((($total - $pending) / $total) * 100, 2) : 0;
			$caption .= "*{$label} ({$pct}%)*\n";
			$caption .= "{$total_label}: " . convert_number_to_symbol($total) . "\n";
			$caption .= "{$pending_label}: " . convert_number_to_symbol($pending) . "\n\n";
		}

		return rtrim($caption, "\n") . "\n\n" . notif_footer();
	}
}

/**
 * WA caption for a personal Excel attachment on report types with no
 * monthly/quarterly/yearly breakdown - just a total count and, if the
 * report's 'personal' config supplies a per-item line formatter, a
 * numbered list of the items.
 */
if (!function_exists('build_simple_excel_caption')) {
	function build_simple_excel_caption($excel_caption_title, $nama, $pending_count, array $items, $lineCallback = null)
	{
		$caption = "📎 *{$excel_caption_title} - {$nama}*\n\n";
		$caption .= "Jumlah: " . convert_number_to_symbol($pending_count) . "\n";

		if ($lineCallback) {
			$no = 1;
			foreach ($items as $item) {
				$caption .= "$no. " . $lineCallback($item) . "\n";
				$no++;
			}
		}

		return rtrim($caption, "\n") . "\n\n" . notif_footer();
	}
}

/**
 * WA text for a personal notification with no Excel attachment: a header
 * (custom via personalConfig['header'], or the default "*title - nama
 * date*"), an optional subtitle, a total count, and a numbered list of
 * items via personalConfig['line'].
 */
if (!function_exists('build_personal_list_message')) {
	function build_personal_list_message(array $personalConfig, $nama, $pending_count, array $items)
	{
		$text = isset($personalConfig['header'])
			? $personalConfig['header']($nama)
			: "*📊 {$personalConfig['title']} - $nama " . date('d/m/Y') . "*\n\n";

		if (!empty($personalConfig['subtitle'])) {
			$text .= "*{$personalConfig['subtitle']}*\n";
		}

		$text .= "Jumlah: " . convert_number_to_symbol($pending_count) . "\n\n";
		$text .= "*Daftar Perkara*\n";
		$no = 1;
		foreach ($items as $item) {
			$text .= "$no. " . $personalConfig['line']($item) . "\n";
			$no++;
		}

		return rtrim($text, "\n") . "\n\n" . notif_footer();
	}
}

/**
 * WA reminder text for the twice-daily presensi (attendance) job -
 * identical apart from the HADIR/PULANG line.
 */
if (!function_exists('build_presensi_reminder_text')) {
	function build_presensi_reminder_text($is_morning_checkin)
	{
		$text = "⚠️ *Reminder Presensi SIKEP*\n\n";
		if ($is_morning_checkin) {
			$text .= "Jangan lupa untuk melakukan presensi *HADIR* sebelum waktunya habis.\n\n";
		} else {
			$text .= "Jangan lupa untuk melakukan presensi *PULANG* sebelum meninggalkan kantor.\n\n";
		}
		$text .= "Silakan lakukan presensi melalui aplikasi SIKEP di tautan sikep.mahkamahagung.go.id\n\n";
		$text .= notif_footer();

		return $text;
	}
}

/**
 * WA text for the daily "Laporan Kinerja Penyelesaian Perkara" broadcast.
 * Takes the raw $ratio/$kinerja_bas/$kinerja_minutasi rows straight from
 * Ratio_Model (each may be missing individual fields, hence the
 * isset() guards) and does its own derived-figure math (totals,
 * not-yet-uploaded/not-yet-setor counts, the e-Court display string) as
 * part of formatting the text.
 */
if (!function_exists('build_kinerja_report_text')) {
	function build_kinerja_report_text($ratio, $kinerja_bas, $kinerja_minutasi)
	{
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

		return $text;
	}
}

/**
 * WA text for the yearly "Ringkasan Mediasi Perkara" broadcast.
 * $yearData is the already-aggregated stdClass (see
 * send_notif_mediasi()'s summing of $yearRows into perkara_mediasi,
 * berhasil_akta, berhasil_cabut, berhasil_sebagian, tidak_berhasil,
 * gagal, sisa_mediasi_lalu, perkara_proses_mediasi) - the per-outcome
 * percentages are display detail, so they're computed here rather than
 * by the caller.
 */
if (!function_exists('build_mediasi_summary_text')) {
	function build_mediasi_summary_text($yearData)
	{
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

		return $text;
	}
}

/**
 * WA text for jadwal sidang notification to pihak (penggugat/tergugat/pengacara).
 * Receives a row object from Sidang_Model::get_sidang_tomorrow_for_notif()
 * and returns the formatted WhatsApp message text.
 */
if (!function_exists('build_sidang_notification_text')) {
	function build_sidang_notification_text($item)
	{
		$tanggalText = format_date($item->tanggal_sidang, 'EEEE, dd MMMM yyyy');
		$pihakText   = format_sidang_pihak($item);

		$text  = "🏛️ *INFORMASI JADWAL SIDANG*\n\n";
		$text .= "*Nomor Perkara* : {$item->nomor_perkara}\n";
		$text .= "*Tanggal Sidang* : {$tanggalText}\n";
		$text .= "*Ruang Sidang* : {$item->nama_ruang}\n";
		$text .= "*Agenda* : {$item->agenda}\n";
		$text .= $pihakText;
		$text .= "\n" . notif_footer();

		return $text;
	}
}

/**
 * Format para pihak string from sidang item.
 * Extracts Penggugat/Pemohon/Tergugat/Termohon and formats for WA.
 */
if (!function_exists('format_sidang_pihak')) {
	function format_sidang_pihak($item)
	{
		$para_pihak = isset($item->para_pihak) ? $item->para_pihak : '';
		if (empty($para_pihak)) return '';

		$pos_termohon = strpos($para_pihak, 'Termohon:');
		$pos_tergugat = strpos($para_pihak, 'Tergugat:');

		if ($pos_termohon !== false || $pos_tergugat !== false) {
			$pos   = min(
				$pos_termohon !== false ? $pos_termohon : PHP_INT_MAX,
				$pos_tergugat !== false ? $pos_tergugat : PHP_INT_MAX
			);
			$pihak1 = _sidang_replace_pattern(preg_replace('/<br \/>(?=[^<]*$)/', '', substr($para_pihak, 0, $pos)));
			$pihak2 = _sidang_replace_pattern(substr($para_pihak, $pos));
			return $pihak1 . "\n" . $pihak2 . "\n";
		}

		return _sidang_replace_pattern($para_pihak) . "\n";
	}
}

/**
 * Replace pattern for sidang pihak formatting.
 */
if (!function_exists('_sidang_replace_pattern')) {
	function _sidang_replace_pattern($string)
	{
		return str_replace(
			['Penggugat:', 'Pemohon:', 'Tergugat:', 'Termohon:', '<br />'],
			['*Penggugat* :', '*Pemohon* :', '*Tergugat* :', '*Termohon* :', "\n▪️ "],
			$string
		);
	}
}
