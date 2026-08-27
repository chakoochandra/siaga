<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Sidang_Model extends Sipp_Base_Model
{
    /**
     * Cached result of is_db_sipandu_available() for this instance, set by
     * prepare_sidang_base and read by prepare_sidang_query()/
     * get_sidang_for_notif() so they only select E.nomor_panggil/
     * E.perkiraan_jam when the E join was actually added.
     */
    private $sipandu_available = false;

    public function __construct()
    {
        parent::__construct();
    }

    protected function list_query($where, $do_filter = true)
    {
        $selectedDateSidang = $this->input->post('selectedDateSidang') ?: date('Y-m-d');
        $selectedRuang      = $this->input->post('selectedRuang') ?: null;

        $this->colSearch = [
            'perkara.nomor_perkara',
            'perkara_jadwal_sidang.agenda',
            'perkara.para_pihak',
        ];

        $this->colOrder = [
            'perkara_jadwal_sidang.tanggal_sidang' => 'asc',
            'ruangan_sidang.kode'   => 'asc',
            'perkara_jadwal_sidang.jam_sidang'            => 'asc',
            'perkara.nomor_perkara' => 'asc',
        ];

        $this->prepare_sidang_query($selectedDateSidang);

        if ($selectedRuang) {
            $this->database->where('ruangan_sidang.id', $selectedRuang);
        }

        parent::list_query($where, $do_filter);
    }

    // -------------------------------------------------------------------------
    // Base join builder (no SELECT — callers add their own selects)
    // -------------------------------------------------------------------------

    protected function prepare_sidang_base($tanggal_sidang)
    {
        $subqueryHk = $this->get_hakim_subquery();
        $subqueryPp = $this->get_panitera_subquery();

        $this->database->from('perkara_jadwal_sidang');
        $this->database->join('perkara',              'perkara.perkara_id = perkara_jadwal_sidang.perkara_id',                                                          'left');
        $this->database->join('perkara_penetapan',    'perkara_penetapan.perkara_id = perkara.perkara_id',                                                              'left');
        $this->database->join("($subqueryPp) AS pp",  'pp.perkara_id = perkara_jadwal_sidang.perkara_id',                                                               'left');
        $this->database->join("($subqueryHk) AS hk",  'hk.perkara_id = perkara_jadwal_sidang.perkara_id',                                                               'left');
        $this->database->join('ruangan_sidang',       'ruangan_sidang.id = perkara_jadwal_sidang.ruangan_id',                                                           'left');
        $this->database->join('perkara_pihak1',       'perkara.perkara_id = perkara_pihak1.perkara_id AND perkara_pihak1.urutan = 1',                                   'left');
        $this->database->join('perkara_pihak2',       'perkara.perkara_id = perkara_pihak2.perkara_id AND perkara_pihak2.urutan = 1',                                   'left');
        $this->database->join('pihak',                'perkara_pihak1.pihak_id = pihak.id',                                                                             'left');
        $this->database->join('pihak pihak_T',        'perkara_pihak2.pihak_id = pihak_T.id',                                                                          'left');
        $this->database->join('perkara_pengacara perkara_pengacara_P', 'perkara.perkara_id = perkara_pengacara_P.perkara_id AND perkara_pengacara_P.pihak_id = pihak.id',    'left');
        $this->database->join('perkara_pengacara perkara_pengacara_T', 'perkara.perkara_id = perkara_pengacara_T.perkara_id AND perkara_pengacara_T.pihak_id = pihak_T.id', 'left');
        $this->database->join('pihak pengacara_P',    'perkara_pengacara_P.pengacara_id = pengacara_P.id',                                                              'left');
        $this->database->join('pihak pengacara_T',    'perkara_pengacara_T.pengacara_id = pengacara_T.id',                                                              'left');

        // db_sipandu (pasidoa14_antrian_ptsp) can be unreachable independently
        // of the primary DB - wrong host, table dropped, grants revoked. Only
        // add this join (and, below, only select E.* columns) when a real
        // connect-and-query check confirms it's actually available. Cache the
        // result on the instance so prepare_sidang_query()/get_sidang_for_notif()
        // can consult the SAME answer without re-checking, and so Generate.php's
        // sidang_today_columns() (via the same is_db_sipandu_available() helper)
        // stays in agreement about whether these columns exist on the rows.
        $this->sipandu_available = is_db_sipandu_available();

        if ($this->sipandu_available) {
            $this->db_sipandu = $this->load->database('db_sipandu', TRUE);
            $this->database->join(
                $this->db_sipandu->database . '.data_antrian_sidang E',
                "perkara.nomor_perkara = E.nomor_perkara AND perkara_jadwal_sidang.tanggal_sidang = DATE(E.tgl)",
                'left'
            );
        }

        $this->database->where('perkara_jadwal_sidang.tanggal_sidang', $tanggal_sidang);
    }

    // -------------------------------------------------------------------------
    // Full SELECT used by the DataTables list and the pihak notification query
    // -------------------------------------------------------------------------

    protected function prepare_sidang_query($tanggal_sidang)
    {
        $this->prepare_sidang_base($tanggal_sidang);

        $this->database->select('
			perkara_jadwal_sidang.id,
			perkara_jadwal_sidang.perkara_id,
			perkara_jadwal_sidang.urutan,
			perkara_jadwal_sidang.sidang_keliling,
			perkara_penetapan.majelis_hakim_kode,
			pp.panitera_nama  AS nama_pp,
			hk.hakim_nama     AS nama_hakim,
			perkara.nomor_perkara,
			perkara.jenis_perkara_nama,
			perkara.para_pihak,
			CONCAT("[P] ", perkara.pihak1_text,
				(CASE WHEN perkara.pihak2_text IS NOT NULL
				      THEN CONCAT("</br>[T] ", perkara.pihak2_text)
				      ELSE "" END)
			) AS pihak,
			perkara_jadwal_sidang.tanggal_sidang,
			perkara_jadwal_sidang.jam_sidang,
			perkara_jadwal_sidang.sampai_jam,
			perkara_jadwal_sidang.ruangan_id,
			CONCAT(ruangan_sidang.nama, IFNULL(CONCAT(" [", ruangan_sidang.kode, "]"), "")) AS nama_ruang,
			perkara_jadwal_sidang.agenda,
			alasan_ditunda,
			ikrar_talak,
			(CASE WHEN ikrar_talak = "Y" THEN "Ya" ELSE "Bukan" END) AS ikrar,
			(CASE
				WHEN perkara.alur_perkara_id = 16 THEN 998
				WHEN perkara.tahapan_terakhir_id = 12 THEN 999
				WHEN (perkara_jadwal_sidang.agenda LIKE "%putusan%" OR perkara_jadwal_sidang.agenda LIKE "%musyawarah%") THEN 800
				WHEN (perkara_jadwal_sidang.agenda LIKE "%lanjutan%") THEN 997
				WHEN (
					perkara_jadwal_sidang.agenda LIKE "%memanggil%"
					OR perkara_jadwal_sidang.agenda LIKE "%mermanggil%"
					OR perkara_jadwal_sidang.agenda LIKE "%panggil%"
				) THEN (CASE WHEN perkara.jenis_perkara_id IN (346, 347)
				             THEN (CASE WHEN perkara_jadwal_sidang.agenda LIKE "%bukti%" THEN 850 ELSE 900 END)
				             ELSE 997 END)
				ELSE 997
			END) AS tahapan,
			(CASE WHEN perkara.tahapan_terakhir_id = 12 THEN 3 ELSE 5 END) AS jam,
			perkara.pihak1_text AS pihak1,
			perkara.pihak2_text AS pihak2,
			pihak.nama          AS nama_P,
			(CASE WHEN pengacara_P.id IS NULL THEN pihak.telepon   ELSE NULL END) AS telepon_P,
			pihak_T.nama        AS nama_T,
			(CASE WHEN pengacara_T.id IS NULL THEN pihak_T.telepon ELSE NULL END) AS telepon_T,
			pengacara_P.nama    AS nama_pengacara_P,
			pengacara_P.telepon AS telepon_pengacara_P,
			pengacara_T.nama    AS nama_pengacara_T,
			pengacara_T.telepon AS telepon_pengacara_T
		');

        // Only select E.* if prepare_sidang_base actually added the E join
        // above - selecting an alias that was never joined would throw an
        // "Unknown column" SQL error rather than degrading gracefully.
        if ($this->sipandu_available) {
            $this->database->select('E.nomor_panggil, E.perkiraan_jam');
        }
    }

    // -------------------------------------------------------------------------
    // Notification queries
    // -------------------------------------------------------------------------

    public function get_sidang_tomorrow_for_notif()
    {
        $this->prepare_sidang_query(date('Y-m-d', strtotime('+1 day')));

        // Same restriction as get_sidang_for_notif(): pihak notifications are
        // only sent for sidang in ruang 10x (kode starting with "10"). This
        // also means sidang with no ruangan_id at all (e.g. "Pemeriksaan
        // Setempat", which has no courtroom) are naturally excluded, since
        // ruangan_sidang.kode is NULL for those rows and NULL never matches
        // a LIKE.
        $this->database->like('ruangan_sidang.kode', '10', 'after');

        $rows = $this->database->get()->result();
        return $rows;
    }

    public function get_sidang_target_date()
    {
        if (is_development()) {
            return $this->_find_nearest_sidang_date();
        }
        return date('Y-m-d');
    }

    /**
     * Fetch today's sidang with PP and Hakim NIP for notification dispatch.
     *
     * Use prepare_sidang_base (joins only, no SELECT), then declare a
     * minimal SELECT. PP NIP comes from joining panitera_pn against the pp
     * subquery alias. Hakim NIP/nama stay as GROUP_CONCAT pipe-strings from the
     * hk subquery — the controller already splits them with explode('|', ...).
     */
    public function get_sidang_for_notif($target_date = null)
    {
        if ($target_date === null) {
            $target_date = $this->get_sidang_target_date();
        }

        $this->prepare_sidang_base($target_date);

        // Join panitera_pn to resolve pp NIP — valid because pp subquery exposes panitera_id
        $this->database->join('panitera_pn', 'panitera_pn.id = pp.panitera_id', 'left');

        // Join v_sum_perkara_biaya for sisa_panjar (same pattern as Jadwal_Model)
        $this->database->join('v_sum_perkara_biaya', 'v_sum_perkara_biaya.perkara_id = perkara.perkara_id', 'left');

        // Join v_perkara for majelis_hakim_text, jenis_perkara_text, pihak texts,
        // and panitera_pengganti_text (same pattern as Jadwal_Model)
        $this->database->join('v_perkara', 'v_perkara.perkara_id = perkara.perkara_id', 'left');

        // Join perkara_efiling_id subquery for e-Court / efiling status
        // (same pattern as Durasi_Putus_Model)
        $subqueryEfiling = $this->db->select('perkara_id, MIN(efiling_id) AS efiling_id')
            ->from('perkara_efiling_id')
            ->group_by('perkara_id')
            ->get_compiled_select();
        $this->database->join("($subqueryEfiling) AS pe", 'pe.perkara_id = perkara.perkara_id', 'left');

        $this->database->select('
			perkara_jadwal_sidang.perkara_id,
			perkara_jadwal_sidang.urutan,
			perkara.nomor_perkara,
			perkara_jadwal_sidang.tanggal_sidang,
			perkara_jadwal_sidang.agenda,
			pp.panitera_nama  AS nama_pp,
			panitera_pn.nip   AS pp_nip,
			hk.hakim_nama     AS hakim_nama,
			hk.hakim_nip      AS hakim_nip,
			CONCAT("[", ruangan_sidang.kode, "] ", ruangan_sidang.nama) AS ruang_sidang,
			v_perkara.jenis_perkara_text,
			v_perkara.pihak1_text AS nama_p,
			v_perkara.pihak2_text AS nama_t,
			v_perkara.majelis_hakim_text,
			v_perkara.panitera_pengganti_text,
			perkara_pihak2.ghaib
		');

        // Only select E.* if prepare_sidang_base actually added the E join
        // above (see is_db_sipandu_available() / sipandu_available). Doing
        // this unconditionally used to succeed only by accident - it would
        // throw an SQL error the moment db_sipandu became unreachable, since
        // the join wasn't added defensively before this fix.
        if ($this->sipandu_available) {
            $this->database->select('E.nomor_panggil, E.perkiraan_jam');
        }

        $this->database->select("(v_sum_perkara_biaya.pemasukan - v_sum_perkara_biaya.pengeluaran) AS sisa_panjar", false);
        $this->database->select('pe.efiling_id');
        $this->database->select('(CASE WHEN pe.efiling_id IS NOT NULL THEN "Ya" ELSE "Tidak" END) AS ecourt_status');
        $this->database->select('(CASE WHEN perkara_pihak2.ghaib = 1 THEN "Ya" ELSE "Tidak" END) AS ghaib');

        // NOTE: hakim_nip must be exposed by get_hakim_subquery() as a
        // GROUP_CONCAT of NIPs separated by "|", matching hakim_nama ordering.
        // If hakim_nip is not available from the subquery, add it there.

        // Ordered by ruang sidang then jam sidang (not panitera_nama) since
        // Generate.php's group_rows_for_report() buckets these rows into one
        // Excel tab per ruang_sidang - this keeps each tab in chronological
        // order instead of PP-name order. The old order_by('tanggal_sidang')
        // was a no-op here: prepare_sidang_base already filters this whole
        // query to a single day, so every row shares the same date.
        $this->database->order_by('ruangan_sidang.nama', 'ASC');
        $this->database->order_by('perkara_jadwal_sidang.jam_sidang', 'ASC');
        $this->database->order_by('perkara_jadwal_sidang.urutan', 'ASC');

        $rows = $this->database->get()->result();
        return $rows;
    }

    private function _find_nearest_sidang_date()
    {
        $sql = "SELECT tanggal_sidang AS tanggal FROM (
			SELECT DISTINCT tanggal_sidang FROM perkara_jadwal_sidang
			WHERE agenda IS NOT NULL
		) AS combined
		ORDER BY ABS(DATEDIFF(tanggal_sidang, CURDATE())) ASC
		LIMIT 1";

        $query = $this->database->query($sql);
        if (!$query) {
            return date('Y-m-d');
        }

        $row = $query->row();

        return isset($row->tanggal) ? $row->tanggal : date('Y-m-d');
    }
}