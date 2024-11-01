<?php
/*
Plugin Name: User Registration Approval for Formidable Forms
Plugin URI: https://wproot.dev/
Description: Allows admin to easily approve or reject user registration that uses forms built with Formidable Forms. Users will have pending role and cannot login until approved by admin. Must be used with Formidable Registration plugin AND Formidable Forms plugin.
Version: 1.0.1
Author: 10Horizons Plugins
Author URI: https://10horizonsplugins.com/
License: GPL-2.0+
License URI: http://www.gnu.org/licenses/gpl-2.0.txt
*/

// exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;


function thp_add_pending_user_role() {
	thp_formidable_registration_activation_check();
	
	global $wp_roles;

	$roles = $wp_roles->roles;
	if ( ! array_key_exists( 'pending', $roles ) ) {
		add_role( 'pending', 'Pending', array() );
	}
	
}
register_activation_hook( __FILE__, 'thp_add_pending_user_role' );


function thp_formidable_registration_activation_check() {
	if ( ! function_exists('is_plugin_inactive')) {
	    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}
	
	if ( is_plugin_inactive( 'formidable-registration/formidable-registration.php' ) ) {
		deactivate_plugins( plugin_basename( __FILE__ ) );
		wp_die( 'The plugin User Registration Approval for Formidable Forms you are trying to activate requires Formidable Registration plugin (WordPress User Registration add-on for Formidable Forms). Please install and activate Formidable Registration plugin before trying to activate this plugin again.' );
	}
}


function thp_formidable_registration_dependency_warning() {
	?>
	<div class="notice notice-error">
		<p>User Registration Approval for Formidable Forms plugin requires <a href="https://formidableforms.com/downloads/user-registration/">Formidable Registration plugin</a> to be installed and activated. You must have both plugins installed and active at the same time for it to work properly!</p>
	</div>
	<?php
}


function thp_formidable_registration_dependency_check() {
	if ( ! function_exists('is_plugin_inactive')) {
	    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}

	if( is_plugin_inactive( 'formidable-registration/formidable-registration.php' ) ) {
	   add_action( 'admin_notices', 'thp_formidable_registration_dependency_warning' );
	   return;
	}
}
add_action( 'plugins_loaded', 'thp_formidable_registration_dependency_check' );


function thp_user_approval_option( $values ) {
	// $values['id'] is form ID
	
	if ( ! function_exists('is_plugin_active')) {
	    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
	}
	
	if ( is_plugin_active( 'formidable-registration/formidable-registration.php' ) ) {
?>
	<tr>
		<td><h3>User Registration Approval Setting</h3></td>
	</tr>
	<tr>
		<td colspan="2">
		<?php 
		$opt = (array)get_option('thp_form_opt_user_needs_approval'); ?>
		<label for="thp_form_opt_user_needs_approval">
			<input type="checkbox" value="1" id="thp_form_opt_user_needs_approval" name="thp_form_opt_user_needs_approval" <?php echo (in_array($values['id'], $opt)) ? 'checked="checked"' : ''; ?> /> User registrations on this form require admin approval?
		</label>
		</td>
	</tr>
<?php }
}
add_action('frm_additional_form_options', 'thp_user_approval_option', 10, 1 );


function thp_user_approval_option_update( $options, $values ){
	$opt = (array)get_option('thp_form_opt_user_needs_approval');
	
	//save if value is different than before
	if ( isset( $values['thp_form_opt_user_needs_approval'] ) && ( ! isset($values['id'] ) || !in_array( $values['id'], $opt ) ) ) {
		$opt[] = $values['id']; //form ID is stored in an array variable in wp options table
		update_option('thp_form_opt_user_needs_approval', $opt);
	} else if ( ! isset( $values['thp_form_opt_user_needs_approval'] ) && isset( $values['id'] ) && in_array( $values['id'], $opt ) ) {
		$pos = array_search( $values['id'], $opt );
		unset( $opt[$pos] );
		update_option('thp_form_opt_user_needs_approval', $opt);
	}

	return $options;
}
add_filter('frm_form_options_before_update', 'thp_user_approval_option_update', 20, 2);


function thp_add_meta_data_to_user( $entry_id, $form_id ) {
	
	$opt = (array)get_option('thp_form_opt_user_needs_approval');
	
	if( in_array($form_id, $opt) ){
		
		$entry = FrmEntry::getOne($entry_id);
		
		$wp_user_object = new WP_User( $entry->user_id );
		
		$roles = $wp_user_object->roles;
		$role_after_approval = $roles[0];
		update_user_meta( $entry->user_id, 'thp_role_after_approval', $role_after_approval );
		
		$wp_user_object->set_role( 'pending' );
		
		update_user_meta( $entry->user_id, 'thp_user_meta_form_id', $form_id );
		update_user_meta( $entry->user_id, 'thp_user_meta_requires_approval', true );
	}
}
add_action('frm_after_create_entry', 'thp_add_meta_data_to_user', 30, 2);


