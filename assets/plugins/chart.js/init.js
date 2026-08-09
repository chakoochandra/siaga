// 1. GLOBAL CONFIG (only once at startup)
function setChartGlobalConfig(options = {}) {
	Chart.defaults.font.family = options.fontFamily || "'Helvetica Neue',Helvetica,Arial,sans-serif";
	Chart.defaults.font.size = options.fontSize || 12;
	Chart.defaults.color = options.fontColor || '#666';
	Chart.defaults.plugins.tooltip.enabled = options.enableTooltip !== false;
	Chart.defaults.responsive = options.responsive !== false;
	Chart.defaults.maintainAspectRatio = options.maintainAspectRatio !== false;
}

// 2. DEFAULT OPTIONS (handles bar/line vs others & centralizes DataLabels)
function applyDefaultOptions(cfg, axisTitles) {
	const isBarOrLine = ['bar', 'line'].includes(cfg.type);

	const defaultOptions = {
		responsive: true,
		maintainAspectRatio: false,
		plugins: {
			title: {
				display: !!cfg.title,
				text: cfg.title ?? '',
				font: { size: 18, weight: 'bold', family: 'Arial' },
				color: getCSSVar('--bs-green'),
				padding: { top: 0, bottom: 0 },
				align: 'center'
			},
			// Use more responsive tooltip defaults; overridden by user options
			tooltip: {
				mode: 'nearest',
				intersect: true
			},
			legend: {
				display: true,
				labels: {
					color: getCSSVar('--bs-green'),
					generateLabels: function (chart) {
						return Chart.defaults.plugins.legend.labels.generateLabels(chart)
							.filter(label => label.text);
					}
				}
			},
			datalabels: {
				color: '#fff',
				font: { weight: 'bold', size: 12 }
			}
		},
		// Make interaction responsive by default; overridden by user options
		interaction: {
			mode: 'nearest',
			intersect: true
		},
		elements: {
			point: {
				radius: 4,
				hoverRadius: 6
			},
			line: {
				borderWidth: 2
			}
		},
		scales: isBarOrLine ? {
			x: {
				stacked: cfg.type === 'bar',
				title: {
					display: !!axisTitles?.x,
					text: axisTitles?.x || ''
				}
			},
			y: {
				stacked: cfg.type === 'bar',
				beginAtZero: true,
				title: {
					display: !!axisTitles?.y,
					text: axisTitles?.y || ''
				}
			}
		} : undefined
	};

	cfg.type = cfg.type || 'bar';
	cfg.options = deepMerge({}, defaultOptions, cfg.options || {});

	if (!isBarOrLine) {
		delete cfg.options.indexAxis;
		delete cfg.options.scales;
	} else {
		cfg.options.indexAxis = cfg.options.indexAxis || 'x';
		setChartAxisOrientation({ options: cfg.options }, cfg.options.indexAxis, axisTitles);

		// Align interaction axis for bar charts to the index axis if not provided
		if (!cfg.options.interaction) cfg.options.interaction = {};
		if (!cfg.options.interaction.axis) {
			cfg.options.interaction.axis = cfg.options.indexAxis;
		}
	}

	// ✅ Apply default dataset styling if not provided
	if (Array.isArray(cfg.data?.datasets)) {
		cfg.data.datasets.forEach(ds => {
			if (cfg.type === 'line') {
				if (ds.fill === undefined) ds.fill = false;
				if (ds.tension === undefined) ds.tension = 0.3;
				if (ds.borderWidth === undefined) ds.borderWidth = 2;
			}
			// Add other type-specific defaults here if needed
		});
	}
}

function deepMerge(target, ...sources) {
	for (const source of sources) {
		if (typeof source !== 'object' || source === null) continue;

		for (const key in source) {
			const value = source[key];
			if (Array.isArray(value)) {
				target[key] = value.slice();
			} else if (typeof value === 'object' && value !== null) {
				if (!target[key] || typeof target[key] !== 'object') {
					target[key] = {};
				}
				deepMerge(target[key], value);
			} else {
				target[key] = value;
			}
		}
	}
	return target;
}

