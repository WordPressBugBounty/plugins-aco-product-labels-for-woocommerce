jQuery(window).on('load berocket_ajax_products_infinite_loaded',function () {
	
	var acoDivClass = acoplw_frontend_object.classname ? '.'+acoplw_frontend_object.classname : '.images';
	var enableJQ    = acoplw_frontend_object.enablejquery ? parseInt(acoplw_frontend_object.enablejquery) : 0;

    // Detail Page Badge
    var badge = jQuery('.acoplw-hidden-wrap').not('header .acoplw-hidden-wrap');
	var flag = false;
	if ( badge.length >= 1 ) { // Check for badges
		var badgeCont = badge.find('.acoplw-badge').clone(); 
		jQuery(badgeCont).addClass('acoplw-singleBadge');
        jQuery(badgeCont).find('.acoplw-badge-icon').removeClass('acoplw-badge-listing-hide');
        if ( acoplw_frontend_object.classname ) {
			jQuery(acoDivClass).each( function (index, cont) {
				if ( !flag && !jQuery(this).is(":hidden")) { 
					var position = jQuery(this);
					jQuery(this).css({'positon':'relative'});
					jQuery(badgeCont).prependTo(jQuery(position).parent());
					// jQuery(position).appendTo(badgeCont);
					flag = true;
				}
			});
			badge.remove();
		} else {
			jQuery('.woocommerce-product-gallery:first, .woocommerce-product-gallery--with-images:first').each( function (index, cont) { 
				var position = jQuery(this);
				jQuery(this).css({'positon':'relative'}); 
				if ( jQuery(position).parent().hasClass('product') ) {
					jQuery(badgeCont).prependTo(jQuery(position));
				} else {
					jQuery(badgeCont).prependTo(jQuery(position).parent());
				}
				flag = true;
			});
			if (!flag) { 
				jQuery(acoDivClass).each( function (index, cont) {
					if ( !flag ) { 
						var position = jQuery(this);
						jQuery(this).css({'positon':'relative'});
						jQuery(badgeCont).prependTo(jQuery(position).parent());
						// jQuery(position).appendTo(badgeCont);
						flag = true;
					}
				});
			} else {
				badge.remove();
			}
		}
	}

	if ( jQuery('.jet-woo-products').length ) {
        jQuery('.jet-woo-products__item').each( function (index) {
            if( jQuery(this).next().is('span.acoplw-badge')) {
                var badgeCont = jQuery(this).next('.acoplw-badge'); 
                var position = jQuery(this);
                jQuery(this).css({'positon':'relative'});
                jQuery(badgeCont).detach().prependTo(jQuery(position));
            }
        });
    }

	// if ( jQuery('.jet-woo-builder-products-loop').length ) { 
	// 	jQuery('.jet-woo-builder-product').each( function (index) { 
    //         if( jQuery(this).prev().is('span.acoplw-badge')) { 
    //             var badgeCont = jQuery(this).prev('.acoplw-badge').removeClass('acoplw-badge-listing-hide'); 
    //             var position = jQuery(this).find('.jet-woo-builder-archive-product-thumbnail a');
    //             jQuery(this).css({'positon':'relative'});
    //             jQuery(badgeCont).detach().prependTo(jQuery(position));
    //         }
    //     });
	// }

	if ( enableJQ == 1 ) {
        jQuery('.acoplw-badge.acoplw-badge-listing-hide:not(.acoplw-jq-badge)').each( function (index) {
            // let ImageContainerDiv = jQuery(this).parent().find('a img').closest('a');
            let ImageContainerDiv = jQuery(this).parent().find('a img').parent();
            let badgeCont = jQuery(this); 
			// jQuery(badgeCont).removeClass('acoplw-badge-listing-hide').addClass('acoplw-jq-badge'); // removing acoplw-badge-listing-hide - loadmore plugin support
			jQuery(badgeCont).addClass('acoplw-jq-badge'); // removing acoplw-badge-listing-hide - loadmore plugin support
            jQuery(this).parent().find('a img').closest('a').addClass('acoplw-badgeOutter');
            jQuery(badgeCont).detach().prependTo(jQuery(ImageContainerDiv));

			// let badgeCont = jQuery(this); 
			// if ( !jQuery(this).parent().is('.products') ) {  
            // 	let ImageContainerDiv = jQuery(this).parent().find('a img').closest('a');
            // 	jQuery(this).parent().find('a img').closest('a').addClass('acoplw-badgeOutter');
            // 	jQuery(badgeCont).detach().prependTo(jQuery(ImageContainerDiv));
            // } else { 
            // 	badgeCont.removeClass('acoplw-badge-listing-hide');
            //     badgeCont.find('span').removeClass('acoplw-badge-listing-hide');
            // 	let ImageContainerDiv = jQuery(this).next('.product').find('img').parents('a').first();
            // 	jQuery(badgeCont).detach().prependTo(jQuery(ImageContainerDiv));
            // }
        });
    }

	// Listing Page
	// let listingBadge = jQuery('.acoplw-badge').not(acoDivClass+' .acoplw-badge');
	// let listFlag = false; 
	// if ( listingBadge.length >= 1 ) { // Check for badges
	// 	jQuery(listingBadge).each (function() {
	// 		let listingParentDiv 	= jQuery(this).parents('.product');
	// 		let listingImgDiv		= jQuery(listingParentDiv).find('img');
	// 		let listingBadgeClone	= jQuery(this);
	// 		jQuery(listingParentDiv).find('img').wrap('<span class="acoplw-badgeWrap"></span>')
	// 		// jQuery(this).css({'positon':'relative'});
	// 		jQuery(listingBadgeClone).prependTo(jQuery(listingImgDiv).parent());
	// 	});
	// }

});

