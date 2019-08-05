<?

// Search bar
function cah_news_search() {
    $search_query = isset($_GET['search']) ? esc_attr($_GET['search']) : '';
    $action = cah_news_get_news_page_link();
    ?>
    <form role="search" method="get" id="search-form" class="mb-3" action="<?= $action ?>">
        <div class="input-group">
            <input type="search" placeholder="Show me news on..." name="search" class="form-control" id="search-input" value="<?= $search_query ?>" aria-label="Search for news"/>
            <!-- <input class="screen-reader-text" type="submit" id="search-submit" value="Search" /> -->
            <span class="input-group-btn">
                <button class="btn btn-primary" type="submit" role="button" aria-label="Submit search">
                    <i class="fa fa-search"></i>
                </button>
            </span>
            <span class="input-group-addon"><a href="<?= cah_news_get_news_page_link() ?>">Reset</a></span>
        </div>
    </form>
    <?
    cah_news_select_department();
    cah_news_select_category();
}

// Department select dropdown
function cah_news_select_department() {
    if (get_current_blog_id() !== 1) return;
    $selected = '';
    if (isset($_GET['dept']) && is_numeric($_GET['dept'])) {
        $term = get_term($_GET['dept'], 'dept');
        if ($term instanceof WP_Term) {
            $selected = $term->name;
        }
    }
    ?>
    <form class="d-inline-block" method="GET" action="<?= cah_news_get_news_link() ?>">
        <div class="input-group">
<!--            <span class="input-group-addon">Departments: </span>-->
            <div class="dropdown">
                <?
                $btn = '<button class="btn btn-primary btn-sm dropdown-toggle" role="button" type="button" id="deptDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">%s</button>';
                $btn_text = 'All Departments';
                if ($selected) {
                    $btn_text = $selected;
                }
                echo sprintf($btn, $btn_text);
                ?>
                <div class="dropdown-menu" aria-labelledby="deptDropdown">
                <?
                if ($selected) {
                    echo '<button class="dropdown-item" role="button" type="submit" name="dept" value="">All Departments</button>';
                    echo '<div class="dropdown-divider"></div>';
                }
                foreach(get_option('cah_news_display_dept2') as $deptID) {
                    $dept = get_term($deptID, 'dept');
                    $dept_id = $dept->term_id;
                    echo sprintf('<button class="dropdown-item" role="button" type="submit" name="dept" value="%d">%s</button>', $dept_id, $dept->name);
                }
                ?>
                </div>
            </div>
        </div>
    </form>
    <?
}

// Category select dropdown
function cah_news_select_category() {
    // $excluded = [
    //         1,                      // Uncategorized
    //         8, 9, 12, 7, 13, 11     // used for Academics pages, not news
    // ];
    $excluded_slugs = [
        'Degree', 'Discipline', 'Graduate', 'Minor', 'Undergraduate',
    ];

    $args = [
        'taxonomy' => 'category',
        'hide_empty' => true,
        // 'exclude' => $excluded,
    ];
    $cats = get_terms($args);
    if (empty($cats)) return;

    $selected = '';
    if (isset($_GET['cat']) && is_numeric($_GET['cat'])) {
        $term = get_term($_GET['cat'], 'category');
        if ($term instanceof WP_Term) {
            $selected = $term->name;
        }
    }

    if (isset($_GET['dept'])) {
        $args = ['dept', esc_attr($_GET['dept'])];
    }
    else {
        $args = [];
    }
    $action = cah_news_get_news_link();
    ?>

    <form class="d-inline-block" method="GET" action="<?= $action ?>">
        <div class="input-group pb-4">
<!--            <span class="input-group-addon">Categories: </span>-->
            <div class="dropdown">
                <?
                $btn = '<button class="btn btn-primary btn-sm dropdown-toggle" role="button" type="button" id="catDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">%s</button>';
                $btn_text = 'All Categories';
                if ($selected) {
                    $btn_text = $selected;
                }
                echo sprintf($btn, $btn_text);
                ?>
                <div class="dropdown-menu" aria-labelledby="catDropdown">
                    <?
                    if ($selected) {
                        echo '<button class="dropdown-item" role="button" type="submit" name="cat" value="">All Categories</button>';
                        echo '<div class="dropdown-divider"></div>';
                    }
                    foreach($cats as $cat) {
                        if (in_array($cat->name, $excluded_slugs)) continue;
                        echo sprintf('<button class="dropdown-item" role="button" type="submit" name="cat" value="%d">%s</button>', $cat->term_id, $cat->name);
                    }
                    ?>
                </div>
            </div>
        </div>
    </form>
    <?
}

?>