// 3. BACKGROUND PLUGIN (only once)
function applyPlugin(cfg) {
	cfg.plugins = [
		...(cfg.plugins || []),
		{
			id: 'canvas',
			beforeDraw(chart, _, opts) {
				const { ctx, width, height, chartArea } = chart;

				// Apply canvas background
				const bgColor = opts?.backgroundColor;
				if (bgColor && bgColor !== 'transparent') {
					ctx.save();
					ctx.globalCompositeOperation = 'destination-over';
					ctx.fillStyle = bgColor;
					ctx.fillRect(0, 0, width, height);
					ctx.restore();
				}

				// Apply chart area border
				const borderColor = opts?.borderColor;
				const borderWidth = opts?.borderWidth;
				if (borderColor && borderWidth) {
					ctx.save();
					ctx.strokeStyle = borderColor;
					ctx.lineWidth = borderWidth;
					ctx.strokeRect(
						chartArea.left,
						chartArea.top,
						chartArea.right - chartArea.left,
						chartArea.bottom - chartArea.top
					);
					ctx.restore();
				}
			}
		}
	];
}

// 4. TOOLTIP (only once)
function applyTooltipCallback(cfg) {
	// cfg.options.plugins.tooltip = {
	//     callbacks: {
	//         label(ctx) {
	//             const v = ctx.formattedValue || ctx.raw;
	//             if (v <= 0) return null;
	//             return `${ctx.label || ''}: ${v}`;
	//         }
	//     }
	// };
}

// 5. BUILD CHART (canvas, config prep, instantiation, controls)
function buildChart(container, cfg, axisTitles) {
	// Clear existing contents
	container.innerHTML = '';

	// Create canvas
	const canvas = document.createElement('canvas');
	canvas.style.width = '100%';
	canvas.style.height = '100%';
	container.appendChild(canvas);

	const ctx = canvas.getContext('2d');

	// Apply chart configurations
	applyDatasetColors(cfg);
	applyDefaultOptions(cfg, axisTitles);
	applyPlugin(cfg);
	applyTooltipCallback(cfg);

	// Instantiate chart
	const chart = new Chart(ctx, cfg);

	// Optional helpers
	handleThemeChange(chart);
	applyDatalabelsRotation(chart);
	applyDatalabelText(chart);

	// Ensure container is wrapped in .leaves before attaching controls
	const parent = container.parentElement;
	if (!parent.classList.contains('leaves')) {
		const mainContainer = document.createElement('div');
		mainContainer.classList.add('leaves', 'w-100', 'px-4', 'my-2');
		mainContainer.style.border = '2px solid ' + getCSSVar('--bs-body-color-disabled');
		// mainContainer.style.borderRadius = '8px';

		parent.replaceChild(mainContainer, container);
		mainContainer.appendChild(container);
	}

	// Attach custom controls, now that .leaves exists
	attachChartControls(chart, container, canvas, cfg, axisTitles);

	return chart;
}

// 6. INIT and DROPDOWN simply delegate to buildChart
function initChart(id, cfg, opts = {}) {
	// Guard: Validate element ID parameter
	if (!id || typeof id !== 'string') {
		console.warn('initChart: Invalid element ID provided. Chart not initialized.');
		return null;
	}

	// Guard: register DataLabels plugin only if present (lazy-load-safe)
	if (typeof window.ChartDataLabels !== 'undefined') {
		Chart.register(window.ChartDataLabels);
	}
	setChartGlobalConfig(opts);

	const container = document.getElementById(id);

	// Guard: Check if element exists
	if (!container) {
		console.warn(`initChart: Element with id "${id}" not found. Chart not initialized.`);
		return null;
	}

	container.classList.add('d-flex', 'flex-column', 'justify-content-center', 'align-items-center', 'mt-3');

	// 1️⃣ Get the predefined target height from element's inline style
	const persisted = container.getAttribute('data-init-height');
	const targetHeight = persisted || container.style.height || '400px';
	if (!persisted) {
		container.setAttribute('data-init-height', targetHeight);
	}
	// console.log('Target height:', targetHeight);

	// 2️⃣ Temporarily let it fit content
	if (!persisted) {
		container.style.height = 'auto';
	} else {
		container.style.height = targetHeight;
	}

	// 3️⃣ Build the chart
	const axes = cfg.options?.axisTitles || { x: 'Kategori', y: 'Nilai' };
	const chart = buildChart(container, cfg, axes);

	// 4️⃣ After chart renders, smoothly stretch to predefined height
	requestAnimationFrame(() => {
		container.style.transition = 'height 0.6s ease';
		container.style.height = targetHeight;
	});

	return chart;
}

