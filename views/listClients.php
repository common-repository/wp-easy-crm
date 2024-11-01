<?php 
global $wpdb;
$clientTable = $wpdb->prefix . 'clients';
$clientLogTable = $wpdb->prefix . 'logclient';
$taskTable = $wpdb->prefix . 'task';
$quoteTable = $wpdb->prefix . 'quoteclient';
$quoteItemTable = $wpdb->prefix . 'lineitemquote';
$projectstable = $wpdb->prefix . 'easyprojects';
$publicLogClienttable = $wpdb->prefix . 'publiclog';
$taskRelationsTable = $wpdb->prefix . 'taskrelations';
$accregionTable = $wpdb->prefix . 'eacraccountingregion';
$projectsTable = $wpdb->prefix . 'easyprojects';
$tagTable = $wpdb->prefix . 'easytags';



$action = sanitize_text_field(isset($_GET['action']) ? trim($_GET['action']) : "");
$id = sanitize_text_field(isset($_GET['id']) ? intval($_GET['id']) : "");


if(!empty($action) && $action=="deleteclient"){


    $allquotestodelete = $wpdb->get_results(
        $wpdb->prepare(
                    "SELECT * from $quoteTable WHERE clientidfk = %d", $id
        ),ARRAY_A
    );

    foreach($allquotestodelete as $singlequote){
        $wpdb->delete($quoteItemTable, array( 'quoteidfk' => $singlequote['id'] ));
    }

    $wpdb->delete($quoteTable, array( 'clientidfk' => $id ));


    $wpdb->delete($clientLogTable, array( 'clientidfk' => $id ));
    $wpdb->delete($taskTable, array( 'clientidfk' => $id ));
    $wpdb->delete($clientTable, array( 'id' => $id ));

    $allprojectstodelete = $wpdb->get_results(
        $wpdb->prepare(
                    "SELECT * from $projectstable WHERE clientidfk = %d", $id
        ),ARRAY_A
    );
    foreach($allprojectstodelete as $singleproject){
        $wpdb->delete($publicLogClienttable, array( 'projectidfk' => $singleproject['id'] ));
        $wpdb->delete($taskRelationsTable, array( 'projectidfk' => $singleproject['id'] ));
    }    
    $wpdb->delete($projectstable, array( 'clientidfk' => $id ));



    echo $wpdb->last_error;

        $msg = "<div class='updated update'>".__( 'contact successfully deleted', 'wp-easy-crm' )."</div>";

}

$allclients = array(); // Initialize $allclients as an empty array

$clientsneedprojectaction = array();

//get all client ids that need project contact
if ($wpdb->get_var("SHOW TABLES LIKE '$projectsTable'") == $projectsTable) {
    // Table exists, select rows with completed = 0
    $results = $wpdb->get_results("SELECT * FROM $projectsTable WHERE completed = 0");

    $countneededprojectaction = 0;

    if ($results) {
        foreach ($results as $result) {
            $project_id = $result->id;
            $project_name = $result->projectname;
            $clientidneedprojectaction = $result->clientidfk;


            // Search for the newest row in wp_publiclog with the same project id
            $log_table_name = $wpdb->prefix . 'publiclog'; // Adjust the table name according to your prefix
            $log_result = $wpdb->get_row("SELECT * FROM $log_table_name WHERE projectidfk = $project_id ORDER BY created_at DESC LIMIT 1");

            if ($log_result) {
                $created_at = strtotime($log_result->created_at);
                $now = time();
                $time_difference = $now - $created_at;
                $days_difference = floor($time_difference / (60 * 60 * 24));

                if ($days_difference > 14) {
                     // If conditions are met, add the client ID to the array
                    $clientsneedprojectaction[] = $clientidneedprojectaction;
                }
            }
            
        }

        if($countneededprojectaction == 0){
        //   echo "<center>Good job! There is no pending client communication!</center>";
        }
    } else {
        // echo "No ongoing projects found.";
    }
} else {
    // echo "Table with projects does not exist.";
}

$leadsneedlogaction = array();

