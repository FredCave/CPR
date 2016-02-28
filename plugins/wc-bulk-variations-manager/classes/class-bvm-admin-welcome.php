<?php
/**
 * Welcome Page Class
*/

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * BVM_Admin_Welcome class
 */
class BVM_Admin_Welcome {

	/**
	 * Hook in tabs.
	 */
	public function __construct() {
		add_action( 'admin_menu', array( $this, 'admin_menus') );
		add_action( 'admin_head', array( $this, 'admin_head' ) );
		add_action( 'admin_init', array( $this, 'bvm_welcome' ) );
	}

	/**
	 * Add admin menus/screens.
	 */
	public function admin_menus() {

		if ( empty( $_GET['page'] ) ) {
			return;
		}

		$welcome_page_name  = __( 'About Bulk Variations Manager', SA_Bulk_Variations::$text_domain );
		$welcome_page_title = __( 'Welcome to Bulk Variations Manager', SA_Bulk_Variations::$text_domain );

		switch ( $_GET['page'] ) {
			case 'bvm-about' :
				$page = add_dashboard_page( $welcome_page_title, $welcome_page_name, 'manage_options', 'bvm-about', array( $this, 'about_screen' ) );
			break;
			case 'bvm-faqs' :
			 	$page = add_dashboard_page( $welcome_page_title, $welcome_page_name, 'manage_options', 'bvm-faqs', array( $this, 'faqs_screen' ) );
			break;
		}
	}

	/**
	 * Add styles just for this page, and remove dashboard page links.
	 */
	public function admin_head() {
		remove_submenu_page( 'index.php', 'bvm-about' );
		remove_submenu_page( 'index.php', 'bvm-faqs' );

		?>
		<style type="text/css">
			/*<![CDATA[*/
			.about-wrap h3 {
				margin-top: 1em;
				margin-right: 0em;
				margin-bottom: 0.1em;
				font-size: 1.25em;
				line-height: 1.3em;
			}
			.about-wrap p {
				margin-top: 0.6em;
				margin-bottom: 0.8em;
				line-height: 1.6em;
				font-size: 14px;
			}
			.about-wrap .feature-section {
				padding-bottom: 5px;
			}
			/*]]>*/
		</style>
		<?php
	}

	/**
	 * Intro text/links shown on all about pages.
	 */
	private function intro() {

		if ( is_callable( 'SA_Bulk_Variations::get_bvm_plugin_data' ) ) {
			$plugin_data = SA_Bulk_Variations::get_bvm_plugin_data();
			$version = $plugin_data['Version'];
		} else {
			$version = '';
		}

		?>
		<h1><?php echo sprintf( __( 'Welcome to Bulk Variations Manager %s', SA_Bulk_Variations::$text_domain ), $version ); ?></h1>

		<h3><?php _e("Thanks for installing! We hope you enjoy using Bulk Variations Manager.", SA_Bulk_Variations::$text_domain); ?></h3>

		<div class="feature-section col two-col"><br>
			<div class="col-1">
				<p class="woocommerce-actions">
					<a href="<?php echo admin_url('edit.php?post_type=product&page=woocommerce_variations'); ?>" class="button button-primary"><?php _e( 'Get Started!', SA_Bulk_Variations::$text_domain ); ?></a>
					<a href="<?php echo esc_url( 'http://www.storeapps.org/support/documentation/bulk-variations-manager/' ); ?>" class="docs button button-primary" target="_blank"><?php _e( 'Docs', SA_Bulk_Variations::$text_domain ); ?></a>
				</p>
			</div>
		</div>

		<h2 class="nav-tab-wrapper">
			<a class="nav-tab <?php if ( $_GET['page'] == 'bvm-about' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'bvm-about' ), 'index.php' ) ) ); ?>">
				<?php _e( "Why Bulk Variations Manager", SA_Bulk_Variations::$text_domain ); ?>
			</a>
			<a class="nav-tab <?php if ( $_GET['page'] == 'bvm-faqs' ) echo 'nav-tab-active'; ?>" href="<?php echo esc_url( admin_url( add_query_arg( array( 'page' => 'bvm-faqs' ), 'index.php' ) ) ); ?>">
				<?php _e( "FAQ's", SA_Bulk_Variations::$text_domain ); ?>
			</a>
		</h2>
		<?php
	}

