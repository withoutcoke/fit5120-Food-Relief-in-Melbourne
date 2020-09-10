<?php
/**
 * Plugin.
 *
 * @package Flexible Shippign
 */

use FSVendor\WPDesk\ABTesting\ABVariant;
use FSVendor\WPDesk\Logger\WPDeskLoggerFactory;
use FSVendor\WPDesk\Mutex\WordpressPostMutex;
use FSVendor\WPDesk\Notice\AjaxHandler;
use FSVendor\WPDesk\PluginBuilder\Plugin\AbstractPlugin;
use FSVendor\WPDesk\PluginBuilder\Plugin\HookableCollection;
use FSVendor\WPDesk\PluginBuilder\Plugin\HookableParent;
use FSVendor\WPDesk\PluginBuilder\Plugin\TemplateLoad;
use FSVendor\WPDesk\Pointer\PointersScripts;
use FSVendor\WPDesk\Session\SessionFactory;
use FSVendor\WPDesk\Tracker\Deactivation\PluginData;
use FSVendor\WPDesk\Tracker\Deactivation\TrackerFactory;
use FSVendor\WPDesk\View\Resolver\ChainResolver;
use FSVendor\WPDesk\View\Resolver\DirResolver;
use FSVendor\WPDesk\View\Resolver\WPThemeResolver;
use FSVendor\WPDesk\WooCommerce\CurrencySwitchers\ShippingIntegrations;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use WPDesk\FS\TableRate\FreeShipping\FreeShippingNotice;
use WPDesk\FS\TableRate\FreeShipping\FreeShippingNoticeGenerator;
use WPDesk\FS\TableRate\NewRulesTableBanner\RulesBannerClickedOption;
use WPDesk\FS\TableRate\NewRulesTableBanner\RulesBannerLikeOption;
use WPDesk\FS\TableRate\NewRulesTableBanner\RulesPointerBannerForNewTable;
use WPDesk\FS\TableRate\NewRulesTableBanner\RulesPointerBannerForOldTable;
use WPDesk\FS\TableRate\NewRulesTableDeactivationTracker;
use WPDesk\FS\TableRate\NewRulesTablePointer\RulesPointerMessage;
use WPDesk\FS\TableRate\NewRulesTablePointer\RulesPointerOption;
use WPDesk\FS\TableRate\NewRulesTableTracker;
use WPDesk\FS\TableRate\UserFeedback;
use WPDesk\FS\TableRate\ContextualInfo;

/**
 * Class Flexible_Shipping_Plugin
 */
class Flexible_Shipping_Plugin extends AbstractPlugin implements HookableCollection {

	use HookableParent;
	use TemplateLoad;

	/*
	 * Plugin file
	 */
	const PLUGIN_FILE = 'flexible-shipping/flexible-shipping.php';

	const PRIORITY_BEFORE_SHARED_HELPER = -35;

	/**
	 * Scripts version.
	 *
	 * @var string
	 */
	private $scripts_version = FLEXIBLE_SHIPPING_VERSION . '.209';

	/**
	 * Admin notices.
	 *
	 * @var WPDesk_Flexible_Shipping_Admin_Notices
	 */
	public $admin_notices;

	/**
	 * Default settings tab.
	 *
	 * @var string
	 */
	private $default_settings_tab = 'connect';

	/**
	 * Renderer.
	 *
	 * @var FSVendor\WPDesk\View\Renderer\Renderer;
	 */
	private $renderer;

	/**
	 * Is order processed on checkout?
	 *
	 * @var bool
	 */
	private $is_order_processed_on_checkout = false;

	/**
	 * Logger.
	 *
	 * @var \Psr\Log\LoggerInterface
	 */
	private $logger;

	/**
	 * Selected variant for AB message testing.
	 *
	 * @var ABVariant
	 */
	private $variant;

	/**
	 * Flexible_Invoices_Reports_Plugin constructor.
	 *
	 * @param FSVendor\WPDesk_Plugin_Info $plugin_info Plugin info.
	 */
	public function __construct( FSVendor\WPDesk_Plugin_Info $plugin_info ) {
		$this->plugin_info = $plugin_info;
		parent::__construct( $this->plugin_info );
		$this->init_logger();
		$this->init_logger_on_shipment();
	}

