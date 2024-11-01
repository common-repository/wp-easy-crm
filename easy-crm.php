<?php

/**
 * Plugin Name:       Easy CRM
 * Description:       Collect new leads, manage clients, quotations, invoices, tasks and more for your entire team
 * Version:           1.0.24
 * Author:            IT-iCO SRL
 * Author URI:        https://it-ico.com
 * Text Domain:       wp-easy-crm
 * Domain Path:       /languages
 */

 // Act on plugin activation
register_activation_hook( __FILE__, "init_db_eacr" );
register_uninstall_hook( __FILE__, 'eacr_uninstall' );


if ( !defined( 'ABSPATH' ) ) exit;

define("EASYCRM_PLUGIN_DIR_PATH",plugin_dir_path(__FILE__));


add_action( 'init', 'eacr_wpdocs_load_textdomain' );
 
function eacr_wpdocs_load_textdomain() {
    load_plugin_textdomain( 'wp-easy-crm', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' ); 
}


// Activate Plugin
// Initialize DB Tables
function init_db_eacr() {

	// WP Globals
	global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
	// Customer Table
	$clientTable = $wpdb->prefix . 'clients';

	// Create client Table if not exist

        $sql = "CREATE TABLE $clientTable (
            id int(11) NOT NULL AUTO_INCREMENT,
            clientname varchar(500) NOT NULL,
            clientstatus varchar(150),
		    email varchar(500) NOT NULL,
		    nombrecontacto varchar(500) NOT NULL,
		    nota varchar(2000),
            tagsrelations varchar(2000),
            clientsource varchar(150),
		    telefono varchar(500),
		    provincia varchar(150),
		    direccion varchar(500),
            lastupdate_at datetime,
            idaccountingregionfk int(11) NOT NULL DEFAULT 1,
            created_at datetime NOT NULL,
            PRIMARY KEY client_id (id)
        ) $charset_collate;";

        add_option( "clients_db_version", "1.0" );

		// Include Upgrade Script
		require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	
		// Create Table
		dbDelta( $sql );




    $quoteclientTable = $wpdb->prefix . 'quoteclient';

	// Create Quote Table if not exist

        $sqlquote = "CREATE TABLE $quoteclientTable (
            id int(11) NOT NULL AUTO_INCREMENT,
            clientidfk int(11) NOT NULL,
            quotestatus varchar(500) NOT NULL,
            header varchar(2000),
            total varchar(500),
            introduction varchar(2000),
            clientinfo varchar(2000),
            currency varchar(3),
		    footer varchar(2000),
            lastupdate_at datetime,
            created_at datetime NOT NULL,
            FOREIGN KEY client_id (clientidfk) REFERENCES $clientTable(id),
            PRIMARY KEY quote_id (id)
            
        ) $charset_collate;";

        add_option( "clients_db_version", "1.0" );

		// Include Upgrade Script
		require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	
		// Create Table
		dbDelta( $sqlquote );



        $accountingregionTable = $wpdb->prefix . 'eacraccountingregion';

	// Create Accounting region Table if not exist

        $sqlaccregion = "CREATE TABLE $accountingregionTable (
            id int(11) NOT NULL AUTO_INCREMENT,
            regionname varchar(500) NOT NULL,
            lastupdate_at datetime,
            created_at datetime NOT NULL,
            PRIMARY KEY accregion_id (id)            
        ) $charset_collate;";

        add_option( "clients_db_version", "1.0" );

		// Include Upgrade Script
		require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	
		// Create Table
		dbDelta( $sqlaccregion );

        //add default entry called general
        // Check if the default entry exists and if not, insert it
        $default_region_exists = $wpdb->get_var("SELECT COUNT(*) FROM $accountingregionTable WHERE regionname = 'General'");
        if ($default_region_exists == 0) {
            $wpdb->insert(
                $accountingregionTable,
                array(
                    'regionname' => 'General',
                    // Assuming you want to set the current time for lastupdate_at and created_at
                    'lastupdate_at' => current_time('mysql'),
                    'created_at' => current_time('mysql')
                )
            );
        }



    $quoteLineclientTable = $wpdb->prefix . 'lineitemquote';

    // Create Quote Line item Table if not exist

    $sqlprod = "CREATE TABLE $quoteLineclientTable (
        id int(11) NOT NULL AUTO_INCREMENT,
        quoteidfk int(11) NOT NULL,
        lineitem varchar(500) NOT NULL,
        quantity int(11) NOT NULL,
        price decimal(10,2),
        tax int(11),
        created_at datetime NOT NULL,
        FOREIGN KEY quote_id (quoteidfk) REFERENCES $quoteclientTable(id),
        PRIMARY KEY product_id (id)
    ) $charset_collate;";

    add_option( "clients_db_version", "1.0" );

    // Include Upgrade Script
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

    // Create Table
    dbDelta( $sqlprod );




    $taskTable = $wpdb->prefix . 'task';

    // Create task Table if not exist

    $sqltask = "CREATE TABLE $taskTable (
        id int(11) NOT NULL AUTO_INCREMENT,
        clientidfk int(11),
        useridfk int(11) NOT NULL,
        completed int(1),
        tasktitle varchar(200) NOT NULL,
        taskdescription varchar(20000),
        start_at datetime NOT NULL,
        end_at datetime NOT NULL,
        created_at datetime NOT NULL,
        PRIMARY KEY task_id (id)
    ) $charset_collate;";

    add_option( "clients_db_version", "1.0" );

    // Include Upgrade Script
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

    // Create Table
    dbDelta( $sqltask );


    $tagTable = $wpdb->prefix . 'easytags';

    // Create tags Table if not exist

    $sqltags = "CREATE TABLE $tagTable (
        id int(11) NOT NULL AUTO_INCREMENT,
        tagtitle varchar(200) NOT NULL,
        tagcolor varchar(200),
        created_at datetime NOT NULL,
        PRIMARY KEY tag_id (id)
    ) $charset_collate;";

    add_option( "clients_db_version", "1.0" );

    // Include Upgrade Script
    require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );

    // Create Table
    dbDelta( $sqltags );




    $logclientTable = $wpdb->prefix . 'logclient';

	// Create log Table if not exist

        $sqllog = "CREATE TABLE $logclientTable (
            id int(11) NOT NULL AUTO_INCREMENT,
            clientidfk int(11) NOT NULL,
            logtitle varchar(200) NOT NULL,
		    logdescription varchar(2000),
            createdbyperson varchar(200) NOT NULL,
            lastupdate_at datetime,
            created_at datetime NOT NULL,
            FOREIGN KEY client_id (clientidfk) REFERENCES $clientTable(id),
            PRIMARY KEY log_id (id)
            
        ) $charset_collate;";

        add_option( "clients_db_version", "1.0" );

		// Include Upgrade Script
		require_once( ABSPATH . '/wp-admin/includes/upgrade.php' );
	
		// Create Table
		dbDelta( $sqllog );

}
function eacr_uninstall() {
    // Uninstallation stuff here
    // WP Globals
	global $wpdb;
    $charset_collate = $wpdb->get_charset_collate();
	// Customer Tables
	$clientTable = $wpdb->prefix . 'clients';
    $quoteclientTable = $wpdb->prefix . 'quoteclient';
    $quoteLineclientTable = $wpdb->prefix . 'lineitemquote';
    $taskTable = $wpdb->prefix . 'task';
    $logclientTable = $wpdb->prefix . 'logclient';
    $accountingregionTable = $wpdb->prefix . 'eacraccountingregion';
    $tagTable = $wpdb->prefix . 'easytags';


    $wpdb->query( "DROP TABLE IF EXISTS $quoteLineclientTable" );
    delete_option("clients_db_version");

    $wpdb->query( "DROP TABLE IF EXISTS $quoteclientTable" );
    delete_option("clients_db_version");

    $wpdb->query( "DROP TABLE IF EXISTS $taskTable" );
    delete_option("clients_db_version");

    $wpdb->query( "DROP TABLE IF EXISTS $logclientTable" );
    delete_option("clients_db_version");

    $wpdb->query( "DROP TABLE IF EXISTS $clientTable" );
    delete_option("clients_db_version");

    $wpdb->query( "DROP TABLE IF EXISTS $accountingregionTable" );
    delete_option("clients_db_version");

    $wpdb->query( "DROP TABLE IF EXISTS $tagTable" );
    delete_option("clients_db_version");

    //remove settings
    unregister_setting( 'wp-easy-crm-settings', 'wp-easy-crm-settings');

}

