<?php

global $wpdb;
$tagTable = $wpdb->prefix . 'easytags';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'easytags_add_edit_tag')) {
        $tagTitle = sanitize_text_field(ltrim($_POST['tagtitle'], '#'));
        $tagColor = sanitize_hex_color($_POST['tagcolor']);
        $currentTime = current_time('mysql');
        
        if ($_POST['action'] == 'add_tag') {
            $wpdb->insert(
                $tagTable,
                [
                    'tagtitle' => $tagTitle,
                    'tagcolor' => $tagColor,
                    'created_at' => $currentTime
                ]
            );
        } elseif ($_POST['action'] == 'edit_tag' && isset($_POST['tag_id'])) {
            $tagId = intval($_POST['tag_id']);
            $wpdb->update(
                $tagTable,
                [
                    'tagtitle' => $tagTitle,
                    'tagcolor' => $tagColor
                ],
                ['id' => $tagId]
            );
        }
    } elseif (isset($_POST['_wpnonce']) && wp_verify_nonce($_POST['_wpnonce'], 'easytags_delete_tag') && $_POST['action'] == 'delete_tag' && isset($_POST['tag_id'])) {
        $tagId = intval($_POST['tag_id']);
        $wpdb->delete($tagTable, ['id' => $tagId]);
    }
    
    echo "<script>location.href = location.href;</script>";
}

$tags = $wpdb->get_results("SELECT * FROM {$tagTable} ORDER BY created_at DESC");

wp_enqueue_style('wp-color-picker');
wp_enqueue_script('wp-color-picker');

add_action('admin_footer', function() {
    ?><script type="text/javascript">jQuery(document).ready(function($) {$('.color-picker').wpColorPicker();});</script><?php
});

?>
<style>
.edit-delete-btn {
    display: inline-block;
    margin-right: 5px;
}
</style>

<div class="wrap">
    <h2>Manage Tags</h2>
    <form method="post" action="">
        <?php wp_nonce_field('easytags_add_edit_tag'); ?>
        <input type="hidden" name="action" value="add_tag" id="tag_form_action">
        <input type="hidden" name="tag_id" id="tag_id" value="">
        <table class="form-table">
            <tbody>
                <tr>
                    <th scope="row"><label for="tagtitle">Tag Title</label></th>
                    <td><input name="tagtitle" id="tagtitle" type="text" class="regular-text" required placeholder="enter text without # symbol"></td>
                </tr>
                <tr>
                    <th scope="row"><label for="tagcolor">Tag Color</label></th>
                    <td><input name="tagcolor" id="tagcolor" type="text" class="color-picker" value="#000000"></td>
                </tr>
            </tbody>
        </table>
        <p class="submit">
            <input type="submit" value="Save Tag" class="button-primary" id="submit_button">
            <button type="button" class="button" onclick="resetForm()">Cancel</button>
        </p>
    </form>

    <h2>Existing Tags</h2>
    <table class="wp-list-table widefat fixed striped">
        <thead>
            <tr>
                <th>ID</th>
                <th>Tag Title</th>
                <th>Tag Color</th>
                <th>Action</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($tags as $tag): ?>
            <tr>
                <td><?php echo esc_html($tag->id); ?></td>
                <td style="color: <?php echo esc_attr($tag->tagcolor); ?>">#<?php echo esc_html($tag->tagtitle); ?></td>
                <td><input type="color" value="<?php echo esc_attr($tag->tagcolor); ?>" disabled></td>
                <td>
                    <button type="button" class="button action edit-delete-btn" onclick="editTag('<?php echo $tag->id; ?>', '<?php echo esc_js($tag->tagtitle); ?>', '<?php echo esc_attr($tag->tagcolor); ?>')">Edit</button>
                    <form method="post" action="" style="display:inline;">
                        <?php wp_nonce_field('easytags_delete_tag'); ?>
                        <input type="hidden" name="action" value="delete_tag">
                        <input type="hidden" name="tag_id" value="<?php echo esc_attr($tag->id); ?>">
                        <input type="submit" class="button-link-delete action edit-delete-btn" value="Delete" onclick="return confirm('Are you sure?');">
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        </tbody>
    </table>
</div>

<script type="text/javascript">
function editTag(id, title, color) {
    document.getElementById('tag_form_action').value = 'edit_tag';
    document.getElementById('tag_id').value = id;
    document.getElementById('tagtitle').value = title; // Removed the prepended '#' for edit consistency
    document.getElementById('tagcolor').value = color;
    document.getElementById('submit_button').value = 'Update Tag';
    window.scrollTo(0, 0);
}

function resetForm() {
    document.getElementById('tag_form_action').value = 'add_tag';
    document.getElementById('tag_id').value = '';
    document.getElementById('tagtitle').value = '';
    document.getElementById('tagcolor').value = '#000000';
    document.getElementById('submit_button').value = 'Save Tag';
}
</script>
