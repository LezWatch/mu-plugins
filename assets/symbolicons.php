<?php
/**
 * Symbolicons
 *
 * Shows the symbolicons settings page, based on the contents on
 * /mu-plugins/symbolicons
 *
 * Version:    2.0
 * Author:     Mika A. Epstein
 * Author URI: https://halfelf.org
 * License:    GPL-2.0+
 *
 */

// if this file is called directly abort
if ( ! defined('WPINC' ) ) {
	die;
}

class LP_SymboliconsSettings {
	
	public $lp_region;
	public $lp_bucket;
	public $lp_prefix;

	/*
	 * Construct
	 *
	 * Actions to happen immediately
	 */
    public function __construct() {
	    
	    $this->lp_region = 'us-east-1';
	    $this->lp_bucket = 'lezpress-icons';
	    $this->lp_prefix = 'symbolicons';
	    
        add_action( 'init', array( &$this, 'init' ) );
        add_action( 'admin_enqueue_scripts', array( &$this, 'admin_enqueue_scripts' ) );

		if ( !defined( 'LP_SYMBOLICONS_PATH' ) ) {
			define( 'LP_SYMBOLICONS_PATH', 'https://' . $this->lp_bucket . '.objects-' . $this->lp_region . '.dream.io/' . $this->lp_prefix . '/' );
		}
    }

	/*
	 * Init
	 *
	 * Actions to happen on WP init
	 * - add settings page
	 * - establish shortcode
	 */
    public function init() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_shortcode( 'symbolicon', array( $this, 'shortcode' ) );
    }

	/*
	 * admin_enqueue_scripts
	 */
    public function admin_enqueue_scripts() {
        wp_register_style( 'symbolicons-admin', '/wp-content/library/assets/css/symbolicons-admin.css', false );
    }

	/*
	 * Shortcode
	 *
	 * Generate the Symbolicon via shortcode
	 *
	 * @param array $atts Attributes for the shortcode
	 *                    - file: Filename
	 *                    - title: Title to use (for A11y)
	 *                    - url: URL to link to (optional)
	 * @return SVG icon of awesomeness
	 */
	function shortcode($atts) {
		$iconsfolder = LP_SYMBOLICONS_PATH;
	    $svg = shortcode_atts( array(
	    	'file'	=> '',
			'title'	=> '',
			'url'	=> '',
	    ), $atts );

		// Default to the square if nothing is there
		$svg = wp_remote_get( $iconsfolder . $svg[ 'file' ] . '.svg' );
		$icon = $svg['body'];
		if ( $svg['response']['code'] == '404' ) {
			$request = wp_remote_get( $iconpath . 'square.svg' );
			$icon = $request['body'];
		}

		$iconpath = '<span role="img" aria-label="' . sanitize_text_field( $svg[ 'title' ] ) . '" title="' . sanitize_text_field( $svg[ 'title' ] ) . '" class="svg-shortcode ' . sanitize_text_field( $svg[ 'title' ] ) . '">';
		if ( !empty( $svg[ 'url' ] ) ) {
			$iconpath .= '<a href=' . esc_url( $svg['url'] ) . '>' . $icon . '</a>';
		} else {
			$iconpath .= $icon;
		}
		$iconpath .= '</span>';

		return $iconpath;
	}

	/**
	 * get_icons function.
	 *
	 * @access public
	 * @return void
	 */
	public function get_icons() {
		
		include_once( dirname( __FILE__ ) . '/aws.phar' );

		$symbolicons = '';

		$s3 = new Aws\S3\S3Client([
		    'version' => 'latest',
		    'region'  => $this->lp_region,
		    'endpoint' => 'https://objects-' . $this->lp_region . '.dream.io',
			'credentials' => [
				'key'    => AWS_ACCESS_KEY_ID,
				'secret' => AWS_SECRET_ACCESS_KEY,
			]
		]);

		$files = $s3->getPaginator( 'ListObjects', [
		    'Bucket' => $this->lp_bucket,
		    'Prefix' => $this->lp_prefix .'/',
		]);

		foreach ( $files as $file ) {
			foreach ( $file['Contents'] as $item ) {
				if ( strpos( $item['Key'], '.svg' ) !== false ) {
					$name = strtolower( substr( $item['Key'] , 0, strrpos( $item['Key'] , ".") ) );
					$name = str_replace( $this->lp_prefix .'/', '', $name );
					$symbolicons .= "$name\r\n";
				}
			}
		}

		$upload_dir = wp_upload_dir();
		$symb_file = $upload_dir['basedir'] . '/symbolicons.txt';
		$open_file  = fopen( $symb_file, 'wa+' );
		$write_file = fputs( $open_file, $symbolicons );
		fclose( $open_file );
	}

	/*
	 * Settings
	 *
	 * Create our settings page
	 */
	public function add_settings_page() {
		$page = add_theme_page( 'Symbolicons', 'Symbolicons', 'edit_posts', 'symbolicons', array( $this, 'settings_page' ) );
	}

	/*
	 * Settings Page Content
	 *
	 * A list of all the Symbolicons and how to use them. Kind of.
	 */
	function settings_page() {
		?>
		<div class="wrap">

		<style>
			span.cmb2-icon {
				width: 80px;
			    display: inline-block;
			    vertical-align: top;
			    margin: 10px;
			    word-wrap: break-word;
			}
			span.cmb2-icon svg {
			    width: 75px;
			    height: 75px;
			}
			span.cmb2-icon svg * {
				fill: #444;
			}
		</style>

		<h2>Symbolicons</h2>

		<?php

		$this->get_icons();
		$imagepath  = LP_SYMBOLICONS_PATH;
		$upload_dir = wp_upload_dir();

		echo '<p>The following are all the symbolicons you have to chose from and their file names. Let this help you be more better with your iconing.</p>';
	
		echo '<p>Usage example: <code>[symbolicon file=rainbow title="Rainbow" url=https://rainbow.com]</code></p>';

		$symbol_list  = fopen( $upload_dir['basedir'] . '/symbolicons.txt', 'r' );
		$symbol_array = array();

		if ( $symbol_list ) {
			delete_option( 'lp_symbolicons' );
			
			while ( ( $line = fgets( $symbol_list ) ) !== false ) {
				$line = trim( str_replace( array( "\r", "\n" ), '', $line ) );
				$symbol_array[ $line ] = $line;
			}
			
			add_option( 'lp_symbolicons', $symbol_array );
		}
		fclose( $symbol_list );
		
		if ( get_option( 'lp_symbolicons' ) !== false ) {
			foreach ( get_option( 'lp_symbolicons' ) as $symbol ) {
				echo '<span role="img" class="cmb2-icon"><img src="' . LP_SYMBOLICONS_PATH . $symbol . '.svg" width="75px">' . $symbol .'</span>';
			}	
		}
		
	}

}
new LP_SymboliconsSettings();