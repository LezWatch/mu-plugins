<?php
/**
 * Name: Gutenberg Blocks
 * Description: Blocks for Gutenberg
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

class LWTV_Library_Gutenblocks {

	public function __construct() {
		add_action( 'init', array( $this, 'spoilers' ) );
		add_action( 'init', array( $this, 'listicles' ) );
	}

	public function spoilers() {
		$dir = dirname( __FILE__ );

		$index_js = 'spoilers/index.js';
		wp_register_script(
			'spoilers-block-editor',
			content_url( 'library/gutenberg/' . $index_js ),
			array( 'wp-editor', 'wp-blocks', 'wp-i18n', 'wp-element' ),
			filemtime( "$dir/$index_js" ),
			false
		);

		$editor_css = 'spoilers/editor.css';
		wp_register_style(
			'spoilers-block-editor',
			content_url( 'library/gutenberg/' . $editor_css ),
			array( 'wp-editor', 'wp-blocks' ),
			filemtime( "$dir/$editor_css" )
		);

		$style_css = 'spoilers/style.css';
		wp_register_style(
			'spoilers-block',
			content_url( 'library/gutenberg/' . $style_css ),
			array( 'wp-editor', 'wp-blocks' ),
			filemtime( "$dir/$style_css" )
		);

		register_block_type( 'lez-library/spoilers', array(
			'editor_script' => 'spoilers-block-editor',
			'editor_style'  => 'spoilers-block-editor',
			'style'         => 'spoilers-block',
		) );
	}

	public function listicles() {
		$dir = dirname( __FILE__ );

		$index_js = 'listicles/index.js';
		wp_register_script(
			'listicles-block-editor',
			content_url( 'library/gutenberg/' . $index_js ),
			array( 'wp-editor', 'wp-blocks', 'wp-i18n', 'wp-element' ),
			filemtime( "$dir/$index_js" ),
			false
		);
		wp_enqueue_script( 'jquery-ui-accordion' );

		$editor_css = 'listicles/editor.css';
		wp_register_style(
			'listicles-block-editor',
			content_url( 'library/gutenberg/' . $editor_css ),
			array( 'wp-editor', 'wp-blocks' ),
			filemtime( "$dir/$editor_css" )
		);

		register_block_type( 'lez-library/listicles', array(
			'editor_script' => 'listicles-block-editor',
			'editor_style'  => 'listicles-block-editor',
		) );
	}

}

new LWTV_Library_Gutenblocks();