function loadChartOverlay(id, style = {}) {
	let container = document.getElementById(id);
	if (!container.querySelector('.overlay')) {
		const overlay = document.createElement('div');
		overlay.className = 'overlay d-flex justify-content-center align-items-center mt-5';
		overlay.innerHTML = '<i class="fa fa-spinner fa-pulse fa-3x fa-fw margin-bottom" aria-hidden="true"></i>';

		// Apply custom styles if provided
		Object.assign(overlay.style, style);

		container.appendChild(overlay);
	}
}

function createChartTypeDropdown(chart, baseConfig, _ctx, axisTitles, _wrapper) {
	const types = baseConfig.customChartTypes === true ? ['bar', 'line', 'pie', 'doughnut'] : (baseConfig.customChartTypes || []);
	const wrapper = document.createElement('div'); wrapper.className = 'btn-group';

	const btn = document.createElement('button');
	btn.className = 'btn btn-sm btn-outline-info dropdown-toggle';
	btn.setAttribute('data-bs-toggle', 'dropdown');
	btn.innerHTML = '<i class="fa fa-bar-chart"></i>';
	const menu = document.createElement('ul'); menu.className = 'dropdown-menu';

	types.filter(t => t !== chart.config.type).forEach(type => {
		const li = document.createElement('li'),
			a = document.createElement('a');
		a.className = 'dropdown-item'; a.href = '#'; a.textContent = type[0].toUpperCase() + type.slice(1);
		a.onclick = e => {
			e.preventDefault();
			const container = chart.canvas.parentElement;
			chart.destroy();
			const cfg = JSON.parse(JSON.stringify(baseConfig));
			cfg.type = type;
			container.innerHTML = '';  // buildChart will re-append
			buildChart(container, cfg, axisTitles);
		};
		li.appendChild(a); menu.appendChild(li);
	});

	wrapper.appendChild(btn);
	wrapper.appendChild(menu);
	return wrapper;
}

function attachChartControls(chart, container, canvas, config, axisTitles) {
	// Create button wrapper
	const wrapper = createButtonWrapper();

	// ✅ Only show dropdown if explicitly enabled
	if (config.customChartTypes) {
		const dropdown = createChartTypeDropdown(chart, config, null, axisTitles, wrapper);
		wrapper.appendChild(dropdown);
	}

	// Toggle-axis (bar only) - default true if not specified
	if (chart.config.type === 'bar' && config.showToggleAxis !== false) {
		wrapper.appendChild(createToggleAxisButton(chart, axisTitles));
	}

	// Download
	const title = config.title || '';
	wrapper.appendChild(createDownloadButton(canvas, chart, title));

	// Append controls outside the chart container to avoid affecting height
	const leaves = container.closest('.leaves') || container.parentElement || container;
	const existing = leaves.querySelector('.chart-controls');
	if (existing) existing.remove();
	leaves.appendChild(wrapper);
}

function createToggleAxisButton(chart, axisTitles) {
	const button = document.createElement('button');
	button.innerHTML = '<i class="fa fa-refresh"></i>';
	button.className = 'btn btn-sm btn-outline-secondary';

	button.onclick = function () {
		const currentAxis = chart.options.indexAxis || 'x';
		const newAxis = currentAxis === 'x' ? 'y' : 'x';
		chart.options.indexAxis = newAxis;
		setChartAxisOrientation(chart, newAxis, axisTitles);

		applyDatalabelsRotation(chart);
		applyDatalabelText(chart);

		chart.update();
	};

	return button;
}

function setChartAxisOrientation(chart, axis, axisTitles) {
	const isXAxisCategory = axis === 'x';
	chart.options.indexAxis = axis;
	chart.options.scales = {
		x: {
			type: isXAxisCategory ? 'category' : 'linear',
			title: { display: true, text: isXAxisCategory ? axisTitles.x : axisTitles.y },
			ticks: { autoSkip: false },
			grid: { display: false }
		},
		y: {
			type: isXAxisCategory ? 'linear' : 'category',
			title: { display: true, text: isXAxisCategory ? axisTitles.y : axisTitles.x },
			ticks: { autoSkip: false },
			grid: { display: false }
		}
	};
}

