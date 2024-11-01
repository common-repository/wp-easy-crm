<?php 

global $wpdb;
$registration_date = date("Y-m-d H:i:s", time());
$quoteTable = $wpdb->prefix . 'quoteclient';
$quoteItemTable = $wpdb->prefix . 'lineitemquote';

$clientTable = $wpdb->prefix . 'clients';
$msg = '';

$action = sanitize_text_field(isset($_GET['action']) ? trim($_GET['action']) : "");
$id = sanitize_text_field(isset($_GET['id']) ? intval($_GET['id']) : "");
$quoteid = sanitize_text_field(isset($_GET['quoteid']) ? intval($_GET['quoteid']) : "");

$row_details_client = $wpdb->get_row(
    $wpdb->prepare(
        "SELECT * from $clientTable WHERE id = %d", $id
    ), ARRAY_A
);


if (!empty($action)) {
 
    if ($action == "delete") {

        //delete all line items
        $count = $wpdb->delete($quoteItemTable, array( 'quoteidfk' => $quoteid ));
        if ($count >= 1) {

            //delete quote
            $count = $wpdb->delete($quoteTable, array( 'id' => $quoteid ));
            if ($count == 1) {
                $msg = "<div class='updated update'>".esc_html(__('quote / invoice successfully deleted. You can return to the client profile or wait until you are redirected to the client profile.', 'wp-easy-crm'))."</div>";

                        // Redirect to the specified URL
                        // Wait for 2 seconds and then redirect
                           echo "<script>
                           setTimeout(function() {
                               window.location.href = '".admin_url("admin.php?page=wp-easy-crm-perfil&id=$id")."';
                           }, 2000);
                           </script>";

            }
            else{
                $msg = "<div style='color:red'>".esc_html(__('error - quote / invoice couldn\'t be deleted', 'wp-easy-crm' ))."</div>";

            }

        }
        else{
            $msg = "<div style='color:red'>".esc_html(__('error - some line items couldn\'t be deleted', 'wp-easy-crm' ))."</div>";

        }

    }
    if ($action == "edit") {

           //load data to edit
            $row_details_Quote = $wpdb->get_row(
                $wpdb->prepare(
                    "SELECT * from $quoteTable WHERE id = %d", $quoteid
                ), ARRAY_A
            );

            //load line items
            $row_details_LineItems = $wpdb->get_results(
                $wpdb->prepare(
                    "SELECT * from $quoteItemTable WHERE quoteidfk = %d", $quoteid
                ), ARRAY_A
            );

            



    }
    if ($action == "markpaid") {
        $updated = $wpdb->update(
            $quoteTable,
            array(
                'quotestatus' => 'paid',
                'lastupdate_at' => sanitize_text_field($registration_date)
            ),
            array('id' => $quoteid),
            array(
                '%s', // Format for quotestatus
                '%s'  // Format for created_at
            ),
            array('%d') // Format for where clause (id)
        );
    
        // Debugging: Log the SQL query
        error_log($wpdb->last_query);
    
        if ($updated !== false) {
            $msg = "<div class='updated update'>".esc_html(__('quote / invoice successfully marked as paid. You can return to the client profile or wait until you are redirected to the client profile.', 'wp-easy-crm'))."</div>";
    
            echo "<script>
                setTimeout(function() {
                    window.location.href = '".admin_url("admin.php?page=wp-easy-crm-perfil&id=$id")."';
                }, 2000);
            </script>";
        } else {
            $msg = "<div style='color:red'>".esc_html(__('error - quote / invoice couldn\'t be marked as paid', 'wp-easy-crm'))."</div>";
        }
    }
    
    
        
}