//get all lead ids that need action
if ($wpdb->get_var("SHOW TABLES LIKE '$clientTable'") == $clientTable) {
    // Table exists, select rows with completed = 0
    $results = $wpdb->get_results("SELECT * FROM $clientTable WHERE clientstatus = 'lead'");

    $countneededaction = 0;

    if ($results) {
        foreach ($results as $result) {
            $client_id = $result->id;
            $client_name = $result->clientname;
  
            // Search for the newest row in wp_logclient with the same client id
            $log_table_name = $wpdb->prefix . 'logclient'; // Adjust the table name according to your prefix
            $log_result = $wpdb->get_row("SELECT * FROM $log_table_name WHERE clientidfk = $client_id ORDER BY created_at DESC LIMIT 1");

            if ($log_result) {
                $created_at = strtotime($log_result->created_at);
                $now = time();
                $time_difference = $now - $created_at;
                $days_difference = floor($time_difference / (60 * 60 * 24));

                if ($days_difference > 3) {
                    //count up
                    $countneededaction = $countneededaction + 1;
                    // Add client ID to the array
                    $leadsneedlogaction[] = $client_id;
                }
            }
            else{
             //count up
             $countneededaction = $countneededaction + 1;
             // Add client ID to the array
             $leadsneedlogaction[] = $client_id;
            }
            
        }

        if($countneededaction == 0){
        //   echo "<center>Good job! There are no leads to contact or work on right now!</center>";
        }
    } else {
        // echo "No ongoing leads found.";
    }
} else {
    // echo "Table with clients does not exist.";
}

$clientsneedprojectcreation = array();

//get all clients that have no project yet
if ($wpdb->get_var("SHOW TABLES LIKE '$projectsTable'") == $projectsTable) {
    // Table exists, select rows with client
    $results = $wpdb->get_results("SELECT * FROM $clientTable WHERE clientstatus = 'customer'");
 
    if ($results) {
        foreach ($results as $result) {
            $client_id = $result->id;

            // Prepare the SQL query
            $queryclientcheckifprojectcount = $wpdb->prepare("SELECT COUNT(*) FROM $projectsTable WHERE clientidfk = %d", $client_id);

            // Execute the query and get the result
             $count = $wpdb->get_var($queryclientcheckifprojectcount);

            $client_name = $result->clientname;

            // Check if there are any rows
            // Check if the client has any projects
        if ($count == 0) {
            // Client doesn't have a project, add their ID to the array
            $clientsneedprojectcreation[] = $client_id;
        }
   
  
    } 
} else {
    // echo "Table with clients does not exist.";
}
}


// Assuming $tagTable is defined and contains the name of your tag table
$allTags = $wpdb->get_results("SELECT * FROM $tagTable ORDER BY tagtitle ASC");
$tagMap = [];
foreach ($allTags as $tag) {
    // Assuming $tag->id, $tag->tagtitle, and $tag->tagcolor are the properties
    $tagMap[$tag->id] = $tag;
}

// get current user id and which regions they have access to
$user_id = get_current_user_id(); // The ID of the user
// echo $user_id;

$regions = $wpdb->get_results("SELECT id, regionname FROM $accregionTable");

// echo "Accounting Regions for User ID $user_id:<br>";
//     foreach ($regions as $region) {
//         $region_meta_key = 'region_' . $region->id;
//         if (get_user_meta($user_id, $region_meta_key, true)) {
//             echo "Region ID: " . $region->id . "<br>";
//         }
//     }



    $selected_regions = [];
    foreach ($regions as $region) {
        if (get_user_meta($user_id, 'region_' . $region->id, true)) {
            $selected_regions[] = $region->id;
        }
    }

$regionNamesString = ''; //this one is just a string spaceholder to show the access area on the screen

