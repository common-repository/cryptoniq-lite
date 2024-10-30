<?php

if ( !defined( 'ABSPATH' ) ) {
	exit();
}

add_filter( 'woocommerce_cart_subtotal', 'cryptoniq_subtotal_filter', 10, 3 );
add_filter( 'woocommerce_cart_total', 'cryptoniq_total_filter', 10, 3 );
	
// Get coin product infos
// ======================================================

function cryptoniq_cpi( $select = 'coin', $type = 'text', $tag = 'no', $divider = '/', $method = '' ) {
	$coin = 'ETH';
	$divider_final = '<span class="cryptoniq-product-divider">' . $divider . '</span>';
	
    $tag_class = 'cryptoniq-coin-tag';
		
	if ( $select == 'divider' ) {
		$data = $divider_final;
	} else {
		if ( $type == 'icon' ) {
			$data = '<i class="cryptoniq-product-list-icon cryptoniq-product-list-icon-' . strtolower( $coin ) . '"></i>';
		} elseif ( $type == 'none' ) {
			$data = '';
		} elseif ( $type == 'text_space' ) {
			$data = ( $tag == 'yes' ) ? '<span class="' . $tag_class . '">' . $coin . '</span> ' : $coin . ' ';
		}  elseif ( $type == 'text_dots' ) {
			$data = ( $tag == 'yes' ) ? '<span class="' . $tag_class . '">' . $coin . '</span>: ' : $coin . ': ';
		} else {
			$data = ( $tag == 'yes' ) ? '<span class="' . $tag_class . '">' . $coin . '</span>' : $coin;
		}
	}
	
	return $data;
}

function cryptoniq_ctags( $set = 'type' ) {
	$type = 'icon';
	if ( $type == 'text' ) $type = 'text_space';	
	
	if ( $set = 'type' ) {
		return $type;
	} else {
		return ( $type == 'text_space' ) ? 'yes' : 'no';	
	}
}

function cryptoniq_subtotal_filter( $price ) {
	$divider = '/';
	$divider_final = '<b class="cryptoniq-product-divider">' . $divider . '</b>';
		
    $price .= $divider_final . cryptoniq_cpi( 'coin', cryptoniq_ctags( 'type' ), cryptoniq_ctags( 'tag' ) ) . cryptoniq_get_price( cryptoniq_cpi( 'coin', 'text' ), WC()->cart->subtotal );
		
    return $price;
}

function cryptoniq_total_filter( $price ) {
	$divider = '/';
	$divider_final = '<b class="cryptoniq-product-divider">' . $divider . '</b>';
		
    $price .= $divider_final . cryptoniq_cpi( 'coin', cryptoniq_ctags( 'type' ), cryptoniq_ctags( 'tag' ) ) . cryptoniq_get_price( cryptoniq_cpi( 'coin', 'text' ), WC()->cart->total );
		
    return $price;
}