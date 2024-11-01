<?php 

global $wpdb;
$registration_date = date("Y-m-d H:i:s", time());
$clientLogTable = $wpdb->prefix . 'logclient';
$clientTable = $wpdb->prefix . 'clients';
$msg = '';

$action = sanitize_text_field(isset($_GET['action']) ? trim($_GET['action']) : "");
$id = sanitize_text_field(isset($_GET['id']) ? intval($_GET['id']) : "");
$logid = sanitize_text_field(isset($_GET['logid']) ? intval($_GET['logid']) : "");

$current_user = wp_get_current_user();

$row_details_client = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * from $clientTable WHERE id = %d",$id
    ),ARRAY_A
);

if(!empty($action)){
    $row_details_log = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * from $clientLogTable WHERE id = %d",$logid
        ),ARRAY_A
    );
    $row_details_client = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * from $clientTable WHERE id = %d",$row_details_log['clientidfk']
        ),ARRAY_A
    );

    $id = $row_details_log['clientidfk'];

    $hourdiff = round((strtotime($registration_date) - strtotime($row_details_log['created_at']))/3600, 1);
        // echo($hourdiff);
    
        if($hourdiff >= 1){
            $msg = "<div style='color:red'>".esc_html(__( 'This log can not be edited or deleted anymore. More than an hour has passed. Please add a new log and/or return to the client profile', 'wp-easy-crm' ))."</div>";
        }
        else{
            if($action == "delete"){

                //delete log
                $count = $wpdb->delete( $clientLogTable, array( 'id' => $logid ) );
                if($count == 1){
                    $msg = "<div class='updated update'>".esc_html(__( 'log successfully deleted. You can return to the client profile.', 'wp-easy-crm' ))."</div>";

                }
                else{
                    $msg = "<div style='color:red'>".esc_html(__( 'error - log couldn\'t be deleted', 'wp-easy-crm' ))."</div>";

                }

            }
        }
}



if (isset($_POST['submit'])) {
 
    if(!empty($action)){
        //save edited log and 
        //check if time already has passed, if log older than 1 hour don't allow editing
        $row_details_log = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * from $clientLogTable WHERE id = %d",$logid
            ),ARRAY_A
        );
        $hourdiff = round((strtotime($registration_date) - strtotime($row_details_log['created_at']))/3600, 1);
        // echo($hourdiff);
    
        if($hourdiff >= 1){
            $msg = "<div style='color:red'>".esc_html(__( 'This log can not be edited anymore. More than an hour has passed. Please add a new log and/or return to the client profile', 'wp-easy-crm' ))."</div>";
        }
        else{
            //load data
            $row_details_log = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * from $clientLogTable WHERE id = %d",$logid
                ),ARRAY_A
            );
            $row_details_client = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * from $clientTable WHERE id = %d",$row_details_log['clientidfk']
                ),ARRAY_A
            );

            
         
            
                //update log
                $wpdb->update($clientLogTable,array(
                    "clientidfk"=>sanitize_text_field($_POST['clientidfk']),
                    "logtitle"=>sanitize_text_field($_POST['logtitle']),
                    "logdescription"=>sanitize_text_field($_POST['logdescription']),
                    "createdbyperson"=>sanitize_text_field($_POST['createdbyperson']),
                    "lastupdate_at"=>sanitize_text_field($registration_date)
                ), array(
                    "id" => $logid
                ));

                $row_details_log = $wpdb->get_row(
                    $wpdb->prepare(
                        "SELECT * from $clientLogTable WHERE id = %d",$logid
                    ),ARRAY_A
                );

                if($wpdb->update($clientLogTable,array(
                    "clientidfk" => sanitize_text_field(wp_unslash($_POST['clientidfk'])),
                    "logtitle" => sanitize_text_field(wp_unslash($_POST['logtitle'])),
                    "logdescription" => sanitize_text_field(wp_unslash($_POST['logdescription'])),
                    "createdbyperson" => sanitize_text_field(wp_unslash($_POST['createdbyperson'])),
                    "created_at" => sanitize_text_field(wp_unslash($registration_date))
                ), array(
                    "id" => $logid
                )) === FALSE){
                    $msg = "<div style='color:red'>".esc_html(__( 'error - log information couldn\'t be stored', 'wp-easy-crm' ))."</div>";

            
                }
                else{
                    $msg = "<div class='updated update'>".esc_html(__( 'log successfully saved. You can return to the client profile.', 'wp-easy-crm' ))."</div>";

                }

            
        }
    
    }
    else{
         //add new log
         $wpdb->insert($clientLogTable,array(
            "clientidfk" => sanitize_text_field(wp_unslash($_POST['clientidfk'])),
            "logtitle" => sanitize_text_field(wp_unslash($_POST['logtitle'])),
            "logdescription" => sanitize_text_field(wp_unslash($_POST['logdescription'])),
            "createdbyperson" => sanitize_text_field(wp_unslash($_POST['createdbyperson'])),
            "created_at" => sanitize_text_field(wp_unslash($registration_date))
        ));
        if($wpdb->insert_id > 0){
            $msg = "<div class='updated update'>".esc_html(__( 'log successfully saved. You can return to the client profile. Redirecting back to client profile... please wait', 'wp-easy-crm' ))."</div>";
            $id = sanitize_text_field($_POST['clientidfk']);
            // Construct the redirect URL dynamically
            $redirect_url = 'admin.php?page=wp-easy-crm-perfil&id=' . $id;

            // JavaScript for delay and redirection
            echo "<script>
                setTimeout(function() {
                    window.location.href = '" . $redirect_url . "';
                }, 1500); // Redirect after 2 seconds
            </script>";
    
        }else{
    
            $msg = "<div style='color:red'>".esc_html(__( 'error - log information couldn\'t be stored', 'wp-easy-crm' ))."</div>";
        }
    }
  
    

}