function eacr_crm_register_settings() {
    register_setting( 'wp-easy-crm-settings', 'wp-easy-crm-settings', array(
    'sanitize_callback' => 'wp_easy_CRM_sanitize_settings',
    'default' => array(
    'header_html' => __('<p>This is sample text for a header. You can change it in the settings of this plugin.</p>', 'wp-easy-crm'),
    // 'introduction' => __('Hello {clientname},  ', 'wp-easy-crm'),
    'currency' => 'USD',
    'standardtaxpercentage' => '13',
    'footer_html' => __('<p>This is sample text for a footer in your quotes/invoices. You can change it in the settings of this plugin.</p>', 'wp-easy-crm'),
    ),
    ) );
}
add_action( 'admin_init', 'eacr_crm_register_settings' );

function wp_easy_CRM_sanitize_settings( $input ) {

    // Allowed HTML tags and attributes
    $allowed_tags = array(
        'div' => array(),
        'p' => array(),
        'br' => array(),
        'img' => array(
            'src' => array(),
            'alt' => array(),
            'width' => array(),
            'height' => array()
        ),
        'strong' => array(),
        'h1' => array(),
        'h2' => array(),
        'h3' => array(),
        'h4' => array(),
        'h5' => array(),
        'h6' => array(),
        'em' => array(),
        'ul' => array(),
        'ol' => array(),
        'li' => array(),
        'blockquote' => array(),
        'ins' => array(),
        'del' => array(),
        'code' => array(),
        'a' => array(
            'href' => array()
        )
    );

    $sanitized = array();
    if ( isset( $input['header_html'] ) ) {
    // Apply the wpautop filter to insert HTML line break tags
    $htmlheader = wpautop($input['header_html']);
    $sanitized['header_html'] = wp_kses( $htmlheader, $allowed_tags );
    }
    // if ( isset( $input['introduction'] ) ) {
    // $sanitized['introduction'] = wp_kses_post( $input['introduction'] );
    // }
    if ( isset( $input['standardtaxpercentage'] ) ) {
        $sanitized['standardtaxpercentage'] = wp_kses_post( $input['standardtaxpercentage'] );
    }
    if ( isset( $input['currency'] ) ) {
    $sanitized['currency'] = sanitize_text_field( $input['currency'] );
    }
    if ( isset( $input['footer_html'] ) ) {
    $htmlfooter = wpautop($input['footer_html']);
    $sanitized['footer_html'] = wp_kses( $htmlfooter, $allowed_tags );
    }
    return $sanitized;
}