if (isset($_POST['submit'])) {

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

    $id = sanitize_text_field($_POST['clientidfk']);

    //calculate total including tax and each line-item
    $itemindex = 0;
    $total = 0;
    foreach ((array) $_POST['lineitem'] as $singleitem){
        $singleitem = trim($singleitem);
        if($singleitem != ""){
            if(trim(sanitize_text_field($_POST['price'][$itemindex])) != "" &&  trim(sanitize_text_field($_POST['tax'][$itemindex])) != "" && trim(sanitize_text_field($_POST['quantity'][$itemindex])) != ""){
                $itemtotal = (sanitize_text_field($_POST['price'][$itemindex]) * sanitize_text_field($_POST['tax'][$itemindex]) / 100 + sanitize_text_field($_POST['price'][$itemindex])) * sanitize_text_field($_POST['quantity'][$itemindex]);
                $total = $total + $itemtotal;
                
            }
        }
        $itemindex = $itemindex + 1;
    }
    $total = round($total, 2);
    $total = number_format($total, 2);
 
    if(!empty($action)){
        
        //save edited / updated quote quote     
        
        $htmlfooter = wpautop($_POST['footer_html']);

        $wpdb->update($quoteTable, array(
            "clientidfk" => stripslashes(sanitize_text_field($_POST['clientidfk'])),
            "quotestatus" => stripslashes(sanitize_text_field($_POST['quotestatus'])),
            "currency" => stripslashes(sanitize_text_field($_POST['currency'])),
            "clientinfo" => stripslashes(wp_kses_post($_POST['clientinfo'])),
            "header" => stripslashes(wp_kses_post($_POST['header_html'])),
            "total" => stripslashes(sanitize_text_field(@$_POST['total'])),
            "footer" => stripslashes(wp_kses($htmlfooter, $allowed_tags)),
            "total" => sanitize_text_field($total),
            "lastupdate_at" => stripslashes(sanitize_text_field($registration_date)),
            "created_at" => stripslashes(sanitize_text_field($_POST['created_at']))
        ), array(
            "id" => $quoteid
        ));

//         //delete existing quote items
//         $wpdb->delete($quoteItemTable, array( 'quoteidfk' => $quoteid ));


//         $itemindex = 0;
//         $lineItems = (array) $_POST['lineitem'];
// var_dump (array_reverse($lineItems));
//             foreach (array_reverse($lineItems) as $singleitem){
//                 $singleitem = trim($singleitem);
//                 if($singleitem != ""){
//                     if(trim($_POST['price'][$itemindex]) != "" &&  trim($_POST['tax'][$itemindex]) != "" && trim($_POST['quantity'][$itemindex]) != ""){
//                         // $itemtotal = ($_POST['price'][$itemindex] * $_POST['tax'][$itemindex] / 100 + $_POST['price'][$itemindex]) * $_POST['quantity'][$itemindex];
//                         // $total = $total + $itemtotal;
//                         // $itemindex = $itemindex + 1;

//                         //add lineitem
//                         $wpdb->insert($quoteItemTable,array(
//                             "quoteidfk"=>sanitize_text_field($quoteid),
//                             "lineitem"=>sanitize_text_field($singleitem),
//                             "quantity"=>sanitize_text_field(trim($_POST['quantity'][$itemindex])),
//                             "price"=>sanitize_text_field(trim($_POST['price'][$itemindex])),
//                             "tax"=>sanitize_text_field(trim($_POST['tax'][$itemindex])),
//                             "created_at"=>sanitize_text_field($registration_date)
//                         ));
//                         if($wpdb->insert_id > 0){
//                              $msg = "<div class='updated update'>".esc_html(__( 'Quote / Invoice edit successfully saved. You can return to the client profile.', 'wp-easy-crm' ))."</div>";
//                         }
//                         else{
//                             $msg = "<div style='color:red'>".esc_html(__( 'error - Some line items for Quote / Invoice couldn\'t be stored. Please review that you have entered all data correctly.', 'wp-easy-crm' ))."</div>";
//                             echo $wpdb->last_error;
//                             break;
//                         }

//                     }
//                 }
//                 $itemindex = $itemindex + 1;
//             }          

// Retrieve existing line items for the quote
$existingLineItems = $wpdb->get_results(
    $wpdb->prepare(
        "SELECT * FROM $quoteItemTable WHERE quoteidfk = %d",
        $quoteid
    ),
    ARRAY_A
);

// Create an associative array for easier matching
$existingLineItemsMap = [];
foreach ($existingLineItems as $existingItem) {
    $existingLineItemsMap[$existingItem['lineitem']] = $existingItem;
}

// Initialize array to track processed line items
$processedLineItems = [];

$newLineItems = (array) $_POST['lineitem'];
$itemindex = 0;

foreach ($newLineItems as $singleitem) {
    $singleitem = trim($singleitem);

    // Add to processed line items
    $processedLineItems[$singleitem] = true;

    // Check if the line item text is not empty
    if (!empty($singleitem)) {
        if (
            trim($_POST['price'][$itemindex]) != "" &&
            trim($_POST['tax'][$itemindex]) != "" &&
            trim($_POST['quantity'][$itemindex]) != ""
        ) {
            if (isset($existingLineItemsMap[$singleitem])) {
                // Update the existing line item with new data
                $existingItem = $existingLineItemsMap[$singleitem];
                $wpdb->update(
                    $quoteItemTable,
                    array(
                        "quantity" => sanitize_text_field(trim($_POST['quantity'][$itemindex])),
                        "price" => sanitize_text_field(trim($_POST['price'][$itemindex])),
                        "tax" => sanitize_text_field(trim($_POST['tax'][$itemindex])),
                        "created_at" => sanitize_text_field($registration_date)
                    ),
                    array("id" => $existingItem["id"])
                );
            } else {
                // Insert the new line item as a new entry
                $wpdb->insert(
                    $quoteItemTable,
                    array(
                        "quoteidfk" => sanitize_text_field($quoteid),
                        "lineitem" => sanitize_text_field($singleitem),
                        "quantity" => sanitize_text_field(trim($_POST['quantity'][$itemindex])),
                        "price" => sanitize_text_field(trim($_POST['price'][$itemindex])),
                        "tax" => sanitize_text_field(trim($_POST['tax'][$itemindex])),
                        "created_at" => sanitize_text_field($registration_date)
                    )
                );
            }
        }
    }
    $itemindex++;
}

// Delete line items that were not in the POST data
foreach ($existingLineItemsMap as $existingItem) {
    if (!isset($processedLineItems[$existingItem['lineitem']])) {
        $wpdb->delete(
            $quoteItemTable,
            array("id" => $existingItem["id"])
        );
    }
}

$msg = "<div class='updated update'>" . esc_html(__('Quote / Invoice edit successfully saved. You can return to the client profile.', 'wp-easy-crm')) . "</div>";




             // Redirect to the specified URL
           // Wait for 2 seconds and then redirect
        //    echo "<script>
        //    setTimeout(function() {
        //        window.location.href = '".admin_url("admin.php?page=wp-easy-crm-quoteview&quoteid=$quoteid")."';
        //    }, 3000);
        //    </script>";
    }
    else{
        // var_dump($_POST);
        // echo('<div>');
        // echo($_POST['header_html']);
        // echo('</div><br/><br/>');
        // echo('<div>');
        // echo(wp_kses_post($_POST['header_html']));
        // echo('</div><br/><br/>');
        // echo('<div>');
        // echo wp_kses($_POST['header_html'], $allowed_tags);
        // echo('</div><br/><br/>');



        //add new quote

        $htmlfooter = wpautop($_POST['footer_html']);

        $wpdb->insert($quoteTable,array(
            "clientidfk"=>sanitize_text_field($_POST['clientidfk']),
            "quotestatus"=>sanitize_text_field($_POST['quotestatus']),
            "currency"=>sanitize_text_field($_POST['currency']),
            "clientinfo"=>wp_kses_post($_POST['clientinfo']),
            "header"=>wp_kses_post($_POST['header_html']),
            "total"=>sanitize_text_field(@$_POST['total']),
            "footer"=>wp_kses( $htmlfooter, $allowed_tags ),
            "total"=>sanitize_text_field($total),
            "lastupdate_at"=>sanitize_text_field($registration_date),
            "created_at"=>sanitize_text_field($registration_date)
        ));
        if($wpdb->insert_id > 0){
            $msg = "<div class='updated update'>".esc_html(__( 'Quote successfully saved. You can return to the client profile or you will be redirected to the invoice view in 2 seconds.', 'wp-easy-crm' ))."</div>";
            $newQuoteid = $wpdb->insert_id;
            //Quote successfully added, now create line items in DB
            $itemindex = 0;
            foreach ((array) $_POST['lineitem'] as $singleitem){
                $singleitem = trim($singleitem);
                if($singleitem != ""){
                    if(trim($_POST['price'][$itemindex]) != "" &&  trim($_POST['tax'][$itemindex]) != "" && trim($_POST['quantity'][$itemindex]) != ""){
                        // $itemtotal = ($_POST['price'][$itemindex] * $_POST['tax'][$itemindex] / 100 + $_POST['price'][$itemindex]) * $_POST['quantity'][$itemindex];
                        // $total = $total + $itemtotal;
                        // $itemindex = $itemindex + 1;

                        //add lineitem
                        $wpdb->insert($quoteItemTable,array(
                            "quoteidfk" => sanitize_text_field($newQuoteid),
                            "lineitem" => stripslashes(sanitize_text_field($singleitem)),
                            "quantity" => stripslashes(sanitize_text_field(trim($_POST['quantity'][$itemindex]))),
                            "price" => stripslashes(sanitize_text_field(trim($_POST['price'][$itemindex]))),
                            "tax" => stripslashes(sanitize_text_field(trim($_POST['tax'][$itemindex]))),
                            "created_at" => sanitize_text_field($registration_date)
                        ));
                        if($wpdb->insert_id > 0){
                            // $msg = "<div class='updated update'>".esc_html(__( 'Quote successfully saved. You can return to the client profile.', 'wp-easy-crm' ))."</div>";
                        }
                        else{
                            $msg = "<div style='color:red'>".esc_html(__( 'error - Some line items for Quote /Invoice couldn\'t be stored. Please review that you have entered all data correctly.', 'wp-easy-crm' ))."</div>";
                            // echo $wpdb->last_error;
                            break;
                        }

                    }
                }
                $itemindex = $itemindex + 1;
            }

            // Redirect to the specified URL
           // Wait for 2 seconds and then redirect
            echo "<script>
            setTimeout(function() {
                window.location.href = '".admin_url("admin.php?page=wp-easy-crm-quoteview&quoteid=$newQuoteid")."';
            }, 2000);
            </script>";
    
        }else{
             
            $msg = "<div style='color:red'>".esc_html(__( 'error - Quote information couldn\'t be stored', 'wp-easy-crm' ))."</div>";
            echo $wpdb->last_error;

        }

    }
  
     //load data again
     $row_details_Quote = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * from $quoteTable WHERE id = %d", $quoteid
        ), ARRAY_A
    );

    //load line items
    $row_details_LineItems = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * from $quoteItemTable WHERE quoteidfk = %d", $quoteid
        ), ARRAY_A
    );

    //load client data again
    $row_details_client = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * from $clientTable WHERE id = %d", $id
        ), ARRAY_A
    );
    

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

