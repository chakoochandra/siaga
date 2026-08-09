<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Shared "create database if missing" logic, used by both:
 *   - application/config/database.php's bootstrap block (the real,
 *     load-bearing trigger — it's the only place guaranteed to run
 *     before ANY database connection is attempted, including the one
 *     CI's Migration library needs just to check the `migrations` table)
 *   - the 001_ensure_database migration (see that file for why it's a
 *     documentation/safety-net step, not a substitute for the above)
 *
 * Deliberately has no CI dependency beyond the direct-access guard, so it
 * can be require_once'd both from raw config-phase code (before CI has
 * bootstrapped) and from within a loaded migration class.
 *
 * Returns TRUE if the database was created, FALSE if it already existed.
 * Throws on connection failure so callers can decide how to surface it
 * (die() in the config-phase context, an Exception in the migration
 * context — CI_Migration::latest() already expects exceptions/errors to
 * propagate as migration failures).
 */
if (!function_exists('ensure_database_exists')) {
	function ensure_database_exists($host, $user, $pass, $name)
	{
		$mysqli = @new mysqli($host, $user, $pass);
		if ($mysqli->connect_error) {
			throw new Exception('Connection failed: ' . $mysqli->connect_error);
		}

		$result = $mysqli->query(
			"SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = '" . $mysqli->real_escape_string($name) . "'"
		);

		$created = false;
		if ($result && $result->num_rows === 0) {
			$mysqli->query("CREATE DATABASE `{$name}` CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci");
			$created = true;
		}

		$mysqli->close();
		return $created;
	}
}