//short code contact form
function eacr_display_contact_form() {

    if(get_option( 'eacr_recaptcha_site_key' ) == "" || get_option( 'eacr_recaptcha_site_key' ) == null || get_option( 'eacr_recaptcha_secret_key' ) == "" || get_option( 'eacr_recaptcha_secret_key' ) == null){
        return '<h2>Google reCaptcha keys missing. Please go to the forms menu of Easy CRM and input your reCaptcha keys to make this form work.</h2>';
    }
    else{
        $form = '
                <script src="https://www.google.com/recaptcha/api.js" async defer></script>

                <form id="easycrmcontactform" method="post" action="' . esc_url(admin_url('admin-post.php')) . '">
                <label for="client-name">Full Name</label><br>
                <input type="text" name="client-name" maxlength="400" class="input-text" required style="width:70%">
                <br><br>
                <label for="client-email">Email</label><br>
                <input type="email" name="client-email" maxlength="400" class="input-text" required style="width:70%">
                <br><br>
                <label for="client-phone">Phone</label><br>
                <input type="tel" name="client-phone" maxlength="400" class="input-text" required style="width:70%">
                <br><br>
                <label for="client-note">Message</label><br>
                <textarea type="text" name="client-note" maxlength="1980" class="input-text" required style="width:70%" rows="5"></textarea><br>
                <input type="hidden" name="action" value="submit_contact_form">
                <input type="datetime-local" name="lastupdate_at" value="'.esc_html(date('Y-m-d\TH:i:s')).'" hidden/>
                <input type="datetime-local" name="created_at" value="'.esc_html(date('Y-m-d\TH:i:s')).'" hidden/>
                <div class="g-recaptcha" data-sitekey="'.get_option( 'eacr_recaptcha_site_key' ).'"></div>
                <div class="error" style="display:none">Please complete the reCAPTCHA verification.</div>
                <input type="submit" id="easysubmit" class="button wp-element-button" value="Submit">
                
            </form>
            <script>
            function onSubmit(token) {
              // Code to handle successful reCAPTCHA verification
              // ...
            }
            
            function onFormSubmit(event) {
              var response = grecaptcha.getResponse();
              if(response.length == 0) {
                event.preventDefault();
                alert("Please complete the reCAPTCHA");
              } else {
                // Call the onSubmit() function to handle successful reCAPTCHA verification
                onSubmit(response);
              }
            }
            
            // Add an event listener to the forms submit event
            document.getElementById("easycrmcontactform").addEventListener("submit", onFormSubmit);
            </script>
            ';
        return $form;
    }
}
add_shortcode('contact_form_clients', 'eacr_display_contact_form');

