<?php namespace nc;

/*
* GoogleCustomSiteSearch is the basis of our plugin and is responsible for building the search query, executing it via cURL
* and parsing the returned XML, which in turn is turned into an array of GoogleSearchResult and GoogleSearchPromotion objects
* which are responsible for the display of individual results returned from the Google Site Search API.
*/
class GoogleCustomSiteSearch{

  // Constants used for generating the Custom Site Search API query string.
  const CLIENT            = 'google-csbe';
  const OUTPUT_TYPE       = 'xml_no_dtd';
  const QUERY_BASE        = 'http://www.google.com/search?start=%d&num=%d&q=%s&client=%s&output=%s&cx=%s&gl=CA';
  const RESULTS_PER_PAGE  = 20;

  // Constants used for generating paging for multi-page result pets.
  const MAX_RESULTS_PAGES = 10;

  private $search_term;
  private $results_page;
  private $api_key;
  private $query;
  private $total_results;
  private $search_time;
  private $spelling_suggestion;
  private $results;

  // Generate a query based on class constants and provided parameters, used to contact the Google Custom Site Search API.
  private function build_query(){
    $query = sprintf(self::QUERY_BASE, ($this->results_page - 1) * self::RESULTS_PER_PAGE, self::RESULTS_PER_PAGE, urlencode($this->search_term), self::CLIENT, self::OUTPUT_TYPE, $this->api_key);
    return $query;
  }

  // Contact Google Custom Site Search API and then parse the response into a SimpleXML object and return it.
  private function get_response(){

    // Initialize cURL and set our options for this connection.
    $ch = curl_init($this->query);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HEADER, 0);

    // Execute our transaction and return false if we encounter any errors.
    $response = curl_exec($ch);
    if(curl_errno($ch)){
      return false;
    }

    // Cleanup our cURL object now that we are finished.
    curl_close($ch);

    // Attempt to turn our response into a SimpleXML object, return false if it fails.
    if(!$xml = simplexml_load_string($response)){
      return false;
    }

