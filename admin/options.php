<?php

if ( !defined( 'ABSPATH' ) ) {
	exit();
}

if ( !class_exists( 'Redux' ) ) {
	return;
}

if ( !empty( Redux::getOption( 'cryptoniq_option', 'cryptoniq_panel_activate' ) ) && Redux::getOption( 'cryptoniq_option', 'cryptoniq_panel_activate' ) != 'yes' ) {
	return;
}

$opt_name = 'cryptoniq_option';
$img_dir = CRYPTONIQ_DIR_URL . 'admin/assets/images';

// Set Arguments
// ======================================================

Redux::setArgs( $opt_name,array(
	'opt_name' => $opt_name,
	'display_name' => 'Cryptoniq Lite',
	'display_version' => CRYPTONIQ_VER,
	'menu_type' => 'menu',
	'allow_sub_menu' => true,
	'menu_title' => 'Cryptoniq',
	'page_title' => 'Cryptoniq',
	'google_api_key' => '',
	'google_update_weekly' => false,
	'async_typography' => true,
	'admin_bar' => true,
	'admin_bar_icon' => 'dashicons-portfolio',
	'admin_bar_priority' => 51,
	'global_variable' => '',
	'dev_mode' => false,
	'show_options_object' => false,
	'update_notice' => true,
	'customizer' => false,
	'page_priority' => 51,
	'page_parent' => 'themes.php',
	'page_permissions' => 'manage_options',
	'menu_icon' => '',
	'last_tab' => '',
	'page_icon' => 'icon-themes',
	'page_slug' => 'cryptoniq',
	'save_defaults' => true,
	'default_show' => false,
	'default_mark' => '',
	'show_import_export' => true,
	'transient_time' => 60 * MINUTE_IN_SECONDS,
	'output' => true,
	'output_tag' => false,
	'database' => '',
	'use_cdn' => true,
	'hints' => array(
		'icon' => 'el el-question-sign',
		'icon_position' => 'right',
		'icon_color' => 'lightgray',
		'icon_size' => 'normal',
		'tip_style' => array(
			'color' => 'red',
			'shadow' => true,
			'rounded' => false,
			'style' => '',
		),
		'tip_position' => array(
			'my' => 'top left',
			'at' => 'bottom right',
		),
		'tip_effect' => array(
			'show' => array(
				'effect' => 'slide',
				'duration' => '500',
				'event' => 'mouseover',
			) ,
			'hide' => array(
				'effect' => 'slide',
				'duration' => '500',
				'event' => 'click mouseleave',
			),
		),
	)
) );

// Add Sections
// ======================================================

Redux::setSection( $opt_name, array(
	'title' => esc_html__( 'Payment', 'cryptoniq' ),
	'id' => 'tab_payment',
	'icon' => 'icon ion-md-cart',
	'fields' => array(
		array(
			'id' => 'payment_coins',
			'type' => 'select',
			'multi' => true,
			'sortable' => true,
			'title' => esc_html__( 'Payment Coins', 'cryptoniq' ),
			'options' => array(
				'ETH' => 'ETH'
			) ,
			'default' => array(	'ETH' )
		),
		array(
			'id' => 'order_status',
			'type' => 'select',
			'title' => esc_html__( 'Order Status', 'cryptoniq' ),
			'subtitle' => esc_html__( 'Select status for order after completed payment', 'cryptoniq' ),
			'options' => array(
				'processing' => 'Processing',
				'completed' => 'Completed',
				'on-hold' => 'On Hold'
			) ,
			'default' => 'processing',
		),
	)
) );

Redux::setSection( $opt_name, array(
	'title' => esc_html__( 'Wallets', 'cryptoniq' ),
	'desc' => sprintf( esc_html__( 'Add your wallets addresses for each payment coin. Try to add as much as possible. Read more %1$s.', 'cryptoniq' ) , '<a href="https://divengine.ticksy.com/article/13482/" target="_blank">' . esc_html__( 'here', 'cryptoniq' ) . '</a>' ),
	'id' => 'tab_wallets',
	'icon' => 'icon ion-md-wallet',
	'fields' => array(
		array(
			'id' => 'wallets_eth',
			'type' => 'multi_text',
			'title' => esc_html__( 'Wallet', 'cryptoniq' ) . ': ETH',
			'subtitle' => esc_html__( 'Add your ETH addresses (at least 10-20).', 'cryptoniq' ),
			'add_text' => esc_html__( 'Add wallet', 'cryptoniq' ),
		)
	)
) );

Redux::setSection( $opt_name, array(
	'title' => esc_html__( 'Titles', 'cryptoniq' ),
	'id' => 'tab_titles',
	'icon' => 'icon ion-md-list-box',
	'fields' => array(
		array(
			'id' => 'description',
			'type' => 'textarea',
			'title' => esc_html__( 'Description', 'cryptoniq' ),
			'subtitle' => esc_html__( 'Write some description for this payment type. It will be shown in \'Checkout\' page.', 'cryptoniq' ),
			'default' => esc_html__( 'Pay with cryptocurrencies.', 'cryptoniq' ),
			'validate' => 'not_empty',
		),
	)
) );