function eacr_submit_contact_form() {

    // Check if the reCAPTCHA response is valid
    if (isset($_POST['g-recaptcha-response'])) {
        $response = $_POST['g-recaptcha-response'];
        $isValid = eacr_verifyRecaptcha($response);
        if ($isValid) {
        // reCAPTCHA verification succeeded. Process the form data.
        // ...
            if (isset($_POST['client-name']) && isset($_POST['client-phone']) && isset($_POST['client-email']) && isset($_POST['client-note'])) {
                global $wpdb;
                $table_name = $wpdb->prefix . 'clients';
                $data = array(
                    'clientname' => sanitize_text_field($_POST['client-name']),
                    'nombrecontacto' => sanitize_text_field($_POST['client-name']),
                    'telefono' => sanitize_text_field($_POST['client-phone']),
                    'email' => sanitize_email($_POST['client-email']),
                    'nota' => sanitize_text_field($_POST['client-note']),
                    "clientstatus"=>sanitize_text_field('lead'),
                    "clientsource"=>sanitize_text_field('website'),
                    "lastupdate_at"=>sanitize_text_field($_POST['lastupdate_at']),
                    "created_at"=>sanitize_text_field($_POST['created_at'])
                );
                $wpdb->insert($table_name, $data);
                if($wpdb->insert_id > 0){
                    // $msg = "<div class='updated update'>".esc_html(__( 'client successfully saved', 'wp-easy-crm' ))."</div>";
                    $msg = esc_html(__( 'Form successfully submitted. Thank you! We will get in touch with you shortly. Feel free to look through our website.', 'wp-easy-crm' ));
                    echo '<script>alert("'.sanitize_text_field($msg).'");window.location.href = "/";'.
                    '</script>';
                    // wp_redirect(get_permalink(get_page_by_title('Thank You')));
                    // wp_redirect(home_url());
        
                    //send email
                    $to = get_option( 'admin_email' );
                    $subject = 'New Lead added from Website form';
                    $message = ('A new lead from '.sanitize_text_field($_POST['client-name'])." has been added to your CRM. The lead left the following note: ".sanitize_text_field($_POST['client-note']));
                    wp_mail($to, $subject, $message );
        
        
                }else{
                    // $msg = "<div style='color:red'>".esc_html(__( 'error - client information couldn\'t be stored', 'wp-easy-crm' ))."</div>";
                    $msg = esc_html(__( 'error - client information couldn\'t be stored: ', 'wp-easy-crm' )).$wpdb->last_error;
        
                    echo '<script>alert("'.sanitize_text_field($msg).'")</script>';
                }
                exit;
            }
        } 
        else {
        // reCAPTCHA verification failed. Display an error message.
        // ...
        $msg = esc_html(__( 'error - reCAPTCHA verification failed. Please go back and try again ', 'wp-easy-crm' ));
        
                    echo '<script>alert("'.sanitize_text_field($msg).'")</script>';
        }
    
    }   
    else {
        // The reCAPTCHA response was not set.
        // ...
        $msg = esc_html(__( 'error - reCAPTCHA verification not set. Do not forget to set the Captcha to submit the form. Please go back and try again. ', 'wp-easy-crm' ));
        
                    echo '<script>alert("'.sanitize_text_field($msg).'")</script>';
    }
    


    
}
add_action('admin_post_nopriv_submit_contact_form', 'eacr_submit_contact_form');
add_action('admin_post_submit_contact_form', 'eacr_submit_contact_form');

