<?php
// Check if the user has the capability to manage options
if (!current_user_can('manage_options')) {
    wp_die(__('You do not have sufficient permissions to access this page.'));
}

global $wpdb;
$accountingregionTable = $wpdb->prefix . 'eacraccountingregion';

// Process add/update
if (isset($_POST['submit']) && check_admin_referer('eacr_edit_region', 'eacr_edit_region_nonce')) {
    // Check for new region addition
    if (!empty($_POST['new_regionname']) && sanitize_text_field($_POST['new_regionname']) != 'General') {
        $wpdb->insert(
            $accountingregionTable,
            [
                'regionname' => sanitize_text_field($_POST['new_regionname']),
                'lastupdate_at' => current_time('mysql'),
                'created_at' => current_time('mysql')
            ]
        );
        echo '<div id="message" class="updated fade"><p><strong>New region added.</strong></p></div>';
    }

    // Iterate through the posted data for updates
    if (isset($_POST['regions'])) {
        foreach ($_POST['regions'] as $id => $region) {
            if ($id == 1) continue; // Skip "General"

            $wpdb->update(
                $accountingregionTable,
                ['regionname' => sanitize_text_field($region['regionname']), 'lastupdate_at' => current_time('mysql')],
                ['id' => intval($id)]
            );
        }
        echo '<div id="message" class="updated fade"><p><strong>Regions updated.</strong></p></div>';
    }
}

// Process delete separately
if (isset($_POST['delete']) && check_admin_referer('eacr_delete_region_' . $_POST['region_id'], 'eacr_delete_region_nonce')) {
    $idToDelete = intval($_POST['region_id']);
    if ($idToDelete != 1) { // Ensure "General" cannot be deleted
        $wpdb->delete($accountingregionTable, ['id' => $idToDelete]);
        echo '<div id="message" class="updated fade"><p><strong>Region deleted.</strong></p></div>';
    }
}

// Retrieve all regions
$regions = $wpdb->get_results("SELECT * FROM $accountingregionTable ORDER BY id ASC");
?>

<div class="wrap">
    <h2>Edit Accounting Regions</h2>
    <div style="padding: 10px; background-color: yellow; margin-bottom: 10px;">
    Those are the accounting regions that can individually be applied to every client profile. The standard region is called "General". For every user that uses the CRM, you can choose the regions the user has access to in the user profile, activating the region through their respective checkmark. Those regions are only visible to a WordPress admin.
    </div>
    <form method="post" action="">
        <?php wp_nonce_field('eacr_edit_region', 'eacr_edit_region_nonce'); ?>
        <table class="widefat fixed" cellspacing="0">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Region Name</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($regions as $region) : ?>
                    <tr>
                        <td><?php echo esc_html($region->id); ?></td>
                        <td>
                            <?php if ($region->id != 1) : ?>
                                <input type="text" name="regions[<?php echo esc_attr($region->id); ?>][regionname]" value="<?php echo esc_attr($region->regionname); ?>" />
                            <?php else : ?>
                                <?php echo esc_html($region->regionname); ?> (Not Editable)
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php if ($region->id != 1) : ?>
                                <form method="post" action="" onsubmit="return confirm('Are you sure you want to delete this region?');">
                                    <?php wp_nonce_field('eacr_delete_region_' . $region->id, 'eacr_delete_region_nonce'); ?>
                                    <input type="hidden" name="region_id" value="<?php echo esc_attr($region->id); ?>">
                                    <input type="submit" name="delete" value="Delete" class="button-link-delete">
                                </form>
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endforeach; ?>
                <tr>
                    <td>#</td>
                    <td><input type="text" name="new_regionname" placeholder="Add new region..." /></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
        <p><input type="submit" name="submit" value="Save Changes" class="button-primary" /></p>
    </form>
</div>