	/**
	 * Init logger on WPDesk_Flexible_Shipping_Shipment class.
	 */
	public function init_logger_on_shipment() {
		WPDesk_Flexible_Shipping_Shipment::set_fs_logger( $this->logger );
	}

	/**
	 * Initialize $this->logger
	 *
	 * @return LoggerInterface
	 */
	private function init_logger() {
		$logger_settings = new WPDesk_Flexible_Shipping_Logger_Settings();
		if ( $logger_settings->is_enabled() ) {
			return $this->logger = ( new WPDeskLoggerFactory() )->createWPDeskLogger( $logger_settings->get_logger_channel_name() );
		}
		return $this->logger = new NullLogger();
	}


	/**
	 * Load dependencies.
	 */
	private function load_dependencies() {
		require_once __DIR__ . '/../inc/functions.php';
		require_once( __DIR__ . '/wp-wpdesk-fs-shipment/shipment/functions.php' );
		require_once( __DIR__ . '/manifest/functions.php' );

		$session_factory = new SessionFactory();

		new WPDesk_Flexible_Shipping_Shipment_CPT( $this );

		new WPDesk_Flexible_Shipping_Shipping_Manifest_CPT( $this );

		new WPDesk_Flexible_Shipping_Shipment_Ajax( $this );

		$this->add_hookable( new WPDesk_Flexible_Shipping_Bulk_Actions( $session_factory ) );

		new WPDesk_Flexible_Shipping_Export( $this );

		new WPDesk_Flexible_Shipping_Multilingual( $this );

		new WPDesk_Flexible_Shipping_Multicurrency( $this );

		$this->admin_notices = new WPDesk_Flexible_Shipping_Admin_Notices( $this );

		$abtesting     = new WPDesk_Flexible_Shipping_AB_Deactivation_Contact_Information();
		$this->variant = $abtesting->get_variant();

		$this->add_hookable( new WPDesk_Flexible_Shipping_Single_Label_File_Dispatcher() );

		$this->add_hookable( new WPDesk_Flexible_Shipping_Tracker() );

		$this->add_hookable( new WPDesk\FS\Rate\Flexible_Shipping_Order_Counter() );
		$this->add_hookable( new WPDesk\FS\Rate\WPDesk_Flexible_Shipping_Rate_Notice() );

		$this->add_hookable( new WPDesk_Flexible_Shipping_AB_Tracker_Deactivation_Data( $this->variant ) );

		$this->add_hookable( new WPDesk_Flexible_Shipping_Add_Shipping() );

		$this->add_hookable( new WPDesk_Flexible_Shipping_Shorcode_Unit_Weight() );
		$this->add_hookable( new WPDesk_Flexible_Shipping_Shorcode_Unit_Dimension() );

		$this->add_hookable( new AjaxHandler( trailingslashit( $this->get_plugin()->get_plugin_url() ) . 'vendor_prefixed/wpdesk/wp-notice/assets' ) );

		$this->add_hookable( new WPDesk_Flexible_Shipping_Method_Created_Tracker_Deactivation_Data() );

		$this->add_hookable( new WPDesk_Flexible_Shipping_Logger_Downloader( new WPDeskLoggerFactory() ) );

		$this->add_hookable( new WPDesk_Flexible_Shipping_Rest_Api_Order_Response_Data_Appender() );

		$this->add_hookable( new ShippingIntegrations( 'flexible_shipping' ) );

		$this->add_hookable( new WPDesk_Flexible_Shipping_Order_Item_Meta() );

		$rules_banner_option = new RulesBannerLikeOption();
		$this->add_hookable( $rules_banner_option );

		$this->add_hookable( new RulesPointerBannerForOldTable() );

		$this->add_hookable( new RulesPointerBannerForNewTable() );

		$this->add_hookable( new RulesBannerClickedOption() );

		$this->add_hookable( new NewRulesTableTracker() );

		$this->add_hookable( new NewRulesTableDeactivationTracker() );

		$this->add_hookable( new UserFeedback() );

		$this->init_contextual_info();

	}