function eacr_verifyRecaptcha($response) {
    $url = "https://www.google.com/recaptcha/api/siteverify";
    $data = array(
      'secret' => get_option( 'eacr_recaptcha_secret_key' ),
      'response' => $response
    );
    $options = array(
      'http' => array(
        'header'  => 'Content-type: application/x-www-form-urlencoded',
        'method'  => 'POST',
        'content' => http_build_query($data),
      ),
    );
    $context  = stream_context_create($options);
    $result = file_get_contents($url, false, $context);
    $resultJson = json_decode($result);
    return $resultJson->success;
  }

//Dashboard widget My Tasks
function eacr_dashboard_widget() {
    global $wpdb;
    $myuserid = get_current_user_id(); 
    $taskTable = $wpdb->prefix . 'task';
    $clientTable = $wpdb->prefix . 'clients';
    
    // Add buttons to filter tasks
    echo '
    <div style="margin-bottom: 10px;">
        <button onclick="filterTasks(\'started\')">Show Started Tasks</button>
        <button onclick="filterTasks(\'due\')">Show Due Tasks</button>
        <button onclick="filterTasks(\'all\')">Show All Open Tasks</button>
    </div>
    ';

    // JavaScript function to filter tasks
    echo '
    <script>
    function filterTasks(type) {
        // Get all task cards
        var tasks = document.getElementsByClassName("task-card");

        // Loop through all task cards
        for (var i = 0; i < tasks.length; i++) {
            var task = tasks[i];
            var start = task.dataset.start;
            var end = task.dataset.end;

            // Show or hide task cards based on filter type
            if (type === "started" && new Date(start) > new Date()) {
                task.style.display = "none";
            } else if (type === "due" && new Date(end) > new Date()) {
                task.style.display = "none";
            } else if (type === "all") {
                task.style.display = "block";
            } else {
                task.style.display = "block";
            }
        }
    }
    </script>
    ';

    //to adjust display of tasks
    echo <<<EOD
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const taskContainer = document.querySelector('#eacr-dashboard-widget .task-container');
        const resizeObserver = new ResizeObserver(entries => {
            for (let entry of entries) {
                const {width} = entry.contentRect;
                if (width <= 400) {
                    // Apply styles for single column layout
                    entry.target.style.gridTemplateColumns = '1fr';
                    const taskCards = entry.target.querySelectorAll('.task-card');
                    taskCards.forEach(card => {
                        card.style.width = 'calc(100% - 40px)'; // Adjust task card width
                    });
                } else {
                    // Revert to original styles for two columns layout
                    entry.target.style.gridTemplateColumns = 'repeat(2, 1fr)';
                    const taskCards = entry.target.querySelectorAll('.task-card');
                    taskCards.forEach(card => {
                        card.style.width = 'auto'; // Revert task card width
                    });
                }
            }
        });
    
        resizeObserver.observe(taskContainer);
    });
    </script>
    EOD;
    

    // Fetch tasks based on filter (not implemented yet)
    $allmyopentasks = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $taskTable WHERE useridfk = %d AND completed = 0 ORDER BY created_at DESC", $myuserid
        ), ARRAY_A
    );
    
    // Add CSS for two-column layout and task card width adjustment
    echo "
    <style>
        /* CSS for two-column layout */
        #eacr-dashboard-widget .task-container {
            display: grid;
            grid-template-columns: repeat(2, 1fr); /* Initially set for two columns, but will be adjusted by JS as needed */
            gap: 10px; /* Smaller gap between tasks */
            max-width: 100%; /* Ensure the container doesn't exceed its parent width */
        }
        
        #eacr-dashboard-widget .task-card {
            box-shadow: 0 4px 8px 0 rgba(0,0,0,0.2);
            transition: 0.3s;
            border-radius: 5px;
            background-color: #e0e4cc;
            padding: 20px;
            margin-bottom: 10px;
            width: auto; /* This will be dynamically adjusted by JS, so set to auto for initial state */
        }
        
        .task-title {
            font-size: 16px;
            margin-bottom: 10px;
        }
        
        .task-buttons {
            display: flex;
            align-items: center;
        }
        
        .task-buttons a {
            margin-left: 10px;
        }
                
    </style>
    ";

    if(count($allmyopentasks) > 0){
        // Wrap tasks in a container with two columns
        echo '<div class="task-container">';
        
        // Reverse the order of tasks
        $reversedTasks = array_reverse($allmyopentasks);
        foreach($reversedTasks as $singletask){
            // Start of card container with shadow, padding, and margin for card-like appearance
            echo '<div class="task-card" data-start="' . $singletask['start_at'] . '" data-end="' . $singletask['end_at'] . '">';
    
                // Title and button container
                echo '<div class="task-title"><b> # '.esc_html($singletask['tasktitle']).'</b></div>';
                echo '<div class="task-buttons">';
                    // Buttons and client name
                    $clientid = sanitize_text_field($singletask['clientidfk']);
                    $taskid = sanitize_text_field($singletask['id']);
                    if($clientid != 0){ 
                        // Get client name
                        $clientdetails = $wpdb->get_row($wpdb->prepare("SELECT * FROM $clientTable WHERE id = %d", $clientid));
                        $clientname = $clientdetails->clientname;
                        echo '<a class="button button-secondary" href="admin.php?page=wp-easy-crm-perfil&id='.$clientid.'">'.$clientname.'</a>';

                    } else {
                        echo '<a class="button button-secondary" href="admin.php?page=wp-easy-crm-tasks">'.__('Tasks', 'wp-easy-crm').'</a>';

                    }
                    echo '<a class="" href="admin.php?page=wp-easy-crm-addtask&action=markcomplete&taskid='.$taskid.'" onclick="return confirm(\'Do you want to mark this task as complete?\');" title="Mark task as complete" style="text-decoration: none; font-size: 24px; font-weight: bold; color: green;">&#10003;</a>';

                    
                echo '</div>'; // End of button container

                // Date information included within the main card container
                $startDateTimeString = $singletask['start_at'];
                $endDateTimeString = $singletask['end_at'];
                $startDateTime = new DateTime($startDateTimeString);
                $endDateTime = new DateTime($endDateTimeString);
                $currentDateTime = new DateTime();
                echo '<div style="padding: 10px; background-color: #F2F2F2; border-radius: 5px; margin-top: 10px;">'; // Added margin-top for spacing
                if ($startDateTime <= $currentDateTime) {
                    echo '<span style="color: rgba(255, 0, 0, 0.5);font-weight: bold; font-size: 11px;">Start: ' . htmlspecialchars($startDateTime->format('Y-m-d')) . '</span>';
                } else {
                    echo '<span style="color: grey;font-size: 11px;">Start: ' . htmlspecialchars($startDateTime->format('Y-m-d')) . '</span>';
                }
    
                if ($endDateTime <= $currentDateTime) {
                    echo '<span style="color: rgba(255, 0, 0, 0.5);font-weight: bold;margin-left: 1em;font-size: 11px;">Due: ' . htmlspecialchars($endDateTime->format('Y-m-d')) . '</span>';
                } else {
                    echo '<span style="color: grey;margin-left: 1em;font-size: 11px;">Due: ' . htmlspecialchars($endDateTime->format('Y-m-d')) . '</span>';
                }
                echo '</div>'; // End of date information container

                // Task description
                echo '<div>'.wp_kses_post($singletask['taskdescription']).'</div>';
    
            echo '</div>'; // End of the main card container
            //echo '<hr/>'; // Separator (consider styling or removing for spacing)
        }
        
        echo '</div>'; // End of task container
    }
}


  
  function eacr_register_dashboard_widget() {
    add_meta_box(
      'eacr-dashboard-widget',
      __( 'My open Tasks', 'wp-easy-crm' ),
      'eacr_dashboard_widget',
      'dashboard', // Screen
      'side', // Context
      'high' // Priority
    );
  }
  
