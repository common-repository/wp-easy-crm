<?php 

global $wpdb;
$registration_date = date("Y-m-d H:i:s", time());
$taskTable = $wpdb->prefix . 'task';
$clientTable = $wpdb->prefix . 'clients';
$msg = '';

$action = sanitize_text_field(isset($_GET['action']) ? trim($_GET['action']) : "");
$id = sanitize_text_field(isset($_GET['id']) ? intval($_GET['id']) : "");
$taskid = sanitize_text_field(isset($_GET['taskid']) ? intval($_GET['taskid']) : "");

$current_user = wp_get_current_user();

$row_details_client = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * from $clientTable WHERE id = %d",$id
    ),ARRAY_A
);

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
    'em' => array(),
    'h1' => array(),
    'h2' => array(),
    'h3' => array(),
    'h4' => array(),
    'h5' => array(),
    'h6' => array(),
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


if(!empty($action)){

    //load data
$row_details_task = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * from $taskTable WHERE id = %d",$taskid
    ),ARRAY_A
);
$row_details_client = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * from $clientTable WHERE id = %d",$row_details_task['clientidfk']
    ),ARRAY_A
);

$id = $row_details_task['clientidfk'];


        if($action == "delete"){

            //delete log
            $count = $wpdb->delete( $taskTable, array( 'id' => $taskid ) );
            if($count == 1){
                $msg = "<div class='updated update'>".esc_html(__( 'task successfully deleted. You can return to the client profile or wait until we redirect you automatically.', 'wp-easy-crm' ))."</div>";
                if (isset($row_details_task['clientidfk']) && $row_details_task['clientidfk'] == 0) {
                    // Construct the redirect URL dynamically
                    $redirect_url = 'admin.php?page=wp-easy-crm-tasks';
                } else {
                    // Construct the redirect URL dynamically
                    $redirect_url = 'admin.php?page=wp-easy-crm-perfil&id=' . $id;
                }

                // JavaScript for delay and redirection
                echo "<script>
                    setTimeout(function() {
                        window.location.href = '" . $redirect_url . "';
                    }, 1500); // Redirect after 2 seconds
                </script>";

            }
            else{
                $msg = "<div style='color:red'>".esc_html(__( 'error - task couldn\'t be deleted', 'wp-easy-crm' ))."</div>";

            }

        }
        else if($action == "markcomplete"){
            if($wpdb->update($taskTable,array(
                "completed"=>sanitize_text_field(1),
            ), array(
                "id" => $taskid
            )) === FALSE){
                $msg = "<div style='color:red'>".esc_html(__( 'error - task information couldn\'t be stored', 'wp-easy-crm' ))."</div>";
            }
            else{
                $msg = "<div class='updated update'>".esc_html(__( 'task successfully saved. You can return to the client profile or wait until we redirect you automatically.', 'wp-easy-crm' ))."</div>";

                if (isset($row_details_task['clientidfk']) && $row_details_task['clientidfk'] == 0) {
                    // Construct the redirect URL dynamically
                    $redirect_url = 'admin.php?page=wp-easy-crm-tasks';
                } else {
                    // Construct the redirect URL dynamically
                    $redirect_url = 'admin.php?page=wp-easy-crm-perfil&id=' . $id;
                }

                // JavaScript for delay and redirection
                echo "<script>
                    setTimeout(function() {
                        window.location.href = '" . $redirect_url . "';
                    }, 1500); // Redirect after 2 seconds
                </script>";
        
                }
        }
        else if($action == "markopen"){
            if($wpdb->update($taskTable,array(
                "completed"=>sanitize_text_field(0),
            ), array(
                "id" => $taskid
            )) === FALSE){
                $msg = "<div style='color:red'>".esc_html(__( 'error - task information couldn\'t be stored', 'wp-easy-crm' ))."</div>";
            }
            else{
                $msg = "<div class='updated update'>".esc_html(__( 'task successfully saved. You can return to the client profile or wait until we redirect you automatically.', 'wp-easy-crm' ))."</div>";

                if (isset($row_details_task['clientidfk']) && $row_details_task['clientidfk'] == 0) {
                    // Construct the redirect URL dynamically
                    $redirect_url = 'admin.php?page=wp-easy-crm-tasks';
                } else {
                    // Construct the redirect URL dynamically
                    $redirect_url = 'admin.php?page=wp-easy-crm-perfil&id=' . $id;
                }

                // JavaScript for delay and redirection
                echo "<script>
                    setTimeout(function() {
                        window.location.href = '" . $redirect_url . "';
                    }, 1500); // Redirect after 2 seconds
                </script>";
                
    
            }
        }

            //load data
            $row_details_task = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * from $taskTable WHERE id = %d",$taskid
                ),ARRAY_A
            );
        
    }




