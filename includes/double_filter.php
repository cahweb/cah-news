
function pass(){
    $test = [];
    if (isset($_GET['dept'])) {
        $query['search'] = esc_attr($_GET['search']);
    }
    if (isset($_GET['cat'])) {
        $query['search'] = esc_attr($_GET['search']);
    }
    return esc_url(add_query_arg($args, get_home_url(null, get_option('cah_news_set_news_page', 'news'))));
    
}

function double_search(){
    if (get_current_blog_id() !== 1) return;
    $selected = '';
    if (isset($_GET['dept']) && is_numeric($_GET['dept'])) {
        $term = get_term($_GET['dept'], 'dept');
        if ($term instanceof WP_Term) {
            $selected = $term->name;
        }
    }

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
<form class="d-inline-block" method="GET" action="<?= pass() ?>">
<div class="input-group">
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
                    ?>
                    <label><input class = "dropdown-item" type = "radio" name = "dept" value = "<?=$dept_id?>">test</label>
                    <?
                }
                ?>
                </div>
    </div>
</div> <!--inpit group-->
<div class="input-group pb-4">
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
                        ?>
                        <label><input class = "dropdown-item" type = "radio" name = "cat" value = "<?=$cat->term_id?>">Category</label><?
                        //echo sprintf('<button class="dropdown-item" role="button" type="submit" name="cat" value="%d">%s</button>', $cat->term_id, $cat->name);
                    }
                    
                    ?>
</div>
</div>


</div><!--inpit group-->
<button role="button" type="submit" name="submit" value="submit">SUBMIT</button>
</form>
<?
}


?>