add_action( 'wp_dashboard_setup', 'eacr_register_dashboard_widget' );

//Cronjob Task reminder

function eacr_send_email_reminder() {
    global $wpdb;
    //get all open tasks
    $taskTable = $wpdb->prefix . 'task';
    $alltasks = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * from $taskTable WHERE completed = 0"
        ),ARRAY_A
    );
    $current_time = current_time( 'mysql', false );
    $currentdatetime = new DateTime( $current_time );
    $home_url = home_url();
    $parsed_url = parse_url( $home_url );
    $domain_name = $parsed_url['host'];

    //check for each task if due in the next 24 hours
    if(count($alltasks) > 0){
        foreach($alltasks as $singletask){
            $interval = $currentdatetime->diff(new DateTime($singletask['end_at']));
            if ($interval->days == 0 && $interval->h <= 24) {
                //send email reminder
                $user_data = get_userdata($singletask['useridfk']);
                $to = $user_data->user_email;
                $subject = __('Your Task is due soon', 'wp-easy-crm' );
                $message = __('The Task Title is: ', 'wp-easy-crm') .sanitize_text_field($singletask['tasktitle']).' '.
                __('The Task Description is: ', 'wp-easy-crm').sanitize_text_field($singletask['taskdescription']);
                $headers = array('From: Easy CRM '.esc_html($domain_name).' <wordpress@'.esc_html($domain_name).'>', 'Content-Type: text/html; charset=UTF-8');
                wp_mail($to, $subject, $message, $headers);
            }
        }
    }

}
add_action( 'eacr_send_email_reminder', 'eacr_send_email_reminder' );

