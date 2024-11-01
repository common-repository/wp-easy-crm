<?php 

global $wpdb;
$registration_date = date("Y-m-d H:i:s", time());
$clientTable = $wpdb->prefix . 'clients';
$clientLogTable = $wpdb->prefix . 'logclient';
$taskTable = $wpdb->prefix . 'task';
$quotesInvoiceTable = $wpdb->prefix . 'quoteclient';
$accregionTable = $wpdb->prefix . 'eacraccountingregion';
$quoteItemTable = $wpdb->prefix . 'lineitemquote';



$msg = '';

$id = sanitize_text_field(isset($_GET['id']) ? intval($_GET['id']) : "");
$action = sanitize_text_field(isset($_GET['action']) ? trim($_GET['action']) : "");


$row_details = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * from $clientTable WHERE id = %d",$id
    ),ARRAY_A
);

$accountingRegionRow = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT regionname FROM $accregionTable WHERE id = %d", $row_details['idaccountingregionfk']
    ),
    OBJECT
);

//check if user has access to this profile
//get users allowed regions
$regions = $wpdb->get_results("SELECT id, regionname FROM $accregionTable");
$user_id = get_current_user_id(); // The ID of the current user

$selected_regions = [];
    foreach ($regions as $region) {
        if (get_user_meta($user_id, 'region_' . $region->id, true)) {
            $selected_regions[] = $region->id;
        }
    }

$contains_id_1 = in_array(1, $selected_regions);
//check if general access
if ($contains_id_1 == false){
    //if not general check if user has access to client region
    $hasaccess = in_array($row_details['idaccountingregionfk'], $selected_regions);
    if($hasaccess == false){
        //doesn't have access -> redirect to client list
        echo '<script type="text/javascript">
        alert("You don\'t have access");
        window.location = "'.admin_url('admin.php?page=wp-easy-crm').'";
        </script>';
    }

}


if ($accountingRegionRow) {
    $accountingRegionName = $accountingRegionRow->regionname;
} else {
    $accountingRegionName = ''; // or any default value you prefer
}

$alllogs = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * from $clientLogTable WHERE clientidfk = %d ORDER BY created_at DESC",$id
    ),ARRAY_A
);

$alltasks = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * from $taskTable WHERE clientidfk = %d ORDER BY created_at DESC",$id
    ),ARRAY_A
);

$allquotes = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * from $quotesInvoiceTable WHERE clientidfk = %d ORDER BY created_at DESC",$id
    ),ARRAY_A
);



?>

<script language=javascript>
function eacr_confirmDeleteSuppl()
{
    var res;
    res = confirm('<?php _e( 'Are you sure that you want to delete the client and all their data like quotes, invoices, tasks etc?', 'wp-easy-crm' );?>');
    if (res==false)
    return false;
    
    res = confirm('<?php _e( 'Are you completely sure that you want to delete the client and all their data like quotes, invoices, tasks etc?', 'wp-easy-crm' );?>');
    if (res==false)
    return false;
    return true;
}
function eacr_confirmDeleteLog()
{
    var res;
    res = confirm('<?php _e( 'Are you sure that you want to delete the this log?', 'wp-easy-crm' );?>');
    if (res==false)
    return false;
    return true;
}
function eacr_confirmDeleteTask()
{
    var res;
    res = confirm('<?php _e( 'Are you sure that you want to delete the this task?', 'wp-easy-crm' );?>');
    if (res==false)
    return false;
    return true;
}
function eacr_confirmDeleteQuote()
{
    var res;
    res = confirm('<?php _e( 'Are you sure that you want to delete the this Quote / Invoice?', 'wp-easy-crm' );?>');
    if (res==false)
    return false;
    return true;
}

function eacr_confirmMarkPaidQuote()
{
    var res;
    res = confirm('<?php _e( 'Are you sure that you want to mark this Quote / Invoice as paid?', 'wp-easy-crm' );?>');
    if (res==false)
    return false;
    return true;
}
</script>

