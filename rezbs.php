<?PHP
/*
 * ********************************************************
 *
 * Plugin name: RezBS Connect
 * Plugin URI: https://www.rezbs.com
 * Description: Easily add online booking through your RezBS.com account. RezBS Connect provides you with easy-to-add shortcodes to add "Book Now" buttons for each of your RezBS trips or events. Requires SSL for PCI/Bank compliance. 
 * Version: 1.7
 * Author: Ben Jamieson
 *
 **********************************************************
 */

require_once( plugin_dir_path( __FILE__ ) . 'global.php' );
require_once( plugin_dir_path( __FILE__ ) . 'class-rezbs-custom-block.php' );//Added By AV.

class rezbs_settings {

		private $options;

		public function __construct() {
			$this->options = get_option( 'rezbs_options' );
			
			if (is_ssl() == false) add_action( 'admin_notices', array( $this, 'rezbs_show_SSL_alert' ) );

			add_action( 'admin_init', array( $this, 'rezbs_page_init' ), 10 );
			add_action( 'admin_menu', array( $this, 'rezbs_add_plugin_page' ) );
		}

		public function rezbs_add_plugin_page() {
				// This page will be in the root category
				add_menu_page(
						'RezBS Connect', 
						'RezBS Connect', 
						'manage_options', 
						'rezbs-api-settings', 
						array( $this, 'rezbs_create_admin_page' )
				);
			}

		public function rezbs_create_admin_page() {
				$this->options = get_option( 'rezbs_options' );
				
				$type = 'updated';
				$message = __( 'RezBS auth successfully updated', 'my-text-domain' );

				add_settings_error
					(
						'rezbs_auth_success_message',
						esc_attr( 'settings_updated' ),
						$message,
						$type
					);
				?>
				<div class="wrap">
					<?php 
							if ( isset( $_GET['settings-updated'] ) )
								settings_errors( 'rezbs_auth_success_message' );
					?>
						<form method="post" action="options.php">
						<input name="action" type="hidden" value="rezbs">
						<?php
							// This prints out all hidden setting fields
							settings_fields( 'rezbs_option_group' );   
							do_settings_sections( 'rezbs-api-settings' );
							submit_button( 'Connect to RezBS' ); 
						?>
						</form>
				</div>
				<?php
			}

		public function rezbs_page_init() {        
				$this->options = get_option( 'rezbs_options' );
				if( empty( $this->options['rezbs_username'] ) || empty( $this->options['rezbs_api_key'] ) || !$this->options['rezbs_auth_success'] )
				add_action( 'admin_notices', array( $this, 'rezbs_show_alert' ) );
				register_setting(
						'rezbs_option_group', // Option group
						'rezbs_options', // Option name
						array( $this, 'rezbs_sanitize' ) // Sanitize
				);

				add_settings_section(
						'rezbs_main_section', // ID
						'Rezbs API Settings', // Title
						array( $this, 'rezbs_print_section_info' ), // Callback
						'rezbs-api-settings' // Page
				);  

				add_settings_field(
						'rezbs_username', 
						'Username', 
						array( $this, 'rezbs_username_cb' ), 
						'rezbs-api-settings', 
						'rezbs_main_section' 
				);      

				add_settings_field(
						'rezbs_api_key', 
						'API Key', 
						array( $this, 'rezbs_api_key_cb' ), 
						'rezbs-api-settings', 
						'rezbs_main_section'
				);      
			}

		public function rezbs_sanitize( $input ) {
			$new_input = array();
			if( isset( $input['rezbs_username'] ) )
				$new_input['rezbs_username'] = sanitize_text_field( $input['rezbs_username'] );

			if( isset( $input['rezbs_api_key'] ) )
				$new_input['rezbs_api_key'] = sanitize_text_field( $input['rezbs_api_key'] );

			if( isset( $input['rezbs_auth_success'] ) )
				$new_input['rezbs_auth_success'] = sanitize_text_field( $input['rezbs_auth_success'] );

			return $new_input;
		}

		public function rezbs_print_section_info() {
			$this->options = get_option( 'rezbs_options' );

			if( $this->options['rezbs_auth_success'] ) {
					print '<p style="color:green;">You are successfully connected to RezBS</p>';
			} else {
					if( ( $this->options['rezbs_username'] != '') && ( $this->options['rezbs_api_key'] != '' ) ) {
						print '<p style="color:red;">Authentication failed. Please check your username and API Key and try again.</p>';
					} else {
						print '<p>Enter your RezBS username and API key to connect your website to your RezBS account.</p>';
					}
						
			}
		}

