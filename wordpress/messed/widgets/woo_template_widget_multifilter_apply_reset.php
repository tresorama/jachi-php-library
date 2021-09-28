<?php
/**
 * Woo Template Widget - MultiFilter Apply and Reset Buttons
 * It works only with :
 * 	Woo Template Product Filter by Attribute
 * 	Woocommerce Price Filter
 * 
 * @version 1.0.0
 */

defined( 'ABSPATH' ) || exit;

/**
 * Widget layered nav class.
 */
class WOO_Template_Multifilter_Apply_Reset extends WC_Widget {

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->widget_cssclass    = 'woo_template_multifilter_apply_reset';
		$this->widget_description = __( 'Add multiple filters selection button, and reset filters button.', 'woocommerce' );
		$this->widget_id          = 'woo_template_multifilter_apply_reset';
		$this->widget_name        = __( 'Woo Template - Apply and Reset Filters', 'woocommerce' );
		parent::__construct();
	}

	/**
	 * Updates a particular instance of a widget.
	 *
	 * @see WP_Widget->update
	 *
	 * @param array $new_instance New Instance.
	 * @param array $old_instance Old Instance.
	 *
	 * @return array
	 */
	public function update( $new_instance, $old_instance ) {
		$this->init_settings();
		return parent::update( $new_instance, $old_instance );
	}

	/**
	 * Outputs the settings update form.
	 *
	 * @see WP_Widget->form
	 *
	 * @param array $instance Instance.
	 */
	public function form( $instance ) {
		$this->init_settings();
		parent::form( $instance );
	}

	/**
	 * Init settings after post types are registered.
	 */
	public function init_settings() {

		$this->settings = array(
			'apply_text'        => array(
				'type'  => 'text',
				'std'   => __( 'Apply Filters', 'woocommerce' ),
				'label' => __( 'Apply Filters Button Text', 'woocommerce' ),
			),
			'reset_text'        => array(
				'type'  => 'text',
				'std'   => __( 'Reset Filters', 'woocommerce' ),
				'label' => __( 'Reset Filters Button Text', 'woocommerce' ),
			),
			'go_to_shop_text'   => array(
				'type'  => 'text',
				'std'   => __( 'Search the whole store', 'woocommerce' ),
				'label' => __( 'Search the whole store Button Text', 'woocommerce' ),
			),
		);
	}

	/**
	 * Get this page url without query string.
	 *
	 * @param array $instance Array of instance options.
	 * @return string
	 */
	protected function get_current_url_without_query_string() {
    global $wp;
    $_current_url = preg_replace( '%\/page/[0-9]+%', '', home_url( trailingslashit( $wp->request ) ) );
    return $_current_url;
	}


	/**
	 * Output widget.
	 *
	 * @see WP_Widget
	 *
	 * @param array $args Arguments.
	 * @param array $instance Instance.
	 */
	public function widget( $args, $instance ) {
    // show only on SHOP BASE page and PRODUCT TAXONOMY PAGE
		if ( ! is_shop() && ! is_product_taxonomy() ) {
			return;
		}

		$base_link = esc_url($this->get_current_url_without_query_string());
		$go_to_shop_link = esc_url(get_permalink( wc_get_page_id( 'shop' ) ));
		$apply_text = __($instance['apply_text'],'woocommerce');
		$reset_text = __($instance['reset_text'],'woocommerce');
		$go_to_shop_text = __($instance['go_to_shop_text'],'woocommerce');

    //start rendering...
    ob_start();
    //wrapper start
    $this->widget_start( $args, $instance );
    
		//content
		?>
		<button class="button" data-apply-filters data-url="<?php echo $base_link; ?>"><?php echo esc_html($apply_text);?></button>
		<button class="button" data-reset-filters data-url="<?php echo $base_link; ?>"><?php echo esc_html($reset_text);?></button>
		<?php if (!is_shop()) : ?>
		<a class="button" href="<?php echo $go_to_shop_link; ?>"><?php echo esc_html($go_to_shop_text); ?></a>
		<?php endif;

		//wrapper end
		$this->widget_end( $args );

		echo ob_get_clean();
	}

}

?>