	/**
	 * Output the about screen.
	 */
	public function about_screen() {
		?>
		<div class="wrap about-wrap">

		<?php $this->intro(); ?>

			<div class="changelog">
				<h3 align="center"><?php echo __( 'Why Bulk Variations Manager?', SA_Bulk_Variations::$text_domain ); ?></h3>
				<div class="feature-section col three-col">
					<div class="col">
						<p><?php echo __( 'Bulk Variations Manager makes the process of creating WooCommerce Variations, much easier & simpler. With few clicks it can create hundreds & thousands of variations, all at once', SA_Bulk_Variations::$text_domain ); ?></p>
					</div>
					<div class="col">
						<p><?php echo __( 'No more frustration with lengthy, complex & multi-click process of creating variations in WooCommerce. No need to repeat same boring steps to create variations by going into each product.', SA_Bulk_Variations::$text_domain ); ?></p>
					</div>
					<div class="col last-feature">
						<p><?php echo __( 'Bulk Variations Manager does this by combining all steps of creating variations, to one. It takes all your inputs in one-page form & processes them at once to create variations.', SA_Bulk_Variations::$text_domain ); ?></p>
					</div>
				</div>
				<h3 align="center"><?php echo __( 'Terminologies you need to know', SA_Bulk_Variations::$text_domain ); ?></h3>
				<div class="feature-section col three-col">
					<div class="col">
						<h4><?php echo  __( 'Base Product', SA_Bulk_Variations::$text_domain ); ?></h4>
						<p><?php echo __( 'The main product in which variations will be added or in which attributes will be added or from which variations will be deleted', SA_Bulk_Variations::$text_domain ); ?></p>
					</div>
					<div class="col">
						<h4><?php echo __( 'Base Price', SA_Bulk_Variations::$text_domain ); ?></h4>
						<p><?php echo __( 'This value will be used for calculating final price of variation or for setting price, in case you are setting attributes in product/s.', SA_Bulk_Variations::$text_domain ); ?></p>
						<p><?php echo __( 'This is not stored anywhere, it is used only for calculation of final price.', SA_Bulk_Variations::$text_domain ); ?></p>
					</div>
					<div class="col last-feature">
						<h4><?php echo __( 'Differential Price', SA_Bulk_Variations::$text_domain ); ?></h4>
						<p><?php echo __( 'You can enter price per attribute. This differential price along with base price will make final price of variation.', SA_Bulk_Variations::$text_domain ); ?></p>
						<p><?php echo __( 'This will be helpful when price of variations are based on attributes used in that variation.', SA_Bulk_Variations::$text_domain ); ?></p>
					</div>
				</div>
				<h3 align="center"><?php echo __( 'Creating Variations', SA_Bulk_Variations::$text_domain ); ?></h3>
				<div class="feature-section col two-col">
					<div align="center" class="col">
						<h4><?php echo __( 'WooCommerce way', SA_Bulk_Variations::$text_domain ); ?></h4>
						<p><?php echo __( '(for each product)', SA_Bulk_Variations::$text_domain ); ?></p>
						<br>
						<p><?php echo sprintf(__( 'Open existing or add new product', SA_Bulk_Variations::$text_domain ) ); ?></p>
						<p>&darr;</p>
						<p><?php echo sprintf(__( 'Set product type to %s', SA_Bulk_Variations::$text_domain ), '<code>' . __( 'Variable', SA_Bulk_Variations::$text_domain ) . '</code>' ); ?></p>
						<p>&darr;</p>
						<p><?php echo sprintf(__( 'Go to %s tab', SA_Bulk_Variations::$text_domain ), '<code>' . __( 'Attributes', SA_Bulk_Variations::$text_domain ) . '</code>' ); ?></p>
						<p>&darr;</p>
						<p><?php echo sprintf(__( 'Add attributes going to be used for variations', SA_Bulk_Variations::$text_domain ) ); ?></p>
						<p>&darr;</p>
						<p><?php echo sprintf(__( 'Select values for each attributes', SA_Bulk_Variations::$text_domain ) ); ?></p>
						<p>&darr;</p>
						<p><?php echo sprintf(__( 'Check %s', SA_Bulk_Variations::$text_domain ), '<code>' . __( 'Used for variations', SA_Bulk_Variations::$text_domain ) . '</code>' ); ?></p>
						<p>&darr;</p>
						<p><?php echo sprintf(__( 'Save attributes', SA_Bulk_Variations::$text_domain ) ); ?></p>
						<p>&darr;</p>
						<p><?php echo sprintf(__( 'Go to %s tab', SA_Bulk_Variations::$text_domain ), '<code>' . __( 'Variations', SA_Bulk_Variations::$text_domain ) . '</code>' ); ?></p>
						<p>&darr;</p>
						<p><?php echo sprintf(__( 'Click on %s', SA_Bulk_Variations::$text_domain ), '<code>' . __( 'Link all variations', SA_Bulk_Variations::$text_domain ) . '</code>' ); ?></p>
						<p>&darr;</p>
						<p><?php echo sprintf(__( 'Set prices for each variations', SA_Bulk_Variations::$text_domain ) ); ?></p>
						<p>&darr;</p>
						<p><?php echo sprintf(__( 'Save product', SA_Bulk_Variations::$text_domain ) ); ?></p>
					</div>
					<div class="col last-feature" align="center">
						<h4><?php echo __( 'Bulk Variations Manager way', SA_Bulk_Variations::$text_domain ); ?></h4>
						<p><?php echo __( '(for multiple products at once)', SA_Bulk_Variations::$text_domain ); ?></p>
						<br>
						<p><?php echo sprintf(__( 'Select %s', SA_Bulk_Variations::$text_domain ), '<code>' . __( 'Create / update variations in product/s', SA_Bulk_Variations::$text_domain ) . '</code>' ); ?></p>
						<p>&darr;</p>
						<p><?php echo sprintf(__( 'Select base products from 3 options - create new product/s, all products from category, choosen product/s', SA_Bulk_Variations::$text_domain ) ); ?></p>
						<p>&darr;</p>
						<p><?php echo sprintf(__( 'Set a base price', SA_Bulk_Variations::$text_domain ) ); ?></p>
						<p>&darr;</p>
						<p><?php echo sprintf(__( 'Choose attributes & its values from provided checkbox', SA_Bulk_Variations::$text_domain ) ); ?></p>
						<p>&darr;</p>
						<p><?php echo sprintf(__( 'Add differential price (optional)', SA_Bulk_Variations::$text_domain ) ); ?></p>
						<p>&darr;</p>
						<p><?php echo sprintf(__( 'Click %s', SA_Bulk_Variations::$text_domain ), '<code>' . __( 'Apply', SA_Bulk_Variations::$text_domain ) . '</code>' ); ?></p>
					</div>
				</div>
				<h3 align="center"><?php echo __( 'What is possible', SA_Bulk_Variations::$text_domain ); ?></h3>
				<div class="feature-section col two-col">
					<div class="col">
						<h4><?php echo __( 'Create WooCommerce Variations in batch', SA_Bulk_Variations::$text_domain ); ?></h4>
						<p><?php echo __( 'Bulk Variations Manager allows you to add same set of variations in multiple products, in batch, which is not possible in WooCommerce.', SA_Bulk_Variations::$text_domain ); ?></p>
						<p><?php echo __( 'You can add variations in 3 ways - create new product/s & add in it, in all products which belong to a category or choose product/s yourself', SA_Bulk_Variations::$text_domain ); ?></p>
						<p><?php echo __( 'Additionally, Bulk Variations Manager allows you to add thousands of variations at once, whereas WooCommerce allows you to add only 50 at a time.', SA_Bulk_Variations::$text_domain ); ?></p>
					</div>
					<div class="col last-feature">
						<h4><?php echo __( 'Delete variations in batch', SA_Bulk_Variations::$text_domain ); ?></h4>
						<p><?php echo __( 'Bulk Variations Manager allows you to delete all variations either from all products that belong to a category or from the product/s you selected', SA_Bulk_Variations::$text_domain ); ?></p>
						<p><?php echo __( 'You can delete variations from multiple products at once from single page, whereas in WooCommerce you\'ll have to go through each products', SA_Bulk_Variations::$text_domain ); ?></p>
						<p><?php echo __( 'You can not delete specific variations, or variations which is having a specific attributes.', SA_Bulk_Variations::$text_domain ); ?></p>
					</div>
				</div>
				<div class="feature-section col two-col">
					<div class="col">
						<h4><?php echo __( 'Set Attributes in products', SA_Bulk_Variations::$text_domain ); ?></h4>
						<p><?php echo __( 'Bulk Variations Manager allows you to add same set of attributes in multiple products, in batch, which is again not possible in WooCommerce', SA_Bulk_Variations::$text_domain ); ?></p>
						<p><?php echo __( 'You can add attributes in same 3 ways, as for adding variations - create new product/s & add in it, in all products which belong to a category or choose product/s yourself', SA_Bulk_Variations::$text_domain ); ?></p>
					</div>
					<div class="col last-feature">
						<h4><?php echo __( 'Update prices of variations', SA_Bulk_Variations::$text_domain ); ?></h4>
						<p><?php echo __( 'In addition to adding variations, Bulk Variations Manager gives you option to add prices in a unique way. It calculates final price for each variations using formula [base price + sum of differential prices of attributes used in that variation]', SA_Bulk_Variations::$text_domain ); ?></p>
						<p><?php echo __( 'This feature will be very useful when you know base price of your product & you also know that which attribute is going to add more price to that base price. In WooCommerce, when you\'ll have to create such variations, you\'ll need to calculate final price of each variations on your own, whereas Bulk Variations Manager will handle this calculation for you. You just need to add base price & differential price against attributes. The rest will be taken care by Bulk Variations Manager.', SA_Bulk_Variations::$text_domain ); ?></p>
					</div>
				</div>
			</div>
			<div class="changelog" align="center">
				<h4><?php _e( 'Do check out Some of our other products!', SA_Bulk_Variations::$text_domain ); ?></h4>
				<p><a target="_blank" href="<?php echo esc_url('http://www.storeapps.org/shop/'); ?>"><?php _e('Let me take to product catalog', SA_Bulk_Variations::$text_domain); ?></a></p>
			</div>
		</div>
		<?php
	}

