<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sidang extends Core_Controller
{
    public function __construct()
    {
        parent::__construct();

        $this->load->helper(['app', 'url']);
        $this->load->model('ck/Sidang_Model', 'sidang');
        $this->model = $this->sidang;

        $this->indexTitle    = 'Jadwal Sidang';
        $this->indexSubtitle = 'Modul ini menampilkan data sidang berdasarkan tanggal sidang.';
        $this->indexIcon     = 'fa-solid fa-gavel';
        $this->indexView     = 'ck/sidang/index';
        $this->module_id     = 'antrian_sidang';
    }

    protected function prepare_index($options = [])
    {
        $this->load->model('settings/Ref_Model', 'ref');
        $this->vars['all_ruang_sidang'] = $this->ref->findRuangSidang();
        $this->vars['has_sipandu'] = is_db_sipandu_available();
        parent::prepare_index($options);
    }

    // =========================================================================
    // NOTIFIKASI SIDANG → PIHAK (Penggugat / Tergugat / Pengacara)
    // =========================================================================

    public function send_notif_sidang_to_pihak()
    {
        $cooldown = job_cooldown_check('send_notif_sidang_to_pihak');
        if (!$cooldown['ok']) {
            stream_cooldown_countdown([$this, 'send_stream_data'], $cooldown['remaining']);
            return;
        }

        $useQueue = $this->input->get('use_queue') !== 'false';

        $this->load->model('whatsapp/Log_Model', 'whatsappLog');
        if ($useQueue) {
            $this->load->model('whatsapp/Queue_Model', 'queue');
        }

        $sidangList = $this->sidang->get_sidang_tomorrow_for_notif();

        if (!$sidangList) {
            $this->send_stream_data(['progress' => 100, 'message' => 'Tidak ada jadwal sidang untuk dikirim', 'status' => true]);
            return;
        }

        $total                = count($sidangList);
        $x                    = 0;
        $sentCount            = 0;
        $failedCount          = 0;
        $skippedDuplicateCount = 0;
        $sentTargets          = [];

        foreach ($sidangList as $item) {
            $x++;

            // Bangun unique key per sidang+perkara+nomor agar tidak kirim duplikat
            // dalam satu batch (sebelum cek DB)
            $sidangDateKey = $item->tanggal_sidang;
            $sidangCaseKey = isset($item->perkara_id)
                ? $item->perkara_id
                : (isset($item->nomor_perkara) ? $item->nomor_perkara : 'no-case');

            $noP = isset($item->telepon_pengacara_P)
                ? cleanse_phone_number($item->telepon_pengacara_P)
                : (isset($item->telepon_P) ? cleanse_phone_number($item->telepon_P) : []);

            $noT = isset($item->telepon_pengacara_T)
                ? cleanse_phone_number($item->telepon_pengacara_T)
                : (isset($item->telepon_T) ? cleanse_phone_number($item->telepon_T) : []);

            $tanggalText = format_date($item->tanggal_sidang, 'EEEE, dd MMMM yyyy');
            $pihakText   = $this->_formatPihak($item);

            $text  = "🏛️ *INFORMASI JADWAL SIDANG*\n\n";
            $text .= "*Nomor Perkara* : {$item->nomor_perkara}\n";
            $text .= "*Tanggal Sidang* : {$tanggalText}\n";
            $text .= "*Ruang Sidang* : {$item->nama_ruang}\n";
            $text .= "*Agenda* : {$item->agenda}\n";
            $text .= $pihakText;
            $text .= "\n" . notif_footer();

            $targets = is_development()
                ? cleanse_phone_number(WA_TEST_TARGET)
                : array_merge($noP, $noT);

            foreach ($targets as $no) {
                if (empty($no)) continue;

                $targetKey = "{$sidangDateKey}|{$sidangCaseKey}|{$no}";

                if (isset($sentTargets[$targetKey])) {
                    $skippedDuplicateCount++;
                    continue;
                }

                // Cek duplikat di storage (queue atau log)
                $isDuplicate = $useQueue
                    ? $this->queue->hasDuplicateMessage($no, $text)
                    : $this->whatsappLog->hasSuccessfulMessage('sidang_today_pihak', $no, $text);

                $sentTargets[$targetKey] = true;

                if ($isDuplicate) {
                    $skippedDuplicateCount++;
                    continue;
                }

                $sendResult = $this->_dispatch_wa('sidang_today_pihak', $no, $text, $useQueue);

                $isSuccess = is_array($sendResult) && in_array($sendResult['status'], ['queued', 'completed', 'duplicate']);
                $message = (is_array($sendResult) && isset($sendResult['sent_response'])) ? $sendResult['sent_response'] : 'Notifikasi sudah dalam proses pengiriman';

                if ($isSuccess) {
                    $sentCount++;
                } else {
                    $failedCount++;
                }

                $this->send_stream_data([
                    'progress' => $this->_progress($x, $total),
                    'no'       => $x,
                    'message'  => $message,
                    'status'   => $isSuccess,
                ]);
            }
        }

        $failedSuffix = $failedCount > 0 ? ", {$failedCount} gagal terkirim" : '';

        $this->send_stream_data([
            'progress' => 100,
            'message'  => "Selesai, {$sentCount} notifikasi terkirim, {$skippedDuplicateCount} duplikat dilewati{$failedSuffix}",
            'status'   => $sentCount > 0,
        ]);
    }

	// =========================================================================
	// PRIVATE HELPERS
	// =========================================================================

    /**
     * Hitung persentase progress. Guard division-by-zero.
     */
    private function _progress($current, $total)
    {
        return $total > 0 ? (int) round($current * 100 / $total) : 100;
    }

    /**
     * Kirim WA — abstraksi queue vs direct agar tidak ada duplikasi logika
     * di setiap loop.
     */
    private function _dispatch_wa($type, $target, $text, $useQueue)
    {
        $waData = compact('type', 'target', 'text');
        $sendResult = $useQueue ? queue_wa_message($waData) : send_wa($waData);
        return $sendResult;
    }


    private function _formatPihak($item)
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
            // Buang <br /> terakhir sebelum posisi pemisah
            $pihak1 = $this->_replace_pattern(preg_replace('/<br \/>(?=[^<]*$)/', '', substr($para_pihak, 0, $pos)));
            $pihak2 = $this->_replace_pattern(substr($para_pihak, $pos));
            return $pihak1 . "\n" . $pihak2 . "\n";
        }

        return $this->_replace_pattern($para_pihak) . "\n";
    }

    private function _replace_pattern($string)
    {
        return str_replace(
            ['Penggugat:', 'Pemohon:', 'Tergugat:', 'Termohon:', '<br />'],
            ['*Penggugat* :', '*Pemohon* :', '*Tergugat* :', '*Termohon* :', "\n▪️ "],
            $string
        );
    }
}
