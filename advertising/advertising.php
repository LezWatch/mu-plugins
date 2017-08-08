<?php
/*
Library: Global Advertising
Description: Advertising Code for the LeZWatch Network
Version: 1.0
Author: Mika Epstein
*/

class LP_Advertising {
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
		add_shortcode( 'amazon-ads', array( $this, 'amazon_ads' ) );
		add_shortcode( 'affiliates', array( $this, 'affiliates' ) );
	}

	/*
	 * Display Affiliate Ads
	 *
	 * Usage: [affiliates type={random|genesis|facetwp|liquidweb} size={heightxwidth}]
	 *
	 * Currently all ads are 300x250 for ... reasons
	 *
	 * @since 1.0
	*/

	public function affiliates( $atts ) {

		$attr = shortcode_atts( array(
			'type'  => 'random',
			'size'  => '300x250',
		), $atts );

		$type = sanitize_text_field( $attr['type'] );
		$size = sanitize_text_field( $attr['size'] );

		$valid_sizes  = array( '300x250' );
		$valid_types  = array( 'genesis', 'facetwp', 'dreamhost', 'yikes' );

		if ( $type == 'random' || !in_array( $type, $valid_types) )
			$type = $valid_types [ array_rand( $valid_types ) ];

		if ( !in_array( $size, $valid_sizes) )
			$size = '300x250';

		$genesis = array(
			'300x250' => '<a target="_blank" href="http://shareasale.com/r.cfm?b=255472&amp;u=728549&amp;m=28169&amp;urllink=&amp;afftrack="><img src="https://i.shareasale.com/image/28169/300x250.png" border="0" alt="Genesis Framework for WordPress" /></a>',
		);

		$facetwp = array(
			'300x250' => '<a href="https://facetwp.com/?ref=91&campaign=LezPress"><img src="' . WP_CONTENT_URL . '/library/advertising/images/facetwp-300x250.png"></a>',
		);

		$dreamhost = array(
			'300x250' => '<a href="https://dreamhost.com/dreampress/"><img src="' . WP_CONTENT_URL . '/library/advertising/images/dreamhost-300x250.png"></a>',
		);

		$yikes = array(
			'300x250' => '<a href="https://www.yikesinc.com"><img src="' . WP_CONTENT_URL . '/library/advertising/images/yikes-300x250.png"></a>',
		);

		$advert = '<!-- BEGIN Affiliate Ads --><div class="affiliate-ads ' . sanitize_html_class( $attr['type'] ) . '">';

		switch ( $type ) {
			case 'genesis':
				$advert .= $genesis[ $size ];
				break;
			case 'facetwp':
				$advert .= $facetwp[ $size ];
				break;
			case 'dreamhost':
				$advert .= $dreamhost[ $size ];
				break;
			default:
				$advert .= $yikes[ $size ];
		}

		$advert .= '</div><!-- END Affiliate Ads -->';

		return $advert;
	}

	/*
	 * Display Amazon Ads
	 *
	 * Usage: [amazon-ads type={banner|gift-card} size={468x30}]
	 *
	 * @since 1.0
	*/
	public function amazon_ads( $atts ) {

		$attr = shortcode_atts( array(
			'type'  => 'gift-card',
			'size'  => '468x30',
		), $atts );

		switch ( $attr['size'] ) {
			case '120x600':
				$width  = '120';
				$height = '600';
				$linkid = 'df6784c1de12263d667401e69a4893e7';
				$p      = '11';
			break;
			default:
				$width  = '468';
				$height = '60';
				$linkid = '21f97ff04402ba07089dfcf071a36c6c';
				$p      = '13';
		}

		$gift_card_ads = '
			<div class="alignleft">
				<script type="text/javascript">
				    amzn_assoc_ad_type = "banner";
					amzn_assoc_marketplace = "amazon";
					amzn_assoc_region = "US";
					amzn_assoc_placement = "assoc_banner_placement_default";
					amzn_assoc_campaigns = "gift_certificates";
					amzn_assoc_banner_type = "category";
					amzn_assoc_isresponsive = "true";
					amzn_assoc_banner_id = "1G274HKHXM7QERC7YAG2";
					amzn_assoc_tracking_id = "lezpress-20";
					amzn_assoc_linkid = "d1494a48a27537cf8ecaa3b732b56b2d";
				</script>
				<script src="//z-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&Operation=GetScript&ID=OneJS&WS=1"></script>
		    </div>';

		$banner_ads = '
			<div class="alignleft">
				<script type="text/javascript">
					amzn_assoc_ad_type = "banner";
					amzn_assoc_marketplace = "amazon";
					amzn_assoc_region = "US";
					amzn_assoc_placement = "assoc_banner_placement_default";
					amzn_assoc_banner_type = "ez";
					amzn_assoc_p = "' . $p . '";
					amzn_assoc_width = "' . $width . '";
					amzn_assoc_height = "' . $height . '";
					amzn_assoc_tracking_id = "lezpress-20";
					amzn_assoc_linkid = "' . $linkid . '";;
			    </script>
			    <script src="//z-na.amazon-adsystem.com/widgets/q?ServiceVersion=20070822&Operation=GetScript&ID=OneJS&WS=1"></script>
			</div>
		';

		$native_ads = '<script src="//z-na.amazon-adsystem.com/widgets/onejs?MarketPlace=US&adInstanceId=03c364f2-4dd2-4fdf-85ea-299766f94353"></script>';

		// Show the ad based on what you picked...
		$ads = '<!-- BEGIN Amazon Ads --><div class="amazon-ads ' . sanitize_html_class( $attr['type'] ) . '">';
		switch ( $attr['type'] ) {
			case 'native':
				$ads .= $native_ads;
			case 'banner':
				$ads .= $banner_ads;
			case 'gift-card':
			default:
				$ads .= $gift_card_ads;
		}
		$ads .= '</div><!-- END Amazon Ads -->';

		return $ads;
	}

}

new LP_Advertising();