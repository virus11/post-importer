<?php
/**
* 
* Plugin Name: Post Importer
* Description: This plugin allows to fetch post from other source and store that data in WordPress along with extra data.
* Version: 1.0.1
* Author: Virendra Rathod | 9307642751 | Virendra.rathod61@gmail.com
*
**/

/*******************************************************************************************************************************************/

/**
*  Activation Hook - 
*  Runs when plugin is activated
*
**/


register_activation_hook( __FILE__, 'activating_post_importer_plugin' );

function activating_post_importer_plugin () {

    // Create Database Table, 

}



/**
*  Deactivation Hook - 
*  Runs when plugin is deactivated
*
**/


register_deactivation_hook( __FILE__, 'deactivating_post_importer_plugin' );

function deactivating_post_importer_plugin () {

    // Remove Temporary data

        // Unregister the post type, so the rules are no longer in memory.
       
}




/**
*  Uninstall Hook - 
*  Runs when plugin is deleted
*
**/


register_uninstall_hook( __FILE__, 'uninstall_post_importer_plugin' );

function uninstall_post_importer_plugin () {

    // Remove Database Table, custom post type etc
}





/*******************************************************************************************************************************************/

/**
*  Creating Article custom post type - 
*  
*
**/



add_action ( 'init', 'post_importer_plugin_create_article_post_type');

function post_importer_plugin_create_article_post_type () {
  
        // Registers the custom post type plugin.
        $labels = array(
            'name'                  => _x( 'Articles', 'Post type general name', 'textdomain' ),
            'singular_name'         => _x( 'Article', 'Post type singular name', 'textdomain' ),
            'menu_name'             => _x( 'Articles', 'Admin Menu text', 'textdomain' ),
            'name_admin_bar'        => _x( 'Article', 'Add New on Toolbar', 'textdomain' ),
            'add_new'               => __( 'Add New', 'textdomain' ),
            'add_new_item'          => __( 'Add New Article', 'textdomain' ),
            'new_item'              => __( 'New Article', 'textdomain' ),
            'edit_item'             => __( 'Edit Article', 'textdomain' ),
            'view_item'             => __( 'View Article', 'textdomain' ),
            'all_items'             => __( 'All Articles', 'textdomain' ),
            'search_items'          => __( 'Search Articles', 'textdomain' ),
            'parent_item_colon'     => __( 'Parent Articles:', 'textdomain' ),
            'not_found'             => __( 'No Articles found.', 'textdomain' ),
            'not_found_in_trash'    => __( 'No Articles found in Trash.', 'textdomain' )
        );
     
        $args = array(
            'labels'             => $labels,
            'public'             => true,
            'publicly_queryable' => true,
            'show_ui'            => true,
            'show_in_menu'       => true,
            'query_var'          => true,
            'rewrite'            => array( 'slug' => 'article' ),
            'capability_type'    => 'post',
            'has_archive'        => true,
            'hierarchical'       => false,
            'menu_position'      => 2,
            'menu_icon'          => 'dashicons-edit-page',
            'supports'           => array( 'title', 'editor', 'author'),
        );
     
        register_post_type( 'Article', $args );

        // Clear the permalinks to remove our post type's rules from the database.
        flush_rewrite_rules();
}





/*******************************************************************************************************************************************/

/**
*  Creating Article custom post type - 
*  
*
**/



add_action ( 'admin_menu', 'post_importer_plugin_add_importer_submenu_page');

function post_importer_plugin_add_importer_submenu_page()
{
    $hookname = add_submenu_page(
        'edit.php?post_type=article',
        'Article and Author Importer',
        'Article and Author Importer',
        'manage_options',
        'post-importer',
        'post_importer_submenu_page_html'
    );

    
    add_action( 'load-' . $hookname, 'post_importer_submenu_page_html_submit' );

    // Clear the permalinks to remove our post type's rules from the database.
    flush_rewrite_rules();

}


