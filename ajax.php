<?php

if ( !defined( 'ABSPATH' ) ) {
	exit();
}

// AJAX Constructor
// ======================================================

class Cryptoniq_AJAX
{
    function __construct() {
        add_action( 'admin_init', array( $this, 'init' ) );
    }

    public function init() {
		add_action( 'wp_ajax_cryptoniq_paybox_checker', array( $this, 'tx_checker' ) );	
		add_action( 'wp_ajax_nopriv_cryptoniq_paybox_checker', array( $this, 'tx_checker' ) );
    }

    // Check Security
    // ======================================================
    
    private function security() {
        // Check nonce security
        check_ajax_referer( 'cryptoniq_front_ajax_nonce', 'nonce' );    
    }
	
    // Call Security 
    // ======================================================
    
    public function sec_caller() {		
        $this->security();
    }
	
    // Update Option Txlist
    // ======================================================
	
	public static function options_txlist_update( $txid = '', $id = '' ) {
		if ( empty( $txid ) || empty( $id ) ) {
			return;
		}
		
		$all_options = wp_load_alloptions();
	
		if ( !array_key_exists( 'cryptoniq_pay_txlist', $all_options ) ) {
    		add_option( 'cryptoniq_pay_txlist', array( $txid => $id ), '', 'yes' );
		} else {
			$tx_list = get_option( 'cryptoniq_pay_txlist' );
			
			if ( is_array( $tx_list ) ) {
				if ( !array_key_exists( $txid, $tx_list ) ) {
					$tx_add = array( $txid => $id );
    				$tx_list = $tx_add + $tx_list;
				}
			} else {
				$tx_list = array( $txid => $id );
			}
			
    		update_option( 'cryptoniq_pay_txlist', $tx_list, 'yes' );
		}		
	}

    // Update Order Status
    // ======================================================

    public static function order_status_update( $id = '', $status = 'processing' ) {		
		if ( empty( $id ) ) {
			return;
		}
		
    	$order = new WC_Order( $id );
		
        if ( !empty( $order ) ) {
			if ( $status == 'failed' ) {
				$order->update_status( 'failed' );
			} elseif ( $status == 'completed' ) {
            	$order->update_status( 'completed' );
			} elseif ( $status == 'on-hold' ) {
				$order->update_status( 'on-hold' );
			} else {
				$order->update_status( 'processing' );
			}
			
    		// Reduce stock levels
    		$order->reduce_order_stock();

    		// Remove cart
    		WC()->cart->empty_cart();
	    }
	}
	
    // Check Transaction
    // ======================================================

    public function tx_checker() {
		$this->security();	
			
		$id = intval( $_POST['id'] );
		if ( empty( $id ) ) {
			return false;
		}
		
		$order = wc_get_order( $id );
		
		date_default_timezone_set('UTC');
		$time = time();
			
		$coin = get_post_meta( $id, 'cryptoniq_coin_name', true );
		$wallet = get_post_meta( $id, 'cryptoniq_wallet', true );
		$amount = get_post_meta( $id, 'cryptoniq_coin_amount', true );
			
		if ( !get_post_meta( $id, 'cryptoniq_time_start', true ) ) {
			update_post_meta( $id, 'cryptoniq_time_start', $time );
		}
			
		cryptoniq_coin_txs( $wallet, $amount, $id );
	
		wp_die();
	}
}

new Cryptoniq_AJAX();