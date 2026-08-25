<style>
	.dropzone {
		border: 3px dashed var(--bs-gray-200);
		border-radius: var(--bs-border-radius);
		padding: 2rem;
		text-align: center;
		transition: all 0.3s ease;
		background: var(--bs-gray-100);
		;
		cursor: pointer;
		width: 100%;
		display: block;
		box-sizing: border-box;
	}

	.dropzone:hover {
		border-color: var(--bs-primary);
		background: var(--bs-gray-200);
	}

	.dropzone.drag-over {
		border-color: var(--bs-primary);
		background: var(--bs-gray-200);
		transform: scale(1.02);
		box-shadow: 0 4px 12px var(--bs-gray-800);
	}

	.dropzone-content {
		display: block;
	}

	.file-info {
		display: none;
		text-align: center;
	}

	.dropzone.has-file .dropzone-content {
		display: none;
	}

	.dropzone.has-file .file-info {
		display: block;
		animation: fade-in 0.3s ease;
	}

	.dropzone.has-file {
		border-color: var(--bs-success);
		background: var(--bs-gray-200);
	}

	/* Multiple files list */
	.file-list {
		list-style: none;
		padding: 0;
		margin: 0;
		max-height: 200px;
		overflow-y: auto;
	}

	.file-list-item {
		display: flex;
		align-items: center;
		justify-content: space-between;
		padding: 0.5rem 0.75rem;
		background: var(--bs-gray-300);
		border-radius: var(--bs-border-radius);
	}

	.file-list-item:hover {
		background: var(--bs-gray-400);
	}

	.file-list-item .file-info-left {
		display: flex;
		align-items: center;
		gap: 0.5rem;
	}

	.file-list-item .file-icon {
		color: var(--bs-success);
	}

	.file-list-item .file-name {
		font-size: 0.875rem;
		font-weight: 500;
	}

	.file-list-item .file-size {
		font-size: 0.75rem;
	}

	.file-list-item .btn-remove {
		padding: 0.25rem 0.5rem;
		font-size: 0.75rem;
	}

	@keyframes fade-in {
		from {
			opacity: 0;
			transform: translateY(-10px);
		}

		to {
			opacity: 1;
			transform: translateY(0);
		}
	}
</style>

<?php
$dropZoneId = isset($dropZoneId) ? $dropZoneId : 'dropZone';
$fileInputId = isset($fileInputId) ? $fileInputId : 'document';
$accept = isset($accept) ? $accept : '.pdf';
$maxSize = isset($maxSize) ? $maxSize : 10;
$maxFiles = isset($maxFiles) ? $maxFiles : 1;
$labelText = isset($labelText) ? $labelText : 'Tarik file ke sini';
$subText = isset($subText) ? $subText : 'atau klik untuk memilih file';
$icon_class = isset($icon_class) ? $icon_class : 'fa-cloud-upload';
$onFileSelect = isset($onFileSelect) ? $onFileSelect : 'function(file) { window.selectedFile = file; }';
$onFileClear = isset($onFileClear) ? $onFileClear : 'function() { window.selectedFile = null; }';
// Convert required to string 'true' or 'false' based on input value
$required = isset($required) ? ($required ? 'true' : 'false') : 'false'; // Default to false (optional) if not specified
$disabled = isset($disabled) ? ($disabled ? 'true' : 'false') : 'false';
$multiple = isset($multiple) ? ($multiple ? 'true' : 'false') : 'false';
$submitBtnId = isset($submitBtnId) ? $submitBtnId : 'submitBtn';
$showBrowseBtn = isset($showBrowseBtn) ? ($showBrowseBtn ? 'true' : 'false') : 'false';
$imageMaxWidth = isset($imageMaxWidth) ? $imageMaxWidth : 212;
$imageQuality = isset($imageQuality) ? $imageQuality : 0.8;
?>

