<?php

    // print_r($_POST);


    if ( !empty( $_POST ) ){ 

    //code 
    //Echo('We have post data, update settings');
    //update_option( 'wp-easy-crm-settings', $_POST );

    $sanitized_values = wp_easy_CRM_sanitize_settings($_POST);
    update_option( 'wp-easy-crm-settings', $sanitized_values );
    
    $settings_updated = 'true';


    }

    $options = get_option( 'wp-easy-crm-settings' );

    $currency_options = array(
        'USD' => __( 'US Dollars', 'wp-easy-crm' ),
        'CRC' => __( 'Costa Rican Colon' ),
        'EUR' => __( 'Euros', 'wp-easy-crm' ),
        'GBP' => __( 'British Pounds', 'wp-easy-crm' ),
        'JPY' => __( 'Japanese Yen', 'wp-easy-crm' ),
        'AUD' => __( 'Australian Dollar', 'wp-easy-crm' ),
        'CAD' => __( 'Canadian Dollar', 'wp-easy-crm' ),
        'CHF' => __( 'Swiss Franc', 'wp-easy-crm' ),
        'CNY' => __( 'Chinese Yuan', 'wp-easy-crm' ),
        'HKD' => __( 'Hong Kong Dollar', 'wp-easy-crm' ),
        'NZD' => __( 'New Zealand Dollar', 'wp-easy-crm' ),
        
    );


?>
    <div class="wrap">
        <h1><?php esc_html_e( 'Easy CRM Settings', 'wp-easy-crm' ); ?></h1>
        <h2><?php esc_html_e( 'Quotation and Invoice settings', 'wp-easy-crm' ); ?></h2>

        <?php if (@$settings_updated == 'true') : ?>
            <div class="notice notice-success">
                <p><?php esc_html_e( 'Settings saved.', 'wp-easy-crm' ); ?></p>
            </div>
        <?php endif; ?>
        <form method="post" action="?page=wp-easy-crm-settings">
            <?php settings_fields( 'wp-easy-crm-settings' ); ?>
            <table class="form-table">
                <tbody>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Header HTML', 'wp-easy-crm' ); ?></th>
                        <td>
                            <?php wp_editor( $options['header_html'], 'header_html', array( 'textarea_name' => 'header_html','textarea_rows' => get_option('default_post_edit_rows', 8), ) ); ?>
                            <p class="description"><?php esc_html_e( 'Enter custom HTML as the header of Quote/Invoice. This could be a place to create your letter head with a logo and address of your company', 'wp-easy-crm' ); ?></p>
                        </td>
                    </tr>
                    <!-- <tr>
                        <th scope="row"><?php esc_html_e( 'Quote / Invoice Introduction sentence / Salutation', 'wp-easy-crm' ); ?></th>
                        <td>
                            <?php wp_editor( $options['introduction'], 'introduction', array( 'textarea_name' => 'introduction','textarea_rows' => get_option('default_post_edit_rows', 8), ) ); ?>
                            <p class="description"><?php esc_html_e( 'Enter an introduction that is used as salutation in your Quotes/Invoices.', 'wp-easy-crm' ); ?></p>
                        </td>
                    </tr> -->
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Standard Tax Percentage', 'wp-easy-crm' ); ?></th>
                        <td>
                        <input type="number" id="standardtaxpercentage" name="standardtaxpercentage" step="1.0" min="0" max="100" value="<?php echo esc_attr( $options['standardtaxpercentage']); ?>" />% 
                             <p class="description"><?php esc_html_e( 'Enter the standard percentage of tax you charge on your items.', 'wp-easy-crm' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Currency', 'wp-easy-crm' ); ?></th>
                        <td>
                            <select name="currency">
                                <?php foreach ( $currency_options as $currency => $label ) : ?>
                                    <option value="<?php echo esc_attr( $currency ); ?>" <?php selected( $options['currency'], $currency ); ?>><?php echo esc_html( $label ); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <p class="description"><?php esc_html_e( 'Choose the default currency for your Quotes/Invoices.', 'wp-easy-crm' ); ?></p>
                        </td>
                    </tr>
                    <tr>
                        <th scope="row"><?php esc_html_e( 'Footer HTML', 'wp-easy-crm' ); ?></th>
                        <td>
                            <?php wp_editor( $options['footer_html'], 'footer_html', array( 'textarea_name' => 'footer_html','textarea_rows' => get_option('default_post_edit_rows', 8), ) ); ?>
                            <p class="description"><?php esc_html_e( 'Enter custom HTML to be added as the footer to your Quotes/Invoices.', 'wp-easy-crm' ); ?></p>
                        </td>
                    </tr>
                </tbody>
            </table>
            <?php submit_button( __( 'Save Settings', 'wp-easy-crm' ) ); ?>
        </form>
    </div>                   