	/**
	 * Init contextual info on Flexible Shipping settings fields.
	 */
	private function init_contextual_info() {
		$base_location = wc_get_base_location();
		$contextual_info_creator = new ContextualInfo\Creator( $base_location['country'] );
		$this->add_hookable( $contextual_info_creator );
		$contextual_info_creator->create_contextual_info();
	}

	/**
	 * Init base variables for plugin
	 */
	public function init_base_variables() {
		$this->plugin_url = $this->plugin_info->get_plugin_url();

		$this->plugin_path   = $this->plugin_info->get_plugin_dir();
		$this->template_path = $this->plugin_info->get_text_domain();

		$this->plugin_text_domain   = $this->plugin_info->get_text_domain();
		$this->plugin_namespace     = $this->plugin_info->get_text_domain();
		$this->template_path        = $this->plugin_info->get_text_domain();
		$this->default_settings_tab = 'main';

		$this->settings_url = admin_url( 'admin.php?page=flexible-shipping-settings' );
		$this->docs_url     = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/docs/flexible-shipping-pro-woocommerce-docs/' : 'https://docs.flexibleshipping.com/collection/20-fs-table-rate/';

		$this->default_view_args = array(
			'plugin_url' => $this->get_plugin_url(),
		);

	}

	/**
	 * Set renderer.
	 */
	private function init_renderer() {
		$resolver = new ChainResolver();
		$resolver->appendResolver( new WPThemeResolver( $this->get_template_path() ) );
		$resolver->appendResolver( new DirResolver( trailingslashit( $this->plugin_path ) . 'templates' ) );
		$this->renderer = new FSVendor\WPDesk\View\Renderer\SimplePhpRenderer( $resolver );
	}

	/**
	 * Init.
	 */
	public function init() {
		$this->init_base_variables();
		$this->init_renderer();
		$this->load_dependencies();
		$this->hooks();
	}

	/**
	 * Hooks.
	 */
	public function hooks() {
		parent::hooks();

		add_filter( 'woocommerce_shipping_methods', array( $this, 'woocommerce_shipping_methods_filter' ), 10, 1 );

		add_action( 'woocommerce_after_shipping_rate', array( $this, 'woocommerce_after_shipping_rate' ), 10, 2 );

		add_action(
			'flexible_shipping_method_rate_id',
			array(
				$this,
				'flexible_shipping_method_rate_id',
			),
			9999999,
			2
		);

		add_filter(
			'woocommerce_shipping_chosen_method',
			array(
				$this,
				'woocommerce_default_shipment_method',
			),
			10,
			3
		);

		add_action(
			'woocommerce_checkout_update_order_meta',
			array(
				$this,
				'add_flexible_shipping_order_meta_on_checkout_woo_pre_27',
			)
		);

		add_action(
			'woocommerce_checkout_create_order',
			array(
				$this,
				'add_flexible_shipping_order_meta_on_checkout',
			)
		);

		add_filter( 'option_woocommerce_cod_settings', array( $this, 'option_woocommerce_cod_settings' ) );

		add_action( 'plugins_loaded', array( $this, 'create_tracker' ), self::PRIORITY_BEFORE_SHARED_HELPER );

		add_action( 'admin_init', array( $this, 'init_deactivation_tracker' ) );

		add_action( 'woocommerce_init', array( $this, 'init_free_shipping_notice' ) );

		$this->hooks_on_hookable_objects();

	}

	/**
	 * Init free shipping notice.
	 *
	 * @internal
	 */
	public function init_free_shipping_notice() {
		$cart = WC()->cart;
		$session = WC()->session;
		if ( null !== $cart && null !== $session ) {
			( new FreeShippingNoticeGenerator( $cart, $session ) )->hooks();
			( new FreeShippingNotice( $session ) )->hooks();
		}
	}

