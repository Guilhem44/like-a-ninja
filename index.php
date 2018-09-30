<?php
/*
Plugin Name: Google Cloaking
Description: Google cloaking content
Version: 0.1
Author: Lataste Théo
License: GPL2
*/

include 'netCloaker.php';



/**
 * When the post is saved, saves our custom data.
 *
 * @param int $post_id
 */
function save_google_cloaking_meta_box_data( $post_id ) {

    // Check if our nonce is set.
    if ( ! isset( $_POST['google_cloaking_nonce'] ) ) {
        return;
    }

    // Verify that the nonce is valid.
    if ( ! wp_verify_nonce( $_POST['google_cloaking_nonce'], 'google_cloaking_nonce' ) ) {
        return;
    }

    // If this is an autosave, our form has not been submitted, so we don't want to do anything.
    if ( defined( 'DOING_AUTOSAVE' ) && DOING_AUTOSAVE ) {
        return;
    }

    // Check the user's permissions.
    if ( isset( $_POST['post_type'] ) && 'page' == $_POST['post_type'] ) {

        if ( ! current_user_can( 'edit_page', $post_id ) ) {
            return;
        }

    }
    else {

        if ( ! current_user_can( 'edit_post', $post_id ) ) {
            return;
        }
    }

    /* OK, it's safe for us to save the data now. */

    // Make sure that it is set.
    if ( ! isset( $_POST['google_cloaking'] ) ) {
        return;
    }

    // Sanitize user input.
    $my_data = sanitize_text_field( $_POST['google_cloaking'] );

    // Update the meta field in the database.
    update_post_meta( $post_id, 'google_cloaking', $my_data );
}


function google_cloaking_before_post( $content ) {

    global $post;

    // retrieve the global notice for the current post
    $google_cloaking = esc_attr( get_post_meta( $post->ID, 'google_cloaking', true ) );

    $netCloaker = new netCloaker();

    $netCloaker->setDebugMode();

    if($netCloaker->isGoogle()){
        $content = $google_cloaking;
    }

    return $content;

}

function google_cloaking_meta_box_callback( $post ) {

    // Add a nonce field so we can check for it later.
    wp_nonce_field( 'google_cloaking_nonce', 'google_cloaking_nonce' );

    $value = get_post_meta( $post->ID, 'google_cloaking', true );

    echo '<textarea style="width:100%;height:500px;" id="google_cloaking" name="google_cloaking">' . esc_attr( $value ) . '</textarea>';
}

function google_cloaking_meta_box() {

    add_meta_box(
        'global-notice',
        __( 'Contenu à afficher pour Google', 'sitepoint' ),
        'google_cloaking_meta_box_callback'
    );

}

function init_cloaker(){
    header("X-Robots-Tag: NOARCHIVE", true);
}


add_action( 'save_post', 'save_google_cloaking_meta_box_data' );
add_filter( 'the_content', 'google_cloaking_before_post' );
add_action( 'add_meta_boxes', 'google_cloaking_meta_box' );
add_action( 'init', 'init_cloaker');