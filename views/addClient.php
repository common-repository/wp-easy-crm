<?php 

global $wpdb;
$registration_date = date("Y-m-d H:i:s", time());
$clientTable = $wpdb->prefix . 'clients';
$accregionTable = $wpdb->prefix . 'eacraccountingregion';
$msg = '';

$action = sanitize_text_field(isset($_GET['action']) ? trim($_GET['action']) : "");
$id = sanitize_text_field(isset($_GET['id']) ? intval($_GET['id']) : "");

if(isset($_POST['btnsubmit'])){
    
    $action = sanitize_text_field(isset($_GET['action']) ? trim($_GET['action']) : "");
    $id = sanitize_text_field(isset($_GET['id']) ? intval($_GET['id']) : "");

    if(!empty($action)){
        // Update client

        // Fetch existing emails associated with the client being updated
        $existing_emails_for_client = $wpdb->get_var(
            $wpdb->prepare(
                "SELECT email FROM $clientTable WHERE id = %d", $id
            )
        );

        // Split existing emails into an array
        $existing_emails_for_client = explode(',', $existing_emails_for_client);

        // Initialize an array to store new emails for the client
        $new_emails = array();

        // Retrieve entered emails and split them by comma
        $entered_emails = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
        $emails_array = explode(',', $entered_emails);
        $existing_emails = array();

        // Check each email against the database, excluding the current client being updated
        foreach ($emails_array as $email) {
            // Trim the email
            $trimmed_email = trim($email);
            
            // Check if the trimmed email is not empty
            if (!empty($trimmed_email)) {
                // If the email is not associated with the client being updated
                if (!in_array($trimmed_email, $existing_emails_for_client)) {
                    // Add it to the new emails array for further processing
                    $new_emails[] = $trimmed_email;
                }
            }
        }

        // Initialize an array to store existing emails for other clients
        $existing_emails = array();

        // Check each new email against the database for duplication
        foreach ($new_emails as $new_email) {
            // Split the new email into an array in case it contains multiple emails
            $new_email_array = explode(',', $new_email);

            // Check each individual email
            foreach ($new_email_array as $individual_email) {
                // If the email already exists for another client
                $existing_client = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * FROM $clientTable WHERE email LIKE %s AND id != %d", '%' . trim($individual_email) . '%', $id
                    ), ARRAY_A
                );
                
                // If an existing client is found, add the email to the existing emails array
                if ($existing_client) {
                    $existing_emails[] = trim($individual_email);
                }
            }
        }
        
        // If there are existing emails, display an alert
        if (!empty($existing_emails)) {
            $msg = "<div style='color:red'>".esc_html(__( 'Error - the following email(s) already exist in the database for other clients: ', 'wp-easy-crm' ));
            foreach ($existing_emails as $existing_email) {
                $msg .= $existing_email . ', ';
            }
            $msg = rtrim($msg, ', '); // Remove trailing comma
            $msg .= ". Please search for those emails in the client-list and add your data to the existing client files.</div>";
        } else {
            // Continue updating the client
            $wpdb->update($clientTable,array(
                "clientname" => sanitize_text_field(wp_unslash($_POST['clientname'])),
                "clientstatus" => sanitize_text_field(wp_unslash($_POST['clientstatus'])),
                "email" => sanitize_text_field(wp_unslash($_POST['email'])),
                "nombrecontacto" => sanitize_text_field(wp_unslash($_POST['nombrecontacto'])),
                "nota" => sanitize_text_field(wp_unslash($_POST['nota'])),
                "telefono" => sanitize_text_field(wp_unslash($_POST['telefono'])),
                "clientsource" => sanitize_text_field(wp_unslash($_POST['clientsource'])),
                "provincia" => sanitize_text_field(wp_unslash($_POST['provincia'])),
                "direccion" => sanitize_text_field(wp_unslash($_POST['direccion'])),
                "lastupdate_at" => sanitize_text_field(wp_unslash($_POST['lastupdate_at'])),
                "idaccountingregionfk" => sanitize_text_field(wp_unslash($_POST['idaccountingregionfk'])),
                "created_at" => sanitize_text_field(wp_unslash($_POST['created_at']))
            ), array(
               "id" => $id
            ));
            $msg = "<div class='updated update'>" . __( 'Client successfully updated. You will be redirected to the client profile in 2 seconds automatically.', 'wp-easy-crm' ) . "</div>";
    
            // Construct the redirect URL dynamically to client profile
            $redirect_url = admin_url('admin.php?page=wp-easy-crm-perfil&id=' . $id);
    
            // JavaScript for delay and redirection
            echo "<script>
                setTimeout(function() {
                    window.location.href = '" . $redirect_url . "';
                }, 2000);
            </script>";
        }

    } else {
        // Add new client

        // Retrieve entered emails and split them by comma
        $entered_emails = isset($_POST['email']) ? sanitize_text_field($_POST['email']) : '';
        $emails_array = explode(',', $entered_emails);
        $existing_emails = array();

        // Check each email against the database
        foreach ($emails_array as $email) {
            $existing_client = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * FROM $clientTable WHERE email = %s", trim($email)
                ), ARRAY_A
            );
            if ($existing_client) {
                $existing_emails[] = $email;
            }
        }

        // If there are existing emails, display an alert
        if (!empty($existing_emails)) {
            $msg = "<div style='color:red'>".esc_html(__( 'Error - the following email(s) already exist in the database: ', 'wp-easy-crm' ));
            foreach ($existing_emails as $existing_email) {
                $msg .= $existing_email . ', ';
            }
            $msg = rtrim($msg, ', '); // Remove trailing comma
            $msg .= ". Please search for that email in the client-list and add your data to the existing client file.</div>";
        } else {
            // Continue adding the new client
            $wpdb->insert($clientTable,array(
                "clientname"=>sanitize_text_field($_POST['clientname']),
                "clientstatus"=>sanitize_text_field($_POST['clientstatus']),
                "email"=>sanitize_text_field($_POST['email']),
                "nombrecontacto"=>sanitize_text_field($_POST['nombrecontacto']),
                "nota"=>sanitize_text_field($_POST['nota']),
                "telefono"=>sanitize_text_field($_POST['telefono']),
                "clientsource"=>sanitize_text_field($_POST['clientsource']),
                "provincia"=>sanitize_text_field($_POST['provincia']),
                "direccion"=>sanitize_text_field($_POST['direccion']),
                "lastupdate_at"=>sanitize_text_field($_POST['lastupdate_at']),
                "idaccountingregionfk" => sanitize_text_field(wp_unslash($_POST['idaccountingregionfk'])),
                "created_at"=>sanitize_text_field($_POST['created_at'])
            ));
            if($wpdb->insert_id > 0){
                $msg = "<div class='updated update'>".esc_html(__( 'client successfully saved. You will be redirected to the client profile in 2 seconds automatically.', 'wp-easy-crm' ))."</div>";
        
                // Construct the redirect URL dynamically to client profile
                $redirect_url = admin_url('admin.php?page=wp-easy-crm-perfil&id=' . $wpdb->insert_id);
        
                // JavaScript for delay and redirection
                echo "<script>
                    setTimeout(function() {
                        window.location.href = '" . $redirect_url . "';
                    }, 2000);
                </script>";
        
            }else{
                $msg = "<div style='color:red'>".esc_html(__( 'error - client information couldn\'t be stored', 'wp-easy-crm' ))."</div>";
            }
        }
    }
}