	/**
	 * Init deactivation tracker.
	 */
	public function init_deactivation_tracker() {
		$plugin_data = new PluginData(
			WPDesk_Flexible_Shipping_Tracker::FLEXIBLE_SHIPPING_PLUGIN_SLUG,
			WPDesk_Flexible_Shipping_Tracker::FLEXIBLE_SHIPPING_PLUGIN_FILE,
			WPDesk_Flexible_Shipping_Tracker::FLEXIBLE_SHIPPING_PLUGIN_TITLE
		);
		$thickbox_factory = new WPDesk_Flexible_Shipping_Deactivation_Thickbox_Factory(
			$this->variant,
			$plugin_data
		);
		$deactivation_tracker = TrackerFactory::createCustomTracker(
			$plugin_data,
			null,
			$thickbox_factory->create_thickbox()
		);
		$deactivation_tracker->hooks();
	}

	/**
	 * Maybe create tracker.
	 */
	public function create_tracker() {
		$tracker_factory = new WPDesk_Tracker_Factory();
		$tracker_factory->create_tracker( basename( dirname( __FILE__ ) ) );
	}

	/**
	 * Woocommerce shipping methods filter.
	 *
	 * @param array $methods .
	 *
	 * @return array
	 */
	public function woocommerce_shipping_methods_filter( $methods ) {
		$methods['flexible_shipping']                            = WPDesk_Flexible_Shipping::class;
		$methods[ WPDesk_Flexible_Shipping_Settings::METHOD_ID ] = WPDesk_Flexible_Shipping_Settings::class;

		return $methods;
	}


	/**
	 * Option woocommerce cod settings filter.
	 *
	 * @param array $value .
	 *
	 * @return array
	 */
	public function option_woocommerce_cod_settings( $value ) {
		if ( is_checkout() ) {
			if (
				! empty( $value )
				&& is_array( $value )
				&& 'yes' === $value['enabled']
				&& ! empty( $value['enable_for_methods'] )
				&& is_array( $value['enable_for_methods'] )
			) {
				foreach ( $value['enable_for_methods'] as $method ) {
					if ( 'flexible_shipping' === $method ) {
						$all_fs_methods          = flexible_shipping_get_all_shipping_methods();
						$all_shipping_methods    = flexible_shipping_get_all_shipping_methods();
						$flexible_shipping       = $all_shipping_methods['flexible_shipping'];
						$flexible_shipping_rates = $flexible_shipping->get_all_rates();
						foreach ( $flexible_shipping_rates as $flexible_shipping_rate ) {
							$value['enable_for_methods'][] = $flexible_shipping_rate['id_for_shipping'];
						}
						break;
					}
				}
			}
		}

		return $value;
	}

	/**
	 * Add flexible shipping order meta on checkout.
	 *
	 * @param WC_Order $order Order.
	 */
	public function add_flexible_shipping_order_meta_on_checkout( $order ) {
		if ( ! $this->is_order_processed_on_checkout ) {
			$mutex = WordpressPostMutex::fromOrder( $order );
			$mutex->acquireLock();
			$this->is_order_processed_on_checkout = true;
			$order_shipping_methods               = $order->get_shipping_methods();
			foreach ( $order_shipping_methods as $shipping_id => $shipping_method ) {
				if ( isset( $shipping_method['item_meta'] )
				     && isset( $shipping_method['item_meta']['_fs_method'] )
				) {
					$fs_method = $shipping_method['item_meta']['_fs_method'];
					if ( ! empty( $fs_method['method_integration'] ) ) {
						$order_meta = $order->get_meta( '_flexible_shipping_integration', false );
						if ( ! in_array( $fs_method['method_integration'], $order_meta, true ) ) {
							$order->add_meta_data( '_flexible_shipping_integration', $fs_method['method_integration'] );
						}
					}
				}
			}
			$mutex->releaseLock();
		}
	}

	/**
	 * Add flexible shipping order meta on checkout (for WooCommerce versions before 2.7).
	 *
	 * @param int $order_id Order id.
	 */
	public function add_flexible_shipping_order_meta_on_checkout_woo_pre_27( $order_id ) {
		if ( version_compare( WC_VERSION, '2.7', '<' ) ) {
			if ( ! $this->is_order_processed_on_checkout ) {
				$this->is_order_processed_on_checkout = true;
				$order                                = wc_get_order( $order_id );
				$order_shipping_methods               = $order->get_shipping_methods();
				foreach ( $order_shipping_methods as $shipping_id => $shipping_method ) {
					if ( isset( $shipping_method['item_meta'] )
					     && isset( $shipping_method['item_meta']['_fs_method'] )
					     && isset( $shipping_method['item_meta']['_fs_method'][0] )
					) {
						$fs_method = unserialize( $shipping_method['item_meta']['_fs_method'][0] );
						if ( ! empty( $fs_method['method_integration'] ) ) {
							add_post_meta( $order->id, '_flexible_shipping_integration', $fs_method['method_integration'] );
						}
					}
				}
			}
		}
	}

