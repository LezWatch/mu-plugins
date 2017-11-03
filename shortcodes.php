<?php
/*
Library: Global Shortcodes
Description: Various shortcodes used on the LeZWatch Network
Version: 1.2
Author: Mika Epstein
*/

class LP_Shortcodes{

	/**
	 * Constructor
	 */
	public function __construct() {
		add_action( 'init', array( $this, 'init' ) );
		add_filter( 'widget_text', 'do_shortcode' );
	}

	/**
	 * Init
	 */
	public function init() {
		add_shortcode( 'copyright', array( $this, 'copyright' ) );
		add_shortcode( 'numposts', array( $this, 'numposts' ) );
		add_shortcode( 'author-box', array( $this, 'author_box' ) );
		add_shortcode( 'glossary', array( $this, 'glossary' ) );
		add_shortcode( 'indiegogo', array( $this, 'indiegogo' ) );
	}

	/*
	 * Display Copyright Year
	 *
	 * Usage: [copyright year=(start year) text=(copyright text)]
	 *
	 * Attributes:
	 * 		year = (int) start year. (default: current year)
	 *		text = (text) copyright message. (default: &copy; )
	 *
	 * @since 1.0
	 */
	public function copyright( $atts ) {
		$attributes = shortcode_atts( array(
			'year' => 'auto',
			'text' => '&copy;'
		), $atts );

		$year = ( $attributes[ 'year' ] == '' || ctype_digit( $attributes[ 'year' ] ) == false )? date( 'Y' ) : intval( $attributes[ 'year' ] );
		$text = ( $attributes[ 'text' ] == '' )? '&copy;' : sanitize_text_field( $attributes[ 'text' ] );

		if ( $year == date( 'Y' ) || $year > date( 'Y' ) ) {
			$output = date( 'Y' );
		} elseif ( $year < date( 'Y' ) ) {
			$output = $year . ' - ' . date( 'Y' );
		}

		return $text . ' ' . $output;
	}

	/*
	 * Number of Posts via shortcodes
	 *
	 * Usage: [numposts data="posts" posttype="post type" term="term slug" taxonomy="taxonomy slug"]
	 *
	 * Attributes:
	 *		data     = [posts|taxonomy]
	 * 		posttype = post type
	 * 		term     = term slug
	 *		taxonomy = taxonomy slug
	 *
	 * @since 1.0
	 */
	public function numposts( $atts ) {
		$attr = shortcode_atts( array(
			'data'     => 'posts',
			'posttype' => 'post',
			'term'     => '',
			'taxonomy' => '',
		), $atts );

		if ( $attr['data'] == 'posts' ) {

			// Collect posts
			$posttype = sanitize_text_field( $attr[ 'posttype' ] );

			if ( post_type_exists( $posttype ) !== true ) $posttype = 'post';

			$to_count = wp_count_posts( $posttype );
			$return = $to_count->publish;

		} elseif ( $attr[ 'data' ] == 'taxonomy' ) {

			// Collect Taxonomies
			$the_term     = sanitize_text_field( $attr[ 'term' ] );
			$the_taxonomy = sanitize_text_field( $attr[ 'taxonomy' ] );

			if ( !is_null($the_term) && $the_taxonomy !== false ) {
				$all_taxonomies = ( empty( $the_taxonomy ) )? get_taxonomies() : array( $the_taxonomy ) ;

				foreach ( $all_taxonomies as $taxonomy ) {
					$does_term_exist = term_exists( $the_term, $taxonomy );
					if ( $does_term_exist !== 0 && $does_term_exist !== null ) {
						$the_taxonomy = $taxonomy;
						break;
					} else {
						$the_taxonomy = false;
					}
				}
				$to_count = get_term_by( 'slug', $the_term, $the_taxonomy );
				$return = $to_count->count;
			} else {
				$return = 'n/a';
			}
		} else {
			$return = 'n/a';
		}
		return $return;
	}

