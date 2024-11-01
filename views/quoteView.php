<?php 
global $wpdb;
$quotesInvoiceTable = $wpdb->prefix . 'quoteclient';
$quoteItemTable = $wpdb->prefix . 'lineitemquote';
$clientTable = $wpdb->prefix . 'clients';

$quoteid = sanitize_text_field(isset($_GET['quoteid']) ? intval($_GET['quoteid']) : "");
$total = 0;
$taxtotal = 0;

if($quoteid != 0 && $quoteid != ""){
    //get all data first

    $quotedetail = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * from $quotesInvoiceTable WHERE id = %d", $quoteid
        ), ARRAY_A
    );

    $clientdetail = $wpdb->get_row(
        $wpdb->prepare(
            "SELECT * from $clientTable WHERE id = %d", $quotedetail['clientidfk']
        ), ARRAY_A
    );

    $lineitems = $wpdb->get_results(
        $wpdb->prepare(
            "SELECT * from $quoteItemTable WHERE quoteidfk = %d", $quoteid
        ), ARRAY_A
    );

     //calculate total including tax and each line-item  

    foreach ((array) $lineitems as $singleitem){
        if($singleitem != null && $singleitem != "" ){
                $itemtotal = ($singleitem['price'] * $singleitem['tax'] / 100 + $singleitem['price']) * $singleitem['quantity'];
                $itemtax = ($singleitem['price'] * $singleitem['tax'] / 100) * $singleitem['quantity'];
                $total = $total + $itemtotal;
                $taxtotal = $taxtotal + $itemtax;
        }
      
    }
    
    $total = round($total, 2);
    $total = number_format($total, 2);

    $taxtotal = round($taxtotal, 2);
    $taxtotal = number_format($taxtotal, 2);
    
    
}



?>

<script>

function eacr_printINV() {

    <?php 
    if($quotedetail['quotestatus'] == "quote" || $quotedetail['quotestatus'] == "cancelled")
    {
                            
        $quotename = 'QUO-00'.esc_html($quotedetail['id']);
        // echo '<a href="#" onclick="eacr_printINV('.$quotename.');" class="button-secondary">'.__( 'Save Quote / Invoice', 'wp-easy-crm' ).' </a><br/>';
        echo 'var easyCRMinvname = "' . $quotename . '";';

    }
    else
    {
        //is invoice
        $invoicename = 'INV-00'.esc_html($quotedetail['id']);

        echo 'var easyCRMinvname = "' . $invoicename . '";';

        // echo '<a href="#" onclick="eacr_printINV('.$invoicename.');" class="button-secondary">'.__( 'Save Quote / Invoice', 'wp-easy-crm' ).' </a><br/>';

    }
    ?>
    

    var divToPrint = document.getElementById("entireInvoice").innerHTML;

    // let newdivtoprint = divToPrint.replace(/<b>/g, '<b style="font-weight: bold;">').replace(/<strong>/g, '<strong style="font-weight: bold;">');

    // console.log(newdivtoprint);
 
    var opt = {
    margin:       0.6,
    filename:     easyCRMinvname + '.pdf',
    image:        { type: 'jpeg', quality: 1 },
    html2canvas:  { scale: 2 ,dpi: 200, letterRendering: true},
    jsPDF:        { unit: 'pt', format: 'letter', orientation: 'portrait',},
    pagebreak: { mode: ['avoid-all', 'css', 'legacy'] }

    };

    var worker = html2pdf().set(opt).from(divToPrint).save();
  


}

</script>

<p><?php echo wp_kses_post(@$msg); ?></p>

<a class="button button-primary" href="admin.php?page=wp-easy-crm"><?php echo __('List of all Clients', 'wp-easy-crm')?></a><span> </span>

<a class="button button-primary" href="admin.php?page=wp-easy-crm-perfil&id=<?php echo __($quotedetail['clientidfk'])?>"><?php echo __('Client Profile', 'wp-easy-crm')?> </a><span> </span>

<a href="#" class="button-secondary" onclick="eacr_printINV();"><?php _e( 'Save Quote / Invoice', 'wp-easy-crm' )?></a><br/>

