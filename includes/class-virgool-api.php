<?php
/**
 * Define the api communications functionality
 *
 * Loads and defines the api communications files for this plugin
 *
 * @link       https://milad.nekofar.com
 * @since      1.0.0
 *
 * @package    Virgool
 * @subpackage Virgool/includes
 */

/**
 * Define the api communications functionality.
 *
 * Loads and defines the api communications files for this plugin
 *
 * @since      1.0.0
 * @package    Virgool
 * @subpackage Virgool/includes
 * @author     Milad Nekofar <milad@nekofar.com>
 */
class Virgool_Api {

	/**
	 * Base URL of Virgool api.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string
	 */
	private $base_url = 'https://virgool.io/api/v1.2';

	/**
	 * Custom headers for requests.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string[]
	 */
	private $headers;

	/**
	 * Login using username and passwords and put token to the headers for future requests
	 *
	 * @param string $username username of virgool account.
	 * @param string $password password of virgool account.
	 *
	 * @return   WP_Error|bool
	 * @since    1.0.0
	 */
	public function login( $username, $password ) {
		$response = wp_remote_post(
			$this->base_url . '/login',
			[
				'body' => [
					'username' => $username,
					'password' => $password,
				],
			]
		);

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $body ) || ! $data['success'] ) {
			return new WP_Error( 'login_failed', __( 'Login to the virgool api has been failed.', 'virgool' ) );
		}

		$this->headers = [
			'Authorization' => 'Bearer ' . $data['token'],
			'Content-Type'  => 'application/json; charset=utf-8',
		];

		return true;
	}

	/**
	 * Retrieve current user information from api.
	 *
	 * @return   WP_Error|array
	 * @since    1.0.0
	 */
	public function retrieve_user_info() {
		$response = wp_remote_get(
			$this->base_url . '/user/info',
			[
				'headers' => $this->headers,
			]
		);

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $body ) || ! $data['success'] ) {
			return new WP_Error( 'retrieve_user_info', __( 'Retrieve user info has been failed.', 'virgool' ) );
		}

		return $data['user'];
	}

	/**
	 * Retrieve current user posts based on their publish status.
	 *
	 * @param string $status status of posts on virgool to filter them.
	 *
	 * @return   WP_Error|array
	 * @since    1.0.0
	 */
	public function retrieve_user_posts( $status = 'draft' ) {
		if ( ! in_array( $status, [ 'draft', 'publish' ], true ) ) {
			return new WP_Error( 'retrieve_user_posts_status', __( 'Wrong post status has been selected.', 'virgool' ) );
		}

		$status = 'publish' === $status ? 'published' : 'drafts';

		$response = wp_remote_get(
			$this->base_url . '/posts/' . $status,
			[
				'headers' => $this->headers,
			]
		);

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $body ) || ! $data['success'] ) {
			return new WP_Error( 'retrieve_user_posts', __( 'Retrieve user posts has been failed.', 'virgool' ) );
		}

		return $data['data'];
	}

	/**
	 * Create a new published or draft post using array of data for current user.
	 *
	 * @param array  $data post data for virgool.
	 * @param string $status status of cross post.
	 *
	 * @return   WP_Error|array
	 * @since    1.0.0
	 */
	public function create_user_post( $data = [], $status = 'draft' ) {
		if ( in_array( $status, [ 'draft', 'publish' ], true ) === false ) {
			return new WP_Error( 'create_user_post_status', __( 'Wrong post status has been selected.', 'virgool' ) );
		}

		$data = [
			'hash'           => $data['hash'],
			'title'          => $data['title'],
			'tag'            => $data['tag'],
			'body'           => $data['body'],
			'primary_img'    => $data['primary_img'],
			'post_id'        => '',
			'og_description' => null,
		];

		$response = wp_remote_post(
			$this->base_url . '/editor/' . $status,
			[
				'headers' => $this->headers,
				'body'    => wp_json_encode( $data ),
			]
		);

		$body = wp_remote_retrieve_body( $response );
		$data = json_decode( $body, true );

		if ( empty( $body ) || ! $data['success'] ) {
			return new WP_Error( 'create_user_post', __( 'Create user post has been failed.', 'virgool' ) );
		}

		return $data['data'];
	}

	/**
	 * Upload given file path to the virgool.
	 *
	 * @param string $file_path Path to the file for upload.
	 *
	 * @param string $folder_name Post hash as folder name.
	 *
	 * @return null|string
	 */
	public function upload_primary_image( $file_path, $folder_name ) {
		WP_Filesystem();
		global $wp_filesystem;

		// path to a local file on your server.
		if ( file_exists( $file_path ) === false ) {
			$file_path = VIRGOOL_PLUGIN_DIR . 'tmp/upload.jpg';
		}

		$boundary = sprintf( '--%s', wp_generate_password( 10, false ) );

		$this->headers['Content-Type'] = 'multipart/form-data; boundary=' . $boundary;

		$data = '';

		// First, add the standard POST fields.
		$data .= '--' . $boundary;
		$data .= "\r\n";
		$data .= 'Content-Disposition: form-data; name="foldername"' . "\r\n\r\n";
		$data .= $folder_name;
		$data .= "\r\n";

		// Upload the file.
		$data .= '--' . $boundary . "\r\n";
		$data .= 'Content-Disposition: form-data; name="upload"; filename="' . basename( $file_path ) . "\r\n";
		$data .= 'Content-Type: image/jpeg' . "\r\n";
		$data .= "\r\n";
		$data .= $wp_filesystem->get_contents( $file_path );
		$data .= "\r\n";

		$data .= '--' . $boundary . '--';

		$response = wp_remote_post(
			$this->base_url . '/post/upload/',
			[
				'body'    => $data,
				'headers' => $this->headers,
			]
		);

		$body = wp_remote_retrieve_body( $response );
		if ( empty( $body ) === true ) {
			return new WP_Error( 'upload_primary_image', __( 'Upload primary image has been failed.', 'virgool' ) );
		}

		$data = json_decode( $body, true );
		if ( isset( $data['success'] ) && false === $data['success'] ) {
			return new WP_Error( 'upload_primary_image', __( 'Upload primary image has been failed.', 'virgool' ) );
		}

		return $data;
	}

}
