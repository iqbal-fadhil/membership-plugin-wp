<?php
/**
 * Plugin Name: Membership Plugin
 * Description: This plugin acts as a custom membership plugin with Bootstrap admin view.
 * Version: 1.0
 * Author: Iqbal Fadhil (https://iqbalfadhil.com)
 */

function mp_enqueue_admin_styles() {
    wp_enqueue_style('bootstrap-css', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css');
    wp_enqueue_script('bootstrap-js', 'https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js', array('jquery'), null, true);
    wp_enqueue_style('custom-css', plugin_dir_url(__FILE__) . 'assets/css/custom-admin.css');
}
add_action('admin_enqueue_scripts', 'mp_enqueue_admin_styles');
 
function mp_register_menu_page() {
    add_menu_page(
        'Membership Plugin', 
        'Membership',        
        'manage_options',    
        'membership-plugin', 
        'mp_admin_page',     
        'dashicons-groups',  
        6                    
    );
}
add_action('admin_menu', 'mp_register_menu_page');

function mp_admin_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'members'; // Adjust the table name to your table
    $members = $wpdb->get_results("SELECT * FROM $table_name");
    ?>
    <div class="container mt-5">
        <h1 class="display-4">Membership Dashboard</h1>

        <?php if (isset($_GET['success']) && $_GET['success'] == 'true') : ?>
            <div class="alert alert-success">New member added successfully!</div>
        <?php endif; ?>

        <!-- Add New Member Form -->
        <h2>Add New Member</h2>
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="add_member">
            <?php wp_nonce_field('add_member_nonce', 'add_member_nonce_field'); ?>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" required>
            </div>
            <div class="mb-3">
                <label for="membership_type" class="form-label">Membership Type</label>
                <input type="text" class="form-control" id="membership_type" name="membership_type" required>
            </div>
            <button type="submit" name="submit" class="btn btn-primary">Add Member</button>
        </form>

        <!-- Display Existing Members -->
        <h2 class="mt-5">Existing Members</h2>
        <?php if (!empty($members)) : ?>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Membership Type</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($members as $member) : ?>
                        <tr>
                            <td><?php echo esc_html($member->id); ?></td>
                            <td><?php echo esc_html($member->name); ?></td>
                            <td><?php echo esc_html($member->email); ?></td>
                            <td><?php echo esc_html($member->membership_type); ?></td>
                            <td>
                                <a href="#" class="btn btn-warning btn-sm">Edit</a>
                                <a href="<?php echo esc_url(admin_url('admin-post.php?action=delete_member&id=' . $member->id)); ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete this member?');">Delete</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        <?php else : ?>
            <p>No members found.</p>
        <?php endif; ?>
    </div>
    <?php
}




function mp_create_membership_cpt() {
    register_post_type('member', array(
        'labels' => array(
            'name' => __('Members'),
            'singular_name' => __('Member')
        ),
        'public' => false,
        'has_archive' => false,
        'show_ui' => true,
        'menu_icon' => 'dashicons-id-alt',
        'supports' => array('title', 'editor', 'custom-fields')
    ));
}
add_action('init', 'mp_create_membership_cpt');

function mp_install() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'members';  // Adjust table name if necessary
    
    $charset_collate = $wpdb->get_charset_collate();
    
    $sql = "CREATE TABLE $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        name varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        membership_type varchar(100) NOT NULL,
        PRIMARY KEY (id)
    ) $charset_collate;";
    
    require_once(ABSPATH . 'wp-admin/includes/upgrade.php');
    dbDelta($sql);
}
register_activation_hook(__FILE__, 'mp_install');


function mp_handle_form_submission() {
    if (isset($_POST['submit'])) {
        global $wpdb;
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $membership_type = sanitize_text_field($_POST['membership_type']);

        $wpdb->insert($wpdb->prefix . 'members', array(
            'name' => $name,
            'email' => $email,
            'membership_type' => $membership_type
        ));

        echo '<div class="notice notice-success"><p>Member added successfully!</p></div>';
    }
}
add_action('admin_post_add_member', 'mp_handle_form_submission');

register_activation_hook(__FILE__, 'mp_install');

if (defined('WP_DEBUG') && WP_DEBUG) {
    error_log('Membership plugin loaded.');
}


function mp_handle_add_member() {
    // Verify nonce for security
    if (!isset($_POST['add_member_nonce_field']) || !wp_verify_nonce($_POST['add_member_nonce_field'], 'add_member_nonce')) {
        wp_die('Security check failed');
    }

    // Check that all required fields are set
    if (isset($_POST['name'], $_POST['email'], $_POST['membership_type'])) {
        global $wpdb;
        
        // Sanitize the input
        $name = sanitize_text_field($_POST['name']);
        $email = sanitize_email($_POST['email']);
        $membership_type = sanitize_text_field($_POST['membership_type']);
        
        // Insert into the custom database table
        $table_name = $wpdb->prefix . 'members';  // Adjust this to your table name

        $wpdb->insert(
            $table_name,
            array(
                'name' => $name,
                'email' => $email,
                'membership_type' => $membership_type,
            ),
            array('%s', '%s', '%s')  // Format: strings
        );

        // Redirect back to the admin page after submission
        wp_redirect(admin_url('admin.php?page=membership-plugin&success=true'));
        exit;
    }
}
add_action('admin_post_add_member', 'mp_handle_add_member');