<p><?php echo wp_kses_post($msg); ?></p>
<a class="button button-primary" href="admin.php?page=wp-easy-crm"><?php echo __('List of all Client Contacts', 'wp-easy-crm')?></a><span> </span>
<a class="button button-secondary" href="admin.php?page=wp-easy-crm-addclient&action=edit&id=<?php echo esc_html($id)?>"><?php _e( 'edit client', 'wp-easy-crm' );?></a>

<div><h1 style="float:left"><?php _e( 'Client Contact Profile', 'wp-easy-crm' );?> || <?php echo esc_html($row_details['clientname']) ?></h1> 
<a href="admin.php?page=wp-easy-crm&id=<?php echo $row_details['id'];?>&action=deleteclient" style="float:right;margin-left:20px;margin-top:12px;" class="delete-client-link" onclick="return eacr_confirmDeleteSuppl();"><?php _e( 'delete client', 'wp-easy-crm' );?></a><br/></div>
<br/><br/>

<code><b><?php _e( 'Status:', 'wp-easy-crm' );?></b> 
<?php if($row_details['clientstatus'] == "lead")
{
    _e( "Lead", 'wp-easy-crm' );
}
elseif($row_details['clientstatus'] == "customer")
{
    _e( 'Customer', 'wp-easy-crm' );
}
elseif($row_details['clientstatus'] == "refused")
{
    _e( 'Refused', 'wp-easy-crm' );
}
elseif($row_details['clientstatus'] == "blacklisted")
{
    _e( 'Blacklisted', 'wp-easy-crm' );
}

?> <b><?php _e( 'Contact(s):', 'wp-easy-crm' );?></b> <?php echo esc_html($row_details['nombrecontacto']) ?> <b>Email(s):</b> <?php echo esc_html($row_details['email']) ?> <b><?php _e( 'Telephone(s):', 'wp-easy-crm' );?></b> <?php echo $row_details['telefono'] ?> <b><?php _e( 'State:', 'wp-easy-crm' );?></b> <?php echo $row_details['provincia'] ?></code>
</br></br><code><b><?php _e( 'Address:', 'wp-easy-crm' );?></b> <?php echo esc_html($row_details['direccion']) ?> <b>Accounting Region:</b> <?php echo esc_html($accountingRegionName) ?> </code>
</br></br><code><b><?php _e( 'Note:', 'wp-easy-crm' );?></b> <?php echo esc_html($row_details['nota']) ?></code><br/><br/>
<b> <?php _e( 'Tags: ', 'wp-easy-crm' );?></b><?php echoTagsStuff($row_details); //add tags menu and pass client data ?>

<style>
    @media (max-width: 767px) { /* Adjust max-width as needed for your target mobile breakpoint */
    .delete-client-link {
        display: none;
    }
    .editcolumn {
        display: none;
    }
    #wpcontent {
        padding-left: 5px !important;
        padding-right: 5px !important;
    }
    
}

/* Add this CSS for the wiggle animation */
@keyframes wiggle {
  0%, 100% {
    transform: rotate(-5deg); /* Increase the rotation angle for a stronger effect */
  }
  50% {
    transform: rotate(5deg); /* Increase the rotation angle for a stronger effect */
  }
}
.wiggle {
  animation: wiggle 0.3s ease-in-out 2; /* Increase the duration to 1.5 seconds */
}

.tooltip {
    position: relative;
    display: inline-block;
    cursor: pointer;
}

.tooltip .tooltiptext {
    visibility: hidden;
    width: 300px; /* Adjust the width if needed */
    background-color: #333;
    color: #fff;
    text-align: left;
    padding: 5px;
    border-radius: 5px;
    position: absolute;
    z-index: 1;
    bottom: 100%; /* Position above the element */
    left: calc(50% + 160px); /* Shift to the right by the width of the WordPress menu */
    transform: translateX(-50%);
    white-space: normal; /* Allow wrapping and line breaks */
}

