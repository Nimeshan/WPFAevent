<?php
/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://fossasia.org
 * @since      1.0.0
 *
 * @package    Wpfaevent
 * @subpackage Wpfaevent/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Wpfaevent
 * @subpackage Wpfaevent/admin
 * @author     FOSSASIA <contact@fossasia.org>
 */
class Wpfaevent_Admin {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.0
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Eventyay REST API import service.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var Wpfaevent_Eventyay_Importer
	 */
	private $eventyay_importer;

	/**
	 * Eventyay JSON:API dashboard sync service.
	 *
	 * @since 1.0.0
	 * @access private
	 * @var Wpfaevent_Eventyay_Ajax_Sync
	 */
	private $eventyay_ajax_sync;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.0
	 * @param      string $plugin_name       The name of this plugin.
	 * @param      string $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version     = $version;
	}

	/**
	 * Get the Eventyay REST API import service.
	 *
	 * @since 1.0.0
	 *
	 * @return Wpfaevent_Eventyay_Importer
	 */
	private function get_eventyay_importer() {
		if ( ! $this->eventyay_importer instanceof Wpfaevent_Eventyay_Importer ) {
			$this->eventyay_importer = new Wpfaevent_Eventyay_Importer();
		}

		return $this->eventyay_importer;
	}

	/**
	 * Get the Eventyay JSON:API dashboard sync service.
	 *
	 * @since 1.0.0
	 *
	 * @return Wpfaevent_Eventyay_Ajax_Sync
	 */
	private function get_eventyay_ajax_sync() {
		if ( ! $this->eventyay_ajax_sync instanceof Wpfaevent_Eventyay_Ajax_Sync ) {
			$this->eventyay_ajax_sync = new Wpfaevent_Eventyay_Ajax_Sync();
		}

		return $this->eventyay_ajax_sync;
	}

	/**
	 * Register the stylesheets for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpfaevent_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpfaevent_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'css/wpfaevent-admin.css', array(), $this->version, 'all' );
	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Wpfaevent_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Wpfaevent_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name . '-admin', plugin_dir_url( __FILE__ ) . 'js/wpfaevent-admin.js', array( 'jquery' ), $this->version, false );
	}

	/**
	 * Add a settings link to the plugin action links.
	 *
	 * @since    1.0.0
	 * @param    array $links Existing plugin action links.
	 * @return   array Modified plugin action links.
	 */
	public function add_settings_link( $links ) {
		$settings_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'admin.php?page=wpfaevent-settings' ) ),
			esc_html__( 'Settings', 'wpfaevent' )
		);

		$import_link = sprintf(
			'<a href="%s">%s</a>',
			esc_url( admin_url( 'edit.php?post_type=wpfa_event&page=wpfaevent-import-events' ) ),
			esc_html__( 'Import Events', 'wpfaevent' )
		);

		array_unshift( $links, $settings_link, $import_link );

		return $links;
	}

	/**
	 * Register the plugin settings menu and Eventyay import page.
	 *
	 * @since    1.0.0
	 */
	public function register_settings_page() {
		add_menu_page(
			esc_html__( 'WPFAEvent Settings', 'wpfaevent' ),
			esc_html__( 'WPFAEvent', 'wpfaevent' ),
			Wpfaevent_Roles::CAP_MANAGE_SETTINGS,
			'wpfaevent-settings',
			array( $this, 'render_plugin_settings_page' ),
			'dashicons-calendar-alt',
			30
		);

		add_submenu_page(
			'wpfaevent-settings',
			esc_html__( 'WPFAEvent Settings', 'wpfaevent' ),
			esc_html__( 'Settings', 'wpfaevent' ),
			Wpfaevent_Roles::CAP_MANAGE_SETTINGS,
			'wpfaevent-settings',
			array( $this, 'render_plugin_settings_page' )
		);

		add_submenu_page(
			'edit.php?post_type=wpfa_event',
			esc_html__( 'Import Events from Eventyay', 'wpfaevent' ),
			esc_html__( 'Import Events', 'wpfaevent' ),
			Wpfaevent_Roles::CAP_IMPORT_EVENTYAY,
			'wpfaevent-import-events',
			array( $this, 'render_settings_page' )
		);

		add_submenu_page(
			'edit.php?post_type=wpfa_event',
			esc_html__( 'Update Events from Eventyay', 'wpfaevent' ),
			esc_html__( 'Update Events', 'wpfaevent' ),
			Wpfaevent_Roles::CAP_IMPORT_EVENTYAY,
			'wpfaevent-update-events',
			array( $this, 'render_update_events_page' )
		);

		add_submenu_page(
			'edit.php?post_type=wpfa_speaker',
			esc_html__( 'Event Speaker Lists', 'wpfaevent' ),
			esc_html__( 'Events', 'wpfaevent' ),
			'edit_posts',
			'wpfaevent-speaker-events',
			array( $this, 'render_speaker_events_page' )
		);

		add_submenu_page(
			'edit.php?post_type=wpfa_speaker',
			esc_html__( 'Speaker Dashboard', 'wpfaevent' ),
			esc_html__( 'Dashboard', 'wpfaevent' ),
			'edit_speakers',
			'wpfa-speaker-dashboard',
			array( $this, 'render_speaker_dashboard_page' )
		);
	}

	/**
	 * Render the plugin settings placeholder page.
	 *
	 * @since 1.0.0
	 */
	public function render_plugin_settings_page() {
		if ( ! Wpfaevent_Roles::current_user_can_manage_settings() ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpfaevent' ) );
		}

		$can_manage_access = Wpfaevent_Roles::current_user_can_manage_plugin_access();
		?>
		<div class="wrap">
			<h1><?php echo esc_html( get_admin_page_title() ); ?></h1>

			<?php if ( $can_manage_access ) : ?>
				<?php settings_errors( Wpfaevent_Roles::SETTINGS_GROUP ); ?>
				<div class="card" style="max-width: 960px;">
					<h2><?php esc_html_e( 'Event Plugin Access', 'wpfaevent' ); ?></h2>
					<p><?php esc_html_e( 'Assign Event Organizer or Event Contributor access to existing WordPress users. Their normal WordPress role stays unchanged.', 'wpfaevent' ); ?></p>
					<p class="description"><?php esc_html_e( 'Administrators always have full plugin access. Organizers can import and publish. Contributors can edit existing event and speaker content only.', 'wpfaevent' ); ?></p>

					<form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
						<?php
						settings_fields( Wpfaevent_Roles::SETTINGS_GROUP );
						$this->render_user_access_settings_fields();
						submit_button( __( 'Save Event Plugin Access', 'wpfaevent' ) );
						?>
					</form>
				</div>
			<?php endif; ?>

			<div class="card" style="max-width: 960px;">
				<h2><?php esc_html_e( 'WPFAEvent Settings', 'wpfaevent' ); ?></h2>
				<p><?php esc_html_e( 'This page is reserved for the future WPFAEvent admin dashboard and shared plugin settings.', 'wpfaevent' ); ?></p>
				<?php if ( Wpfaevent_Roles::current_user_can_import_eventyay() ) : ?>
					<p>
						<a class="button button-primary" href="<?php echo esc_url( admin_url( 'edit.php?post_type=wpfa_event&page=wpfaevent-import-events' ) ); ?>">
							<?php esc_html_e( 'Open Eventyay Import', 'wpfaevent' ); ?>
						</a>
					</p>
				<?php endif; ?>
			</div>

			<div class="card" style="max-width: 960px;">
				<h2><?php esc_html_e( 'Where Imported Eventyay Data Appears', 'wpfaevent' ); ?></h2>
				<table class="widefat striped">
					<tbody>
						<tr>
							<th scope="row"><?php esc_html_e( 'Events', 'wpfaevent' ); ?></th>
							<td><?php esc_html_e( 'Created or updated as Events posts, with date, location, Eventyay URL, and Eventyay source metadata saved as post meta.', 'wpfaevent' ); ?></td>
						</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Speakers', 'wpfaevent' ); ?></th>
								<td><?php esc_html_e( 'Created or updated as Speaker posts and linked to the imported event through event-specific relationship meta.', 'wpfaevent' ); ?></td>
							</tr>
							<tr>
								<th scope="row"><?php esc_html_e( 'Dashboard files', 'wpfaevent' ); ?></th>
								<td><?php esc_html_e( 'Event dashboard data is written under uploads/fossasia-data using event-specific files such as speakers-{event_id}.json, schedule-{event_id}.json, sponsors-{event_id}.json, exhibitors-{event_id}.json, and site-settings-{event_id}.json.', 'wpfaevent' ); ?></td>
							</tr>
						<tr>
							<th scope="row"><?php esc_html_e( 'Frontend', 'wpfaevent' ); ?></th>
							<td><?php esc_html_e( 'Imported data appears on the event detail page and on the event-filtered speakers page, for example /speakers/?event={event-slug}.', 'wpfaevent' ); ?></td>
						</tr>
					</tbody>
				</table>
			</div>
		</div>
		<?php
	}

	/**
	 * Render an event chooser under the Speakers admin menu.
	 *
	 * @since 1.0.0
	 */
	public function render_speaker_events_page() {
		if ( ! current_user_can( 'edit_posts' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to view event speaker lists.', 'wpfaevent' ) );
		}

		$events = get_posts(
			array(
				'post_type'      => 'wpfa_event',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);
		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Event Speaker Lists', 'wpfaevent' ); ?></h1>
			<p><?php esc_html_e( 'Choose an event to view the speakers linked only to that event.', 'wpfaevent' ); ?></p>

			<?php if ( empty( $events ) ) : ?>
				<div class="notice notice-info">
					<p><?php esc_html_e( 'No events found yet.', 'wpfaevent' ); ?></p>
				</div>
			<?php else : ?>
				<table class="widefat striped">
					<thead>
						<tr>
							<th scope="col"><?php esc_html_e( 'Event', 'wpfaevent' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Status', 'wpfaevent' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Speakers', 'wpfaevent' ); ?></th>
							<th scope="col"><?php esc_html_e( 'Actions', 'wpfaevent' ); ?></th>
						</tr>
					</thead>
					<tbody>
						<?php foreach ( $events as $event ) : ?>
							<?php
							$speaker_ids    = $this->get_admin_event_speaker_ids( $event->ID );
							$speaker_count  = count( $speaker_ids );
							$speakers_url   = add_query_arg(
								array(
									'post_type'          => 'wpfa_speaker',
									'wpfa_speaker_event' => absint( $event->ID ),
								),
								admin_url( 'edit.php' )
							);
							$edit_event_url = get_edit_post_link( $event->ID, '' );
							$status_object  = get_post_status_object( get_post_status( $event ) );
							$status_label   = $status_object ? $status_object->label : get_post_status( $event );
							?>
							<tr>
								<td>
									<strong>
										<a href="<?php echo esc_url( $speakers_url ); ?>">
											<?php echo esc_html( get_the_title( $event ) ); ?>
										</a>
									</strong>
								</td>
								<td><?php echo esc_html( $status_label ); ?></td>
								<td>
									<?php
									echo esc_html(
										sprintf(
											/* translators: %s: Speaker count. */
											_n( '%s speaker', '%s speakers', $speaker_count, 'wpfaevent' ),
											number_format_i18n( $speaker_count )
										)
									);
									?>
								</td>
								<td>
									<a class="button button-small" href="<?php echo esc_url( $speakers_url ); ?>">
										<?php esc_html_e( 'View Speakers', 'wpfaevent' ); ?>
									</a>
									<?php if ( $edit_event_url ) : ?>
										<a class="button button-small" href="<?php echo esc_url( $edit_event_url ); ?>">
											<?php esc_html_e( 'Edit Event', 'wpfaevent' ); ?>
										</a>
									<?php endif; ?>
								</td>
							</tr>
						<?php endforeach; ?>
					</tbody>
				</table>
			<?php endif; ?>
		</div>
		<?php
	}

	/**
	 * Register plugin settings stored under WPFAEvent -> Settings.
	 *
	 * @since 1.0.0
	 */
	public function register_plugin_settings() {
		register_setting(
			Wpfaevent_Roles::SETTINGS_GROUP,
			Wpfaevent_Roles::ACCESS_LEVELS_OPTION,
			array(
				'type'              => 'array',
				'sanitize_callback' => array( 'Wpfaevent_Roles', 'sanitize_user_access_levels' ),
				'default'           => array(),
			)
		);
	}

	/**
	 * Render the per-user plugin access assignment table.
	 *
	 * @since 1.0.0
	 */
	private function render_user_access_settings_fields() {
		$access_labels   = Wpfaevent_Roles::get_access_level_labels();
		$assigned_levels = Wpfaevent_Roles::get_user_access_levels();
		$users           = get_users(
			array(
				'orderby' => 'display_name',
				'order'   => 'ASC',
			)
		);

		if ( empty( $users ) ) {
			echo '<p>' . esc_html__( 'No WordPress users are available to assign.', 'wpfaevent' ) . '</p>';
			return;
		}

		$this->render_user_access_level_guide();
		?>
		<table class="widefat striped" style="max-width: 960px;">
			<thead>
				<tr>
					<th scope="col"><?php esc_html_e( 'User', 'wpfaevent' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Email', 'wpfaevent' ); ?></th>
					<th scope="col"><?php esc_html_e( 'WordPress role', 'wpfaevent' ); ?></th>
					<th scope="col"><?php esc_html_e( 'Event plugin access', 'wpfaevent' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<?php foreach ( $users as $user ) : ?>
					<?php
					$user_id        = absint( $user->ID );
					$is_admin       = Wpfaevent_Roles::user_is_site_administrator( $user );
					$role_names     = array_map( 'translate_user_role', array_filter( (array) $user->roles ) );
					$wordpress_role = ! empty( $role_names ) ? implode( ', ', $role_names ) : __( 'No role', 'wpfaevent' );
					$field_name     = Wpfaevent_Roles::ACCESS_LEVELS_OPTION . '[' . $user_id . ']';
					$current_level  = $is_admin ? '' : ( $assigned_levels[ $user_id ] ?? '' );
					?>
					<tr>
						<td><strong><?php echo esc_html( $user->display_name ); ?></strong></td>
						<td><?php echo esc_html( $user->user_email ); ?></td>
						<td><?php echo esc_html( $wordpress_role ); ?></td>
						<td>
							<?php if ( $is_admin ) : ?>
								<em><?php esc_html_e( 'Full access (Administrator)', 'wpfaevent' ); ?></em>
							<?php else : ?>
								<label class="screen-reader-text" for="<?php echo esc_attr( 'wpfaevent-access-' . $user_id ); ?>">
									<?php
									printf(
										/* translators: %s: user display name. */
										esc_html__( 'Event plugin access for %s', 'wpfaevent' ),
										esc_html( $user->display_name )
									);
									?>
								</label>
								<select id="<?php echo esc_attr( 'wpfaevent-access-' . $user_id ); ?>" name="<?php echo esc_attr( $field_name ); ?>">
									<?php foreach ( $access_labels as $level => $label ) : ?>
										<option value="<?php echo esc_attr( $level ); ?>" <?php selected( $current_level, $level ); ?>>
											<?php echo esc_html( $label ); ?>
										</option>
									<?php endforeach; ?>
								</select>
							<?php endif; ?>
						</td>
					</tr>
				<?php endforeach; ?>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Render the access-level reference guide shown above the assignment table.
	 *
	 * @since 1.0.0
	 */
	private function render_user_access_level_guide() {
		?>
		<h3 class="title" style="margin-top: 1.5em;"><?php esc_html_e( 'Access level guide', 'wpfaevent' ); ?></h3>
		<table class="widefat striped" style="max-width: 960px; margin-bottom: 1.5em;">
			<thead>
				<tr>
					<th scope="col" style="width: 28%;"><?php esc_html_e( 'Access level', 'wpfaevent' ); ?></th>
					<th scope="col"><?php esc_html_e( 'What they can do', 'wpfaevent' ); ?></th>
				</tr>
			</thead>
			<tbody>
				<tr>
					<td><strong><?php esc_html_e( 'Administrator', 'wpfaevent' ); ?></strong></td>
					<td><?php esc_html_e( 'Full plugin access automatically. Can import from Eventyay, publish and delete events and speakers, and open WPFAEvent settings.', 'wpfaevent' ); ?></td>
				</tr>
				<tr>
					<td><strong><?php echo esc_html( Wpfaevent_Roles::get_access_level_labels()[ Wpfaevent_Roles::ACCESS_ORGANIZER ] ); ?></strong></td>
					<td><?php esc_html_e( 'Import and sync events from Eventyay, publish events and speakers, delete content, and open WPFAEvent settings. Does not change their WordPress role.', 'wpfaevent' ); ?></td>
				</tr>
				<tr>
					<td><strong><?php echo esc_html( Wpfaevent_Roles::get_access_level_labels()[ Wpfaevent_Roles::ACCESS_CONTRIBUTOR ] ); ?></strong></td>
					<td><?php esc_html_e( 'Edit existing event and speaker content only. Cannot import, publish, delete, or change plugin settings.', 'wpfaevent' ); ?></td>
				</tr>
				<tr>
					<td><strong><?php echo esc_html( Wpfaevent_Roles::get_access_level_labels()[''] ); ?></strong></td>
					<td><?php esc_html_e( 'No access to WPFAEvent features. The user keeps their normal WordPress permissions only.', 'wpfaevent' ); ?></td>
				</tr>
			</tbody>
		</table>
		<?php
	}

	/**
	 * Register Eventyay import options.
	 *
	 * @since 1.0.0
	 */
	public function register_eventyay_import_settings() {
		register_setting(
			'wpfaevent_eventyay_import',
			'wpfaevent_eventyay_import_settings',
			array(
				'type'              => 'array',
				'sanitize_callback' => array( $this, 'sanitize_eventyay_import_settings' ),
				'default'           => $this->get_eventyay_importer()->get_eventyay_import_default_settings(),
				'autoload'          => false,
			)
		);
	}

	/**
	 * Render the event filter on the speaker admin list.
	 *
	 * Event-owned speakers are hidden from the default speaker list. This filter
	 * gives admins an event-scoped way to inspect those records when needed.
	 *
	 * @since 1.0.0
	 *
	 * @param string $post_type Current list-table post type.
	 * @return void
	 */
	public function render_speaker_event_filter( $post_type ) {
		if ( 'wpfa_speaker' !== $post_type ) {
			return;
		}

		$events = get_posts(
			array(
				'post_type'      => 'wpfa_event',
				'post_status'    => 'any',
				'posts_per_page' => -1,
				'orderby'        => 'title',
				'order'          => 'ASC',
				'no_found_rows'  => true,
			)
		);

		if ( empty( $events ) ) {
			return;
		}

		$selected_event_id = $this->get_selected_speaker_admin_event_id();
		?>
		<label class="screen-reader-text" for="wpfa_speaker_event">
			<?php esc_html_e( 'Filter speakers by event', 'wpfaevent' ); ?>
		</label>
		<select name="wpfa_speaker_event" id="wpfa_speaker_event">
			<option value=""><?php esc_html_e( 'Site speakers', 'wpfaevent' ); ?></option>
			<?php foreach ( $events as $event ) : ?>
				<option value="<?php echo esc_attr( $event->ID ); ?>" <?php selected( $selected_event_id, $event->ID ); ?>>
					<?php echo esc_html( sprintf( /* translators: %s: Event title. */ __( 'Event: %s', 'wpfaevent' ), get_the_title( $event ) ) ); ?>
				</option>
			<?php endforeach; ?>
		</select>
		<?php
	}

	/**
	 * Scope the speaker admin list to site speakers or one selected event.
	 *
	 * @since 1.0.0
	 *
	 * @param WP_Query $query Admin list-table query.
	 * @return void
	 */
	public function filter_speaker_admin_list( $query ) {
		if ( ! is_admin() || ! $query->is_main_query() ) {
			return;
		}

		$post_type = $query->get( 'post_type' );
		if ( is_array( $post_type ) || 'wpfa_speaker' !== $post_type ) {
			return;
		}

		$selected_event_id = $this->get_selected_speaker_admin_event_id();

		if ( $selected_event_id ) {
			$speaker_ids = $this->get_admin_event_speaker_ids( $selected_event_id );
			$query->set( 'post__in', ! empty( $speaker_ids ) ? $speaker_ids : array( 0 ) );
			return;
		}

		$event_owned_speaker_ids = $this->get_all_event_owned_speaker_ids();
		if ( ! empty( $event_owned_speaker_ids ) ) {
			$current_exclusions = $query->get( 'post__not_in' );
			$current_exclusions = is_array( $current_exclusions ) ? $current_exclusions : array();
			$query->set( 'post__not_in', Wpfaevent_Event_Speaker_Relation_Manager::sanitize_post_id_list( array_merge( $current_exclusions, $event_owned_speaker_ids ) ) );
		}

		$existing_meta_query = $query->get( 'meta_query' );
		$meta_query          = array(
			'relation' => 'AND',
			array(
				'key'     => 'wpfa_speaker_events',
				'compare' => 'NOT EXISTS',
			),
			array(
				'key'     => '_wpfa_eventyay_speaker_id',
				'compare' => 'NOT EXISTS',
			),
		);

		if ( ! empty( $existing_meta_query ) ) {
			$meta_query[] = $existing_meta_query;
		}

		// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Admin list filtering is intentionally based on speaker ownership meta.
		$query->set( 'meta_query', $meta_query );
	}

	/**
	 * Replace raw speaker CPT status tabs with scoped counts.
	 *
	 * @since 1.0.0
	 *
	 * @param array $views Existing list-table view links.
	 * @return array Scoped view links.
	 */
	public function filter_speaker_admin_views( $views ) {
		$selected_event_id = $this->get_selected_speaker_admin_event_id();
		$current_status    = filter_input( INPUT_GET, 'post_status', FILTER_SANITIZE_FULL_SPECIAL_CHARS );
		$current_status    = $current_status ? sanitize_key( $current_status ) : '';
		$base_args         = array(
			'post_type' => 'wpfa_speaker',
		);

		if ( $selected_event_id ) {
			$base_args['wpfa_speaker_event'] = $selected_event_id;
		}

		$scoped_views = array();
		$view_items   = array(
			'all'     => array(
				'label'  => $selected_event_id ? __( 'Event speakers', 'wpfaevent' ) : __( 'Site speakers', 'wpfaevent' ),
				'status' => '',
			),
			'publish' => array(
				'label'  => __( 'Published', 'wpfaevent' ),
				'status' => 'publish',
			),
			'trash'   => array(
				'label'  => __( 'Trash', 'wpfaevent' ),
				'status' => 'trash',
			),
		);

		foreach ( $view_items as $view_key => $view_item ) {
			$count = $this->count_scoped_speaker_admin_posts( $selected_event_id, $view_item['status'] );

			if ( 'trash' === $view_key && 0 === $count && empty( $views['trash'] ) ) {
				continue;
			}

			$url_args = $base_args;
			if ( $view_item['status'] ) {
				$url_args['post_status'] = $view_item['status'];
			}

			$is_current = $view_item['status'] === $current_status || ( '' === $view_item['status'] && '' === $current_status );

			$scoped_views[ $view_key ] = sprintf(
				'<a href="%1$s"%2$s>%3$s <span class="count">(%4$s)</span></a>',
				esc_url( add_query_arg( $url_args, admin_url( 'edit.php' ) ) ),
				$is_current ? ' class="current" aria-current="page"' : '',
				esc_html( $view_item['label'] ),
				esc_html( number_format_i18n( $count ) )
			);
		}

		return $scoped_views;
	}

	/**
	 * Read the selected event filter from the speaker admin list.
	 *
	 * @since 1.0.0
	 *
	 * @return int Event post ID, or 0 for site speakers.
	 */
	private function get_selected_speaker_admin_event_id() {
		$event_id = filter_input( INPUT_GET, 'wpfa_speaker_event', FILTER_VALIDATE_INT );

		return absint( $event_id );
	}

	/**
	 * Get speakers assigned to one event for the admin list.
	 *
	 * @since 1.0.0
	 *
	 * @param int $event_id Event post ID.
	 * @return array<int> Speaker post IDs.
	 */
	private function get_admin_event_speaker_ids( $event_id ) {
		return Wpfaevent_Event_Speaker_Relation_Manager::get_admin_event_speaker_ids( $event_id );
	}

	/**
	 * Get every speaker owned by any event.
	 *
	 * @since 1.0.0
	 *
	 * @return array<int> Speaker post IDs.
	 */
	private function get_all_event_owned_speaker_ids() {
		return Wpfaevent_Event_Speaker_Relation_Manager::get_all_event_owned_speaker_ids();
	}

	/**
	 * Count speaker admin rows in the current scoped list.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $event_id    Selected event ID, or 0 for site speakers.
	 * @param string $post_status Optional post status.
	 * @return int
	 */
	private function count_scoped_speaker_admin_posts( $event_id, $post_status = '' ) {
		$event_id    = absint( $event_id );
		$post_status = sanitize_key( $post_status );
		$query_args  = array(
			'post_type'      => 'wpfa_speaker',
			'post_status'    => $post_status ? $post_status : 'any',
			'posts_per_page' => -1,
			'fields'         => 'ids',
			'no_found_rows'  => true,
		);

		if ( $event_id ) {
			$speaker_ids = $this->get_admin_event_speaker_ids( $event_id );

			if ( empty( $speaker_ids ) ) {
				return 0;
			}

			$query_args['post__in'] = $speaker_ids;
		} else {
			$event_owned_speaker_ids = $this->get_all_event_owned_speaker_ids();

			if ( ! empty( $event_owned_speaker_ids ) ) {
				$query_args['post__not_in'] = $event_owned_speaker_ids;
			}

			// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query -- Admin list counts mirror the scoped speaker ownership query.
			$query_args['meta_query'] = array(
				'relation' => 'AND',
				array(
					'key'     => 'wpfa_speaker_events',
					'compare' => 'NOT EXISTS',
				),
				array(
					'key'     => '_wpfa_eventyay_speaker_id',
					'compare' => 'NOT EXISTS',
				),
			);
		}

		$speaker_ids = get_posts( $query_args );

		return count( $speaker_ids );
	}

	/**
	 * Sanitize Eventyay import options.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $input Raw option input.
	 * @return array Sanitized settings.
	 */
	public function sanitize_eventyay_import_settings( $input ) {
		return $this->get_eventyay_importer()->sanitize_eventyay_import_settings( $input );
	}

	/**
	 * Render the Eventyay import page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_settings_page() {
		$this->get_eventyay_importer()->render_settings_page();
	}

	/**
	 * Render the Eventyay update page.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function render_update_events_page() {
		$this->get_eventyay_importer()->render_update_events_page();
	}

	/**
	 * Handle Eventyay JSON:API speaker sync for the admin dashboard.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_sync_eventyay() {
		$this->get_eventyay_ajax_sync()->ajax_sync_eventyay();
	}

	/**
	 * Handle Eventyay import form submissions.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function handle_eventyay_events_import() {
		$this->get_eventyay_importer()->handle_eventyay_events_import();
	}

	/**
	 * Show notice when block themes are active.
	 *
	 * @since 1.0.0
	 */
	public function maybe_show_block_theme_notice() {
		if ( ! function_exists( 'wp_is_block_theme' ) || ! wp_is_block_theme() ) {
			return;
		}

		echo '<div class="notice notice-warning is-dismissible"><p>';
		echo esc_html__(
			'WPFA Event page templates require a classic theme. Block themes (e.g., Twenty Twenty-Five) do not support PHP page templates.',
			'wpfaevent'
		);
		echo '</p></div>';
	}

	/**
	 * Get normalized speaker IDs assigned to an event.
	 *
	 * @since 1.0.0
	 *
	 * @param int $event_id Event post ID.
	 * @return array<int> Speaker post IDs.
	 */
	private function get_event_speaker_ids( $event_id ) {
		$speaker_ids = get_post_meta( $event_id, 'wpfa_event_speakers', true );

		return $this->sanitize_post_id_list( $speaker_ids );
	}

	/**
	 * Get normalized event IDs assigned to a speaker.
	 *
	 * @since 1.0.0
	 *
	 * @param int $speaker_id Speaker post ID.
	 * @return array<int> Event post IDs.
	 */
	private function get_speaker_event_ids( $speaker_id ) {
		$event_ids = get_post_meta( $speaker_id, 'wpfa_speaker_events', true );

		return $this->sanitize_post_id_list( $event_ids );
	}

	/**
	 * Sanitize, deduplicate, and reindex a list of post IDs.
	 *
	 * @since 1.0.0
	 *
	 * @param mixed $post_ids Raw post IDs.
	 * @return array<int> Sanitized post IDs.
	 */
	private function sanitize_post_id_list( $post_ids ) {
		if ( ! is_array( $post_ids ) ) {
			$post_ids = array( $post_ids );
		}

		$post_ids = array_map( 'absint', $post_ids );
		$post_ids = array_filter( $post_ids );

		return array_values( array_unique( $post_ids ) );
	}

	/**
	 * Save a normalized post ID list as post meta.
	 *
	 * @since 1.0.0
	 *
	 * @param int        $post_id  Post ID.
	 * @param string     $meta_key Meta key.
	 * @param array<int> $post_ids Post IDs to save.
	 * @return void
	 */
	private function update_post_id_list_meta( $post_id, $meta_key, $post_ids ) {
		$post_ids = $this->sanitize_post_id_list( $post_ids );

		if ( empty( $post_ids ) ) {
			delete_post_meta( $post_id, $meta_key );
			return;
		}

		update_post_meta( $post_id, $meta_key, $post_ids );
	}

	/**
	 * Update a meta key when it has content, otherwise delete it.
	 *
	 * @since 1.0.0
	 *
	 * @param int    $post_id Post ID.
	 * @param string $key     Meta key.
	 * @param string $value   Meta value.
	 * @return void
	 */
	private function update_or_delete_post_meta( $post_id, $key, $value ) {
		if ( '' === $value ) {
			delete_post_meta( $post_id, $key );
			return;
		}

		update_post_meta( $post_id, $key, $value );
	}

	/**
	 * Save Speaker meta box data.
	 *
	 * @since 1.0.0
	 * @param int $post_id The post ID.
	 */
	public function save_speaker_meta( $post_id ) {
		$speaker_nonce = isset( $_POST['wpfa_speaker_meta_nonce'] ) ? sanitize_text_field( wp_unslash( $_POST['wpfa_speaker_meta_nonce'] ) ) : '';

		if ( ! $speaker_nonce || ! wp_verify_nonce( $speaker_nonce, 'wpfa_speaker_meta_nonce' ) ) {
			return;
		}

		if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
			return;
		}

		if ( ! current_user_can( 'edit_post', $post_id ) ) {
			return;
		}

		if ( isset( $_POST['wpfa_speaker_position'] ) ) {
			update_post_meta( $post_id, 'wpfa_speaker_position', sanitize_text_field( wp_unslash( $_POST['wpfa_speaker_position'] ) ) );
		}

		if ( isset( $_POST['wpfa_speaker_organization'] ) ) {
			update_post_meta( $post_id, 'wpfa_speaker_organization', sanitize_text_field( wp_unslash( $_POST['wpfa_speaker_organization'] ) ) );
		}

		if ( isset( $_POST['wpfa_speaker_bio'] ) ) {
			update_post_meta( $post_id, 'wpfa_speaker_bio', wp_kses_post( wp_unslash( $_POST['wpfa_speaker_bio'] ) ) );
		}

		if ( isset( $_POST['wpfa_speaker_headshot_url'] ) ) {
			update_post_meta( $post_id, 'wpfa_speaker_headshot_url', esc_url_raw( wp_unslash( $_POST['wpfa_speaker_headshot_url'] ) ) );
		}

		$previous_events = $this->get_speaker_event_ids( $post_id );
		$events          = array();

		if ( isset( $_POST['wpfa_speaker_events'] ) && is_array( $_POST['wpfa_speaker_events'] ) ) {
			$events = $this->sanitize_post_id_list(
				array_map(
					'sanitize_text_field',
					wp_unslash( $_POST['wpfa_speaker_events'] )
				)
			);
		}

		$this->update_post_id_list_meta( $post_id, 'wpfa_speaker_events', $events );
		$this->sync_speaker_event_relationships( $post_id, $previous_events, $events );
	}

	/**
	 * Sync event-side speaker relationship meta after a speaker is saved.
	 *
	 * @since 1.0.0
	 *
	 * @param int        $speaker_id      Speaker post ID.
	 * @param array<int> $previous_events Event IDs before save.
	 * @param array<int> $current_events  Event IDs after save.
	 * @return void
	 */
	private function sync_speaker_event_relationships( $speaker_id, $previous_events, $current_events ) {
		$speaker_id      = absint( $speaker_id );
		$previous_events = $this->sanitize_post_id_list( $previous_events );
		$current_events  = $this->sanitize_post_id_list( $current_events );

		if ( ! $speaker_id || 'wpfa_speaker' !== get_post_type( $speaker_id ) ) {
			return;
		}

		$previous_events = array_values(
			array_unique(
				array_merge(
					$previous_events,
					$this->get_events_linked_to_speaker( $speaker_id )
				)
			)
		);

		$removed_events = array_diff( $previous_events, $current_events );

		foreach ( $removed_events as $event_id ) {
			$this->remove_speaker_from_event( $event_id, $speaker_id );
		}

		foreach ( $current_events as $event_id ) {
			$this->add_speaker_to_event( $event_id, $speaker_id );
		}
	}

	/**
	 * Find events whose event-side speaker meta includes a speaker.
	 *
	 * @since 1.0.0
	 *
	 * @param int $speaker_id Speaker post ID.
	 * @return array<int> Event post IDs.
	 */
	private function get_events_linked_to_speaker( $speaker_id ) {
		$speaker_id = absint( $speaker_id );

		if ( ! $speaker_id ) {
			return array();
		}

		$batch_size   = 100;
		$current_page = 1;
		$event_ids    = array();

		do {
			$batch_ids = get_posts(
				array(
					'post_type'              => 'wpfa_event',
					'post_status'            => 'any',
					'posts_per_page'         => $batch_size,
					'paged'                  => $current_page,
					'fields'                 => 'ids',
					'no_found_rows'          => true,
					'orderby'                => 'ID',
					'order'                  => 'ASC',
					'update_post_meta_cache' => false,
					'update_post_term_cache' => false,
				)
			);

			if ( empty( $batch_ids ) ) {
				break;
			}

			$batch_count = count( $batch_ids );
			update_meta_cache( 'post', $batch_ids );

			foreach ( $batch_ids as $event_id ) {
				if ( in_array( $speaker_id, $this->get_event_speaker_ids( $event_id ), true ) ) {
					$event_ids[] = $event_id;
				}
			}

			++$current_page;
		} while ( $batch_count === $batch_size );

		return $this->sanitize_post_id_list( $event_ids );
	}

	/**
	 * Add a speaker ID to an event's related speakers.
	 *
	 * @since 1.0.0
	 *
	 * @param int $event_id   Event post ID.
	 * @param int $speaker_id Speaker post ID.
	 * @return void
	 */
	private function add_speaker_to_event( $event_id, $speaker_id ) {
		$event_id   = absint( $event_id );
		$speaker_id = absint( $speaker_id );

		if ( ! $event_id || ! $speaker_id ) {
			return;
		}

		if ( 'wpfa_event' !== get_post_type( $event_id ) ) {
			return;
		}

		if ( 'wpfa_speaker' !== get_post_type( $speaker_id ) ) {
			return;
		}

		$speaker_ids   = $this->get_event_speaker_ids( $event_id );
		$speaker_ids[] = $speaker_id;

		$this->update_post_id_list_meta( $event_id, 'wpfa_event_speakers', $speaker_ids );
	}

	/**
	 * Remove a speaker ID from an event's related speakers.
	 *
	 * @since 1.0.0
	 *
	 * @param int $event_id   Event post ID.
	 * @param int $speaker_id Speaker post ID.
	 * @return void
	 */
	private function remove_speaker_from_event( $event_id, $speaker_id ) {
		$event_id   = absint( $event_id );
		$speaker_id = absint( $speaker_id );

		if ( ! $event_id || ! $speaker_id ) {
			return;
		}

		if ( 'wpfa_event' !== get_post_type( $event_id ) ) {
			return;
		}

		if ( 'wpfa_speaker' !== get_post_type( $speaker_id ) ) {
			return;
		}

		$speaker_ids = array_diff( $this->get_event_speaker_ids( $event_id ), array( $speaker_id ) );

		$this->update_post_id_list_meta( $event_id, 'wpfa_event_speakers', $speaker_ids );
	}

	/**
	 * Handle AJAX request to get speaker data.
	 *
	 * @since 1.0.0
	 */
	public function ajax_get_speaker() {
		// Verify nonce.
		if ( ! check_ajax_referer( 'wpfa_speakers_ajax', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Invalid nonce', 'wpfaevent' ),
				),
				403
			);
		}

		$speaker_id = isset( $_POST['speaker_id'] ) ? absint( $_POST['speaker_id'] ) : 0;

		if ( ! $speaker_id ) {
			wp_send_json_error( esc_html__( 'Invalid speaker ID', 'wpfaevent' ) );
		}

		$speaker = get_post( $speaker_id );

		if ( ! $speaker || 'wpfa_speaker' !== $speaker->post_type || ! current_user_can( 'edit_post', $speaker_id ) ) {
			wp_send_json_error( esc_html__( 'Speaker not found', 'wpfaevent' ) );
		}

		// Get category term.
		$category      = '';
		$category_slug = '';
		$terms         = wp_get_object_terms( $speaker_id, 'wpfa_speaker_category' );
		if ( ! is_wp_error( $terms ) && ! empty( $terms ) ) {
			$category      = $terms[0]->name;
			$category_slug = $terms[0]->slug;
		}

		$data = array(
			'id'            => $speaker_id,
			'name'          => $speaker->post_title,
			'position'      => get_post_meta( $speaker_id, 'wpfa_speaker_position', true ),
			'organization'  => get_post_meta( $speaker_id, 'wpfa_speaker_organization', true ),
			'bio'           => get_post_meta( $speaker_id, 'wpfa_speaker_bio', true ),
			'headshot_url'  => get_post_meta( $speaker_id, 'wpfa_speaker_headshot_url', true ),
			'linkedin'      => get_post_meta( $speaker_id, 'wpfa_speaker_linkedin', true ),
			'twitter'       => get_post_meta( $speaker_id, 'wpfa_speaker_twitter', true ),
			'github'        => get_post_meta( $speaker_id, 'wpfa_speaker_github', true ),
			'website'       => get_post_meta( $speaker_id, 'wpfa_speaker_website', true ),
			'category'      => $category,
			'category_slug' => $category_slug,
			'talk_title'    => get_post_meta( $speaker_id, 'wpfa_speaker_talk_title', true ),
			'talk_date'     => get_post_meta( $speaker_id, 'wpfa_speaker_talk_date', true ),
			'talk_time'     => get_post_meta( $speaker_id, 'wpfa_speaker_talk_time', true ),
			'talk_end_time' => get_post_meta( $speaker_id, 'wpfa_speaker_talk_end_time', true ),
			'talk_abstract' => get_post_meta( $speaker_id, 'wpfa_speaker_talk_abstract', true ),
		);

		wp_send_json_success( $data );
	}

	/**
	 * Handle AJAX request to add a new speaker.
	 *
	 * @since 1.0.0
	 */
	public function ajax_add_speaker() {
		// Verify nonce.
		if ( ! check_ajax_referer( 'wpfa_speakers_ajax', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Invalid nonce', 'wpfaevent' ),
				),
				403
			);
		}

		if ( ! current_user_can( 'publish_speakers' ) ) {
			wp_send_json_error(
				array(
					'message' => __( 'Unauthorized', 'wpfaevent' ),
				),
				403
			);
		}

		// Validate required fields.
		$required_fields = array( 'name', 'position', 'bio', 'talk_title', 'talk_date', 'talk_time', 'talk_end_time' );
		foreach ( $required_fields as $field ) {
			if ( empty( $_POST[ $field ] ) ) {
				/* translators: %s: Required field key. */
				wp_send_json_error( sprintf( esc_html__( 'Missing required field: %s', 'wpfaevent' ), $field ) );
			}
		}

		// Create speaker post.
		$speaker_name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		$speaker_data = array(
			'post_title'   => $speaker_name,
			'post_type'    => 'wpfa_speaker',
			'post_status'  => 'publish',
			'post_content' => '',
		);

		$speaker_id = wp_insert_post( $speaker_data );

		if ( is_wp_error( $speaker_id ) ) {
			wp_send_json_error( $speaker_id->get_error_message() );
		}

		// Handle image upload.
		$image_url = '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- media_handle_upload() requires the raw $_FILES payload.
		$uploaded_file = ( isset( $_FILES['image_upload'] ) && is_array( $_FILES['image_upload'] ) ) ? $_FILES['image_upload'] : array();
		if ( ! empty( $uploaded_file['name'] ) ) {
			// Validate file type.
			$allowed_types = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );
			$file_type     = isset( $uploaded_file['type'] ) ? sanitize_mime_type( wp_unslash( $uploaded_file['type'] ) ) : '';

			if ( ! in_array( $file_type, $allowed_types, true ) ) {
				wp_send_json_error( esc_html__( 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.', 'wpfaevent' ) );
			}

			// Validate file size (2MB max).
			$max_size  = 2 * 1024 * 1024; // 2MB in bytes.
			$file_size = isset( $uploaded_file['size'] ) ? absint( $uploaded_file['size'] ) : 0;
			if ( $file_size > $max_size ) {
				wp_send_json_error( esc_html__( 'File size exceeds 2MB limit.', 'wpfaevent' ) );
			}

			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';

			// Upload and create attachment.
			$attachment_id = media_handle_upload( 'image_upload', 0 );

			if ( is_wp_error( $attachment_id ) ) {
				/* translators: %s: Upload error message. */
				wp_send_json_error( sprintf( esc_html__( 'Image upload failed: %s', 'wpfaevent' ), $attachment_id->get_error_message() ) );
			}

			$image_url = wp_get_attachment_url( $attachment_id );
		} elseif ( ! empty( $_POST['image_url'] ) ) {
			$image_url = esc_url_raw( wp_unslash( $_POST['image_url'] ) );
		}

		// Save meta fields.
		$meta_fields = array(
			'wpfa_speaker_position'     => 'position',
			'wpfa_speaker_organization' => 'organization',
			'wpfa_speaker_bio'          => 'bio',
			'wpfa_speaker_headshot_url' => 'image_url',
			'wpfa_speaker_linkedin'     => 'linkedin',
			'wpfa_speaker_twitter'      => 'twitter',
			'wpfa_speaker_github'       => 'github',
			'wpfa_speaker_website'      => 'website',
		);

		foreach ( $meta_fields as $meta_key => $post_key ) {
			if ( 'image_url' === $post_key && ! empty( $image_url ) ) {
				// Use uploaded image URL or provided URL.
				update_post_meta( $speaker_id, $meta_key, $image_url );
			} elseif ( isset( $_POST[ $post_key ] ) ) {
				$value = sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) );

				if ( 'bio' === $post_key ) {
					$value = wp_kses_post( wp_unslash( $_POST[ $post_key ] ) );
				} elseif ( in_array( $post_key, array( 'linkedin', 'twitter', 'github', 'website' ), true ) ) {
					$value = esc_url_raw( wp_unslash( $_POST[ $post_key ] ) );
				} else {
					$value = sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) );
				}

				if ( strlen( $value ) === 0 ) {
					delete_post_meta( $speaker_id, $meta_key );
				} else {
					update_post_meta( $speaker_id, $meta_key, $value );
				}
			}
		}

		$session_fields = array(
			'wpfa_speaker_talk_title'    => 'talk_title',
			'wpfa_speaker_talk_date'     => 'talk_date',
			'wpfa_speaker_talk_time'     => 'talk_time',
			'wpfa_speaker_talk_end_time' => 'talk_end_time',
			'wpfa_speaker_talk_abstract' => 'talk_abstract',
		);

		foreach ( $session_fields as $meta_key => $post_key ) {
			if ( isset( $_POST[ $post_key ] ) ) {

				if ( 'talk_abstract' === $post_key ) {
					$value = wp_kses_post( wp_unslash( $_POST[ $post_key ] ) );
				} else {
					$value = sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) );
				}

				if ( strlen( $value ) === 0 ) {
					delete_post_meta( $speaker_id, $meta_key );
				} else {
					update_post_meta( $speaker_id, $meta_key, $value );
				}
			}
		}

		if ( isset( $_POST['category'] ) ) {
			$category = sanitize_text_field( wp_unslash( $_POST['category'] ) );

			// If it's numeric, it's a term ID.
			if ( is_numeric( $category ) ) {
				$term_id = (int) $category;
				wp_set_object_terms( $speaker_id, $term_id, 'wpfa_speaker_category' );
			} elseif ( '_custom' === $category && isset( $_POST['category_custom'] ) && ! empty( $_POST['category_custom'] ) ) {
				// If it's "_custom" with custom value.
				$category_name = sanitize_text_field( wp_unslash( $_POST['category_custom'] ) );
				wp_set_object_terms( $speaker_id, $category_name, 'wpfa_speaker_category' );
			} elseif ( ! empty( $category ) && '_custom' !== $category ) {
				// If it's a slug or name.
				wp_set_object_terms( $speaker_id, $category, 'wpfa_speaker_category' );
			} else {
				// Empty value.
				wp_set_object_terms( $speaker_id, array(), 'wpfa_speaker_category' );
			}
		}

		wp_send_json_success( array( 'speaker_id' => $speaker_id ) );
	}

	/**
	 * Handle AJAX request to update a speaker.
	 *
	 * @since 1.0.0
	 */
	public function ajax_update_speaker() {
		// Verify nonce.
		if ( ! check_ajax_referer( 'wpfa_speakers_ajax', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Invalid nonce', 'wpfaevent' ),
				),
				403
			);
		}

		$speaker_id = isset( $_POST['speaker_id'] ) ? absint( $_POST['speaker_id'] ) : 0;

		if ( ! $speaker_id ) {
			wp_send_json_error( __( 'Invalid speaker ID', 'wpfaevent' ) );
		}

		// Verify the speaker exists and the user can edit it.
		$speaker = get_post( $speaker_id );
		if ( ! $speaker || 'wpfa_speaker' !== $speaker->post_type || ! current_user_can( 'edit_post', $speaker_id ) ) {
			wp_send_json_error( __( 'Cannot edit this speaker', 'wpfaevent' ) );
		}

		// Validate required fields.
		$required_fields = array( 'name', 'position', 'bio', 'talk_title', 'talk_date', 'talk_time', 'talk_end_time' );
		foreach ( $required_fields as $field ) {
			if ( empty( $_POST[ $field ] ) ) {
				/* translators: %s: Required field key. */
				wp_send_json_error( sprintf( esc_html__( 'Missing required field: %s', 'wpfaevent' ), $field ) );
			}
		}

		// Update post title if the name changed.
		$speaker_name = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
		if ( ! empty( $speaker_name ) ) {
			wp_update_post(
				array(
					'ID'         => $speaker_id,
					'post_title' => $speaker_name,
				)
			);
		}

		// Handle image upload.
		$image_url = '';
		// phpcs:ignore WordPress.Security.ValidatedSanitizedInput.InputNotSanitized -- media_handle_upload() requires the raw $_FILES payload.
		$uploaded_file = ( isset( $_FILES['image_upload'] ) && is_array( $_FILES['image_upload'] ) ) ? $_FILES['image_upload'] : array();
		if ( ! empty( $uploaded_file['name'] ) ) {
			// Validate file type.
			$allowed_types = array( 'image/jpeg', 'image/png', 'image/gif', 'image/webp' );
			$file_type     = isset( $uploaded_file['type'] ) ? sanitize_mime_type( wp_unslash( $uploaded_file['type'] ) ) : '';

			if ( ! in_array( $file_type, $allowed_types, true ) ) {
				wp_send_json_error( esc_html__( 'Invalid file type. Only JPG, PNG, GIF, and WebP are allowed.', 'wpfaevent' ) );
			}

			// Validate file size (2MB max).
			$max_size  = 2 * 1024 * 1024; // 2MB in bytes.
			$file_size = isset( $uploaded_file['size'] ) ? absint( $uploaded_file['size'] ) : 0;
			if ( $file_size > $max_size ) {
				wp_send_json_error( esc_html__( 'File size exceeds 2MB limit.', 'wpfaevent' ) );
			}

			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
			require_once ABSPATH . 'wp-admin/includes/media.php';

			// Upload and create attachment.
			$attachment_id = media_handle_upload( 'image_upload', $speaker_id );

			if ( is_wp_error( $attachment_id ) ) {
				/* translators: %s: Upload error message. */
				wp_send_json_error( sprintf( esc_html__( 'Image upload failed: %s', 'wpfaevent' ), $attachment_id->get_error_message() ) );
			}

			$image_url = wp_get_attachment_url( $attachment_id );
		} elseif ( ! empty( $_POST['image_url'] ) ) {
			$image_url = esc_url_raw( wp_unslash( $_POST['image_url'] ) );
		}

		// Save meta fields.
		$meta_fields = array(
			'wpfa_speaker_position'     => 'position',
			'wpfa_speaker_organization' => 'organization',
			'wpfa_speaker_bio'          => 'bio',
			'wpfa_speaker_headshot_url' => 'image_url',
			'wpfa_speaker_linkedin'     => 'linkedin',
			'wpfa_speaker_twitter'      => 'twitter',
			'wpfa_speaker_github'       => 'github',
			'wpfa_speaker_website'      => 'website',
		);

		foreach ( $meta_fields as $meta_key => $post_key ) {
			if ( 'image_url' === $post_key && ! empty( $image_url ) ) {
				// Use uploaded image URL or provided URL.
				update_post_meta( $speaker_id, $meta_key, $image_url );
			} elseif ( isset( $_POST[ $post_key ] ) ) {

				if ( 'bio' === $post_key ) {
					$value = wp_kses_post( wp_unslash( $_POST[ $post_key ] ) );
				} elseif ( in_array( $post_key, array( 'linkedin', 'twitter', 'github', 'website' ), true ) ) {
					$value = esc_url_raw( wp_unslash( $_POST[ $post_key ] ) );
				} else {
					$value = sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) );
				}

				// Delete meta when the field is intentionally cleared to avoid storing empty values.
				if ( strlen( $value ) === 0 ) {
					delete_post_meta( $speaker_id, $meta_key );
				} else {
					update_post_meta( $speaker_id, $meta_key, $value );
				}
			}
		}

		$session_fields = array(
			'wpfa_speaker_talk_title'    => 'talk_title',
			'wpfa_speaker_talk_date'     => 'talk_date',
			'wpfa_speaker_talk_time'     => 'talk_time',
			'wpfa_speaker_talk_end_time' => 'talk_end_time',
			'wpfa_speaker_talk_abstract' => 'talk_abstract',
		);

		foreach ( $session_fields as $meta_key => $post_key ) {
			if ( isset( $_POST[ $post_key ] ) ) {

				if ( 'talk_abstract' === $post_key ) {
					$value = wp_kses_post( wp_unslash( $_POST[ $post_key ] ) );
				} else {
					$value = sanitize_text_field( wp_unslash( $_POST[ $post_key ] ) );
				}

				if ( strlen( $value ) === 0 ) {
					delete_post_meta( $speaker_id, $meta_key );
				} else {
					update_post_meta( $speaker_id, $meta_key, $value );
				}
			}
		}

		if ( isset( $_POST['category'] ) ) {
			$category = sanitize_text_field( wp_unslash( $_POST['category'] ) );

			// If it's numeric, it's a term ID.
			if ( is_numeric( $category ) ) {
				$term_id = (int) $category;
				wp_set_object_terms( $speaker_id, $term_id, 'wpfa_speaker_category' );
			} elseif ( '_custom' === $category && isset( $_POST['category_custom'] ) && ! empty( $_POST['category_custom'] ) ) {
				// If it's "_custom" with a custom value.
				$category_name = sanitize_text_field( wp_unslash( $_POST['category_custom'] ) );
				wp_set_object_terms( $speaker_id, $category_name, 'wpfa_speaker_category' );
			} elseif ( ! empty( $category ) && '_custom' !== $category ) {
				// If it's a slug or name.
				wp_set_object_terms( $speaker_id, $category, 'wpfa_speaker_category' );
			} else {
				// Empty value.
				wp_set_object_terms( $speaker_id, array(), 'wpfa_speaker_category' );
			}
		}

		wp_send_json_success();
	}

	/**
	 * Handle AJAX request to delete a speaker.
	 *
	 * @since 1.0.0
	 */
	public function ajax_delete_speaker() {
		// Verify nonce.
		if ( ! check_ajax_referer( 'wpfa_speakers_ajax', 'nonce', false ) ) {
			wp_send_json_error(
				array(
					'message' => esc_html__( 'Invalid nonce', 'wpfaevent' ),
				),
				403
			);
		}

		$speaker_id = isset( $_POST['speaker_id'] ) ? absint( $_POST['speaker_id'] ) : 0;

		if ( ! $speaker_id ) {
			wp_send_json_error( __( 'Invalid speaker ID', 'wpfaevent' ) );
		}

		// Verify the speaker exists and the user can delete it.
		$speaker = get_post( $speaker_id );
		if ( ! $speaker || 'wpfa_speaker' !== $speaker->post_type || ! current_user_can( 'delete_post', $speaker_id ) ) {
			wp_send_json_error( __( 'Cannot delete this speaker', 'wpfaevent' ) );
		}

		// Delete the speaker.
		$result = wp_delete_post( $speaker_id, true );

		if ( ! $result ) {
			wp_send_json_error( __( 'Failed to delete speaker', 'wpfaevent' ) );
		}

		wp_send_json_success();
	}

	/**
	 * Handle Eventyay JSON:API speaker sync for the admin dashboard.
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function ajax_sync_eventyay() {
		$this->get_eventyay_importer()->ajax_sync_eventyay();
	}

	/**
	 * Render the speaker statistics dashboard page.
	 *
	 * @since 1.0.0
	 */
	public function render_speaker_dashboard_page() {
		// Check capabilities.
		if ( ! current_user_can( 'edit_speakers' ) ) {
			wp_die( esc_html__( 'You do not have sufficient permissions to access this page.', 'wpfaevent' ) );
		}

		// Total speakers.
		$total_query = new WP_Query(
			array(
				'post_type'      => 'wpfa_speaker',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'post_status'    => 'any',
			)
		);
		$total_count = $total_query->post_count;

		// Synced from Eventyay.
		$synced_query = new WP_Query(
			array(
				'post_type'      => 'wpfa_speaker',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'post_status'    => 'any',
				// phpcs:ignore WordPress.DB.SlowDBQuery.slow_db_query_meta_query
				'meta_query'     => array(
					array(
						'key'     => '_wpfa_eventyay_speaker_id',
						'compare' => 'EXISTS',
					),
				),
			)
		);
		$synced_count = $synced_query->post_count;

		// Featured speakers.
		$events               = get_posts(
			array(
				'post_type'      => 'wpfa_event',
				'posts_per_page' => -1,
				'fields'         => 'ids',
				'post_status'    => 'any',
			)
		);
		$featured_speaker_ids = array();
		foreach ( $events as $event_id ) {
			$ids = get_post_meta( $event_id, 'wpfa_event_featured_speakers', true );
			if ( is_array( $ids ) ) {
				$featured_speaker_ids = array_merge( $featured_speaker_ids, $ids );
			}
		}
		$featured_speaker_ids = array_unique( array_map( 'absint', $featured_speaker_ids ) );
		$featured_count       = count( $featured_speaker_ids );

		// Categories.
		$categories = get_terms(
			array(
				'taxonomy'   => 'wpfa_speaker_category',
				'hide_empty' => false,
			)
		);

		?>
		<div class="wrap">
			<h1><?php esc_html_e( 'Speakers Dashboard', 'wpfaevent' ); ?></h1>
			<p class="description"><?php esc_html_e( 'Overview and statistics of all event speakers.', 'wpfaevent' ); ?></p>

			<div class="welcome-panel" style="padding: 23px 10px 30px; margin-top: 20px;">
				<div class="welcome-panel-content">
					<h2><?php esc_html_e( 'Speaker Statistics', 'wpfaevent' ); ?></h2>
					<div class="welcome-panel-column-container" style="display: flex; gap: 40px; margin-top: 20px;">
						<div class="welcome-panel-column" style="flex: 1; background: #fff; border: 1px solid #e5e5e5; padding: 20px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
							<h3 style="margin-top: 0; color: #555;"><?php esc_html_e( 'Total Speakers', 'wpfaevent' ); ?></h3>
							<p style="font-size: 36px; font-weight: 600; margin: 10px 0; color: #23282d;"><?php echo esc_html( $total_count ); ?></p>
							<p class="description"><?php esc_html_e( 'The total number of speakers registered in the database.', 'wpfaevent' ); ?></p>
						</div>

						<div class="welcome-panel-column" style="flex: 1; background: #fff; border: 1px solid #e5e5e5; padding: 20px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
							<h3 style="margin-top: 0; color: #555;"><?php esc_html_e( 'Synced from Eventyay', 'wpfaevent' ); ?></h3>
							<p style="font-size: 36px; font-weight: 600; margin: 10px 0; color: #23282d;"><?php echo esc_html( $synced_count ); ?></p>
							<p class="description"><?php esc_html_e( 'Speakers imported/synchronized with Eventyay sessions.', 'wpfaevent' ); ?></p>
						</div>

						<div class="welcome-panel-column" style="flex: 1; background: #fff; border: 1px solid #e5e5e5; padding: 20px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
							<h3 style="margin-top: 0; color: #555;"><?php esc_html_e( 'Featured Speakers', 'wpfaevent' ); ?></h3>
							<p style="font-size: 36px; font-weight: 600; margin: 10px 0; color: #23282d;"><?php echo esc_html( $featured_count ); ?></p>
							<p class="description"><?php esc_html_e( 'Speakers highlighted as featured on any event landing page.', 'wpfaevent' ); ?></p>
						</div>
					</div>
				</div>
			</div>

			<div class="card" style="margin-top: 20px; max-width: 100%; background: #fff; border: 1px solid #e5e5e5; padding: 20px; border-radius: 4px; box-shadow: 0 1px 1px rgba(0,0,0,.04);">
				<h2><?php esc_html_e( 'Category / Track Breakdown', 'wpfaevent' ); ?></h2>
				<p class="description"><?php esc_html_e( 'Number of speakers assigned to each category/track.', 'wpfaevent' ); ?></p>

				<?php if ( ! empty( $categories ) && ! is_wp_error( $categories ) ) : ?>
					<table class="wp-list-table widefat fixed striped" style="margin-top: 20px;">
						<thead>
							<tr>
								<th><strong><?php esc_html_e( 'Category / Track Name', 'wpfaevent' ); ?></strong></th>
								<th><strong><?php esc_html_e( 'Speaker Count', 'wpfaevent' ); ?></strong></th>
							</tr>
						</thead>
						<tbody>
							<?php foreach ( $categories as $category ) : ?>
								<tr>
									<td><strong><?php echo esc_html( $category->name ); ?></strong></td>
									<td><?php echo esc_html( $category->count ); ?></td>
								</tr>
							<?php endforeach; ?>
						</tbody>
					</table>
				<?php else : ?>
					<p style="margin-top: 20px;"><?php esc_html_e( 'No categories or tracks found.', 'wpfaevent' ); ?></p>
				<?php endif; ?>
			</div>
		</div>
		<?php
	}
}
