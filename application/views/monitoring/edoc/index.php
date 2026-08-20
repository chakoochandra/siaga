<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<div class="card card-filter" style="display: none;">
			<div class="card-body">
				<form id="filter-form" class="row g-3 align-items-center">
					<div class="col-auto">
						<label class="form-label">Tanggal Putus:</label>
						<input type="text" class="form-control daterange_dokpen_putus" placeholder="Tanggal Putus" value="">
					</div>
					<div class="col-auto">
						<label class="form-label">Status PMH:</label>
						<select class="form-select dropdown-pmh-<?php echo time() ?> select2-picker">
							<option value="">Pilih Status PMH</option>
							<option value="1" <?php echo ($this->uri->segment(5) == '1') ? 'selected' : '' ?>>Ada</option>
							<option value="2" <?php echo ($this->uri->segment(5) == '2') ? 'selected' : '' ?>>Belum</option>
						</select>
					</div>
					<div class="col-auto">
						<label class="form-label">Status PPP:</label>
						<select class="form-select dropdown-ppp-<?php echo time() ?> select2-picker">
							<option value="">Pilih Status PPP</option>
							<option value="1" <?php echo ($this->uri->segment(6) == '1') ? 'selected' : '' ?>>Ada</option>
							<option value="2" <?php echo ($this->uri->segment(6) == '2') ? 'selected' : '' ?>>Belum</option>
						</select>
					</div>
					<div class="col-auto">
						<label class="form-label">Status PJS:</label>
						<select class="form-select dropdown-pjs-<?php echo time() ?> select2-picker">
							<option value="">Pilih Status PJS</option>
							<option value="1" <?php echo ($this->uri->segment(7) == '1') ? 'selected' : '' ?>>Ada</option>
							<option value="2" <?php echo ($this->uri->segment(7) == '2') ? 'selected' : '' ?>>Belum</option>
						</select>
					</div>
					<div class="col-auto">
						<label class="form-label">Status PHS:</label>
						<select class="form-select dropdown-phs-<?php echo time() ?> select2-picker">
							<option value="">Pilih Status PHS</option>
							<option value="1" <?php echo ($this->uri->segment(8) == '1') ? 'selected' : '' ?>>Ada</option>
							<option value="2" <?php echo ($this->uri->segment(8) == '2') ? 'selected' : '' ?>>Belum</option>
						</select>
					</div>
					<div class="col-auto">
						<label class="form-label">Status BAS:</label>
						<select class="form-select dropdown-bas-<?php echo time() ?> select2-picker">
							<option value="">Pilih Status BAS</option>
							<option value="1" <?php echo ($this->uri->segment(9) == '1') ? 'selected' : '' ?>>Ada</option>
							<option value="2" <?php echo ($this->uri->segment(9) == '2') ? 'selected' : '' ?>>Belum</option>
						</select>
					</div>
				</form>
			</div>
		</div>

		<div class="row mt-3 summary-edoc-cards flex-nowrap overflow-auto pb-2">
			<div class="col mb-2" style="min-width: 160px;">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body pb-3">
						<div class="d-flex justify-content-between align-items-center mb-1">
							<span class="fw-bold text-muted">PMH</span>
							<span class="summary-pmh-count fw-bold">0 / 0</span>
						</div>
						<div class="progress" style="height: 8px;">
							<div class="progress-bar bg-success summary-pmh-bar" role="progressbar" style="width: 0%;"></div>
						</div>
						<small class="text-muted summary-pmh-pct">0%</small>
					</div>
				</div>
			</div>
			<div class="col mb-2" style="min-width: 160px;">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body pb-3">
						<div class="d-flex justify-content-between align-items-center mb-1">
							<span class="fw-bold text-muted">PPP</span>
							<span class="summary-ppp-count fw-bold">0 / 0</span>
						</div>
						<div class="progress" style="height: 8px;">
							<div class="progress-bar bg-success summary-ppp-bar" role="progressbar" style="width: 0%;"></div>
						</div>
						<small class="text-muted summary-ppp-pct">0%</small>
					</div>
				</div>
			</div>
			<div class="col mb-2" style="min-width: 160px;">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body pb-3">
						<div class="d-flex justify-content-between align-items-center mb-1">
							<span class="fw-bold text-muted">PJS</span>
							<span class="summary-pjs-count fw-bold">0 / 0</span>
						</div>
						<div class="progress" style="height: 8px;">
							<div class="progress-bar bg-success summary-pjs-bar" role="progressbar" style="width: 0%;"></div>
						</div>
						<small class="text-muted summary-pjs-pct">0%</small>
					</div>
				</div>
			</div>
			<div class="col mb-2" style="min-width: 160px;">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body pb-3">
						<div class="d-flex justify-content-between align-items-center mb-1">
							<span class="fw-bold text-muted">PHS</span>
							<span class="summary-phs-count fw-bold">0 / 0</span>
						</div>
						<div class="progress" style="height: 8px;">
							<div class="progress-bar bg-success summary-phs-bar" role="progressbar" style="width: 0%;"></div>
						</div>
						<small class="text-muted summary-phs-pct">0%</small>
					</div>
				</div>
			</div>
			<div class="col mb-2" style="min-width: 160px;">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body pb-3">
						<div class="d-flex justify-content-between align-items-center mb-1">
							<span class="fw-bold text-muted">BAS</span>
							<span class="summary-bas-count fw-bold">0 / 0</span>
						</div>
						<div class="progress" style="height: 8px;">
							<div class="progress-bar bg-success summary-bas-bar" role="progressbar" style="width: 0%;"></div>
						</div>
						<small class="text-muted summary-bas-pct">0%</small>
					</div>
				</div>
			</div>
			<div class="col mb-2" style="min-width: 160px;">
				<div class="card border-0 shadow-sm h-100">
					<div class="card-body pb-3">
						<div class="d-flex justify-content-between align-items-center mb-1">
							<span class="fw-bold text-muted">Relaas</span>
							<span class="summary-relaas-count fw-bold">0 / 0</span>
						</div>
						<div class="progress" style="height: 8px;">
							<div class="progress-bar bg-success summary-relaas-bar" role="progressbar" style="width: 0%;"></div>
						</div>
						<small class="text-muted summary-relaas-pct">0%</small>
					</div>
				</div>
			</div>
		</div>

		<div class="table-responsive">
			<table id="table-edoc-<?php echo $this->uri->segment(4) ?>-<?php echo $this->uri->segment(5) ?>-<?php echo $this->uri->segment(6) ?>-<?php echo $this->uri->segment(7) ?>-<?php echo $this->uri->segment(8) ?>-<?php echo $this->uri->segment(9) ?>" class="display"></table>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function() {
		const getMonth = '<?php echo $this->uri->segment(4) ?: null ?>';
		const getStatusPmh = '<?php echo $this->uri->segment(5) ?: null ?>';
		const getStatusPpp = '<?php echo $this->uri->segment(6) ?: null ?>';
		const getStatusPjs = '<?php echo $this->uri->segment(7) ?: null ?>';
		const getStatusPhs = '<?php echo $this->uri->segment(8) ?: null ?>';
		const getStatusBas = '<?php echo $this->uri->segment(9) ?: null ?>';
		const theTime = '<?php echo time() ?>';

		function setBar(prefix, count, total, percentage) {
			const formattedCount = Number(count).toLocaleString('id-ID');
			const formattedTotal = Number(total).toLocaleString('id-ID');
			$('.summary-' + prefix + '-count').text(formattedCount + ' / ' + formattedTotal);
			$('.summary-' + prefix + '-pct').text(percentage + '%');

			const bar = $('.summary-' + prefix + '-bar');
			bar.css('width', percentage + '%');
			bar.removeClass('bg-success bg-warning bg-danger');
			if (percentage >= 75) {
				bar.addClass('bg-success');
			} else if (percentage >= 40) {
				bar.addClass('bg-warning');
			} else {
				bar.addClass('bg-danger');
			}
		}

		function loadSummary() {
			const data = {
				selectedRangePutus: $('.daterange_dokpen_putus').val(),
			};
			data[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');

			$.post("<?php echo base_url("monitoring/edoc/get_summary") ?>", data, function(res) {
				// Each category now carries its own denominator (`total`) from the
				// backend: PMH/PPP/PJS/PHS are out of total_perkara, but BAS is out
				// of total sidang and Relaas is out of total pihak-per-sidang, since
				// a single perkara can have several of each.
				setBar('pmh', res.pmh.count, res.pmh.total, res.pmh.percentage);
				setBar('ppp', res.ppp.count, res.ppp.total, res.ppp.percentage);
				setBar('pjs', res.pjs.count, res.pjs.total, res.pjs.percentage);
				setBar('phs', res.phs.count, res.phs.total, res.phs.percentage);
				setBar('bas', res.bas.count, res.bas.total, res.bas.percentage);
				setBar('relaas', res.relaas.count, res.relaas.total, res.relaas.percentage);
			}, 'json');
		}

		loadSummary();

		initDataTable("#table-edoc-<?php echo $this->uri->segment(4) ?>-<?php echo $this->uri->segment(5) ?>-<?php echo $this->uri->segment(6) ?>-<?php echo $this->uri->segment(7) ?>-<?php echo $this->uri->segment(8) ?>-<?php echo $this->uri->segment(9) ?>", {
			title: "<?php echo $title ?>",
			ajax: {
				url: "<?php echo base_url("monitoring/edoc/get_list") ?>",
				data: function(d) {
					d['selectedRangePutus'] = $('.daterange_dokpen_putus').val();
					d['selectedPmh'] = $('.dropdown-pmh-<?php echo time() ?>').val();
					d['selectedPpp'] = $('.dropdown-ppp-<?php echo time() ?>').val();
					d['selectedPjs'] = $('.dropdown-pjs-<?php echo time() ?>').val();
					d['selectedPhs'] = $('.dropdown-phs-<?php echo time() ?>').val();
					d['selectedBas'] = $('.dropdown-bas-<?php echo time() ?>').val();
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
				}
			},
			columns: [{
					data: null,
					title: "No.",
					className: "dt-center",
					render: function(data, type, row, meta) {
						return meta.row + meta.settings._iDisplayStart + 1;
					}
				},
				{
					data: "nomor_perkara",
					title: "Nomor Perkara",
					className: "dt-center text-nowrap",
					render: function(data, type, row) {
						if (type === 'export') {
							return data;
						}
						let result = `<strong>${data}</strong>`;
						if (row.jenis_perkara_nama || row.efiling_id || row.ghaib == 1) {
							result += '<br/>';
							if (row.jenis_perkara_nama) {
								result += `<span class="badge badge-info me-1">${row.jenis_perkara_nama}</span>`;
							}
							if (row.efiling_id) {
								result += '<span class="badge badge-success me-1">e-Court</span>';
							}
							if (row.ghaib == 1) {
								result += '<span class="badge badge-warning me-1">Ghaib</span>';
							}
						}
						return result;
					}
				},
				{
					data: "hakim_nama",
					title: "KM/PP",
					className: "text-nowrap",
					render: function(data, type, row) {
						if (type === 'export') {
							return data;
						}
						return data + '<br>' + (row.panitera_nama || '');
					},
				},
				{
					data: "jurusita_nama",
					title: "Jurusita",
					className: "text-nowrap",
					render: function(data, type, row) {
						if (type === 'export') {
							return data;
						}
						return data || '';
					},
				},
				{
					data: "next_sidang_putus_date",
					title: "Tanggal Rencana Putus",
					className: "dt-center text-nowrap",
					render: function(data, type, row) {
						if (type === 'export') {
							return data || row.prev_sidang_putus_date || '';
						}
						if (data) {
							const isToday = moment(data).isSame(moment(), 'day');
							const formatted = moment(data).format('Do MMMM YYYY');
							return isToday ?
								`<span class="badge badge-warning">Hari Ini</span><br>${formatted}` :
								formatted;
						}
						if (row.prev_sidang_putus_date) {
							const formattedPrev = moment(row.prev_sidang_putus_date).format('Do MMMM YYYY');
							const decidedOnSchedule = row.tanggal_putus &&
								moment(row.prev_sidang_putus_date).isSame(moment(row.tanggal_putus), 'day');
							if (decidedOnSchedule) {
								return `<span class="text-success">${formattedPrev}</span>`;
							}
							return `<span class="text-danger">${formattedPrev}<br><small>(Lewat Jadwal)</small></span>`;
						}
						return '<span class="text-muted">-</span>';
					},
				},
				{
					data: "tanggal_putus",
					title: "Tanggal Putus",
					className: "dt-center",
					render: function(data, type, row) {
						return data ? moment(data).format('Do MMMM YYYY') : '';
					},
				},
				{
					data: "pmh",
					title: "PMH",
					className: "dt-center text-nowrap",
					render: function(data, type, row) {
						if (data) {
							if (!row.sipp_url) {
								return '<span class="badge badge-success">Ada</span>';
							}
							return '<a target="_blank" href="' + data + '" class="btn btn-sm btn-outline-success"><i class="fa fa-file-pdf"></i></a>';
						}
						if (!row.sipp_url) {
							return '<span class="badge badge-danger">Belum</span>';
						}
						return '<a target="_blank" href="' + row.sipp_url + '" class="btn btn-sm btn-outline-danger"><i class="fa fa-upload"></i> Unggah</a>';
					}
				},
				{
					data: "ppp",
					title: "PPP",
					className: "dt-center text-nowrap",
					render: function(data, type, row) {
						if (data) {
							if (!row.sipp_url) {
								return '<span class="badge badge-success">Ada</span>';
							}
							return '<a target="_blank" href="' + data + '" class="btn btn-sm btn-outline-success"><i class="fa fa-file-pdf"></i></a>';
						}
						if (!row.sipp_url) {
							return '<span class="badge badge-danger">Belum</span>';
						}
						return '<a target="_blank" href="' + row.sipp_url + '" class="btn btn-sm btn-outline-danger"><i class="fa fa-upload"></i> Unggah</a>';
					}
				},
				{
					data: "pjs",
					title: "PJS",
					className: "dt-center text-nowrap",
					render: function(data, type, row) {
						if (data) {
							if (!row.sipp_url) {
								return '<span class="badge badge-success">Ada</span>';
							}
							return '<a target="_blank" href="' + data + '" class="btn btn-sm btn-outline-success"><i class="fa fa-file-pdf"></i></a>';
						}
						if (!row.sipp_url) {
							return '<span class="badge badge-danger">Belum</span>';
						}
						return '<a target="_blank" href="' + row.sipp_url + '" class="btn btn-sm btn-outline-danger"><i class="fa fa-upload"></i> Unggah</a>';
					}
				},
				{
					data: "phs",
					title: "PHS",
					className: "dt-center text-nowrap",
					render: function(data, type, row) {
						if (data) {
							if (!row.sipp_url) {
								return '<span class="badge badge-success">Ada</span>';
							}
							return '<a target="_blank" href="' + data + '" class="btn btn-sm btn-outline-success"><i class="fa fa-file-pdf"></i></a>';
						}
						if (!row.sipp_url) {
							return '<span class="badge badge-danger">Belum</span>';
						}
						return '<a target="_blank" href="' + row.sipp_url + '" class="btn btn-sm btn-outline-danger"><i class="fa fa-upload"></i> Unggah</a>';
					}
				},
				{
					data: "bas_percentage",
					title: "BAS",
					className: "dt-center text-nowrap",
					render: function(data, type, row) {
						if (type === 'export') {
							return data !== null ? data + '%' : 'N/A';
						}
						var basUrl = "<?php echo base_url('kinerja/bas/bas_list') ?>?perkara_id=" + row.row_id;
						if (data === null || data === 0) {
							return '<a href="' + basUrl + '" class="btn btn-sm btn-outline-danger btn-modal"><i class="fa fa-list"></i> Belum</a>';
						}
						let color = data >= 75 ? 'success' : data >= 40 ? 'warning' : 'danger';
						return '<a href="' + basUrl + '" class="btn btn-sm btn-outline-' + color + ' btn-modal">' + data + '%</a>';
					}
				},
				{
					data: "relaas_percentage",
					title: "Relaas",
					className: "dt-center text-nowrap",
					render: function(data, type, row) {
						if (type === 'export') {
							return data !== null ? data + '%' : 'N/A';
						}
						var relaasUrl = "<?php echo base_url('kinerja/relaas/index') ?>?perkara_id=" + row.row_id;
						if (data === null || data === 0) {
							return '<a href="' + relaasUrl + '" class="btn btn-sm btn-outline-danger btn-modal"><i class="fa fa-list"></i> Belum</a>';
						}
						let color = data >= 75 ? 'success' : data >= 40 ? 'warning' : 'danger';
						return '<a href="' + relaasUrl + '" class="btn btn-sm btn-outline-' + color + ' btn-modal">' + data + '%</a>';
					}
				},
			],
		});
	});
</script>