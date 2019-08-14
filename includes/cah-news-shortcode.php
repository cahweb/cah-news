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
        'section_title' => "In the News",
        'section_title_classes' => '',
        'button_text' => "More News",
        'button_classes' => ''
    ), $atts);

    $section_title = $atts['section_title'];
    $section_title_classes = $atts['section_title_classes'];
    $button_text = $atts['button_text'];
    $button_classes = $atts['button_classes'];

    if ($atts['view'] == 'preview') {
        echo '<h2 class="h1 ' . $section_title_classes . '">' . $section_title . '</h2>';
        cah_news_get_news(3, false);
        $news_page = cah_news_get_news_page_link();
        echo sprintf('<a class="btn btn-primary ' . $button_classes . '" href="%s">' . $button_text . '</a><br>', $news_page);
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