function applyDatasetColors(chartOrConfig) {
	const chartType = chartOrConfig.type || chartOrConfig.config?.type;
	const datasets = chartOrConfig.data?.datasets || [];

	const isPieOrDoughnut = ['pie', 'doughnut'].includes(chartType);
	const isLine = chartType === 'line';

	datasets.forEach((dataset, index) => {
		if (isPieOrDoughnut) {
			const dataLength = dataset.data?.length || 0;
			// Respect existing colors if provided; only generate when missing or incomplete
			const existing = dataset.backgroundColor;
			if (!Array.isArray(existing) || existing.length !== dataLength) {
				dataset.backgroundColor = Array.from({ length: dataLength }, () => getRandomColor());
			}
		} else {
			const color = getRandomColor();
			// Only set a default color if none provided
			if (dataset.backgroundColor == null ||
				(Array.isArray(dataset.backgroundColor) && dataset.backgroundColor.length === 0) ||
				(typeof dataset.backgroundColor === 'string' && dataset.backgroundColor.trim() === '')) {
				dataset.backgroundColor = color;
			}

			if (isLine) {
				// Respect existing borderColor; set sensible defaults otherwise
				if (dataset.borderColor == null || (typeof dataset.borderColor === 'string' && dataset.borderColor.trim() === '')) {
					dataset.borderColor = Array.isArray(dataset.backgroundColor)
						? dataset.backgroundColor[0]
						: dataset.backgroundColor || color;
				}
				if (dataset.fill === undefined) dataset.fill = false;
				if (dataset.tension === undefined) dataset.tension = 0.4;
				if (dataset.pointRadius === undefined) dataset.pointRadius = 4;
				if (dataset.borderWidth === undefined) dataset.borderWidth = 2;
			}
		}
	});
}

function handleThemeChange(chart) {
	const listener = () => {
		if (!chart || !chart.canvas || !chart.ctx) return;

		// Update chart theme styles
		chart.options.plugins.title.color = getCSSVar('--bs-green');
		if (chart.options.plugins.legend?.labels) {
			chart.options.plugins.legend.labels.color = getCSSVar('--bs-green');
		}

		// Reapply dataset colors
		applyDatasetColors(chart);

		// Ensure the outer '.leaves' container has updated border color
		const container = chart.canvas.closest('.leaves');
		if (container) {
			container.style.border = '2px solid ' + getCSSVar('--bs-body-color-disabled');
		}

		chart.update();
	};

	$(document).on('theme-change', listener);

	return () => {
		$(document).off('theme-change', listener);
	};
}

function createButtonWrapper() {
	const wrapper = document.createElement('div');
	wrapper.className = 'chart-controls d-flex justify-content-center gap-2 my-2';
	return wrapper;
}

// Utility: lazy-load a script from URL
function loadScript(url) {
	return new Promise(function (resolve, reject) {
		try {
			var s = document.createElement('script');
			s.src = url;
			s.async = true;
			s.onload = resolve;
			s.onerror = reject;
			document.head.appendChild(s);
		} catch (e) {
			reject(e);
		}
	});
}

// function createDownloadButton(canvas, chart, title = 'chart') {
//     const button = document.createElement('button');
//     button.className = 'btn btn-sm btn-outline-success';
//     button.innerHTML = '<i class="fa fa-download"></i>';

//     button.onclick = function () {
//         const ctx = canvas.getContext('2d');
//         const { width, height } = canvas;

//         let original;
//         if (chart.config.type === 'bar') {
//             // Save the current canvas content
//             original = ctx.getImageData(0, 0, width, height);

//             // Draw white background below chart
//             ctx.save();
//             ctx.globalCompositeOperation = 'destination-over';
//             ctx.fillStyle = '#000000';
//             ctx.fillRect(0, 0, width, height);
//             ctx.restore();
//         }

//         // Export image
//         const link = document.createElement('a');
//         link.href = canvas.toDataURL('image/png');
//         link.download = `${title}.png`;
//         link.click();

//         // Restore original canvas (if modified)
//         if (original) {
//             ctx.putImageData(original, 0, 0);
//         }
//     };

