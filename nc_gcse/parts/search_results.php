<div class="search-results-wrapper">
    <div class="search-header">
        <?php get_search_form(); ?>
        <?php echo $this->search_stats(); ?>
        <?php echo $this->spelling_suggestion(); ?>
    </div><!--/search-header-->
    <div class="search-results-inner">
        <div class="row">
            <div class="columns large-8">
                <!-- Search Results -->
                <ol class="search-results-list">
                  <?php foreach($this->results as $result) {
                    $result->display();
                  } ?>
                </ol>
                <?php echo $this->display_navigation(); ?>
            </div><!--/col-->
            
            <div class="columns large-4 extra-search-options">
                <a href="/programs/" class="sl-button find-a-program">Find a Program</a>
                <a href="http://www.niagaracollege.ca/parttimestudies/registration/" class="sl-button find-pt-courses">Find a Part-Time Studies Course</a>
                <a href="/requestinfo/" class="sl-button request-info">Request Information</a>
                <a href="/infocentre/" class="sl-button infocentre">Contact the InfoCentre</a>
                
                <div class="escalation">
                    <h3>Having trouble finding an answer?</h3>
                    <a href="http://www.niagaracollege.ca/search-results/ask-nc/" class="button expand radius">Ask Us a Question</a>
                </div><!--/escalation-->
                
            </div><!--/col-->
        </div><!--/row-->
    </div><!--/search-results-inner-->
</div><!--/search-results-wrapper-->
