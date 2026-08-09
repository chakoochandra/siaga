<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Standalone CLI-only cooldown guard, kept separate from Cron.php (the web
 * UI for managing crontab entries) to avoid the is_local_ip() gate and
 * library loads in that controller's constructor - those would block this
 * from ever running under cron, where there's no client IP at all.
 *
 * NOTE: as of the in-app cooldown checks added to Generate.php, Notification.php,
 * and Sidang.php, cronjobs_SECRET.php no longer chains this in front of those
 * jobs' commands - the job methods themselves call job_cooldown_check() with
 * the same label, so cron AND UI triggers share one cooldown window. Chaining
 * this guard in front of a command that ALSO checks the same label internally
 * would double-touch the lock and make the real command block itself.
 *
 * This is kept around for any other CLI-only script that has no app-layer
 * entry point of its own to hold the check.
 *
 * Usage (chained with && so the guarded command only runs if this exits 0):
 *   php index.php cron_guard guard <label> <cooldown_seconds> && <command...>
 */
class Cron extends CI_Controller
{
	public function __construct()
	{
		parent::__construct();

		// This is a plumbing endpoint for shell chaining only, never a web page.
		if (!is_cli()) {
			show_404();
		}

		$this->load->helper('app');
	}

	public function guard($label = null, $cooldown_seconds = null)
	{
		if (empty($label) || !is_numeric($cooldown_seconds)) {
			fwrite(STDERR, "cron guard: usage: guard <label> <cooldown_seconds>\n");
			exit(1);
		}

		$result = job_cooldown_check($label, (int) $cooldown_seconds);

		if (!$result['ok']) {
			fwrite(STDERR, "cron_guard: '{$label}' ran " . format_cooldown($result['elapsed']) . " ago, within its " . format_cooldown($cooldown_seconds) . " cooldown (" . format_cooldown($result['remaining']) . " left) - skipping\n");
			exit(1);
		}

		exit(0);
	}
}