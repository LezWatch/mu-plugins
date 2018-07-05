<?php
/**
 * Name: Gutenberg Blocks
 * Description: Blocks for Gutenberg
 */

if ( ! defined( 'WPINC' ) ) {
	die;
}

// If register_block_type (aka Gutenblocks) exists, let's do this thing!
if ( function_exists( 'register_block_type' ) ) {
	require_once dirname( __FILE__ ) . '/gutenberg/spoilers.php';
	//require_once dirname( __FILE__ ) . '/gutenberg/author-box.php';
}