Redux::setSection( $opt_name, array(
	'title' => esc_html__( 'Colors', 'cryptoniq' ),
	'id' => 'tab_colors',
	'icon' => 'icon ion-md-color-fill',
	'fields' => array(
		array(
			'id' => 'pg_bar_tx_errors',
			'type' => 'color',
			'title' => esc_html__( 'Progress bar: Errors', 'cryptoniq' ),
			'subtitle' => sprintf( esc_html__( 'When there is an error during the process. %1$s', 'cryptoniq' ), '<a href="' . $img_dir . '/cryptoniq.pgbar.errors.png" target="_blank">' . esc_html__( 'example!', 'cryptoniq' ) . '</a>' ),
			'default'  => '#bf4141',
			'transparent' => false,
			'validate' => 'color',
		),
		array(
			'id' => 'pg_bar_tx_checking',
			'type' => 'color',
			'title' => esc_html__( 'Progress bar: Waiting for a transaction', 'cryptoniq' ),
			'subtitle' => sprintf( esc_html__( 'When waitng for a transaction to be registered in a blockchain. %1$s', 'cryptoniq' ), '<a href="' . $img_dir . '/cryptoniq.pgbar.waiting.png" target="_blank">' . esc_html__( 'example!', 'cryptoniq' ) . '</a>' ),
			'default'  => '#536782',
			'transparent' => false,
			'validate' => 'color',
		),
		array(
			'id' => 'pg_bar_tx_found',
			'type' => 'color',
			'title' => esc_html__( 'Progress bar: Transaction is found', 'cryptoniq' ),
			'subtitle' => sprintf( esc_html__( 'When the transaction is found and have 0 confirmations. %1$s', 'cryptoniq' ), '<a href="' . $img_dir . '/cryptoniq.pgbar.processing.png" target="_blank">' . esc_html__( 'example!', 'cryptoniq' ) . '</a>' ),
			'default'  => '#509c76',
			'transparent' => false,
			'validate' => 'color',
		),
		array(
			'id' => 'pg_bar_tx_conf',
			'type' => 'color',
			'title' => esc_html__( 'Progress bar: Transaction confirmations', 'cryptoniq' ),
			'subtitle' => sprintf( esc_html__( 'When the transaction has at least 1 confirmation. %1$s', 'cryptoniq' ), '<a href="' . $img_dir . '/cryptoniq.pgbar.confirming.png" target="_blank">' . esc_html__( 'example!', 'cryptoniq' ) . '</a>' ),
			'default'  => '#2c7c54',
			'transparent' => false,
			'validate' => 'color',
		),
		array(
			'id' => 'pg_bar_payment_completed',
			'type' => 'color',
			'title' => esc_html__( 'Progress bar: Payment completed', 'cryptoniq' ),
			'subtitle' => sprintf( esc_html__( 'When payment completed (not optional). %1$s', 'cryptoniq' ), '<a href="' . $img_dir . '/cryptoniq.pgbar.completed.png" target="_blank">' . esc_html__( 'example!', 'cryptoniq' ) . '</a>' ),
			'default'  => '#2c7c54',
			'transparent' => false,
			'validate' => 'color',
		),
	)
) );

Redux::setSection( $opt_name, array(
	'title' => esc_html__( 'Api Keys', 'cryptoniq' ),
	'id' => 'tab_keys',
	'icon' => 'icon ion-md-key',
	'fields' => array(
		array(
			'id' => 'apikey_eth',
			'type' => 'text',
			'title' => esc_html__( 'ETH Api Key', 'cryptoniq' ),
			'subtitle' => sprintf( esc_html__( 'See how to get api-key %1$s.', 'cryptoniq' ), '<a href="https://divengine.ticksy.com/article/13483/" target="_blank">' . esc_html__( 'here', 'cryptoniq' ) . '</a>' )
		),
	)
) );

Redux::setSection( $opt_name, array(
	'title' => esc_html__( 'Documentation', 'cryptoniq' ),
	'id' => 'tab_doc',
	'icon' => 'icon ion-md-book',
	'desc' => sprintf( esc_html__( 'For documentation please visit %1$s.', 'cryptoniq' ) , '<a href="https://divengine.ticksy.com/articles/100013199/" target="_blank">' . esc_html__( 'here', 'cryptoniq' ) . '</a>' )
) );

Redux::setSection( $opt_name, array(
	'title' => esc_html__( 'PRO', 'cryptoniq' ),
	'id' => 'tab_pro',
	'icon' => 'icon ion-md-unlock',
	'desc' => sprintf( esc_html__( 'Get %1$s version and unlock all awesome features.', 'cryptoniq' ), '<a href="https://codecanyon.net/item/cryptoniq-cryptocurrency-payment-plugin-for-wordpress/22419379?rel=DivEngine" target="_blank"><strong>' . esc_html__( 'PRO', 'cryptoniq' ) . '</strong></a>' ) . '</br>' . '<ul><li>Muliple Coins</li><li>Coin Prices near product prices</li><li>6 Month Support</li><li>Lifetime Updates</li><li>& Much more...</li></ul>' . '<a href="https://codecanyon.net/item/cryptoniq-cryptocurrency-payment-plugin-for-wordpress/22419379?rel=DivEngine" target="_blank"><strong>' . esc_html__( 'Buy Now!', 'cryptoniq' ) . '</strong></a>'
) );

Redux::setSection( $opt_name, array(
	'title' => esc_html__( 'Import / Export', 'cryptoniq' ),
	'id' => 'tab_import_export',
	'icon' => 'icon ion-md-git-compare',
	'fields' => array(
		array(
			'id' => 'opt-import-export',
			'type' => 'import_export',
			'full_width' => true,
		),
	),
) );


// Add custom icons
// ======================================================

function cryptoniq_custom_icons()
{
	wp_enqueue_style( 'cryptoniq-libs-ionicons' );
}
add_action( 'redux/page/' . $opt_name . '/enqueue', 'cryptoniq_custom_icons' );