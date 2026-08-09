var $ = $.noConflict();
var table;
var currentAjax = null;
// Tracks the in-flight fetch() stream started by startProgress(), so a new
// .btn-progress click can cancel any still-running previous stream (e.g. a
// long cooldown countdown) instead of letting it keep writing into whatever
// modal is currently open.
var currentProgressController = null;

// Escapes text before it's interpolated into an HTML string (e.g. via
// jQuery .append()), so values coming from server data (names, case
// numbers, API responses) can't be interpreted as markup/script.
function escapeHtml(value) {
	if (value === null || value === undefined) {
		return "";
	}
	return String(value)
		.replace(/&/g, "&amp;")
		.replace(/</g, "&lt;")
		.replace(/>/g, "&gt;")
		.replace(/"/g, "&quot;")
		.replace(/'/g, "&#39;");
}

if (typeof $.busyLoadSetup === "function") {
	$.busyLoadSetup({
		animation: "fade", //fade, false, slide
		animationDuration: "slow", //4000, etc...
		background: "rgba(44, 59, 65, 0.5)",
		color: "#EEFFDD", //white, etc...
		textColor: "#EEFFDD", //white, etc...
		text: "SEDANG MEMPROSES...",
		textPosition: "bottom",
		textMargin: "2rem",
		fontSize: "2rem",
		spinner: "cube-grid", //accordion, circles, circle-line, cube, cubes, cube-grid, pulsar
		// maxSize: '150px',
		// minSize: '150px',
		//fontawesome: 'fa fa-spinner fa-pulse fa-5x fa-fw',
		//containerClass: 'my-own-container-class',
		//containerItemClass: 'my-own-container-item-class',
		//spinnerClass: 'my-own-spinner-class',
		//textClass: 'my-own-text-class',
	});
}

function showToast(
	message,
	title = "",
	type = "bg-primary",
	autohide = true,
	delay = 5000
) {
	const toastElement = document.getElementById("globalToast");
	if (!toastElement) {
		console.error('[showToast] Toast element not found in DOM');
		return;
	}
	const toastHeader = document.getElementById("toastHeader");
	const toastTitle = document.getElementById("toastTitle");
	const toastBodyContainer = document.getElementById("toastBodyContainer");
	const toastBody = document.getElementById("toastBody");

	const closeButton = document.createElement("button");
	closeButton.type = "button";
	closeButton.className = "btn-close btn-close-white me-2 m-auto";
	closeButton.setAttribute("data-bs-dismiss", "toast");
	closeButton.setAttribute("aria-label", "Close");

	toastBody.innerHTML = message;

	if (!title) {
		toastHeader.style.display = "none";

		if (!toastBodyContainer.querySelector(".btn-close")) {
			toastBodyContainer.appendChild(closeButton);
		}
	} else {
		toastTitle.textContent = title;
		toastHeader.style.display = "flex"; // Reset to default if visible

		if (!toastHeader.querySelector(".btn-close")) {
			toastHeader.appendChild(closeButton);
		}
	}

	toastElement.className = "toast";
	toastElement.classList.add(type || "bg-primary");

	const toast = new bootstrap.Toast(toastElement, { autohide, delay });
	toast.show();
}

/* Busy-Load guards: avoid errors when plugin is gated off per-page */
function busyShow() {
	try {
		if (typeof $.busyLoadFull === "function") {
			$.busyLoadFull("show");
		}
	} catch (e) {
		console.error("Error in busyShow:", e);
	}
}
function busyHide() {
	try {
		if (typeof $.busyLoadFull === "function") {
			$.busyLoadFull("hide");
		}
	} catch (e) { }
}

/**
 * Update page header when content is loaded via AJAX
 * @param {string} title - Page title
 * @param {string} subtitle - Page subtitle (optional)
 * @param {string} icon - Page icon (optional)
 */
function updatePageHeader(title, subtitle, icon) {
	var $pageHeader = $('.page-header');

	// Create page header if it doesn't exist
	if ($pageHeader.length === 0) {
		$('main.app-main').prepend(
			'<div class="page-header mb-4">' +
			'<div class="d-flex align-items-center justify-content-between">' +
			'<div class="d-flex align-items-center">' +
			'<h1 class="m-0"></h1>' +
			'</div>' +
			'</div>' +
			'<p class="m-0 mt-1"></p>' +
			'</div>'
		);
		$pageHeader = $('.page-header');
	}

	// Update title with icon
	var $titleEl = $pageHeader.find('h1');
	if ($titleEl.length === 0) {
		$pageHeader.find('.d-flex.align-items-center').append('<h1 class="m-0"></h1>');
		$titleEl = $pageHeader.find('h1');
	}

	if (icon) {
		$titleEl.html('<i class="' + icon + '" style="opacity:.9"></i> ' + title);
	} else {
		$titleEl.text(title);
	}

	// Update subtitle
	var $subtitleEl = $pageHeader.find('p');
	if ($subtitleEl.length === 0) {
		$pageHeader.find('.d-flex.align-items-center').after('<p class="m-0 mt-1"></p>');
		$subtitleEl = $pageHeader.find('p');
	}

	if (subtitle) {
		$subtitleEl.text(subtitle);
	} else {
		$subtitleEl.text('JOSS (JOSS)');
	}

	// Scroll to top smoothly
	$('html, body').animate({ scrollTop: 0 }, 300);
}

// Handle DataTable detection in ajaxSend where we have access to settings
$(document).ajaxSend(function (event, xhr, settings) {
	// Check if the AJAX request is from DataTables by looking for DataTable-specific parameters
	var data = settings.data || "";
	var url = settings.url || "";

	// Convert data to string if it's an object (like FormData)
	if (typeof data !== "string" && data !== "") {
		if (data instanceof FormData) {
			// For FormData, we can't easily check contents directly,
			// We'll need to access the FormData differently or just check the URL
			// This is complex, so let's try to access it differently
			data = "";
		} else {
			// Convert object to query string for checking
			if (typeof data === "object") {
				data = $.param(data);
			} else {
				data = String(data);
			}
		}
	}

	// DataTables specific detection - comprehensive check
	const hasDraw =
		(data && data.includes("draw=")) || (url && url.includes("draw="));
	const hasStart =
		(data && data.includes("start=")) || (url && url.includes("start="));
	const hasLength =
		(data && data.includes("length=")) || (url && url.includes("length="));

	// Additional DataTables identifiers
	const hasColumns =
		(data && data.includes("columns[")) || (url && url.includes("columns["));
	const hasOrder =
		(data && data.includes("order[")) || (url && url.includes("order["));
	const hasSearchValue =
		(data && data.includes("search[value]")) ||
		(url && url.includes("search[value]"));
	const hasSearchRegex =
		(data && data.includes("search[regex]")) ||
		(url && url.includes("search[regex]"));

	// Determine if this is likely a DataTable request
	let isDataTableRequest = false;

	// Primary check: draw + start + length (standard DataTables params)
	if (hasDraw && hasStart && hasLength) {
		isDataTableRequest = true;
	}
	// Alternative check: draw + any other DataTable-specific param
	else if (
		hasDraw &&
		(hasColumns || hasOrder || hasSearchValue || hasSearchRegex)
	) {
		isDataTableRequest = true;
	}

	// Store the result in xhr object so we can access it in other handlers
	xhr._isDataTableRequest = isDataTableRequest;

	if (
		(typeof hideLoader === "undefined" || !hideLoader) &&
		!isDataTableRequest
	) {
		busyShow();
	}
});

$(document).ajaxSuccess(function (event, jqxhr, settings, response) {
	if (response && response.not_logged_in && response.not_logged_in === true) {
		document.location.href = window.location.href;
	}

	if (response && response.csrf_token_name) {
		localStorage.setItem("csrfName", response.csrf_token_name);
	}

	if (response && response.csrf_hash) {
		localStorage.setItem("csrfToken", response.csrf_hash);
	}
});

$(document).ajaxComplete(function (event, jqxhr, settings) {
	busyHide();

	// ── Restore any .form-ajax submit button that was put into loading state ──
	// Walk all forms that have a pending restore callback and call it.
	document.querySelectorAll('form._ajax-btn-pending').forEach(function (form) {
		if (typeof form._restoreSubmitBtn === 'function') {
			form._restoreSubmitBtn();
		}
		form.classList.remove('_ajax-btn-pending');
	});

	// Also handle the simpler case: the form that just submitted stores
	// _restoreSubmitBtn directly — find it via the XHR's originating form.
	// Fallback: scan all forms for a pending restore.
	document.querySelectorAll('form').forEach(function (form) {
		if (typeof form._restoreSubmitBtn === 'function') {
			form._restoreSubmitBtn();
		}
	});

	// Reinitialize menu filter after any AJAX request completes
	if (typeof initializeMenuFilter === 'function') {
		setTimeout(function () {
			initializeMenuFilter();
			if (typeof reexpandActiveMenus === 'function') {
				reexpandActiveMenus();
			}
		}, 100);
	}
});

// $(document).ajaxError(function (event, jqxhr, settings, thrownError) {
// 	if (thrownError !== 'abort')
// 		showToast('Terjadi Kesalahan. Silakan refresh halaman terlebih dahulu.', '', 'bg-danger');
// });

setInterval(function () {
	$(".realtime-clock").html(
		new Date()
			.toLocaleDateString("id-ID", {
				weekday: "long",
				day: "2-digit",
				month: "long",
				year: "numeric",
				hour: "2-digit",
				minute: "2-digit",
				second: "2-digit",
			})
			.replace(/\./g, ":")
	);
}, 1000);

//handle back button ajax
$(function () {
	var type;

	// Guard: jquery-history may be omitted in light assets mode
	if (
		typeof $.history !== "undefined" &&
		$.history &&
		$.history.on &&
		$.history.listen
	) {
		$.history
			.on("load change", function (event, url, type) {
				if (
					event.type === "change" ||
					(event.type === "load" && type === "hash")
				) {
					// Update active menu highlighting for SPA navigation
					if (typeof updateActiveMenu === 'function') {
						updateActiveMenu(url);
					}
					callAjax({ url: url });
				}
			})
			.listen();

		type = $.history.type();
		if (type === "hash" && location.pathname.length > 1) {
			// /pathname -> /#/pathname
			location.href = "/#" + location.pathname;
		} else if (type === "pathname" && location.hash.substr(1, 1) === "/") {
			// /#/pathname -> /pathname
			location.href = location.hash.substr(1);
		}
	}

	// Activate correct menu on page load (exact match only)
	// PHP already sets active classes on initial page render.
	// For SPA navigation, updateActiveMenu() will be called on click/history change.
	// No need for initial setTimeout call.
});

function prepareModalContent(showLoadingBar = true) {
	$("#modal-input .modal-title").html("Sedang memproses...");

	if (showLoadingBar) {
		$("#modal-input .modal-body").html(
			'<div class="progress my-2"><div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%"></div></div>'
		);
	} else {
		busyShow();
	}
	// Ensure default modal size is set before showing
	$("#modal-input-dialog").removeClass();
	$("#modal-input-dialog").addClass(
		"modal-dialog modal-dialog-scrollable modal-md"
	);

	$("#modal-input").modal("show", {
		backdrop: "static",
		keyboard: false,
	});
}

function setModalContent(data) {
	let showTitle = data.showTitle != undefined ? data.showTitle : true;
	if (showTitle) {
		$("#modal-input .modal-title").html(data.title ? data.title : "Info");
	} else {
		$("#modal-input .modal-title").html("");
	}

	let hideCloseButton =
		data.hideCloseButton != undefined ? data.hideCloseButton : false;
	if (hideCloseButton) {
		// hide close button
		$("#modal-input .modal-header .btn-close").hide();
	} else {
		// ensure close button is visible if it exists
		$("#modal-input .modal-header .btn-close").show();
	}

	if (data.redirect && data.message) {
		showToast(data.message, "", "bg-danger");
		$("#modal-input .modal-body").html(
			'<div class="alert alert-danger alert-dismissible"><button type="button" class="btn-close ms-0" data-bs-dismiss="alert" aria-label="Close"></button><h5><i class="icon fa fa-warning"></i> Perhatian!</h5>' +
			data.message +
			"</div>"
		);
	} else {
		$("#modal-input .modal-body").html("");
		$("#modal-input .modal-body").html(data.content ? data.content : "");

		$("#modal-input .modal-body").find(".table-sticky thead th").css("top", 0);

		// Reinitialize menu filter after modal content is loaded
		if (typeof initializeMenuFilter === 'function') {
			initializeMenuFilter();
		}
		if (typeof reexpandActiveMenus === 'function') {
			reexpandActiveMenus();
		}
	}

	// Reset to default size first
	$("#modal-input-dialog").removeClass();
	$("#modal-input-dialog").addClass(
		"modal-dialog modal-dialog-scrollable modal-md"
	);

	// Then apply the requested size if provided
	if (data.size) {
		$("#modal-input-dialog").removeClass("modal-sm modal-md modal-lg modal-xl");
		$("#modal-input-dialog").addClass(data.size);
	}

	$("#modal-input").modal("show", {
		backdrop: "static",
		keyboard: false,
	});

	$(".btn-back").addClass("btn-modal");
}

function showAlert(
	message,
	title = "",
	target = ".container-main .card-body:first",
	alertClass = "danger",
	dismissible = true
) {
	removeAlert(target);
	$(target).prepend(
		'<div class="mb-1 my-alert alert alert-' +
		alertClass +
		" " +
		(dismissible ? "alert-dismissible " : "") +
		'"><h5>' +
		title +
		"</h5>" +
		message +
		"</div>"
	);
}

function removeAlert(target = ".container-main .card-body:first") {
	$(target + " .my-alert").remove();
}

function openModal(url, data = {}) {
	prepareModalContent();

	var title = data.title ? data.title : "";

	currentAjax = $.ajax({
		url: url,
		data: data,
		success: function (data) {
			if (title) {
				data.title = title;
			}
			setModalContent(data);
		},
	});
}

function postAjax(url, data = {}, processData = true, showLoadingBar = true, fullPageRedirect = false) {
	var csrfName = localStorage.getItem("csrfName");
	var csrfHash = localStorage.getItem("csrfToken");

	if (data instanceof FormData) {
		data.append(csrfName, csrfHash);
	} else {
		data[csrfName] = csrfHash;
	}

	let options = {
		url: url,
		type: "post",
		data: data,
		// dataType: 'text',
		// contentType: 'application/x-www-form-urlencoded',
		beforeSend: function () {
			if ($("#modal-input").is(":visible")) {
				prepareModalContent(showLoadingBar);
			} else if (showLoadingBar) {
				busyShow();
			}
		},
		success: function (data, textStatus, jQxhr) {
			// Update CSRF tokens immediately from response (synchronous)
			if (data.csrf_token_name && data.csrf_hash) {
				localStorage.setItem('csrfName', data.csrf_token_name);
				localStorage.setItem('csrfToken', data.csrf_hash);
			}

			if (data.message) {
				showToast(data.message, "", data.status ? "bg-primary" : "bg-danger");
			}

			if (data.status) {
				if (data.urlSummary) {
					count_summary(data.urlSummary);
				}

				if (data.redirect) {
					if (fullPageRedirect) {
						window.location.href = data.redirect;
					} else {
						callAjax({ url: data.redirect, showOnModal: data.showOnModal || false, showBreadcrumb: true, showLoadingBar: showLoadingBar });
					}
				} else if (data.content) {
					if (data.showOnModal) {
						setModalContent(data);
					} else {
						if (data.breadcrumb) {
							if ($(".app-breadcrumb").length === 0) {
								$('<div class="app-breadcrumb"></div>').prependTo(
									"app-content"
								);
							}
							$(".app-breadcrumb").html(data.breadcrumb);
						}

						$("#modal-input").modal("hide");
						$(".container-main").html(data.content);

						// Update page header if showPageHeader is not explicitly false
						if (typeof updatePageHeader === 'function' && data.title && (data.showPageHeader === undefined || data.showPageHeader !== false)) {
							updatePageHeader(
								data.title,
								data.subtitle || data.description || '',
								data.icon || data.page_icon || ''
							);
							$('.page-header').show();
						} else if (data.showPageHeader === false) {
							// Hide page header if explicitly set to false
							$('.page-header').hide();
						}
					}
				}
			} else {
				if (data.content) {
					if ($("#modal-input.show").length > 0) {
						setModalContent(data);
					} else {
						if (data.breadcrumb) {
							if ($(".app-breadcrumb").length === 0) {
								$('<div class="app-breadcrumb"></div>').prependTo(
									"app-content"
								);
							}
							$(".app-breadcrumb").html(data.breadcrumb);
						}

						$(".container-main").html(data.content);
						$(".btn-back").addClass("btn-ajax");

						// Update page header if showPageHeader is not explicitly false
						if (typeof updatePageHeader === 'function' && data.title && (data.showPageHeader === undefined || data.showPageHeader !== false)) {
							updatePageHeader(
								data.title,
								data.subtitle || data.description || '',
								data.icon || data.page_icon || ''
							);
							$('.page-header').show();
						} else if (data.showPageHeader === false) {
							// Hide page header if explicitly set to false
							$('.page-header').hide();
						}

						// Reinitialize menu filter after content is loaded
						if (typeof initializeMenuFilter === 'function') {
							initializeMenuFilter();
						}
						if (typeof reexpandActiveMenus === 'function') {
							reexpandActiveMenus();
						}
					}
				}
			}
		},
		complete: function () {
			if (showLoadingBar) {
				busyHide();
			}
		},
		error: function (xhr, status, error) {
			// Hide busyload on error
			if (showLoadingBar) {
				busyHide();
			}
		},
	};

	if (processData === false) {
		options.processData = false;
		options.contentType = false;
	}

	currentAjax = $.ajax(options);
}

function updateActiveMenu(currentUrl) {
	// Minimal exact-match active state updater for AJAX navigation.
	// Uses server-rendered active classes on initial load; on subsequent navigation
	// we compute exact match only (no prefix-starts-with to avoid false positives).

	// Remove existing active/menu-open from sidebar
	$("ul.sidebar-menu .nav-item").removeClass('active menu-open');
	$("ul.sidebar-menu .nav-link").removeClass('active');
	$("ul.sidebar-menu ul.nav-treeview").css('display', ''); // collapse all

	// Extract pathname if full URL
	let currentPath = currentUrl;
	if (currentUrl && currentUrl.startsWith('http')) {
		try {
			currentPath = new URL(currentUrl).pathname;
		} catch (e) {
			currentPath = currentUrl;
		}
	}

	// Find the leaf whose href exactly matches the current path
	let $matchedItem = null;
	$("ul.sidebar-menu .nav-item").each(function (idx, li) {
		const $link = $(li).find('> a.nav-link');
		const href = $link.attr('href');
		if (!href || href === '#') return; // skip parents

		// Normalize href: if absolute URL, take pathname; else as-is
		let linkPath = href;
		if (href.startsWith('http')) {
			try { linkPath = new URL(href).pathname; } catch (e) { }
		}

		if (linkPath === currentPath) {
			$matchedItem = $(li);
			return false; // break
		}
	});

	// If exact match found, mark it active and expand all ancestors
	if ($matchedItem) {
		$matchedItem.addClass('active');
		$matchedItem.find('> a.nav-link').addClass('active');
		expandParentMenus($matchedItem);
	}
	// If no exact match, do nothing — keep no active state (will show none)
}

// Function to expand all parent menus up to the root
function expandParentMenus($element) {
	if ($element.is('li')) {
		$element.addClass('menu-open');
		// Also add active class to parent <li> and its <a> nav-link for visual highlighting
		$element.addClass('active');
		const $link = $element.find('> a.nav-link');
		if ($link.length) {
			$link.addClass('active');
		}
		// FIX: Also set inline display on the submenu so it's visible
		const $subMenu = $element.find('> ul.nav-treeview');
		if ($subMenu.length) {
			$subMenu.css('display', 'block');
		}
	}

	// Find the parent li element that contains this element
	const $parentLi = $element.closest('ul.nav-treeview').closest('li');

	// If a parent li is found, recursively call this function
	if ($parentLi.length) {
		expandParentMenus($parentLi);
	}
}

function callAjax($options) {
	// Extract options with defaults
	const url = $options.url;
	const showBreadcrumb = $options.showBreadcrumb !== undefined ? $options.showBreadcrumb : true;
	const showLoadingBar = $options.showLoadingBar !== undefined ? $options.showLoadingBar : true;
	const isRedirect = $options.isRedirect !== undefined ? $options.isRedirect : false;
	const showOnModal = $options.showOnModal !== undefined ? $options.showOnModal : false;

	$.ajax({
		url: url,
		beforeSend: function () {
			if (!isRedirect && $("#modal-input").is(":visible")) {
				prepareModalContent();
			} else if (showLoadingBar) {
				busyShow();
			}
		},
		success: function (data) {
			if (data.message) {
				showToast(data.message, "", data.status ? "bg-primary" : "bg-danger");
			}

			if (data.redirect) {
				callAjax({ url: data.redirect, showOnModal: data.showOnModal || false });
			} else if (data.content) {
				if (showOnModal || data.showOnModal) {
					setModalContent(data);
				} else {
					if (data.breadcrumb) {
						if (
							showBreadcrumb &&
							url != new RegExp(/^.*\//).exec(window.location.href)[0]
						) {
							if ($(".app-breadcrumb").length === 0) {
								$('<div class="app-breadcrumb"></div>').prependTo(
									"app-content"
								);
							}
							$(".app-breadcrumb").html(data.breadcrumb);
						} else {
							$(".app-breadcrumb").html("");
						}
					}

					if (!isRedirect) {
						$("#modal-input").modal("hide");
					}

					$(".container-main").html(data.content);

					if (data.title) {
						$(".container-main-title").html(data.title);
					}

					// Update page header if showPageHeader is not explicitly false
					if (typeof updatePageHeader === 'function' && data.title && (data.showPageHeader === undefined || data.showPageHeader !== false)) {
						updatePageHeader(
							data.title,
							data.subtitle || data.description || '',
							data.icon || data.page_icon || ''
						);
						$('.page-header').show();
					} else if (data.showPageHeader === false) {
						// Hide page header if explicitly set to false
						$('.page-header').hide();
					}

					// Reinitialize menu filter after content is loaded
					if (typeof initializeMenuFilter === 'function') {
						initializeMenuFilter();
					}
					if (typeof reexpandActiveMenus === 'function') {
						reexpandActiveMenus();
					}
				}

				if (typeof $.history !== 'undefined' && $.history) {
					$.history.push(url);
				}

				if (document.title != data.title) {
					document.title = data.title;
				}
			}
		},
		error: function (xhr, status, error) {
			console.log("-----------ERROR", status, error);
			console.log("Response:", xhr.responseText);
			console.log("Status code:", xhr.status);
		},
		// complete: function () {
		// },
	});
}

function loadPartial(url, target, data = {}, showLoader = false) {
	if (showLoader) {
		busyShow();
	}

	currentAjax = $.ajax({
		url: url,
		data: data,
		success: function (data) {
			$(target).html(data.content ? data.content : "");
			if (showLoader) {
				busyHide();
			}

			// Reinitialize menu filter after content is loaded
			if (typeof initializeMenuFilter === 'function') {
				initializeMenuFilter();
			}
			if (typeof reexpandActiveMenus === 'function') {
				reexpandActiveMenus();
			}
		},
		error: function (jqXhr, textStatus, errorThrown) {
			if (showLoader) {
				busyHide();
			}
		},
	});
}

function paginationPartial(target) {
	$("body").on("click", target + " .pagination-partial li a", function (e) {
		e.preventDefault();
		if (currentAjax) {
			currentAjax.abort();
		}
		loadPartial($(this).attr("href"), target);
	});
}

var enableRefresh = false;
var intervalRefresh = 1000;
var worker;

//handle form ajax
$("body").on("submit", ".form-ajax", function (e) {
	e.preventDefault();

	var form = this;

	// ── Loading state for the submit button ───────────────────────────────────
	var $btn = $(form).find(
		'button[type="submit"]:not(.btn-no-loading),' +
		'input[type="submit"]:not(.btn-no-loading)'
	).filter(':visible:not(:disabled)').first();

	var $inputBtn = $btn.is('input[type="submit"]') ? $btn : null;
	var $buttonBtn = $btn.is('button[type="submit"]') ? $btn : null;

	if ($inputBtn) {
		// <input type="submit">: value-swap + opacity (no innerHTML injection possible)
		$inputBtn[0].dataset.originalValue = $inputBtn.val();
		$inputBtn.val('Memproses...').addClass('loading').prop('disabled', true);
	} else if ($buttonBtn) {
		// <button type="submit">: spinner injection (shared prepareButton logic)
		if (typeof prepareButton === 'function') {
			prepareButton($buttonBtn[0]);
		} else {
			// Inline fallback if prepareButton isn't in scope
			if (!$buttonBtn[0].dataset.loadingPrepared) {
				$buttonBtn[0].dataset.loadingPrepared = '1';
				if (!$buttonBtn[0].querySelector('.btn-text')) {
					$buttonBtn[0].innerHTML =
						'<span class="btn-text">' + $buttonBtn[0].innerHTML.trim() + '</span>';
				}
				var sp = document.createElement('span');
				sp.className = '_btn-spinner';
				sp.setAttribute('aria-hidden', 'true');
				$buttonBtn[0].appendChild(sp);
			}
		}
		$buttonBtn.addClass('loading').prop('disabled', true);
	}

	// ── Restore helper (called in ajaxComplete below) ─────────────────────────
	// Store on the form element so the ajaxComplete handler can reach it
	// regardless of closure scope.
	form._restoreSubmitBtn = function () {
		if ($inputBtn) {
			var orig = $inputBtn[0].dataset.originalValue;
			if (orig !== undefined) $inputBtn.val(orig);
			delete $inputBtn[0].dataset.originalValue;
			$inputBtn.removeClass('loading').prop('disabled', false);
		} else if ($buttonBtn) {
			$buttonBtn.removeClass('loading').prop('disabled', false);
		}
		delete form._restoreSubmitBtn;
	};

	var formData = new FormData(form);

	// Look for dropzone file inputs and check for associated selectedFiles
	$(form).find('input[type="file"]').each(function () {
		var fileInput = this;
		var fieldName = fileInput.name.replace('[]', '');
		var isMultiple = fileInput.multiple || fileInput.name.endsWith('[]');

		var selectedFilesKey = 'selectedFiles_' + fileInput.id;
		var selectedFiles = window[selectedFilesKey];

		if (selectedFiles && selectedFiles.length > 0) {
			formData.delete(fieldName);
			formData.delete(fieldName + '[]');
			if (isMultiple || selectedFiles.length > 1) {
				for (var i = 0; i < selectedFiles.length; i++) {
					formData.append(fieldName + '[]', selectedFiles[i]);
				}
			} else {
				formData.append(fieldName, selectedFiles[0]);
			}
		} else if (fileInput.files && fileInput.files.length > 0) {
			formData.delete(fieldName);
			formData.delete(fieldName + '[]');
			if (isMultiple) {
				for (var i = 0; i < fileInput.files.length; i++) {
					formData.append(fieldName + '[]', fileInput.files[i]);
				}
			} else {
				formData.append(fieldName, fileInput.files[0]);
			}
		}
	});

	postAjax($(form).attr("action"), formData, false, true, $(form).attr("action").indexOf("login") !== -1);
});

$("body").on("click", ".btn-modal", function (e) {
	e.preventDefault();
	if (currentAjax) {
		currentAjax.abort();
	}
	openModal($(this).attr("href"), {});
});

$("form").on("submit", function () {
	if (currentAjax) {
		currentAjax.abort();
	}
});

// ── Submit-button loading state for standard (non-AJAX) form submits ──────────
// Mirrors the login page pattern: swap btn-text→spinner, disable, guard double-submit.
// Applies to any submit button that is NOT inside a .form-ajax form and NOT
// explicitly opted out with .btn-no-loading.
// ── Submit-button loading state for standard (non-AJAX) form submits ──────────
(function () {
	if (!document.getElementById('_submit-loading-style')) {
		var style = document.createElement('style');
		style.id = '_submit-loading-style';
		style.textContent = [
			'@keyframes _btn-spin { to { transform: rotate(360deg); } }',

			'button[type="submit"] ._btn-spinner {',
			'  display: none;',
			'  width: 17px; height: 17px;',
			'  border: 2px solid rgba(255,255,255,.35);',
			'  border-top-color: #fff;',
			'  border-radius: 50%;',
			'  animation: _btn-spin .7s linear infinite;',
			'  flex-shrink: 0;',
			'  vertical-align: middle;',
			'}',

			'button[type="submit"].loading .btn-text { display: none !important; }',
			'button[type="submit"].loading ._btn-spinner { display: inline-block !important; }',

			'button[type="submit"].loading .spinner {',
			'  display: block !important;',
			'  width: 18px; height: 18px;',
			'  border: 2px solid rgba(255,255,255,.3);',
			'  border-top-color: #fff;',
			'  border-radius: 50%;',
			'  animation: _btn-spin .7s linear infinite;',
			'  flex-shrink: 0;',
			'}',

			// ── FIX: <input type="submit"> loading state ──────────────────
			// Can't inject child nodes into <input>, so we:
			//   1. swap value text to a loading label  (done in JS)
			//   2. show a CSS ::after pseudo-spinner   (needs position:relative wrapper,
			//      but <input> doesn't support ::after either, so we rely on
			//      opacity + cursor change + value swap only)
			'input[type="submit"].loading {',
			'  opacity: 0.72;',
			'  cursor: not-allowed;',
			'  pointer-events: none;',
			'}',
		].join('\n');
		document.head.appendChild(style);
	}

	function prepareButton(btn) {
		if (btn.dataset.loadingPrepared) return;
		btn.dataset.loadingPrepared = '1';
		if (btn.querySelector('.btn-text') && btn.querySelector('.spinner')) return;
		var inner = btn.innerHTML.trim();
		if (!btn.querySelector('.btn-text')) {
			btn.innerHTML = '<span class="btn-text">' + inner + '</span>';
		}
		var sp = document.createElement('span');
		sp.className = '_btn-spinner';
		sp.setAttribute('aria-hidden', 'true');
		btn.appendChild(sp);
	}

	// ── NEW: handle <input type="submit"> separately ──────────────────────────
	function applyInputLoading($input) {
		// Store original value so we can restore it in the safety valve
		$input[0].dataset.originalValue = $input.val();
		$input.val('Memproses...');
		$input.addClass('loading').prop('disabled', true);
	}

	function restoreInputLoading($input) {
		var original = $input[0].dataset.originalValue;
		if (original !== undefined) {
			$input.val(original);
			delete $input[0].dataset.originalValue;
		}
		$input.removeClass('loading').prop('disabled', false);
	}

	$('body').on('submit', 'form:not(.form-ajax)', function (e) {
		var form = this;

		if (form._submitLocked) {
			e.preventDefault();
			return;
		}

		var $candidates = $(form).find(
			'button[type="submit"]:not(.btn-no-loading),' +
			'input[type="submit"]:not(.btn-no-loading)'
		).filter(':visible:not(:disabled)');

		var $btn = $candidates.first();
		if (!$btn.length) return;

		form._submitLocked = true;

		if ($btn.is('input[type="submit"]')) {
			// ── <input> path: value-swap + opacity ───────────────────────────
			applyInputLoading($btn);

			setTimeout(function () {
				form._submitLocked = false;
				restoreInputLoading($btn);
			}, 15000);

		} else {
			// ── <button> path: spinner injection (existing behaviour) ─────────
			prepareButton($btn[0]);
			$btn.addClass('loading').prop('disabled', true);

			setTimeout(function () {
				form._submitLocked = false;
				$btn.prop('disabled', false).removeClass('loading');
			}, 15000);
		}
	});
})();

// ── Loading state for .btn-ajax clicks ────────────────────────────────────────
// Same visual pattern as the submit-button block above:
//   • wraps button content in .btn-text + injects ._btn-spinner if needed
//   • adds .loading + disabled on click
//   • restores original state on ajaxComplete (covers callAjax, postAjax, openModal)
// Opt-out: add .btn-no-loading to the button.
(function () {
	var $activeBtn = null;     // the button that triggered the current request
	var originalHtml = null;   // its original innerHTML before prepareButton mutated it
	var wasDisabled = false;   // its disabled state before we changed it

	// Reuse prepareButton from the submit-loading block (already defined on body scope).
	// Guard: define a local copy if somehow this block runs before that one.
	function prepareBtn(btn) {
		if (btn.dataset.loadingPrepared) return;
		btn.dataset.loadingPrepared = '1';
		if (btn.querySelector('.btn-text') && btn.querySelector('.spinner')) return;
		var inner = btn.innerHTML.trim();
		if (!btn.querySelector('.btn-text')) {
			btn.innerHTML = '<span class="btn-text">' + inner + '</span>';
		}
		var sp = document.createElement('span');
		sp.className = '_btn-spinner';
		sp.setAttribute('aria-hidden', 'true');
		btn.appendChild(sp);
	}

	function startLoading($btn) {
		if (!$btn || !$btn.length) return;
		var btn = $btn[0];

		// Snapshot state BEFORE prepareBtn mutates innerHTML
		wasDisabled = btn.disabled;
		originalHtml = btn.innerHTML;
		$activeBtn = $btn;

		prepareBtn(btn);
		$btn.addClass('loading').prop('disabled', true);
	}

	function stopLoading() {
		if (!$activeBtn || !$activeBtn.length) return;
		$activeBtn
			.removeClass('loading')
			.prop('disabled', wasDisabled);

		// Restore original markup so repeated clicks look identical
		if (originalHtml !== null) {
			$activeBtn[0].innerHTML = originalHtml;
			// prepareButton must re-run next time since we just wiped its injected nodes
			delete $activeBtn[0].dataset.loadingPrepared;
		}

		$activeBtn = null;
		originalHtml = null;
		wasDisabled = false;
	}

	// ── Intercept .btn-ajax clicks ────────────────────────────────────────────
	// The existing handler calls callAjax(); we piggy-back on the same event
	// (delegated, so this runs for dynamically loaded buttons too).
	$('body').on('click', '.btn-ajax:not(.btn-no-loading)', function () {
		// Only <button> and <a> rendered as buttons get the spinner.
		// Plain <a> elements are included because .btn-ajax is often on anchors.
		startLoading($(this));
	});

	// ── Restore on every ajaxComplete ─────────────────────────────────────────
	// ajaxComplete fires after callAjax, postAjax, openModal, loadPartial —
	// every jQuery $.ajax path — so we never leave the button stuck.
	$(document).ajaxComplete(function () {
		stopLoading();
	});
})();

$("body").on(
	"click",
	'a:not([href="#"]):not([target="_blank"]):not(.btn-modal):not(.btn-non-ajax):not(.btn-external):not(.btn-block):not(.btn-confirm):not(.btn-progress):not(.btn-faq)',
	function (e) {
		busyShow();
	}
);

$(document).on("click", "a.btn-external", function (e) {
	e.preventDefault();
	const url = $(this).attr("href");
	if (url) {
		window.open(url, "_blank");
	}
});

//klik pada left menu
$("body").on(
	"click",
	"aside.app-sidebar a:not(.btn-non-ajax):not(.btn-modal):not(.btn-external)",
	function (e) {
		e.preventDefault();

		if ($(this).attr("href") != "#") {
			if (currentAjax) {
				currentAjax.abort();
			}
			// Update active state using exact URL matching (no fuzzy prefix matching)
			updateActiveMenu($(this).attr("href"));
			$(".container-main").html("");
			callAjax({ url: $(this).attr("href"), showBreadcrumb: true, showLoadingBar: true });
		}
	}
);

//klik pada nav item antrian
$("body").on("click", ".navbar-nav .nav-item-antrian a", function (e) {
	e.preventDefault();
	if (currentAjax) {
		currentAjax.abort();
	}
	callAjax({ url: $(this).attr("href"), showBreadcrumb: true, showLoadingBar: false });
});

//klik pada breadcrumb
$("body").on("click", ".app-breadcrumb li a", function (e) {
	e.preventDefault();
	if (currentAjax) {
		currentAjax.abort();
	}
	callAjax({ url: $(this).attr("href"), showBreadcrumb: !$(this).parent().is(":first-child"), showLoadingBar: false });
});

//klik pada card tab panel
// $('body').on('click', '[role="tab"]', function (e) {
// 	let url = $(this).data('url');
// 	let control = $(this).attr('aria-controls');
// 	let html = $('#' + control).html();
// 	if (url !== undefined && html.length == 0) {
// 		$(this)
// 			.closest(".card")
// 			.append(
// 				'<div class="overlay leaves"><i class="fa fa-spinner fa-pulse fa-3x fa-fw margin-bottom" aria-hidden="true"></i></div>'
// 			);

// 		currentAjax = $.ajax({
// 			url: url,
// 			success: function (data) {
// 				$('#' + control).html(data.content ? data.content : '');
// 				$('.overlay').remove();
// 			},
// 			error: function (jqXhr, textStatus, errorThrown) {
// 				$('.overlay').remove();
// 			},
// 		});
// 	}
// });

//klik pada pagination
$("body").on("click", ".pagination-page li a", function (e) {
	e.preventDefault();
	if (currentAjax) {
		currentAjax.abort();
	}
	callAjax({ url: $(this).attr("href") });
});

$("body").on("click", ".btn-ajax", function (e) {
	e.preventDefault();
	if (currentAjax) {
		currentAjax.abort();
	}
	callAjax({ url: $(this).attr("href") });
});

$("body").on("click", ".btn-confirm", function (e) {
	e.preventDefault();
	const href = $(this).attr("href");
	if (confirm($(this).data("confirm-message"))) {
		if (currentAjax) {
			currentAjax.abort();
		}
		// Logout must use full-page navigation (not AJAX POST) because
		// Site::logout destroys the session and issues a redirect; the CSRF
		// token becomes invalid mid-request and postAjax gets a 403.
		if (this.id === "fabLogout" || href.indexOf("site/logout") !== -1) {
			window.location.href = href;
		} else {
			postAjax(
				href,
				$(this).data("json") ? $(this).data("json") : {},
				true,
				true
			);
		}
	}
});

$("body").on("click", ".btn-progress", function (e) {
	e.preventDefault();
	if (confirm($(this).data("confirm-message"))) {
		if (currentAjax) {
			currentAjax.abort();
		}
		startProgress(
			this,
			$(this).attr("href"),
			$(this).data("title") || "Sedang memproses...",
			$(this).data("token") || "",
			$(this).data("redirect") || ""
		);
	} else {
		busyHide();
	}
});

// Lazy loading function to load images when they become visible
function preloadImage(img) {
	if (img && img.dataset.src && !img.src.includes(img.dataset.src)) {
		img.src = img.dataset.src;
	}
}

function startProgress(
	el,
	url,
	title = "Sedang memproses",
	token = "",
	redirect = ""
) {
	// Cancel any previous progress stream still running (e.g. a job stuck
	// in its cooldown countdown loop) so it stops writing into whatever
	// modal/#progressResponses is opened next.
	if (currentProgressController) {
		currentProgressController.abort();
	}
	currentProgressController = new AbortController();
	const thisController = currentProgressController;

	busyShow();

	const data = {
		title: title,
		content: `<div class="progress">
						<div id="dynamicProgressBar" class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">0%</div>
					</div>
					<div id="progressResponses" class="callout callout-success mt-3 d-none"></div>`,
	};

	setModalContent(data);

	if (window.NProgress && NProgress.start) {
		NProgress.start();
	}

	fetch(url, {
		method: "POST",
		headers: token
			? {
				Authorization: "Bearer " + token,
			}
			: {},
		signal: thisController.signal,
	})
		.then((response) => {
			const reader = response.body.getReader();
			const decoder = new TextDecoder();
			let buffer = '';

			function read() {
				reader.read().then(({ done, value }) => {
					// A newer click started its own stream and aborted this
					// one - stop touching the DOM, it no longer belongs to us.
					if (thisController.signal.aborted) {
						return;
					}
					if (done) {
						if (window.NProgress && NProgress.done) {
							NProgress.done();
						}
						return;
					}

					const chunk = decoder.decode(value, {
						stream: true,
					});
					buffer += chunk;
					const responses = buffer.split("\n");
					buffer = responses.pop();

					responses.forEach((response) => {
						if (response.trim()) {
							try {
								const data = JSON.parse(response);
								const progress = Math.max(1, Math.min(data.progress || 0, 100));

								if (window.NProgress && NProgress.set) {
									NProgress.set(progress / 100);
								}

								const progressBar = $("#dynamicProgressBar");
								progressBar.attr("aria-valuenow", progress);
								progressBar.css("width", progress + "%");
								progressBar.text(progress + "%");

								if ($("#progressResponses").hasClass("d-none")) {
									$("#progressResponses").removeClass("d-none");
									$("#progressResponses").addClass("d-flex flex-column");
								}

								if (data.countdown) {
									const $last = $("#progressResponses span:last");
									if ($last.length) {
										$last.text(data.message || data.response || "");
									} else {
										$("#progressResponses").append(
											`<span class="text-break text-danger">${escapeHtml(data.message || data.response || "")}</span>`
										);
									}
								} else {
									$("#progressResponses").append(
										`<span class="text-break ${data.status ? "" : "text-danger"
										}">${data.no ? escapeHtml(data.no) + ". " : ""}${escapeHtml(data.message || data.response || "")
										}</span>`
									);
								}

								if (progress >= 100) {
									progressBar.removeClass("progress-bar-animated");
									$(".modal-title").text("Selesai");
									busyHide();

									if (redirect) {
										callAjax({ url: redirect, showBreadcrumb: true, showLoadingBar: false, isRedirect: true });
									}
								}
							} catch (e) {
								busyHide();
								console.error("Error parsing JSON", e);
								console.log("Raw response:");
								console.log(response);
							}
						}
					});

					read();
				}).catch((error) => {
					if (error && error.name === "AbortError") {
						return;
					}
					console.error("Stream read error:", error);
				});
			}

			read();
		})
		.catch((error) => {
			// Expected when a newer click aborted this stream - not a real error.
			if (error && error.name === "AbortError") {
				return;
			}

			busyHide();

			if (window.NProgress && NProgress.done) {
				NProgress.done();
			}
			console.error("Fetch error:", error);
		});
}

function getAgeAt(birthDateStr, xDateStr) {
	const birthDate = new Date(birthDateStr);
	const xDate = new Date(xDateStr);

	if (isNaN(birthDate) || isNaN(xDate)) return null;

	let years = xDate.getFullYear() - birthDate.getFullYear();
	let months = xDate.getMonth() - birthDate.getMonth();

	// Adjust if the day of the month hasn't passed yet
	if (xDate.getDate() < birthDate.getDate()) {
		months -= 1;
	}

	if (months < 0) {
		years -= 1;
		months += 12;
	}

	return `${years} tahun ${months} bulan`;
}

function formatDate(dateStr, format = "Do MMMM YYYY") {
	if (!dateStr) return "-";
	if (typeof window.moment === "function") {
		return moment(dateStr).format(format);
	} else {
		// Fallback to native JavaScript date formatting if moment is not available
		try {
			const date = new Date(dateStr);
			if (isNaN(date.getTime())) return "-"; // Invalid date

			// Check if format includes time components
			if (format.includes("HH") || format.includes("mm") || format.includes("ss")) {
				// Format with time components
				const day = date.toLocaleString("id-ID", { day: "2-digit" });
				const month = date.toLocaleString("id-ID", { month: "long" }); // Use "long" for full month name like "Januari"
				const year = date.toLocaleString("id-ID", { year: "numeric" });

				// Extract time components
				const hours = date.toLocaleString("id-ID", { hour: "2-digit", hour12: false }).padStart(2, '0');
				const minutes = date.toLocaleString("id-ID", { minute: "2-digit" }).padStart(2, '0');
				const seconds = date.toLocaleString("id-ID", { second: "2-digit" }).padStart(2, '0');

				// Replace format placeholders with actual values
				let formatted = format
					.replace("DD", day)
					.replace("MMMM", month)
					.replace("YYYY", year)
					.replace("HH", hours)
					.replace("mm", minutes)
					.replace("ss", seconds);

				// Handle "Do" (ordinal day) format if present
				if (format.includes("Do")) {
					const dayNum = parseInt(day, 10);
					const ordinalDay = dayNum + (dayNum === 1 ? "st" : dayNum === 2 ? "nd" : dayNum === 3 ? "rd" : "th");
					formatted = formatted.replace("Do", ordinalDay);
				}

				return formatted;
			} else {
				// Format without time components (original behavior)
				const options = { day: "numeric", month: "long", year: "numeric" };
				const locale = "id-ID"; // Indonesian locale
				return date.toLocaleDateString(locale, options);
			}
		} catch (e) {
			return "-";
		}
	}
}

function updateHeaderSubtitle(date, labelSelector, text) {
    var selector = labelSelector || '.card-header .subtitle';
    var $label = $(selector);

    if (!$label.length) {
        var $cardHeader = $('.card-header').first();
        if ($cardHeader.length) {
            $label = $('<small>', { class: 'subtitle' });
            $cardHeader.append($label);
        }
    }

    if (!$label.length) return;

    var formatted = '-';
    if (date) {
        if (typeof window.moment === "function") {
            formatted = moment(date).locale('id').format('dddd, D MMMM YYYY');
        } else {
            try {
                var d = new Date(date);
                if (!isNaN(d.getTime())) {
                    formatted = d.toLocaleDateString('id-ID', { weekday: 'long', day: 'numeric', month: 'long', year: 'numeric' });
                }
            } catch (e) {
                formatted = '-';
            }
        }
    }
    var prefix = text || 'Data sidang tanggal ';
    $label.text(prefix + formatted);
}

function getHariLagi(tanggal) {
	if (typeof window.moment === "function") {
		const diff = moment(tanggal, "YYYY-MM-DD")
			.startOf("day")
			.diff(moment().startOf("day"), "days");
		if (diff === 1) return [diff, "besok"];
		if (diff === 2) return [diff, "lusa"];
		if (diff > 2) return [diff, `${diff} hari lagi`];
		if (diff === 0) return [diff, "hari ini"];
		if (diff === -1) return [diff, "kemarin"];
		return [diff, `${Math.abs(diff)} hari lalu`];
	} else {
		try {
			const today = new Date();
			today.setHours(0, 0, 0, 0);
			const target = new Date(tanggal);
			target.setHours(0, 0, 0, 0);
			const diff = Math.round((target.getTime() - today.getTime()) / 86400000); // 24*60*60*1000
			if (diff === 1) return [diff, "besok"];
			if (diff === 2) return [diff, "lusa"];
			if (diff > 2) return [diff, `${diff} hari lagi`];
			if (diff === 0) return [diff, "hari ini"];
			if (diff === -1) return [diff, "kemarin"];
			return [diff, `${Math.abs(diff)} hari lalu`];
		} catch (e) {
			return [0, ""];
		}
	}
}

function diffDatesExcludeHoliday(start, end, excludes, excludeFriday = false) {
	if (
		typeof window.moment === "function" &&
		start &&
		end &&
		typeof start.add === "function"
	) {
		let diffDays = 0;
		start.add(1, "day"); // Skip the start date to behave like .diff()
		while (start.isSameOrBefore(end)) {
			let dayOfWeek = start.day(); // 0 = Sunday, 6 = Saturday
			const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
			const isFriday = dayOfWeek === 5;
			const isHoliday = excludes.includes(start.format("YYYY-MM-DD"));
			if (!isWeekend && (!excludeFriday || !isFriday) && !isHoliday) {
				diffDays++;
			}
			start.add(1, "day");
		}
		return diffDays;
	} else {
		// Fallback using native Date for inputs as Date or date strings (YYYY-MM-DD)
		let s = start instanceof Date ? new Date(start) : new Date(start);
		let e = end instanceof Date ? new Date(end) : new Date(end);
		if (isNaN(s) || isNaN(e)) return 0;
		s.setHours(0, 0, 0, 0);
		e.setHours(0, 0, 0, 0);
		// Skip the start date to mimic .diff()
		s = new Date(s.getTime() + 86400000);
		let diffDays = 0;
		const fmt = (d) => d.toLocaleDateString("en-CA"); // YYYY-MM-DD
		while (s.getTime() <= e.getTime()) {
			const dayOfWeek = s.getDay(); // 0=Sun..6=Sat
			const isWeekend = dayOfWeek === 0 || dayOfWeek === 6;
			const isFriday = dayOfWeek === 5;
			const isHoliday = excludes.indexOf(fmt(s)) !== -1;
			if (!isWeekend && (!excludeFriday || !isFriday) && !isHoliday) {
				diffDays++;
			}
			s = new Date(s.getTime() + 86400000);
		}
		return diffDays;
	}
}

function getCSSVar(name) {
	return getComputedStyle(document.body).getPropertyValue(name).trim();
}

function toggleParticle(pauseState = null) {
	if (pauseState === null) {
		pauseState = localStorage.getItem('particle-paused');
	}

	if (!window.pJSDom || !window.pJSDom.length) return;

	const pJS = window.pJSDom[0].pJS;

	if (pauseState !== '1') {
		// Resume animation
		pJS.particles.move.enable = true;
		pJS.fn.particlesRefresh();
	} else {
		// Pause animation
		pJS.particles.move.enable = false;
		pJS.fn.particlesRefresh();
	}
}

/* Perf: Resource Timing logger for top asset costs on first paint */
(function () {
	if (!("performance" in window) || !performance.getEntriesByType) return;

	window.addEventListener("load", function () {
		try {
			var entries = performance.getEntriesByType("resource") || [];
			var assets = entries
				.filter(function (e) {
					return (
						["script", "css", "img", "link"].indexOf(e.initiatorType) !== -1
					);
				})
				.map(function (e) {
					return {
						name: e.name,
						initiator: e.initiatorType,
						duration_ms: Math.round(e.duration || 0),
						transfer_bytes: e.transferSize || 0,
						encoded_bytes: e.encodedBodySize || 0,
						decoded_bytes: e.decodedBodySize || 0,
					};
				})
				.sort(function (a, b) {
					return b.duration_ms - a.duration_ms;
				})
				.slice(0, 10);

			if (assets.length) {
				if (console.groupCollapsed)
					console.groupCollapsed("[Perf] Top asset durations (ms)");
				try {
					console.table(assets);
				} catch (err) {
					console.log(assets);
				}
				if (console.groupEnd) console.groupEnd();
			}
		} catch (err) {
			// swallow
		}
	});

	// Add this to your main app.js or similar - only once globally
	$(document).on('select2:open', function (e) {
		const selectId = e.target.id;

		// Temporarily disable Bootstrap modal's focus enforcement
		const modal = $(e.target).closest('.modal');
		if (modal.length) {
			modal.removeAttr('tabindex');
		}

		// Focus the search field
		setTimeout(function () {
			const searchField = document.querySelector('.select2-search__field');
			if (searchField) {
				searchField.focus();
			}
		}, 50);
	});
})();