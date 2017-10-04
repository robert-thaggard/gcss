<?php
/*
   Plugin Name: NC Google Custom Search Engine
   Plugin URI: http://wordpress.org/extend/plugins/nc-google-custom-site-search/
   Version: 1.0
   Author: Robert Thaggard
   Description:
   Text Domain: nc-google-custom-site-search
   License: GPLv3
*/

// These constants are used to find the PATH and URL to our plugin's assets on multi-site installs.
define('NC_GCSE_PLUGIN_PATH', plugin_dir_path(__FILE__));
define('NC_GCSE_PLUGIN_URL', plugin_dir_url(__FILE__));

/* To prevent the CSS from being appended well after page load and causing a 'flash'
* we search for the presence of the shortcode earlier in Wordpress' call chain and
* enqueue the CSS.
*/
add_action('wp_enqueue_scripts', function(){
  global $post;
	if(is_a( $post, 'WP_Post') && has_shortcode($post->post_content, 'nc_gcse')){
		wp_enqueue_style('nc-google-search', NC_GCSE_PLUGIN_URL . 'css/nc-google-search.css');
	}
});

/* This filter replaces the default Wordpress one in favor of our own which supports
* our custom search setup. This uses a custom 'q' query variable as opposed to the
* standard 's' used by Wordpress to circumvent the internal Wordpress Search mechanism
* in favor of our own to deliver more consistent results and reduce some overhead.
*/
add_filter('get_search_form', function() {
  $action_url         = home_url( '/search-results/' );
  $screen_reader_text = _x( 'Search for:', 'label' );
  $placeholder_text   = esc_attr_x( 'Search …', 'placeholder' );
  $placeholder_value  = isset($_GET['q']) ? esc_attr(stripslashes($_GET['q'])) : '';
  $search_title       = esc_attr_x( 'Search for:', 'label' );
  $submit_value       = esc_attr_x( 'Search', 'submit button' );
  return "
    <div class='search-box'>
      <form role='search' method='get' class='search-form' action='{$action_url}'>
        <label>
          <span class='screen-reader-text'>{$screen_reader_text}</span>
          <input type='search' id='search-input' class='search-field shiftnav-search-input' placeholder='{$placeholder_text}' value=\"{$placeholder_value}\" name='q' title='{$search_title}' />
        </label>
          <input type='submit' class='search-btn' value='{$submit_value}' />
      </form>
    </div><!--/search-box-->
  ";
});

/* The [nc_gcse] shortcode is used in favor of hooking 'the_content' to minimize
* the risk of other plugins interfering or outright blocking our attempts to run on
* the /search-results/ page which we utilize for our search mechanism instead of the
* usual Wordpress templates. This gives us more explicit control over how the
* search process runs and what points of failure are introduced by the filter/action
* system in Wordpress.
*/
add_shortcode('nc_gcse', function() {

  // Safely retrieve our query variables and provide sane defaults if they are not available.
  $search_term = isset($_GET['q']) ? esc_attr(stripslashes($_GET['q'])) : '';
  $results_page = isset($_GET['results_page']) ? esc_attr((int)$_GET['results_page']) : 1;

  include_once(NC_GCSE_PLUGIN_PATH . 'inc/GoogleCustomSiteSearch.inc.php');

  // Create our GoogleCustomSiteSearch object.
  $google_search = new nc\GoogleCustomSiteSearch($search_term, $results_page, '005200784774386614563:ymm8zrvnepk');

  /* Execute our search and check if any results were found. If they were, enqueue
  * our javascript file which facilitates the use of AJAX-based paging.
  */
  if($google_search->search() && $google_search->has_results()){
    wp_enqueue_script('nc_google_cse', NC_GCSE_PLUGIN_URL . 'js/nc_google_cse.js', array('jquery'));
    wp_localize_script('nc_google_cse', 'nc_gcse',
        array(
            'ajaxurl'       => admin_url('admin-ajax.php'),
            'current_page'  => $results_page,
            'search_term'   => $search_term
        )
    );

    // Display our retrieved results.
    return $google_search->display_results();
  } else {
    if ($google_search->has_spelling_suggestion()) {
        return $google_search->display_empty_results_with_spelling_suggestion();
    } else {
        return $google_search->display_errors();
    }
  }
});


// Register our AJAX event for returning results from the search paging.
add_action('wp_ajax_nopriv_nc_gcse_get_results', 'nc_gcse_get_results');
add_action('wp_ajax_nc_gcse_get_results', 'nc_gcse_get_results');

/* This is the AJAX form of our shortcode function, used by the javascript library
* to retrieve new results and paging to display on the front end.
*/
function nc_gcse_get_results(){
    include_once(NC_GCSE_PLUGIN_PATH . 'inc/GoogleCustomSiteSearch.inc.php');
    if(isset($_POST['results_page']) && isset($_POST['search_term'])){
        $google_search = new nc\GoogleCustomSiteSearch($_POST['search_term'], $_POST['results_page'], '005200784774386614563:ymm8zrvnepk');
        if($google_search->search() && $google_search->has_results()){
            echo $google_search->display_results();
        } else {
            if ($google_search->has_spelling_suggestion()) {
                echo $google_search->display_empty_results_with_spelling_suggestion();
            } else {
                echo $google_search->display_errors();
            }
        }
    }
    // This is used to ensure that the script does not hangs and the AJAX results are returned promptly.
    wp_die();
}
?>