//check if user has general access
$contains_id_1 = in_array(1, $selected_regions);
// print_r($selected_regions);
// echo $contains_id_1;
if ($contains_id_1 == true || current_user_can('manage_options') == true ) {
//if ($contains_id_1 == true) {
    // The selected_regions array contains the ID 1 - General access to all regions
    // get all countries and get their name    
    foreach ($regions as $region) {
        $regionNamesString .= $region->regionname . ' '; // Append each region name followed by a space
    }
    // Trim the trailing space
    $regionNamesString = trim($regionNamesString);

       // The current user is an administrator
       $allclients = $wpdb->get_results(

        $wpdb->prepare(
                    "SELECT * from $clientTable ORDER BY clientname",""
        ),ARRAY_A
    );

    // Check if the export button was clicked
if(isset($_POST['export_data'])) {
    exportTableDataToCSV($allclients);
}


} else {
    // The selected_regions array does not contain the ID 1 now check if you're editor
    if ( current_user_can( 'editor' ) || current_user_can( 'shop_manager' ) ) {
        // The current user is either an editor or a shop manager
        // Ensure $selected_regions is an array of integers
            $selected_regions = array_map('intval', $selected_regions);
    
            // Create a string of comma-separated values from the $selected_regions array
            $selected_regions_str = implode(',', $selected_regions);
    
            if (!empty($selected_regions_str)) {

                //get regionnames with access 
                $allclients = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM $clientTable WHERE idaccountingregionfk IN (%s) ORDER BY clientname",
                        $selected_regions_str
                    ),
                    ARRAY_A
                );

                // Assuming $selected_regions is an array of ids

                    foreach ($selected_regions as $selected_id) {
                        foreach ($regions as $region) {
                            if ($region->id == $selected_id) {
                                $regionNamesString .= $region->regionname . ' ';
                                // Do not break the loop, as there might be more matches
                            }
                        }
                    }

                    // Trim the trailing space from the string
                    $regionNamesString = trim($regionNamesString);

            } else {
                // Handle the case where there are no selected regions
                $allclients = array();
            }
    
    }
}


