<?

add_shortcode('cah-news', 'cah_news_shortcode');

// Display news
function cah_news_shortcode($atts) {
    $atts = shortcode_atts(array(
        'dept' => array(),
        'limit' => -1,
        'per_page' => 20,
        'view' => 'full',
        'cat'  => array(),
        'exclude' => array(),
    ), $atts);

    if ($atts['view'] == 'preview') {
        echo '<h2 class="h1" >' . "In the News" . '</h2>';
        cah_news_get_news(3, false);
        $news_page = cah_news_get_news_page_link();
        echo sprintf('<a class="btn btn-primary" href="%s">More News</a><br>', $news_page);
    }
    else {
        echo '<div class="container">';
        cah_news_search();
        cah_news_get_news($atts['per_page'], true);
        echo '</div>';
    }
}

add_shortcode('cah-news-search', 'cah_news_search_shortcode');

// Display dropdown selection to filter news
function cah_news_search_shortcode() {
    cah_news_search();
}

?>