function post_importer_submenu_page_html () {
      // check user capabilities
      if ( ! current_user_can( 'manage_options' ) ) {
        return;
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
        <form action="" method="post">
            <?php
            submit_button( __( 'Import Authers', 'textdomain' ), 'primary', 'post_importer_authers' );
            submit_button( __( 'Import Articles', 'textdomain' ), 'primary', 'post_importer_articles' );
            ?>
        </form>

    </div>
    <?php
    if ('POST' === $_SERVER['REQUEST_METHOD']) {

        if( isset( $_POST['post_importer_authers'])) {
        
         
   
            $url      = 'https://jsonplaceholder.typicode.com/users';
            $response = wp_remote_get( esc_url_raw( $url ) );
            
            /* Will result in $api_response being an array of data,
            parsed from the JSON response of the API listed above */
            $users = json_decode( wp_remote_retrieve_body( $response ), true );
            
            foreach ( $users as $user) {

                // check if the username is taken
                $user_id = username_exists( $user['username'] );
                
                // check that the email address does not belong to a registered user
                if ( ! $user_id && email_exists( $user['email'] ) === false ) {
                    // create a random password
                    $random_password = wp_generate_password( 12, false );
                    // create the user
                    $userdata = array(
                        'user_pass'             => $random_password,   //(string) The plain-text user password.
                        'user_login'            => $user['username'],   //(string) The user's login username.
                        'user_nicename'         => $user['name'],   //(string) The URL-friendly user name.
                        'user_email'            => $user['email'],   //(string) The user email address.
                        'display_name'          =>  $user['name'],   //(string) The user's display name. Default is the user's username.
                        'user_registered'       => '',   //(string) Date the user registered. Format is 'Y-m-d H:i:s'.
                        'role'                  => 'author',   //(string) User's role.
                    
                    );

                    $user_id = wp_insert_user( $userdata );
                
                    if ( ! is_wp_error( $user_id ) ) {
                    
                    echo "User ID : ". $user_id;
                    add_user_meta( $user_id, 'post_importer_auther_street', $user['address']['street']);
                    add_user_meta( $user_id, 'post_importer_auther_suite', $user['address']['suite']);
                    add_user_meta( $user_id, 'post_importer_auther_city', $user['address']['city']);
                    add_user_meta( $user_id, 'post_importer_auther_zipcode', $user['address']['zipcode']);
                    add_user_meta( $user_id, 'post_importer_auther_phone', $user['phone']);
                    add_user_meta( $user_id, 'post_importer_auther_website', $user['website']);    
                    add_user_meta( $user_id, 'post_importer_auther_company_name', $user['company']['name']);

                    
                    echo '<div class="wrap"><h4>'. $user['id'].' -- '.$user['name'].'  --  imported successfully!</h4></div><hr>';  
    }


    }




    }
    echo '<div class="notice notice-success is-dismissible"><h2>Authers Imported successfully!.</h2></div>';
    echo '<div class="wrap"><h2>Authers Imported successfully!</h2></div>'; 

    }

    if( isset( $_POST['post_importer_articles'])) {

        $url      = 'https://jsonplaceholder.typicode.com/posts';
        $response = wp_remote_get( esc_url_raw( $url ) );
        
        /* Will result in $api_response being an array of data,
        parsed from the JSON response of the API listed above */
        $posts = json_decode( wp_remote_retrieve_body( $response ), true );
        
        $blogusers = get_users( array( 'role__in' => array( 'author'), 'fields' => 'ID') );

        foreach ( $posts as $post) {
            

            // Create post object
            $my_post = array(
                'post_title'    => wp_strip_all_tags( $post['title'] ),
                'post_content'  => $post['body'],
                'post_status'   => 'publish',
                'post_author'   => $blogusers[array_rand($blogusers)],
                'post_type'     => 'article',
            );
            
            // Insert the post into the database
            $post_id = wp_insert_post( $my_post );

            if ( ! is_wp_error( $post_id ) ) {
                    
                echo "Article ID : ". $post_id;
                                
                echo '<div class="wrap"><h4>'. $post['id'].' -- '.$post['title'].'  --  imported successfully!</h4></div><hr>';  
}

        }

        
    echo '<div class="notice notice-success is-dismissible"><h2>Articles Imported successfully!.</h2></div>';
    echo '<div class="wrap"><h2>Articles Imported successfully!</h2></div>'; 

    }
}
}

function post_importer_submenu_page_html_submit () {

    
}