<div class="form-group w-100">
	<div class="input-wrap">
		<div id="<?= $dropZoneId ?>" class="dropzone" data-max-size="<?= $maxSize ?>" data-accept="<?= $accept ?>" data-max-files="<?= $maxFiles ?>" data-multiple="<?= $multiple ?>">
			<div class="dropzone-content">
				<i class="fa <?= $icon_class ?> fa-3x text-primary mb-3"></i>
				<h5 class="mb-2"><?= $labelText ?></h5>
				<p class="text-muted mb-3"><?= $subText ?></p>
				<?php if ($multiple === 'true' || $maxFiles > 1): ?>
					<p class="text-info mb-3"><small><i class="fas fa-circle-info"></i> Anda dapat memilih beberapa file sekaligus</small></p>
				<?php endif; ?>
				<p class="form-text text-muted mb-3">
					<small><i class="fas fa-file-lines"></i> Format: <strong><?= str_replace(',', ', ', trim($accept, '.')) ?></strong> (Max: <?= $maxSize ?>MB)</small>
				</p>
				<input type="file" id="input_<?= $fileInputId ?>" name="<?= $multiple === 'true' || $maxFiles > 1 ? $fileInputId . '[]' : $fileInputId ?>" accept="<?= $accept ?>" <?= $multiple === 'true' || $maxFiles > 1 ? 'multiple' : '' ?> style="display: none;">
				<?php if ($showBrowseBtn === 'true'): ?>
					<button type="button" class="btn btn-outline-primary browse-btn">
						<i class="fas fa-folder-open"></i> Pilih File
					</button>
				<?php endif; ?>
			</div>
			<div class="file-info">
				<i class="fas fa-file-lines fa-2x text-success mb-2"></i>
				<h5 class="file-name mb-1"></h5>
				<p class="file-size text-muted mb-3"></p>
				<button type="button" class="btn btn-sm btn-outline-danger" onclick="(function(){ var fn = window['clearFile_<?= $dropZoneId ?>']; if(fn) fn(); })()">
					<i class="fas fa-trash"></i> Hapus
				</button>
			</div>
			<ul class="file-list" style="display: none;"></ul>
		</div>
	</div>
</div>

