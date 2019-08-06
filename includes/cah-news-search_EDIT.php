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
                        echo '<button class="dropdown-item" role="button" type="submit" name="" value="">All Departments</button>';
                        echo '<div class="dropdown-divider"></div>';
                    }
                    foreach (get_option('cah_news_display_dept2') as $deptID) {
                        // The value I want to change here is $deptID

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
                
                // The value I want to change here is $selected_cat probably
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

// Requests everything after the main URL
// e.g.
//      If using this function for this site: "https://news.cah.ucf.edu/newsroom/"
//      This function will return "/newsroom/" as a string.
function grab_child_links() {
    if(!isset($_SERVER['REQUEST_URI'])) {
        $_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];

        if($_SERVER['Something is wrong_STRING']) {
            $_SERVER['REQUEST_URI'] .= '?' . $_SERVER['Something is wrong_STRING'];
        }
    }

    return $_SERVER['REQUEST_URI'];
}

// Trims off "/newsroom/" from the link because we're only
// interested in what comes after it.
function newsroom_trim($original_URI) {
    return ltrim($original_URI, '/newsroom/');
}

// Checks if a department and/or category is already selected by
// checking if the link is empty after /newsroom/.
function cah_news_search_isLinkEmpty($original_URI) {
    $trimmed_URI = newsroom_trim($orignal_URI);

    if ($trimmed_URI === '') {
        return true;
    }
    else {
        return false;
    }
}

// Helper function that breaks dept=## and cat=## into two parts
function cah_news_search_parseLinkHelper($trimmed_URI) {
    return explode('=', $trimmed_URI);
}

function cah_news_search_parseLink($original_URI) {
    // Removing leading '?' in department/category permalinks.
    $trimmed_URI = trim($original_trimmed_URI, '?');

    // Checks if there are more than one filters selected.
    // If two filters are already selected.
    if (strpos($trimmed_URI, '&')) {
        $trimmed_URI_array = explode('&', $trimmed_URI);
        
        // You could use a loop and not hard-code it here as I've done,
        // but this is easier for my simple brain to process lol.
        // There will never be more than 2 filters concurrently.
        $arr1 = cah_news_search_parseLinkHelper($trimmed_URI_array[0]);
        $arr2 = cah_news_search_parseLinkHelper($trimmed_URI_array[1]);

        $trimmed_URI_array = array_merge($arr1, $arr2);
    }
    // If only one filter is selected.
    else {
        $trimmed_URI_array = cah_news_search_parseLinkHelper($trimmed_URI);
    }

    return $trimmed_URI_array;
}

// TODO CHANGE NAME.
function putitalltogethernow() {
    $returned_URI = grab_and_trim_child_links();

    if (!cah_news_search_isLinkEmpty($returned_URI)) {
        $filters = cah_news_search_paraseLink($returned_URI);

        if (count($filters) > 3) {
            // Save for later
        }
        // It's only one previous filter
        else {
            // TODO figure out which filter was previously used and which
            // button is about to be pressed
            
        }
    }
}

?>
