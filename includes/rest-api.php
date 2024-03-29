<?php

namespace MonoclePocRestApi;

class Rest_Api {
	public function __construct() {}

	/**
	 * Register hooks for the REST API.
	 */
	public static function register() {
		$rest_api = new self();

		\add_action( 'rest_api_init', [ $rest_api, 'register_routes' ] );
		\add_filter( 'rest_pre_dispatch', [ $rest_api, 'rest_pre_dispatch' ], 999, 3 );
	}

	/**
	 * Register the custom routes for the REST API.
	 */
	public function register_routes() {
		register_rest_route(
			MON_POC_REST_NAMESPACE,
			'/whoami',
			[
				'methods'  => 'GET',
				'callback' => [ $this, 'whoami' ],
				'permission_callback' => [ $this, 'permissions_whoami' ],
			]
		);

		register_rest_route(
			MON_POC_REST_NAMESPACE,
			'/posts',
			[
				'methods'  => 'GET',
				'callback' => [ $this, 'get_posts' ],
				'permission_callback' => [ $this, 'permissions_posts' ],
			]
		);

		register_rest_route(
			MON_POC_REST_NAMESPACE,
			'/post/(?P<id>\d+)',
			[
				'methods'  => 'GET',
				'callback' => [ $this, 'get_post' ],
				'permission_callback' => [ $this, 'permissions_posts' ],
				'args'			=> [
					'id' => [
						'type'        => 'integer',
						'description' => esc_html__( 'Post ID', 'weeg-pro' ),
						'required'    => true,
						'sanitize_callback' => function( $param ) {
							return absint( $param );
						}
					]
				]
			]
		);
	}

	/**
	 * Sets the permissions for the post/posts endpoint.
	 *
	 * @param \WP_REST_Request $request
	 * @return boolean
	 */
	public function permissions_posts( $request ) {
		// Change this to the capability you want to use to check if the user has a valid digital subscription.
		return current_user_can( 'read_monocle_digital' );
	}

	/**
	 * Get a list of posts.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function get_posts( $request ) {
		$args = [
			'post_type' => 'post',
			'posts_per_page' => 20,
		];

		$query = new \WP_Query( $args );
		$posts = $query->get_posts();

		$response = [];
		foreach ( $posts as $post ) {
			$response[] = [
				'ID' => $post->ID,
				'post_title' => $post->post_title,
				'post_excerpt' => \get_the_excerpt( $post ),
				'post_date' => $post->post_date,
				'post_author' => get_the_author_meta( 'display_name', $post->post_author ),
			];
		}

		return rest_ensure_response( $response );
	}

	/**
	 * Get a single post.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function get_post( $request ) {
		$id = $request->get_param( 'id' );

		$post = \get_post( $id );
		if ( ! $post || is_wp_error( $post ) ) {
			return new \WP_Error( 'monocle_rest_post_not_found',
				'Post not found',
				[ 'status' => 404 ]
			);
		}

		$response = [
			'ID' => $post->ID,
			'post_title' => $post->post_title,
			'post_content' => $this->parse_content( $post->post_content ),
			'post_date' => $post->post_date,
			'post_author' => $post->post_author,
			'featured_image' => \get_the_post_thumbnail_url( $post, 'full' ),
		];

		return rest_ensure_response( $response );
	}

	/**
	 * Checks the permissions for the whoami endpoint.
	 *
	 * @param \WP_REST_Request $request
	 * @return boolean
	 */
	public function permissions_whoami( $request ) {
		return current_user_can( 'read' );
	}

	/**
	 * Get the current user information.
	 *
	 * @param \WP_REST_Request $request
	 * @return \WP_REST_Response
	 */
	public function whoami( $request ) {
		$user = wp_get_current_user();
		$response = [
			'ID' => $user->ID,
			'user_email' => $user->user_email,
			'display_name' => $user->display_name,
			// Add more user information here like subscription status, etc.
		];

		return rest_ensure_response( $response );
	}

	/**
	 * Removes permissions for the default WP routes.
	 *
	 * @param mixed            $result Rest result.
	 * @param \WP_REST_Server  $srv The rest server object.
	 * @param \WP_REST_Request $request The rest request object.
	 */
	public function rest_pre_dispatch( mixed $result, \WP_REST_Server $srv, \WP_REST_Request $request ): mixed {
		$method = $request->get_method();
		$path   = $request->get_route();

		if ( \preg_match( '!^/wp/v2(?:$|/)!', strtolower( $path ) ) ) {
			if ( ! \current_user_can( 'edit_pages' ) ) {
				return new \WP_Error( 'monocle_rest_cannot_view',
					'Sorry, you are not allowed to use this API.',
					[ 'status' => \rest_authorization_required_code() ]
				);
			}
		}

		return $result;
	}

	/**
	 * Parse the content of a post to feed a mobile app.
	 *
	 * @param string $content
	 * @return array
	 */
	public function parse_content( $content ) {
		$blocks = \parse_blocks( $content );

		$output = [];
		foreach ( $blocks as $block ) {
			if ( empty( $block['blockName'] ) ) {
				continue;
			}

			// Strip all tags except the allowed ones form the core blocks
			if ( in_array( $block['blockName'], [ 'core/paragraph',  'core/heading' ], true ) ) {
				$block['innerHTML'] = \strip_tags( trim( $block['innerHTML'] ), '<strong><a><em><br>' );
			}

			// Includes more attributes (e.g. src, alt) for the image block (core/image)
			if ( 'core/image' === $block['blockName'] && ! empty( $block['attrs']['id'] ) ) {
				// Include the image source in the attributes
				$block['attrs']['src'] = \wp_get_attachment_image_url( $block['attrs']['id'], 'full' );

				// Include the image alt in the attributes
				$raw = new \WP_HTML_Tag_Processor( $block['innerHTML'] );
				if ( $raw->next_tag('img') ) {
					$custom_alt = $raw->get_attribute( 'alt' );
					$block['attrs']['alt'] = ! empty( $custom_alt ) ? $custom_alt : \get_post_meta( $block['attrs']['id'], '_wp_attachment_image_alt', true );
				}
			}

			// Include the content for the related articles block (monocle/related-articles)
			if ( 'monocle/related-articles' === $block['blockName'] && ! empty( $block['attrs']['posts'] ) ) {
				$related_posts = [];
				foreach ( $block['attrs']['posts'] as $post_id ) {
					$post = \get_post( $post_id );
					if ( ! $post || is_wp_error( $post ) ) {
						continue;
					}

					$related_posts[] = [
						'ID' => $post->ID,
						'post_title' => $post->post_title,
						'post_excerpt' => \get_the_excerpt( $post ),
						'post_date' => $post->post_date,
						'post_author' => get_the_author_meta( 'display_name', $post->post_author ),
						'featured_image' => \get_the_post_thumbnail_url( $post, 'full' ),
					];
				}

				$block['related_posts'] = $related_posts;
			}

			$output[] = $block;
		}

		return $output;
	}

}