<script type="text/javascript">

    let lastitemelement = document.querySelectorAll(".lineitemdiv");

    jQuery(document).ready(function() {
    
        lastitemelement = document.querySelectorAll(".lineitemdiv");
        lastitemelement = lastitemelement[lastitemelement.length -1];

    });

    function eacr_addLineItem(){
            // alert("add line");
            let destination = document.getElementById("line-items");
            jQuery(lastitemelement).clone().appendTo(destination);
        }

    function eacr_removeLineItem(clickedElement){

        clickedElement.parentNode.parentNode.removeChild(clickedElement.parentNode);
    }


</script>

<p><?php echo wp_kses_post($msg); ?></p>
<a class="button button-primary" href="admin.php?page=wp-easy-crm"><?php echo __('List of all Clients', 'wp-easy-crm')?></a><span> </span>

<?php echo '<a class="button button-primary" href="admin.php?page=wp-easy-crm-perfil&id='.$id.'">'.__('Client Profile', 'wp-easy-crm').'</a><span> </span>'; ?>

<h1><?php _e( 'Add / Edit Quotation / Invoice || ', 'wp-easy-crm' );?><?php echo esc_html(@$row_details_client['clientname']) ?></h1>
<div class="wrap">
        <form action="<?php echo esc_html($_SERVER['PHP_SELF']) ?>?page=wp-easy-crm-addquote<?php 
