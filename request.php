<?php

function cryptoniq_remote_get( $wallet = '' ) {
	$api_change = cryptoniq_get_option( 'apikey_eth' );
	$api_def = 'Z3RSPYD5E7Y84C7REJEA4HPVGFWW85CXTD';
	$key = $api_change ? $api_change : $api_def; 
	
	$url = 'http://api.etherscan.io/api?module=account&action=txlist&sort=desc&page=1&offset=100&apikey=' . $key . '&address=' . $wallet;
    
	$request = wp_remote_get( $url, array( 
		'timeout' => 300,
		'redirection' => 5,
		'sslverify' => false,
		'user-agent' => get_bloginfo( 'url' )
	) );
	
	return $request;
}