if (isset($_POST['submit'])) {

    $id = sanitize_text_field($_POST['clientidfk']);
    $taskdescription = wpautop($_POST['taskdescription']);

    $row_details_client = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * from $clientTable WHERE id = %d",$id
        ),ARRAY_A
    );
    
 
    if(!empty($action)){
        //save edited task and           
        //update log
        $wpdb->update($taskTable,array(
            "clientidfk" => sanitize_text_field(wp_unslash($_POST['clientidfk'])),
            "useridfk" => sanitize_text_field(wp_unslash($_POST['useridfk'])),
            "completed" => sanitize_text_field(wp_unslash($_POST['completed'])),
            "tasktitle" => sanitize_text_field(wp_unslash($_POST['tasktitle'])),
            "taskdescription" => wp_kses(wp_unslash($taskdescription), $allowed_tags),
            "start_at" => sanitize_text_field(wp_unslash($_POST['start_at'])),
            "end_at" => sanitize_text_field(wp_unslash($_POST['end_at'])),
            "created_at" => sanitize_text_field(wp_unslash($registration_date))
        ), array(
            "id" => $taskid
        ));

        $row_details_task = $wpdb->get_row(
            $wpdb->prepare(
                "SELECT * from $taskTable WHERE id = %d",$taskid
            ),ARRAY_A
        );

        if($wpdb->update($taskTable,array(
            "clientidfk" => sanitize_text_field(wp_unslash($_POST['clientidfk'])),
            "useridfk" => sanitize_text_field(wp_unslash($_POST['useridfk'])),
            "completed" => sanitize_text_field(wp_unslash($_POST['completed'])),
            "tasktitle" => sanitize_text_field(wp_unslash($_POST['tasktitle'])),
            "taskdescription" => wp_kses(wp_unslash($taskdescription), $allowed_tags),
            "start_at" => sanitize_text_field(wp_unslash($_POST['start_at'])),
            "end_at" => sanitize_text_field(wp_unslash($_POST['end_at'])),
            "created_at" => sanitize_text_field(wp_unslash($registration_date))
        ), array(
            "id" => $taskid
        )) === FALSE){
            $msg = "<div style='color:red'>".esc_html(__( 'error - task information couldn\'t be stored', 'wp-easy-crm' ))."</div>";

    
        }
        else{
            $home_url = home_url();
            $parsed_url = parse_url( $home_url );
            $domain_name = $parsed_url['host'];
            $msg = "<div class='updated update'>".esc_html(__( 'task successfully saved. You can return to the client profile or wait until we redirect you automatically.', 'wp-easy-crm' ))."</div>";
            //send email
            $user_data = get_userdata(sanitize_text_field($_POST['useridfk']));
            $to = $user_data->user_email;
            if (isset($row_details_client['clientname']) && is_string($row_details_client['clientname'])) {
                // $row_details_client is not empty, process the data
                $subject = __('A task was edited for you for client: ', 'wp-easy-crm' ).$row_details_client['clientname'];
                // Construct the redirect URL dynamically
                $redirect_url = 'admin.php?page=wp-easy-crm-perfil&id=' . $id;
            } else {
                // $row_details_client is empty, handle the case where no data was found
                $subject = __('A task was edited for you', 'wp-easy-crm' );
                // Construct the redirect URL dynamically
                $redirect_url = 'admin.php?page=wp-easy-crm-tasks';
            }
            $message = __('The Task Title is: ', 'wp-easy-crm') .sanitize_text_field($_POST['tasktitle']);
            $headers = array('From: Easy CRM '.esc_html($domain_name).' <wordpress@'.esc_html($domain_name).'>', 'Content-Type: text/html; charset=UTF-8');

            wp_mail($to, $subject, $message, $headers);

            
            // JavaScript for delay and redirection
            echo "<script>
                setTimeout(function() {
                    window.location.href = '" . $redirect_url . "';
                }, 1500); // Redirect after 2 seconds
            </script>";

        }

            
        
    
    }
    else{
        // Allowed HTML tags and attributes
     

        $taskdescription = wpautop($_POST['taskdescription']);

        // Get the current user's information
        $current_user = wp_get_current_user();

        // Check if the user exists and is not a guest (user ID is not 0)
        if ($current_user->ID != 0) {
            // Append the current user's display name to the task description
            $taskDescriptionWithUserName = $taskdescription . " (Task created by: " . $current_user->display_name . ")";
        } else {
            // If no user is logged in, just use the original task description
            $taskDescriptionWithUserName = $taskdescription;
        }

         //add new task
         $wpdb->insert($taskTable,array(
            "clientidfk" => sanitize_text_field(wp_unslash($_POST['clientidfk'])),
            "useridfk" => sanitize_text_field(wp_unslash($_POST['useridfk'])),
            "completed" => sanitize_text_field(wp_unslash($_POST['completed'])),
            "tasktitle" => sanitize_text_field(wp_unslash($_POST['tasktitle'])),
            "taskdescription" => wp_kses(wp_unslash($taskDescriptionWithUserName), $allowed_tags),
            "start_at" => sanitize_text_field(wp_unslash($_POST['start_at'])),
            "end_at" => sanitize_text_field(wp_unslash($_POST['end_at'])),
            "created_at" => sanitize_text_field(wp_unslash($registration_date))
        ));
        if($wpdb->insert_id > 0){
            $msg = "<div class='updated update'>".esc_html(__( 'task successfully saved. You can return to the client profile or wait until we redirect you automatically.', 'wp-easy-crm' ))."</div>";
            $id = sanitize_text_field($_POST['clientidfk']);
            $home_url = home_url();
            $parsed_url = parse_url( $home_url );
            $domain_name = $parsed_url['host'];
            //send email
            $user_data = get_userdata($_POST['useridfk']);
            $to = $user_data->user_email;
            if (isset($row_details_client['clientname']) && is_string($row_details_client['clientname'])) {
                // $row_details_client is not empty, process the data
                $subject = __('A new task was added for you for client: ', 'wp-easy-crm' ).$row_details_client['clientname'];
                // Construct the redirect URL dynamically
                $redirect_url = 'admin.php?page=wp-easy-crm-perfil&id=' . $id;
            } else {
                // $row_details_client is empty, handle the case where no data was found
                $subject = __('A new task was added for you', 'wp-easy-crm' );
                // Construct the redirect URL dynamically
                $redirect_url = 'admin.php?page=wp-easy-crm-tasks';
            }
            
            $message = __('The Task Title is: ', 'wp-easy-crm') .sanitize_text_field($_POST['tasktitle']);
            $headers = array('From: Easy CRM '.esc_html($domain_name).' <wordpress@'.esc_html($domain_name).'>', 'Content-Type: text/html; charset=UTF-8');
            wp_mail($to, $subject, $message, $headers);

            // JavaScript for delay and redirection
            echo "<script>
                setTimeout(function() {
                    window.location.href = '" . $redirect_url . "';
                }, 1500); // Redirect after 2 seconds
            </script>";

    
        }else{
             
            $msg = "<div style='color:red'>".esc_html(__( 'error - task information couldn\'t be stored', 'wp-easy-crm' ))."</div>";
            // echo($wpdb->print_error());
            //echo $_POST['clientidfk'];
            echo $wpdb->last_error;

        }
    }
      

}