<div id="entireInvoice" class="entireInvoice" style="background-color:#FAF9F6;padding: 20px; margin:1em; max-width:600px">
<div style="max-width:94%;margin:1em;">
<?php 
if($quotedetail['quotestatus'] == "quote" || $quotedetail['quotestatus'] == "cancelled"){
                            

        echo '<h1>'.'QUO-00'.esc_html($quotedetail['id']).'</h1>';  

    }
    else{
        //is invoice
        echo '<h1>'.'INV-00'.esc_html($quotedetail['id']).'</h1>';

}
?>
    <?php echo '<div style="float:right;">'.wp_kses_post(date('Y-m-d',strtotime($quotedetail['lastupdate_at']))).'</div>'?>
    <?php echo '<div>'.wp_kses_post($quotedetail['header']).'</div><br/>'?>
    <?php echo '<hr/>'?>
    <?php echo $clientdetail['email']?>
    <?php echo '<div>'.wp_kses_post($quotedetail['clientinfo']).'</div>'?>
    <?php echo '<hr/>'?>
    <?php echo '<hr/>'?>
    <?php if($quotedetail['quotestatus'] == "quote" || $quotedetail['quotestatus'] == "cancelled"){
                            

                            echo '<h2>'.__('Quotation', 'wp-easy-crm').'</h2>';  
                    
                        }
                        else{
                            //is invoice
                            echo '<h2>'.__('Invoice', 'wp-easy-crm').'</h2>';
                    
                    }
    ?>

    <table class="wp-list-table widefat fixed striped table-view-list posts" cellspacing="0" >           
        <thead>
        <tr>
            <th class="manage-column column-cb check-column" scope="col" style="width:60%;padding-left:10px;padding-top:10px;"><b><?php _e( 'Item', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col" style="padding-left:10px;padding-top:10px;"><b><?php _e( 'Quantity', 'wp-easy-crm' );?></b></th>
            <th class="manage-column column-cb check-column" scope="col" style="padding-left:10px;padding-top:10px;"><b><?php _e( 'Price', 'wp-easy-crm' );_e(' ');_e($quotedetail['currency'])?></b></th>
            <th class="manage-column column-cb check-column" scope="col" style="padding-left:10px;padding-top:10px;"><b><?php _e( 'Tax %', 'wp-easy-crm' );?></b></th>
        </tr>
        </thead>
        <tbody>
    <?php 
                //existing line items section
                // var_dump($row_details_LineItems);

                if ($lineitems != null && count($lineitems) > 0) {
               
                    foreach ((array) $lineitems as $singleitem) {

                        echo '<tr>';
                        echo '<td>'.esc_html($singleitem['lineitem']).'</td>';
                        echo '<td>'.esc_html($singleitem["quantity"]).'</td>';
                        echo '<td style="float:right;">'.esc_html($singleitem["price"]).'</td>';
                        echo '<td>'.esc_html($singleitem["tax"]).'</td>';
                        echo '</tr>';
                    }                
                }

                echo '<tr>';
                echo '<td><hr/></td>';
                echo '<td><hr/></td>';
                echo '<td><hr/></td>';
                echo '<td><b><hr/>'.'</b></td>';
                echo '</tr>';

                echo '<tr>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td><b style="float:right;font-weight: bold;">'.__('Tax ', 'wp-easy-crm').'</b></td>';
                echo '<td><b style="float:right;font-weight: bold;">'.esc_html($taxtotal).' '.esc_html($quotedetail['currency']).'</b></td>';
                echo '</tr>';

                echo '<tr>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td><hr/></td>';
                echo '<td><b><hr/>'.'</b></td>';
                echo '</tr>';                

                echo '<tr>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td><b style="float:right;font-weight: bold;">'.__('Total ', 'wp-easy-crm').'</b></td>';
                echo '<td><b style="float:right;font-weight: bold;">'.esc_html($total).' '.esc_html($quotedetail['currency']).'</b></td>';
                echo '</tr>';

                echo '<tr>';
                echo '<td></td>';
                echo '<td></td>';
                echo '<td><hr/><hr/></td>';
                echo '<td><hr/><hr/></td>';
                echo '</tr>';
               
    ?>
    </tbody>
    </table>
    <?php echo '<h2></h2><hr/><h2></h2><br/><br/>'?>
    <?php echo '<div>'.wp_kses_post($quotedetail['footer']).'</div>'?>


    </div>
</div>








