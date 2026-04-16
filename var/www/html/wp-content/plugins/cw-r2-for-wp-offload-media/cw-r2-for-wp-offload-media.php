<?php

/**
 * Plugin Name: Cloudflare R2 for WP Offload Media Lite
 * Description: Adds Cloudflare R2 as a storage provider for WP Offload Media Lite.
 * Version: 1.0.0
 * Author: Nelll
 * Author URI: https://cloudwp.pro
 * Requires Plugins: amazon-s3-and-cloudfront-pro
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 */

if (!defined("ABSPATH")) exit;

if (defined("AS3CF_PROVIDER") && "r2" === AS3CF_PROVIDER && !defined("AS3CF_REGION")) {
	define("AS3CF_REGION", "auto");
}

register_activation_hook(__FILE__, "cw_r2_deploy_icons");
add_action("admin_init", "cw_r2_deploy_icons");

function cw_r2_deploy_icons()
{
	$target = WP_PLUGIN_DIR . "/amazon-s3-and-cloudfront-pro/assets/img/icon/provider/storage/";
	if (!is_dir($target)) return;

	$source = plugin_dir_path(__FILE__) . "assets/img/icon/provider/storage/";
	foreach (array("r2.svg", "r2-round.svg", "r2-link.svg") as $file) {
		if (!file_exists($target . $file) && file_exists($source . $file)) {
			copy($source . $file, $target . $file);
		}
	}
}

add_filter("as3cf_storage_provider_classes", function ($providers) {
	if (!class_exists("DeliciousBrains\\WP_Offload_Media\\Providers\\Storage\\AWS_Provider")) {
		return $providers;
	}

	if (!class_exists("CW_R2_Provider")) {
		class CW_R2_Provider extends \DeliciousBrains\WP_Offload_Media\Providers\Storage\AWS_Provider
		{

			protected static $provider_name = "Cloudflare";
			protected static $provider_short_name = "Cloudflare";
			protected static $provider_key_name = "r2";
			protected static $service_name = "R2";
			protected static $service_short_name = "R2";
			protected static $service_key_name = "r2";
			protected static $provider_service_name = "Cloudflare R2";
			protected static $provider_service_quick_start_slug = "";

			protected static $access_key_id_constants = array("AS3CF_R2_ACCESS_KEY_ID");
			protected static $secret_access_key_constants = array("AS3CF_R2_SECRET_ACCESS_KEY");
			protected static $use_server_roles_constants = array();

			protected static $block_public_access_supported = false;
			protected static $object_ownership_supported = false;

			protected static $regions = array("auto" => "Automatic");
			protected static $default_region = "auto";
			protected static $region_required = false;

			protected function init_client_args(array $args)
			{
				if (defined("AS3CF_R2_ENDPOINT")) {
					$args["endpoint"] = AS3CF_R2_ENDPOINT;
				}
				$args["region"] = "auto";
				$args["use_path_style_endpoint"] = true;
				return $args;
			}

			protected function init_service_client_args(array $args)
			{
				$args["use_path_style_endpoint"] = true;
				return $args;
			}

			protected function init_service_client(array $args = array())
			{
				$args["region"] = "auto";
				return parent::init_service_client($args);
			}

			public function get_bucket_location(array $args)
			{
				return "auto";
			}

			protected function url_prefix($region = "", $expires = null)
			{
				return "";
			}

			public function block_public_access(string $bucket, bool $block)
			{
				// no-op
			}

			public function enforce_object_ownership(string $bucket, bool $enforce)
			{
				// no-op
			}
		}
	}

	$providers["r2"] = "CW_R2_Provider";
	return $providers;
});

