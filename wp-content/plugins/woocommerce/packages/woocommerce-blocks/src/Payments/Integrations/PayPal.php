<?php
/**
 * PayPal Standard (core) gateway implementation.
 *
 * @package WooCommerce/Blocks
 * @since 2.6.0
 */

namespace Automattic\WooCommerce\Blocks\Payments\Integrations;

use Exception;
use WC_Stripe_Payment_Request;
use WC_Stripe_Helper;
use Automattic\WooCommerce\Blocks\Assets\Api;

/**
 * PayPal Standard payment method integration
 *
 * @since 2.6.0
 */
final class PayPal extends AbstractPaymentMethodType {
	/**
	 * Payment method name defined by payment methods extending this class.
	 *
	 * @var string
	 */
	protected $name = 'paypal';

	/**
	 * Settings from the WP options table
	 *
	 * @var array
	 */
	private $settings;

	/**
	 * An instance of the Asset Api
	 *
	 * @var Api
	 */
	private $asset_api;

	/**
	 * Constructor
	 *
	 * @param Api $asset_api An instance of Api.
	 */
	public function __construct( Api $asset_api ) {
		$this->asset_api = $asset_api;
	}

	/**
	 * Initializes the payment method type.
	 */
	public function initialize() {
		$this->settings = get_option( 'woocommerce_paypal_settings', [] );
	}

	/**
	 * Returns if this payment method should be active. If false, the scripts will not be enqueued.
	 *
	 * @return boolean
	 */
	public function is_active() {
		return ! empty( $this->settings['enabled'] ) && 'yes' === $this->settings['enabled'];
	}

	/**
	 * Returns an array of scripts/handles to be registered for this payment method.
	 *
	 * @return array
	 */
	public function get_payment_method_script_handles() {
		$this->asset_api->register_script(
			'wc-payment-method-paypal',
			'build/wc-payment-method-paypal.js'
		);
		return [ 'wc-payment-method-paypal' ];
	}

	/**
	 * Returns an array of key=>value pairs of data made available to the payment methods script.
	 *
	 * @return array
	 */
	public function get_payment_method_data() {
		return [
			'title'       => isset( $this->settings['title'] ) ? $this->settings['title'] : '',
			'description' => isset( $this->settings['description'] ) ? $this->settings['description'] : '',
		];
	}
}
