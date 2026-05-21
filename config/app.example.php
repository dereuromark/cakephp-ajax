<?php

/**
 * Ajax Example Configuration
 *
 * Merge the keys below into your application's config/app.php (or
 * config/app_local.php) — do not replace the whole file, since this snippet
 * only contains this plugin's configuration. When copying entries that
 * reference imported classes, use fully-qualified class names or move the
 * `use` imports to the top of the target file. Customize the values as needed.
 *
 * The `Ajax` namespace is read by Ajax\Controller\Component\AjaxComponent and merged as
 * defaults at construction time. Component load options still override these.
 */
return [
	'Ajax' => [
		// View class used to render AJAX (JSON) responses. Default: 'Ajax.Ajax'.
		'viewClass' => 'Ajax.Ajax',

		// Automatically detect AJAX requests (using `detectors`) and switch to JSON
		// output. Set false to require explicit enable() calls. Default: true.
		'autoDetect' => true,

		// Request detectors used to identify an AJAX request. Default: ['ajax'].
		'detectors' => ['ajax'],

		// When true, redirects within an AJAX request are not performed but passed back as
		// the `_redirect` URL in the JSON response body. Default: true.
		'resolveRedirect' => true,

		// Flash key handling for AJAX responses. When a non-empty string, it is used
		// verbatim (e.g. 'Flash.flash') to read flash messages into the response. When
		// false, flash messages are skipped entirely. When null, the key is auto-derived
		// from the loaded Flash component's configured key. Default: null.
		'flashKey' => null,

		// Optional callable that fully takes over flash consumption for the response. When
		// a callable is set it is delegated to and returns whatever shape it likes (it
		// takes precedence over `flashKey`). Default: null.
		'flashConsumer' => null,

		// Restrict AJAX handling to specific controller actions. Empty array = all actions
		// are eligible; otherwise only the listed action names are handled. Default: [].
		'actions' => [],
	],
];