.tooltip:hover .tooltiptext {
    visibility: visible;
}




</style>

<script>
jQuery(document).ready(function() {
    // Inject CSS to hide sorting arrows and minimize thead height
    var css = `
    table.dataTable thead th {
        border: 0;
        padding: 0;
        height: 1px;
        font-size: 0;
    }
    table.dataTable thead .sorting,
    table.dataTable thead .sorting_asc,
    table.dataTable thead .sorting_desc,
    table.dataTable thead .sorting_asc_disabled,
    table.dataTable thead .sorting_desc_disabled {
        background-image: none !important;
    }`;
    var style = document.createElement('style');
    if (style.styleSheet) {
        style.styleSheet.cssText = css;
    } else {
        style.appendChild(document.createTextNode(css));
    }
    document.getElementsByTagName('head')[0].appendChild(style);

    // Add an empty thead and initialize DataTables
    jQuery('table').each(function() {
        var emptyThead = jQuery('<thead><tr></tr></thead>');
        var numCols = jQuery(this).find('tbody tr:first td').length;
        for (var i = 0; i < numCols; i++) {
            emptyThead.find('tr').append('<th></th>');
        }
        jQuery(this).prepend(emptyThead);

        // Initialize DataTables with specific options
        jQuery(this).DataTable({
            responsive: {
                details: {
                    type: 'column',
                    target: 1 // Target the second column (index starts at 0)
                }
            },
            paging: false,        // Disable pagination
            autoWidth: false, // Disable automatic column width calculation
            searching: false,     // Hide the search box
            info: false,          // Hide table information summary
            lengthChange: false,  // Hide length menu (number of rows per page selector)
            ordering: false,       // Disable ordering (sorting)
            columnDefs: [
                { width: '20%', targets: 0, className: 'all' },
                { width: '50%', targets: 1, className: 'all' },
                // Other columns will use auto width
            ]
        });
    });

 

    //set tables to full width on mobile
    
        // Define the max width for mobile devices
        var maxWidth = 768;

        // Function to apply styles
        function applyMobileStyles() {
            var divs = document.querySelectorAll('div > div');
            divs.forEach(function(div) {
                if (div.querySelector('table')) {
                    div.style.paddingLeft = '0';
                    div.style.paddingRight = '0';
                    div.style.width = '100%';
                    if (div.parentElement) {
                        div.parentElement.style.paddingLeft = '0';
                        div.parentElement.style.paddingRight = '0';
                    }
                    var table = div.querySelector('table');
                    if (table) {
                        table.style.width = '100%';
                    }
                }
            });
        }

        // Check if the current viewport width is less than or equal to maxWidth
        if (window.innerWidth <= maxWidth) {
            applyMobileStyles();
        }

        // Optionally, listen for window resize to re-apply or adjust styles
        window.addEventListener('resize', function() {
            if (window.innerWidth <= maxWidth) {
                applyMobileStyles();
            }
        });
  



});

//tooltip show
document.addEventListener('DOMContentLoaded', function () {
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    const adminMenuWidth = document.getElementById('adminmenuwrap').offsetWidth; // Get the width of the WordPress admin menu

    tooltipElements.forEach(function (elem) {
        const tooltipText = elem.getAttribute('data-tooltip');
        const tooltipDiv = document.createElement('div');
        tooltipDiv.className = 'tooltiptext';
        tooltipDiv.innerHTML = tooltipText;

        const wrapper = document.createElement('span');
        wrapper.className = 'tooltip';
        elem.parentNode.insertBefore(wrapper, elem);
        wrapper.appendChild(elem);
        wrapper.appendChild(tooltipDiv);

        // Adjust tooltip position based on admin menu width
        tooltipDiv.style.left = `calc(50% + ${adminMenuWidth}px)`;
    });
});




</script>