$row_details = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * from $clientTable WHERE id = %d",$id
    ),ARRAY_A
);

?>

<p><?php echo wp_kses_post($msg); ?></p>
<a class="button button-primary" href="admin.php?page=wp-easy-crm"><?php echo __('List of all Clients', 'wp-easy-crm')?></a><span> </span>
<h1><?php _e( 'Add Client', 'wp-easy-crm' );?></h1>
<form action="<?php echo esc_html($_SERVER['PHP_SELF']) ?>?page=wp-easy-crm-addclient<?php 
if(!empty($action)){
     echo '&action=edit&id='.$id ; 
     } ?>" method="post">
<input type="datetime-local" name="created_at" value="<?php echo esc_html(isset($row_details['created_at']) ? $row_details['created_at'] : date('Y-m-d\TH:i:s')); ?>" hidden/>
<input type="datetime-local" name="lastupdate_at" value="<?php echo esc_html(date('Y-m-d\TH:i:s')); ?>" hidden/>


<div class="form-group">

    <label><?php _e( 'Client name / Company name*', 'wp-easy-crm' );?></label><br>
    <input type="text" name="clientname" value="<?php echo esc_html(isset($row_details['clientname']) ? $row_details['clientname'] : ""); ?>" placeholder="<?php _e( 'Enter client or company name', 'wp-easy-crm' );?>" class="form-control" style="width:50%" required/><br><br>
</div>

<div class="form-group">

    <label><?php _e( 'Client status*', 'wp-easy-crm' );?></label><br>
    <!-- <input type="text" name="clientstatus" value="<?php echo esc_html(isset($row_details['clientstatus']) ? $row_details['clientstatus'] : ""); ?>" placeholder="<?php _e( 'Choose client status', 'wp-easy-crm' );?>" class="form-control" style="width:50%" required/><br><br> -->
    <select name="clientstatus" required>
        <option value="<?php echo esc_html(isset($row_details['clientstatus']) ? $row_details['clientstatus'] : ""); ?>"><?php echo esc_html(isset($row_details['clientstatus']) ? $row_details['clientstatus'] : _e( 'Choose client status', 'wp-easy-crm' ))?></option>
        <option value="lead"><?php _e( "Lead", 'wp-easy-crm' );?></option>
        <option value="customer"><?php _e( 'Customer', 'wp-easy-crm' );?></option>
        <option value="refused"><?php _e( 'Refused', 'wp-easy-crm' );?></option>
        <option value="blacklisted"><?php _e( 'Blacklisted', 'wp-easy-crm' );?></option>

    </select>

    