if(count($allclients) > 0){

    ?>
    <script>
        


        jQuery(document).ready(function ($) {
            // Custom Sorting Function
            jQuery.fn.dataTable.ext.type.order['span-title-pre'] = function (data) {
            // Extract the title attribute from the span
            var title = jQuery(data).find('span').attr('title') || '';
            return title.toLowerCase(); // Using lower case for case-insensitive sorting
        };

        // Initialize your DataTable
        var table = $('#clientstable').DataTable({
            order: [[8, 'desc']],
            "pageLength": 12, // Default number of rows to display
            "lengthMenu": [[10, 25, 50, 100, -1], [10, 25, 50, 100, "All"]], // Page length options (including 'All')
            "columnDefs": [
                { "width": "4%", "targets": 0 }, // Adjust the width of the first column
                {
                    type: 'span-title-pre',  // Use the custom sorting type
                    targets: 'action'        // Apply it to the column with class 'action'
                },
                {
                    "targets": 9, // Index of the 9th column (Region ID)
                    "visible": false // Hide the column
                }
            ],
            responsive: {
                details: {
                    type: 'column',
                    target: 2 // Target the third column (index starts at 0)
                }
            }
        });

    
        $('#buttonshowall').on('click', function() {
            event.preventDefault(); // Prevent the default anchor behavior
            // Perform a column-specific search
            // Assuming the 'Status' column is the second column (index 1)
            table.column(2).search('').draw();
        });

        $('#buttonleadsonly').on('click', function() {
            event.preventDefault(); // Prevent the default anchor behavior
            // Perform a column-specific search
            // Assuming the 'Status' column is the second column (index 1)
            table.column(2).search('Lead').draw();
        });

        $('#buttonclientsonly').on('click', function() {
            event.preventDefault(); // Prevent the default anchor behavior
            // Perform a column-specific search
            // Assuming the 'Status' column is the second column (index 1)
            table.column(2).search('Customer').draw();
        });

        $('#buttonblackrefonly').on('click', function() {
            event.preventDefault(); // Prevent the default anchor behavior
            // Perform a column-specific search
            // Assuming the 'Status' column is the second column (index 1)
            table.column(2).search('Refused|Blacklisted', true, false).draw();
        });

        // Event listener for region filter buttons
        jQuery('.region-filter-btn').on('click', function() {
            var regionId = jQuery(this).data('regionid');
            
            if (regionId === 1) {
                // If region ID is 1, clear the filter and show all regions
                table.search('').columns().search('').draw(); 
            } else {
                // Filter DataTables rows based on the region ID
                // Assumes the region ID is in a specific column (e.g., column index 9)
                table.column(9).search(regionId).draw(); 
            }
        });

        jQuery('#filterbytags').click(function(e) {
        e.preventDefault();
        jQuery(this).toggleClass('show-menu');
        jQuery('#tagFilterContainer').slideToggle();
        

    });

// Filter button click event handler
jQuery('#applyTagFilter').click(function() {
    // Get an array of selected tag IDs
    var selectedTags = jQuery('input[name="tags[]"]:checked').map(function() {
        return jQuery(this).val();
    }).get();

    // Clear any previous custom search function
    jQuery.fn.dataTable.ext.search = [];

    if (selectedTags.length > 0) {
        // Use custom filtering function to check if any of the selected tags match the tags associated with each row
        jQuery.fn.dataTable.ext.search.push(
            function(settings, data, dataIndex) {
                var rowTags = jQuery(table.row(dataIndex).node()).find('.column-columnname span[data-tagid]').map(function() {
                    return jQuery(this).data('tagid').toString();
                }).get();

                return selectedTags.every(function(tag) {
                    return rowTags.includes(tag);
                });
            }
        );
    }

    table.draw(); // Redraw table to apply search/filter

    //jQuery('#tagFilterContainer').slideUp(); // Hide the tag filter container
});




    });


    

    </script>
    
    <p><?php echo wp_kses_post(@$msg); ?></p>
    <!-- Flex container -->
<div style="display: flex; justify-content: space-between; align-items: center;">

<!-- All your links -->
<div>
    <a class="button button-primary" href="admin.php?page=wp-easy-crm-addclient"><?php echo __('Add Client', 'wp-easy-crm')?></a>
    <a class="button button-secondary" id="buttonshowall" href=""><?php _e( 'Show all Contacts', 'wp-easy-crm' );?></a>
    <a class="button button-secondary" id="buttonleadsonly" href=""><?php _e( 'Show Leads only', 'wp-easy-crm' );?></a>
    <a class="button button-secondary" id="buttonclientsonly" href=""><?php _e( 'Show Clients only', 'wp-easy-crm' );?></a>
    <a class="button button-secondary" id="buttonblackrefonly" href=""><?php _e( 'Show Refused and Blacklisted only', 'wp-easy-crm' );?></a>
    <style>#tagFilterContainer {
    padding: 10px;
    border: 1px solid #ccc;
    margin-top: 10px;
    }
    #filterbytags::after {
    content: '\25BC'; /* Unicode character for down-pointing triangle */
    }

    #filterbytags.show-menu::after {
        content: '\25B2'; /* Unicode character for up-pointing triangle */
    }

    </style>
    <a class="button button-secondary" id="filterbytags" href=""><?php _e( 'Filter by Tags', 'wp-easy-crm' );?></a>
    <div id="tagFilterContainer" style="display:none;">
    <span>Those filters will persist until the page is reloaded. Uncheck all and click 'Filter' again to show all entries.</span>
    <form id="tagFilterForm" style="display: flex; flex-wrap: wrap; align-items: center; padding: 10px;">
        <?php foreach ($allTags as $tag): ?>
            <label style="margin: 5px; padding: 5px; background-color: <?php echo esc_attr($tag->tagcolor); ?>; color: #ffffff; border-radius: 5px; display: flex; align-items: center;">
                <input type="checkbox" name="tags[]" value="<?php echo esc_attr($tag->id); ?>" style="margin-right: 5px;" />
                <?php echo esc_html($tag->tagtitle); ?>
            </label>
        <?php endforeach; ?>
        <button type="button" class="button-primary" id="applyTagFilter" style="margin-left: auto; padding: 5px 10px; cursor: pointer;"><?php _e('Filter', 'wp-easy-crm'); ?></button>
    </form>
    </div>

    <?php 
    // if general access list buttons to filter by country / accounting region
    if ($contains_id_1 == true) {
        echo "<div style='padding-top:10px;'>Sort by region: ";
        foreach ($regions as $region) {
            $regionId = $region->id; // Use -> notation to access object property
            $regionName = htmlspecialchars($region->regionname);
            echo "<button class='region-filter-btn' data-regionid='$regionId' style='margin-right:5px;'>$regionName</button>";
        }
        echo "</div>";
    }
    ?>
</div>



<!-- Div aligned to the right -->
<div><?php _e( 'Access: ', 'wp-easy-crm' ); echo $regionNamesString;
// if general access list buttons to filter by country / accounting region
if ($contains_id_1 == true) {
    echo "<form method='post'>
    <button type='submit' name='export_data'>Export Client Data</button>
    </form>";

}
?></div>

