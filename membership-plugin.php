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
    ?>
    <div class="container mt-5">
        <h1 class="display-4">Membership Dashboard</h1>
        <div class="row">
            <div class="col-md-4">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Total Members</h5>
                        <p class="card-text">50 Members</p>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Membership Type</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>John Doe</td>
                            <td>john@example.com</td>
                            <td>Gold</td>
                            <td>
                                <a href="#" class="btn btn-sm btn-primary">Edit</a>
                                <a href="#" class="btn btn-sm btn-danger">Delete</a>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <?php
}
