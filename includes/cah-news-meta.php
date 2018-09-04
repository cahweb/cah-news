<?

// Show links to other sites where post appears
function cah_news_appears_on($post_ID, $exclude=[]) {
    $links = cah_news_get_post_links($post_ID, $exclude);
    if ($links) {
        echo '<span class="text-muted">Appears on </span> ' . implode(',', $links);
    }
}


// Display post's categories and tags
function cah_news_categories_tags($post_ID) {
    switch_to_blog(1);
    $post_categories = wp_get_post_categories($post_ID, [
        'fields' => 'all',
    ]);
    $tags = get_the_tag_list(
        '<span class="text-muted">Tags:</span> ',
        ', ',
        '<br>'
    );
    restore_current_blog();

    $cat_links = [];
    $cat_str = '<a href="%s">%s</a>';
    $base_url = cah_news_get_news_link();
    foreach($post_categories as $cat) {
        $link = add_query_arg('cat', $cat->term_id, $base_url);
        $cat_links[] = sprintf($cat_str, $link, $cat->name);
    }

    if (count($cat_links) > 0) {
        echo "<span class='text-muted'>Posted in </span>";
        echo implode(', ', $cat_links);
        echo '<br>';
    }

    echo $tags;
}


// Display links to posts with same categories as current news post
function cah_news_related_posts($post_ID) {
    $dept = get_option('cah_news_display_dept2');
    switch_to_blog(1);
    $cats = wp_get_post_categories($post_ID);
    restore_current_blog();

    $posts = cah_news_query(array(
        'dept' => $dept,
        'categories' => $cats,
        'per_page' => 4,
        'exclude' => $post_ID,
    ));

    $dept = is_array($dept) && count($dept) === 1 ? $dept[0] : 0;

    if ($posts) {
        echo '<h4>Related Posts</h4>';
        echo '<ul class="list-group list-group-flush">';
        foreach($posts as $post) {
            $link = $post->link;
            if ($dept) {
                $link = add_query_arg(['dept' => $dept], $link);
            }
            $link = esc_url($link);
            echo sprintf('<a class="nounderline" href="%s"><li class="small list-group-item list-group-item-action">%s</li></a>', $link, $post->title->rendered);
        }
        echo '</ul>';
    }
}

// Return to referrer
function referral($content='') {
    $ref_string = '<a href="%s" class="btn btn-default btn-sm mt-4">%s</a>';
    if (isset($_SERVER['HTTP_REFERER'])) {
        $ref_url = $_SERVER['HTTP_REFERER'];
        if (!preg_match('/archive/', $ref_url)) {
            $ref = sprintf($ref_string, $_SERVER['HTTP_REFERER'], '&laquo; Back to news');
            return $content . $ref;
        }
    }
    $ref = sprintf($ref_string, cah_news_get_news_link(), 'More news');

    return $content . $ref;
}

// Return URL to site's news page
function cah_news_get_news_link() {
    return get_home_url(null, get_option('cah_news_set_news_page', 'news'));
}

// Change page title (<head><title>...) to the correct blog
function cah_news_change_title()
{
    global $post_title;
    return $post_title . ' &raquo; ' . get_bloginfo('name');
}

function cah_news_share($title, $link) {
  ?>
                    <p class='d-inline-block text-muted'>Share</p>
                    <div class="ucf-social-icons d-inline-block">
                        <a class="ucf-social-link btn-facebook md color" rel="noopener" target="_blank"
                           href="https://www.facebook.com/sharer?u=<?= $link ?>&t=<?= $title ?>"
                           title="Share this post on Facebook">
                            <span class="fa fa-facebook" aria-hidden="true"></span>
                            <p class="sr-only">Share this post on Facebook</p>
                        </a>

                        <a class="ucf-social-link btn-twitter md color" rel="noopener" target="_blank"
                           href="http://twitter.com/intent/tweet?text=<?= $title ?>&amp;url=<?= $link ?>"
                           title="Share this post on Twitter">
                            <span class="fa fa-twitter" aria-hidden="true"></span>
                            <p class="sr-only">Share this post on Twitter</p>
                        </a>

                        <a class="ucf-social-link btn-youtube md color" rel="noopener" target="_blank"
                           href="mailto:?to=&body=You might find this article interesting: <?= $link; ?>&subject=<?= $title ?>"
                           title="Share this post by email">
                            <span class="fa fa-envelope" aria-hidden="true"></span>
                            <p class="sr-only">Share this post on Instagram</p>
                        </a>
                    </div>
    <?
}

function cah_news_fetch_footer($footer_id) {
    $url = esc_url(home_url());
    $endpoint = 'wp-json/wp-rest-api-sidebars/v1/sidebars/' . $footer_id;
    $request_url = $url . '/' . $endpoint;
    $response = wp_remote_get($request_url, array('timeout'=>5, 'reject_unsafe_url' => true,));
    if (is_wp_error($response)) {
        return '';
    }
    $body = json_decode(wp_remote_retrieve_body($response));
    if (isset($body->{'rendered'})) {
        return $body->{'rendered'};
    }
    return '';
}
    ?>
