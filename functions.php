<?php 

if ( !defined( 'ABSPATH' ) ) {
	exit();
}

// Get Panel Options
// ======================================================

function cryptoniq_get_option( $var = '' ) { 		
	if ( !class_exists( 'Redux' ) ) {
		return;
	}
		
	if ( !empty( Redux::getOption( CRYPTONIQ_OPTION, 'cryptoniq_panel_activate' ) ) && Redux::getOption( CRYPTONIQ_OPTION, 'cryptoniq_panel_activate' ) != 'yes' ) {
		return;
	}

	return !empty( Redux::getOption( CRYPTONIQ_OPTION, $var ) ) ? Redux::getOption( CRYPTONIQ_OPTION, $var ) : '';
}

// Set Custom Template For Payment Type
// ======================================================

function cryptoniq_set_custom_template( $located, $template_name, $args, $template_path, $default_path ) {
	$data = $args['order']->data;
	$pay_method = $data['payment_method'];
    
	if ( 'checkout/order-receipt.php' == $template_name && $pay_method == CRYPTONIQ_PAY_ID ) {
		$located = CRYPTONIQ_DIR_PATH . 'payment.php';
	}
    
	return $located;
}
add_filter( 'wc_get_template', 'cryptoniq_set_custom_template', 10, 5 );

// Update Order Transaction Data
// ======================================================

function cryptoniq_tx_data_update( $txid = '', $id = '' ) {
	if ( !$txid || !$id ) {
		return;
	}
	
	if ( empty( get_post_meta( $id, 'cryptoniq_tx', true ) ) ) {
		update_post_meta( $id, 'cryptoniq_tx', $txid );
							
		// update txlist option
		Cryptoniq_AJAX::options_txlist_update( $txid, $id );							
	}
}

// Check if Transaction ID exists
// ======================================================

function cryptoniq_check_txid( $txid = '', $id = '' ) { 		
	$answer = '';
	$tx_list = get_option( 'cryptoniq_pay_txlist' );
		
	if ( is_array( $tx_list ) && array_key_exists( $txid, $tx_list ) ) {
		$answer = 1;
			
		if ( $id != null && $id == $tx_list[$txid] ) {
			$answer = '';
		}
	}
		
	return $answer;
}


// Update Meta Fields
// ======================================================

function cryptoniq_update_order_meta_fields( $order_id ) {	
	$coin = 'ETH';
	$wallets = cryptoniq_get_option( 'wallets_eth' );

	// Get wallet address
	$wallet =  preg_replace( '/\s+/', '', $wallets[array_rand( $wallets )] );	

	$order = wc_get_order( $order_id );
	$total = wp_kses_post( $order->get_total() );
	
	// Calc total coin price
	$total_coin_price = cryptoniq_get_price( $coin, $total, '', 'yes' );
	
	date_default_timezone_set('UTC');
	$time = time();
	
	// Update order meta fields
	update_post_meta( $order_id, 'cryptoniq_coin_name', $coin );
	update_post_meta( $order_id, 'cryptoniq_coin_amount', $total_coin_price );	
	update_post_meta( $order_id, 'cryptoniq_wallet', $wallet );
	update_post_meta( $order_id, 'cryptoniq_time_start', $time );
}
add_action( 'woocommerce_checkout_update_order_meta', 'cryptoniq_update_order_meta_fields' );

// Add Custom Data To Header
// ======================================================