// Variable product {salepercentage}: show 'max' or 'min' discount percentage
jQuery(function ($) {

	var decimals = ( typeof acoplw_frontend_object !== 'undefined' && acoplw_frontend_object.decimals !== undefined ) ? parseInt(acoplw_frontend_object.decimals, 10) : 2;
	if ( isNaN(decimals) ) { decimals = 2; }

	function acoplwRound(value, places) {
		var factor = Math.pow(10, places);
		return Math.round(value * factor) / factor;
	}

	function acoplwProductId($form) {
		return $form.attr('data-product_id') || $form.find('input[name="product_id"]').val() || $form.find('[name="add-to-cart"]').val() || '';
	}

	// text: '' hides the badge (variation not on sale)
	function acoplwSetPercentage($els, textFn) {
		$els.each(function () {
			var $el		= $(this);
			var $badge	= $el.closest('.acoplw-badge-icon');
			var text	= textFn($el);
			if ( text ) {
				$el.text(text);
				$badge.removeClass('acoplw-var-hidden');
			} else {
				$badge.addClass('acoplw-var-hidden');
			}
		});
	}

	$(document).on('found_variation', 'form.variations_form', function (event, variation) {
		var $els = $('.acoplw-salepercentage[data-product-id="' + acoplwProductId($(this)) + '"]');
		if ( !$els.length || !variation ) { return; }

		var regular	= parseFloat(variation.display_regular_price);
		var price	= parseFloat(variation.display_price);
		var text	= '';
		if ( regular > 0 && price > 0 && price < regular ) {
			text = parseFloat((100 - acoplwRound((price / regular) * 100, decimals)).toFixed(decimals)) + '%';
		}
		acoplwSetPercentage($els, function () { return text; });
	});

	$(document).on('reset_data hide_variation', 'form.variations_form', function () {
		var $els = $('.acoplw-salepercentage[data-product-id="' + acoplwProductId($(this)) + '"]');
		if ( !$els.length ) { return; }
		acoplwSetPercentage($els, function ($el) { return $el.attr('data-default'); });
	});

});
