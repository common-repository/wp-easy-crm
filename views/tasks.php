<?php 

global $wpdb;
$registration_date = date("Y-m-d H:i:s", time());
$clientTable = $wpdb->prefix . 'clients';
$clientLogTable = $wpdb->prefix . 'logclient';
$taskTable = $wpdb->prefix . 'task';
$accregionTable = $wpdb->prefix . 'eacraccountingregion';


$msg = '';

$id = sanitize_text_field(isset($_GET['id']) ? intval($_GET['id']) : "");
$action = sanitize_text_field(isset($_GET['action']) ? trim($_GET['action']) : "");
$taskid = sanitize_text_field(isset($_GET['taskid']) ? trim($_GET['taskid']) : "");


$myuserid = get_current_user_id(); 

// $row_details = $wpdb->get_row(
//     $wpdb->prepare(
//         "SELECT * from $clientTable WHERE id = %d",$id
//     ),ARRAY_A
// );

// $alllogs = $wpdb->get_results(
//     $wpdb->prepare(
//         "SELECT * from $clientLogTable WHERE clientidfk = %d ORDER BY created_at DESC",$id
//     ),ARRAY_A
// );

$allmyopentasks = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * from $taskTable WHERE useridfk = %d AND completed = 0 ORDER BY created_at DESC",$myuserid
    ),ARRAY_A
);

$alltasks = array(); // Initialize $alltasks as an empty array


// get current user id and which regions they have access to
$user_id = get_current_user_id(); // The ID of the user
$regions = $wpdb->get_results("SELECT id FROM $accregionTable");

    $selected_regions = [];
    foreach ($regions as $region) {
        if (get_user_meta($user_id, 'region_' . $region->id, true)) {
            $selected_regions[] = $region->id;
        }
    }

//check if user has general access or is admin
$contains_id_1 = in_array(1, $selected_regions);

if ($contains_id_1 == true || current_user_can('manage_options') == true ) {

    // The selected_regions array contains the ID 1 - General access
       // The current user is an administrator
       $alltasks = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * FROM $taskTable ORDER BY created_at DESC LIMIT 3000",$id
        ), ARRAY_A
    );
    
} else {
    // The selected_regions array does not contain the ID 1 now check if you're editor
    if ( current_user_can( 'editor' ) || current_user_can( 'shop_manager' ) ) {
        // The current user is either an editor or a shop manager
        // Ensure $selected_regions is an array of integers
            $selected_regions = array_map('intval', $selected_regions);
    
            // Create a string of comma-separated values from the $selected_regions array
            $selected_regions_str = implode(',', $selected_regions);
    
            if (!empty($selected_regions_str)) {
                $allclients = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM $clientTable WHERE idaccountingregionfk IN (%s) ORDER BY clientname",
                        $selected_regions_str
                    ),
                    ARRAY_A
                );


                // Iterate over each client and load tasks
                foreach ($allclients as $client) {
                    $client_id = $client['id']; // Assuming 'id' is the field name in $clientTable

                    // Load tasks for the current client that are not assigned to the current user
                    $client_tasks = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT * FROM $taskTable WHERE clientidfk = %d ORDER BY created_at DESC LIMIT 3000",
                            $client_id
                        ),
                        ARRAY_A
                    );

                    // Merge the tasks of this client into the overall tasks array
                    $alltasks = array_merge($alltasks, $client_tasks);
                }
            } else {
                // Handle the case where there are no selected regions
                $alltasks = array();
            }
    
    }
}





if(!empty($action)){
    //check what action
    if($action == "changepostatus"){

    }

}

?>

<script language=javascript>
function aecr_confirmDeleteSuppl()
{
    var res;
    res = confirm('<?php _e( 'Are you sure that you want to delete the client and all its data like quotes, invoices, tasks etc?', 'wp-easy-crm' );?>');
    if (res==false)
    return false;
    
    res = confirm('<?php _e( 'Are you completely sure that you want to delete the client and all its data like quotes, invoices, tasks etc?', 'wp-easy-crm' );?>');
    if (res==false)
    return false;
    return true;
}
function aecr_confirmDeleteLog()
{
    var res;
    res = confirm('<?php _e( 'Are you sure that you want to delete this log?', 'wp-easy-crm' );?>');
    if (res==false)
    return false;
    return true;
}
function aecr_confirmDeleteTask()
{
    var res;
    res = confirm('<?php _e( 'Are you sure that you want to delete this task?', 'wp-easy-crm' );?>');
    if (res==false)
    return false;
    return true;
}
</script>

