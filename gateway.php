<?php

if ( !defined( 'ABSPATH' ) ) {
	exit();
}

add_action( 'plugins_loaded', 'cryptoniq_payment_gateway_init' );
function cryptoniq_payment_gateway_init() {
    if ( !class_exists( 'WC_Payment_Gateway' ) ) {
		return;
	}

    class Cryptoniq_Gateway extends WC_Payment_Gateway
	{
        public function __construct() {
			$description = esc_html__( 'Pay with cryprocurrencies.', 'cryptoniq' );
			if ( class_exists( 'Redux' ) && !empty( Redux::getOption( CRYPTONIQ_OPTION, 'description' ) ) ) {
				$description = Redux::getOption( CRYPTONIQ_OPTION, 'description' );
			} 
			
            $this->id = CRYPTONIQ_PAY_ID;
			
            $this->has_fields = false;
			$this->icon = CRYPTONIQ_DIR_URL . 'assets/images/cryptoniq.logo.png';
            $this->init_form_fields();
            $this->init_settings();
			
            $this->method_title = 'Cryptoniq'; 
            $this->method_description = esc_html__( 'Pay with cryprocurrencies.', 'cryptoniq' );
            $this->title = 'Cryptoniq';
			$this->description = $description;
		
			$this->is_enabled(); 
			
            add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_filter( 'woocommerce_endpoint_order-pay_title', array( $this, 'pay_title' ), 10, 2 );
        }
    
        public function init_form_fields() {
            $this->form_fields = array (
                'enabled' => array (
                    'title' => esc_html__( 'Online/Offline gateway', 'cryptoniq' ),
                    'type' => 'checkbox',
                    'label' => esc_html__( 'Enable', 'cryptoniq' ),
                    'default' => 'no'
                )
            );
        }

        public function admin_options(){
            echo '<h3>Cryptoniq</h3>';
            echo '<table class="form-table">';
            	$this->generate_settings_html();
            echo '</table>';
        }

     	public function pay_title( $title, $endpoint ) {
			return;
    	}   
  
        public function process_payment( $order_id ){
            $order = new WC_Order( $order_id );

            return array(
				'result' => 'success',
				'redirect' => add_query_arg(
					'order-pay',
					$order->id,
					add_query_arg( 
						'key',
						$order->order_key,
						get_permalink( woocommerce_get_page_id( 'pay' ) )
					)
				)
            );
        }
    
	    public function is_enabled() {
    		if ( !class_exists( 'Redux' ) ) {
        		return;
    		}
			
		    if ( $this->get_option( 'enabled' ) == 'yes' ) {
			    Redux::setOption( CRYPTONIQ_OPTION, 'cryptoniq_panel_activate', 'yes' );
		    } else {
		        Redux::setOption( CRYPTONIQ_OPTION, 'cryptoniq_panel_activate', 'no' );
		    }
	    }    
    }

    // Add Gateway to WooCommerce
    // ======================================================

    function cryptoniq_add_payment_gateway( $methods ) {
        $methods[] = 'Cryptoniq_Gateway';
        return $methods;
    }

    add_filter( 'woocommerce_payment_gateways', 'cryptoniq_add_payment_gateway' );
}