<script>
	(function() {
		var dropZoneId = '<?= $dropZoneId ?>';
		var fileInputId = 'input_<?= $fileInputId ?>';
		var maxSize = <?= $maxSize ?>;
		var maxFiles = <?= $maxFiles ?>;
		var accept = '<?= $accept ?>';
		var onFileSelect = <?= !empty($onFileSelect) ? $onFileSelect : 'function() {}' ?>;
		var onFileClear = <?= !empty($onFileClear) ? $onFileClear : 'function() {}' ?>;
		var submitBtnId = '<?= $submitBtnId ?>';
		var required = '<?= $required ?>';
		var multiple = '<?= $multiple ?>';
		var imageMaxWidth = <?= $imageMaxWidth ?>;
		var imageQuality = <?= $imageQuality ?>;

		var dropZone = document.getElementById(dropZoneId);
		var fileInput = document.getElementById(fileInputId);
		var selectedFiles = [];

		if (!dropZone) {
			return;
		}

		if (!fileInput) {
			return;
		}

		// Store selectedFiles in a globally accessible way for this dropzone
		window['selectedFiles_' + dropZoneId] = [];

		// Prevent default drag behaviors
		['dragenter', 'dragover', 'dragleave', 'drop'].forEach(function(eventName) {
			dropZone.addEventListener(eventName, preventDefaults, false);
			document.body.addEventListener(eventName, preventDefaults, false);
		});

		function preventDefaults(e) {
			e.preventDefault();
			e.stopPropagation();
		}

		// Highlight drop zone when item is dragged over it
		['dragenter', 'dragover'].forEach(function(eventName) {
			dropZone.addEventListener(eventName, highlight, false);
		});

		['dragleave', 'drop'].forEach(function(eventName) {
			dropZone.addEventListener(eventName, unhighlight, false);
		});

		function highlight(e) {
			dropZone.classList.add('drag-over');
		}

		function unhighlight(e) {
			dropZone.classList.remove('drag-over');
		}

		// Handle dropped files
		dropZone.addEventListener('drop', handleDrop, false);

		function handleDrop(e) {
			var dt = e.dataTransfer;
			var files = dt.files;
			handleFiles(files);
		}

		function isValidFileType(file, accept) {
			if (accept === '*') return true;

			var acceptTypes = accept.split(',').map(function(t) {
				return t.trim().toLowerCase();
			});
			var fileName = file.name.toLowerCase();
			var fileType = file.type.toLowerCase();

			for (var i = 0; i < acceptTypes.length; i++) {
				var type = acceptTypes[i];
				if (type.startsWith('.')) {
					if (fileName.endsWith(type)) return true;
				} else if (type.endsWith('/*')) {
					var baseType = type.split('/')[0];
					if (fileType.startsWith(baseType + '/')) return true;
				} else {
					if (fileType === type || fileName.endsWith(type)) return true;
				}
			}

			return false;
		}

		function isImageFile(file) {
			return !!file.type && file.type.indexOf('image/') === 0;
		}

		// Resize (max width = imageMaxWidth, height scaled proportionally) and
		// compress an image file using a canvas. Falls back to the original
		// file if anything goes wrong (e.g. unsupported browser).
		function resizeImage(file, maxWidth, quality) {
			return new Promise(function(resolve) {
				var reader = new FileReader();

				reader.onload = function(e) {
					var img = new Image();

					img.onload = function() {
						var width = img.width;
						var height = img.height;

						// Only shrink, never upscale smaller images
						if (width > maxWidth) {
							height = Math.round((height * maxWidth) / width);
							width = maxWidth;
						}

						var canvas = document.createElement('canvas');
						canvas.width = width;
						canvas.height = height;

						var ctx = canvas.getContext('2d');
						ctx.drawImage(img, 0, 0, width, height);

						// Keep PNG as PNG (for transparency), compress everything else as JPEG
						var outputType = (file.type === 'image/png') ? 'image/png' : 'image/jpeg';

						canvas.toBlob(function(blob) {
							if (!blob) {
								resolve(file);
								return;
							}

							var resizedFile;
							try {
								resizedFile = new File([blob], file.name, {
									type: outputType,
									lastModified: Date.now()
								});
							} catch (err) {
								// Older browsers without File constructor support
								blob.name = file.name;
								resizedFile = blob;
							}

							resolve(resizedFile);
						}, outputType, quality);
					};

					img.onerror = function() {
						resolve(file);
					};

					img.src = e.target.result;
				};

				reader.onerror = function() {
					resolve(file);
				};

				reader.readAsDataURL(file);
			});
		}

		async function handleFiles(files) {
			for (var i = 0; i < files.length; i++) {
				var file = files[i];

				// Check max files limit
				if (maxFiles > 0 && selectedFiles.length >= maxFiles) {
					if (typeof showError !== 'undefined') {
						showError('Maksimal ' + maxFiles + ' file');
					} else {
						alert('Maksimal ' + maxFiles + ' file');
					}
					break;
				}

				// Validate file type
				if (accept !== '*' && !isValidFileType(file, accept)) {
					if (typeof showError !== 'undefined') {
						showError('Format file tidak sesuai: ' + file.name);
					} else {
						alert('Format file tidak sesuai: ' + file.name);
					}
					continue;
				}

				// Validate file size
				if (file.size > maxSize * 1024 * 1024) {
					if (typeof showError !== 'undefined') {
						showError('Ukuran file melebihi ' + maxSize + 'MB: ' + file.name);
					} else {
						alert('Ukuran file melebihi ' + maxSize + 'MB: ' + file.name);
					}
					continue;
				}

				// Resize & compress if the file is an image
				if (isImageFile(file)) {
					try {
						file = await resizeImage(file, imageMaxWidth, imageQuality);
					} catch (err) {
						console.error('Dropzone: image resize failed, using original file:', err);
					}
				}

				// Check for duplicates
				var isDuplicate = false;
				for (var j = 0; j < selectedFiles.length; j++) {
					if (selectedFiles[j].name === file.name && selectedFiles[j].size === file.size) {
						isDuplicate = true;
						break;
					}
				}
				if (isDuplicate) continue;

				// Add file
				selectedFiles.push(file);
			}

			updateUI();
		}

		// Handle file input change
		fileInput.addEventListener('change', function(e) {
			if (e.target.files.length > 0) {
				handleFiles(e.target.files);
			}
		});

		function updateUI() {
			window['selectedFiles_' + dropZoneId] = selectedFiles;

			if (selectedFiles.length === 0) {
				dropZone.classList.remove('has-file');

				// Ensure file-list is hidden
				var fileList = dropZone.querySelector('.file-list');
				if (fileList) {
					fileList.style.display = 'none';
					fileList.innerHTML = '';
				}

				if (submitBtnId) {
					var btn = document.getElementById(submitBtnId);
					// Fix: Convert required to boolean properly
					var shouldDisable = (required === true || required === 'true');
					if (btn && shouldDisable) {
						btn.disabled = true;
					} else if (btn) {
						btn.disabled = false;
					}
				}

				onFileClear();
			} else {
				dropZone.classList.add('has-file');

				// Hide file-list when only 1 file
				if (selectedFiles.length === 1) {
					var fileList = dropZone.querySelector('.file-list');
					if (fileList) fileList.style.display = 'none';
				}

				// Show file list if multiple files OR if maxFiles > 1
				if ((multiple || maxFiles > 1) && selectedFiles.length > 0) {
					// Show file list for multiple files
					var fileList = dropZone.querySelector('.file-list');
					fileList.innerHTML = '';

					for (var i = 0; i < selectedFiles.length; i++) {
						var file = selectedFiles[i];
						var li = document.createElement('li');
						li.className = 'file-list-item';
						li.innerHTML =
							'<div class="file-info-left">' +
							'<i class="fas fa-file-lines file-icon"></i>' +
							'<div>' +
							'<span class="file-name">' + escapeHtml(file.name) + '</span>' +
							'<span class="file-size"> (' + formatFileSize(file.size) + ')</span>' +
							'</div>' +
							'</div>' +
							'<button type="button" class="btn btn-sm btn-outline-danger btn-remove" data-index="' + i + '">' +
							'<i class="fas fa-xmark"></i>' +
							'</button>';
						fileList.appendChild(li);
					}

					fileList.style.display = 'block';
					dropZone.querySelector('.file-info').style.display = 'none';

					// Add remove button handlers
					fileList.querySelectorAll('.btn-remove').forEach(function(btn) {
						btn.addEventListener('click', function(e) {
							e.stopPropagation();
							var index = parseInt(this.getAttribute('data-index'));
							removeFile(index);
						});
					});
				} else {
					// Show single file info
					var file = selectedFiles[0];
					dropZone.querySelector('.file-name').textContent = file.name;
					dropZone.querySelector('.file-size').textContent = formatFileSize(file.size);
					dropZone.querySelector('.file-list').style.display = 'none';
					dropZone.querySelector('.file-info').style.display = 'block';
				}

				if (submitBtnId) {
					var btn = document.getElementById(submitBtnId);
					if (btn) {
						btn.disabled = false;
					}
				}

				onFileSelect(selectedFiles);
			}
		}

		function removeFile(index) {
			selectedFiles.splice(index, 1);
			updateUI();
		}

		function escapeHtml(text) {
			var div = document.createElement('div');
			div.textContent = text;
			return div.innerHTML;
		}

		window['clearFile_' + dropZoneId] = function() {
			selectedFiles = [];
			window['selectedFiles_' + dropZoneId] = [];
			fileInput.value = '';
			updateUI();
		};

		function formatFileSize(bytes) {
			if (bytes === 0) return '0 Bytes';
			var k = 1024;
			var sizes = ['Bytes', 'KB', 'MB', 'GB'];
			var i = Math.floor(Math.log(bytes) / Math.log(k));
			return Math.round(bytes / Math.pow(k, i) * 100) / 100 + ' ' + sizes[i];
		}

		// Click on dropzone to select file (always works, even without browse button)
		dropZone.addEventListener('click', function(e) {
			if (!dropZone.classList.contains('has-file')) {
				fileInput.click();
			}
		});

		// Browse button click handler (only if button exists)
		var browseBtn = dropZone.querySelector('.browse-btn');
		if (browseBtn) {
			browseBtn.addEventListener('click', function(e) {
				e.preventDefault();
				e.stopPropagation();
				fileInput.click();
			});
		}

		// Initialize submit button state
		if (submitBtnId) {
			var submitBtn = document.getElementById(submitBtnId);
			if (submitBtn && required === 'true') {
				submitBtn.disabled = true;
			}
		}

		// Add form submit validation if required
		if (required === 'true') {
			var form = dropZone.closest('form');
			if (form) {
				form.addEventListener('submit', function(e) {
					if (!selectedFiles || selectedFiles.length === 0) {
						e.preventDefault();
						e.stopPropagation();

						// Highlight dropzone to show error
						dropZone.style.borderColor = 'var(--bs-danger)';
						dropZone.style.boxShadow = '0 0 0 0.25rem rgba(220, 53, 69, 0.25)';

						// Scroll to dropzone
						dropZone.scrollIntoView({
							behavior: 'smooth',
							block: 'center'
						});

						// Show alert
						alert('Silakan pilih file untuk diunggah');

						// Remove highlight after 3 seconds
						setTimeout(function() {
							dropZone.style.borderColor = '';
							dropZone.style.boxShadow = '';
						}, 3000);

						return false;
					}
				}, true);
			}
		}

		// Expose selectedFiles getter for this dropzone
		window['getSelectedFiles_' + dropZoneId] = function() {
			return window['selectedFiles_' + dropZoneId];
		};

		// Backward compatibility
		window['getSelectedFile_' + dropZoneId] = function() {
			var files = window['selectedFiles_' + dropZoneId];
			return files && files.length > 0 ? files[0] : null;
		};

		// Handle AJAX form submission - transfer files from dropzone to FormData
		var form = dropZone.closest('form');
		if (form && form.classList.contains('form-ajax')) {
			// Store reference to original submit handler
			var originalSubmit = form.onsubmit;

			// Override form submit to inject files
			form.onsubmit = function(e) {
				if (selectedFiles && selectedFiles.length > 0) {
					// Try to set files directly to the input
					try {
						// Modern browsers: use DataTransfer
						var dataTransfer = new DataTransfer();
						for (var i = 0; i < selectedFiles.length; i++) {
							dataTransfer.items.add(selectedFiles[i]);
						}
						fileInput.files = dataTransfer.files;
					} catch (err) {
						console.error('Dropzone: DataTransfer failed:', err);
					}
				}

				// Call original handler if exists
				if (originalSubmit) {
					return originalSubmit.apply(this, arguments);
				}
				return true;
			};
		}
	})();
</script>