<p><?php echo wp_kses_post($msg); ?></p>
<a class="button button-primary" href="admin.php?page=wp-easy-crm"><?php echo __('List of all Client Contacts', 'wp-easy-crm')?></a><span> </span>
<!-- <a class="button button-secondary" href="admin.php?page=wp-easy-crm-addclient&action=edit&id=<?php echo esc_html($id)?>"><?php _e( 'edit client', 'wp-easy-crm' );?></a> -->
<a class="button button-secondary" style="float-left" href="admin.php?page=wp-easy-crm-addtask&id=<?php echo esc_html($id)?>"><?php _e( 'add task', 'wp-easy-crm' );?></a>
<a class="button button-secondary" id="buttonshowall" href=""><?php _e( 'Show all Taks', 'wp-easy-crm' );?></a>
<a class="button button-secondary" id="buttonopenonly" href=""><?php _e( 'Show open Tasks only', 'wp-easy-crm' );?></a>
<a class="button button-secondary" id="buttoncompleteonly" href=""><?php _e( 'Show completed Tasks only', 'wp-easy-crm' );?></a>

<div><h1 style="float:left"><?php _e( 'Tasks', 'wp-easy-crm' );?></h1> </br></br>

<script>

    jQuery(document).ready(function ($) {

        var mytaskstable = $('#tablemyopentasks').DataTable({
            "pageLength": -1,  // Initially show all rows
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],  // Dropdown for number of rows to be displayed per page
            "order": [[4, 'asc']], // Replace 'columnIndex' with the index of the end-date column
            responsive: {
                details: {
                    type: 'column',
                    target: 1 // Target the second column (index starts at 0)
                }
            },
        });
        var recenttaskstable = $('#tablerecenttasks').DataTable({
            "pageLength": -1,  // Initially show all rows
            "lengthMenu": [[10, 25, 50, -1], [10, 25, 50, "All"]],  // Dropdown for number of rows to be displayed per page
            "order": [[4, 'asc']], // Replace 'columnIndex' with the index of the end-date column
            responsive: {
                    details: {
                        type: 'column',
                        target: 1 // Target the second column (index starts at 0)
                    }
                },
        });

        $('#buttonshowall').on('click', function() {
            event.preventDefault(); // Prevent the default anchor behavior
            // Perform a column-specific search
            // Assuming the 'Status' column is the second column (index 1)
            mytaskstable.column(2).search('').draw();
            recenttaskstable.column(2).search('').draw();

        });

        $('#buttonopenonly').on('click', function() {
            event.preventDefault(); // Prevent the default anchor behavior
            // Perform a column-specific search
            // Assuming the 'Status' column is the second column (index 1)
            mytaskstable.column(2).search('open').draw();
            recenttaskstable.column(2).search('open').draw();
        });

        $('#buttoncompleteonly').on('click', function() {
            event.preventDefault(); // Prevent the default anchor behavior
            // Perform a column-specific search
            // Assuming the 'Status' column is the second column (index 1)
            mytaskstable.column(2).search('completed').draw();
            recenttaskstable.column(2).search('completed').draw();

        });

    });

    </script>