function cryptoniq_add_custom_header_data() {
?>

<script>
var cryptoniq_paybox_notes = {
	found: '<?php esc_html_e( 'Transaction is found', 'cryptoniq' ); ?>',
	waiting: '<?php esc_html_e( 'Waiting for transaction...', 'cryptoniq' ); ?>',
	expire: {
		old: '<?php esc_html_e( "That is an old transaction", "cryptoniq" ); ?>',
		new: '<?php esc_html_e( 'Transaction is expired. Payment failed', 'cryptoniq' ); ?>'
	},
	check: '<?php esc_html_e( 'Checking...', 'cryptoniq' ); ?>',
	process: '<?php esc_html_e( 'Processing...', 'cryptoniq' ); ?>',
	done: '<?php esc_html_e( 'Completed', 'cryptoniq' ); ?>',
	redirect: '<?php esc_html_e( 'Redirecting...', 'cryptoniq' ); ?>',
	error: '<?php esc_html_e( 'Error. Try to refresh the page', 'cryptoniq' ); ?>',
	txlink: {
		eth: 'https://etherscan.io/tx/'
	}
}; 
</script>		


<?php if ( cryptoniq_get_option('pg_bar_tx_errors') || cryptoniq_get_option('pg_bar_tx_checking') || cryptoniq_get_option('pg_bar_tx_found') || cryptoniq_get_option('pg_bar_tx_conf') || cryptoniq_get_option('pg_bar_payment_completed') ) { ?>
<style>
<?php if ( cryptoniq_get_option('pg_bar_tx_errors') ) { ?>
.cryptoniq-paybox-tx-checking-error {
	background: <?php echo cryptoniq_get_option('pg_bar_tx_errors'); ?>
}
<?php } ?>
	
<?php if ( cryptoniq_get_option('pg_bar_tx_checking') ) { ?>
.cryptoniq-paybox-tx-form {
	background: <?php echo cryptoniq_get_option('pg_bar_tx_checking'); ?>
}
<?php } ?>
	
<?php if ( cryptoniq_get_option('pg_bar_tx_found') ) { ?>
.cryptoniq-paybox-tx-form.cryptoniq-paybox-tx-checking-found {
	background: <?php echo cryptoniq_get_option('pg_bar_tx_found'); ?>
}
<?php } ?>
	
<?php if ( cryptoniq_get_option('pg_bar_tx_conf') ) { ?>
.cryptoniq-paybox-tx-form.cryptoniq-paybox-tx-checking-found .cryptoniq-paybox-pg-bar-width {
	background: <?php echo cryptoniq_get_option('pg_bar_tx_conf'); ?>
}
<?php } ?>
	
<?php if ( cryptoniq_get_option('pg_bar_payment_completed') ) { ?>
.cryptoniq-paybox-tx-form.cryptoniq-paybox-tx-checking-done {
	background: <?php echo cryptoniq_get_option('pg_bar_payment_completed'); ?>
}
<?php } ?>
</style>
<?php } ?>

<?php
}
add_action( 'wp_head', 'cryptoniq_add_custom_header_data' );

// Add Payment Data (Coin) To Success Page
// ======================================================

function cryptoniq_data_thankyou( $id ) {
	$order = wc_get_order( $id );
	
	$coin = get_post_meta( $id, 'cryptoniq_coin_name', true );
	$amount = get_post_meta( $id, 'cryptoniq_coin_amount', true );
	$wallet = get_post_meta( $id, 'cryptoniq_wallet', true );
	$txid = get_post_meta( $id, 'cryptoniq_tx', true );
?>
 
	<section class="cryptoniq-woocommerce-order-details">
		<h2 class="woocommerce-order-details__title">Cryptoniq</h2>
		<table class="woocommerce-table woocommerce-table--order-details shop_table order_details">
			<tr>
				<th><?php esc_html_e( 'Coin', 'cryptoniq' ); ?>:</th>
				<th class="woocommerce-table__product-table product-total"><?php echo $coin; ?></th>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Total:', 'cryptoniq' ); ?></th>
				<td><i class="cryptoniq-product-list-icon cryptoniq-product-list-icon-eth"></i><?php echo $amount; ?></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Address:', 'cryptoniq' ); ?></th>
				<td><?php echo $wallet; ?></td>
			</tr>
			<tr>
				<th scope="row"><?php esc_html_e( 'Transaction:', 'cryptoniq' ); ?></th>
				<td><?php echo $txid; ?></td>
			</tr>
		</table>
	</section>

<?php
}
add_action( 'woocommerce_thankyou_cryptoniq', 'cryptoniq_data_thankyou' );

// Set Custom Hold Time For Products
// ======================================================

function cryptoniq_custom_hold_time() {
	$time = '180';
	$duration = get_option( 'woocommerce_hold_stock_minutes' );

	if ( get_option( 'woocommerce_manage_stock' ) != 'yes' || $duration > $time ) {
		return;
	}
	
	update_option( 'woocommerce_hold_stock_minutes', $time );
}
add_action( 'admin_init', 'cryptoniq_custom_hold_time' );