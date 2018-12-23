<?php
/*
 * Jetpack Customizations
 * @ver 1.0
 * @package library
*/

if ( ! defined( 'WPINC' ) ) {
	die;
}

// Bail early if Jetpack isn't active.
if ( ! class_exists( 'Jetpack' ) ) {
	return;
}

/**
 * LP_Jetpack_Feedback class.
 * Functions used by Jetpack
 */
class LP_Jetpack_Feedback {

	/**
	 * Constructor
	 * @since 1.0
	 */
	public function __construct() {
		add_action( 'dashboard_glance_items', array( $this, 'dashboard_glance' ) );
		add_action( 'admin_head', array( $this, 'dashboard_glance_css' ) );
		add_action( 'init', array( $this, 'custom_post_statuses' ), 0 );
		add_filter( 'post_row_actions', array( $this, 'add_posts_rows' ), 10, 2 );
		add_action( 'plugins_loaded', array( $this, 'mark_as_answered' ) );
		add_filter( 'display_post_states', array( $this, 'display_post_states' ) );
		add_action( 'admin_footer-post.php', array( $this, 'add_archived_to_post_status_list' ) );
		add_action( 'admin_footer-edit.php', array( $this, 'add_archived_to_bulk_edit' ) );
		add_filter( 'jetpack_contact_form_is_spam', array( $this, 'jetpack_spammers' ), 11, 2 );
		add_filter( 'jetpack_contact_form_is_spam', array( $this, 'way2enjoy_harassment' ), 11, 2 );
	}

	/**
	 * [jetpack_spammers description]
	 * @param  boolean $is_spam   Default spam decision
	 * @param  array   $form      The form data
	 * @return boolean $is_spam   If the person is spam
	 */
	public function jetpack_spammers( $is_spam, $form ) {
		// Defaults
		$emaillist = array();
		$iplist    = array();
		$blacklist = explode( "\n", get_option( 'blacklist_keys' ) );

		// Check the list for valid emails. Add them to spam if found.
		// Also check for IP address and add them
		foreach ( $blacklist as $spammer ) {
			if ( is_email( $spammer ) ) {
				$emaillist[] = $spammer;
			} elseif ( filter_var( $spammer, FILTER_VALIDATE_IP ) ) {
				$iplist[] = $spammer;
			}
		}

		// Get the email from the form:
		$this_email = $form['comment_author_email'];
		// Get the IP address:
		$this_ip = $form['comment_author_IP'];

		// If the email or IP is on the list, spam it.
		if ( in_array( $this_email, $emaillist ) || in_array( $this_ip, $iplist ) ) {
			$is_spam = true;
		}

		// Return the results
		return $is_spam;

	}

	/**
	 * [way2enjoy_harassment description]
	 * @param  boolean $is_spam   Default spam decision
	 * @param  array   $form      The form data
	 * @return boolean $is_spam   If the person is spam
	 */
	public function way2enjoy_harassment( $is_spam, $form ) {

		// This is a person who is serial harassing.
		$bad_names = array( 'sonam', 'Ravi', 'ravi' );
		$bad_email = array( 'sonam', 'rstbiet' );
		$bad_text  = array( 'Mika madam', 'mika madam', 'Tracy madam', 'tracy madam', 'Respected Madam', 'way2enjoy' );

		// Check if the comment author is on the bad names list
		if ( in_array( $form['comment_author'], $bad_names ) ) {
			$is_spam = true;
		}

		// Check if the email is one of the bad ones
		foreach ( $bad_email as $an_email ) {
			if ( strpos( $form['comment_author_email'], $an_email ) ) {
				$is_spam = true;
			}
		}

		// Check if the text contains key phrases
		foreach ( $bad_text as $a_text ) {
			if ( strpos( $form['comment_content'], $a_text ) ) {
				$is_spam = true;
			}
		}

		// Return the results
		return $is_spam;
	}

	/**
	 * Add custom post status for Answered
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	public function custom_post_statuses() {
		register_post_status( 'answered', array(
			'label'                     => 'Answered',
			'public'                    => false,
			'exclude_from_search'       => true,
			'show_in_admin_all_list'    => true,
			'show_in_admin_status_list' => true,
			// translators: %s is the number of answered feedbacks
			'label_count'               => _n_noop( 'Answered <span class="count">(%s)</span>', 'Answered <span class="count">(%s)</span>' ),
		) );
	}

	/**
	 * Add URL for replying to feedback.
	 *
	 * @access public
	 * @param mixed $actions
	 * @param mixed $post
	 * @return void
	 * @since 1.0
	 */
	public function add_posts_rows( $actions, $post ) {
		// Only for Feedback
		if ( 'feedback' === $post->post_type ) {
			$url = add_query_arg( 'answered_post_status-post_id', $post->ID );
			$url = add_query_arg( 'answered_post_status-nonce', wp_create_nonce( 'answered_post_status-post_id' . $post->ID ), $url );

			// Edit URLs based on status
			if ( 'answered' !== $post->post_status ) {
				$url                      = add_query_arg( 'answered_post_status-status', 'answered', $url );
				$actions['answered_link'] = '<a href="' . $url . '" title="Mark This Post as Answered">Answered</a>';
			} elseif ( 'answered' === $post->post_status ) {
				$url                 = add_query_arg( 'answered_post_status-status', 'publish', $url );
				$actions['answered'] = '<a class="untrash" href="' . $url . '" title="Mark This Post as Unanswered">Unanswered</a>';
				unset( $actions['edit'] );
				unset( $actions['trash'] );
			}
		}
		return $actions;
	}