<!--My open Tasks section-->
<div style="background-color:lavender;padding: 20px; margin-top:2em">
    
    <h2><?php _e( 'My open Tasks', 'wp-easy-crm' );?></h2>
    <table id="tablemyopentasks" class="wp-list-table widefat fixed striped table-view-list posts" cellspacing="0" style="padding:20px;padding-left:10px;padding-right:10px">
        <thead>
        <tr>
            <th class="manage-column column-cb check-column all" scope="col" style="width:20%"><b><?php _e( 'Title', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column all" scope="col" style="width:40%"><b><?php _e( 'Description', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column all" scope="col"><b><?php _e( 'Status', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Owner', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'End-date', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col"><b></b></th>
        </tr>
        </thead>
        <tbody>
            <?php   
            if(count($allmyopentasks) > 0){
                foreach($allmyopentasks as $singletask){
                    echo '<tr>';

                    $clientid = sanitize_text_field($singletask['clientidfk']);

                            if($clientid != 0)
                            { 
                                echo '<td class="column-columnname" style="width:20%"><a href="admin.php?page=wp-easy-crm-perfil&id='.$clientid.'">'.esc_html($singletask['tasktitle']).'</a>'.'</td>';
                            }
                            else
                            {
                                echo '<td class="column-columnname" style="width:20%">'.esc_html($singletask['tasktitle']).'</td>';
                            }

                    
                    // echo '<td class="column-columnname" style="width:40%">'.esc_html($singletask['taskdescription']).'</td>';
                    echo '<td class="column-columnname" style="width:40%">'.wp_kses_post($singletask['taskdescription']).'</td>';


                    if($singletask['completed'] == 0){
                        echo '<td class="column-columnname">'.__( 'open', 'wp-easy-crm' ).'</td>';
                    }
                    else{
                        echo '<td class="column-columnname">'.__( 'completed', 'wp-easy-crm' ).'</td>';
                    }

                    $user = get_user_by( 'id', $singletask['useridfk'] );
                    echo '<td class="column-columnname">'.$user->display_name.'</td>';

                    echo '<td class="column-columnname">'.esc_html($singletask['end_at']).'</td>';

                    echo '<td class="column-columnname">
                    <a class="" href="admin.php?page=wp-easy-crm-addtask&action=markcomplete&taskid='.esc_html($singletask['id']).'" onclick="return confirm(\'Do you want to mark this task as complete?\');" title="Mark task as complete">mark complete</a> | 
                    <a href="admin.php?page=wp-easy-crm-addtask&action=edit&taskid='.esc_html($singletask['id']).'&id='.esc_html($id).'">'.__( 'edit', 'wp-easy-crm' ).'</a>
                    | <a onclick="return aecr_confirmDeleteTask();" href="admin.php?page=wp-easy-crm-addtask&action=delete&taskid='.esc_html($singletask['id']).'&id='.esc_html($id).'">'.__( 'delete', 'wp-easy-crm' ).'</a></td>';
                    
                    echo '</tr>';
                }
            }
            ?>
        
        </tbody>
    </table>
</div>

<!--Activity Tasks section-->
<div style="background-color:cornsilk;padding: 20px; margin-top:2em">
    <h2><?php _e( 'Recent Tasks (latest 3000 entries)', 'wp-easy-crm' );?></h2>
    <table id="tablerecenttasks" class="wp-list-table widefat fixed striped table-view-list posts" cellspacing="0" style="padding:20px;padding-left:10px;padding-right:10px">
    <thead>
        <tr>
            <th class="manage-column column-cb check-column all" scope="col" style="width:20%"><b><?php _e( 'Title', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column all" scope="col" style="width:40%"><b><?php _e( 'Description', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column all" scope="col"><b><?php _e( 'Status', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Owner', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'End-date', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col"><b></b></th>
        </tr>
        </thead>
        <tbody>

            <?php   
            if(count($alltasks) > 0){
                foreach($alltasks as $singletask){
                    echo '<tr>';

                    $clientid = sanitize_text_field($singletask['clientidfk']);

                            if($clientid != 0)
                            { 
                                echo '<td class="column-columnname" style="width:20%"><a href="admin.php?page=wp-easy-crm-perfil&id='.$clientid.'">'.esc_html($singletask['tasktitle']).'</a>'.'</td>';
                            }
                            else
                            {
                                echo '<td class="column-columnname" style="width:20%">'.esc_html($singletask['tasktitle']).'</td>';
                            }

                    
                    // echo '<td class="column-columnname" style="width:40%">'.esc_html($singletask['taskdescription']).'</td>';
                    echo '<td class="column-columnname" style="width:40%">'.wp_kses_post($singletask['taskdescription']).'</td>';

                    if($singletask['completed'] == 0){
                        echo '<td class="column-columnname">'.__( 'open', 'wp-easy-crm' ).'</td>';
                    }
                    else{
                        echo '<td class="column-columnname">'.__( 'completed', 'wp-easy-crm' ).'</td>';
                    }

                    $user = get_user_by( 'id', $singletask['useridfk'] );
                    echo '<td class="column-columnname">'.esc_html($user->display_name).'</td>';

                    echo '<td class="column-columnname">'.esc_html($singletask['end_at']).'</td>';

                    echo '<td class="column-columnname"><a href="admin.php?page=wp-easy-crm-addtask&action=edit&taskid='.esc_html($singletask['id']).'&id='.esc_html($id).'">'.__( 'edit', 'wp-easy-crm' ).'</a>
                    | <a onclick="return aecr_confirmDeleteTask();" href="admin.php?page=wp-easy-crm-addtask&action=delete&taskid='.esc_html($singletask['id']).'&id='.esc_html($id).'">'.__( 'delete', 'wp-easy-crm' ).'</a></td>';
                    
                    echo '</tr>';
                }
            }
            ?>
        
        </tbody>
    </table>
</div>

  





