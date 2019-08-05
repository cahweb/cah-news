<?php

$msg = '';

add_action('admin_init', 'cah_news_options_update');
// Process options in POST to 
function cah_news_options_update() {
    global $msg;
    if (isset($_POST['doSetDept']) && $_POST['doSetDept'] == 'on' && !empty($_POST['setDept'])) {
        $setDept = $_POST['setDept'];
        $count = bulk_apply_dept_tax($setDept);
        $msg .= "Setting department to " . $setDept . " on " . $count . " posts";
    }
}

add_action('admin_menu', 'cah_news_register_options_page');
function cah_news_register_options_page() {
    $optionsTitle = 'CAH News Options';
    $parentMenu = 'tools.php';
    $capability = 'manage_options'; 
    $slug = 'cah_news_options';
    $callback = 'cah_news_options_page'; 
    add_submenu_page($parentMenu, $optionsTitle, $optionsTitle, $capability, $slug, $callback);
}

add_action('admin_init', 'setup_cah_news_settings');
function setup_cah_news_settings() {
    // Table view
    add_settings_section('cah_news_display_dept2_section',
                        'Display Options',
                        function($_) { echo 'Select department(s) to display on news page.'; },
                        'cah_news_options');
    add_settings_field('cah_news_display_dept2',
                        'Departments',
                        'cah_news_display_dept2_field',
                        'cah_news_options',
                        'cah_news_display_dept2_section'); 
    register_setting('cah_news_display_dept2_section', 'cah_news_display_dept2'); 

    add_settings_field('cah_news_set_news_page',
                        'News Page',
                        'cah_news_set_news_page_field',
                        'cah_news_options',
                        'cah_news_display_dept2_section');
    register_setting('cah_news_display_dept2_section', 'cah_news_set_news_page'); 

}

function cah_news_set_news_page_field() {
    $field_title = 'cah_news_set_news_page';
    $value = get_option($field_title);
    if ($value) {
        $post = sprintf('<a href="%s">Link</a>', home_url($value)); 
        echo home_url() . '/';
    }
    else {
        $post = '(unset)'; 
        $value = 'news'; 
    }
    ?>
    <input type='text', name='<?= $field_title ?>' value='<?= $value ?>'>
    <?
    echo $post; 
}

// Table view of departments to select for display 
function cah_news_display_dept2_field($args) {
    $field_title = 'cah_news_display_dept2'; 
    $field_value = get_option($field_title) ? get_option($field_title) : array(); 
    $field_name = $field_title . '[]'; 
    ?>
    <style>
        table#deptTable {
            border-collapse: collapse; 
        }
        table#deptTable th {
            padding-left: 10px; 
        }
        table#deptTable td, table#deptTable th {
            width: auto !important;
            white-space: nowrap;
            border: 1px solid black; 
        }
    </style>
        <table id='deptTable'>
            <tr>
                <th><input type="checkbox" onClick="toggle(this, '<?=$field_name?>')"></th>
                <th>Department</th>
                <th>ID</th>
                <th>Slug</th>
                <th>Posts</th>
                <th>Blog ID</th>
            </tr>
            <?
           
            foreach(get_departments() as $dept) {
                $checked = in_array($dept->term_id, $field_value) ? 'checked=checked' : '';
            ?>
                <tr>
                    <td><input type="checkbox" name="<?=$field_name?>" value="<?=$dept->term_id?>" <?=$checked?> ></td>
                    <td><? echo $dept->name; ?></td>
                    <td><? echo $dept->term_id; ?></td>
                    <td><? echo $dept->slug; ?></td>
                    <td><? echo $dept->count; ?></td>

                    <td><?= cah_news_get_blog_id($dept->term_id) ?></td>
                </tr>
            <?
            }
            ?>

        </table>

        <script>
            // Toggle all checkboxes 
            function toggle(source, name) {
                let checkboxes = document.getElementsByName(name);
                // let checkboxes = document.forms['cah-news'].elements[name];
                for(let i=0; i<checkboxes.length; i++) {
                    let cb = checkboxes[i]; 
                    cb.checked = source.checked; 
                }
            }
        </script>
    <?
}

// Option to bulk set current uncategorized posts (handled in POST, not a setting)
function cah_news_set_dept_field() {
    ?>
    <h2>Set Department</h2>
    <input type="checkbox" name="doSetDept" id="setDept">
    <label for="setDept">Apply Department taxonomy to this site's <b><? echo count(get_uncategorized_news()); ?></b>  uncategorized news posts:</label>

    <input list="deptList" name="setDept" id="setDept" autocomplete="off">
    <datalist id="deptList">
    <?
    $deptOptions = array_column(get_departments(), 'name'); 
    $currentBlog = get_bloginfo('name'); 
    if (!in_array($currentBlog, $deptOptions)) {
        echo sprintf('<option value="%s">%s</option>', $currentBlog, $currentBlog); 
        echo '<option disabled="disabled" value="──────────">──────────</option>';
    }
    foreach($deptOptions as $deptName) {
        // echo sprintf('<option value="%s">%s</option>', $dept->name, $dept->name); 
        echo sprintf('<option value="%s">%s</option>', $deptName, $deptName); 
    }
    ?>
    </datalist>
    <?
}

// Form to set CAH News plugin options
function cah_news_options_page() {
    ?>
    <div class="wrap">
        <?
        global $msg;
        echo '<div> ' . $msg . '</div>';
        ?>

        <h1>CAH News Options</h1>

        <form method="post" action="options.php" id='cah-news'>
            <? 
            settings_fields('cah_news_display_dept2_section');
            do_settings_sections('cah_news_options');
            if (get_current_blog_id() == 1) {
                cah_news_set_dept_field();
            }
            submit_button();
            ?>

            <hr>
        </form>
    </div>
<?php
}

?>