?>

<p><?php echo wp_kses_post($msg); ?></p>
<a class="button button-primary" href="admin.php?page=wp-easy-crm"><?php echo __('List of all Clients', 'wp-easy-crm')?></a><span> </span>
<?php if($id != 0){ 
echo '<a class="button button-primary" href="admin.php?page=wp-easy-crm-perfil&id='.$id.'">'.__('Client Profile', 'wp-easy-crm').'</a><span> </span>';
}else{
    echo '<a class="button button-primary" href="admin.php?page=wp-easy-crm-tasks">'.__('Tasks', 'wp-easy-crm').'</a><span> </span>';
} ?>
<?php if(@$row_details_task['completed'] == 0 && $taskid != ""){ 
echo '<a class="button btn-secondary" href="admin.php?page=wp-easy-crm-addtask&action=markcomplete&taskid='.esc_html($taskid).'">'.__('mark task as complete', 'wp-easy-crm').'</a><span> </span>';
}else if(@$row_details_task['completed'] == 1 && $taskid != ""){
    echo '<a class="button btn-secondary" href="admin.php?page=wp-easy-crm-addtask&action=markopen&taskid='.esc_html($taskid).'">'.__('mark task as open', 'wp-easy-crm').'</a><span> </span>';
} ?>
<h1><?php _e( 'Add / Edit Task || ', 'wp-easy-crm' );?><?php echo esc_html(@$row_details_client['clientname']) ?></h1>
<div class="wrap">
        <form action="<?php echo esc_html($_SERVER['PHP_SELF']) ?>?page=wp-easy-crm-addtask<?php 
