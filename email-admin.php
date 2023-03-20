<?php

/*
* Plugin Name: email-admin
* Author: Atul
* Author URI: https://wpemailadmin.com
* Description: a mail for all the posts at the end of the day
* Text Domain: maildesk
*/

add_action( 'publish_post', 'send_email_to_admin' );

//if this file called directly, abort
if( !defined( 'WPINC' ) )
{
    die;
}

if( !defined( 'WP_PLUGIN_VERSION' ) )
{
    define( 'WP_PLUGIN_VERSION', '1.0.0' );
}

if( !defined( 'WP_PLUGIN_DIR' ) )
{
    define( 'WP_PLUGIN_DIR', plugin_dir_url(__FILE__) );
}


//setting the menu page for the trial
function wp_email_menu()
{
    add_menu_page(
        'WP email',
        'email',
         'manage_options',
          'email-admin',
           'get_emaildata',
            'dashicons-calendar',
             7 );
    

}
add_action('admin_menu', 'wp_email_menu');
//ending the setting


//email to send in evry minute or so 
// if(!function_exists('adding_cron_schedule'))
// {
//     function adding_cron_schedule($schedules = array())
//     {
//         $schedules['every_minute'] = array(
//             'iterval' => 120,
//             'display' => __('Every Minute', 'maildesk'),
//         );
//         return $schedules;
//     }
// }
// add_filter('cron_schedules', 'adding_cron_schedule');


function get_emaildata()
{
    $args = array(
        'date_query' => array(
            array(
                'after' => '24 hours ago',
            ),
        ),
    );

    $query = new WP_Query( $args );
    $posts = $query->posts;
    $data = array();

    foreach($posts as $post)
    {
        $post_data = array(
            'title' => $post->post_title,
            'url' => get_permalink($post->ID),
            'meta_title' => get_post_meta($post->ID, '_yoast_wpseo_title', true),
            'meta_description' => get_post_meta( $post->ID, '_yoast_wpseo_metadesc', true ),
            'meta_keywords' => get_post_meta($post->ID, '_yoast_wpseo_focuskw', true),
            //'page_speed' => get_page_speed_score( get_permalink( $post->ID ) ),
        );
        array_push($data, $post_data);
    }
    //return $data; 

    print("<pre>".print_r( $data, true ). "</pre>");
    var_dump($data);
}

// function emails_data()
// {
//     echo 'inside this function';
//     get_emaildata();
// }


function send_emaildata()
{
    $to = get_option('admin_email');
    $subject = 'Daily posts';
    $data = get_daily_post_summary();
    $message = '';

    foreach($data as $post_data)
    {
        $message .= 'Title:' . $post_data['title'] . "\n";
        $message .= 'URL:' . $post_data['url'] . "\n";
        $message .= 'Metta Title:' . $post_data['meta_title'] . "\n";
        $message .= 'Meta Description' . $post_data['meta_description'] . "\n";
        $message .= 'Meta Keywords:' . $post_data['meta_keywords'] . "\n";
        //$message .= 'Page Speed Score:' . $post_data['page_speed'] . "seconds \n";
        $message .= "\n";
    }
    $headers = array(
        'From: atul.kumar@wisdmlabs.com',
        'Content-Type: text/html; charset=UTF-8'
    );

    wp_mail($to, $subject, $message, $headers);
}


function send_email_to_admin() {
    $to = 'atul.kumar@wisdmlabs.com';
    $subject = 'Daily Update';
    $message = 'This is your daily update from WordPress.';
    $headers = array( 
        'From: atul.kumar@wisdmlabs.com',
        'Content-Type: text/html; charset=UTF-8' );

    wp_mail( $to, $subject, $message, $headers );
}