</div>
    <div style="float:right"><?php _e( 'Total Contacts: ', 'wp-easy-crm' ); echo count($allclients)?></div>


<style>

@media (max-width: 767px) { /* Adjust max-width as needed for your target mobile breakpoint */
    #wpcontent {
        padding-left: 5px !important;
        padding-right: 5px !important;
    }
}
    .dot {
    height: 16px;
    width: 16px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 6px;
}

.dot-action-needed {
    background-color: rgba(255, 0, 0, 0.5); /* Red with half opacity */
}

.dot-log-action-needed {
    background-color: rgba(128, 0, 128, 0.5); /* Purple with half opacity */
}

.dot-project-creation-needed {
    background-color: rgba(255, 165, 0, 0.5); /* Orange with half opacity */
}

.dot-no-action-needed {
    background-color: rgba(0, 128, 0, 0.5); /* Green with half opacity */
}


</style>

    <h1><?php _e( 'Client Contacts', 'wp-easy-crm' );?></h1>
    <table class="wp-list-table widefat fixed striped table-view-list posts" id="clientstable" cellspacing="0" style="padding:20px;padding-left:10px;padding-right:10px;max-width:100%">
    <thead>
    <tr>
        <th class="manage-column column-cb check-column action all" scope="col"><b><?php _e( 'Action', 'wp-easy-crm' );?></b></th>
        <th class="manage-column column-cb check-column all" scope="col"><b><?php _e( 'Client', 'wp-easy-crm' );?></b></th>
        <th class="manage-column column-cb check-column all" scope="col"><b><?php _e( 'Status', 'wp-easy-crm' );?></b></th>
        <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Source', 'wp-easy-crm' );?></b></th>
        <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Email(s)', 'wp-easy-crm' );?></b></th>
        <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Contacts', 'wp-easy-crm' );?></b></th>
        <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Telephone', 'wp-easy-crm' );?></b></th>
        <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Tags', 'wp-easy-crm' );?></b></th>
        <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Date added', 'wp-easy-crm' );?></b></th>
        <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Region ID', 'wp-easy-crm' );?></b></th>

        <!-- <th class="manage-column column-cb check-column" scope="col"><b></b></th> -->
    </tr>
    </thead>
    <tbody>