add_filter("as3cf_delivery_provider_classes", function ($providers) {
	if (!class_exists("CW_R2_Delivery_CloudFront") && class_exists("DeliciousBrains\\WP_Offload_Media\\Providers\\Delivery\\AWS_CloudFront")) {
		class CW_R2_Delivery_CloudFront extends \DeliciousBrains\WP_Offload_Media\Providers\Delivery\AWS_CloudFront
		{
			protected static $supported_storage_providers = array("aws", "r2");
		}
	}
	if (!class_exists("CW_R2_Delivery_Cloudflare") && class_exists("DeliciousBrains\\WP_Offload_Media\\Providers\\Delivery\\Cloudflare")) {
		class CW_R2_Delivery_Cloudflare extends \DeliciousBrains\WP_Offload_Media\Providers\Delivery\Cloudflare
		{
			protected static $supported_storage_providers = array("aws", "r2");
		}
	}
	if (!class_exists("CW_R2_Delivery_StackPath") && class_exists("DeliciousBrains\\WP_Offload_Media\\Providers\\Delivery\\StackPath")) {
		class CW_R2_Delivery_StackPath extends \DeliciousBrains\WP_Offload_Media\Providers\Delivery\StackPath
		{
			protected static $supported_storage_providers = array("aws", "r2");
		}
	}

	$map = array(
		"aws"        => "CW_R2_Delivery_CloudFront",
		"cloudflare" => "CW_R2_Delivery_Cloudflare",
		"stackpath"  => "CW_R2_Delivery_StackPath",
	);
	foreach ($map as $key => $wrapper_class) {
		if (isset($providers[$key]) && class_exists($wrapper_class)) {
			$providers[$key] = $wrapper_class;
		}
	}
	return $providers;
});

add_action("admin_menu", function () {
	add_management_page(
		"R2 Migration",
		"R2 Migration",
		"manage_options",
		"cw-r2-migration",
		"cw_r2_migration_page"
	);
});

function cw_r2_migration_page()
{
	global $wpdb;
	$table = $wpdb->prefix . "as3cf_items";

	$total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
	$remaining = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE provider != %s OR region != %s",
			"r2",
			"auto"
		)
	);
	$done = $total - $remaining;

	// prettier-ignore - preserve HTML structure, do not remove
