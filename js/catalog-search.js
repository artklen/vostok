/*
$(function ()
{
	var showResult = function ()
	{
		$.ajax
		({
			  url: '/catalog'
			, method: 'GET'
			, data: {searchtrigram: searchStr}
		})
		.done(function (html)
		{
			console.log('showing result');
			$('.search-list').remove();
			$(search).after(html);
		});
	};

	var
		showItemsTimeout
		, searchStr
		, search = $('[type=search][name=search]');

	$(search).keyup(function (e)
	{
		//alert('keyup');
		searchStr = $(this).val();

		if (e.which == 13)
		{
			event.preventDefault();
			console.log('enter search');
			if (searchStr)
			{
				showResult(searchStr);
			}
		}
		else
		{
			//alert('serchStr: ' + searchStr);

			clearTimeout(showItemsTimeout);
			if (searchStr)
			{
				showItemsTimeout = setTimeout(showResult, 500);
			}
		}
	});

	$('body').on('mouseleave', '.search-list', function ()
	{
		$('.search-list').remove();
	});

	$(search).attr('autocomplete', 'off');
});*/

var searchTimer;
$('.search-input').typeahead({
	hint: false,
}, {
	display: 'title',
	limit: 20,
	async: true,
	source: function(query, sync, async) {
		$.ajax({
			type: 'get',
			url: '/catalog',
			data: {
				searchtrigram: query
			},
			dataType: 'json',
			success: function(data) {
				if (searchTimer) {
					clearTimeout(searchTimer);
				}
				if (data.length) {
					console.log('data:', data);
					searchTimer = setTimeout(function() {
						async(data);
					}, 200);
				}
			}
		});
	}
}).on('typeahead:select', function(event, suggestion) {
	if (suggestion.link) {
		document.location.href = suggestion.link;
	}
});