<!--Activity Log section-->
<div style="background-color:Lavender;padding: 20px; margin-top:2em">
    <a class="button button-secondary" style="float-left" href="admin.php?page=wp-easy-crm-addclientlog&id=<?php echo esc_html($id)?>"><?php _e( 'add log', 'wp-easy-crm' );?></a>
    <h2><?php _e( 'Recent Logs', 'wp-easy-crm' );?></h2>
    <table id="tablecreatepo" class="wp-list-table widefat fixed striped table-view-list posts" cellspacing="0" style="padding:20px;padding-left:10px;padding-right:10px">
        <!-- <thead>
        <tr>
            <th class="manage-column column-cb check-column" scope="col" style="width:20%"><b><?php _e( 'Title', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col" style="width:50%"><b><?php _e( 'Description', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'By', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Date', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col"><b></b></th>
        </tr>
        </thead> -->
        <tbody>

            <?php   

 function make_links_clickable($text) {
    // Match URLs with http:// or https://
    $text = preg_replace(
        '~(https?://[^\s]+)~',
        '<a href="$1" target="_blank" rel="noopener noreferrer">$1</a>',
        $text
    );

    // Match URLs starting with www or ftp
    $text = preg_replace(
        '~((www|ftp)\.[^\s]+)~',
        '<a href="http://$1" target="_blank" rel="noopener noreferrer">$1</a>',
        $text
    );

    // Match URLs without http(s) or www (e.g., google.com)
    $text = preg_replace(
        '/\b((?:[a-z0-9-]+\.)+[a-z]{2,})(?!\S)/i',
        '<a href="http://$1" target="_blank" rel="noopener noreferrer">$1</a>',
        $text
    );

    return $text;
 }

            if(count($alllogs) > 0){
                foreach($alllogs as $singlelog){
                    echo '<tr>';
                    echo '<td class="column-columnname" style="width:20%">'.esc_html($singlelog['logtitle']).'</td>';

                    $logdescription_with_links = make_links_clickable($singlelog['logdescription']);
                    echo '<td class="column-columnname" style="width:50%">' . wp_kses_post($logdescription_with_links) . '</td>';

                    //echo '<td class="column-columnname" style="width:50%">'.esc_html($singlelog['logdescription']).'</td>';

                    echo '<td class="column-columnname">by '.esc_html($singlelog['createdbyperson']).'</td>';

                    echo '<td class="column-columnname">'.esc_html($singlelog['created_at']).'</td>';

                    echo '<td class="column-columnname"><a href="admin.php?page=wp-easy-crm-addclientlog&action=edit&logid='.esc_html($singlelog['id']).'&id='.esc_html($id).'">'.__( 'edit', 'wp-easy-crm' ).'</a>
                    | <a onclick="return eacr_confirmDeleteLog();" href="admin.php?page=wp-easy-crm-addclientlog&action=delete&logid='.esc_html($singlelog['id']).'&id='.esc_html($id).'">'.__( 'delete', 'wp-easy-crm' ).'</a></td>';
                    echo '</tr>';
                }
            }
            ?>
        
        </tbody>
    </table>
</div>

<!--Activity Tasks section-->
<div style="background-color:cornsilk;padding: 20px; margin-top:2em">
    <a class="button button-secondary" style="float-left" href="admin.php?page=wp-easy-crm-addtask&id=<?php echo esc_html($id)?>"><?php _e( 'add task', 'wp-easy-crm' );?></a>
    <h2><?php _e( 'Recent Tasks', 'wp-easy-crm' );?></h2>
    <table id="tablecreatepo" class="wp-list-table widefat fixed striped table-view-list posts" cellspacing="0" style="padding:20px;padding-left:10px;padding-right:10px">
        <!-- <thead>
        <tr>
            <th class="manage-column column-cb check-column" scope="col" style="width:20%"><b><?php _e( 'Title', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col" style="width:50%"><b><?php _e( 'Description', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'By', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Date', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col"><b></b></th>
        </tr>
        </thead> -->
        <tbody>

            <?php   
            if(count($alltasks) > 0){
                foreach($alltasks as $singletask){
                    echo '<tr>';
                    echo '<td class="column-columnname" style="width:20%">'.esc_html($singletask['tasktitle']).'</td>';
                    
                    // echo '<td class="column-columnname" style="width:40%">'.esc_html($singletask['taskdescription']).'</td>';
                    echo '<td class="column-columnname" style="width:40%">'.wp_kses_post($singletask['taskdescription']).'</td>';


                    if($singletask['completed'] == 0){
                        echo '<td class="column-columnname"><b>'.__( 'open', 'wp-easy-crm' ).'</b></td>';
                    }
                    else{
                        echo '<td class="column-columnname"><b>'.__( 'completed', 'wp-easy-crm' ).'</b></td>';
                    }

                    $user = get_user_by( 'id', $singletask['useridfk'] );
                    echo '<td class="column-columnname">'.$user->display_name.'</td>';

                    echo '<td class="column-columnname">'.esc_html($singletask['end_at']).'</td>';

                    echo '<td class="column-columnname"><a href="admin.php?page=wp-easy-crm-addtask&action=edit&taskid='.esc_html($singletask['id']).'&id='.esc_html($id).'">'.__( 'edit', 'wp-easy-crm' ).'</a>
                    | <a onclick="return eacr_confirmDeleteTask();" href="admin.php?page=wp-easy-crm-addtask&action=delete&taskid='.esc_html($singletask['id']).'&id='.esc_html($id).'">'.__( 'delete', 'wp-easy-crm' ).'</a></td>';
                    
                    echo '</tr>';
                }
            }
            ?>
        
        </tbody>
    </table>
</div>

<!--Quotes / Invoice section-->
<div style="background-color:HoneyDew;padding: 20px; margin-top:2em">
    <a class="button button-secondary" style="float-left" href="admin.php?page=wp-easy-crm-addquote&id=<?php echo esc_html($id)?>"><?php _e( 'add quote / invoice', 'wp-easy-crm' );?></a>
    <h2><?php _e( 'Recent Quotes / Invoices', 'wp-easy-crm' );?></h2>
    <table id="tablecreatepo" class="wp-list-table widefat striped table-view-list posts" cellspacing="0" style="padding:20px;padding-left:10px;padding-right:10px">
        <!-- <thead>
        <tr>
            <th class="manage-column column-cb check-column" scope="col" style="width:20%"><b><?php _e( 'Title', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col" style="width:50%"><b><?php _e( 'Description', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'By', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Date', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col"><b></b></th>
        </tr>
        </thead> -->
        <tbody>
            <?php   
            if(count($allquotes) > 0){
                foreach($allquotes as $singlequote){
                    echo '<tr>';

                    if($singlequote['quotestatus'] == "quote" || $singlequote['quotestatus'] == "cancelled"){
                        //is Quote
                        $lineItems = $wpdb->get_results(
                            $wpdb->prepare(
                                "SELECT * FROM $quoteItemTable WHERE quoteidfk = %d",
                                $singlequote['id']
                            ),
                            ARRAY_A
                        );
                    
                        $lineItemsTooltip = '';
                        if (!empty($lineItems)) {
                            foreach ($lineItems as $item) {
                                $lineItemsTooltip .= esc_html($item['lineitem']) . ' (Qty: ' . esc_html($item['quantity']) . ', Price: ' . esc_html($item['price']) . ', Tax: ' . esc_html($item['tax']) . '%)<br>';
                            }
                        } else {
                            $lineItemsTooltip = 'No line items available';
                        }
                        
                        echo '<td class="column-columnname" style="width:20%">'.'<a href="admin.php?page=wp-easy-crm-quoteview&quoteid='.$singlequote['id'].'" data-tooltip="'.esc_attr($lineItemsTooltip).'">QUO-00'.esc_html($singlequote['id']).'</a></td>';
                    } else {
                        // Is invoice
                        $lineItems = $wpdb->get_results(
                            $wpdb->prepare(
                                "SELECT * FROM $quoteItemTable WHERE quoteidfk = %d",
                                $singlequote['id']
                            ),
                            ARRAY_A
                        );
                    
                        $lineItemsTooltip = '';
                        if (!empty($lineItems)) {
                            foreach ($lineItems as $item) {
                                $lineItemsTooltip .= esc_html($item['lineitem']) . ' (Qty: ' . esc_html($item['quantity']) . ', Price: ' . esc_html($item['price']) . ', Tax: ' . esc_html($item['tax']) . '%)<br>';
                            }
                        } else {
                            $lineItemsTooltip = 'No line items available';
                        }
                    
                        echo '<td class="column-columnname" style="width:20%">'.'<a href="admin.php?page=wp-easy-crm-quoteview&quoteid='.$singlequote['id'].'" data-tooltip="'.esc_attr($lineItemsTooltip).'">INV-00'.esc_html($singlequote['id']).'</a></td>';
                    }                    
                    
                    

                    if($singlequote['quotestatus'] == "quote"){
                        echo '<td class="column-columnname" style="width:40%">'.__( "Quote", 'wp-easy-crm').'</td>';
                    }
                    else if($singlequote['quotestatus'] == "invoice"){
                        echo '<td class="column-columnname" style="width:40%">'.__( "Invoice", 'wp-easy-crm').'</td>';
                    }
                    else if($singlequote['quotestatus'] == "paid"){
                        echo '<td class="column-columnname" style="width:40%">'.__( "Paid", 'wp-easy-crm').'</td>';
                    }
                    else if($singlequote['quotestatus'] == "cancelled"){
                        echo '<td class="column-columnname" style="width:40%">'.__( "Cancelled / not accepted", 'wp-easy-crm').'</td>';
                    }

                    echo '<td class="column-columnname"><b>'.esc_html($singlequote['total'].' '.$singlequote['currency']).'</b></td>';

                    echo '<td class="column-columnname">'.esc_html($singlequote['lastupdate_at']).'</td>';

                    //show mark paid button for easy access
                    if(esc_html($singlequote['quotestatus']) == "paid" || esc_html($singlequote['quotestatus']) == "cancelled"){
                        echo '<td class="column-columnname editcolumn">
                        <a href="admin.php?page=wp-easy-crm-addquote&action=edit&quoteid='.esc_html($singlequote['id']).'&id='.esc_html($id).'">'.__( 'edit', 'wp-easy-crm' ).'</a>
                        | <a onclick="return eacr_confirmDeleteQuote();" href="admin.php?page=wp-easy-crm-addquote&action=delete&quoteid='.esc_html($singlequote['id']).'&id='.esc_html($id).'">'.__( 'delete', 'wp-easy-crm' ).'</a></td>';
                    }
                    else{
                        echo '<td class="column-columnname editcolumn">
                        <a onclick="return eacr_confirmMarkPaidQuote();" href="admin.php?page=wp-easy-crm-addquote&action=markpaid&quoteid='.esc_html($singlequote['id']).'&id='.esc_html($id).'">'.__( 'mark paid', 'wp-easy-crm' ).'</a>
                        | <a href="admin.php?page=wp-easy-crm-addquote&action=edit&quoteid='.esc_html($singlequote['id']).'&id='.esc_html($id).'">'.__( 'edit', 'wp-easy-crm' ).'</a>
                        | <a onclick="return eacr_confirmDeleteQuote();" href="admin.php?page=wp-easy-crm-addquote&action=delete&quoteid='.esc_html($singlequote['id']).'&id='.esc_html($id).'">'.__( 'delete', 'wp-easy-crm' ).'</a></td>';
                    }
                    
                    
                    echo '</tr>';
                }
            }
            ?>
        
        </tbody>
    </table>
</div>
<script>
document.addEventListener('DOMContentLoaded', (event) => {
    document.querySelectorAll('table').forEach(function(table) {
        let rows = table.querySelectorAll('tr');
        if (rows.length > 8) {
            // Hide rows beyond the first 8
            rows.forEach((row, index) => {
                if (index >= 8) {
                    row.style.display = 'none';
                }
            });

            // Create the "Show More" button
            let showMoreBtn = document.createElement('button');
            showMoreBtn.innerText = 'Show More';
            showMoreBtn.classList.add('show-more-btn');

            // Style the button
            showMoreBtn.style.backgroundColor = '#4CAF50'; // Green background
            showMoreBtn.style.color = 'white'; // White text
            showMoreBtn.style.border = 'none';
            showMoreBtn.style.padding = '10px 10px';
            showMoreBtn.style.marginTop = '10px';
            showMoreBtn.style.cursor = 'pointer';
            showMoreBtn.style.borderRadius = '10px';
            showMoreBtn.style.display = 'block'; // Set display to block
            showMoreBtn.style.marginLeft = 'auto'; // Align to center horizontally
            showMoreBtn.style.marginRight = 'auto'; // Align to center horizontally
            showMoreBtn.style.minWidth = '200px'; // Minimum width of 200px
            //showMoreBtn.style.fontWeight = 'bold'; // Make font bold


            // Append the button after the table
            table.parentNode.insertBefore(showMoreBtn, table.nextSibling);

            // Add event listener to the button
            showMoreBtn.addEventListener('click', function() {
                // Show all hidden rows
                rows.forEach((row) => {
                    row.style.display = 'table-row';
                });

                // Optionally, hide the button
                this.style.display = 'none';
            });
        }
    });

    // Function to add wiggle effect
    function addWiggleEffect() {
        document.querySelectorAll('.show-more-btn').forEach(function(showMoreBtn) {
            var elementPosition = showMoreBtn.getBoundingClientRect().top;
            var screenPosition = window.innerHeight;
            if (elementPosition < screenPosition && elementPosition > 0) {
                showMoreBtn.classList.add('wiggle');
            } else {
                showMoreBtn.classList.remove('wiggle');
            }
        });
    }

    // Apply wiggle effect to elements in view on load
    addWiggleEffect();

    // Apply wiggle effect to elements when scrolling
    window.addEventListener('scroll', addWiggleEffect);
});
</script>

<?php 

//this section echos all the hashtag section in the client profile menu
function echoTagsStuff($clientdata) {
    global $wpdb;
    $tagTable = $wpdb->prefix . 'easytags';
    $clientTable = $wpdb->prefix . 'clients';
    $clientId = isset($clientdata['id']) ? $clientdata['id'] : 0;
    $currentTagsIds = !empty($clientdata['tagsrelations']) ? explode(',', $clientdata['tagsrelations']) : [];

    // Handle tag deletion
    if (isset($_POST['delete_tag'], $_POST['tag_id'], $_POST['_wpnonce_delete_tag']) && wp_verify_nonce($_POST['_wpnonce_delete_tag'], 'delete_tag_action')) {
        $tagIdToDelete = intval($_POST['tag_id']);
        if (($key = array_search($tagIdToDelete, $currentTagsIds)) !== false) {
            unset($currentTagsIds[$key]);
            $newTagsIds = implode(',', $currentTagsIds);
            $wpdb->update($clientTable, ['tagsrelations' => $newTagsIds], ['id' => $clientId]);
            echo "<script>window.location.reload();</script>";
        }
    }

    // Handle adding a new tag
    if (isset($_POST['add_tag'], $_POST['new_tag_id'], $_POST['_wpnonce_add_new_tag']) && wp_verify_nonce($_POST['_wpnonce_add_new_tag'], 'add_new_tag_action')) {
        $newTagId = intval($_POST['new_tag_id']);
        if (!in_array($newTagId, $currentTagsIds)) {
            $currentTagsIds[] = $newTagId;
            $newTagsIds = implode(',', $currentTagsIds);
            $wpdb->update($clientTable, ['tagsrelations' => $newTagsIds], ['id' => $clientId]);
            echo "<script>window.location.reload();</script>";
        }
    }

    // Fetch and display current tags
    if (!empty($currentTagsIds)) {
        $placeholders = implode(',', array_fill(0, count($currentTagsIds), '%d'));
        $tags = $wpdb->get_results($wpdb->prepare("SELECT id, tagtitle, tagcolor FROM $tagTable WHERE id IN ($placeholders)", $currentTagsIds));
        foreach ($tags as $tag) {
            echo '<div class="tag-bubble" style="background-color:' . esc_attr($tag->tagcolor) . '; color: #ffffff;">';
            echo '#' . esc_html($tag->tagtitle);
            echo '<form method="post" action="" style="display:inline;"><input type="hidden" name="tag_id" value="' . esc_attr($tag->id) . '"><input type="hidden" name="delete_tag" value="1"><input type="hidden" name="_wpnonce_delete_tag" value="' . wp_create_nonce('delete_tag_action') . '"><button type="submit" class="tag-delete">X</button></form>';
            echo '</div>';
        }
    }
 
    // Collapsible Add Tag Form
    echo '<div id="addTagButton" style="cursor: pointer; display: inline-block; margin-left: 20px; font-size: 24px;">+</div>';
    echo '<div id="addTagForm" style="display: none; margin-top: 10px;">';
    echo '<form method="post" action="">';

    
    $allTags = $wpdb->get_results("SELECT id, tagtitle FROM $tagTable");

    if (empty($allTags)) {
        // If there are no tags available, inform the user to create some.
        echo '<p>No tags have been created yet. You can create some in the <a href="admin.php?page=wp-easy-crm-tags">Tags Menu</a>.</p>';

    } else {
        // If there are tags, display them as options in the dropdown.
        echo '<select name="new_tag_id"><option value="">Select a tag to add</option>';

        foreach ($allTags as $tag) {
            if (!in_array($tag->id, $currentTagsIds)) {
                echo '<option value="' . esc_attr($tag->id) . '">' . esc_html($tag->tagtitle) . '</option>';
            }
        }

        echo '</select>';
        echo '<input type="hidden" name="add_tag" value="1">';
        echo wp_nonce_field('add_new_tag_action', '_wpnonce_add_new_tag', true, false);
        echo '<input type="submit" value="Add Tag">';

    }

 


    
    echo '</form></div>';

    // JavaScript for collapsible form
    echo '<script>
    jQuery(document).ready(function($) {
        $("#addTagButton").click(function() {
            $("#addTagForm").slideToggle();
        });
    });
    </script>';

    // Styling
echo '<style>
.tag-bubble {
    display: inline-block;
    border-radius: 15px;
    padding: 5px 10px;
    margin: 5px;
    cursor: pointer;
    font-weight: bold;
    background-color: #ffffff;
    color: #ffffff;
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    letter-spacing: 2px; /* Increased letter spacing */
}
.tag-delete {
    margin-left: 8px;
    color: #000000;
    cursor: pointer;
}
#addTagButton {
    background-color: #f0f0f0;
    padding: 8px; /* Reduced padding for a smaller circle */
    border-radius: 50%;
    width: 24px; /* Reduced width for a smaller circle */
    height: 24px; /* Reduced height for a smaller circle */
    text-align: center;
    line-height: 8px; /* Adjust line height for vertical alignment */
    box-shadow: 0 2px 4px rgba(0,0,0,0.2);
    cursor: pointer;
}
#addTagForm select, #addTagForm input[type="submit"] {
    margin-top: 5px;
}
</style>';
}








?>

<!-- Hook for Projects and other modules to be added -->
<?php
//Hook to add projects and other modules to bottom of client profile
do_action( 'eacr_project_content_hook', $id );
?>
  