</div>
</br>
<div class="form-group">

    <label><?php _e( 'Client source*', 'wp-easy-crm' );?></label><br>
    <!-- <input type="text" name="clientstatus" value="<?php echo esc_html(isset($row_details['clientsource']) ? $row_details['clientsource'] : ""); ?>" placeholder="<?php _e( 'Choose client source', 'wp-easy-crm' );?>" class="form-control" style="width:50%" required/><br><br> -->
    <select name="clientsource" required>
        <option value="<?php echo esc_html(isset($row_details['clientsource']) ? $row_details['clientsource'] : ""); ?>"><?php echo esc_html(isset($row_details['clientsource']) ? $row_details['clientsource'] : _e( 'Choose client source', 'wp-easy-crm' ))?></option>
        <option value="website"><?php _e( "Website", 'wp-easy-crm' );?></option>
        <option value="email"><?php _e( 'Email', 'wp-easy-crm' );?></option>
        <option value="call"><?php _e( 'Call', 'wp-easy-crm' );?></option>
        <option value="social media"><?php _e( 'Social Media', 'wp-easy-crm' );?></option>
        <option value="recommendation"><?php _e( 'Recommendation', 'wp-easy-crm' );?></option>
        <option value="other"><?php _e( 'Other', 'wp-easy-crm' );?></option>
    </select>

    
</div></br>
<div class="form-group">
    <label><?php _e( 'Accounting Region*', 'wp-easy-crm' );?></label><br>
    <select name="idaccountingregionfk" required>
        <option value=""><?php _e( 'Choose accounting region', 'wp-easy-crm' );?></option>
        <?php
            global $wpdb;
            $selected_region_id = isset($row_details['idaccountingregionfk']) ? $row_details['idaccountingregionfk'] : '';
            $results = $wpdb->get_results("SELECT id, regionname FROM $accregionTable", ARRAY_A);
            if (!empty($results)) {
                foreach ($results as $row) {
                    // Check if the current row ID matches the selected region ID
                    $selected = ($row['id'] == $selected_region_id) ? 'selected' : '';
                    echo '<option value="'. esc_attr($row['id']) .'" '. $selected .'>'. esc_html($row['regionname']) .'</option>';
                }
            }
        ?>
    </select>
</div>
</br>

<div class="form-group">

    <label><?php _e( 'Email(s)*', 'wp-easy-crm' );?></label><span>  <?php _e( 'several emails divided by a comma are allowed', 'wp-easy-crm' );?></span><br>
    <input type="text" name="email" value="<?php echo esc_html(isset($row_details['email']) ? $row_details['email'] : ""); ?>" placeholder="<?php _e( 'Enter email(s)', 'wp-easy-crm' );?>" class="form-control" style="width:50%" required/><br><br>
</div>

<div class="form-group">

    <label><?php _e( 'Name(s) of contact*', 'wp-easy-crm' );?></label><br>
    <input type="text" name="nombrecontacto" value="<?php echo esc_html(isset($row_details['nombrecontacto']) ? $row_details['nombrecontacto'] : ""); ?>" placeholder="<?php _e( 'Enter contact name(s)', 'wp-easy-crm' );?>" class="form-control" style="width:50%" required/><br><br>
</div>

<div class="form-group">

    <label><?php _e( 'Telephone number(s)', 'wp-easy-crm' );?>*</label><br>
    <input type="text" name="telefono" value="<?php echo esc_html(isset($row_details['telefono']) ? $row_details['telefono'] : ""); ?>" placeholder="<?php _e( 'Enter telephone number(s)', 'wp-easy-crm' );?>" class="form-control" style="width:50%" required/><br><br>
</div>

<div class="form-group">

    <label><?php _e( 'Province / State', 'wp-easy-crm' );?></label><br>
    <input type="text" name="provincia" value="<?php echo esc_html(isset($row_details['provincia']) ? $row_details['provincia'] : ""); ?>" placeholder="<?php _e( 'Enter province / state', 'wp-easy-crm' );?>" class="form-control" style="width:50%"/><br><br>

</div>

<div class="form-group">

    <label><?php _e( 'Address', 'wp-easy-crm' );?></label><br>
    <input type="text" name="direccion" placeholder="<?php esc_html(_e( 'Enter address', 'wp-easy-crm' ));?>" class="form-control" style="width:50%" maxlength="500" value="<?php echo esc_html(isset($row_details['direccion']) ? $row_details['direccion'] : ""); ?>"/><br><br>
</div>

<div class="form-group">

    <label><?php _e( 'Note', 'wp-easy-crm' );?></label><br>
    <textarea type="text" name="nota" maxlength="1980" placeholder="<?php esc_html(_e( 'Enter note', 'wp-easy-crm' ));?>" class="form-control" rows="3" maxlength="2000" style="width:50%"><?php echo esc_html(isset($row_details['nota']) ? $row_details['nota'] : ""); ?></textarea><br><br>
</div>

<div class="form-group">

<button type="submit" name="btnsubmit" class="btn btn-primary button-primary" onclick="var btn = this; setTimeout(function() { btn.disabled = true; setTimeout(function() { btn.disabled = false; }, 4000); }, 50);">
 <?php esc_html(_e( 'Submit', 'wp-easy-crm' )); ?> </button>
</div>


</form>
