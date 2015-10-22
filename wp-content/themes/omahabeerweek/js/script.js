var showEventsAuthorId = 0;

(function ($) {
	$(function () {
		var destinations = new Array();
		var getRequest = null;
		var navigating = false;
		var scrollToEvent = 0;

		if (IS_MOBILE || IS_TABLET) {
			$('#wrapper').css({
				'background-image': 'url("' + $('#home-image', '#home').attr('src') + '")',
				'background-repeat': 'none',
				'background-size': 'cover'
			});

			$('#home-image', '#home').remove();
			$('#home').show();
		}
		else {
			$('#home-image', '#home').imagesLoaded(function() {
				$('#home').css({
					'background-image': 'url("' + $(this).attr('src') + '")'
				});

				$(this).remove();

				$('#home').delay(400).fadeIn('slow', function () {
					
				});

				if ($('#countdown').length > 0) {
					var austDay = new Date(2015, 1, 13);
					$('#countdown').countdown({
						until: austDay,
						layout: '<span class="countdown_section">{d<}{dn} {dl} {d>}</span> <span class="countdown_section">{hn} {hl}</span> <span class="countdown_section">{mn} {ml}</span> <span class="countdown_section">{sn} {sl}</span>'
					});
				}
			});
		}

		if ($('#selected-page').length > 0) {
			$('#selected-page').click(function(e) {
				var parentDiv = $(this).parent('#mobile-nav');

				e.preventDefault();

				if (parentDiv.hasClass('open')) {
					parentDiv.removeClass('open');
				}
				else {
					parentDiv.addClass('open');
				}
			})
		}

		$('#branding nav a').click(function () {
			$('#branding nav a.selected').removeClass('selected');

			if (!$(this).hasClass('title')) {
				$(this).addClass('selected');
			}
		});

		$('div#all-events a').click(function (e) {
			var p = $(this).parent('#all-events');

			e.preventDefault();

			if ($(this).hasClass('title')) {
				if (p.hasClass('open')) {
					p.removeClass('open');
				}
				else {
					p.addClass('open');
				}

				return;
			}

			// scroll date into view
			if ($('#events #' + $(this).data('id')).length !== 0) {
				$('html, body').animate({
					scrollTop: $('#events #' + $(this).data('id')).offset().top - $('#branding').outerHeight()
				}, 1500);
			}
			else {
				scrollToEvent = $(this).data('id');

				$.address.value('/events');
			}

			p.removeClass('open');
		});

		if (IS_TABLET) {
			$('#view-events').click(function(e) {
				e.preventDefault();

				$('html, body').animate({
	        		'scrollTop': $('div.events').offset().top
	        	}, 750);
			});
		}

		if (!IS_TABLET) {
			$(window).scroll(function () {
				if ($('#events').length !== 0) {
					if ($(window).scrollTop() > $(window).height() / 2) {
						if (!$('div#home div').first().is(':animated') && $('div#home div').first().css('display') !== 'none') {
							//TODO: stop countdown
							$('div#home a, div#home div').fadeOut();
						}
					}
					else {
						if (!$('div#home div').first().is(':animated') && $('div#home div').first().css('display') !== 'block') {
							//TODO: restart countdown
							$('div#home a, div#home div').fadeIn();
						}
					}

					$('section', '#events').each(function(index) {
						if (Math.floor($(this).offset().top) - $(window).scrollTop() - $('#branding').outerHeight() < 0) {
							$('header', $(this)).css({
								'position': 'fixed',
								'top': $('#branding').outerHeight().toString() + 'px'
							});
						}
						else {
							$('header', $(this)).css({
								'position': 'absolute',
								'top': '0px'
							});
						}
					});
				}
			});
		}

		$.address.init(function(event) {
			//TODO: anything?
        }).change(function(event) {
        	var className = '',
        		previousClassName = '',
        		url = '';

        	if ($('div#content').length > 1) {
        		previousClassName = $('div#content:eq(1)').attr('class');
        	}
        	else {
        		previousClassName = $('div#content').attr('class');
        	}

            switch (event.value) {
            	case '/events':
            		if ($('div.events', '#main').length === 0 || IS_MOBILE) {
            			className = 'events';
            			url = ROOT + 'events/';
            		}
            		else {
            			$('html, body').animate({
			        		'scrollTop': $('div.events').offset().top - $('#branding').height()
			        	}, 750);
	            	}

            		break;

            	case '/about':
            		className = 'about';
            		url = ROOT + 'about/';

            		break;

        		case '/bars':
        			className = 'bars';
            		url = ROOT + 'bars/';

            		break;

        		case '/breweries':
        			className = 'breweries';
            		url = ROOT + 'breweries/';

            		break;

            	case '/faq':
            		className = 'faq';
            		url = ROOT + 'faq/';

            		break;

            	case '/members':
            		className = 'bars';
            		url = ROOT + 'members/';

            		break;

            	case '/sponsors':
            		className = 'sponsors';
            		url = ROOT + 'sponsors/';

            		break;

            	case '/tours':
            		className = 'tours';
            		url = ROOT + 'bus-tours/';

            		break;

            	default:
            		break;
            }

	        if (className.length > 0 && !$('a[href="#/' + className + '"]').hasClass('selected')) {
	        	$('a[href="#/' + className + '"]').addClass('selected');
	        }

	        if (url.length > 0) {
	        	var $mobileNav = $('#mobile-nav');
	        	var $selectedPage = $('#selected-page');

	        	if ($mobileNav.length > 0 && $mobileNav.hasClass('open')) {
	        		$mobileNav.removeClass('open');

	        		$('a', $mobileNav).each(function() {
	        			if ($(this).attr('href').indexOf('/' + className) !== -1) {
	        				$selectedPage.text($(this).text());
	        			}
	        		});
	        	}

				trackPageView(event.value);

	        	if (navigating) {
	        		destinations.push({
	        			'url': url,
	        			'previousClassName': previousClassName
	        		});

	        		return;
	        	}

            	getContent(url, previousClassName);
	        }
        });

		$(window).resize(function() {
			updateMinSize();
		});

		function getContent (url, previousClassName) {
			if (url.length > 0) {
				navigating = true;

				if (getRequest !== null) {
					getRequest.abort();
				}

				getRequest = $.get(url, function(result) {
					var $newContent = $(result);

					updateMinSize();

			        $(document).trigger('page_animating', this);

	            	if (IS_MOBILE || IS_TABLET) {
	            		var $wrapper = $('#wrapper');

	            		$newContent.hide();

	            		if ($('div', $wrapper).length > 1) {
	            			$('div', $wrapper).each(function(index) {
	            				if (index > 0) {
	            					$(this).fadeOut();
	            				}
	            			});
	            		}

		            	$('div', $wrapper).first().fadeOut(function () {
		            		$(this).remove();
							$wrapper.append($newContent);

							$newContent.fadeIn(function () {
								navigating = false;
							});
		            	});

		            	var selectedPageText = 'HOME'

		            	if ($('#mobile-nav a[data-path="' + $.address.path() + '"]').length > 0) {
		            		selectedPageText = $('#mobile-nav a[data-path="' + $.address.path() + '"]').text();
		            	}

		            	$('#selected-page').text(selectedPageText);
	            	}
	            	else {
	            		var newTop = $('#branding').outerHeight();;

						$newContent.css({
		            		'position': 'fixed',
		            		'top': $(window).innerHeight().toString() + 'px',
		            		'z-index': 2
		            	});

						$('#wrapper').append($newContent);

						if (previousClassName !== 'events') {
							$('header', 'div.' + previousClassName).css({
								'top': 0,
								'position': 'absolute'
							});

							$('div.' + previousClassName, "#main").animate({
								top: -($(window).innerHeight()).toString() + 'px'
							}, 1150);
						}

						// if ($newContent.hasClass('events')) {
						// 	newTop = $('#branding').outerHeight();
						// }

						$newContent.animate({
							top: newTop
						}, 750, function() {
							$(window).scrollTop(0);

							$('div.' + previousClassName, "#main").remove();

							$('header', $(this)).css({
								'top': newTop,
								'position': 'fixed'
							});

							$(this).css({
								'position': 'relative',
		            			'z-index': 0
							});

							if (scrollToEvent !== 0) {
								$('html, body').animate({
									scrollTop: $('#events #' + scrollToEvent.toString()).offset().top - $('#branding').outerHeight()
								}, 1500, function () {
									navigating = false;
								});

								scrollToEvent = 0;
							}
							else {
								navigating = false;

								if (destinations.length > 0) {
									var newContent = destinations.shift(0);

									getContent(newContent.url, newContent.previousClassName);
								}
							}
						});
					}

					getRequest = null;
	            }, 'html');
			}
		}

		function trackPageView(page) {
			if (typeof _gaq !== 'undefined') {
				_gaq.push(['_trackPageview', page]);
			}
		}

		function updateMinSize() {
			if ($('#events').length > 0) {
	            $('section', '#events').css('min-height', ($(window).innerHeight() - $('#branding').outerHeight() - 190).toString() + 'px');
	        }
		}

		updateMinSize();
	});
})(jQuery);