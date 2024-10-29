jQuery(document).on('click', '.zt_dcf_form_read a[data-id]', function(event) {
    event.preventDefault();
    var cf_id = jQuery(this).attr('data-id');
    var cf_status = jQuery(this).attr('data-status');
    var me = jQuery(this);
    
    jQuery.ajax({
        url: ztcfd_admin_ajax_object.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {action: 'change_status', id: cf_id, status: cf_status},
        success: function(data){
            if(cf_status == 1)
            {
                me.attr("data-status",0);
                me.find('span').removeClass("dashicons-buddicons-pm");
                me.find('span').addClass("dashicons-email-alt");
            }
            else
            {
                me.attr("data-status",1);
                me.find('span').removeClass("dashicons-email-alt");
                me.find('span').addClass("dashicons-buddicons-pm");
            }
            location.reload();
        }
    });

});
jQuery(document).on('click', '.zt_dcf_form_bookmark a[data-id]', function(event) {
    event.preventDefault();
    var cf_id = jQuery(this).attr('data-id');
    var cf_status = jQuery(this).attr('data-status');
    var me = jQuery(this);

    jQuery.ajax({
        url: ztcfd_admin_ajax_object.ajax_url,
        type: 'POST',
        dataType: 'json',
        data: {action: 'change_bookmark', id: cf_id, status: cf_status},
        success: function(data){
            if(cf_status == 1)
            {
                me.attr("data-status",0);
                me.find('span').removeClass("dashicons-star-filled");
                me.find('span').addClass("dashicons-star-empty");
            }
            else
            {
                me.attr("data-status",1);
                me.find('span').removeClass("dashicons-star-empty");
                me.find('span').addClass("dashicons-star-filled");
            }
            location.reload();
        }
    });

});

jQuery(".tab-links a").click(function() {
    var section = jQuery(this).attr("href");
    jQuery(".zestard-frm-setting-consent-tab").find(".ztdcfcf-tab").removeClass("active");
    jQuery(".zestard-frm-setting-consent-tab").find(section).addClass("active");
});

jQuery(".col-show").on('click',function(){
    jQuery(this).val('true');
});

jQuery(function() { 
    jQuery( ".ztdcfcf-tab .zt-col-setting" ).sortable({
            cancel:".zt-column-title"
    });
}); 

jQuery(".copy-shortcode").on('click',function(){
    jQuery(".shortcode-value").focus();
    jQuery(".shortcode-value").select();
    document.execCommand("copy");
    alert("Sucessfully Copied Shortcode!");
});

/* Date : 08-07-2022
Description : back office tab active in setting screen */
jQuery(document).ready(function(){
    //default page load 
    var idValue = jQuery(".tabs .ztdcfcf-tab.active").attr("id");    
    jQuery('.tabs .tab-links li a[href="'+'#'+idValue+'"]').css("color", "white");
    jQuery('.tabs .tab-links li a[href="'+'#'+idValue+'"]').parent("li").css("background-color", "black");

    //change tab
    jQuery(".tab-links li").click(function(){   
        jQuery(this).siblings().css("background-color", "white");     
        jQuery(this).siblings().find('a').css("color", "#2271b1");     
        
        jQuery(this).css("background-color", "black");
        jQuery(this).find('a').css("color", "white");
        
    });
});