?>

<p><?php echo wp_kses_post($msg); ?></p>
<a class="button button-primary" href="admin.php?page=wp-easy-crm"><?php echo __('List of all Clients', 'wp-easy-crm')?></a><span> </span>
<a class="button button-primary" href="admin.php?page=wp-easy-crm-perfil&id=<?php echo esc_html($id);?>"><?php echo __('Client Profile', 'wp-easy-crm')?></a>
<h1><?php _e( 'Add / Edit Client Log for ', 'wp-easy-crm' );?><?php echo esc_html(@$row_details_client['clientname']) ?></h1>
<div class="wrap">
        <form action="<?php echo esc_html($_SERVER['PHP_SELF']) ?>?page=wp-easy-crm-addclientlog<?php 
if(!empty($action)){
     echo '&action=edit&logid='.$logid. '&clientid='.$id ; 
     } ?>" method="post">
            <input type="text" name="clientidfk" hidden id="clientidfk" required value="<?php echo $id?>"><br><br>
            
            <label for="logtitle"><?php echo __('Log Title*', 'wp-easy-crm')?></label></br>
            <input type="text" name="logtitle" id="logtitle" maxlength="198" style="width:70%" required value="<?php echo esc_html(@$row_details_log['logtitle']) ?>"><br><br>

            <label for="logdescription"><?php echo __('Log Description', 'wp-easy-crm')?></label></br>
            <textarea name="logdescription" id="logdescription" maxlength="1980" style="width:70%" rows="6"><?php echo esc_html(@$row_details_log['logdescription']) ?></textarea><br><br>

            <label for="createdbyperson"><?php echo __('Created By', 'wp-easy-crm')?></label></br>
            <input type="text" readonly="readonly" name="createdbyperson" id="createdbyperson" required value="<?php echo esc_html( $current_user->display_name )?>"><br><br>

            <input type="submit" name="submit" onclick="var btn = this; setTimeout(function() { btn.disabled = true; setTimeout(function() { btn.disabled = false; }, 4000); }, 50);" value="<?php echo __('Submit', 'wp-easy-crm')?>">
        </form>
    </div>