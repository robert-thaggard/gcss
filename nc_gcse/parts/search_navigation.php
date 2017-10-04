<div class="search-pagination">
    <ul>
        <?php if($this->results_page > 1){ ?>
            <li class="search-prev"><a class="ncgcse-prev" href="<?php echo get_permalink(); ?>?q=<?php echo $this->search_term; ?>&amp;results_page=<?php echo ($this->results_page - 1); ?>">Previous</a></li>
		    <?php } ?>
        <?php foreach(range(max($first_page, 0), $last_page) as $number) { ?>
            <?php if($this->results_page == $number){ ?>
				          <li class="cur"><a class="ncgcse-page" href="<?php echo get_permalink(); ?>?q=<?php echo $this->search_term; ?>&amp;results_page=<?php echo $number; ?>"><?php echo $number; ?></a></li>
			      <?php } else { ?>
				          <li><a class="ncgcse-page" href="<?php echo get_permalink(); ?>?q=<?php echo $this->search_term; ?>&amp;results_page=<?php echo $number; ?>"><?php echo $number; ?></a></li>
			      <?php } ?>
		    <?php } ?>
        <?php if($this->results_page < $total_pages) { ?>
			<li class="search-next"><a class="ncgcse-next" href="<?php echo get_permalink(); ?>?q=<?php echo $this->search_term; ?>&amp;results_page=<?php echo ($this->results_page + 1)?>">Next</a></li>
		<?php } ?>
    </ul>
</div><!--/search-pagination-->
