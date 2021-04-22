<?php
/**
 * Plugin Name:       Conexus Solutions Metals
 * Plugin URI:        https://github.com/conexussolutions/conexpro-metal
 * Description:       Simple plugin to pull metal prices in USD from https://tradingeconomics.com/.
 * Author:            Conexus Solutions Ltd.
 * Author URI:        https://conexussolutions.ca
 * Version:           1.0.1
 * License:           GPL-3.0+
 * License URI:       http://www.gnu.org/licenses/gpl-3.0.txt
 * Text Domain:       conexpro-metal
*/

if ( ! defined( 'ABSPATH' ) ) {
	die;
}

define( "CNXSMETALSPATH", dirname( __FILE__ ) );
include_once( CNXSMETALSPATH.'/autoload.php' );