    return $xml;
  }

  // Parse our SimpleXML object into individual GoogleSearchPromotion or GoogleSearchResult objects.
  private function parse_response(&$response){

    $results = [];
    $promotions = [];
    $promotion_count = 0;

    // Check that our SimpleXML object contains a valid <RES> tag as part of the response before proceeding.
    if(isset($response->RES)){

      // Iterate through our <R> tags which are Results or Promotions and build the applicable object as warranted.
      foreach($response->RES->R as $result){
        if($this->is_promotion($result)){
          $title          = isset($result->T) ? $result->T : '';
          $url            = isset($result->SL_RESULTS->SL_MAIN->U) ? $result->SL_RESULTS->SL_MAIN->U : '';
          $summary        = isset($result->SL_RESULTS->SL_MAIN->BODY_LINE->BLOCK->T) ? $result->SL_RESULTS->SL_MAIN->BODY_LINE->BLOCK->T->asXML() : '';
          $promotions[]   = new GoogleSearchPromotion($title, $url, $summary);
          $promotion_count++;
        } else {
          $title      = isset($result->T) ? str_replace(" - Niagara Falls", "", $result->T) : '';
          $url        = isset($result->U) ? $result->U : '';
          $type       = isset($result['MIME']) ? $result['MIME'] : '';
          $summary    = isset($result->S) ? $result->S : '';
          $results[]  = new GoogleSearchResult($title, $url, $type, $summary);
        }
      }

    } else {
      if(isset($response->Spelling->Suggestion)){
        $this->spelling_suggestion  = isset($response->Spelling->Suggestion) ? (string)$response->Spelling->Suggestion['q'] : '';
      }
      return false;
    }

    // Parse the remaining search-related data we will be utilizing in displaying our search results.
    $this->total_results        = isset($response->RES->M) ? (int)$response->RES->M + $promotion_count : 0;
    $this->search_time          = isset($response->TM) ? round((float)$response->TM, 2, PHP_ROUND_HALF_UP) : 0;
    $this->spelling_suggestion  = isset($response->Spelling->Suggestion) ? (string)$response->Spelling->Suggestion['q'] : '';
    $this->results              = $promotions + $results;

    return true;
  }

  // Constructor.
  public function __construct($search_term, $results_page, $api_key){
    $this->search_term = $search_term;
    $this->results_page = $results_page;
    $this->api_key = $api_key;
    $this->query = $this->build_query();
  }

  // Check to ensure that we have results by ensuring result count is greater than zero.
  public function has_results(){
    return isset($this->results) ? (count($this->results) > 0) : false;
  }

  // Generate navigation based on total number of search results in conjunction with RESULTS_PER_PAGE and MAX_RESULTS_PAGES.
  public function display_navigation(){


    $total_pages = ceil($this->total_results / self::RESULTS_PER_PAGE);

    // For 0 or 1 pages, no navigation is needed. Return early.
    if($total_pages < 2){
        return '';
    }

    // Calculate the start, end and any applicable page numbers inbetween for use in the paging.
    if($this->results_page <= 5){
        $first_page = 1;
        $last_page = ($total_pages > self::MAX_RESULTS_PAGES) ? self::MAX_RESULTS_PAGES : $total_pages;
    } else if ($this->results_page > ($total_pages - 5)) {
        $first_page = $total_pages - self::MAX_RESULTS_PAGES;
        $last_page = $total_pages;
    } else {
        $first_page = $this->results_page - 5;
        $last_page = $this->results_page + 4;
    }

    // Generate the actual output of the paging to be displayed.
    ob_start();
    include_once(NC_GCSE_PLUGIN_PATH . 'parts/search_navigation.php');
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
  }

  // An error has occured or no search term was provided, display the applicable error(s).
  public function display_errors(){
    ob_start();
    include_once(NC_GCSE_PLUGIN_PATH . 'parts/search_error.php');
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
  }

  // Output our search results to screen.
  public function display_results(){
    ob_start();
    include_once(NC_GCSE_PLUGIN_PATH . 'parts/search_results.php');
    $content = ob_get_contents();
    ob_end_clean();
    return $content;
  }

    // Display our search suggestion in the event there's no valid results but a suggestion does exist.
    public function display_empty_results_with_spelling_suggestion(){
        ob_start();
        include_once(NC_GCSE_PLUGIN_PATH . 'parts/search_empty_results_with_spelling_suggestion.php');
        $content = ob_get_contents();
        ob_end_clean();
        return $content;
    }

  // Check for the existence of SL_RESULTS, which indicates if this result is a promotion or not.
  public function is_promotion(&$result){
    return isset($result->SL_RESULTS);
  }

  // Verify we have results, return them if we do.
  public function results(){
    if(!$this->results){
      return false;
    }
    return $this->results;
  }

  // A search is comprised of contacting the Google Custom Site Search API, then parsing the restulting response.
  public function search(){

    // If there's no valid query or search term, fail early.
    if($this->search_term == '' || !$this->query){
      return false;
    }

    if(!$response = $this->get_response()){
      return false;
    }

    if(!$this->parse_response($response)){
      return false;
    }

    return true;
  }

  // Generate display for how many results were found and how long it took to execute the search.
  public function search_stats(){
    $stats = '<div class="result-stats">';
    if($this->results_page > 1){
      $stats .= "<p><span class='result-page-number'>Page {$this->results_page}</span> of about {$this->total_results} results ({$this->search_time} seconds) for <b>{$this->search_term}</b></p>";
    } else {
      $stats .= "<p>About {$this->total_results} results ({$this->search_time} seconds) for <b>{$this->search_term}</b></p>";
    }
    $stats .= '</div><!--/result-stats-->';
    return $stats;
  }

    public function has_spelling_suggestion(){
        return ($this->spelling_suggestion == true);
    }

  // Generate a link to a spell-corrected version of the current search term.
  public function spelling_suggestion(){
    if($this->spelling_suggestion == false){
      return '';
    }
    $spelling  = '<div class="spelling">';
    $spelling .=  "<p><span>Did you mean: </span><a href='" . get_page_link() . "?q={$this->spelling_suggestion}'>{$this->spelling_suggestion}</a></p>";
    $spelling .= '</div><!--/spelling-->';
    return $spelling;
  }
}

/* GoogleSearchPromotion is for displaying a promoted result which has been entered for particular keyword(s) in the
* Google Custom Site Search administrative section. They utilize some different tags and so differ from the standard
* GoogleSearchResult as such.
*/
class GoogleSearchPromotion{

  private $title;
  private $url;
  private $summary;

  // Constructor.
  public function __construct($title, $url, $summary){
    $this->title    = $title;
    $this->url      = $url;
    $this->summary  = $summary;
  }

  // Generate the HTML for this promotion.
  public function display(){
    echo "<li class='promoted-result'>
        <h4><a href='{$this->url}'>{$this->title}</a></h4>
        <cite>{$this->url}</cite>
        <p>{$this->summary}</p>
    </li>";
  }

}

// GoogleSearchResult is for displaying a default search result returned by the Google Custom Site Search API.
class GoogleSearchResult{

  private $title;
  private $url;
  private $type;
  private $summary;

  public function __construct($title, $url, $type, $summary){
    $this->title    = $title;
    $this->url      = $url;
    $this->type     = (string)$type;
    $this->summary  = $summary;
  }

  // Generate the HTML for this result.
  public function display(){
    $type = $this->result_type();
    echo "<li>
        <h4>{$type}<a href='{$this->url}'>{$this->title}</a></h4>
        <cite>{$this->url}</cite>
        <p>{$this->summary}</p>
    </li>";
  }

  // Lookup for non-URL result types and return their applicable abbreviation/signature.
  private function result_type(){
    $mime_lookup_table = array(
        'application/pdf' 		=> 'PDF',
        'application/msword' 	=> 'DOC'
    );
    return isset($mime_lookup_table[$this->type]) ? '<span class="type">[' . $mime_lookup_table[$this->type] . ']</span>' : '';
  }

}

?>
