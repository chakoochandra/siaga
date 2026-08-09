<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once("./application/third_party/dompdf/autoload.inc.php");

use Dompdf\Dompdf;
use Dompdf\Options;

/**
 * Reusable PDF generator wrapper around Dompdf.
 *
 * Usage:
 *   $this->load->library('Pdfgenerator');
 *
 *   // Get raw PDF bytes (e.g. to attach to WA message logic yourself)
 *   $bytes = $this->pdfgenerator->output($html);
 *
 *   // Save straight to a file path, returns the path or false
 *   $path = $this->pdfgenerator->save($html, $path, 'A4', 'landscape');
 *
 *   // Stream directly to the browser (ends the request)
 *   $this->pdfgenerator->stream($html, 'laporan-kinerja');
 */
class Pdfgenerator
{
	/** @var Options */
	protected $options;

	/** @var string */
	protected $cache_dir;

	public function __construct()
	{
		$this->cache_dir = $this->resolve_writable_cache_dir();

		$this->options = new Options();
		$this->options->set('isRemoteEnabled', false); // security: don't fetch remote images/css by default
		// Both of these noticeably slow down rendering. The HTML5 parser exists to
		// tolerate malformed markup, which doesn't apply to HTML we build ourselves;
		// font subsetting shrinks file size at the cost of render time, not worth it
		// for large internal reports.
		$this->options->set('isHtml5ParserEnabled', false);
		$this->options->set('isFontSubsettingEnabled', false);
		$this->options->set('defaultFont', 'DejaVu Sans');
		$this->options->set('chroot', realpath(FCPATH));
		$this->options->set('tempDir', $this->cache_dir);
		$this->options->set('fontDir', $this->cache_dir . DIRECTORY_SEPARATOR . 'fonts');
		$this->options->set('fontCache', $this->cache_dir . DIRECTORY_SEPARATOR . 'fonts');
	}

	/**
	 * Render HTML to a PDF binary string.
	 *
	 * @return string|false PDF bytes, or false on failure
	 */
	public function output($html, $paper = 'A4', $orientation = 'portrait', $time_limit = 0)
	{
		try {
			$dompdf = $this->render($html, $paper, $orientation, $time_limit);
			return $dompdf->output();
		} catch (\Exception $e) {
			log_message('error', 'Pdfgenerator::output failed: ' . $e->getMessage());
			return false;
		}
	}

	/**
	 * Render HTML to a PDF and save it to disk.
	 *
	 * @param int $time_limit Seconds allowed for rendering, 0 = unlimited (PHP-side only;
	 *                         a fronting web server/proxy may still enforce its own timeout
	 *                         if this is called from an HTTP request instead of CLI/queue).
	 * @return string|false Saved file path, or false on failure
	 */
	public function save($html, $path, $paper = 'A4', $orientation = 'portrait', $time_limit = 0)
	{
		$bytes = $this->output($html, $paper, $orientation, $time_limit);
		if ($bytes === false) {
			return false;
		}

		$dir = dirname($path);
		if (!is_dir($dir) && !@mkdir($dir, 0775, true)) {
			log_message('error', 'Pdfgenerator::save could not create directory: ' . $dir);
			return false;
		}

		if (@file_put_contents($path, $bytes) === false) {
			log_message('error', 'Pdfgenerator::save could not write file: ' . $path);
			return false;
		}

		return $path;
	}

	/**
	 * Stream a PDF directly to the browser. Ends script execution.
	 */
	public function stream($html, $filename = 'document', $paper = 'A4', $orientation = 'portrait', $attachment = true, $time_limit = 0)
	{
		try {
			$dompdf = $this->render($html, $paper, $orientation, $time_limit);
		} catch (\Exception $e) {
			log_message('error', 'Pdfgenerator::stream failed: ' . $e->getMessage());
			show_error('Gagal membuat PDF', 500);
			return;
		}

		if (headers_sent($file, $line)) {
			log_message('error', "Pdfgenerator::stream aborted, headers already sent in {$file}:{$line}");
			show_error('Gagal membuat PDF', 500);
			return;
		}

		$dompdf->stream($this->clean_filename($filename) . '.pdf', ['Attachment' => $attachment]);
		exit;
	}

	/**
	 * Delete generated PDF files older than $max_age_seconds from the cache/temp dir.
	 * Call this periodically (e.g. from a cron task) since save() output files
	 * are consumed asynchronously (queued WA sends) and are never auto-deleted.
	 */
	public function cleanup_old_files($dir, $max_age_seconds = 86400)
	{
		if (!is_dir($dir)) {
			return;
		}

		foreach (glob(rtrim($dir, '/\\') . DIRECTORY_SEPARATOR . '*.pdf') as $file) {
			if (is_file($file) && (time() - filemtime($file)) > $max_age_seconds) {
				@unlink($file);
			}
		}
	}

	/**
	 * @deprecated Use output()/save()/stream() instead. Kept for backward compatibility
	 * with existing calls to generate($html, $filename, $stream, $paper, $orientation).
	 */
	public function generate($html, $filename = '', $stream = true, $paper = 'A4', $orientation = 'portrait')
	{
		if ($stream) {
			$this->stream($html, $filename ?: 'document', $paper, $orientation);
			return;
		}

		return $this->output($html, $paper, $orientation);
	}

	protected function render($html, $paper, $orientation, $time_limit = 0)
	{
		// PDF rendering of large tables can be slow/memory-heavy; give it headroom
		// without permanently raising limits for the whole request. $time_limit = 0
		// means unlimited (matches PHP's own convention), since large yearly reports
		// can legitimately take a while and an arbitrary cap just trades one failure
		// mode for another. Pass an explicit limit if this runs inside an HTTP
		// request and you'd rather fail fast than tie up a web worker.
		$previous_time_limit = ini_get('max_execution_time');
		$previous_memory_limit = ini_get('memory_limit');
		@set_time_limit($time_limit);
		@ini_set('memory_limit', '1024M');

		try {
			$dompdf = new Dompdf($this->options);
			$dompdf->loadHtml($html, 'UTF-8');
			$dompdf->setPaper($paper, $orientation);
			$dompdf->render();
		} catch (\Exception $e) {
			@set_time_limit((int) $previous_time_limit);
			@ini_set('memory_limit', $previous_memory_limit);
			throw $e;
		}

		@set_time_limit((int) $previous_time_limit);
		@ini_set('memory_limit', $previous_memory_limit);

		return $dompdf;
	}

	protected function resolve_writable_cache_dir()
	{
		$candidates = [
			rtrim(sys_get_temp_dir(), '/\\') . DIRECTORY_SEPARATOR . 'dompdf_cache',
			(defined('FCPATH') ? FCPATH : '.') . 'writable' . DIRECTORY_SEPARATOR . 'dompdf_cache',
		];

		foreach ($candidates as $dir) {
			if (!is_dir($dir)) {
				@mkdir($dir, 0775, true);
			}
			if (is_dir($dir) && is_writable($dir)) {
				return $dir;
			}
		}

		// last resort, may still fail but avoids a hard crash in the constructor
		return sys_get_temp_dir();
	}

	protected function clean_filename($filename)
	{
		$filename = preg_replace('/[^A-Za-z0-9_\-]+/', '_', $filename);
		return $filename !== '' ? $filename : 'document';
	}
}