	/*
	 * Display Author Box
	 *
	 * Usage: [author-box users=username]
	 *
	 * @since 1.2
	*/
	public function author_box( $atts ) {

		if ( $atts['users'] == '' ) return;

		wp_enqueue_style( 'author-box-shortcode', content_url( 'library/assets/css/author-box.css' ) );

		$users    = explode(',', sanitize_user( $atts['users'] ) );
		$author_box = '<div class="author-box-shortcode">';

		foreach( $users as $user ) {
			$user = username_exists( sanitize_user( $user ) );
			if ( $user ) {
				// Get author gravatar
				$gravatar = get_avatar( get_the_author_meta( 'email', $user ) );

				// Get author's display name 
				// If display name is not available then use nickname as display name
				$display_name = ( get_the_author_meta( 'display_name', $user ) )? get_the_author_meta( 'display_name', $user ) : get_the_author_meta( 'nickname', $user ) ;
				 
				// Get author's biographical information or description
				$user_description = ( get_the_author_meta( 'user_description', $user ) )? get_the_author_meta( 'user_description', $user ) : '';
				 
				// Get author's website URL 
				$user_twitter = get_the_author_meta( 'twitter', $user );
				 
				// Get link to the author archive page
				$numposts   = count_many_users_posts( array( $user ), 'post', true );
				$user_posts = $numposts[$user];

				// Get author Fav Shows
				$all_fav_shows = get_the_author_meta( 'lez_user_favourite_shows', $user );
				if ( $all_fav_shows !== '' ) {
					$show_title = array();
					foreach ( $all_fav_shows as $each_show ) {
						if ( get_post_status ( $each_show ) !== 'publish' ) {
							array_push( $show_title, '<em><span class="disabled-show-link">' . get_the_title( $each_show ) . '</span></em>' );
						} else {
							array_push( $show_title, '<em><a href="' . get_permalink( $each_show ) . '">' . get_the_title( $each_show ) . '</a></em>' );
						}
					}
					$favourites = ( empty( $show_title ) )? '' : implode( ', ', $show_title );
					$fav_title =  _n( 'Show', 'Shows', count( $show_title ) );
				}

				// Build the author box
				$author_details  = '<div class="col-sm-3">' . $gravatar . '</div>';
				$author_details .= '<div class="col-sm-9">';
				$author_details .= '<h4 class="author_name">' . $display_name . '</h4>';
				$author_details .= '<div class="author-bio">' . nl2br( $user_description ) . '</div>';

				$author_details .= '<div class="author-details">';

				// If the author has posts, show a link
				$author_details .= ( $user_posts > 0 )? '<div class="author-archives">' . lwtv_yikes_symbolicons( 'newspaper.svg', 'fa-newspaper-o' ) . '&nbsp;<a href="'. get_author_posts_url( get_the_author_meta( 'ID' , $user ) ) .'">View all articles by ' . $display_name . '</a></div>' : ''; 
				
				// Add Twitter if it's there
				$author_details .= ( ! empty( $user_twitter ) )? '<div class="author-twitter">' . lwtv_yikes_symbolicons( 'twitter.svg', 'fa-twitter' ) . '&nbsp;<a href="https://twitter.com/' . $user_twitter . '" target="_blank" rel="nofollow">@' . $user_twitter . '</a> </div>' : '';
				
				// Add favourite shows if they're there
				$author_details .= ( isset( $favourites ) && !empty( $favourites ) )? '<div class="author-favourites">' . lwtv_yikes_symbolicons( 'tv_flatscreen.svg', 'fa-television' ) . '&nbsp;Favorite ' . $fav_title . ': ' . $favourites . '</div>' : '';
				
				$author_details .= '</div>';
				$author_details .= '</div>';

				$author_box   .= '
					<section class="author-box">' . $author_details . '</section>';
			}
		}

		$author_box .= '</div>';

		return $author_box;
	}

	/*
	 * Outputs Glossary Terms
	 *
	 * Usage: [glossary taxonomy="taxonomy slug"]
	 *
	 * Attributes:
	 *		taxonomy = taxonomy slug
	 *
	 * @since 1.0
	 */
	public function glossary( $atts ) {
		$attr = shortcode_atts( array(
			'taxonomy' => '',
		), $atts );
		
		// Bail Early
		if ( $atts['taxonomy'] == '' ) return;
		
		$the_taxonomy = sanitize_text_field( $attr[ 'taxonomy' ] );
		$the_terms    = get_terms( $the_taxonomy );
		$return       = '<ul class="trope-list list-group">';

		if ( $the_terms && !is_wp_error( $the_terms ) ) {
			// loop over each returned trope
			foreach( $the_terms as $term ) {
				$icon    = lwtv_yikes_symbolicons( get_term_meta( $term->term_id, 'lez_termsmeta_icon', true ) .'.svg', 'fa-square' );
				$return .= '<li class="list-group-item glossary term term-' . $term->slug . '"><a href="' . get_term_link( $term->slug, $the_taxonomy ) .'" rel="glossary term">' . $icon .'</a> <a href="' . get_term_link( $term->slug, $the_taxonomy) .'" rel="glossary term" class="trope-link">' . $term->name .'</a></li>';
			}
		}
			
		$return .= '</ul>';
		
		return $return;
		
	}

	/*
	 * Embed an IndieGoGo Campaign
	 *
	 * Usage: [indiegogo url="https://www.indiegogo.com/projects/riley-parra-season-2-lgbt"]
	 *
	 * Attributes:
	 *		id = slug of thi
	 *
	 * @since 1.0
	 */
	public function indiegogo( $atts ) {
		$attr = shortcode_atts( array(
			'url' => '',
		), $atts );
	
		$url    = esc_url( $attr['url'] );
		$url    = rtrim( $url, "#/");
		$url    = str_replace( 'projects/', 'project/', $url );
		$return =  '<iframe src="' . $url . '/embedded" width="222px" height="445px" frameborder="0" scrolling="no"></iframe>";

		return $return;
	}

}

new LP_Shortcodes();