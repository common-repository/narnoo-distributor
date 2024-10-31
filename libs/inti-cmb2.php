<?php 

if ( file_exists( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/cmb2/init.php' ) ) {
	require_once NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/cmb2/init.php';
} elseif ( file_exists( NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/CMB2/init.php' ) ) {
	require_once NARNOO_DISTRIBUTOR_PLUGIN_PATH . 'libs/CMB2/init.php';
}

?>