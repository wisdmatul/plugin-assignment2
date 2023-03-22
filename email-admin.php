<?php

/*
* Plugin Name: email-admin
* Author: Atul
* Author URI: https://wpemailadmin.com
* Description: a mail for all the posts at the end of the day
* Text Domain: maildesk
*/

// add_action( 'wp', 'schedule_emails' );
register_activation_hook(__FILE__, 'schedule_emails');

//email to send in evry minute or so 
if(!function_exists('adding_cron_schedule')):
    function adding_cron_schedule($schedules = array())
    {
        $schedules['every_minute'] = array(
            'interval' => 120,
            'display' => __('Every Minute', 'maildesk'),
        );
        return $schedules;
    }
add_filter('cron_schedules', 'adding_cron_schedule');
endif;

function schedule_emails()
{
    
    if( !wp_next_scheduled( 'send_daily_post_summary' ) )
    {
        wp_schedule_event(time(), 'daily', 'send_daily_post_summary');
    }
}
add_action('send_daily_post_summary', 'send_emaildata');


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
            'page_speed' => get_page_speed_score( get_permalink( $post->ID ) ),
        );
        array_push($data, $post_data);
    }
    return $data;
}

function send_emaildata()
{
    $to = get_option('admin_email');
    $subject = 'Daily posts';
    $data = get_emaildata();
    $message = '';

    foreach($data as $post_data)
    {
        $message .= 'Title:' . $post_data['title'] . "\n";
        $message .= 'URL:' . $post_data['url'] . "\n";
        $message .= 'Metta Title:' . $post_data['meta_title'] . "\n";
        $message .= 'Meta Description' . $post_data['meta_description'] . "\n";
        $message .= 'Meta Keywords:' . $post_data['meta_keywords'] . "\n";
        $message .= 'Page Speed Score:' . $post_data['page_speed'] . "seconds \n";
        $message .= "\n";
    }
    $headers = array(
        'From: atul.kumar@wisdmlabs.com',
        'Content-Type: text/html; charset=UTF-8'
    );

    wp_mail($to, $subject, $message, $headers);
}

//google page speed score
function get_page_speed_score($url) {

    $api_key = "416ca0ef-63e4-4caa-a047-ead672ecc874"; // your api key
	$new_url = "http://www.webpagetest.org/runtest.php?url=".$url."&runs=1&f=xml&k=".$api_key; 
	$run_result = simplexml_load_file($new_url);
	$test_id = $run_result->data->testId;

    $status_code=100;
    
    while( $status_code != 200){
        sleep(10);
        $xml_result = "http://www.webpagetest.org/xmlResult/".$test_id."/";
	    $result = simplexml_load_file($xml_result);
        $status_code = $result->statusCode;
        $time = (float) ($result->data->median->firstView->loadTime)/1000;
    };

    return $time;
}
