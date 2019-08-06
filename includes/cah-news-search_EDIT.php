<?

// Search bar
function cah_news_search() {
    $search_query = isset($_GET['search']) ? esc_attr($_GET['search']) : '';
    $action = cah_news_get_news_page_link();
    ?>
    
    <form role="search" method="get" id="search-form" class="mb-3" action="<?= $action ?>">
        <div class="input-group">
            <input type="search" placeholder="Show me news on..." name="search" class="form-control" id="search-input" value="<?= $search_query ?>" aria-label="Search for news"/>
            <span class="input-group-btn">
                <button class="btn btn-primary" type="submit" role="button" aria-label="Submit search">
                    <i class="fa fa-search"></i>
                </button>
            </span>
            <span class="input-group-addon"><a href="<?= cah_news_get_news_page_link() ?>">Reset</a></span>
        </div>
    </form>

    <?
    echo cah_news_search_isLinkEmpty();
    cah_news_search_filter();
}

function cah_news_search_filter() {
    if (get_current_blog_id() !== 1 && empty($cats)) {
        return;
    }
    
    $selected_dept = '';
    $selected_cat = '';
    $action = cah_news_get_news_link();
    
    // Category specific variables
    $excluded_slugs = ['Degree', 'Discipline', 'Graduate', 'Minor', 'Undergraduate',];
    $args = [
        'taxonomy' => 'category',
        'hide_empty' => true,
    ];
    $cats = get_terms($args);

    // Department
    if (isset($_GET['dept']) && is_numeric($_GET['dept'])) {
        $term_dept = get_term($_GET['dept'], 'dept');
        
        if ($term_dept instanceof WP_Term) {
            $selected_dept = $term_dept->name;
        }
    }

    // Category
    if (isset($_GET['cat']) && is_numeric($_GET['cat'])) {
        $term_cat = get_term($_GET['cat'], 'category');

        if ($term_cat instanceof WP_Term) {
            $selected_cat = $term_cat->name;
        }
    }
    ?>

    <form class="d-inline-block" method="GET" action="<?= $action ?>">
        <div class="input-group mb-3">
            <!-- Department Dropdown Button -->
            <div class="dropdown">
                <?
                $btn = '<button class="btn btn-primary btn-sm dropdown-toggle" role="button" type="button" id="deptDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">%s</button>';
                $btn_text = 'All Departments';
                
                if ($selected_dept) {
                    $btn_text = $selected_dept;
                }
                echo sprintf($btn, $btn_text);
                ?>

                <div class="dropdown-menu" aria-labelledby="deptDropdown">
                    <?
                    if ($selected_dept) {
                        echo '<button class="dropdown-item" role="button" type="submit" name="dept" value="">All Departments</button>';
                        echo '<div class="dropdown-divider"></div>';
                    }
                    foreach (get_option('cah_news_display_dept2') as $deptID) {
                        $dept = get_term($deptID);
                        $dept_id = $dept->term_id;
                        echo sprintf('<button class="dropdown-item" role="button" type="submit" name="dept" value="%d">%s</button>', $dept_id, $dept->name);
                    }
                    ?>
                </div>
            </div>

            <!-- Category Dropdown Button -->
            <div class="dropdown ml-2">
                <?
                $btn = '<button class="btn btn-primary btn-sm dropdown-toggle" role="button" type="button" id="catDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">%s</button>';
                $btn_text = 'All Categories';

                if ($selected_cat) {
                    $btn_text = $selected_cat;
                }
                echo sprintf($btn, $btn_text);
                ?>

                <div class="dropdown-menu" aria-labelledby="catDropdown">
                    <?
                    if ($selected_cat) {
                        echo '<button class="dropdown-item" role="button" type="submit" name="cat" value="">All Categories</button>';
                        echo '<div class="dropdown-divider"></div>';
                    }
                    foreach ($cats as $cat) {
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

function cah_news_search_isLinkEmpty() {
    // Requests current URI
    if(!isset($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];

        if($_SERVER['Something is wrong_STRING']) {
            $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['Something is wrong_STRING'];
        }
    }

    $original_URI = $_SERVER['REQUEST_URI'];

    // Trim off '/newsroom/' from the link
    $trimmed_URI = ltrim($original_URI, '/newsroom/');

    // Checks if a department and/or category is already selected by
    // checking if the link is empty after /newsroom/.
    if ($trimmed_URI === '') {
        return true;
    }
    else {
        return false;
    }
}

?>
