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
            $selected_dept_id = $term_dept->term_id;
        }
    }

    // Category
    if (isset($_GET['cat']) && is_numeric($_GET['cat'])) {
        $term_cat = get_term($_GET['cat'], 'category');

        if ($term_cat instanceof WP_Term) {
            $selected_cat = $term_cat->name;
            $selected_cat_id = $term_cat->term_id;
        }
    }
    ?>

    <!-- New filter form, dual filter possible -->
    <form class="form-inline mb-3" method="GET" action="<?= $action ?>">
        <!-- Department Select -->
        <select name="dept" class="btn btn-default btn-sm" style="background: lightgrey">
            <?
            // Condition checks if a department is selected, makes the
            // department the selected option.
            if ($selected_dept) {
                echo sprintf('<option value="%d" class="dropdown-menu">%s</option>', $selected_dept_id, $selected_dept);
            }
            else {
                // Default "all" option.
                echo '<option value="" class="dropdown-menu">All Departments</option>';
            }

            // Remains at the top of the select options when selecting other
            // departments.
            if ($dept->term_id !== $selected_dept_id) {
                echo '<option value="" class="dropdown-item">All Departments</option>';

                // Really janky select option divider.
                echo '<option class="dropdown-item" disabled>-----------</option>';
            }

            // Generates all of the other department options.
            foreach (get_option('cah_news_display_dept2') as $deptID) {
                $dept = get_term($deptID);
                $dept_id = $dept->term_id;
                
                // This prevents duplicate options.
                if ($dept_id === $selected_dept_id) {
                    continue;
                }
                else {
                    echo sprintf('<option name="dept" value="%d" class="dropdown-item">%s</option>', $dept_id, $dept->name);
                }
            }
            ?>
        </select>
        
        <!-- Category Select -->
        <select name="cat" class="btn btn-default btn-sm ml-3" style="background: lightgrey">
            <?
            // Condition checks if a category is selected, makes the category
            // the selected option.
            if ($selected_cat) {
                echo sprintf('<option value="%d" class="dropdown-menu">%s</option>', $selected_cat_id, $selected_cat);
            }
            else {
                // Default "all" option.
                echo '<option value="" class="dropdown-menu">All Categories</option>';
            }
            
            // Remains at the top of the select options when selecting other
            // categories.
            if ($cat->term_id !== $selected_cat_id) {
                echo '<option value="" class="dropdown-item">All Categories</option>';

                // Really janky select option divider.
                echo '<option class="dropdown-item" disabled>-----------</option>';
            }

            foreach ($cats as $cat) {
                // This prevents duplicate options.
                if ($cat->term_id === $selected_cat_id) {
                    continue;
                }
                else {
                    if (in_array($cat->name, $excluded_slugs)) continue;
                    echo sprintf('<option name="cat" value="%d" class="dropdown-item">%s</option>', $cat->term_id, $cat->name);
                }
            }
            ?>
        </select>
        
        <!-- Filter submit button -->
        <button class="btn btn-primary btn-sm ml-3" type="submit">Filter</button>

        <!-- Reset all filters button. It just redirects to the home page. -->
        <span class="input-group-addon ml-2"><a href="<?= cah_news_get_news_page_link() ?>">Reset</a></span>
    </form>
    <?
}

function test() {
    return;
}

?>
