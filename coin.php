<?php

if ( !defined( 'ABSPATH' ) ) {
	exit();
}
	
function cryptoniq_coin_txs( $wallet = '', $amount = '', $id = '', $print = '' )
{		
    $request = cryptoniq_remote_get( $wallet );
    
    $output = array( 'found' => 0 );

	if ( is_wp_error( $request ) || wp_remote_retrieve_response_code( $request ) !== 200 ) {
		print_r( json_encode( $output ) );
		
		return;
	}
    
    $body = wp_remote_retrieve_body( $request );
	$content = json_decode( $body, true );
    
	$empty = 0;
	
	$order = wc_get_order( $id );
	if ( empty( $order ) || $order->get_status() == 'failed' || $order->get_status() == 'cancelled' ) {
		return; 
	}
			
	// time props
	$time_start = get_post_meta( $id, 'cryptoniq_time_start', true );
	$time_counter = 60 * 60 * 3;
	$time_end = $time_start + $time_counter;
	
	date_default_timezone_set('UTC');
	$time_current = time();
 
	// number without decimal
	$num_without_decimal = str_replace( '.', '', $amount );

	// number after decimal
	if ( strpos( $amount, '.' ) !== false ) {
    	$num_after_decimal = explode( '.', $amount );
    	$num_after_decimal = strlen( $num_after_decimal[1] );
	} else {
    	$num_after_decimal = 0;
	}

	// number multiply
	$num_multiply = 18 - $num_after_decimal;

	// number final
	$num_final = $num_without_decimal * pow( 10, $num_multiply );
	
	$txs = $content['result'];
			
    if ( is_array( $txs ) ) {
    	foreach ( $txs as $tx ) {
           	if ( strtolower( $wallet ) === strtolower( $tx['to'] ) && (float) $tx['value'] === (float) $num_final ) {	
            	$output = array(
                  	'found' => 1,
					'tx' => $tx['hash'],
                   	'conf' => $tx['confirmations'],
                   	'completed' => 0,
					'expire' => 0
                );
                
				// check it time expired
				if ( $time_current > $time_end || cryptoniq_check_txid( $tx['hash'], $id ) == 1 ) {
					if ( $time_current > $time_end ) {
						// time left
						$output['expire'] = 1;
									
						// update status
						Cryptoniq_AJAX::order_status_update( $id, 'failed' );
					} elseif ( cryptoniq_check_txid( $tx['hash'], $id ) == 1 ) {
						// old transaction
						$output['expire'] = -1;
					}
				} else {
					if ( $tx['confirmations'] >= 1 ) {
						// update tx meta field
						cryptoniq_tx_data_update( $tx['hash'], $id );						
					}
					
                	if ( $tx['confirmations'] >= 6 && $tx['txreceipt_status'] == 1 ) {							
						$output['completed'] = 1;
									
						// update status
						Cryptoniq_AJAX::order_status_update( $id, 'processing' );

						$output['status'] = 1;
						$output['redirect'] = WC_Payment_Gateway::get_return_url( $order );
                	}
				}
           	}
       	}
    }
		
	if ( $time_current > $time_end && $output['found'] != 1 ) {
		// time left
		$output['expire'] = 1;
							
		// update status
		Cryptoniq_AJAX::order_status_update( $id, 'failed' );					
	}
 
	if ( $print != 'no' ) {
		print_r( json_encode( $output ) );
	}
}