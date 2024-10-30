<?php
/**
* Plugin Name: iOS Smart App Banner For Safari
* Plugin URI: http://carpemobile.com/
* Description: Plugin for adding an iOS Smart App Banner to the top of selected pages for iOS mobile safari users.
* Version: 1.0
* Author: Carpe Mobile
* Author URI: http://carpemobile.com/
**/

// Block direct access to plugin to avoid script kiddies and script kitties
defined( 'ABSPATH' ) or die( 'Access not granted' );

//-------------------------------------------------------------------------------
// Functions
//-------------------------------------------------------------------------------

// Outputs the smart_banner on each page that it is configured for
function carpe_output_smart_banner()
{

    global $post;
    $post_id = $post->ID; 
    // Try to retrieve app id
    $app_id = get_post_meta($post_id, '_carpe_ios_app_id', true);

    // If app_id doesn't exist or is blank then exit
    if (is_null($app_id) or $app_id == "") {
        echo "<meta name=\"carpemobile\" content=\"no_app_id_ $post_id\">";
        return;
    }

    // Try to retrieve affiliate_id, affiliate_campaign and app_argumet
    $affiliate_id = get_post_meta($post_id, '_carpe_affiliate_param', true);
    $affiliate_campaign = get_post_meta($post_id, '_carpe_affiliate_campaign', true);
    $app_argument = get_post_meta($post_id, '_carpe_app_argument', true);
    

    $options = "";

    // Add affiliate if it exists to the options otherwise use default
    if (!is_null($affiliate_id) and $affiliate_id != "")
    {
        $options = "$options, affiliate-data=at=$affiliate_id";
        
        // Add affiliate campaign if it exists to the affiliate options
        if (!is_null($affiliate_campaign) and $affiliate_campaign != "")
        {
            $options = $options. "&ct=$affiliate_campaign";
        }

    }
    else
    {
        $options = "$options, affiliate-data=at=11l5aw&ct=smart-app-banner";
    }

    // Add app argument if it exists to the options
    if (!is_null($app_argument) and $app_argument != "")
    {
        $options = "$options, app-argument=$app_argument";
    }

    // Output the header
    echo "<meta name=\"apple-itunes-app\" content=\"app-id=$app_id$options\">";

}



// Adds a box to the main column on the Post and Page edit screens.
function carpe_add_meta_box() {

    // No longer using hard-coded page types because many themes have custom page types
	//$screens = array( 'post', 'page' );

    /*
    foreach (get_post_types() as $screen)
    {
        add_meta_box(
            'wsl_smart_app_banner_id',                        // this is HTML id of the box on edit screen
            __('Smart App Banner','wsl-smart-app-banner'),    // title of the box
            'wsl_smart_app_banner_display_options',           // function to be called to display the checkboxes, see the function below
            $screen,                                         // on which edit screen the box should appear
            'normal',                                         // part of page where the box should appear
            'default'                                         // priority of the box
        );
    }
    */
    
    
    // Loop through all post types and add the meta box to each
	foreach (get_post_types() as $screen)
    {
		add_meta_box(
			'ios_smart_app_banner_for_safari_id',                               // ID of the box on the edit screen
			__( 'iOS Smart App Banner For Safari', 'myplugin_textdomain' ),     // Title of meta box
			'carpe_display_meta_box',                                           // Function to be called to display the box
			$screen
		);
	}
}

// Actual display of the meta box content and saved data (if any)
function carpe_display_meta_box( $post )
{

	// Add a nonce field so we can check for it later.
	wp_nonce_field( 'carpe_save_meta_box_data', 'carpe_meta_box_nonce' );

    
    // Use get_post_meta() to retrieve an existing value from the database
    
    // Get iOS App ID from database and display
	$iosAppId = get_post_meta( $post->ID, '_carpe_ios_app_id', true );
	echo '<div title="Nine digit numerical App ID for your App. Leave this blank if you do not want to display a Smart App Banner on this page.">';
	echo '<p><label class="carpe" for="carpe_ios_app_id_field">';
	_e( 'iOS App Store ID (Required)', 'myplugin_textdomain' );
	echo '</label> ';
	echo '<input type="text" id="carpe_ios_app_id_field" name="carpe_ios_app_id_field" value="' . esc_attr($iosAppId) . '" size="15" /></p>';
	echo '</div>';

    // Get Affiliate Param from database and display
	$affiliateParam = get_post_meta( $post->ID, '_carpe_affiliate_param', true );
	echo '<div title="Optional six character Affiliate Code from PHG.">';
	echo '<p><label class="carpe" for="carpe_affiliate_param_field">';
	_e( 'PHG Affiliate Code', 'myplugin_textdomain' );
	echo '</label> ';
	echo '<input type="text" id="carpe_affiliate_param_field" name="carpe_affiliate_param_field" value="' . esc_attr($affiliateParam) . '" size="15" placeholder="(Optional)" /></p>';
	echo '</div>';
	
    // Get Affiliate Campaign from database and display
	$affiliateCampaign = get_post_meta( $post->ID, '_carpe_affiliate_campaign', true );
	echo '<div title="Optional campaign text which lets you track your affiliate sales from a specific marketing campaign.">';
	echo '<p><label class="carpe" for="carpe_affiliate_campaign_field">';
	_e( 'PHG Affiliate Campaign', 'myplugin_textdomain' );
	echo '</label> ';
	echo '<input type="text" id="carpe_affiliate_campaign_field" name="carpe_affiliate_campaign_field" value="' . esc_attr($affiliateCampaign) . '" size="60" placeholder="(Optional) Campaign name" /></p>';
	echo '</div>';
    
    // Get App Argument from database and display
	$appArgument = get_post_meta( $post->ID, '_carpe_app_argument', true );
	echo '<div title="Optional URL Scheme which passes codes for deep linking to your app.">';
	echo '<p><label class="carpe" for="carpe_app_argument_field">';
	_e( 'App Argument', 'myplugin_textdomain' );
	echo '</label> ';
	echo '<input type="text" id="carpe_app_argument_field" name="carpe_app_argument_field" value="' . esc_attr($appArgument) . '" size="60" placeholder="(Optional) myapp://open_params" /></p>';
	echo '</div>';
	
	
    echo '<style>label.carpe{width: 180px;display: inline-block;}</style>';
    
    echo '<p>See <a href="https://developer.apple.com/library/mac/documentation/AppleApplications/Reference/SafariWebContent/PromotingAppswithAppBanners/PromotingAppswithAppBanners.html#//apple_ref/doc/uid/TP40002051-CH6-SW2" target="_blank">Apple Documentation</a> ';
	echo 'for help on how to determine your app id, affiliate settings and app arguments.</p>';

}

