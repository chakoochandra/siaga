<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<div class="table-responsive">
			<table id="table-hakim" class="display">
				<thead>
					<tr>
						<th class="dt-center" rowspan="2">No.</th>
						<th rowspan="2">Hakim</th>
						<th class="dt-center" colspan="4">Sisa Sebelumnya</th>
						<th class="dt-center" colspan="4">Diterima</th>
						<th class="dt-center" colspan="4">Putus</th>
						<th class="dt-center" colspan="4">Sisa</th>
					</tr>
					<tr>
						<th class="text-rotatess">G</th>
						<th class="text-rotatess">P</th>
						<th class="text-rotatess">GS</th>
						<th class="text-rotatess">Jumlah</th>

						<th class="text-rotatess">G</th>
						<th class="text-rotatess">P</th>
						<th class="text-rotatess">GS</th>
						<th class="text-rotatess">Jumlah</th>

						<th class="text-rotatess">G</th>
						<th class="text-rotatess">P</th>
						<th class="text-rotatess">GS</th>
						<th class="text-rotatess">Jumlah</th>

						<th class="text-rotatess">G</th>
						<th class="text-rotatess">P</th>
						<th class="text-rotatess">GS</th>
						<th class="text-rotatess">Jumlah</th>
					</tr>
				</thead>
				<tfoot class="collapse">
					<tr>
						<td colspan="2" class="dt-right">Total</td>
						<!-- Sisa Sebelumnya - 4 columns -->
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<!-- Diterima - 4 columns -->
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<!-- Putus - 4 columns -->
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<!-- Sisa - 4 columns -->
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
				</tfoot>
			</table>
		</div>

		<hr class="mb-5">

		<div class="table-responsive">
			<table id="table-hakim-monthly" class="display">
				<thead>
					<tr>
						<th class="dt-center">No.</th>
						<th>Hakim</th>
						<th class="dt-center">Sisa Sebelumnya</th>
						<th class="dt-center">Jan</th>
						<th class="dt-center">Peb</th>
						<th class="dt-center">Mrt</th>
						<th class="dt-center">April</th>
						<th class="dt-center">Mei</th>
						<th class="dt-center">Juni</th>
						<th class="dt-center">Juli</th>
						<th class="dt-center">Agt</th>
						<th class="dt-center">Sept</th>
						<th class="dt-center">Okt</th>
						<th class="dt-center">Nov</th>
						<th class="dt-center">Des</th>
						<th class="dt-center">Jumlah</th>
					</tr>
				</thead>
				<tfoot class="collapse">
					<tr>
						<td colspan="2" class="dt-right">Total</td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
						<td></td>
					</tr>
				</tfoot>
			</table>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		const viewType = 'monthly';
		const getYear = '<?php echo $this->uri->segment(4) ?: date('Y') ?>';
		const getEcourt = '<?php echo $this->uri->segment(5) ?: 0 ?>';
		const theTime = '<?php echo time() ?>';

		// Toggle: when true, "Hakim Anggota" (jabatan_hakim_id = 2) is hidden from the
		// jabatan dropdown and excluded from the statistic + drill-down list queries
		// whenever no specific jabatan is selected (i.e. the "all jabatan" view).
		const excludeHakimAnggota = true;

		function createUrl(hakim_id, data, column, alur_id) {
			const selectedRange = $('.table-hakim_daterange input.form-control').val() || '<?php echo date('Y-01-01') . '|' . date('Y-m-d') ?>';
			const encodedRange = encodeURIComponent(selectedRange);
			// NOTE: must never be '' — CodeIgniter's URI class silently drops empty
			// segments, which shifts every segment after this one left by one and
			// corrupts hakim_id/jabatan/exclude parsing in the controller & model.
			const selectedJabatan = $(`.dropdown-jabatan-hakim-${theTime} select`).val() || '0';
			const excludeFlag = excludeHakimAnggota ? 1 : 0;
			return data <= 0 ? `<span class="text-muted">${data}</span>` : `<a href="${'<?php echo base_url('kinerja/hakim/perkara_list/') ?>'}${column}/${alur_id}/range/${encodedRange}/${hakim_id}/${selectedJabatan}/${excludeFlag}" class="btn-modal">${data}</a>`;
		}

		var columns = [{
				data: null,
				className: 'dt-right',
				render: function(data, type, row, meta) {
					return meta.row + meta.settings._iDisplayStart + 1;
				}
			}, {
				data: null,
				className: 'text-nowrap',
				render: function(data, type, row) {
					return (row.kode ? '[' + row.kode + '] ' : '') + row.hakim;
				}
			},
			// Sisa Sebelumnya: sblg (G), sblp (P), sblgs (GS), jumlah
			{
				data: 'sblg',
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					return createUrl(row.hakim_id, data, 'sisa_bulan_lalu', 15);
				}
			},
			{
				data: 'sblp',
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					return createUrl(row.hakim_id, data, 'sisa_bulan_lalu', 16);
				}
			},
			{
				data: 'sblgs',
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					return createUrl(row.hakim_id, data, 'sisa_bulan_lalu', 17);
				}
			},
			{
				data: null,
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					const calculatedValue = (parseInt(row.sblg) || 0) + (parseInt(row.sblp) || 0) + (parseInt(row.sblgs) || 0);
					return createUrl(row.hakim_id, calculatedValue, 'sisa_bulan_lalu', 0);
				}
			},
			// Diterima: tig (G), tip (P), tigs (GS), jumlah
			{
				data: 'tig',
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					return createUrl(row.hakim_id, data, 'terima', 15);
				}
			},
			{
				data: 'tip',
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					return createUrl(row.hakim_id, data, 'terima', 16);
				}
			},
			{
				data: 'tigs',
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					return createUrl(row.hakim_id, data, 'terima', 17);
				}
			},
			{
				data: null,
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					const calculatedValue = (parseInt(row.tig) || 0) + (parseInt(row.tip) || 0) + (parseInt(row.tigs) || 0);
					return createUrl(row.hakim_id, calculatedValue, 'terima', 0);
				}
			},
			// Putus: ptig (G), ptip (P), ptigs (GS), jumlah
			{
				data: 'ptig',
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					return createUrl(row.hakim_id, data, 'jumlah_putus', 15);
				}
			},
			{
				data: 'ptip',
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					return createUrl(row.hakim_id, data, 'jumlah_putus', 16);
				}
			},
			{
				data: 'ptigs',
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					return createUrl(row.hakim_id, data, 'jumlah_putus', 17);
				}
			},
			{
				data: null,
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					const calculatedValue = (parseInt(row.ptig) || 0) + (parseInt(row.ptip) || 0) + (parseInt(row.ptigs) || 0);
					return createUrl(row.hakim_id, calculatedValue, 'jumlah_putus', 0);
				}
			},
			// Sisa: bptig (G), bptip (P), bptigs (GS), jumlah
			{
				data: 'bptig',
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					return createUrl(row.hakim_id, data, 'sisa_bulan_ini', 15);
				}
			},
			{
				data: 'bptip',
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					return createUrl(row.hakim_id, data, 'sisa_bulan_ini', 16);
				}
			},
			{
				data: 'bptigs',
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					return createUrl(row.hakim_id, data, 'sisa_bulan_ini', 17);
				}
			},
			{
				data: null,
				className: 'dt-center',
				visible: true,
				render: function(data, type, row) {
					const calculatedValue = (parseInt(row.bptig) || 0) + (parseInt(row.bptip) || 0) + (parseInt(row.bptigs) || 0);
					return createUrl(row.hakim_id, calculatedValue, 'sisa_bulan_ini', 0);
				}
			},
		];

		initDataTable('#table-hakim', {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url("kinerja/hakim/get_statistic") ?>",
				data: function(d) {
					d['selectedRange'] = $('.table-hakim_daterange input.form-control').val();
					d['jabatanHakim'] = $(`.dropdown-jabatan-hakim-${theTime} select`).val();
					d['excludeHakimAnggota'] = excludeHakimAnggota ? 1 : 0;
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			ordering: [
				[1, 'asc']
			],
			showSearchField: false,
			layout: {
				topStart: {
					buttons: (function() {
						var buttons = [];

						buttons.push({
							extend: 'daterange',
							config: {
								id: "table-hakim_daterange",
								colSearch: 2,
								format: 'YYYY-MM-DD',
								opens: 'right',
								placeholder: 'Pilih tanggal',
								startDate: moment().startOf('year'),
								endDate: moment(),
								refreshCharts: true,
								cardTitleSelector: '#hakim-title'
							}
						});
						buttons.push({
							extend: 'dropdown',
							config: {
								id: 'dropdown-jabatan-hakim-' + theTime,
								class: 'dropdown-jabatan-hakim-' + theTime,
								placeholder: 'Jabatan Hakim',
								options: (function() {
									const opts = {
										1: 'Ketua Majelis',
										2: 'Hakim Anggota',
										3: 'Hakim Tunggal',
									};
									if (excludeHakimAnggota) {
										delete opts[2];
									}
									return opts;
								})(),
							},
						});

						return buttons;
					})(),
				},
			},
			columns: columns,
			columnDefs: [{
				"targets": [5, 9, 13, 17],
				"className": "dt-striped"
			}],
			footerCallback: function(tfoot, data, start, end, display) {
				const api = this.api();

				// Map each column index to its list filter key, alur_id,
				// and the row fields to sum. hakim_id = 0 means no hakim filter.
				const footerMap = {
					2: {
						column: 'sisa_bulan_lalu',
						alur_id: 15,
						fields: ['sblg']
					},
					3: {
						column: 'sisa_bulan_lalu',
						alur_id: 16,
						fields: ['sblp']
					},
					4: {
						column: 'sisa_bulan_lalu',
						alur_id: 17,
						fields: ['sblgs']
					},
					5: {
						column: 'sisa_bulan_lalu',
						alur_id: 0,
						fields: ['sblg', 'sblp', 'sblgs']
					},
					6: {
						column: 'terima',
						alur_id: 15,
						fields: ['tig']
					},
					7: {
						column: 'terima',
						alur_id: 16,
						fields: ['tip']
					},
					8: {
						column: 'terima',
						alur_id: 17,
						fields: ['tigs']
					},
					9: {
						column: 'terima',
						alur_id: 0,
						fields: ['tig', 'tip', 'tigs']
					},
					10: {
						column: 'jumlah_putus',
						alur_id: 15,
						fields: ['ptig']
					},
					11: {
						column: 'jumlah_putus',
						alur_id: 16,
						fields: ['ptip']
					},
					12: {
						column: 'jumlah_putus',
						alur_id: 17,
						fields: ['ptigs']
					},
					13: {
						column: 'jumlah_putus',
						alur_id: 0,
						fields: ['ptig', 'ptip', 'ptigs']
					},
					14: {
						column: 'sisa_bulan_ini',
						alur_id: 15,
						fields: ['bptig']
					},
					15: {
						column: 'sisa_bulan_ini',
						alur_id: 16,
						fields: ['bptip']
					},
					16: {
						column: 'sisa_bulan_ini',
						alur_id: 17,
						fields: ['bptigs']
					},
					17: {
						column: 'sisa_bulan_ini',
						alur_id: 0,
						fields: ['bptig', 'bptip', 'bptigs']
					},
				};

				const allRows = api.rows({
					page: 'all'
				}).data();

				$.each(footerMap, function(colIndex, meta) {
					const footerCell = api.column(parseInt(colIndex)).footer();
					if (!footerCell) return;

					let total = 0;
					allRows.each(function(row) {
						meta.fields.forEach(function(f) {
							total += parseInt(row[f]) || 0;
						});
					});

					$(footerCell).html(createUrl(0, total, meta.column, meta.alur_id));
				});

				$('tfoot').slideDown();
			}
		});

		// ── Monthly recap table (Jan-Des per hakim, per year), filterable by
		// category — Terima / Putus / Sisa — via the dropdown below ───────────────
		const monthlyFields = ['bln_1', 'bln_2', 'bln_3', 'bln_4', 'bln_5', 'bln_6', 'bln_7', 'bln_8', 'bln_9', 'bln_10', 'bln_11', 'bln_12'];
		const currentYear = new Date().getFullYear();

		function pad2(n) {
			return String(n).padStart(2, '0');
		}

		function getSelectedMonthlyYear() {
			return $(`.select-hakim-monthly-year-${theTime} input[type="text"]`).val() || currentYear;
		}

		function getSelectedMonthlyJabatan() {
			// Must never be '' — see note on selectedJabatan above; an empty URI
			// segment gets dropped by CI, shifting hakim_id/exclude parsing.
			return $(`.dropdown-jabatan-hakim-monthly-${theTime} select`).val() || '0';
		}

		// Maps the category filter to (a) the table title/column label and
		// (b) the drill-down `column` segment list_query() already understands
		// (see filters[] in Hakim_Model::list_query: 'terima', 'putus',
		// 'sisa_bulan_ini') — keep these in sync with the backend's category
		// switch in statistic_monthly_query()/monthly_category_condition().
		// 'sisa' here is only used for the Jumlah (month===0) column, which
		// already matches table 1's Sisa via 'sisa_bulan_ini' with
		// endDateTime = 31 Dec — the per-month Sisa cells use the
		// 'sisa_bulan_kohort' filter instead (see createMonthlyUrl() below),
		// since 'sisa_bulan_ini' has no way to also constrain registration to
		// a single month.
		const categoryMap = {
			terima: {
				label: 'Diterima',
				drillColumn: 'terima'
			},
			putus: {
				label: 'Diputus',
				drillColumn: 'putus'
			},
			sisa: {
				label: 'Sisa',
				drillColumn: 'sisa_bulan_ini'
			},
		};

		function getSelectedMonthlyCategory() {
			const val = $(`.select-hakim-monthly-category-${theTime} select`).val();
			return categoryMap[val] ? val : 'terima';
		}

		// Snapshot of the filters actually used for the data currently loaded in the
		// table. Cells and the footer must build their drill-down links from THIS,
		// not from a live re-read of the dropdowns — if a dropdown change redraws
		// the table before a fresh ajax fetch completes, a live read would produce a
		// link whose filters no longer match the numbers still shown (this was the
		// source of the number/list discrepancy).
		var monthlyFilterState = {
			year: currentYear,
			jabatan: '0',
			exclude: excludeHakimAnggota ? 1 : 0,
			category: 'terima'
		};

		// month = 1-12 for a single month, or 0 for the whole-year "Jumlah" column
		function createMonthlyUrl(hakim_id, data, year, month) {
			if (data <= 0) {
				return `<span class="text-muted">${data}</span>`;
			}
			const range = month === 0 ?
				`${year}-01-01|${year}-12-31` :
				`${year}-${pad2(month)}-01|${year}-${pad2(month)}-${pad2(new Date(year, month, 0).getDate())}`;
			const encodedRange = encodeURIComponent(range);
			const selectedJabatan = monthlyFilterState.jabatan;
			const excludeFlag = monthlyFilterState.exclude;
			// Per-month Sisa cells need the cohort variant — reconciled with
			// table 1's Sisa (bptig): outstanding as of 31 Dec of $year, not
			// as of the end of that single month. The Jumlah column
			// (month === 0) already matches table 1's Sisa via the plain
			// 'sisa_bulan_ini' filter (endDateTime = 31 Dec from the range
			// above), so it's left alone.
			const drillColumn = (monthlyFilterState.category === 'sisa' && month !== 0) ?
				'sisa_bulan_kohort' :
				categoryMap[monthlyFilterState.category].drillColumn;
			// alur '15,16,17' matches exactly what the monthly count sums — NOT '0',
			// which list_query treats as "no alur filter" and would pull in other case types
			return `<a href="${'<?php echo base_url('kinerja/hakim/perkara_list/') ?>'}${drillColumn}/15,16,17/range/${encodedRange}/${hakim_id}/${selectedJabatan}/${excludeFlag}" class="btn-modal">${data}</a>`;
		}

		// Sisa Sebelumnya column — only shown when category === 'sisa'. Drills
		// into 'sisa_sebelumnya_tahun' (backlog before Jan 1 of the year,
		// outstanding as of 31 Dec of that same year — the cohort variant
		// reconciled with table 1's Sisa), independent of
		// monthlyFilterState.category's drillColumn mapping, since this
		// column has a fixed meaning regardless of which category is
		// currently selected.
		function createSisaSebelumnyaUrl(hakim_id, data, year) {
			data = parseInt(data) || 0;
			if (data <= 0) {
				return `<span class="text-muted">${data}</span>`;
			}
			const range = `${year}-01-01|${year}-01-01`;
			const encodedRange = encodeURIComponent(range);
			const selectedJabatan = monthlyFilterState.jabatan;
			const excludeFlag = monthlyFilterState.exclude;
			return `<a href="${'<?php echo base_url('kinerja/hakim/perkara_list/') ?>'}sisa_sebelumnya_tahun/15,16,17/range/${encodedRange}/${hakim_id}/${selectedJabatan}/${excludeFlag}" class="btn-modal">${data}</a>`;
		}

		var monthlyColumns = [{
				data: null,
				className: 'dt-right',
				render: function(data, type, row, meta) {
					return meta.row + meta.settings._iDisplayStart + 1;
				}
			},
			{
				data: null,
				className: 'text-nowrap',
				render: function(data, type, row) {
					return (row.kode ? '[' + row.kode + '] ' : '') + row.hakim;
				}
			},
			{
				data: 'sisa_sebelumnya',
				className: 'dt-center',
				visible: false, // toggled by updateSisaSebelumnyaColumnVisibility() — visible only when category === 'sisa'
				render: function(data, type, row) {
					return createSisaSebelumnyaUrl(row.hakim_id, data, monthlyFilterState.year);
				}
			}
		];

		monthlyFields.forEach(function(field, idx) {
			monthlyColumns.push({
				data: field,
				className: 'dt-center',
				render: function(data, type, row) {
					return createMonthlyUrl(row.hakim_id, data, monthlyFilterState.year, idx + 1);
				}
			});
		});

		monthlyColumns.push({
			data: null,
			className: 'dt-center dt-striped',
			render: function(data, type, row) {
				let total = monthlyFields.reduce((sum, f) => sum + (parseInt(row[f]) || 0), 0);
				// Fold in the backlog carried into the year so, for category
				// 'sisa': Sisa Sebelumnya + Jan + ... + Des = Jumlah (current Sisa).
				if (monthlyFilterState.category === 'sisa') {
					total += parseInt(row.sisa_sebelumnya) || 0;
				}
				return createMonthlyUrl(row.hakim_id, total, monthlyFilterState.year, 0);
			}
		});

		// Table title reflects whichever filters produced the data currently shown —
		// built from monthlyFilterState so it always matches what's on screen (same
		// reasoning as the drill-down snapshot above).
		function computeMonthlyTitle() {
			const cat = categoryMap[monthlyFilterState.category].label;
			return `Rekap Perkara ${cat} Hakim (Bulanan) - Tahun ${monthlyFilterState.year}`;
		}

		function updateMonthlyTitle() {
			const titleText = computeMonthlyTitle();
			// initDataTable() generates the title element id from the table id as
			// `${tableId}-title` (see getTitleId() in init.js) — for
			// '#table-hakim-monthly' that's '#table-hakim-monthly-title', not
			// '#hakim-monthly-title'.
			const $explicit = $('#table-hakim-monthly-title');
			if ($explicit.length) {
				$explicit.text(titleText);
			} else {
				$('#table-hakim-monthly').closest('.card').find('.card-title').first().text(titleText);
			}
		}

		initDataTable('#table-hakim-monthly', {
			title: computeMonthlyTitle(),
			showTitle: true, // without this, init.js never creates the #table-hakim-monthly-title <h3>, so updateMonthlyTitle() has nothing to update
			ajax: {
				url: "<?php echo base_url("kinerja/hakim/get_statistic_monthly") ?>",
				data: function(d) {
					monthlyFilterState.year = getSelectedMonthlyYear();
					monthlyFilterState.jabatan = getSelectedMonthlyJabatan();
					monthlyFilterState.exclude = excludeHakimAnggota ? 1 : 0;
					monthlyFilterState.category = getSelectedMonthlyCategory();

					d['year'] = monthlyFilterState.year;
					d['jabatanHakim'] = monthlyFilterState.jabatan;
					d['excludeHakimAnggota'] = monthlyFilterState.exclude;
					d['category'] = monthlyFilterState.category;
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				},
				dataSrc: function(json) {
					updateMonthlyTitle();
					return json.data;
				}
			},
			ordering: [
				[1, 'asc']
			],
			showSearchField: false,
			layout: {
				topStart: {
					buttons: (function() {
						var buttons = [];

						buttons.push({
							extend: 'datepicker',
							config: {
								id: 'select-hakim-monthly-year-' + theTime,
								class: 'select-hakim-monthly-year-' + theTime,
								minViewMode: 'years',
								format: 'yyyy',
								placeholder: 'Tahun',
								value: currentYear,
								refreshCharts: true
							}
						});

						buttons.push({
							extend: 'dropdown',
							config: {
								id: 'select-hakim-monthly-category-' + theTime,
								class: 'select-hakim-monthly-category-' + theTime,
								placeholder: 'Kategori',
								value: 'terima',
								options: {
									terima: 'Terima',
									putus: 'Putus',
									sisa: 'Sisa',
								},
								refreshCharts: true
							}
						});

						buttons.push({
							extend: 'dropdown',
							config: {
								id: 'dropdown-jabatan-hakim-monthly-' + theTime,
								class: 'dropdown-jabatan-hakim-monthly-' + theTime,
								placeholder: 'Jabatan Hakim',
								options: (function() {
									const opts = {
										1: 'Ketua Majelis',
										2: 'Hakim Anggota',
										3: 'Hakim Tunggal',
									};
									if (excludeHakimAnggota) {
										delete opts[2];
									}
									return opts;
								})(),
							},
						});

						return buttons;
					})(),
				},
			},
			columns: monthlyColumns,
			columnDefs: [{
				// +3: No., Hakim, Sisa Sebelumnya precede the 12 month columns
				"targets": [monthlyFields.length + 3],
				"className": "dt-striped"
			}],
			footerCallback: function(tfoot, data, start, end, display) {
				const api = this.api();
				const allRows = api.rows({
					page: 'all'
				}).data();
				const year = monthlyFilterState.year;

				// Sisa Sebelumnya footer total (column 2). Harmless to compute even
				// when the column is hidden for other categories.
				const sisaSebelumnyaFooterCell = api.column(2).footer();
				if (sisaSebelumnyaFooterCell) {
					let sisaSebelumnyaTotal = 0;
					allRows.each(function(row) {
						sisaSebelumnyaTotal += parseInt(row.sisa_sebelumnya) || 0;
					});
					$(sisaSebelumnyaFooterCell).html(createSisaSebelumnyaUrl(0, sisaSebelumnyaTotal, year));
				}

				monthlyFields.forEach(function(field, idx) {
					const colIndex = idx + 3;
					const footerCell = api.column(colIndex).footer();
					if (!footerCell) return;

					let total = 0;
					allRows.each(function(row) {
						total += parseInt(row[field]) || 0;
					});

					$(footerCell).html(createMonthlyUrl(0, total, year, idx + 1));
				});

				const jumlahColIndex = monthlyFields.length + 3;
				const jumlahFooterCell = api.column(jumlahColIndex).footer();
				if (jumlahFooterCell) {
					let grandTotal = 0;
					allRows.each(function(row) {
						monthlyFields.forEach(function(f) {
							grandTotal += parseInt(row[f]) || 0;
						});
						if (monthlyFilterState.category === 'sisa') {
							grandTotal += parseInt(row.sisa_sebelumnya) || 0;
						}
					});
					$(jumlahFooterCell).html(createMonthlyUrl(0, grandTotal, year, 0));
				}

				$('#table-hakim-monthly tfoot').slideDown();
			}
		});

		// The Sisa Sebelumnya column (index 2) only makes sense when the
		// 'sisa' category is selected — hidden otherwise so Jan..Des+Jumlah
		// keep their original meaning for Terima/Putus.
		function updateSisaSebelumnyaColumnVisibility() {
			const isSisa = getSelectedMonthlyCategory() === 'sisa';
			const column = $('#table-hakim-monthly').DataTable().column(2);
			if (column.visible() !== isSisa) {
				column.visible(isSisa);
			}
		}

		// Safety net: force a full ajax refetch (not just a client-side redraw) when
		// either filter changes, so the numbers shown always match the filters —
		// same filters are then captured into monthlyFilterState via ajax.data above.
		$(document).on('change', `.select-hakim-monthly-year-${theTime} input[type="text"], .dropdown-jabatan-hakim-monthly-${theTime} select, .select-hakim-monthly-category-${theTime} select`, function() {
			updateSisaSebelumnyaColumnVisibility();
			$('#table-hakim-monthly').DataTable().ajax.reload();
		});
	});
</script>