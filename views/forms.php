<?php 
// actual short code in main plugin file

if ( !empty( $_POST ) ){ 

    //code 
    //Echo('We have post data, update settings');
    //update_option( 'wp-easy-crm-settings', $_POST );

    // $sanitized_values = wp_easy_CRM_sanitize_settings($_POST);
    // update_option( 'wp-easy-crm-settings', $sanitized_values );

    // Get the new values from the form submission
        $new_site_key = $_POST['eacr_recaptcha_site_key'];
        $new_secret_key = $_POST['eacr_recaptcha_secret_key'];

        // Update the site key option
        update_option( 'eacr_recaptcha_site_key', $new_site_key );

        // Update the secret key option
        update_option( 'eacr_recaptcha_secret_key', $new_secret_key );

    
    $settings_updated = 'true';


}



echo '<h1>'.__( 'Forms', 'wp-easy-crm' ).'</h1>';
echo '<p>'.__( 'You can display the form below on your front end using the short code <pre>[contact_form_clients]</pre> on any page you like. ', 'wp-easy-crm' ).'</p>';
echo '<p>'.__( 'Once somebody submits the form below, it will appear in your contacts as a Lead.', 'wp-easy-crm' ).'</p>';
echo '<b>'.__( 'This form below only works with Google reCAPTCHA v2. Please enter the keys below. More info can be found in this link --> ', 'wp-easy-crm' ).'<a target="_blank" href="https://www.google.com/recaptcha/about/"> Google Captcha Information</a></b>';


?>
    <div class="wrap">
        
        <?php if (@$settings_updated == 'true') : ?>
            <div class="notice notice-success">
                <p><?php esc_html_e( 'Settings saved.', 'wp-easy-crm' ); ?></p>
            </div>
        <?php endif; ?>

        <h1>reCAPTCHA Settings</h1>
        <form method="post">
            <?php
            settings_fields( 'eacr-recaptcha-settings' );
            do_settings_sections( 'eacr-recaptcha-settings' );
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row">Site Key</th>
                    <td><input style="width:50%" type="password" name="eacr_recaptcha_site_key" value="<?php echo esc_attr( get_option( 'eacr_recaptcha_site_key' ) ); ?>" /></td>
                </tr>
                <tr valign="top">
                    <th scope="row">Secret Key</th>
                    <td><input style="width:50%" type="password" name="eacr_recaptcha_secret_key" value="<?php echo esc_attr( get_option( 'eacr_recaptcha_secret_key' ) ); ?>" /></td>
                </tr>
            </table>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php


// Register the reCAPTCHA settings fields
function eacr_captcha_register_settings() {
    register_setting( 'eacr-recaptcha-settings', 'eacr_recaptcha_site_key' );
    register_setting( 'eacr-recaptcha-settings', 'eacr_recaptcha_secret_key' );
}
add_action( 'admin_init', 'eacr_captcha_register_settings' );
?>

<div style="background-color:lightgray;">
<h1>Form Example</h1>
<form method="post" action="#">
            <label for="client-name">Full Name</label><br>
            <input type="text" name="client-name" maxlength="400" class="input-text" required style="width:50%">
            <br><br>
            <label for="client-email">Email</label><br>
            <input type="email" name="client-email" maxlength="400" class="input-text" required style="width:50%">
            <br><br>
            <label for="client-phone">Phone</label><br>
            <input type="tel" name="client-phone" maxlength="400" class="input-text" required style="width:50%">
            <br><br>
            <label for="client-email">Message</label><br>
            <textarea type="email" name="client-email" maxlength="1980" class="input-text" required style="width:50%" rows="5"></textarea><br><br>
            <br>
            <input type="hidden" name="action" value="submit_contact_form">
            <input type="submit" class="button wp-element-button" value="Submit">
        </form>
    
</div>