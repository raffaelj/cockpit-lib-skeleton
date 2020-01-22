<?php

if (!class_exists('DotEnv')) {
    include(__DIR__.'/lib/cockpit/lib/DotEnv.php');
}

// load .env file if exists
DotEnv::load(__DIR__);

// check for custom defines
if (file_exists(__DIR__.'/defines.php')) {
    include(__DIR__.'/defines.php');
}

// Collect needed paths
// copied/pasted from core cockpit/bootstrap.php
// author: Artur Heinze, www.agentejo.com, MIT License

$COCKPIT_DIR         = str_replace(DIRECTORY_SEPARATOR, '/', __DIR__);
$COCKPIT_DOCS_ROOT   = str_replace(DIRECTORY_SEPARATOR, '/', isset($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : dirname(__DIR__));

# make sure that $_SERVER['DOCUMENT_ROOT'] is set correctly
if (strpos($COCKPIT_DIR, $COCKPIT_DOCS_ROOT)!==0 && isset($_SERVER['SCRIPT_NAME'])) {
    $COCKPIT_DOCS_ROOT = str_replace(dirname(str_replace(DIRECTORY_SEPARATOR, '/', $_SERVER['SCRIPT_NAME'])), '', $COCKPIT_DIR);
}

$COCKPIT_BASE        = trim(str_replace($COCKPIT_DOCS_ROOT, '', $COCKPIT_DIR), "/");
$COCKPIT_BASE_URL    = strlen($COCKPIT_BASE) ? "/{$COCKPIT_BASE}": $COCKPIT_BASE;

if (!defined('COCKPIT_DOCS_ROOT'))  define('COCKPIT_DOCS_ROOT'  , $COCKPIT_DOCS_ROOT);
if (!defined('COCKPIT_BASE_URL'))   define('COCKPIT_BASE_URL'   , $COCKPIT_BASE_URL);
if (!defined('COCKPIT_BASE_ROUTE')) define('COCKPIT_BASE_ROUTE' , strlen($COCKPIT_BASE) ? "/{$COCKPIT_BASE}": $COCKPIT_BASE);
// end of copy/paste


// bootstrap cockpit
require(__DIR__.'/lib/cockpit/bootstrap.php');

// fix broken assets paths for via App.base() and App.route()
$cockpit->on('app.layout.header', function() {
    echo '<script>
        App.base_url = (App.base_url + "/lib/cockpit/").replace(/\/$/, "");
        App.env_url = "'. $this->pathToUrl(COCKPIT_ENV_ROOT) .'";
        App.base = function(url) {
            return url.indexOf("/addons") === 0 || url.indexOf("/config") === 0 ? this.env_url+url : this.base_url+url;
        };
        App.route = function(url) {
            if (url.indexOf("/assets") === 0 && url.indexOf("/assetsmanager") !== 0) {
                return this.base_route+"/lib/cockpit"+url;
            }
            if (url.indexOf("/addons") === 0 || url.indexOf("/config") === 0) {
                return this.env_url+url;
            }
            return this.base_route+url;
        };
    </script>';
});