//     return button;
// }
function createDownloadButton(canvas, chart, title = 'chart') {
	const button = document.createElement('button');
	button.className = 'btn btn-sm btn-outline-success';
	button.innerHTML = '<i class="fa fa-download"></i>';

	button.onclick = async function () {
		try {
			// Lazy-load html2canvas only on demand
			if (typeof window.html2canvas === 'undefined') {
				var prefix = (typeof window.ASSET_URL_PREFIX === 'string') ? window.ASSET_URL_PREFIX : '';
				await loadScript(prefix + 'assets/plugins/html2canvas/html2canvas.min.js');
			}

			// Create a temporary wrapper div styled like `.leaves`
			const wrapper = document.createElement('div');
			wrapper.className = 'leaves my-3 px-4';
			wrapper.style.border = '2px solid #32363b8a';
			wrapper.style.background = 'transparent';
			wrapper.style.padding = '1rem';
			wrapper.style.display = 'inline-block'; // allow wrapper to fit content
			wrapper.style.width = 'fit-content';

			// Clone and insert canvas
			const clonedCanvas = canvas.cloneNode(true);
			const ctx = clonedCanvas.getContext('2d');
			ctx.drawImage(canvas, 0, 0);
			wrapper.appendChild(clonedCanvas);

			// Off-screen positioning
			wrapper.style.position = 'fixed';
			wrapper.style.top = '-10000px';

			document.body.appendChild(wrapper);

			// Wait for DOM to settle
			await new Promise(resolve => setTimeout(resolve, 100));

			// Take snapshot
			const canvasImage = await html2canvas(wrapper, {
				backgroundColor: null,
				useCORS: true,
				scale: 2
			});

			// Download the image
			const link = document.createElement('a');
			link.href = canvasImage.toDataURL('image/png');
			link.download = `${title}.png`;
			link.click();

			// Cleanup
			document.body.removeChild(wrapper);
		} catch (err) {
			console.error('Download failed:', err);
		}
	};

	return button;
}

function applyDatalabelsRotation(chart) {
	if (!chart.options.plugins.datalabels) {
		chart.options.plugins.datalabels = {};
	}

	// Only apply default rotation if user hasn't specified one
	if (chart.options.plugins.datalabels.rotation === undefined) {
		if (chart.config.type === 'bar') {
			const isHorizontal = chart.options.indexAxis === 'y';
			chart.options.plugins.datalabels.rotation = isHorizontal ? 0 : -90;
		} else if (['pie', 'doughnut', 'polarArea'].includes(chart.config.type)) {
			chart.options.plugins.datalabels.rotation = 0; // force horizontal for radial charts
		} else {
			chart.options.plugins.datalabels.rotation = 0; // default fallback
		}
	}
}

function applyDatalabelText(chart) {
	if (!chart?.options?.plugins?.datalabels) {
		console.warn('Datalabels plugin not available on this chart config.');
		return;
	}

	// Respect existing formatter provided by user; set default only if missing
	if (!chart.options.plugins.datalabels.formatter) {
		chart.options.plugins.datalabels.formatter = (value, context) => {
			const chartType = context.chart.config.type;
			if (value <= 0) return null;

			// Only show labels for non-bar charts
			if (['bar', 'line'].includes(chartType)) {
				return value; // or return just value.toString(); or null if you want to hide completely
			}

			const labels = context.chart.data?.labels || [];
			const label = labels[context.dataIndex] || '';
			return `${label}: ${value}`;
		};
	}

	chart.update();
}

function isDarkThemeByBg() {
	const bg = getCSSVar('--bs-body-bg-rgb');
	const match = bg.match(/\d+/g);

	if (!match) {
		console.warn('Unable to parse background color:', bg);
		return false; // default to light
	}

	const rgb = match.map(Number);

	// Calculate luminance
	const luminance = (0.299 * rgb[0] + 0.587 * rgb[1] + 0.114 * rgb[2]) / 255;
	return luminance < 0.5;
}

function getRandomColor(opacity = 0.6) {
	const r = isDarkThemeByBg() ?
		(Math.floor(Math.random() * 200) + 56) : // brighter colors
		Math.floor(Math.random() * 100); // darker range: 0–99
	const g = isDarkThemeByBg() ?
		(Math.floor(Math.random() * 200) + 56) :
		Math.floor(Math.random() * 100);
	const b = isDarkThemeByBg() ?
		(Math.floor(Math.random() * 200) + 56) :
		Math.floor(Math.random() * 100);
	return `rgba(${r}, ${g}, ${b}, ${opacity})`;
}