<?php    
$count = 1;
    foreach($allclients as $index => $client){
?>

<tr class="clientrow">
    <td class="column-columnname">
    <?php
        $client_id = $client['id']; 
        $client_in_any_array = false; // Flag to track if the client is in any array

        // Check if the client ID is in the $clientsneedprojectaction array
        if (in_array($client_id, $clientsneedprojectaction)) {
            echo "<span class='dot dot-action-needed' title='Customer Project Communcation overdue through project log'></span>";
            $client_in_any_array = true;
        }

        // Check if the client ID is in the $leadsneedlogaction array
        if (in_array($client_id, $leadsneedlogaction)) {
            echo "<span class='dot dot-log-action-needed' title='Lead has no log or has not been worked on in over 3 days.'></span>";
            $client_in_any_array = true;
        }

        // Check if the client ID is in the $clientsneedprojectcreation array
        if (in_array($client_id, $clientsneedprojectcreation)) {
            echo "<span class='dot dot-project-creation-needed' title='Client Project creation needed'></span>";
            $client_in_any_array = true;
        }

        // If the client ID isn't in any of the arrays
        if (!$client_in_any_array) {
            echo "<span class='dot dot-no-action-needed' title='No suggested action needed'></span>"; // You can customize this part as needed
        }
    ?>
    </td>

    <td class="column-columnname"><a href="admin.php?page=wp-easy-crm-perfil&id=<?php echo esc_html($client['id']);?>"><?php echo esc_html($client['clientname']) ?></a>
    <a href="admin.php?page=wp-easy-crm-perfil&id=<?php echo esc_html($client['id']); ?>" target="_blank" title="Open in new tab">
        🔗
    </a>
    </td>
    <td class="column-columnname">
        <?php if($client['clientstatus'] == "lead")
            {
                _e( "Lead", 'wp-easy-crm' );
            }
            elseif($client['clientstatus'] == "customer")
            {
                _e( 'Customer', 'wp-easy-crm' );
            }
            elseif($client['clientstatus'] == "refused")
            {
                _e( 'Refused', 'wp-easy-crm' );
            }
            elseif($client['clientstatus'] == "blacklisted")
            {
                _e( 'Blacklisted', 'wp-easy-crm' );
            }
        ?>
    </td>
    <td class="column-columnname">
        <?php if($client['clientsource'] == "website") 
        {
            _e( "Website", 'wp-easy-crm' );
        }
        elseif($client['clientsource'] == "email")
        {
            _e( 'Email', 'wp-easy-crm' );
        }
        elseif($client['clientsource'] == "call")
        {
            _e( 'Call', 'wp-easy-crm' );
        }
        elseif($client['clientsource'] == "social media")
        {
            _e( 'Socal Media', 'wp-easy-crm' );
        }
        elseif($client['clientsource'] == "recommendation")
        {
            _e( 'Recommendation', 'wp-easy-crm' );
        }
        elseif($client['clientsource'] == "other")
        {
            _e( 'Other', 'wp-easy-crm' );
        }

        ?>
    </td>
    <td class="column-columnname"><?php echo esc_html($client['email']) ?></td>
    <td class="column-columnname"><?php echo esc_html($client['nombrecontacto']) ?></td>
    <td class="column-columnname"><?php echo esc_html($client['telefono']) ?></td>
    <td class="column-columnname">
    <?php 
    // Split the client's tag relations into an array of tag IDs
    $clientTagIds = explode(',', $client['tagsrelations']);
    
    foreach ($clientTagIds as $tagId) {
        // Check if the tag ID exists in the tag map
        if (isset($tagMap[$tagId])) {
            // Display the tag title in its defined color
            $tag = $tagMap[$tagId];
            echo '<span data-tagid="' . esc_attr($tag->id) . '" style="background-color: ' . esc_attr($tag->tagcolor) . '; color: #ffffff; padding: 2px 5px; border-radius: 3px; margin-right: 5px;">' . esc_html($tag->tagtitle) . '</span>';
        }
    }
    ?>
    </td>


    <td class="column-columnname"><?php echo esc_html($client['created_at']) ?></td>
    <td class="column-columnname"><?php echo esc_html($client['idaccountingregionfk']) ?></td>

    <!-- <td class="column-columnname"><a href="admin.php?page=wp-easy-crm-addclient&action=edit&id=<?php echo esc_html($client['id']);?>"><?php _e( 'edit client', 'wp-easy-crm' );?></a></td> -->
</tr>
<?php

    }
    ?>
     </tbody>
    </table>




    <?php
}
else{
    echo '<a class="button-primary" href="admin.php?page=wp-easy-crm-addclient" style="margin:2em">'.__( 'Please add your first client. All your clients will show here.', 'wp-easy-crm' ).'</a>';
}




function exportTableDataToCSV($data) {
    // Clean (erase) the output buffer and turn off output buffering
    // In case any output has been inadvertently started earlier
    if (ob_get_level()) {
        ob_end_clean();
    }
    ob_start(); // Start buffering output

    // Set the filename with the current date
    $filename = "clients_export_" . date("Y-m-d") . ".csv";

    // Send headers to prompt download
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '"');

    // Open PHP output stream as file handle for fputcsv()
    $output = fopen("php://output", "w");

    // Add CSV header
    fputcsv($output, array('Client', 'Status', 'Source', 'Email(s)', 'Contacts', 'Telephone', 'Date added', 'Region ID'));

    // Loop through data and add to CSV, ensuring correct order and presence of fields
    foreach ($data as $row) {
        $csvRow = array(
            isset($row['clientname']) ? $row['clientname'] : '',
            isset($row['clientstatus']) ? $row['clientstatus'] : '',
            isset($row['clientsource']) ? $row['clientsource'] : '',
            isset($row['email']) ? $row['email'] : '',
            isset($row['nombrecontacto']) ? $row['nombrecontacto'] : '',
            isset($row['telefono']) ? $row['telefono'] : '',
            isset($row['created_at']) ? $row['created_at'] : '',
            isset($row['idaccountingregionfk']) ? $row['idaccountingregionfk'] : '',
        );

        fputcsv($output, $csvRow);
    }

    // Close the output stream
    fclose($output);

    // Flush the output buffer and turn off output buffering
    ob_end_flush();

    // Ensure no further processing is done
    exit();
}




?>