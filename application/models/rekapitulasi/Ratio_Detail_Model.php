<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Ratio_Detail_Model extends Sipp_Base_Model
{

	public function get_bas_belum_unggah_detail()
	{
		$tahun = date('Y');
		$hari_ini = date('Y-m-d');

		$subqueryPp = $this->database
			->select('
                    perkara_panitera_pn.perkara_id,
                    panitera_pn.id AS panitera_id,
                    panitera_pn.nip,
                    panitera_pn.nama_gelar
                ')
			->from('perkara_panitera_pn')
			->join('panitera_pn', 'panitera_pn.id = perkara_panitera_pn.panitera_id', 'inner')
			->where('perkara_panitera_pn.urutan', 1)
			->where('perkara_panitera_pn.aktif', 'Y')
			->group_by('perkara_panitera_pn.perkara_id')
			->get_compiled_select();

		$subqueryHk = $this->get_hakim_subquery();

		// NOTE: nama_gelar is selected AS nama_pp with no "[kode] " prefix,
		// deliberately matching the plain nama_gelar format used by
		// get_bas_belum_unggah_detail_by_period(), get_total_sidang_per_pp(),
		// and get_total_sidang_bulan_lalu(). Those methods' results get
		// matched up by nama_pp/nama_gelar as a map key (see
		// Generate.php::get_bas_period_summary_data()); if this method
		// formatted the name differently (e.g. with a "[kode] " prefix, as
		// it previously did), every PP with a kode would silently fail to
		// match against the total_sidang_per_pp map and be reported with a
		// total_sidang of 0 / an incorrect percentage.
		//
		// The previous 'left' join to perkara_penetapan was also removed:
		// none of its columns were ever selected, and because
		// perkara_penetapan isn't grouped/deduped per perkara_id, a perkara
		// with more than one penetapan row would have silently produced
		// duplicate rows in the result set.
		return $this->database
			->select('
                    pp.panitera_id,
                    pp.nip,
                    pp.nama_gelar AS nama_pp,
                    hk.hakim_nama,
                    jd.id AS jadwal_sidang_id,
                    jd.perkara_id,
                    p.nomor_perkara,
                    p.jenis_perkara_nama,
                    jd.tanggal_sidang,
                    jd.agenda
                ')
			->from('perkara_jadwal_sidang jd')
			->join('perkara p', 'p.perkara_id = jd.perkara_id', 'inner')
			->join('(' . $subqueryPp . ') pp', 'pp.perkara_id = jd.perkara_id', 'left')
			->join('(' . $subqueryHk . ') hk', 'hk.perkara_id = jd.perkara_id', 'left')
			->where('YEAR(jd.tanggal_sidang)', $tahun)
			->where('jd.tanggal_sidang <=', $hari_ini)
			->where('jd.edoc_bas IS NULL', null, false)
			->order_by('pp.nama_gelar', 'ASC')
			->order_by('jd.tanggal_sidang', 'DESC')
			->get()
			->result();
	}

	public function get_bas_belum_unggah_detail_by_period($year = null, $month = null)
	{
		$year = $year ?: date('Y');
		$hari_ini = date('Y-m-d');

		$subqueryPp = $this->database
			->select('
                    perkara_panitera_pn.perkara_id,
                    panitera_pn.id AS panitera_id,
                    panitera_pn.nip,
                    panitera_pn.nama_gelar
                ')
			->from('perkara_panitera_pn')
			->join('panitera_pn', 'panitera_pn.id = perkara_panitera_pn.panitera_id', 'inner')
			->where('perkara_panitera_pn.urutan', 1)
			->where('perkara_panitera_pn.aktif', 'Y')
			->group_by('perkara_panitera_pn.perkara_id')
			->get_compiled_select();

		$this->database
			->select('
                    pp.panitera_id,
                    pp.nip,
                    pp.nama_gelar AS nama_pp,
                    jd.id AS jadwal_sidang_id,
                    jd.perkara_id,
                    p.nomor_perkara,
                    p.jenis_perkara_nama,
                    jd.tanggal_sidang,
                    jd.agenda
                ')
			->from('perkara_jadwal_sidang jd')
			->join('perkara p', 'p.perkara_id = jd.perkara_id', 'inner')
			->join('(' . $subqueryPp . ') pp', 'pp.perkara_id = jd.perkara_id', 'left')
			->where('YEAR(jd.tanggal_sidang)', $year)
			->where('jd.tanggal_sidang <=', $hari_ini)
			->where('jd.edoc_bas IS NULL', null, false);

		if ($month) {
			if (is_array($month)) {
				$this->database->where_in('MONTH(jd.tanggal_sidang)', $month);
			} else {
				$this->database->where('MONTH(jd.tanggal_sidang)', $month);
			}
		}

		$this->database
			->order_by('pp.nama_gelar', 'ASC')
			->order_by('jd.tanggal_sidang', 'DESC');

		return $this->database->get()->result();
	}

	/**
	 * $month, if given, restricts to putusan within that month (or, as an
	 * array, that set of months - e.g. a quarter's 3 months) of $year, on
	 * top of the existing year/floor/cap filters. Mirrors how
	 * get_bas_belum_unggah_detail_by_period() layers a month filter onto
	 * get_bas_belum_unggah_detail()'s base query, so Generate.php's
	 * monthly/quarterly WA-caption blocks for putus_belum_setor_detail can
	 * reuse this same query (see 'periodic'.'detail_fetcher' in
	 * report_configs()) instead of a separate near-duplicate method.
	 */
	public function get_putus_belum_setor_detail($year = null, $month = null)
	{
		$year = $year ?: date('Y');
		$today = date('Y-m-d');
		$jossDb = $this->db->database;

		$subqueryHk = $this->get_hakim_subquery();

		$subqueryPp = $this->database
			->select('
					perkara_panitera_pn.perkara_id,
					panitera_pn.id AS panitera_id,
					panitera_pn.nip,
					perkara_panitera_pn.aktif,
					CASE
						WHEN panitera_pn.kode IS NOT NULL AND panitera_pn.kode != \'\'
						THEN CONCAT("[", panitera_pn.kode, "] ", perkara_panitera_pn.panitera_nama)
						ELSE perkara_panitera_pn.panitera_nama
					END AS nama_pp
				')
			->from('perkara_panitera_pn')
			->join('panitera_pn', 'panitera_pn.id = perkara_panitera_pn.panitera_id', 'inner')
			->where('perkara_panitera_pn.urutan', 1)
			->where('perkara_panitera_pn.aktif', 'Y')
			->group_by('perkara_panitera_pn.perkara_id')
			->get_compiled_select();

		$subqueryPe = $this->database
			->select('perkara_id, MIN(efiling_id) AS efiling_id')
			->from('perkara_efiling_id')
			->group_by('perkara_id')
			->get_compiled_select();

		$subqueryLatestSidang = $this->database
			->select('perkara_id, MAX(tanggal_sidang) AS latest_tanggal_sidang, keterangan')
			->from('perkara_jadwal_sidang')
			->group_by('perkara_id')
			->get_compiled_select();

		$subqueryIn = $this->database
			->select('DISTINCT pp_sub.id', false)
			->from('perkara_putusan put_sub')
			->join('perkara_panitera_pn ppn_sub', 'ppn_sub.perkara_id = put_sub.perkara_id AND ppn_sub.urutan = 1 AND ppn_sub.aktif = \'Y\'', 'inner')
			->join('panitera_pn pp_sub', 'pp_sub.id = ppn_sub.panitera_id', 'inner')
			->join($jossDb . '.trans_minutation tm_sub', 'tm_sub.perkara_id = put_sub.perkara_id', 'left')
			->where('tm_sub.perkara_id IS NULL', null, false)
			->where('YEAR(put_sub.tanggal_putusan)', date('Y'))
			->where('put_sub.tanggal_putusan <=', $today)
			->get_compiled_select();

		$tblMinutasi = $this->db->database . '.trans_minutation';

		$diff_days_sql = "CASE WHEN tm.tanggal_panmudg_terima IS NOT NULL "
			. "THEN {$jossDb}.WorkingDaysBetween(c.tanggal_putusan, tm.tanggal_panmudg_terima) "
			. "ELSE {$jossDb}.WorkingDaysBetween(c.tanggal_putusan, CURDATE()) END AS diff_days";

		$this->database
			->select('
					c.perkara_id AS row_id,
					c.putusan_verstek,
					pe.efiling_id,
					pp.panitera_id,
					pp.nip,
					sp.nama AS status_putusan,
					c.perkara_id,
					p.nomor_perkara,
					hk.hakim_nama,
					pp.nama_pp,
					p.jenis_perkara_nama,
					c.tanggal_putusan,
					c.tanggal_minutasi,
					tm.tanggal_panmudg_terima,
					latest_sidang.keterangan,
					p.proses_terakhir_text
				')
			->select($diff_days_sql, false)
			->from('perkara_putusan c')
			->join('perkara p', 'p.perkara_id = c.perkara_id', 'right')
			->join('(' . $subqueryHk . ') hk', 'hk.perkara_id = c.perkara_id', 'left')
			->join('(' . $subqueryPp . ') pp', 'pp.perkara_id = c.perkara_id', 'left')
			->join('(' . $subqueryPe . ') pe', 'pe.perkara_id = c.perkara_id', 'left')
			->join('(' . $subqueryLatestSidang . ') latest_sidang', 'latest_sidang.perkara_id = c.perkara_id', 'left')
			->join('status_putusan sp', 'sp.id = c.status_putusan_id', 'left')
			->join($tblMinutasi . ' tm', 'tm.perkara_id = c.perkara_id', 'left')
			->group_start()
			->where('pp.aktif', 'Y')
			->or_where("pp.panitera_id IN ({$subqueryIn})", null, false)
			->group_end()
			->where('tm.tanggal_panmudg_terima IS NULL', null, false)
			->where('c.tanggal_putusan >=', '2024-07-01')
			->where('c.tanggal_putusan <=', $today)
			->where('YEAR(c.tanggal_putusan)', $year);

		if ($month) {
			if (is_array($month)) {
				$this->database->where_in('MONTH(c.tanggal_putusan)', $month);
			} else {
				$this->database->where('MONTH(c.tanggal_putusan)', $month);
			}
		}

		return $this->database
			->order_by("(CASE WHEN tm.tanggal_panmudg_terima IS NULL THEN 0 ELSE 1 END)", 'ASC', false)
			->order_by('c.tanggal_putusan', 'ASC')
			->order_by('tm.tanggal_panmudg_terima', 'DESC')
			->order_by('p.alur_perkara_id', 'ASC')
			->order_by('c.perkara_id', 'ASC')
			->get()
			->result();
	}

	/**
	 * Total putusan count per PP for the year, keyed by nama_pp - the
	 * "universe" total that get_putus_belum_setor_detail()'s pending count
	 * is a subset of. Mirrors get_total_sidang_per_pp()'s shape/pattern but
	 * for putusan instead of jadwal sidang.
	 *
	 * nama_pp here MUST be derived exactly the same way (same CONCAT "[kode] "
	 * logic) as get_putus_belum_setor_detail()'s subqueryPp, since callers in
	 * Generate.php match this map's keys against that method's rows by
	 * nama_pp. Also applies the same '2024-07-01' floor and "<= today" cap as
	 * that method so the total and pending counts are drawn from the same
	 * universe of putusan and the resulting percentage is meaningful.
	 */
	public function get_total_putus_per_pp($year = null)
	{
		$year = $year ?: date('Y');
		$today = date('Y-m-d');

		$subqueryPp = $this->database
			->select('
					perkara_panitera_pn.perkara_id,
					panitera_pn.id AS panitera_id,
					panitera_pn.aktif,
					CASE
						WHEN panitera_pn.kode IS NOT NULL AND panitera_pn.kode != \'\'
						THEN CONCAT("[", panitera_pn.kode, "] ", perkara_panitera_pn.panitera_nama)
						ELSE perkara_panitera_pn.panitera_nama
					END AS nama_pp
				')
			->from('perkara_panitera_pn')
			->join('panitera_pn', 'panitera_pn.id = perkara_panitera_pn.panitera_id', 'inner')
			->where('perkara_panitera_pn.urutan', 1)
			->where('perkara_panitera_pn.aktif', 'Y')
			->group_by('perkara_panitera_pn.perkara_id')
			->get_compiled_select();

		$result = $this->database
			->select('
                    pp.nama_pp,
                    pp.aktif,
                    COUNT(c.perkara_id) AS total_putus
                ')
			->from('perkara_putusan c')
			->join('(' . $subqueryPp . ') pp', 'pp.perkara_id = c.perkara_id', 'left')
			->where('YEAR(c.tanggal_putusan)', $year)
			->where('c.tanggal_putusan >=', '2024-07-01')
			->where('c.tanggal_putusan <=', $today)
			->group_by('pp.panitera_id, pp.nama_pp, pp.aktif')
			->get()
			->result();

		$data = [];
		foreach ($result as $row) {
			if (!empty($row->nama_pp)) {
				$data[$row->nama_pp] = [
					'total_putus' => intval($row->total_putus),
					'aktif' => $row->aktif,
				];
			}
		}

		return $data;
	}

	public function get_total_putus_bulan_lalu($year, $month)
	{
		$today = date('Y-m-d');

		$subqueryPp = $this->database
			->select('
					perkara_panitera_pn.perkara_id,
					panitera_pn.id AS panitera_id,
					panitera_pn.aktif,
					CASE
						WHEN panitera_pn.kode IS NOT NULL AND panitera_pn.kode != \'\'
						THEN CONCAT("[", panitera_pn.kode, "] ", perkara_panitera_pn.panitera_nama)
						ELSE perkara_panitera_pn.panitera_nama
					END AS nama_pp
				')
			->from('perkara_panitera_pn')
			->join('panitera_pn', 'panitera_pn.id = perkara_panitera_pn.panitera_id', 'inner')
			->where('perkara_panitera_pn.urutan', 1)
			->where('perkara_panitera_pn.aktif', 'Y')
			->group_by('perkara_panitera_pn.perkara_id')
			->get_compiled_select();

		$this->database
			->select('
					pp.nama_pp,
					pp.aktif,
					COUNT(c.perkara_id) AS total_putus
				')
			->from('perkara_putusan c')
			->join('(' . $subqueryPp . ') pp', 'pp.perkara_id = c.perkara_id', 'left')
			->where('YEAR(c.tanggal_putusan)', $year)
			->where('c.tanggal_putusan >=', '2024-07-01')
			->where('c.tanggal_putusan <=', $today);

		if (is_array($month)) {
			$this->database->where_in('MONTH(c.tanggal_putusan)', $month);
		} else {
			$this->database->where('MONTH(c.tanggal_putusan)', $month);
		}

		$result = $this->database
			->group_by('pp.panitera_id, pp.nama_pp, pp.aktif')
			->get()
			->result();

		$data = [];
		foreach ($result as $row) {
			if (!empty($row->nama_pp)) {
				$data[$row->nama_pp] = [
					'total_putus' => intval($row->total_putus),
					'aktif' => $row->aktif,
				];
			}
		}

		return $data;
	}

	public function get_total_sidang_per_pp($year = null)
	{
		$year = $year ?: date('Y');
		$hari_ini = date('Y-m-d');

		$subqueryPp = $this->database
			->select('
                    perkara_panitera_pn.perkara_id,
                    panitera_pn.id AS panitera_id,
                    panitera_pn.nama_gelar,
                    panitera_pn.aktif
                ')
			->from('perkara_panitera_pn')
			->join('panitera_pn', 'panitera_pn.id = perkara_panitera_pn.panitera_id', 'inner')
			->where('perkara_panitera_pn.urutan', 1)
			->where('perkara_panitera_pn.aktif', 'Y')
			->group_by('perkara_panitera_pn.perkara_id')
			->get_compiled_select();

		$result = $this->database
			->select('
                    pp.nama_gelar AS nama_pp,
                    pp.aktif,
                    COUNT(jd.id) AS total_sidang
                ')
			->from('perkara_jadwal_sidang jd')
			->join('perkara p', 'p.perkara_id = jd.perkara_id', 'inner')
			->join('(' . $subqueryPp . ') pp', 'pp.perkara_id = jd.perkara_id', 'left')
			->where('YEAR(jd.tanggal_sidang)', $year)
			->where('jd.tanggal_sidang <=', $hari_ini)
			->group_by('pp.panitera_id, pp.nama_gelar, pp.aktif')
			->get()
			->result();

		$data = [];
		foreach ($result as $row) {
			if (!empty($row->nama_pp)) {
				$data[$row->nama_pp] = [
					'total_sidang' => intval($row->total_sidang),
					'aktif' => $row->aktif,
				];
			}
		}

		return $data;
	}

	public function get_total_sidang_bulan_lalu($year, $month)
	{
		$hari_ini = date('Y-m-d');  // Use today's date to match pending query

		$subqueryPp = $this->database
			->select('
                    perkara_panitera_pn.perkara_id,
                    panitera_pn.id AS panitera_id,
                    panitera_pn.nama_gelar,
                    panitera_pn.aktif
                ')
			->from('perkara_panitera_pn')
			->join('panitera_pn', 'panitera_pn.id = perkara_panitera_pn.panitera_id', 'inner')
			->where('perkara_panitera_pn.urutan', 1)
			->where('perkara_panitera_pn.aktif', 'Y')
			->group_by('perkara_panitera_pn.perkara_id')
			->get_compiled_select();

		$this->database
			->select('
                    pp.nama_gelar AS nama_pp,
                    pp.aktif,
                    COUNT(jd.id) AS total_sidang
                ')
			->from('perkara_jadwal_sidang jd')
			->join('perkara p', 'p.perkara_id = jd.perkara_id', 'inner')
			->join('(' . $subqueryPp . ') pp', 'pp.perkara_id = jd.perkara_id', 'left')
			->where('YEAR(jd.tanggal_sidang)', $year)
			->where('jd.tanggal_sidang <=', $hari_ini);

		if (is_array($month)) {
			$this->database->where_in('MONTH(jd.tanggal_sidang)', $month);
		} else {
			$this->database->where('MONTH(jd.tanggal_sidang)', $month);
		}

		$result = $this->database
			->group_by('pp.panitera_id, pp.nama_gelar, pp.aktif')
			->get()
			->result();

		$data = [];
		foreach ($result as $row) {
			if (!empty($row->nama_pp)) {
				$data[$row->nama_pp] = [
					'total_sidang' => intval($row->total_sidang),
					'aktif' => $row->aktif,
				];
			}
		}

		return $data;
	}
}
