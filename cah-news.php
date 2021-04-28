<?php

/*
 *
 * Plugin Name: CAH News Plugin
 * Description: News aggregation and distribution for UCF College of Arts and Humanities department sites
 * Author: Sean Reedy
 *
 */

// Constants
define('CAH_NEWS_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('CAH_NEWS_REST_BASE', 'https://news.cah.ucf.edu/wp-json/wp/v2/');

require_once CAH_NEWS_PLUGIN_PATH . 'includes/cah-news-setup.php';

// Get deparmtent IDs of those departments that are set to display in the options menu
function cah_news_get_dept_option() {
    return get_option('cah_news_display_dept2');
}

function cah_news_get_news($per_page=8, $paged=true, $args = NULL) {
    $dept = cah_news_get_dept_option();
    if(sizeof($dept)<5)
	$query = array(
		'dept' => $dept,
        'per_page' => $per_page,
    );
	else
	$query = array(
        'per_page' => $per_page,
    );
    // Department filter
    if (isset($_GET['dept'])) {
        $query['dept'] = esc_attr($_GET['dept']);
    }
    // Search term query
    if (isset($_GET['search'])) {
        $query['search'] = esc_attr($_GET['search']);
    }
    // Category filter
    if (isset($_GET['cat'])) {
        $cat = esc_attr($_GET['cat']);
        $query['categories'] = $cat;
    }
    // Page number 
    $query['page'] = max(get_query_var('paged'), 1);

    if( !is_null( $args ) && isset( $args['tags'] ) && !empty( $args['tags'] ) ){
        $query['tags'] = $args['tags'];
    }

    $result = cah_news_query($query, true);
    $posts = $result['posts'];

    // Post count
    if ($posts === null || count($posts) < 1) {
        echo sprintf('<div class="alert alert-warning my-3" role="alert"><span><strong>No posts found.</strong> Please <a href="%s">refresh</a> or try another query.</span></div>', cah_news_get_news_page_link());
        return;
    }

    // Decide which department style to display
    if (count($dept) === 1) {
        $dept = $dept[0];
    }
    else {
        $dept = 0;
    }

    // Display news list
    echo '<div class="mb-4">';
    foreach ($posts as $post) {
        cah_news_display_post($post, $dept);
    }
    echo '</div>';

    // Pagination
    if ($paged) {
        $max_pages = $result['max_pages'];
        cah_news_pagination($max_pages);
    }

}

// Make REST request and return JSON body object
function cah_news_get_rest_body($request) {
    $request = CAH_NEWS_REST_BASE . $request;
    $response = wp_remote_get($request, array('timeout'=>20));
    if (is_wp_error($response)) {
        return false;
    }
    $body = json_decode(wp_remote_retrieve_body($response));
    return $body;
}

// Get name of category via REST API
function cah_news_get_category_name($cat_ID) {
    $response = cah_news_get_rest_body('categories/' . $cat_ID);
    if ($response) {
        return $response->name;
    }
}

// Get a link to the main news page on the site
function cah_news_get_news_page_link() {
    $page = get_option('cah_news_set_news_page', 'news'); 
    $url = get_home_url(null, $page); 
    return $url; 
}

// Exclude current post from query, called with filter
function cah_news_exclude_current_post($query) {
    global $post_ID; 
    if (isset($post_ID) && is_numeric($post_ID)) {
        $query->set('post__not_in', array($post_ID)); 
    }
}

// Returns a REST API query URL of news posts
function cah_news_query($params, $advanced=false, $embed=true) {
    $base_url = CAH_NEWS_REST_BASE . '/news?';
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

// Retrieve thumbnail media from post JSON
function cah_news_get_thumbnail($post, $sizes=['medium_large', 'medium','large']) {
    if (isset($post->_embedded->{'wp:featuredmedia'}[0]->media_details->sizes->thumbnail->source_url))
    {
        $media_sizes = $post->_embedded->{'wp:featuredmedia'}[0]->media_details->sizes;
        foreach($sizes as $size) {
            if ( isset($media_sizes->{$size}->source_url) ) {
                return $media_sizes->{$size}->source_url; 
            }
        }
    }
    return false; 
}

// Display post preview with JSON information from REST API
function cah_news_display_post($post, $dept=0) {
    if (!is_object($post)) {
        return;
    }

    $title = $post->title->rendered;
    $excerpt = cah_news_excerpt($post->excerpt->rendered);
    
    $link = $post->link;
    if ($dept) {
        $link = add_query_arg(['dept' => $dept], $link);
    }
    $link = esc_url($link);
    
    $date = date_format(date_create($post->date), 'F d, Y');
    $thumbnail = '';
    // // $thumbnail = $post.embedded.{'wp:featuredmedia'}.media_details.sizes.thumbnail.source_url;
    if (isset($post->_embedded->{'wp:featuredmedia'}[0]->media_details->sizes->thumbnail->source_url))
    {
        $thumbnail = $post->_embedded->{'wp:featuredmedia'}[0]->media_details->sizes->thumbnail->source_url;
    }
	
	// Looking to see if it links to an outside page
	$links_to = (bool) get_post_meta( $post->id, '_links_to', true );
	$links_to_target = (bool) get_post_meta( $post->id, '_links_to_target', true) == "_blank";

    ?>

    <a class="cah-news-item" href="<?=$link?>"<?= $links_to && $links_to_target ? 'target="_blank" rel="noopener"' : ''?>>
    <div class="media py-3 px-2">
            <?
            if ($thumbnail) {
                echo '<img data-src="' . $thumbnail . '" class="d-flex align-self-start mr-3" aria-label="Featured image">';
                echo '<noscript><img src="' . $thumbnail . '" width="150" height="150" class="d-flex align-self-start mr-3" aria-label="Featured image"></noscript>';
            }
            else {
                echo '<input type="hidden" value="' . get_the_post_thumbnail_url( $post->ID ) . '">';
            }
            ?>
            <div class="media-body">
                <h5 class="mt-0"><?=$title?></h5>
                <p>
                    <span class="text-muted"><?=$date?>&nbsp;</span>
                    <span class="cah-news-item-excerpt"><?=$excerpt?></span>
                </p>
            </div>
    </div>
    </a>
    <?
}

// Modify excerpt for display
function cah_news_excerpt($text) {
    return wp_trim_words($text, 50, '...');
}

// Display pagination navigation 
function cah_news_pagination($max_pages) {

    if ($max_pages <= 1) {
        return;
    }

    $current_page = max(get_query_var('paged'), 1);
    $show_prev = $current_page > 1;
    $show_next = $current_page < $max_pages;
    $page_nums = array();

    $width = 5;
    if ($current_page > 1) {
        $page_nums[] = 1;
        for($i=$current_page-$width; $i<$current_page; $i++) {
            if ($i > 1) {
                $page_nums[] = $i;
            }
        }
    }

    $page_nums[] = $current_page;

    if ($current_page < $max_pages) {
        for($i=$current_page+1; $i<$current_page+$width; $i++) {
            if ($i < $max_pages) {
                $page_nums[] = $i;
            }
        }
        if (end($page_nums) !== $max_pages) {
            $page_nums[] = $max_pages;
        }
    }

    ?>
    <nav aria-label="News posts">
        <ul class="pagination pagination-lg">
            <?
            if ($show_prev) {
                echo sprintf('<li class="page-item"><a class="page-link" href="%s">&laquo; Previous</a></li>', get_pagenum_link($current_page-1));
            }

            $prev = $page_nums[0];
            echo '<span class="hidden-sm-down d-flex">';
            foreach($page_nums as $page) {
                // divider
                if ($page - $prev > 1) {
                    echo sprintf('<li class="page-item disabled"><a class="page-link disabled">...</a></li>');
                }
                $link = get_pagenum_link($page);
                $active = $page == $current_page ? 'active' : '';
                echo sprintf('<li class="page-item %s"><a href="%s" class="page-link">%s</a></li>', $active, $link, $page);
                $prev = $page;
            }
            echo '</span>';
            if ($show_next) {
                echo sprintf('<li class="page-item"><a class="page-link" href="%s">Next &raquo;</a></li>', get_pagenum_link($current_page+1));
            }
            ?>
        </ul>
    </nav>
    <?
}

// Get a list of all Departments (terms)
function get_departments() {
    switch_to_blog(1);
    $depts = get_terms([
        'taxonomy'   => 'dept',
        'hide_empty' => false,
        ]);
    restore_current_blog();

    if( !is_array( $depts ) || empty( $depts ) ) {
        $url = "https://news.cah.ucf.edu/wp-json/news/depts";
        $response = wp_remote_get( $url, ['timeout' => 20] );
        if( is_wp_error( $response ) ) {
            $depts = [];
        }
        else {
            $depts = json_decode( $response['body'] );
        }
    }
    return $depts;
}

// Returns news posts that do not have assigned Department taxonomy
function get_uncategorized_news() {
    $args = array(
        'post_type' => 'news',
        'posts_per_page' => -1,
        'post_status' => 'publish',
        'tax_query' => array(
            array(
                'taxonomy' => 'dept',
                'operator' => 'NOT EXISTS',
            ),
        ),
    );
    return get_posts($args);
}

// Get direct links to child sites where post appears
function cah_news_get_post_links($id, $exclude=[]) {
    $terms = wp_get_post_terms($id, 'dept');
    $permalink = get_the_permalink($id);
    $links = [];
    foreach($terms as $term) {
        $dept_id = $term->term_id;
        if (!in_array($dept_id, $exclude)) {
            $post_url = esc_url(add_query_arg('dept', $dept_id, $permalink));
            $links[] = sprintf('<a href="%s">%s</a>', $post_url, $term->name);
        }
    }
    return $links;
}



// Included files 
require_once CAH_NEWS_PLUGIN_PATH . 'includes/cah-news-search.php';
require_once CAH_NEWS_PLUGIN_PATH . 'includes/cah-news-shortcode.php';
require_once CAH_NEWS_PLUGIN_PATH . 'admin/cah-news-toolbar.php';
require_once CAH_NEWS_PLUGIN_PATH . 'includes/cah-news-meta.php';

// Included admin files
if (is_admin()) {
    require_once CAH_NEWS_PLUGIN_PATH . 'admin/cah-news-edit-taxonomy.php';
    require_once CAH_NEWS_PLUGIN_PATH . 'admin/cah-news-options.php';
    require_once CAH_NEWS_PLUGIN_PATH . 'admin/cah-news-admin-list.php';
    require_once CAH_NEWS_PLUGIN_PATH . 'admin/cah-news-edit.php';
}

?>