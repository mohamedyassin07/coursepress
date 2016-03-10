<?php

class CoursePress_View_Front_Instructor {

	public static $discussion = false;  // Used for hooking discussion filters
	public static $title = ''; // The page title
	public static $last_instructor;

	public static function init() {

		add_action( 'parse_request', array( __CLASS__, 'parse_request' ) );

		/**
		 * Intercep virtual page when dealing with invitation code.
		 **/
		add_filter( 'coursepress_virtual_page', array( __CLASS__, 'instructor_verification' ), 10, 2 );

	}

	public static function render_instructor_page() {
		if ( $theme_file = locate_template( array( 'instructor-single.php' ) ) ) {
		} else {
			// wp_enqueue_style( 'front_course_single', $this->plugin_url . 'css/front_course_single.css', array(), $this->version );
			if ( locate_template( array( 'instructor-single.php' ) ) ) {// add custom content in the single template ONLY if the post type doesn't already has its own template
				// just output the content
			} else {

				$content = CoursePress_Template_User::render_instructor_page();

			}
		}

		return $content;
	}


	public static function parse_request( &$wp ) {

		if ( array_key_exists( 'instructor_username', $wp->query_vars ) ) {

			$username = sanitize_text_field( $wp->query_vars['instructor_username'] );
			$instructor = CoursePress_Data_Instructor::instructor_by_login( $username );
			if ( empty( $instructor ) ) {
				$instructor = CoursePress_Data_Instructor::instructor_by_hash( $username );
			}
			$content = '';
			if ( empty( $instructor ) ) {
				$content = __( 'The requested instuctor does not exists', 'CP_TD' );
			}

			self::$last_instructor = empty( $instructor ) ? 0 : $instructor->ID;

			$page_title = ! empty( self::$last_instructor ) ? CoursePress_Helper_Utility::get_user_name( self::$last_instructor, false, false ) : __( 'Instructor not found.', 'CP_TD' );
			$args = array(
				'slug' => 'instructor_' . self::$last_instructor,
				'title' => $page_title,
				'content' => ! empty( $content ) ? esc_html( $content ) : self::render_instructor_page(),
				'type' => 'coursepress_instructor',
			);

			$pg = new CoursePress_Data_VirtualPage( $args );

			return;

		}
	}

	public static function instructor_verification( $_vp_args, $cp ) {
		if( $course_invite = CoursePress_Data_Instructor::is_course_invite() ) {
			$is_verified = CoursePress_Data_Instructor::verify_invitation_code( $course_invite->course_id, $course_invite->code, $course_invite->invitation_data );
			$vp_args = array(
				'slug' => 'instructor_verification' . $course_invite->course_id,
				'type' => CoursePress_Data_Course::get_post_type_name() . '_archive',
				'is_page' => true,
			);

			if( $is_verified ) {
				if( ! is_user_logged_in() ) {
					add_filter( 'coursepress_localize_object', array( 'CoursePress_Data_Instructor', 'invitation_data' ) );
					add_action( 'wp_footer', array( __CLASS__, 'modal_view' ) );
					$vp_args = $_vp_args;
				} else {
					$user_id = get_current_user_id();
					$is_added = CoursePress_Data_Instructor::add_from_invitation( $course_invite->course_id, $user_id, $course_invite->code );

					if( $is_added ) { 
						$vp_args = wp_parse_args( array(
							'show_title' => true,
							'title' => get_the_title( $course_invite->course_id ),
							'content' => sprintf( '<h3>%s</h3><p>%s</p>',
								esc_html__( 'Invitation activated.', 'CP_TD' ),
								esc_html__( 'Congratulations. You are now an instructor of this course. ', 'CP_TD' )
							)
							. apply_filters( 'coursepress_view_course',
								CoursePress_View_Front_Course::render_course_main(),
								$course_invite->course_id,
								'main'
							),
						), $vp_args );
					} else {
						$vp_args = wp_parse_args( array(
							'show_title' => false,
							'content' => sprintf( '<h3>%s</h3><p>%s</p><p>%s</p>',
								esc_html__( 'Invalid invitation.', 'CP_TD' ),
								esc_html__( 'This invitation link is not associated with your email address.', 'CP_TD' ),
								esc_html__( 'Please contact your course administator and ask them to send a new invitation to the email address that you have associated with your account.', 'CP_TD' )
							)
						), $vp_args );
					}

				}
			} else {
				$vp_args = wp_parse_args( array(
					'show_title' => false,
					'content' => sprintf( '<h3>%s</h3><p>%s</p><p>%s</p>',
						esc_html__( 'Invitation not found.', 'CP_TD' ),
						esc_html__( 'This invitation could not be found or is no longer available.', 'CP_TD' ),
						esc_html__( 'Please contact us if you believe this to be an error.', 'CP_TD' )
					),
				), $vp_args );
			}

		} else {
			$vp_args = $_vp_args;
		}

		return $vp_args;
	}

	public static function modal_view() {
		$invite_data = CoursePress_Data_Instructor::is_course_invite();
		?>
		<script type="text/template" id="modal-view4-template" data-type="modal-step" data-modal-action="instructor-verified">
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title"><?php esc_html_e( 'Invitation activated.', 'CP_TD' ); ?></h3>
			</div>
			<div class="bbm-modal__section">
				<p><?php esc_html_e( 'Congratulations. You are now an instructor of this course. ', 'CP_TD' ); ?></p>
			</div>
			<div class="bbm-modal__bottombar">
				<a href="<?php echo esc_url( get_permalink( $invite_data->course_id ) ); ?>" class="bbm-button button"><?php esc_html_e( 'Continue...', 'CP_TD' ); ?></a>
			</div>
		</script>

		<script type="text/template" id="modal-view5-template" data-type="modal-step" data-modal-action="verification-failed">
			<div class="bbm-modal__topbar">
				<h3 class="bbm-modal__title"><?php esc_html_e( 'Invalid invitation.', 'CP_TD' ); ?></h3>
			</div>
			<div class="bbm-modal__section">
				<p><?php esc_html_e( 'This invitation link is not associated with your email address.', 'CP_TD'  ); ?></p>
				<p><?php esc_html_e( 'Please contact your course administator and ask them to send a new invitation to the email address that you have associated with your account.', 'CP_TD' ); ?></p>
			</div>
			<div class="bbm-modal__bottombar">
				<a href="<?php echo esc_url( get_permalink( $invite_data->course_id ) ); ?>" class="bbm-button button"><?php esc_html_e( 'Continue...', 'CP_TD' ); ?></a>
			</div>
		</script>
		<?php
	}
}