function thp_deny_login_unapproved_user($user_object) {
if ( !is_wp_error($user_object) ) {
	
	$user_roles = $user_object->roles;
	$user_role = $user_roles[0];
	
	$user_unapproved = get_user_meta( $user_object->ID, 'thp_user_meta_requires_approval', true );
	
	if ( ($user_unapproved == true) && ($user_role == 'pending') ) {
		return new WP_Error( 'login_failed', __( "Your account has not been approved yet.", "ohs-frm-user-approval" ) );
	}
	else {
		return $user_object;
	}
}
else {
	return $user_object;
}
}
add_filter('authenticate', 'thp_deny_login_unapproved_user', 100, 1);


function thp_delete_user_confirmation($current_user, $user_ids) {
	$formidable_user_ids = array();
	
	foreach ( $user_ids as $user_id ) {
		$formidableuser = get_user_meta( $user_id, 'thp_user_meta_requires_approval', true );
		if ($formidableuser) {
			array_push($formidable_user_ids, $user_id);
		}
	}
	
	if (!empty($formidable_user_ids)) {
		$reminder = '<div class="notice notice-warning"><h3>REMINDER:</h3>';
		$reminder .= '<p>One or more users below were created by Formidable Forms. Once you click the button, they will be deleted <strong>PERMANENTLY</strong>. ';
		$reminder .= 'If you enabled entries for your form, you will still see their details in the <a href="'.admin_url("admin.php?page=formidable-entries").'">Entries section</a>. ';
		$reminder .= 'Once the button below is clicked, an email will be sent to the user(s) to let them know their registration has been <strong>REJECTED</strong>.</p>';
		$reminder .= '<p>&nbsp;</p>';
		$reminder .= '</div>';
		
		echo $reminder;
	}	
}
add_action( 'delete_user_form', 'thp_delete_user_confirmation', 10, 2 );


function thp_user_rejected_send_email($user_id){
//send notif email

	$formidableuser = get_user_meta( $user_id, 'thp_user_meta_requires_approval', true );
	
	if ($formidableuser) {
		
		$user_info = get_userdata($user_id);		
		$email = $user_info->user_email;
		
		/*if ( ! function_exists('is_plugin_active')) {
			require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
		}
		
		if( is_plugin_active( 'wp-ohs-formidable-forms-user-approval-pro/index.php' ) ) {
			
			$subject = '';
			$message = '';
			
		}
		else {*/
			
			$subject = 'Your registration has been rejected.';
			
			$message = "Hello, ".$user_info->first_name.",\n\n";
			$message .= "We are sorry to inform you that your registration on ";
			$message .= get_bloginfo( 'name' );
			$message .= " has been rejected. \n\n";
			$message .= "_____________________\n\n";
			$message .= get_bloginfo( 'name' )."\n\n";
			$message .= get_bloginfo( 'url' )."\n\n";
			
			$domain = $_SERVER['SERVER_NAME'];
			$headers[] = 'From: '.get_bloginfo( 'name' ).' <noreply@'.$domain.'>';
		
		/*}*/
				
		wp_mail( $email, $subject, $message, $headers );
	}
}
add_action( 'delete_user', 'thp_user_rejected_send_email' );


function thp_user_approved_send_email($user_id){
//send notif email

	$user_info = get_userdata($user_id);
	
	$email = $user_info->user_email;
	$subject = 'Your registration has been approved!';
	
	$message = "Hello, ".$user_info->first_name.",\n\n";
	$message .= "We are pleased to inform you that your registration on ";
	$message .= get_bloginfo( 'name' );
	$message .= " has been approved! You can now login with the username/email address and password that you registered. ";
	$message .= "If you forgot your password, you can reset it on the login screen. \n\n";
	$message .= "_____________________\n\n";
	$message .= get_bloginfo( 'name' )."\n\n";
	$message .= get_bloginfo( 'url' )."\n\n";
	
	$domain = $_SERVER['SERVER_NAME'];
	$headers[] = 'From: '.get_bloginfo( 'name' ).' <noreply@'.$domain.'>';
			
	wp_mail( $email, $subject, $message, $headers );
}


function thp_approve_user_button_clicked() {
	if ( isset( $_GET['page'] ) && isset( $_GET['thp_action'] ) ) {
		if ( $_GET['page'] == 'ohs-users-pending-approvals' && $_GET['thp_action'] == 'approve' ) {
			
			if (!current_user_can('manage_options')) exit;
			
			$validreq = check_admin_referer( 'thp_frm_user_action_approve' );
			
			if ($validreq) {
				$user_id = absint( $_GET['user'] );
				$new_role = get_user_meta( $user_id, 'thp_role_after_approval', true );
				
				$wp_user_object = new WP_User( $user_id );
				
				$wp_user_object->set_role( $new_role );
				
				update_user_meta( $user_id, 'thp_user_meta_requires_approval', false );
				delete_user_meta( $user_id, 'thp_user_meta_form_id' );
				delete_user_meta( $user_id, 'thp_role_after_approval' );
				
				//send notif email
				thp_user_approved_send_email($user_id);
				
				$location = esc_url_raw(admin_url( "users.php?page=ohs-users-pending-approvals&approved=true" ));
				wp_redirect($location);
				exit;
			}
			
		}
	}
}
add_action( 'admin_init', 'thp_approve_user_button_clicked' );