?>
	<div class="wrap">
		<h1>Migrate to Cloudflare R2</h1>

		<div style="background:#fff;border:1px solid #dcdcde;border-radius:8px;padding:24px 28px;margin-top:16px;max-width:680px">
			<p style="margin:0 0 20px;color:#50575e">Update <code>provider</code> to <code>r2</code> and <code>region</code> to <code>auto</code> in <code>wp_as3cf_items</code>.</p>

			<div id="r2m-stats" style="margin-bottom:20px;font-size:14px;color:#1d2327">
				<span>Total: <strong id="r2m-total"><?php echo esc_html($total); ?></strong></span>
				&nbsp;&nbsp;|&nbsp;&nbsp;
				<span>Done: <strong id="r2m-done"><?php echo esc_html($done); ?></strong></span>
				&nbsp;&nbsp;|&nbsp;&nbsp;
				<span>Remaining: <strong id="r2m-remaining"><?php echo esc_html($remaining); ?></strong></span>
			</div>

			<div style="width:100%;background:#f0f0f1;border-radius:4px;overflow:hidden;height:28px;position:relative">
				<div id="r2m-bar" style="width:<?php echo $total ? round($done / $total * 100, 1) : 0; ?>%;background:#2271b1;height:100%;transition:width .3s"></div>
				<span id="r2m-pct" style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;font-weight:600;color:#fff;text-shadow:0 1px 2px rgba(0,0,0,.4)"><?php echo $total ? round($done / $total * 100, 1) : 0; ?>%</span>
			</div>

			<div style="margin-top:20px;display:flex;align-items:center;gap:8px">
				<button id="r2m-start" class="button button-primary" <?php echo $remaining === 0 ? 'disabled' : ''; ?>>
					<?php echo $remaining === 0 ? 'Completed' : 'Start Migration'; ?>
				</button>
				<button id="r2m-recheck" class="button">Re-check</button>
				<button id="r2m-force" class="button" style="color:#d63638">Force Re-run All</button>
			</div>

			<div id="r2m-log" style="margin-top:12px;color:#50575e;font-size:13px"></div>
		</div>
	</div>

	<script>
		(function() {
			var btn = document.getElementById('r2m-start');
			var recheckBtn = document.getElementById('r2m-recheck');
			var bar = document.getElementById('r2m-bar');
			var pct = document.getElementById('r2m-pct');
			var elDone = document.getElementById('r2m-done');
			var elRemaining = document.getElementById('r2m-remaining');
			var log = document.getElementById('r2m-log');
			var total = <?php echo (int) $total; ?>;
			var running = false;

			btn.addEventListener('click', function() {
				if (running) return;
				running = true;
				btn.disabled = true;
				btn.textContent = 'Migrating…';
				log.textContent = '';
				runBatch();
			});

			document.getElementById('r2m-force').addEventListener('click', function() {
				if (running) return;
				if (!confirm('This will revert all items to the original provider/region, then re-migrate. Continue?')) return;
				running = true;
				btn.disabled = true;
				btn.textContent = 'Migrating…';
				bar.style.width = '0%';
				pct.textContent = '0%';
				elDone.textContent = '0';
				elRemaining.textContent = total;
				log.textContent = 'Resetting all items…';
				var fd = new FormData();
				fd.append('action', 'cw_r2_migrate_force_reset');
				fd.append('_wpnonce', '<?php echo wp_create_nonce("cw_r2_migrate"); ?>');
				fetch(ajaxurl, {
						method: 'POST',
						body: fd,
						credentials: 'same-origin'
					})
					.then(function(r) {
						return r.json()
					})
					.then(function(r) {
						if (!r.success) {
							log.textContent = 'Error: ' + (r.data || 'unknown');
							running = false;
							btn.disabled = false;
							btn.textContent = 'Start Migration';
							return
						}
						total = r.data.total;
						document.getElementById('r2m-total').textContent = r.data.total;
						elDone.textContent = '0';
						elRemaining.textContent = r.data.total;
						log.textContent = 'Reset done. Starting migration…';
						runBatch();
					})
					.catch(function(e) {
						log.textContent = 'Request failed: ' + e.message;
						running = false;
						btn.disabled = false;
						btn.textContent = 'Start Migration';
					});
			});

			recheckBtn.addEventListener('click', function() {
				if (running) return;
				recheckBtn.disabled = true;
				recheckBtn.textContent = 'Checking…';
				var fd = new FormData();
				fd.append('action', 'cw_r2_migrate_recheck');
				fd.append('_wpnonce', '<?php echo wp_create_nonce("cw_r2_migrate"); ?>');
				fetch(ajaxurl, {
						method: 'POST',
						body: fd,
						credentials: 'same-origin'
					})
					.then(function(r) {
						return r.json()
					})
					.then(function(r) {
						recheckBtn.disabled = false;
						recheckBtn.textContent = 'Re-check';
						if (!r.success) {
							log.textContent = 'Error: ' + (r.data || 'unknown');
							return
						}
						var d = r.data;
						total = d.total;
						document.getElementById('r2m-total').textContent = d.total;
						elDone.textContent = d.done;
						elRemaining.textContent = d.remaining;
						var p = d.total ? (d.done / d.total * 100).toFixed(1) : 100;
						bar.style.width = p + '%';
						pct.textContent = p + '%';
						if (d.remaining > 0) {
							btn.disabled = false;
							btn.textContent = 'Start Migration';
						} else {
							btn.disabled = true;
							btn.textContent = 'Completed';
						}
						log.textContent = 'Status refreshed';
					})
					.catch(function(e) {
						recheckBtn.disabled = false;
						recheckBtn.textContent = 'Re-check';
						log.textContent = 'Request failed: ' + e.message;
					});
			});

			function runBatch() {
				var fd = new FormData();
				fd.append('action', 'cw_r2_migrate_batch');
				fd.append('_wpnonce', '<?php echo wp_create_nonce("cw_r2_migrate"); ?>');
				fetch(ajaxurl, {
						method: 'POST',
						body: fd,
						credentials: 'same-origin'
					})
					.then(function(r) {
						return r.json()
					})
					.then(function(r) {
						if (!r.success) {
							log.textContent = 'Error: ' + (r.data || 'unknown');
							btn.textContent = 'Retry';
							btn.disabled = false;
							running = false;
							return;
						}
						var d = r.data;
						elDone.textContent = d.done;
						elRemaining.textContent = d.remaining;
						var p = total ? (d.done / total * 100).toFixed(1) : 100;
						bar.style.width = p + '%';
						pct.textContent = p + '%';
						log.textContent = 'Batch updated: ' + d.updated + ' rows';

						if (d.remaining > 0) {
							runBatch();
						} else {
							btn.textContent = 'Completed';
							running = false;
							log.textContent = 'All done';
						}
					})
					.catch(function(e) {
						log.textContent = 'Request failed: ' + e.message;
						btn.textContent = 'Retry';
						btn.disabled = false;
						running = false;
					});
			}
		})();
	</script>
