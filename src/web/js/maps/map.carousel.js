	(function(){
	
		$.fn.mapCarousel = function(){
		
			function repeat(str, n){
				return new Array(n + 1).join(str);
			}
		
		
			return this.each(function(){
				var $wrapper = $('> div', this).css('overflow', 'hidden'),
						$slider  = $wrapper.find('> ul').width(9999),
						$items = $slider.find('> li').width(Math.ceil($wrapper.outerWidth() / 5)),
						$single = $items.filter(':first'),
						singleWidth = $single.outerWidth(),
						visible = Math.ceil($wrapper.outerWidth() / singleWidth),
						currentPage = 1,
						pages =  Math.ceil($items.length / visible);
						
						if($items.length % visible != 0){
							$slider.append(repeat('<li class="empty" />', visible - ($items.length % visible)));
							$items = $slider.find('> li');
						}

						
						$items.filter(':first').before($items.slice(-visible).clone().addClass("cloned"));
						$items.filter(':last').after($items.slice(0, visible).clone().addClass("cloned"));
						$items = $slider.find('> li');
						
						$wrapper.scrollLeft(singleWidth * visible);
						
						function gotoPage(page){
							var dir = page < currentPage ? - 1 : 1,
									n = Math.abs(currentPage - page),
									left =  singleWidth * dir * visible * n;
									
							$wrapper.filter(':not(:animated)').animate({
								scrollLeft : '+=' + left
							}, 500, function(){
								if(page > pages){
									$wrapper.scrollLeft(singleWidth * visible);		
									page = 1;
								}else if(page == 0){
									page = pages;
									$wrapper.scrollLeft(singleWidth * visible * pages);
								}
								currentPage = page;
							});		
						}
											
						
												
						//$wrapper.before('<a href="#" class="arrow back">&lt;</a><a href="#" class="arrow forward">&gt;</a>');
						
						$('a.back', this).click(function(){
							gotoPage(currentPage - 1);
							return false;
						});
						
						$('a.forward', this).click(function(){
							gotoPage(currentPage + 1);
							return false;							
						});
						
						$(this).bind('goto', function(event, page){
							gotoPage(page);
						});
						
						$(this).bind('next', function(){
								gotoPage(currentPage + 1);
						});				
						
			});
		};	
	})(jQuery);