	/**
	 * Add Answered to post statues
	 *
	 * @access public
	 * @param mixed $states
	 * @return void
	 * @since 1.0
	 */
	public function display_post_states( $states ) {
		global $post;

		if ( 'feedback' === $post->post_type ) {
			$arg = get_query_var( 'post_status' );
			if ( 'answered' !== $arg ) {
				if ( 'answered' === $post->post_status ) {
					return array( 'Answered' );
				}
			}
		}

		return $states;
	}

	/**
	 * Process marking as answered
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	public function mark_as_answered() {

		// If contact forms aren't active, we'll just pass
		if ( Jetpack::is_module_active( 'contact-form' ) ) {

			// Check Nonce
			if ( isset( $_GET['answered_post_status-nonce'] ) && wp_verify_nonce( $_GET['answered_post_status-nonce'], 'answered_post_status-post_id' . $_GET['answered_post_status-post_id'] ) ) {
				// Check Current user Can and then process
				if ( current_user_can( 'publish_posts' ) && isset( $_GET['answered_post_status-status'] ) ) {
					$GLOBALS['wp_rewrite'] = new wp_rewrite(); // WPSC: override ok.

					$status  = $_GET['answered_post_status-status'];
					$post_id = (int) $_GET['answered_post_status-post_id'];

					// If it's not a valid status, we have a problem
					if ( ! in_array( $status, array( 'answered', 'publish' ), true ) ) {
						die( 'ERROR!!!' );
					}

					$answered = array(
						'ID'          => $post_id,
						'post_status' => $status,
					);
					wp_update_post( $answered );
				}
			}
		}
	}


	/**
	 * add_archived_to_post_status_list function.
	 *
	 * @access public
	 * @return void
	 * @since 1.0
	 */
	public function add_archived_to_post_status_list() {
		global $post;
		$complete = '';
		$label    = '';

		// Bail if not feedback
		if ( 'feedback' === $post->post_type ) {
			return;
		}

		if ( 'answered' === $post->post_status ) {
			echo '
				<script>
					jQuery(document).ready(function($){
						$("#post-status-display" ).text("Answered");
						$("select#post_status").append("<option value=\"answered\" selected=\"selected\">Answered</option>");
						$(".misc-pub-post-status label").append("<span id=\"post-status-display\">Answered</span>");
					});
				</script>
			';
		} elseif ( 'publish' === $post->post_status ) {
			echo '
				<script>
					jQuery(document).ready(function($){
						$("select#post_status").append("<option value=\"answered\" >Answered</option>");
					});
				</script>
			';
		}
	}

	public function add_archived_to_bulk_edit() {
		global $post;
		if ( ! isset( $post->post_type ) || 'feedback' !== $post->post_type ) {
			return;
		}
		?>
			<script>
			jQuery(document).ready(function($){
				$(".inline-edit-status select ").append("<option value=\"answered\">Answered</option>");
				$(".bulkactions select ").append("<option value=\"answered\">Mark As Answered</option>");
			});
			</script>
		<?php
	}

	/*
	 * Show Feedback in "Right Now"
	 *
	 * @since 1.0
	 */
	public function dashboard_glance() {
		if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'contact-form' ) ) {
			foreach ( array( 'feedback' ) as $post_type ) {
				$num_posts   = wp_count_posts( $post_type );
				$count_posts = ( isset( $num_posts->publish ) ) ? $num_posts->publish : '0';
				if ( 0 !== $count_posts ) {
					if ( 'feedback' === $post_type ) {
						// translators: %s is the number of messages
						$text = _n( '%s Message', '%s Messages', $count_posts );
					}
					$text = sprintf( $text, number_format_i18n( $count_posts ) );
					printf( '<li class="%1$s-count"><a href="edit.php?post_type=%1$s">%2$s</a></li>', esc_attr( $post_type ), wp_kses_post( $text ) );
				}
			}
		}
	}

	/*
	 * Custom Icon for Feedback in "Right Now"
	 *
	 * @since 1.0
	 */
	public function dashboard_glance_css() {
		if ( class_exists( 'Jetpack' ) && Jetpack::is_module_active( 'contact-form' ) ) {
			?>
			<style type='text/css'>
				#adminmenu #menu-posts-feedback div.wp-menu-image:before, #dashboard_right_now li.feedback-count a:before {
					content: '\f466';
					margin-left: -1px;
				}
			</style>
			<?php
		}
	}

}

new LP_Jetpack_Feedback();