	/**
	 * Output the FAQ's screen.
	 */
	public function faqs_screen() {
		?>
		<div class="wrap about-wrap">

			<?php $this->intro(); ?>
            
            <h3><?php echo __("FAQ / Common Problems", SA_Bulk_Variations::$text_domain); ?></h3>

            <?php
            	$faqs = array(
            				array(
            						'que' => __( 'Bulk Variations Manager\'s fields are broken?', SA_Bulk_Variations::$text_domain ),
            						'ans' => __( 'Make sure you are using latest version of Bulk Variations Manager. If still the issue persist, deactivate all plugins except WooCommerce & Bulk Variations Manager. Recheck the issue, if the issue still persists, contact us. If the issue goes away, re-activate other plugins one-by-one & re-checking the fields, to find out which plugin is conflicting. Inform us about this issue.', SA_Bulk_Variations::$text_domain )
            					),
            				array(
            						'que' => __( 'Unable to open product after creating variations from Bulk Variations Manager OR Product is not loading OR Product edit page is unresponsive?', SA_Bulk_Variations::$text_domain ),
            						'ans' => sprintf(__( 'Opening product & loading variations are not handled by Bulk Variations Manager. These are handled by WooCommerce core. Bulk Variations Manager\'s functionality is to create variations.', SA_Bulk_Variations::$text_domain ),
            											'<p>' . __( 'You can try increasing WP Memory Limit & PHP Post Max Size', SA_Bulk_Variations::$text_domain ) . '</p>' )
            					),
            				array(
            						'que' => __( 'Bulk Variations Manager\'s process is not completing. It is getting stuck at some point?', SA_Bulk_Variations::$text_domain ),
            						'ans' => sprintf(__( 'Try %s. This method can be slow but it gives better results. %s %s %s', SA_Bulk_Variations::$text_domain ), '<a href="' . admin_url( 'edit.php?post_type=product&page=woocommerce_variations&bvm_version=old' ) . '">' . __( 'this method', SA_Bulk_Variations::$text_domain ) . '</a>',
            											'<p>' . __( 'Bulk Variations Manager comes with 2 methods: faster & slower. The default one, that loads on clicking "Bulk Variations" from sidebar admin menu, is the faster method.', SA_Bulk_Variations::$text_domain ) . '</p>',
            											'<p>' . __( 'Faster method processes values entered by you and saves the data in a CSV file. After that it imports that file directly into database. Since this method uses file operation for writing data & then importing it in database, it needs additional settings & permissions. If the faster method is getting stuck at some point, there are higher chances that required settings & permissions are not there. For more information on these settings you can contact us.', SA_Bulk_Variations::$text_domain ) . '</p>',
            											'<p>' . __( 'Slower method processes values entered by you and uses WooCommerce\'s core functions to create variations. Therefore it gives better results. Since it doesn\'t uses file import, this method is much slower.', SA_Bulk_Variations::$text_domain ) . '</p>' )
            					),
            				array(
            						'que' => __( 'Small number of variations are creating but large number of variations creation is failing.', SA_Bulk_Variations::$text_domain ),
            						'ans' => sprintf(__( 'When Bulk Variations Manager processes data entered by you to create large number of variations, it needs sufficient memory to hold this huge data. Please check %s and %s. Increase its value to check if it is creating more variations or not. You can check these values from "WooCommerce -> System Status"', SA_Bulk_Variations::$text_domain ), '<strong>WP Memory Limit</strong>', '<strong>PHP Post Max Size</strong>' )
            					)
            			);

            	$faqs = array_chunk( $faqs, 2 );

				echo '<div>';
            	foreach ( $faqs as $fqs ) {
            		echo '<div class="two-col">';
            		foreach ( $fqs as $index => $faq ) {
            			echo '<div' . ( ( $index == 1 ) ? ' class="col last-feature"' : ' class="col"' ) . '>';
            			echo '<h4>' . $faq['que'] . '</h4>';
            			echo '<p>' . $faq['ans'] . '</p>';
            			echo '</div>';
            		}
            		echo '</div>';
            	}
            	echo '</div>';
            ?>

		</div>
		
		<?php
	}


	/**
	 * Sends user to the welcome page on first activation.
	 */
	public function bvm_welcome() {

       	if ( ! get_transient( '_bulk_variations_manager_activation_redirect' ) ) {
			return;
		}
		
		// Delete the redirect transient
		delete_transient( '_bulk_variations_manager_activation_redirect' );

		wp_redirect( admin_url( 'index.php?page=bvm-about' ) );
		exit;

	}
}

new BVM_Admin_Welcome();