if ( ! wp_next_scheduled( 'eacr_send_email_reminder' ) ) {
    // wp_schedule_event( time(), 'hourly', 'my_custom_cron_job' );
    wp_schedule_event( strtotime('6:00am'), 'daily', 'eacr_send_email_reminder' );
 
}



//menu
add_action("admin_menu","eacr_crm_menus_development", 25);

function eacr_crm_menus_development(){
    add_menu_page("Easy CRM",__('Easy CRM', 'wp-easy-crm'),"edit_others_posts","wp-easy-crm","eacr_contact_list_call", 'dashicons-performance', '10');
    add_submenu_page("wp-easy-crm","Contacts",__('Contacts', 'wp-easy-crm'),"edit_others_posts",'wp-easy-crm');
    add_submenu_page("wp-easy-crm","Tasks",__('Tasks', 'wp-easy-crm'),"edit_others_posts","wp-easy-crm-tasks","eacr_tasks_call");
    add_submenu_page("wp-easy-crm","Quotes / Invoices",__('Quotes / Invoices', 'wp-easy-crm'),"edit_others_posts","wp-easy-crm-quotes","eacr_quotes_call");
    add_submenu_page("wp-easy-crm","Tags",__('Tags', 'wp-easy-crm'),"edit_others_posts","wp-easy-crm-tags","eacr_tags_call");
    add_submenu_page("wp-easy-crm","Forms",__('Forms', 'wp-easy-crm'),"edit_others_posts","wp-easy-crm-forms","eacr_forms_call");
    add_submenu_page("wp-easy-crm","Settings",__('Settings', 'wp-easy-crm'),"manage_options","wp-easy-crm-settings","eacr_settings_call");
    add_submenu_page("wp-easy-crm","Accounting Regions",__('Accounting Regions', 'wp-easy-crm'),"manage_options","wp-easy-crm-accregions","eacr_accregions_call");



    add_submenu_page(NULL,"Add Client","Add Client","edit_others_posts","wp-easy-crm-addclient","eacr_addclient_call");
    add_submenu_page(NULL,"Add Task","Add Task","edit_others_posts","wp-easy-crm-addtask","eacr_addtask_call");
    add_submenu_page(NULL,"Add Task","Add Task","edit_others_posts","wp-easy-crm-addquote","eacr_addquote_call");
    add_submenu_page(NULL,"Add ClientLog","Add ClientLog","edit_others_posts","wp-easy-crm-addclientlog","eacr_addclientlog_call");
    add_submenu_page(NULL,"Client Profile","Client Profile","edit_others_posts","wp-easy-crm-perfil","eacr_client_profile_call");
    add_submenu_page(NULL,"Quote Invoice View","Quote Invoice View","edit_others_posts","wp-easy-crm-quoteview","eacr_client_quoteview_call");



}

function eacr_accregions_call(){

    include_once EASYCRM_PLUGIN_DIR_PATH.'/views/accountingregions.php';
}

function eacr_tags_call(){

    include_once EASYCRM_PLUGIN_DIR_PATH.'/views/tagsView.php';
}

