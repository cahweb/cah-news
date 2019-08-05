<?php 

// Register custom REST route
function cah_news_register_search_route() {
    register_rest_route('cah_news/v1', '/search', [
        'methods'   => WP_REST_Server::READABLE,
        'callback'  => 'cah_news_search',
        'args'      => cah_news_get_search_args(),
    ]);
}
add_action('rest_api_init', 'cah_news_register_search_route');

// Arguments of REST search route
function cah_news_get_search_args() {
    $args = []; 
    $args['s'] = [
        'description' => esc_html__('The search term', 'cah_news'),
        'type'        => 'string'
    ];
    return $args; 
}

function cah_news_rest_search($request) {
    $posts = [];
    $results = [];

    $args = [
        'post_type' => 'news',
    ];
    if(isset($request['s'])) {
        $args['s'] = $request['s'];
    }

}

// Returns a REST API query URL of news posts
function cah_news_query_copy($params, $advanced=false, $embed=true) {
    $base_url = 'http://wordpress.cah.ucf.edu/wp-json/wp/v2/news?';
    $query = '';

    foreach($params as $key => $value) {
        if (is_array($value)) {
            $value = implode(',', $value);
        }
        if ($value != '') {
            $query .= sprintf('%s=%s&', $key, $value);
        }
    }
    if ($embed) {
        $query .= '_embed';
    }


    $request_url = $base_url . $query;
    $response = wp_remote_get($request_url, array('timeout'=>20));
    if (is_wp_error($response)) {
        echo 'Error showing news ';
        return null;
    }

    $body = json_decode(wp_remote_retrieve_body($response));
    if (!$advanced) {
        return $body;
    }
    $max_pages = $response['headers']['X-WP-TotalPages'];
    $result = array(
        'posts' => $body,
        'max_pages' => $max_pages,
    );
    return $result;
}



// REST route callback
// function cah_news_search($request) {
//     $posts = [];
//     $results = []; 
//     if (isset($request)) 
// }


?> 