		public function rezbs_username_cb() {
			printf(
					'<input style="min-width: 360px;" type="text" id="rezbs_username" name="rezbs_options[rezbs_username]" value="%s" %s />',
					isset( $this->options['rezbs_username'] ) ? esc_attr( $this->options['rezbs_username']) : '',
					(is_ssl() == false) ? "disabled='disabled'" : ''
			);
			print '<br /><i>Your RezBS account username</i>';
		}

		public function rezbs_api_key_cb() {
			printf(
					'<input style="min-width: 360px;" type="text" id="rezbs_api_key" name="rezbs_options[rezbs_api_key]" value="%s" %s />',
					isset( $this->options['rezbs_api_key'] ) ? esc_attr( $this->options['rezbs_api_key']) : '',
					(is_ssl() == false) ? "disabled='disabled'" : ''
			);
			print '<br /><i>The API Key found in the "Integration » Wordpress Integration" area of your RezBS Account.</i>';
		}

		public function rezbs_show_alert() {
				print '<div class="notice notice-warning is-dismissible">
					<p><strong>You have not connected the RezBS plugin to your RezBS account.</strong> Please visit the <a href="/wp-admin/admin.php?page=rezbs-api-settings">RezBS plugin settings page</a> to complete configuration.</p>
				</div>';
			}
			
		public function rezbs_show_SSL_alert() {
				print '<div class="notice notice-error">
					<p><strong>To comply with PCI policies, the RezBS plugin can only run on sites secured with an <a href="https://ssl.comodo.com/landing/ssl/index-new.php?af=7697&amp;key1sk1=sem&amp;ap=3SEMSept16&amp;gclid=CMb38-Dr_c4CFZaHaQodxZYPOQ" target="_blank">SSL certificate</a>.</strong></p>
				</div>';
			}
	}

class rezbs_API_Endpoint {
		public function __construct() {
				add_filter('query_vars', array($this, 'rezbs_add_query_vars'), 0);
				add_action('parse_request', array($this, 'rezbs_sniff_requests'), 0);
				add_action('init', array($this, 'rezbs_add_endpoint'), 0);
			}
		
		# Make vars global
		public function rezbs_add_query_vars($vars) {
				$vars[] = '__api';
				$vars[] = 'key';
				return $vars;
			}
		

		public function rezbs_add_endpoint() {
				add_rewrite_rule('^api/trips?(.*)','index.php?__api=1&key=$matches[1]','top');
			}

		public function rezbs_sniff_requests()
			{
				global $wp;
				if( isset( $wp->query_vars['__api'] ) && $wp->query_vars['__api'] == 1)
					{
						$this->rezbs_handle_request();
						exit;
					}
			}
			
		public function rezbs_get_trips( $key )
			{
				$url = 'https://rezbs.com/api/trips?key='.$key;
				$response = wp_remote_get( $url );
				if( is_array( $response) ) 
					return json_decode( $response['body'] );

				return NULL;
			}
			
			

		protected function rezbs_handle_request()
			{
				global $wp;
				header('Content-Type: application/json');

				$key = $wp->query_vars['key'];

				$options = get_option( 'rezbs_options' );
				if( ( get_option( 'rezbs_options[rezbs_auth_success]' ) != 1 ) || ( $options['rezbs_api_key'] != $key ) )
					die( wp_send_json( array( 'authstatus' => 'Invalid Key' ) ) );

				$trips = $this->rezbs_get_trips( $options['rezbs_api_key'] );

				die( wp_send_json( $trips ) );
			}
}


if( is_admin() )
	new rezbs_settings();

new rezbs_API_Endpoint();


add_shortcode( 'rezbs_button', 'rezbs_button_html' );
function rezbs_button_html( $atts ){
	
	$html='';//Added By AV.
	$api = new rezbs_API_Endpoint();
	$atts = shortcode_atts( array (
			'id'=> '',
			'class'=> 'rezbs_button',
			'label'=> 'Book Now!'
		), $atts );
	$options = get_option( 'rezbs_options' );
	$trips = $api->rezbs_get_trips( $options['rezbs_api_key'] );
	$lightItUp = "target='_blank'";
	
	foreach( $trips->alltrips as $trip ) {
			if( $trip->id == $atts['id'] )
				return '<a class="button '.$atts['class'].'" '.$lightItUp.' href="'.$trip->url.'">'.$atts['label'].'</a>';
		}

	return $html;//Added By AV.
}


add_action( 'media_buttons', 'rezbs_editor_button', 15 );
function rezbs_editor_button() {
		add_thickbox();
		echo '<a href="#TB_inline?width=600&height=800&inlineId=rb_insert" id="add-booking-button" title="RezBS Booking Integration" class="button thickbox">Add RezBS ‘Book Now’ Button</a>';
		
		
	}