if(!empty($action)){
     echo '&action=edit&quoteid='.esc_html($quoteid). '&clientid='.esc_html($id) ; 
     } ?>" method="post">
            <input type="text" name="clientidfk" hidden id="clientidfk" required value="<?php echo $id?>">
            <input type="datetime-local" hidden id="lastupdate_at" name="lastupdate_at" value="<?php echo esc_html(@$row_details_Quote['lastupdate_at']) ?>">
            <input type="datetime-local" hidden id="created_at" name="created_at" value="<?php echo esc_html(@$row_details_Quote['created_at']) ?>">                 
            
            <label for="header_html"><?php echo __('Quote / Invoice Header', 'wp-easy-crm')?></label></br>
            <div style="background-color:HoneyDew;padding: 20px; margin-top:1em">
                <?php 
                if ($action == "edit") {  
                    echo(str_replace('"', "'", $row_details_Quote['header']).'<input name="header_html" hidden value="'.htmlspecialchars($row_details_Quote['header'], ENT_QUOTES, 'UTF-8').'">');
                }
                else{
                    echo(str_replace('"', "'", $options['header_html']).'<input name="header_html" hidden value="'.htmlspecialchars($options['header_html'], ENT_QUOTES, 'UTF-8').'">');
                }
                ?>
            </div>
            </br>
            <label for="clientinfo"><?php echo __('Client Information', 'wp-easy-crm')?></label></br>
            <?php  $clienthtml = '<b>'.$row_details_client['clientname'].'</b></br>Contact: '.$row_details_client['nombrecontacto'].'</br>Phone(s): '.$row_details_client['telefono'].'</br>'.$row_details_client['direccion'].'</br>'.$row_details_client['provincia'];
              
                if ($action == "edit") {
                    echo('<div style="background-color:HoneyDew;padding: 20px; margin-top:1em">'.$row_details_Quote['clientinfo'].'</div><input name="clientinfo" hidden value="'.$row_details_Quote['clientinfo'].'"></b></br>');
                }
                else{
                    echo('<div style="background-color:HoneyDew;padding: 20px; margin-top:1em">'.$clienthtml.'</div><input name="clientinfo" hidden value="'.$clienthtml.'"></b></br>');
                }
                
            ?>

            <div style="background-color:HoneyDew;padding: 20px; margin-top:1em">
                <label><?php _e( 'Choose current Quote / Invoice status*', 'wp-easy-crm' );?></label><br>
                <select name="quotestatus" required>
                    <option value="<?php echo esc_html(isset($row_details_Quote['quotestatus']) ? $row_details_Quote['quotestatus'] : ""); ?>"><?php echo esc_html(isset($row_details_Quote['quotestatus']) ? $row_details_Quote['quotestatus'] : _e( "choose status", 'wp-easy-crm' )); ?></option>
                    <option value="quote">"<?php _e( "Quote", 'wp-easy-crm');?>"</option>
                    <option value="invoice">"<?php _e( 'Invoice', 'wp-easy-crm');?>"</option>
                    <option value="paid">"<?php _e( 'Paid', 'wp-easy-crm');?>"</option>
                    <option value="cancelled">"<?php _e( 'Cancelled / not accepted', 'wp-easy-crm');?>"</option>
                </select></br></br>

                <label for="currency"><?php echo __('Currency*', 'wp-easy-crm')?></label></br>
                <select name="currency">
                                    <?php
                                        if ($action == "edit") {
                                            echo('<option value="'.esc_attr($row_details_Quote['currency']).selected($row_details_Quote['currency'], $currency).'">'.esc_html($row_details_Quote['currency']).'</option>');
                                            foreach ( $currency_options as $currency => $label ) {
                                                echo('<option value="'.esc_attr($currency).'">'.esc_html($label).'</option>');
                                            }
                                        }
                                        else{
                                            foreach ( $currency_options as $currency => $label ) {
                                                echo('<option value="'.esc_attr($currency).'"'.selected($options['currency'], $currency).'>'.esc_html($label).'</option>');
                                            }
                                        }
                                    ?>
                                 
                                    
                </select><br><br>
                <!-- Product / line item section -->
                <label style="color: red;">Tip: If you want to delete a line item, simply erase its description and save the invoice.</label></br>
                <label for="line-items"><?php echo __('Line items [Description | Quantity | Price | Tax percentage % (inserted value taken from settings)]', 'wp-easy-crm')?></label></br>
                <div id="line-items" name="line-items">
                <?php 
                //edit existing line items section
                // var_dump($row_details_LineItems);

                if (@$row_details_LineItems != null && count($row_details_LineItems) > 0) {
                    $index = 0;
                    foreach ((array) $row_details_LineItems as $singleitem) {

                        echo '<div class="input-group lineitemdiv" style="width:90%;padding-top:5px;">';
                        echo '<input type="text" name="lineitem[]" class="form-control" maxlength="480" style="width:60%" placeholder="'.__('Item / Product description', 'wp-easy-crm').'" value="'.$singleitem['lineitem'].'"/>';
                        echo '<span class="input-group-btn" style="width:0px;"></span>';
                        echo '<input type="number" min="1" step="1" name="quantity[]" class="form-control " style="width:10%" value="'.$singleitem["quantity"].'" placeholder="'.__('Quantity', 'wp-easy-crm').'" x />';
                        echo '<span class="input-group-btn" style="width:0px;"></span>';
                        echo '<input type="number" min="0.01" step="0.01" name="price[]" class="form-control " style="width:10%" value="'.$singleitem["price"].'" placeholder="'.__('Price', 'wp-easy-crm').'" />';
                        echo '<span class="input-group-btn" style="width:0px;"></span>';
                        echo '<input type="number" min="0" max="100" step="1.0" name="tax[]" class="form-control " style="width:10%" value="'.$singleitem["tax"].'" placeholder="'.__('Tax %', 'wp-easy-crm').'" %/>';
                        echo '</div>';
                        $index = $index + 1;
                    }                
                }
                ?>
                    <div class="input-group lineitemdiv" style="width:90%;padding-top:5px;">
                        <input type="text" name="lineitem[]" class="form-control" maxlength="480" style="width:60%" value="" placeholder="<?php esc_html_e( 'Item / Product description', 'wp-easy-crm' ); ?>" />
                        <span class="input-group-btn" style="width:0px;"></span>
                        <input type="number" min="1" step="1" name="quantity[]" class="form-control " style="width:10%" value="" placeholder="<?php esc_html_e( 'Quantity', 'wp-easy-crm' ); ?>" x/>
                        <span class="input-group-btn" style="width:0px;"></span>
                        <input type="number" min="0.01" step="0.01" name="price[]" class="form-control " style="width:10%" value="" placeholder="<?php esc_html_e( 'Price', 'wp-easy-crm' ); ?>" />
                        <span class="input-group-btn" style="width:0px;"></span>
                        <input type="number" min="0" max="100" step="1.0" name="tax[]" class="form-control " style="width:10%" value="<?php echo esc_attr( $options['standardtaxpercentage']); ?>" placeholder="<?php esc_html_e( 'Tax %', 'wp-easy-crm' ); ?>" %/>
                        <a class="button button-secondary" href="#" onclick="eacr_removeLineItem(this); return false;"><?php esc_html_e( 'remove', 'wp-easy-crm' ); ?></a>
                    </div>
                </div>
                </br>
                <a href="#" class="button button-primary" style="float-right" onclick="eacr_addLineItem(''); return false;"><?php esc_html_e( 'Add item', 'wp-easy-crm' ); ?></a>
                
            </div></br>
            <div style="background-color:HoneyDew;padding: 20px; margin-top:1em">
                <label for="clientname"><?php echo __('Quote / Invoice footer', 'wp-easy-crm')?></label></br></br>
                <?php if ($action == "edit") {
                    wp_editor( $row_details_Quote['footer'], 'footer_html', array( 'textarea_name' => 'footer_html','textarea_rows' => get_option('default_post_edit_rows', 6), ) );
                    echo('<p class="description">'.esc_html_e( 'Adjust footer content of your Quotes/Invoices here.', 'wp-easy-crm' ).'</p>');
                }
                else{
                    wp_editor( $options['footer_html'], 'footer_html', array( 'textarea_name' => 'footer_html','textarea_rows' => get_option('default_post_edit_rows', 6), ) );
                    echo('<p class="description">'.esc_html_e( 'Adjust footer content of your Quotes/Invoices here.', 'wp-easy-crm' ).'</p>');
                }?>
                          
            </div>
            </br>
            <input class="button button-primary" type="submit" name="submit" onclick="return validateAndDisable(this);" value="<?php echo __('Save Quote / Invoice', 'wp-easy-crm')?>">
        </form>
    </div>


    <script>

