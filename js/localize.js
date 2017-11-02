var hrefs = [];
var country = "";

jQuery(document).ready(function(){
  "use strict";
  if(window.location.href.indexOf("wp-admin") == -1) {
     jQuery('body')
      .find('a[href*="amazon"], a[href*="amzn.to"]')
          .each(function() {
              hrefs.push(this.href);
          })
    getUrls(hrefs);
  }
  function getUrls(hrefs){
    if (hrefs.length != 0){
      jQuery.ajax({
          type:"POST",
          url: myAjax.ajax_url,
          data: {
            action: "get_flovidy_urls",
            urls: hrefs,
          },
          success:function(data){
           JSON.parse(data).forEach(function(one){
            jQuery('body').find('a[href="'+one['us_link']+'"]').attr("href", one['new_link']).attr('rel','nofollow');
           })
          },
          error: function(errorThrown){
            console.log(errorThrown);
          } 

      });
    }
  }



})