add_action("media_buttons", "rezbs_shortcode_button_script");

function rezbs_shortcode_button_script() {
		//if( wp_script_is( "quicktags" ) )
			//{
				$api = new rezbs_API_Endpoint();
				$options = get_option( 'rezbs_options' );

				# Create modal window
				$trips_html = '<div id="rb_insert" style="display:none;"><div id="tripSelector">';
				$trips_html .= '<h2 >Insert Booking Button</h2>';
				$trips_html .= '<div><p>Select a Trip below to add the booking button to your post or page. </p>
				<p>You can assign a custom class to the button to match make the button match your existing theme. </p>
				<p>You can also change the default text shown on the button ("Book Now!").</p></div>';

				if( $trips = $api->rezbs_get_trips( $options['rezbs_api_key'] ) ) {
						$trips_html .= '<div style=" ">
						<select class="all_trips" name="all_trips">';
						$trips_html .= '<option value="" >Select a Trip</option>';
						foreach( $trips->alltrips as $trip ) {
								$trips_html .= '<option value="'.esc_attr( $trip->id ).'">'.esc_attr( $trip->name ).'</option>';
							}
						$trips_html .= '</select>
						</div>';
					}
				else {$trips_html .= '<p><strong>No available trips</strong></p>';}
				# Add custom "class" input box
				$trips_html .= '<div>
				<p>Add your own button class name <br><input type="text" name="rezbs_button" class="rezbs_btn" value="rezbs_button" /></p>
				</div>';

				# Add custom "label" input box
				$trips_html .= '<div>
				<p style="color:grey;">Enter a custom Label for your button: <br><input type="text" name="btn_label" class="btn_label" value="Book Now!"/></p>
				</div>';
				
				$trips_html .= '<div>
				<input type="button" class="button-primary" value="Add Button for This Trip" onclick="BuildButtonSC();"/>&nbsp;&nbsp;&nbsp;
            <a class="button" style="color:#bbb;" href="#" onclick="tb_remove(); return false;">Cancel</a>
            </div>
       ';
				$trips_html .= '</div></div>';
				
				//}
				echo $trips_html;
				?>

										
			<?php
	}

add_action( 'admin_init', 'rezbs_checkAuth', 5 );
function rezbs_checkAuth() {
		if ( isset( $_GET['settings-updated'] ) )
			{
				$options = get_option( 'rezbs_options' );

				if( !empty( $options['rezbs_username'] ) && !empty( $options['rezbs_api_key'] ) )
					if( rezbs_authorize( $options['rezbs_username'], $options['rezbs_api_key'] ) )
						{
							$options['rezbs_auth_success'] = 1;
							remove_action( 'admin_notices', 'rezbs_show_alert' );
						}
					else
						{
							$options['rezbs_auth_success'] = 0;
							add_action( 'admin_notices', 'rezbs_show_alert' );
						}

				update_option( 'rezbs_options', $options );
			}
	}

function rezbs_authorize( $user, $key ){
		$options = get_option( 'rezbs_options' );
		$url = 'https://rezbs.com/api/validate?uname='.$options['rezbs_username'].'&key='.$options['rezbs_api_key'];
		$response = wp_remote_get( $url );
		if( is_array( $response) ) 
			{
				$response = json_decode( $response['body'] );
				if( $response->authstatus == 'Key Authorised' )
					return TRUE;
			}
		
		return FALSE;
	}


add_action( 'wp_footer', 'rezbs_default_button_style', 5 );
function rezbs_default_button_style(){
	echo "<style>
.rezbs_button { color:white; padding:10px; border-radius:3px; background: #02c20f; background-image: -webkit-linear-gradient(top, #02c20f, #038a0c); background-image: -moz-linear-gradient(top, #02c20f, #038a0c); background-image: -ms-linear-gradient(top, #02c20f, #038a0c); background-image: -o-linear-gradient(top, #02c20f, #038a0c); background-image: linear-gradient(to bottom, #02c20f, #038a0c);} 	
.rezbs_button:hover {  color:white; background: #02c72a; background-image: -webkit-linear-gradient(top, #02c72a, #00e02d); background-image: -moz-linear-gradient(top, #02c72a, #00e02d); background-image: -ms-linear-gradient(top, #02c72a, #00e02d); background-image: -o-linear-gradient(top, #02c72a, #00e02d); background-image: linear-gradient(to bottom, #02c72a, #00e02d);}
</style>";
}

?>