<?php
}

add_action("wp_ajax_cw_r2_migrate_batch", function () {
	check_ajax_referer("cw_r2_migrate");
	if (!current_user_can("manage_options")) {
		wp_send_json_error("Unauthorized");
		return;
	}

	global $wpdb;
	$table = $wpdb->prefix . "as3cf_items";
	$batch = 500;

	if (!get_option("cw_r2_migration_source")) {
		$src = $wpdb->get_row(
			$wpdb->prepare("SELECT provider, region FROM {$table} WHERE provider != %s LIMIT 1", "r2"),
			ARRAY_A
		);
		if ($src) {
			update_option("cw_r2_migration_source", $src, false);
		}
	}

	$updated = (int) $wpdb->query(
		$wpdb->prepare(
			"UPDATE {$table} SET provider = %s, region = %s WHERE provider != %s OR region != %s LIMIT %d",
			"r2",
			"auto",
			"r2",
			"auto",
			$batch
		)
	);

	$total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
	$remaining = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE provider != %s OR region != %s",
			"r2",
			"auto"
		)
	);

	wp_send_json_success(array(
		"updated" => $updated,
		"done" => $total - $remaining,
		"remaining" => $remaining,
	));
});

add_action("wp_ajax_cw_r2_migrate_force_reset", function () {
	check_ajax_referer("cw_r2_migrate");
	if (!current_user_can("manage_options")) {
		wp_send_json_error("Unauthorized");
		return;
	}

	$source = get_option("cw_r2_migration_source");
	if (empty($source["provider"]) || empty($source["region"])) {
		wp_send_json_error("No migration source recorded. Run a normal migration first.");
		return;
	}

	$src_provider = $source["provider"];
	$src_region = $source["region"];

	global $wpdb;
	$table = $wpdb->prefix . "as3cf_items";

	$wpdb->query(
		$wpdb->prepare(
			"UPDATE {$table} SET provider = %s, region = %s WHERE provider = %s AND region = %s",
			$src_provider,
			$src_region,
			"r2",
			"auto"
		)
	);

	$total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");

	wp_send_json_success(array(
		"total" => $total,
	));
});

add_action("wp_ajax_cw_r2_migrate_recheck", function () {
	check_ajax_referer("cw_r2_migrate");
	if (!current_user_can("manage_options")) {
		wp_send_json_error("Unauthorized");
		return;
	}

	global $wpdb;
	$table = $wpdb->prefix . "as3cf_items";

	$total = (int) $wpdb->get_var("SELECT COUNT(*) FROM {$table}");
	$remaining = (int) $wpdb->get_var(
		$wpdb->prepare(
			"SELECT COUNT(*) FROM {$table} WHERE provider != %s OR region != %s",
			"r2",
			"auto"
		)
	);

	wp_send_json_success(array(
		"total" => $total,
		"done" => $total - $remaining,
		"remaining" => $remaining,
	));
});