// Save custom meta data when the post is saved. See docs https://codex.wordpress.org/Function_Reference/add_meta_box for more details
function carpe_save_meta_box_data( $post_id ) {

	/*
	 * We need to verify this came from our screen and with proper authorization,
	 * because the save_post action can be triggered at other times.
	 */

	// Check if our nonce is set. Nonce is used to verify that the request came from the current site
	if ( ! isset( $_POST['carpe_meta_box_nonce'] ) )
    {
		return;
	}

	// Verify that the nonce is valid.
	if ( ! wp_verify_nonce( $_POST['carpe_meta_box_nonce'], 'carpe_save_meta_box_data' ) )
    {
		return;
	}

	// If this is an autosave, our form has not been submitted, so we don't want to do anything.
	if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE )
    {
		return;
	}

	// Check the user's permissions.
	if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] )
    {
		if ( ! current_user_can( 'edit_page', $post_id ) )
        {
			return;
		}
	}
    else
    {
		if ( ! current_user_can( 'edit_post', $post_id ) )
        {
			return;
		}
	}

    // Validation that post came from correct site and a user with correct permissions has succeeded so begin saving our data
	
	// Make sure that the ios app id is set otherwise don't save anything and return
	if ( ! isset( $_POST['carpe_ios_app_id_field'] ) ) {
		return;
	}

    
    // Save app id
    if (isset( $_POST['carpe_ios_app_id_field']))
    {
		// Sanitize user input.
        $ios_app_id = sanitize_text_field( $_POST['carpe_ios_app_id_field'] );

        // Adds the meta field in the database if it doesn't exist otherwise it updates existing
        update_post_meta( $post_id, '_carpe_ios_app_id', $ios_app_id );
	}
    
    // Save affiliate code
    if (isset( $_POST['carpe_affiliate_param_field']))
    {
		// Sanitize user input.
        $ios_affiliate_param = sanitize_text_field( $_POST['carpe_affiliate_param_field'] );

        // Adds the meta field in the database if it doesn't exist otherwise it updates existing
        update_post_meta( $post_id, '_carpe_affiliate_param', $ios_affiliate_param );
	}
    
    // Save affiliate campaign
    if (isset( $_POST['carpe_affiliate_campaign_field']))
    {
		// Sanitize user input.
        $ios_affiliate_campaign = sanitize_text_field( $_POST['carpe_affiliate_campaign_field'] );

        // Adds the meta field in the database if it doesn't exist otherwise it updates existing
        update_post_meta( $post_id, '_carpe_affiliate_campaign', $ios_affiliate_campaign );
	}
    
    // Save app id
    if (isset( $_POST['carpe_app_argument_field']))
    {
		// Sanitize user input.
        $ios_app_argument = sanitize_text_field( $_POST['carpe_app_argument_field'] );

        // Adds the meta field in the database if it doesn't exist otherwise it updates existing
        update_post_meta( $post_id, '_carpe_app_argument', $ios_app_argument );
	}

}




//-------------------------------------------------------------------------------
// Add action hooks
//-------------------------------------------------------------------------------

// Add the meta boxes to the admin interface
add_action( 'add_meta_boxes', 'carpe_add_meta_box' );

// Add hook for admin pages to save our data when the user presses save button
add_action( 'save_post', 'carpe_save_meta_box_data' );

// Add hook to place the smart app banner in the output page
add_action( 'wp_head', 'carpe_output_smart_banner' );
