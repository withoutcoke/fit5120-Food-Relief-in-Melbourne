
		jQuery(function() {
			jQuery(".wysj-bp-content").css("height", jQuery(".wysj-bp-content").height());
			jQuery(".wysj-bp-close").on("click", function(event) {
				if (jQuery(this).hasClass("wysj-bp-status-close")) {
					jQuery(this).removeClass("wysj-bp-status-close");
					jQuery(".wysj-bp-content").removeClass("wysj-bp-status-close");
				}else{
					jQuery(this).addClass("wysj-bp-status-close");
					jQuery(".wysj-bp-content").addClass("wysj-bp-status-close");
				}
			});
		});
	