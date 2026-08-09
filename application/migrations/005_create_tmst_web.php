<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Migration_Create_Tmst_web extends CI_Migration {

	public function up()
	{
		if ($this->db->table_exists('tmst_web'))
		{
			return;
		}

		// Create table structure
		$this->dbforge->add_field(array(
		'id' => array(
			'type' => 'INT',
			'unsigned' => TRUE,
			'null' => FALSE,
			'auto_increment' => TRUE,
		),
			'name' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,
			),
			'url' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => FALSE,
			),
			'tag' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'default' => '',
			),
			'category' => array(
				'type' => 'VARCHAR',
				'constraint' => 50,
				'null' => FALSE,
			),
			'icon' => array(
				'type' => 'VARCHAR',
				'constraint' => 255,
				'null' => TRUE,
				'default' => NULL,
			),
			'order' => array(
				'type' => 'INT',
				'null' => TRUE,
				'default' => NULL,
			),
			'description' => array(
				'type' => 'TEXT',
				'null' => TRUE,
			),
			'icon_width' => array(
				'type' => 'INT',
				'null' => TRUE,
				'default' => NULL,
			),
			'icon_height' => array(
				'type' => 'INT',
				'null' => TRUE,
				'default' => NULL,
			),
			'is_active' => array(
				'type' => 'TINYINT',
				'constraint' => 1,
				'null' => FALSE,
				'default' => 1,
			),
		));
		$this->dbforge->add_key('id', TRUE);
		$this->dbforge->create_table('tmst_web');

		// Insert records
		$data = array(
			array('id' => 1, 'name' => 'Mahkamah Agung RI', 'url' => 'https://mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'ma.png', 'order' => 1, 'description' => 'Portal resmi Mahkamah Agung Republik Indonesia', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 2, 'name' => 'SIWAS MA-RI', 'url' => 'https://siwas.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'siwas.png', 'order' => NULL, 'description' => 'Sistem Pengawasan MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 3, 'name' => 'JDIH MA-RI', 'url' => 'https://jdih.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'jdih_ma.png', 'order' => NULL, 'description' => 'Jaringan Dokumentasi dan Informasi Hukum MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 4, 'name' => 'e-Court MA-RI', 'url' => 'https://ecourt.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'ecourt.png', 'order' => NULL, 'description' => 'Layanan Pendaftaran Perkara Online MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 5, 'name' => 'Direktori Putusan Admin', 'url' => 'https://putusan.mahkamahagung.go.id/admin/main', 'tag' => '', 'category' => 'MA', 'icon' => 'dirput_v2.png', 'order' => NULL, 'description' => 'Direktori Putusan Pengadilan Administrasi', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 6, 'name' => 'Direktori Putusan', 'url' => 'https://putusan3.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'dirput_v3.png', 'order' => NULL, 'description' => 'Direktori Putusan Pengadilan Versi 3', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 7, 'name' => 'SIKEP', 'url' => 'https://sikep.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'sikep.png', 'order' => NULL, 'description' => 'Sistem Informasi Kepegawaian MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 8, 'name' => 'SIMARI', 'url' => 'https://simari.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'simari.png', 'order' => NULL, 'description' => 'Sistem Informasi Manajemen MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 9, 'name' => 'KOMDANAS', 'url' => 'https://komdanas.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'komdanas.png', 'order' => NULL, 'description' => 'Komunikasi Data Nasional MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 10, 'name' => 'e-SADEWA', 'url' => 'https://e-sadewa.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'esadewa.png', 'order' => NULL, 'description' => 'Sistem Administrasi Keuangan MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 11, 'name' => 'e-BIMA', 'url' => 'https://e-bima.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'ebima.png', 'order' => NULL, 'description' => 'Sistem Informasi Bantuan Hukum MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 12, 'name' => 'e-IPLANS', 'url' => 'https://eiplans.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'eiplans.png', 'order' => NULL, 'description' => 'Sistem Perencanaan MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 13, 'name' => 'e-PRIMA', 'url' => 'https://e-prima.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'eprima.png', 'order' => NULL, 'description' => 'Sistem Informasi Prima Pelayanan MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 14, 'name' => 'e-Learning MA-RI', 'url' => 'https://elearning.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'elearning.png', 'order' => NULL, 'description' => 'Platform E-Learning MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 15, 'name' => 'SIPP MA-RI', 'url' => 'https://sipp-ma.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'sipp_ma.png', 'order' => NULL, 'description' => 'Sistem Informasi Penelusuran Perkara MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 16, 'name' => 'Perpustakaan MA-RI', 'url' => 'https://perpustakaan.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'perpustakaan_ma.png', 'order' => NULL, 'description' => 'Perpustakaan Digital MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 17, 'name' => 'Email MA-RI', 'url' => 'https://vmail.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'email.png', 'order' => NULL, 'description' => 'Email Resmi MA-RI (VMAIL)', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 18, 'name' => 'Informasi Perkara', 'url' => 'https://kepaniteraan.mahkamahagung.go.id/perkara/', 'tag' => '', 'category' => 'MA', 'icon' => 'info_perkara.png', 'order' => NULL, 'description' => 'Layanan Informasi Perkara MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 19, 'name' => 'PPID MA-RI', 'url' => 'https://eppid.mahkamahagung.go.id/web/beranda', 'tag' => '', 'category' => 'MA', 'icon' => 'ppid.png', 'order' => NULL, 'description' => 'Pejabat Pengelola Informasi dan Dokumentasi MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 20, 'name' => 'SAKIP MA-RI', 'url' => 'https://www.mahkamahagung.go.id/id/sakip', 'tag' => '', 'category' => 'MA', 'icon' => 'sakip.png', 'order' => NULL, 'description' => 'Sistem Akuntabilitas Kinerja MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 21, 'name' => 'Badan Litbang Diklat MA-RI', 'url' => 'https://bldk.mahkamahagung.go.id/id/', 'tag' => '', 'category' => 'MA', 'icon' => 'bldk.png', 'order' => NULL, 'description' => 'Badan Penelitian dan Pengembangan Diklat MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 22, 'name' => 'Kepaniteraan MA-RI', 'url' => 'https://kepaniteraan.mahkamahagung.go.id/', 'tag' => '', 'category' => 'MA', 'icon' => 'kepaniteraan.png', 'order' => NULL, 'description' => 'Kepaniteraan Mahkamah Agung RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 23, 'name' => 'Email MA-RI', 'url' => 'https://mail.mahkamahagung.go.id', 'tag' => '', 'category' => 'MA', 'icon' => 'email.png', 'order' => NULL, 'description' => 'Email Alternatif MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 24, 'name' => 'Buku Puslitbang Kumdil', 'url' => 'https://ebook.bldk.mahkamahagung.go.id/', 'tag' => '', 'category' => 'MA', 'icon' => 'ebook.png', 'order' => NULL, 'description' => 'Buku Digital Puslitbang Kumdil', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 25, 'name' => 'Library Of MA Corporate University', 'url' => 'https://perpustakaan.bldk.mahkamahagung.go.id/', 'tag' => '', 'category' => 'MA', 'icon' => 'corporate_university.png', 'order' => NULL, 'description' => 'Perpustakaan Corporate University MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 26, 'name' => 'Badilag', 'url' => 'https://badilag.mahkamahagung.go.id', 'tag' => '', 'category' => 'Eselon1', 'icon' => 'badilag.png', 'order' => 1, 'description' => 'Portal resmi Badan Peradilan Agama MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 27, 'name' => 'KINSATKER', 'url' => 'https://kinsatker.badilag.net', 'tag' => '', 'category' => 'Eselon1', 'icon' => 'kinsatker.png', 'order' => NULL, 'description' => 'Kinerja Satker Peradilan Agama', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 28, 'name' => 'Gugatan Mandiri', 'url' => 'https://gugatanmandiri.badilag.net/gugatan_mandiri', 'tag' => '', 'category' => 'Eselon1', 'icon' => '', 'order' => NULL, 'description' => 'Layanan Gugatan Mandiri Online', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 0),
			array('id' => 29, 'name' => 'ACO', 'url' => 'https://cctv.badilag.net', 'tag' => '', 'category' => 'Eselon1', 'icon' => 'aco.png', 'order' => NULL, 'description' => 'CCTV Online Peradilan Agama', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 30, 'name' => 'SIMTALAK', 'url' => 'https://simtalak.badilag.net', 'tag' => '', 'category' => 'Eselon1', 'icon' => 'simtalak.png', 'order' => NULL, 'description' => 'Sistem Informasi Talak Badilag', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 31, 'name' => 'ABS', 'url' => 'https://abs.badilag.net', 'tag' => '', 'category' => 'Eselon1', 'icon' => 'abs.png', 'order' => NULL, 'description' => 'Absensi Online Badilag', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 32, 'name' => 'SIMTEPA', 'url' => 'https://simtepa.mahkamahagung.go.id', 'tag' => '', 'category' => 'Eselon1', 'icon' => 'simtepa.png', 'order' => NULL, 'description' => 'Sistem Teknologi Informasi MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 33, 'name' => 'ELEMENT BADILAG', 'url' => 'https://legalisasi.badilag.ne', 'tag' => '', 'category' => 'Eselon1', 'icon' => 'element.png', 'order' => NULL, 'description' => 'Element Legalisasi Badilag', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 43, 'name' => 'Web PA Sidoarjo', 'url' => 'https://pa-sidoarjo.go.id/', 'tag' => '', 'category' => 'Web', 'icon' => 'web.png', 'order' => NULL, 'description' => 'Website resmi Pengadilan Agama Sidoarjo', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 44, 'name' => 'SIASTER', 'url' => 'https://joss.pa-sidoarjo.go.id/app/siaster', 'tag' => '', 'category' => 'Web', 'icon' => 'siaster.png', 'order' => NULL, 'description' => 'Sistem Informasi Astana (SIASTER) - Manajemen Aplikasi', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 0),
			array('id' => 45, 'name' => 'SIPP WEB', 'url' => 'https://sipp.pa-sidoarjo.go.id/', 'tag' => '', 'category' => 'Web', 'icon' => 'sipp_web.png', 'order' => NULL, 'description' => 'Sistem Informasi Penelusuran Perkara Web', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 47, 'name' => 'SIJANGKAR', 'url' => 'https://ptsp.pa-sidoarjo.go.id', 'tag' => '', 'category' => 'Web', 'icon' => 'sijangkar.png', 'order' => NULL, 'description' => 'Sistem Informasi Pelayanan Terpadu Satu Pintu', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 53, 'name' => 'Web Monitoring', 'url' => 'https://www.mahkamahagung.go.id/id/webmon', 'tag' => '', 'category' => 'MA', 'icon' => 'webmon.png', 'order' => NULL, 'description' => 'Website Monitoring MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 54, 'name' => 'MyASN', 'url' => 'https://myasn.bkn.go.id/', 'tag' => '', 'category' => 'Lain-lain', 'icon' => 'myasn.png', 'order' => NULL, 'description' => 'MyASN - Aplikasi ASN BerAKHLAK BKN', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 55, 'name' => 'SIASN', 'url' => 'https://siasn.bkn.go.id/', 'tag' => '', 'category' => 'Lain-lain', 'icon' => 'siasn.png', 'order' => NULL, 'description' => 'Sistem Informasi ASN BKN', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 56, 'name' => 'e-Kinerja', 'url' => 'https://kinerja.bkn.go.id', 'tag' => '', 'category' => 'Lain-lain', 'icon' => 'kinerja.png', 'order' => NULL, 'description' => 'E-Kinerja BKN - Penilaian Kinerja ASN', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 57, 'name' => 'SAKTI', 'url' => 'https://sakti.kemenkeu.go.id', 'tag' => '', 'category' => 'Lain-lain', 'icon' => 'sakti.png', 'order' => NULL, 'description' => 'Sistem Aplikasi Keuangan Terintegrasi', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 58, 'name' => 'SIRUP', 'url' => 'https://sirup.lkpp.go.id', 'tag' => '', 'category' => 'Lain-lain', 'icon' => 'sirup.png', 'order' => NULL, 'description' => 'Sistem Informasi Rencana Umum Pengadaan', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 59, 'name' => 'DJP ONLINE', 'url' => 'https://djponline.pajak.go.id', 'tag' => '', 'category' => 'Lain-lain', 'icon' => 'djp.png', 'order' => NULL, 'description' => 'Direktorat Jenderal Pajak Online', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 60, 'name' => 'SIHARKA LHKPN / LHKASN', 'url' => 'https://siharka.menpan.go.id', 'tag' => '', 'category' => 'Lain-lain', 'icon' => 'siharka.png', 'order' => NULL, 'description' => 'Sistem Informasi Harta Kekayaan LHKPN/LHKASN', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 61, 'name' => 'Hukum Online', 'url' => 'https://www.hukumonline.com', 'tag' => '', 'category' => 'Lain-lain', 'icon' => 'hukum_online.png', 'order' => NULL, 'description' => 'Portal Berita dan Informasi Hukum', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 62, 'name' => 'Facebook', 'url' => 'https://www.facebook.com/pengadilanagama.sidoarjo', 'tag' => '', 'category' => 'Socmed', 'icon' => 'facebook-new.png', 'order' => NULL, 'description' => 'Facebook resmi Pengadilan Agama Sidoarjo', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 63, 'name' => 'Instagram', 'url' => 'https://instagram.com/pasidoarjo_', 'tag' => '', 'category' => 'Socmed', 'icon' => 'instagram-new.png', 'order' => NULL, 'description' => 'Instagram resmi Pengadilan Agama Sidoarjo', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 64, 'name' => 'X', 'url' => 'https://twitter.com/PASidoarjo_', 'tag' => '', 'category' => 'Socmed', 'icon' => 'x.png', 'order' => NULL, 'description' => 'Twitter (X) resmi Pengadilan Agama Sidoarjo', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 65, 'name' => 'Tiktok', 'url' => 'https://www.tiktok.com/@pasidoarjo', 'tag' => '', 'category' => 'Socmed', 'icon' => 'tik-tok.png', 'order' => NULL, 'description' => 'TikTok resmi Pengadilan Agama Sidoarjo', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 66, 'name' => 'Youtube', 'url' => 'https://youtube.com/channel/UCkkJVJezC4ZUXOdSsxmDqZw', 'tag' => '', 'category' => 'Socmed', 'icon' => 'play-button-circled.png', 'order' => NULL, 'description' => 'YouTube resmi Pengadilan Agama Sidoarjo', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 67, 'name' => 'Google Maps', 'url' => 'https://g.page/r/CQRvpn0Z-orBEBE/review', 'tag' => '', 'category' => 'Socmed', 'icon' => 'gmap.png', 'order' => NULL, 'description' => 'Google Maps - Lokasi Pengadilan Agama Sidoarjo', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 69, 'name' => 'Dirput PA Sidoarjo', 'url' => 'https://putusan3.mahkamahagung.go.id/pengadilan/profil/pengadilan/pa-sidoarjo.html', 'tag' => '', 'category' => 'MA', 'icon' => '', 'order' => NULL, 'description' => 'Direktori Putusan PA Sidoarjo', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 76, 'name' => 'LPSE', 'url' => 'https://lpse.mahkamahagung.go.id/eproc4', 'tag' => '', 'category' => 'MA', 'icon' => 'lpse.png', 'order' => NULL, 'description' => 'Layanan Pengadaan Elektronik MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 78, 'name' => 'Peta e-Court', 'url' => 'https://ecourt.mahkamahagung.go.id/mapecourt_agama', 'tag' => '', 'category' => 'MA', 'icon' => 'map.png', 'order' => NULL, 'description' => 'Peta Lokasi e-Court MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 82, 'name' => 'Penilaian Triwulan', 'url' => 'https://kinsatker.badilag.net/penilaiantriwulan', 'tag' => '', 'category' => 'Eselon1', 'icon' => 'triwulan.png', 'order' => NULL, 'description' => 'Penilaian Kinerja Triwulan Badilag', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 83, 'name' => 'LLK', 'url' => 'https://llk.mahkamahagung.go.id/', 'tag' => '', 'category' => 'MA', 'icon' => 'llk.png', 'order' => NULL, 'description' => 'Laporan Laba Rugi MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 84, 'name' => 'PNBP', 'url' => 'https://pnbp.mahkamahagung.go.id/', 'tag' => '', 'category' => 'MA', 'icon' => 'pnbp.png', 'order' => NULL, 'description' => 'Informasi PNBP MA-RI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 85, 'name' => 'TTE SIMARI', 'url' => 'https://simari.mahkamahagung.go.id/tte', 'tag' => '', 'category' => 'MA', 'icon' => 'tte.png', 'order' => NULL, 'description' => 'Tanda Tangan Elektronik SIMARI', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 92, 'name' => 'Jadwal Sidang', 'url' => 'https://ptsp.pa-sidoarjo.go.id/daftar_antrian_sidang', 'tag' => '', 'category' => 'Web', 'icon' => 'sijangkar.png', 'order' => NULL, 'description' => 'Jadwal Sidang Online PA Sidoarjo', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 105, 'name' => 'SIPACAR', 'url' => 'https://joss.pa-sidoarjo.go.id/app/sipacar', 'tag' => '', 'category' => 'Web', 'icon' => 'sipacar.png', 'order' => NULL, 'description' => 'Sistem Informasi Pelayanan Akta Cerai (SIPACAR)', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 106, 'name' => 'SIPANDU', 'url' => 'http://192.168.1.14/sipandu', 'tag' => '', 'category' => 'Lokal', 'icon' => 'pengaduan.png', 'order' => NULL, 'description' => 'Sistem Informasi Pelayanan Terpadu Satu Pintu', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 107, 'name' => 'PERPUS PASDA', 'url' => 'http://192.168.1.212/perpuspasda', 'tag' => '', 'category' => 'Lokal', 'icon' => 'pengaduan.png', 'order' => NULL, 'description' => 'Perpustakaan PA Sidoarjo', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
			array('id' => 108, 'name' => 'Pendukung', 'url' => 'http://192.168.1.14/pendukung', 'tag' => '', 'category' => 'Lokal', 'icon' => 'sipp_ma.png', 'order' => NULL, 'description' => 'Sistem Informasi Pelayanan Terpadu Satu Pintu', 'icon_width' => NULL, 'icon_height' => NULL, 'is_active' => 1),
		);

		$this->db->insert_batch('tmst_web', $data);
	}

	public function down()
	{
		// if ($this->db->table_exists('tmst_web'))
		// {
		// 	$this->dbforge->drop_table('tmst_web');
		// }
	}
}