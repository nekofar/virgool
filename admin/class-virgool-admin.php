<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://milad.nekofar.com
 * @since      1.0.0
 *
 * @package    Virgool
 * @subpackage Virgool/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Virgool
 * @subpackage Virgool/admin
 * @author     Milad Nekofar <milad@nekofar.com>
 */
class Virgool_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $plugin_name The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string $version The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @param string $plugin_name The name of this plugin.
	 * @param string $version The version of this plugin.
	 *
	 * @since    1.0.0
	 */
	public function __construct( $plugin_name, $version ) {
		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Add the action links displayed for plugin in the Plugins list table.
	 *
	 * @param string[] $actions An array of plugin action links.
	 * @param string   $plugin_file Path to the plugin file relative to the plugins directory.
	 *
	 * @return array
	 */
	public function add_action_links( $actions, $plugin_file ) {
		static $plugin;

		if ( ! isset( $plugin ) ) {
			$plugin = plugin_basename( VIRGOOL_PLUGIN_FILE );
		}

		if ( $plugin === $plugin_file ) {
			$actions = array_merge(
				[
					'settings' => '<a href="options-general.php?page=virgool">' . __( 'Settings', 'virgool' ) . '</a>',
					'support'  => '<a href="http://github.com/nekofar/virgool/issues" target="_blank">' . __( 'Support', 'virgool' ) . '</a>',
				],
				$actions
			);
		}

		return $actions;
	}

	/**
	 * Add a sub menu under settings for plugin specific settings
	 *
	 * @since    1.0.0
	 */
	public function add_settings_menu() {
		add_submenu_page(
			'options-general.php',
			__( 'Virgool Settings', 'virgool' ),
			__( 'Virgool', 'virgool' ),
			'manage_options',
			'virgool',
			[ $this, 'add_settings_page' ]
		);
	}

	/**
	 * Settings page template
	 *
	 * @since    1.0.0
	 */
	public function add_settings_page() {
		require_once VIRGOOL_PLUGIN_DIR . 'admin/partials/virgool-admin-settings.php';
	}


	/**
	 * Register settings
	 *
	 * @since    1.0.0
	 */
	public function register_settings() {
		register_setting(
			$this->plugin_name . '_options',
			$this->plugin_name . '_options',
			[ $this, 'callback_validate_options' ]
		);

		add_settings_section(
			$this->plugin_name . '_section_login',
			__( 'Login Information', 'virgool' ),
			[ $this, 'callback_section_login' ],
			'virgool'
		);

		add_settings_section(
			$this->plugin_name . '_section_publish',
			__( 'Publish Settings', 'virgool' ),
			[ $this, 'callback_section_publish' ],
			'virgool'
		);

		add_settings_field(
			'username',
			__( 'Username', 'virgool' ),
			[ $this, 'callback_field_text' ],
			$this->plugin_name,
			$this->plugin_name . '_section_login',
			[
				'id'    => 'username',
				'label' => __( 'The username you use to enter the Virgool.', 'virgool' ),
			]
		);

		add_settings_field(
			'password',
			__( 'Password', 'virgool' ),
			[ $this, 'callback_field_password' ],
			$this->plugin_name,
			$this->plugin_name . '_section_login',
			[
				'id'    => 'password',
				'label' => __( 'The password you use to enter the Virgool.', 'virgool' ),
			]
		);

		add_settings_field(
			'status',
			__( 'Status', 'virgool' ),
			[ $this, 'callback_field_select' ],
			$this->plugin_name,
			$this->plugin_name . '_section_publish',
			[
				'id'    => 'status',
				'label' => __( 'Default status of the publication of the contents sent to the comma.', 'virgool' ),
			]
		);
	}

	/**
	 * Callback for settings page login section
	 *
	 * @since    1.0.0
	 */
	public function callback_section_login() {
		echo '<p>' . esc_html__( 'This plugin uses your login information to communicate with the Virgool. Please enter your login information as requested.', 'virgool' ) . '</p>';
	}

	/**
	 * Callback for settings page publish section
	 *
	 * @since    1.0.0
	 */
	public function callback_section_publish() {
		echo '<p>' . esc_attr__( 'Using this section, you can choose the options for publishing the content in the Virgool.', 'virgool' ) . '</p>';
	}

	/**
	 * Callback for settings page text fields
	 *
	 * @param array $args Array of field parameters.
	 *
	 * @since    1.0.0
	 */
	public function callback_field_text( $args ) {
		$options = get_option( $this->plugin_name . '_options' );

		$id    = isset( $args['id'] ) ? esc_html( $args['id'] ) : '';
		$label = isset( $args['label'] ) ? $args['label'] : '';

		$value = isset( $options[ $id ] ) ? sanitize_text_field( $options[ $id ] ) : '';

		?>
		<label for="<?php echo esc_html( $this->plugin_name . '_options_' . $id ); ?>">
			<input type="text" size="40" value="<?php echo esc_html( $value ); ?>"
				id="<?php echo esc_html( $this->plugin_name . '_options_' . $id ); ?>"
				name="<?php echo esc_html( $this->plugin_name . '_options[' . $id . ']' ); ?>"/>
			<p class="description">
				<?php echo esc_html( $label ); ?>
			</p>
		</label>
		<?php
	}

	/**
	 * Callback for settings page password fields
	 *
	 * @param array $args Array of field parameters.
	 *
	 * @since    1.0.0
	 */
	public function callback_field_password( $args ) {
		$options = get_option( $this->plugin_name . '_options' );

		$id    = isset( $args['id'] ) ? $args['id'] : '';
		$label = isset( $args['label'] ) ? $args['label'] : '';

		$value = isset( $options[ $id ] ) ? sanitize_text_field( $options[ $id ] ) : '';

		?>
		<label for="<?php echo esc_html( $this->plugin_name . '_options_' . $id ); ?>">
			<input type="password" size="40" value="<?php echo esc_html( $value ); ?>"
				id="<?php echo esc_html( $this->plugin_name . '_options_' . $id ); ?>"
				name="<?php echo esc_html( $this->plugin_name . '_options[' . $id . ']' ); ?>"/>
			<p class="description">
				<?php echo esc_html( $label ); ?>
			</p>
		</label>
		<?php
	}

	/**
	 * Callback for settings page select fields
	 *
	 * @param array $args Array of field parameters.
	 *
	 * @since    1.0.0
	 */
	public function callback_field_select( $args ) {
		$options = get_option( $this->plugin_name . '_options' );

		$id    = isset( $args['id'] ) ? $args['id'] : '';
		$label = isset( $args['label'] ) ? $args['label'] : '';

		$selected_option = isset( $options[ $id ] ) ? sanitize_text_field( $options[ $id ] ) : '';

		$select_options = [
			'draft'     => __( 'Draft', 'virgool' ),
			'published' => __( 'Published', 'virgool' ),
		];
		?>
		<label for="<?php echo esc_html( $this->plugin_name . '_options_' . $id ); ?>">
			<select id="<?php echo esc_html( $this->plugin_name . '_options_' . $id ); ?>"
				name="<?php echo esc_html( $this->plugin_name . '_options[' . $id . ']' ); ?>">
				<?php foreach ( $select_options as $value => $option ) : ?>
					<?php $selected = selected( $selected_option === $value, true, false ); ?>
					<option value="<?php echo esc_html( $value ); ?>"<?php echo esc_html( $selected ); ?>>
						<?php echo esc_html( $option ); ?>
					</option>
				<?php endforeach; ?>
			</select>
			<p class="description">
				<?php echo esc_html( $label ); ?>
			</p>
		</label>
		<?php
	}

	/**
	 * Callback for validate option field input before store in database
	 *
	 * @param array $input Input value came from setting field.
	 *
	 * @return array
	 *
	 * @since    1.0.0
	 */
	public function callback_validate_options( $input ) {
		if ( isset( $input['username'] ) ) {
			$input['username'] = sanitize_text_field( $input['username'] );
		}

		if ( isset( $input['password'] ) ) {
			$input['password'] = sanitize_text_field( $input['password'] );
		}

		$select_options = [
			'draft'     => __( 'Draft', 'virgool' ),
			'published' => __( 'Published', 'virgool' ),
		];

		if ( ! isset( $input['status'] ) ) {
			$input['status'] = null;
		}

		if ( ! array_key_exists( $input['status'], $select_options ) ) {
			$input['status'] = null;
		}

		return $input;
	}

	/**
	 * Add custom actions to the list of Bulk Actions drop-down.
	 *
	 * @param string[] $actions An array of the available bulk actions.
	 *
	 * @return string[] $actions An array of the available bulk actions.
	 */
	public function add_bulk_actions( $actions ) {
		$actions = array_merge(
			$actions,
			[
				'cross_post_virgool' => __( 'Cross post to the Virgool', 'virgool' ),
			]
		);

		return $actions;
	}

	/**
	 * Fires when a custom bulk action should be handled.
	 *
	 * @param string $redirect_to The redirect URL.
	 * @param string $doaction The action being taken.
	 * @param array  $post_ids The posts ids to take the action on.
	 *
	 * @return mixed
	 */
	public function handle_bulk_actions( $redirect_to, $doaction, $post_ids ) {
		if ( in_array( $doaction, [ 'cross_post_virgool' ], true ) === false ) {
			return $redirect_to;
		}

		foreach ( $post_ids as $post_id ) {
			$post = get_post( $post_id );

			// Send post to the virgool and receive created post info.
			$virgool_post = $this->cross_post( $post );

			// Save virgool post data associated with the post.
			update_post_meta( $post_id, 'virgool_post', $virgool_post );
		}

		$redirect_to = add_query_arg( 'bulk_cross_posts', count( $post_ids ), $redirect_to );

		// Create an nonce, and add it as a query var in a link to perform an action.
		$redirect_to = add_query_arg( '_wpnonce', wp_create_nonce( 'bulk_cross_posts' ), $redirect_to );

		return $redirect_to;
	}

	/**
	 * Print bulk actions notices.
	 */
	public function bulk_action_admin_notices() {
		if ( ! isset( $_GET['_wpnonce'] ) ) {
			return;
		}

		if ( ! wp_verify_nonce( sanitize_text_field( wp_unslash( $_GET['_wpnonce'] ) ), 'bulk_cross_posts' ) ) {
			return;
		}

		if ( empty( $_GET['bulk_cross_posts'] ) === false ) {
			$crossed_count = intval( $_GET['bulk_cross_posts'] );
			printf(
				sprintf(
					'<div id="message" class="updated fade">%s</div>',
					/* translators: %s: number of posts crossed. */
					esc_html( _n( 'Crossed %s post to Virgool.', 'Crossed %s posts to Virgool.', esc_html( $crossed_count ), 'virgool' ) )
				),
				esc_html( $crossed_count )
			);
		}
	}

	/**
	 * Cross posts to the Virgool on publish regular posts
	 *
	 * @param int     $post_ID Post ID.
	 * @param WP_Post $post Post object.
	 *
	 * @since 1.0.0
	 */
	public function publish_post( $post_ID, $post ) {

		// Check if virgool post info already exist.
		$virgool_post = get_post_meta( $post_ID, 'virgool_post', true );
		if ( empty( $virgool_post ) === false ) {
			return;
		}

		// Send post to the virgool and receive created post info.
		$virgool_post = $this->cross_post( $post );

		// Save virgool post data associated with the post.
		update_post_meta( $post_ID, 'virgool_post', $virgool_post );
	}

	/**
	 * Create a post on Virgool
	 *
	 * @param WP_Post $post Post object.
	 *
	 * @return array|WP_Error
	 *
	 * @since 1.0.0
	 */
	private function cross_post( $post ) {

		// Retrieve list of post tags.
		$tags = wp_get_post_tags( $post->ID, [ 'fields' => 'names' ] );
		$hash = $this->generate_hash( 12 );

		$virgool_api = new Virgool_Api();

		$virgool_post = $virgool_api->create_user_post(
			[
				'hash'           => $hash,
				'title'          => $post->post_title,
				'tag'            => $tags,
				'body'           => $post->post_content,
				'slug'           => $post->post_name . '-' . $hash,
				'primary_img'    => '',
				'post_id'        => '',
				'og_description' => null,
			],
			'draft'
		);

		return $virgool_post;
	}

	/**
	 * Generate random string to use as virgool post hash.
	 *
	 * @param int $length the length of generated sting.
	 *
	 * @return string
	 *
	 * @since 1.0.0
	 */
	private function generate_hash( $length = 10 ) {
		$characters        = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$characters_length = strlen( $characters );
		$random_string     = '';
		for ( $i = 0; $i < $length; $i ++ ) {
			$random_string .= $characters[ wp_rand( 0, $characters_length - 1 ) ];
		}

		return $random_string;
	}
}
