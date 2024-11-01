<?php 
global $wpdb;
$quotesInvoiceTable = $wpdb->prefix . 'quoteclient';
$quoteItemTable = $wpdb->prefix . 'lineitemquote';
$clientTable = $wpdb->prefix . 'clients';
$accregionTable = $wpdb->prefix . 'eacraccountingregion';

$allquotes = array(); // Initialize $allquotes as an empty array
$regionNamesString = 'Filter by Accounting Region:'; // Initialize with default text
$addedRegionNames = array(); // Initialize an array to keep track of added region names

// get current user id and which regions they have access to
$user_id = get_current_user_id(); // The ID of the user
$regions = $wpdb->get_results("SELECT id, regionname FROM $accregionTable");

$selected_regions = [];
foreach ($regions as $region) {
    if (get_user_meta($user_id, 'region_' . $region->id, true)) {
        $selected_regions[] = $region->id;
    }
}

// Check if user has general access or is admin
$contains_id_1 = in_array(1, $selected_regions);

if ($contains_id_1 == true || current_user_can('manage_options') == true ) {
    // The selected_regions array contains the ID 1 - General access
    // The current user is an administrator
    $allquotes = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * from $quotesInvoiceTable ORDER BY id",""
        ),ARRAY_A
    );

    // Trim the trailing space
    $regionNamesString = trim($regionNamesString);
} else {
    // The selected_regions array does not contain the ID 1, now check if you're editor
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

            // Initialize an array to store all tasks
            $allquotes = array();

            // Iterate over each client and load tasks
            foreach ($allclients as $client) {
                $client_id = $client['id']; // Assuming 'id' is the field name in $clientTable

                $client_quotes = $wpdb->get_results(
                    $wpdb->prepare(
                        "SELECT * FROM $quotesInvoiceTable WHERE clientidfk = %d ORDER BY created_at DESC",
                        $client_id
                    ),
                    ARRAY_A
                );

                // Merge the tasks of this client into the overall tasks array
                $allquotes = array_merge($allquotes, $client_quotes);

                // Assuming $selected_regions is an array of ids
                foreach ($selected_regions as $selected_id) {
                    foreach ($regions as $region) {
                        if ($region->id == $selected_id && !in_array($region->regionname, $addedRegionNames)) {
                            $regionNamesString .= $region->regionname . ' ';
                            $addedRegionNames[] = $region->regionname; // Add the region name to the tracking array
                        }
                    }
                }

                // Trim the trailing space from the string
                $regionNamesString = trim($regionNamesString);
            }
        } else {
            // Handle the case where there are no selected regions
            $allquotes = array();
        }
    }
}

if(count($allquotes) > 0){
?>
<style>
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
jQuery(document).ready(function ($) {
    var table = $('#tableinvoices').DataTable({
        responsive: {
            details: {
                type: 'column',
                target: 1 // Target the second column (index starts at 0)
            }
        },
        order: [[4, 'desc']],
        "iDisplayLength": 12,
    });

     // Function to filter the table by region
     function filterTableByRegion(regionName, regionId) {
        if (regionId == 1) {
            // Clear the search filter to show all entries
            table.columns(5).search('').draw();
        } else {
            table.columns(5).search(regionName).draw();
        }
    }

    // Add click event listeners to region filter buttons
    $('.region-filter-button').on('click', function () {
        var regionName = $(this).text();
        var regionId = $(this).data('region-id');
        filterTableByRegion(regionName, regionId);
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
    
<p><?php echo wp_kses_post(@$msg); ?></p>
<!-- <a class="button button-primary" href="admin.php?page=wp-easy-crm-addquote"><?php echo __('Add Quote', 'wp-easy-crm')?></a> -->

<div>
    <?php _e( 'Access: ', 'wp-easy-crm' ); echo $regionNamesString ?>
</div>

<div>
    <?php 
    // Add region filter buttons if $contains_id_1 is true
    if ($contains_id_1) {
        foreach ($regions as $region) {
            echo '<button class="region-filter-button" data-region-id="' . $region->id . '">' . esc_html($region->regionname) . '</button> ';
        }
    }
    ?>
</div>

<h1><?php _e( 'Quotes / Invoices', 'wp-easy-crm' );?></h1>
<table id="tableinvoices" class="wp-list-table widefat fixed striped table-view-list posts" cellspacing="0" style="padding:20px;padding-left:10px;padding-right:10px">
    <thead>
    <tr>
        <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Invoice / Quote', 'wp-easy-crm' );?></b></th>
        <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Status', 'wp-easy-crm' );?></b></th>
        <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Client', 'wp-easy-crm' );?></b></th>
        <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Total', 'wp-easy-crm' );?></b></th>
        <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Date', 'wp-easy-crm' );?></b></th>
        <th class="manage-column column-cb check-column" scope="col"><b><?php _e( 'Region', 'wp-easy-crm' );?></b></th> <!-- Column for region name -->
    </tr>
    </thead>
    <tbody>
        <?php   
        if(count($allquotes) > 0){
            foreach($allquotes as $singlequote){
                if($singlequote["clientidfk"] != null and trim($singlequote["clientidfk"]) != "" ){
                    $clientname = $wpdb->get_row(
                        $wpdb->prepare(
                            "SELECT * from $clientTable WHERE id = %d",$singlequote["clientidfk"]
                        ),ARRAY_A
                    );

                    $client_region_id = $clientname['idaccountingregionfk']; // Get the region ID of the client
                    $client_region_name = $wpdb->get_var(
                        $wpdb->prepare(
                            "SELECT regionname FROM $accregionTable WHERE id = %d", $client_region_id
                        )
                    ); // Get the region name of the client

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
                        echo '<td class="column-columnname">'.__( "Quote", 'wp-easy-crm').'</td>';
                    } else if($singlequote['quotestatus'] == "invoice"){
                        echo '<td class="column-columnname">'.__( "Invoice", 'wp-easy-crm').'</td>';
                    } else if($singlequote['quotestatus'] == "paid"){
                        echo '<td class="column-columnname">'.__( "Paid", 'wp-easy-crm').'</td>';
                    } else if($singlequote['quotestatus'] == "cancelled"){
                        echo '<td class="column-columnname">'.__( "Cancelled / not accepted", 'wp-easy-crm').'</td>';
                    }                   

                    echo '<td class="column-columnname">'.'<a href="admin.php?page=wp-easy-crm-perfil&id='.$singlequote['clientidfk'].'">'.esc_html($clientname['clientname']).'</a>'.'</td>';

                    echo '<td class="column-columnname"><b>'.esc_html($singlequote['total'].' '.$singlequote['currency']).'</b></td>';

                    echo '<td class="column-columnname">'.esc_html($singlequote['lastupdate_at']).'</td>';

                    echo '<td class="column-columnname">'.esc_html($client_region_name).'</td>'; // Column for region name

                    echo '</tr>';
                }
            }
        }
        ?>
    </tbody>
</table>

<?php
} else {
    echo '<p>'.__( 'This is where a list of Quotations and Invoices will appear, once there are some added to the system.', 'wp-easy-crm' ).'</p>';
}
?>