function eacr_client_quoteview_call(){

    include_once EASYCRM_PLUGIN_DIR_PATH.'/views/quoteView.php';
}

function eacr_quotes_call(){

    include_once EASYCRM_PLUGIN_DIR_PATH.'/views/quotationsInvoices.php';
}

function eacr_addquote_call(){

    include_once EASYCRM_PLUGIN_DIR_PATH.'/views/addQuote.php';
}

function eacr_forms_call(){

    include_once EASYCRM_PLUGIN_DIR_PATH.'/views/forms.php';
}

function eacr_tasks_call(){

    include_once EASYCRM_PLUGIN_DIR_PATH.'/views/tasks.php';
}

function eacr_addclientlog_call(){

    include_once EASYCRM_PLUGIN_DIR_PATH.'/views/addClientLog.php';
}

function eacr_addtask_call(){

    include_once EASYCRM_PLUGIN_DIR_PATH.'/views/addTask.php';
}

function eacr_client_profile_call(){

    include_once EASYCRM_PLUGIN_DIR_PATH.'/views/clientProfile.php';
}

function eacr_addclient_call(){

    include_once EASYCRM_PLUGIN_DIR_PATH.'/views/addClient.php';
}

function eacr_settings_call(){

    include_once EASYCRM_PLUGIN_DIR_PATH.'/views/settings.php';
}

function eacr_contact_list_call(){

    include_once EASYCRM_PLUGIN_DIR_PATH.'/views/listClients.php';
}


/* datatables css and js */
function datatables_assets() {
	wp_register_style( 'datatable_style', plugin_dir_url( __FILE__ ).'scripts/datatables.min.css' );
    wp_enqueue_style( 'datatable_style' );
	wp_register_script( 'datatables', plugin_dir_url( __FILE__ ).'scripts/datatables.min.js', array( 'jquery' ) );
    wp_enqueue_script( 'datatables' );
    wp_register_script( 'datatablesresponsive', plugin_dir_url( __FILE__ ).'scripts/dataTables.responsive.min.js', array( 'jquery' ) );
    wp_enqueue_script( 'datatablesresponsive' );
    wp_register_script( 'html2pdf', plugin_dir_url( __FILE__ ).'scripts/html2pdf.bundle.min.js', array( 'jquery' ) );
    wp_enqueue_script( 'html2pdf' );

}
add_action( 'admin_enqueue_scripts', 'datatables_assets' );

// Function to add accounting regions to user profiles
function add_regions_to_user_profile($user) {
    global $wpdb;
    $accountingregionTable = $wpdb->prefix . 'eacraccountingregion'; // Replace with your table name
    $regions = $wpdb->get_results("SELECT id, regionname FROM $accountingregionTable");

    // Check if the current user is an editor, shop-manager, or admin
    if (!current_user_can('edit_users') && !current_user_can('manage_woocommerce')) {
        return;
    }

    echo '<h3>Accounting Region Access</h3>';
    echo '<table class="form-table">';
    foreach ($regions as $region) {
        $is_checked = get_user_meta($user->ID, 'region_' . $region->id, true);
        $disabled = !current_user_can('administrator') ? 'disabled' : '';
        echo '<tr>';
        echo '<th><label for="region_' . $region->id . '">' . esc_html($region->regionname) . '</label></th>';
        echo '<td><input type="checkbox" name="region_' . $region->id . '" id="region_' . $region->id . '" ' . checked($is_checked, true, false) . ' ' . $disabled . '></td>';
        echo '</tr>';
    }
    echo '</table>';
}

add_action('show_user_profile', 'add_regions_to_user_profile');
add_action('edit_user_profile', 'add_regions_to_user_profile');

// Function to save changes when admin updates user profile and the accounting regions
function save_regions_for_user_profile($user_id) {
    global $wpdb;
    $accountingregionTable = $wpdb->prefix . 'eacraccountingregion'; // Replace with your table name
    $regions = $wpdb->get_results("SELECT id FROM $accountingregionTable");

    if (!current_user_can('administrator')) {
        return false;
    }

    foreach ($regions as $region) {
        update_user_meta($user_id, 'region_' . $region->id, isset($_POST['region_' . $region->id]));
    }
}

add_action('personal_options_update', 'save_regions_for_user_profile');
add_action('edit_user_profile_update', 'save_regions_for_user_profile');


?>