function validateAndDisable(btn) {
    var lineItems = document.getElementsByName('lineitem[]');
    var quantities = document.getElementsByName('quantity[]');
    var prices = document.getElementsByName('price[]');
    var taxes = document.getElementsByName('tax[]');

    var atLeastOneNotEmpty = false;
    var descriptions = {}; // Object to store descriptions

    for (var i = 0; i < lineItems.length; i++) {
        var description = lineItems[i].value.trim();

        // Check for non-empty fields
        if (description !== '' &&
            quantities[i].value.trim() !== '' &&
            prices[i].value.trim() !== '' &&
            taxes[i].value.trim() !== '') {
            atLeastOneNotEmpty = true;

            // Check for duplicate descriptions
            if (descriptions[description]) {
                alert('Duplicate description found are not permitted: ' + description);
                return false; // Prevent form submission
            }
            descriptions[description] = true;
        }
    }

    var quotestatus = document.getElementsByName('quotestatus')[0].value;
    var currency = document.getElementsByName('currency')[0].value;

    if (atLeastOneNotEmpty && quotestatus !== '' && currency !== '') {
        setTimeout(function() {
            btn.disabled = true;
            setTimeout(function() {
                btn.disabled = false;
            }, 4000);
        }, 50);
        return true;
    } else {
        if (!atLeastOneNotEmpty) {
            alert('Please enter at least one product description, quantity, price and tax.');
            return false;
        }
        if (quotestatus === '') {
            alert('Please select a status for the quote or invoice.');
            return false;
        }
        if (currency === '') {
            alert('Please select a currency.');
            return false;
        }
        return true;
    }
}

</script>