	/**
	 * Set appropriate default FS method if no method chosen.
	 *
	 * @param string $default Default shipping method in frontend.
	 * @param WC_Shipping_Rate[] $available_methods Available methods in frontend.
	 *        Function is assigned to woocommerce_default_shipment_method filter.
	 *        In this parameter we expecting array of WC_Shipping_Rate objects.
	 *        But third party plugins can change this parameter type or set it to null.
	 * @param string|bool|null $chosen_method If false or null then no method is chosen.
	 *
	 * @return string
	 */
	public function woocommerce_default_shipment_method( $default, $available_methods, $chosen_method ) {
		// @TODO: Infinite methods calling on Woocommerce 3.1
		if ( version_compare( WC_VERSION, '3.2', '<' ) ) {
			return $default;
		}
		if ( ! is_array( $available_methods ) ) {
			return $default;
		}
		if ( null === $chosen_method || false === $chosen_method || ! $this->check_if_shipment_available_for_current_cart( $chosen_method ) ) {
			foreach ( $available_methods as $available_method ) {
				$method_meta      = $available_method->get_meta_data();
				$default_meta_key = WPDesk_Flexible_Shipping::META_DEFAULT;

				if ( $method_meta && isset( $method_meta[ $default_meta_key ] ) && 'yes' === $method_meta[ $default_meta_key ] ) {

					$candidate_id = $available_method->get_id();
					if ( $this->check_if_shipment_available_for_current_cart( $candidate_id ) ) {
						return $candidate_id;
					}
				}
			}

			return $default;
		}

		return $chosen_method;
	}