if(!empty($action)){
     echo '&action=edit&taskid='.$taskid. '&clientid='.$id ; 
     } ?>" method="post">
            <input type="text" name="clientidfk" hidden id="clientidfk" required value="<?php echo $id?>">
            <input type="text" name="completed" hidden id="completed" required value="0">

            <label for="useridfk"><?php echo __('Task Owner*', 'wp-easy-crm')?></label></br>
            <?php  
            
            $roles = array('administrator', 'editor', 'author', 'shop_manager'); 
            $args = array(
                'role__in' => $roles,
                'id' => 'useridfk',
                'name' => 'useridfk',
                'show' => 'display_name_with_login'
            );
            $argsselect = array(
                'role__in' => $roles,
                'id' => 'useridfk',
                'name' => 'useridfk',
                'show' => 'display_name_with_login',
                'selected' => @$row_details_task['useridfk']
            );

            if(@$row_details_task['useridfk'] != 0 && $row_details_task['useridfk'] != null){
                wp_dropdown_users($argsselect);
            }
            else{
                wp_dropdown_users($args);

            }
            
            ?><br><br>
            
            <div>
                <div style="float: left; margin-right: 1em;">
                <label for="start_at"><?php echo __('Start time', 'wp-easy-crm')?></label></br>
                <input type="datetime-local" id="start_at" name="start_at" required value="<?php echo esc_html(@$row_details_task['start_at']) ?>"></div>
                <div>
                <label for="end_at"><?php echo __('End time', 'wp-easy-crm')?></label></br>
                <input type="datetime-local" id="end_at" name="end_at" required value="<?php echo esc_html(@$row_details_task['end_at']) ?>"></div><br>   
            </div>

            <script>
            // Listen for changes on the start_at input
            document.getElementById('start_at').addEventListener('change', function() {
                // When start_at changes, set end_at to have the same value
                document.getElementById('end_at').value = this.value;
            });
            </script>

            <label for="tasktitle"><?php echo __('Task Title*', 'wp-easy-crm')?></label></br>
            <input type="text" name="tasktitle" id="tasktitle" maxlength="198" style="width:70%" required value="<?php echo esc_html(@$row_details_task['tasktitle']) ?>"><br><br>

            <label for="taskdescription"><?php echo __('Task Description', 'wp-easy-crm')?></label></br>
            <!-- <textarea name="taskdescription" id="taskdescription" maxlength="19800" style="width:70%" rows="6"><?php echo esc_html(@$row_details_task['taskdescription']) ?></textarea><br><br> -->
            <?php wp_editor( @$row_details_task['taskdescription'], 'taskdescription', array( 'textarea_name' => 'taskdescription','textarea_rows' => get_option('default_post_edit_rows', 6), ),
            array('editor_id' => 'taskdescription' // Editor ID, which must match the ID used in the selector in the filter
            ) ); ?>
            <br>  

            <input type="submit" name="submit" onclick="var btn = this; setTimeout(function() { btn.disabled = true; setTimeout(function() { btn.disabled = false; }, 4000); }, 50);" value="<?php echo __('Submit', 'wp-easy-crm')?>">
        </form>

        <script>
            function eacr_checklengthsetMaxlengthForTextarea() {
                // Get the textarea element by ID
                var textarea = document.getElementById('taskdescription');

                // Set the maximum length of characters
                var maxlength = 19800;

                // Add an event listener to the textarea to limit its length
                textarea.addEventListener('input', function () {
                    if (textarea.value.length > maxlength) {
                        textarea.value = textarea.value.substring(0, maxlength);
                    }
                });
            }

            jQuery(document).ready(function ($) {
                // Call the function after document ready
                eacr_checklengthsetMaxlengthForTextarea();
            });

        </script>

        <script>
            setTimeout(eacr_checklength, 3000);

            function eacr_checklength() {

            jQuery(document).ready(function($) {
                var editor = $('#taskdescription_ifr').contents().find('#tinymce');
                var maxChars = 19800;

                editor.on('keyup', function(e) {
                    var content = $(this).text();
                    if (content.length >= maxChars && e.keyCode !== 8) {
                        e.preventDefault();
                        alert('You have reached the maximum character limit.');
                    }
                });
            });

            }
        </script>
     

    </div>