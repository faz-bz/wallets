<?php

/**
 * Make the shortcodes also available as widgets.
 *
 */

if ( ! class_exists( 'Dashed_Slug_Wallets_Widget' ) ) {
	class Dashed_Slug_Wallets_Widget extends WP_Widget {

		private $widget;
		private $description;
		private $capabilities;
		private $views_dir;
		private $templates = array();

		public static function widgets_init() {
			register_widget( 'Dashed_Slug_Wallets_Widget_Deposit' );
			register_widget( 'Dashed_Slug_Wallets_Widget_Withdraw' );
			register_widget( 'Dashed_Slug_Wallets_Widget_Move' );
			register_widget( 'Dashed_Slug_Wallets_Widget_Balance' );
			register_widget( 'Dashed_Slug_Wallets_Widget_Transactions' );
			register_widget( 'Dashed_Slug_Wallets_Widget_AccountValue' );
		}

		/**
		 * Sets up the widgets name etc
		 */
		public function __construct( $widget, $title, $desc, $caps, $classname ) {
			$this->widget = $widget;
			$this->description = $desc;
			$this->capabilities = $caps;

			$this->views_dir = apply_filters( 'wallets_views_dir', __DIR__ . '/views' );
			$view = preg_replace( '/^wallets_/', '', $widget );
			$templates_dir = trailingslashit( $this->views_dir ) . $view;

			$this->templates = array_diff( scandir( $templates_dir ), array( '.', '..', 'index.php' ) );
			foreach ( $this->templates as &$template ) {
				$template = basename( $template, '.php' );
			}

			$widget_ops = array(
				'classname' => $classname,
				'description' => $desc,
			);

			parent::__construct(
				strtolower( $classname ),
				$title,
				$widget_ops
			);
		}

		/**
		 * Outputs the content of the widget
		 *
		 * @param array $args
		 * @param array $instance
		 */
		public function widget( $args, $instance ) {
			if ( is_user_logged_in() ) {
				$allowed = true;
				foreach ( $this->capabilities as $capability ) {
					$allowed = $allowed && current_user_can( $capability );
				}
				if ( false === array_search( $instance['template'], $this->templates ) ) {
					$instance['template'] = 'default';
				}

				if ( $allowed ): ?>
				<div class="widget widget-wallets widget-<?php echo str_replace( '_', '-', $this->widget ); ?>">
					<h3 class="widget-heading"><?php esc_html_e( $this->name, 'wallets' ); ?></h3>
					<?php echo do_shortcode( '[' . $this->widget . " template=\"{$instance['template']}\" views_dir=\"{$this->views_dir}\"]" ); ?>
				</div>
				<?php endif;
			}
		}

		/**
		 * Outputs the options form on admin
		 *
		 * @param array $instance The widget options
		 */
		public function form( $instance ) {
			if ( !isset( $instance['template'] ) ) {
				$instance['template'] = 'default';
			}

			?>
			<label>
				<?php esc_html_e( 'Template', 'wallets' ); ?>
				<select id="<?php echo $this->get_field_id('template'); ?>" name="<?php echo $this->get_field_name('template'); ?>" class="widefat" style="width:100%;">
					<?php foreach ( $this->templates as $template ): ?>
					<option <?php selected( $instance['template'], $template ); ?> value="<?php echo esc_attr( basename( $template ) ); ?>"><?php echo esc_html( $template ); ?></option>
					<?php endforeach; ?>
				</select>
			</label>
			<?php
		}

		/**
		 * Processing widget options on save
		 *
		 * @param array $new_instance The new options
		 * @param array $old_instance The previous options
		 */
		public function update( $new_instance, $old_instance ) {
			$instance['template'] = $new_instance['template'];
			return $instance;
		}
	}

	class Dashed_Slug_Wallets_Widget_Deposit extends Dashed_Slug_Wallets_Widget {
		public function __construct( ) {
			parent::__construct(
				'wallets_deposit',
				__( 'Deposit to wallet', 'wallets-front' ),
				__( 'A form that will let the user know which address they can send coins to if they wish to make a deposit.', 'wallets' ),
				array( 'has_wallets' ),
				__CLASS__
			);
		}
	}

	class Dashed_Slug_Wallets_Widget_Withdraw extends Dashed_Slug_Wallets_Widget {
		public function __construct( ) {
			parent::__construct(
				'wallets_withdraw',
				__( 'Withdraw from wallet', 'wallets-front' ),
				__( 'A form that will let the user withdraw funds.', 'wallets' ),
				array( 'has_wallets', 'withdraw_funds_from_wallet' ),
				__CLASS__
			);
		}
	}

	class Dashed_Slug_Wallets_Widget_Move extends Dashed_Slug_Wallets_Widget {
		public function __construct( ) {
			parent::__construct(
				'wallets_move',
				__( 'Transfer to user wallet', 'wallets-front' ),
				__( 'A form that lets the user transfer coins to other users on your site.', 'wallets' ),
				array( 'has_wallets', 'send_funds_to_user' ),
				__CLASS__
			);
		}
	}

	class Dashed_Slug_Wallets_Widget_Balance extends Dashed_Slug_Wallets_Widget {
		public function __construct( ) {
			parent::__construct(
				'wallets_balance',
				__( 'Wallet balance', 'wallets-front' ),
				__( "The current user's balances in all enabled coins.", 'wallets' ),
				array( 'has_wallets' ),
				__CLASS__
			);
		}
	}

	class Dashed_Slug_Wallets_Widget_Transactions extends Dashed_Slug_Wallets_Widget {
		public function __construct( ) {
			parent::__construct(
				'wallets_transactions',
				__( 'Wallet transactions', 'wallets-front' ),
				__( 'An interactive table that shows past deposits, withdrawals and transfers for the user.', 'wallets' ),
				array( 'has_wallets', 'list_wallet_transactions' ),
				__CLASS__
			);
		}
	}

	class Dashed_Slug_Wallets_Widget_AccountValue extends Dashed_Slug_Wallets_Widget {
		public function __construct( ) {
			parent::__construct(
				'wallets_account_value',
				__( 'Account value', 'wallets-front' ),
				__( 'Shows the account\'s total value expressed in the default fiat currency.', 'wallets' ),
				array( 'has_wallets' ),
				__CLASS__
			);
		}
	}

	// bind all widgets
	add_action( 'widgets_init', 'Dashed_Slug_Wallets_Widget::widgets_init' );

} // end if class exists