function thp_approval_successful_admin_notice() {
	$screen = get_current_screen();
	
	if ($screen->id === 'users_page_ohs-users-pending-approvals') {
		if (isset($_GET['approved'])) {
			
			if ($_GET['approved'] === 'true') : ?>				
				<div class="notice notice-success is-dismissible">
					<p>User has been approved. They will be notified by email shortly.</p>
				</div>				
			<?php else : ?>				
				<div class="notice notice-error is-dismissible">
					<p>An error has occured.</p>
				</div>				
			<?php endif;
			
		}
	}
}
add_action('admin_notices', 'thp_approval_successful_admin_notice');


function thp_pending_users_list_menu() {
    add_users_page( __( 'Users Pending Approvals', 'ohs-formidable-user-approval' ), __( 'Pending Approvals', 'ohs-formidable-user-approval' ), 'administrator', 'ohs-users-pending-approvals', 'thp_user_submenu_pending_approval' );
}
add_action( 'admin_menu', 'thp_pending_users_list_menu' );


function thp_user_submenu_pending_approval() { 

    //check if user has the required capability 
    if (!current_user_can('manage_options'))
    {
      wp_die( __('You do not have sufficient permissions to access this page.') );
    }
	
	$args = array(
		'role'         => 'pending',
		'meta_key'     => 'thp_user_meta_requires_approval',
		'meta_value'   => true,
	);

	$pendingusers = get_users( $args );
?>

<div class="wrap">

	<h2 style="margin-bottom:30px;">Users Pending Approvals</h2>
	
	<table class="wp-list-table widefat fixed striped users">
		<thead>
			<tr>
				<th>Username</th>
				<th>Name</th>
				<th>Email</th>
				<th>Form Submitted</th>
				<th>Actions</th>
			</tr>
		</thead>
	
		<tbody>			
			<?php 
			if ($pendingusers) {
			foreach ( $pendingusers as $pendinguser ) { ?>
			<tr>
				<td class="username column-username column-primary">
					<?php
					if ( current_user_can( 'edit_user', $pendinguser->ID ) ) {
						if ( get_current_user_id() == $pendinguser->ID ) {
							$edit_link = 'profile.php';
						} else {
							$edit_link = admin_url("user-edit.php?user_id=$pendinguser->ID");
						}
						echo '<a href="'.esc_url( $edit_link ).'">'.get_avatar( $pendinguser->user_email, 32 ).' '.esc_html( $pendinguser->user_login ).'</a>';
					} else {
						echo get_avatar( $pendinguser->user_email, 32 ).' '.esc_html( $pendinguser->user_login );
					}
					?>
				</td>
				<td>
					<?php echo esc_html( $pendinguser->display_name ) ?>
				</td>
				<td>
					<a href="mailto:<?php echo esc_html( $pendinguser->user_email ) ?>"><?php echo esc_html( $pendinguser->user_email ) ?></a>
				</td>
				<td>
					<?php
					$formID = get_user_meta( $pendinguser->ID, 'thp_user_meta_form_id', true );
					$formname = FrmFormsHelper::edit_form_link( $formID );
					if ( ($formID != '') && ($formname != '') ) {						
						echo $formname;
					}
					else {
						echo 'Error. Form does not exist';
					}
					?>
				</td>
				<td>
					<?php
					$thp_approve_link = wp_nonce_url( admin_url( "users.php?page=ohs-users-pending-approvals&thp_action=approve&amp;user=$pendinguser->ID" ), 'thp_frm_user_action_approve' );
					$thp_reject_link = wp_nonce_url( admin_url( "users.php?action=delete&amp;user=$pendinguser->ID" ), 'bulk-users' );
					?>
					<a id="ohs-approve-button" class="button" style="margin-right:10px;" href="<?php echo esc_url($thp_approve_link); ?>">Approve</a>
					<a id="ohs-reject-button" class="button" href="<?php echo esc_url($thp_reject_link); ?>">Reject</a>
				</td>
			</tr>
			<?php }
			} else { ?>
				<tr>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
					<td style="text-align:center">No pending users.</td>
					<td>&nbsp;</td>
					<td>&nbsp;</td>
				</tr>
			<?php } ?>
		</tbody>
	</table>
	
</div>

<?php }
