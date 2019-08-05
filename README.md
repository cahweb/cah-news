# CAH-News-Plugin
Wordpress plugin for the UCF College of Arts and Humanities. CAH contains many departments, each with its own Wordpress instance in the multisite. To simplify the process of adding news posts, a central site was created, from which the individual department sites can pull content. 

* This plugin creates a 'Department' taxonomy. When publishing a news story on the main site, multiple departments can be selected on which to display the article.
* The departments that are displayed on a site can be set in the Tools menu on the dashboard. 
* A department taxonomy can be applied to all of the news posts without a set department in the same menu.

## Dependencies
This plugin is intented for use with the CAH-UCF department sites. The **Athena framework** must be active, as this plugin depends on it's style classes and the included Javascript libraries to function correctly. 

## Shortcodes
The **[cah-news]** shortcode can be used to display a paginated list of news posts from the main site. Only news posts with the department taxonomies set in Tools>CAH News admin page are displayed. The attribute 'view' determines the layout to be displayed:
* view='full' shows a paginated list of all available news items with a search bar and category selection
* 'view='preview' shows 3 news posts and linkto the full listing

The **[cah-news-search] ** shortcode can accompany the main shortcode. It displays a search bar and a category selection dropdown to filter news posts.

## Installation and Usage
To get started with a new department site:
* Enable plugins: 
  * CAH News Plugin (this plugin)
  * common-news (defines the 'news' custom post type)
  * Export media with selected content (allows featured images of posts to be exported)
  * WP REST API Sidebars (exposes sidebar content to API to allow main news site to mimic child sites' footers)
* Export news posts
  * Tools>Export
  * Choose 'News' and select the option to export media content
* Import news posts to news.cah.ucf.edu
  * Tools>Import>Wordpress>Run Importer
  * Select the XML file generated in the previous step, check to import media, and perform import
* Apply Department taxonomy
  * Go to the CAH-News Options page (under Tools in the Dashboard)
  * Check the box to "Apply Department taxonomy to this site's _ uncategorized news posts"
  * Enter the full name of the department to use (a list of existing departments will appear as a dropdown)
  * Click 'Submit'
* Configure the child site (see next section)

## Configuration Options
Options are available under Tools >> CAH News Options in the Dashboard. 

* **Departments:** Select department(s) to display on news page by checking boxes in the 'Departments' table
* **News Page:** Choose a page to display news on
  * Ensure that the chosen page has the '[cah-news view="full"]' shortcode in its contents
  * The selected page template for that file must include the necessary Javascript. Recommended template: 'Full Width'

## Admin Features
* Filter news posts by department taxonomy in table
* 'Edit News Post' link in admin toolbar which links to correct editing page
* Meta box on editing page which links to specific department instances of news post
* Meta box on editing page to determine which department sites to publish news post to 