	/**
	 * Shipment can be possible but not for the current cart. Check if possible for cart.
	 *
	 * @param string $shipment_method_id .
	 *
	 * @return bool Possible or not
	 */
	private function check_if_shipment_available_for_current_cart( $shipment_method_id ) {
		if ( empty( WC()->shipping()->packages ) ) {
			$shipping_packages = WC()->shipping()->calculate_shipping( WC()->cart->get_shipping_packages() );
		} else {
			$shipping_packages = WC()->shipping()->packages;
		}
		if ( is_array( $shipping_packages ) ) {
			foreach ( $shipping_packages as $package ) {
				if ( isset( $package['rates'][ $shipment_method_id ] ) && $package['rates'][ $shipment_method_id ] ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Admin enqueue scripts.
	 */
	public function admin_enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		wp_register_script(
			'fs_admin',
			trailingslashit( $this->get_plugin_assets_url() ) . 'js/admin' . $suffix . '.js',
			array( 'jquery' ),
			$this->scripts_version
		);

		$notice_url = get_locale() == 'pl_PL' ? 'https://wpde.sk/fs-rate-not-good-pl' : 'https://wpde.sk/fs-rate-not-good';
		wp_localize_script(
			'fs_admin',
			'fs_admin',
			array(
				'ajax_url'                => admin_url( 'admin-ajax.php' ),
				'notice_not_good_enought' => sprintf(
				// Translators: link.
					__( 'How can We make Flexible Shipping better for you? %1$sJust write to us.%2$s', 'flexible-shipping' ),
					'<a class="button close-fs-rate-notice" target="_blank" href="' . esc_url( $notice_url ) . '">',
					'</a>'
				),
			)
		);
		wp_enqueue_script( 'fs_admin' );

		$current_screen = get_current_screen();

		wp_register_script(
			'wpdesk_contextual_info',
			trailingslashit( $this->get_plugin_assets_url() ) . 'js/contextual-info' . $suffix . '.js',
			array( 'jquery' ),
			$this->scripts_version
		);
		wp_enqueue_script( 'wpdesk_contextual_info' );

		if ( ! empty( $current_screen ) && 'shop_order' === $current_screen->id ) {
			wp_enqueue_media();
		}

		wp_register_script( 'fs_ap_conditional_logic', trailingslashit( $this->get_plugin_assets_url() ) . 'js/ap_conditional_logic' . $suffix . '.js', array( 'jquery' ), $this->scripts_version );
		wp_enqueue_script( 'fs_ap_conditional_logic' );

		wp_enqueue_style(
			'fs_admin',
			trailingslashit( $this->get_plugin_assets_url() ) . 'css/admin' . $suffix . '.css',
			array(),
			$this->scripts_version
		);
		wp_enqueue_style(
			'fs_font',
			trailingslashit( $this->get_plugin_assets_url() ) . 'css/font' . $suffix . '.css',
			array(),
			$this->scripts_version
		);
	}

	/**
	 * Enqueue Wordpress scripts.
	 */
	public function wp_enqueue_scripts() {
		$suffix = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
	}

	/**
	 * Links filter.
	 *
	 * @param array $links .
	 *
	 * @return array
	 */
	public function links_filter( $links ) {
		$docs_link    = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/docs/flexible-shipping-pro-woocommerce-docs/' : 'https://docs.flexibleshipping.com/collection/20-fs-table-rate/';
		$support_link = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/docs/?utm_campaign=flexible-shipping&utm_source=user-site&utm_medium=link&utm_term=&utm_content=fs-plugins-support' : 'https://flexibleshipping.com/support/?utm_campaign=flexible-shipping&utm_source=user-site&utm_medium=link&utm_term=&utm_content=fs-plugins-support';

		$docs_link .= '?utm_campaign=flexible-shipping&utm_source=user-site&utm_medium=link&utm_term=&utm_content=fs-plugins-docs';

		$settings_url = admin_url( 'admin.php?page=wc-settings&tab=shipping&section=' . WPDesk_Flexible_Shipping_Settings::METHOD_ID );

		$plugin_links = array(
			'<a href="' . $settings_url . '">' . __(
				'Settings',
				'flexible-shipping'
			) . '</a>',
			'<a target="_blank" href="' . $docs_link . '">' . __( 'Docs', 'flexible-shipping' ) . '</a>',
			'<a target="_blank" href="' . $support_link . '">' . __( 'Support', 'flexible-shipping' ) . '</a>',
		);
		$pro_link     = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/sklep/flexible-shipping-pro-woocommerce/' : 'https://flexibleshipping.com/table-rate/';
		$utm          = '?utm_campaign=flexible-shipping&utm_source=user-site&utm_medium=link&utm_term=&utm_content=fs-plugins-upgrade';

		if ( ! wpdesk_is_plugin_active( 'flexible-shipping-pro/flexible-shipping-pro.php' ) ) {
			$plugin_links[] = '<a href="' . $pro_link . $utm . '" target="_blank" style="color:#d64e07;font-weight:bold;">' . __(
					'Upgrade',
					'flexible-shipping'
				) . '</a>';
		}

		return array_merge( $plugin_links, $links );
	}


	/**
	 * WooCommerce after shipping rate action.
	 *
	 * @param WC_Shipping_Rate $method .
	 * @param int $index .
	 */
	public function woocommerce_after_shipping_rate( $method, $index ) {
		if ( 'flexible_shipping' === $method->method_id ) {
			$description = WC()->session->get( 'flexible_shipping_description_' . $method->id, false );
			if ( $description && '' !== $description ) {
				echo $this->load_template(
					'flexible-shipping/after-shipping-rate',
					'cart/',
					array(
						'method_description' => $description,
					)
				); // WPCS: XSS OK.
			}
		}
	}

	/**
	 * .
	 *
	 * @param string $method_id .
	 * @param array $shipping_method .
	 *
	 * @return string
	 */
	public function flexible_shipping_method_rate_id( $method_id, array $shipping_method ) {
		if ( isset( $shipping_method['id_for_shipping'] ) && '' !== $shipping_method['id_for_shipping'] ) {
			$method_id = $shipping_method['id_for_shipping'];
		}

		return $method_id;
	}


}
