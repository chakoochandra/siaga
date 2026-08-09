<div class="card leaves">
	<div class="card-header leaves align-items-center">
		<h5 id="sidang-title" class="m-0"><?php echo $title ?></h5>
	</div>
	<div class="card-body">
		<?php
		$tableId = 'table-sidang-' . time();
		$theTime = time();
		$defaultDate = date('Y-m-d');
		?>
		<div class="table-responsive">
			<table id="<?php echo $tableId ?>" class="display table-striped table-hover"></table>
		</div>
	</div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		moment.locale('id');
		const theTime = '<?php echo $theTime ?>';
		const hasSipandu = <?php echo isset($has_sipandu) && $has_sipandu ? 'true' : 'false'; ?>;

		function escapeHtml(value) {
			return $('<div>').text(value || '').html();
		}

		function formatDate(value) {
			if (!value) {
				return '-';
			}
			return moment(value).locale('id').format('dddd, D MMM YYYY');
		}

		function formatTime(value) {
			if (!value) {
				return '';
			}
			return moment(value, ['HH:mm:ss', 'HH:mm']).format('HH:mm');
		}

		function phoneCell(value) {
			const phones = String(value || '').split(',').map(function (item) {
				return item.trim();
			}).filter(Boolean);

			if (!phones.length) {
				return '<span class="badge bg-danger">Tidak tersedia</span>';
			}

			return phones.map(function (phone) {
				return '<span class="text-nowrap">' + escapeHtml(phone) + '</span>';
			}).join('<br>');
		}

		function formatParties(value) {
			const text = String(value || '').replace(/<br\s*\/?>/gi, '\n');
			return text.split('\n').map(function (line) {
				return escapeHtml(line);
			}).join('<br>');
		}

		const columns = [{
			data: null,
			title: "No.",
			className: "dt-center",
			render: function (data, type, row, meta) {
				return meta.row + meta.settings._iDisplayStart + 1;
			}
		}, {
			data: "tanggal_sidang",
			title: "Tanggal Sidang",
			className: "text-nowrap",
			render: function (data, type) {
				if (type === 'export') {
					return data || '';
				}
				return formatDate(data);
			}
		}, {
			data: "nama_ruang",
			title: "Ruang",
			className: "text-nowrap",
			render: function (data, type) {
				return type === 'export' ? data || '' : escapeHtml(data);
			}
		}];

		if (hasSipandu) {
			columns.push({
				data: "nomor_panggil",
				title: "Nomor Panggil",
				className: "text-nowrap",
				render: function (data, type) {
					return type === 'export' ? (data || '') : escapeHtml(data);
				}
			});
			columns.push({
				data: "perkiraan_jam",
				title: "Perkiraan Jam",
				className: "text-nowrap",
				render: function (data, type) {
					return type === 'export' ? (data || '') : formatTime(data);
				}
			});
		}

		columns.push({
			data: null,
			title: "Info Perkara",
			render: function (data, type, row) {
				if (type === 'export') {
					return [row.nomor_perkara, row.jenis_perkara_nama, row.agenda].filter(Boolean).join(' | ');
				}

				let result = '<strong>' + escapeHtml(row.nomor_perkara) + '</strong>';
				if (row.jenis_perkara_nama) {
					result += '<br><span class="badge badge-info me-1">' + escapeHtml(row.jenis_perkara_nama) + '</span>';
				}
				if (row.agenda) {
					result += '<br>' + escapeHtml(row.agenda);
				}
				return result;
			}
		}, {
			data: null,
			title: "Hakim dan PP",
			className: "text-nowrap",
			render: function (data, type, row) {
				if (type === 'export') {
					let parts = [];
					if (row.nama_hakim) parts.push(row.nama_hakim);
					if (row.nama_pp) parts.push('PP: ' + row.nama_pp);
					return parts.join(' | ') || '-';
				}
				let html = '';
				if (row.nama_hakim) {
					html += '<div><small><strong>Hakim:</strong> ' + escapeHtml(row.nama_hakim) + '</small></div>';
				}
				if (row.nama_pp) {
					html += '<div><small><strong>PP:</strong> ' + escapeHtml(row.nama_pp) + '</small></div>';
				}
				return html || '<span class="badge bg-secondary">Tidak tersedia</span>';
			}
		}, {
			data: null,
			title: "Pihak",
			render: function (data, type, row) {
				if (type === 'export') {
					return String(row.para_pihak || '').replace(/<br\s*\/?>/gi, '\n');
				}
				const parties = formatParties(row.para_pihak);
				return parties || '<span class="badge bg-secondary">Tidak tersedia</span>';
			}
		}, {
			data: "telepon_P",
			title: "No Telp P",
			className: "text-nowrap",
			render: function (data, type) {
				return type === 'export' ? data || '' : phoneCell(data);
			}
		}, {
			data: "telepon_T",
			title: "No Telp T",
			className: "text-nowrap",
			render: function (data, type) {
				return type === 'export' ? data || '' : phoneCell(data);
			}
		});

		updateHeaderSubtitle('<?php echo $defaultDate ?>');

		initDataTable('#<?php echo $tableId ?>', {
			title: "<?php echo $title ?>",
			scrollX: true,
			ajax: {
				url: "<?php echo base_url('ck/sidang/get_list') ?>",
				data: function (d) {
					d.selectedDateSidang = $(`.table-sidang-date-filter-${theTime} input[type="text"]`).val() || '<?php echo $defaultDate ?>';
					d.selectedRuang = $(`.dropdown-ruang-${theTime} select`).val();
					d[localStorage.getItem('csrfName')] = localStorage.getItem('csrfToken');
					updateHeaderSubtitle(d.selectedDateSidang);
				}
			},
			layout: {
				topStart: {
					buttons: [{
						extend: 'datepicker',
						config: {
							id: 'table-sidang-date-filter-' + theTime,
							format: 'yyyy-mm-dd',
							placeholder: 'Tanggal Sidang',
							value: '<?php echo $defaultDate ?>',
							cardTitleSelector: '#sidang-title'
						},
					}, {
						extend: 'dropdown',
						config: {
							id: 'dropdown-ruang-' + theTime,
							placeholder: 'Ruang Sidang',
							options: <?php echo json_encode($all_ruang_sidang) ?>,
						},
					}]
				},
			},
			columns: columns
		});
	});
</script>