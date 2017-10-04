<div class="search-results-wrapper">
    <div class="search-header">
      <?php get_search_form(); ?>
    </div><!--/search_header-->
    <div class="search-results-inner">
      <div class="no-results row">
        <div class="columns large-3 medium-4 hide-for-small-only">
            <img src="/wp-content/themes/wpf-niagara-college-core/img/oops-birds.png" />
        </div><!--/col-->
        
        <div class="columns large-9 medium-8">
            <h4>Sorry!</h4>
            <p class="l">We were unable to find any results for <b><?php echo $this->search_term; ?></b>.</p>
            <?php echo $this->spelling_suggestion(); ?>
            <div class="look-elsewhere">
                <hr />
                <h5>Can't find what you're looking for?</h5>
                <ul class="arrow-list">
                    <li><span>Search Again:</span> Use the search box above to try your search again with a different term. <em>"If at first you don't succeed..."</em></li>
                    <li><a href="/programs"><span>Find a Program:</span></a> Trying to find a Niagara College program? Check out our new <a href="/programs">Program Finder</a>!</li>
                    <li><a href="http://www.niagaracollege.ca/parttimestudies/registration/"><span>Find a Part-Time Studies Course:</span></a> Looking for a Part-Time Studies course? Use our handy <a href="http://www.niagaracollege.ca/parttimestudies/registration/">Course Finder</a> tool.</li>
                    <li><a href="/infocentre/"><span>Contact the InfoCentre:</span></a> Still no luck? Our friendly <a href="/infocentre/">InfoCentre staff</a> will be happy to help you.</li>
                </ul>
            </div><!--/look-elsewhere-->
        </div><!--/col-->
          
      </div><!--/no-results row-->
    </div><!--/search-results-inner-->
  </div><!--/search-results-wrapper-->
