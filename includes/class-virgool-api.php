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
	 * @var      string
	 */
	private $headers;

	/**
	 * Login using username and passwords and put token to the headers for future requests
	 *
	 * @param    string $username username of virgool account.
	 * @param    string $password password of virgool account.
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

}
