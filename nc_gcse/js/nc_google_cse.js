/* Whenever we replace the existing search/navigation display from an AJAX call,
* make sure the new elements are registered for the appropriate events.
*/
jQuery(document).ready(function() {
    nc_gcse_register_events();
});

/* Register our paging elements for click and setup our AJAX call to get the
* set of results which we will then display before returning the browser window
* to the top.
*/
function nc_gcse_register_events(){
    jQuery(".search-pagination a").click(function(e){

        if(jQuery(this).hasClass('ncgcse-prev')){
            nc_gcse.current_page--;
        }

        if(jQuery(this).hasClass('ncgcse-page')){
            nc_gcse.current_page = jQuery(this).html();
        }

        if(jQuery(this).hasClass('ncgcse-next')){
            nc_gcse.current_page++;
        }

        jQuery.ajax({
            type: 'POST',
            url: nc_gcse.ajaxurl,
            data: {
                'action': 'nc_gcse_get_results',
                'results_page': nc_gcse.current_page,
                'search_term': nc_gcse.search_term
            },
            success: function(data, textStatus, XMLHttpRequest){
                jQuery("div.search-results-wrapper").replaceWith(data);
                jQuery("html, body").animate({ scrollTop: 0 }, 0);
                nc_gcse_register_events();
            },
            error: function(XMLHttpRequest, textStatus, errorThrown){
                //console.log(errorThrown + ' ' + textStatus);
            }
        });
        e.preventDefault();
    });
}
