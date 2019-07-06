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
	 * Add a sub menu under settings for plugin specific settings
	 *
	 * @since    1.0.0
	 */
	public function add_settings_menu() {

		add_submenu_page(
			'options-general.php',
			__( 'Virgool', 'virgool' ),
			__( 'Virgool Settings', 'virgool' ),
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

		require_once plugin_dir_path( __FILE__ ) . 'partials/virgool-admin-settings.php';

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

		if ( ! extension_loaded( 'openssl' ) ) {
			$value = isset( $options[ $id ] ) ? sanitize_text_field( $options[ $id ] ) : '';
		} else {
			$value = isset( $options[ $id ] ) ? openssl_decrypt( $options[ $id ], 'AES-256-CBC', AUTH_KEY ) : '';
		}

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
	 * @since    1.0.0
	 */
	public function callback_validate_options( $input ) {

		if ( isset( $input['username'] ) ) {
			$input['username'] = sanitize_text_field(
				$input['username']
			);
		}

		if ( isset( $input['password'] ) ) {
			if ( ! extension_loaded( 'openssl' ) ) {
				$input['password'] = sanitize_text_field(
					$input['password']
				);
			} else {
				$input['password'] = openssl_encrypt(
					$input['password'],
					'AES-256-CBC',
					AUTH_KEY
				);
			}
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
	 * Cross posts to the Virgool on save posts
	 *
	 * @param int     $post_ID Post ID.
	 * @param WP_Post $post Post object.
	 *
	 * @since 1.0.0
	 */
	public function save_post( $post_ID, $post ) {

	}
}
