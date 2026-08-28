Filter = function (options) {
	var _ = {}
    _.options = options
		_.preloader = $('.js-filter-preloader')
		_.total = $('.js-total-result')
		_.pagination = $('.js-filter-pagination')
		_.filterTags = $('.js-filter-tags')
		_.inputs = []

		_.sendRequest = function() {
			_.preloader.show();
			_.changeUrl();
			$.ajax({
				method: 'POST',
				url: this.o.url,
				data: _.inputs,
				success: function(data) {
					_.container.html(data.view);					
					_.total.html(data.total);
					_.pagination.html(data.pagination);
					_.preloader.hide();
					_.generateTags();
				}
			})
		}

		_.generateTags = function(filters) {
			var tag, checkboxes = $('.js-filter:checked'),
				_reset = $('<button/>', {
					text: _.o.trans.btn_reset,
					class: 'btn filter__reset js-reset-filter',
				}),
				_this, _name;

			_.filterTags.html('');
			checkboxes.each(function(i, item) {
				_this = $(this);
				_name = _this.parent().find('.checkbox__spacer').clone()
				_name.find('span, i').remove();

				tag = $('<button/>', {
					text: _name.html(),
					class: 'btn btn--green filter__dismisser rounded'
				}).attr('data-id', _this.attr('id')).append('<i class="icon-cancel filter__icon-dismisser js-tag-dismisser"></i>');

				_.filterTags.prepend(tag);
			});

			if (checkboxes.length > 0) {
				_.filterTags.append(_reset);
			}
		}

		_.onTagDismisserClick = function() {
			var _this;
			$('body').on('click', '.js-tag-dismisser', function(e) {
				_this = $(this);
				$('#' + _this.parent().data('id')).prop('checked', false);
				_.inputs = $('.js-filter:input').serializeArray();
				_.sendRequest();
			});
		}

		_.change = function() {
			$('.js-filter').on('change keyup', function() {
				_.inputs = $('.js-filter:input').serializeArray();
				_.sendRequest();
			});
			$('.js-filter-price').on('change keyup', function() {
				let prices = $('.js-filter-price:input').serializeArray(),
						inputs = $('.js-filter:input').serializeArray();
				_.inputs = prices.concat(inputs)
				_.sendRequest();
			});
		}

		_.changeUrl = function () {
			// History SEO//
			var url = _.o.current_url + _.friendlyUrl(_.inputs),
						title = $('head title').html();

				history.pushState({
						url: url,
						title: title
				}, title, url);

				document.title = title;
		}

		_.friendlyUrl = function(filters)
		{
			var cleanArray = [];

			$.each(filters, function(i, item) {
				if (item.value != '' && item.name != 'categories[]') {
					item.value = item.value.replace(/\s+/g, '');
					cleanArray.push(item);
				}
			});

			if (cleanArray.length > 0) {
				return  '?' + $.param(cleanArray);
			}

			return '';
		}
		_.onClickSort = function() {
			var _sortInput = $('.js-sort-input');
			$('.js-sort-btn').on('click', function(e) {
				_sortInput.val($(this).data('val'));
				_.inputs = $('.js-filter:input').serializeArray();
				_.sendRequest();
			});
		}

		_.onClickList = function() {
			$('.js-list').on('click', function(e) {
				$('.products__sort-icons').removeClass('active');
					$(this).addClass('active');
					$('.js-blog').removeClass('col-md-4').addClass('col-md-12');
					$('.product-blog').addClass('product-blog--favorite');
			})
		}

		_.onClickGrid = function() {
			$('.js-grid').on('click', function(e) {
					$('.products__sort-icons').removeClass('active');
					$(this).addClass('active');
					$('.js-blog').removeClass('col-md-12').addClass('col-md-4');
					$('.product-blog').removeClass('product-blog--favorite');
			});
		}

		_.initPlugins = function() {
			let index = 1;
			const on = (listener, query, fn) => {
					document.querySelectorAll(query).forEach(item => {
							item.addEventListener(listener, el => {
									fn(el);
							})
					})
			}			
		}

		_.onClickReset = function()
		{
			$('body').on('click', '.js-reset-filter', function(e) {
				_.reset();
				_.inputs = []
				_.sendRequest();
				e.preventDefault();
			});
		}

		_.reset = function() {
			$('input[type=checkbox].js-filter').prop('checked', false);
			$('input[type=text].js-filter').val('');
			$('input[type=range].js-filter').val('');
		}

		_.init = function () {
			_.o = options;
			_.container = $(this.o.container);
			_.change();
			_.onClickSort();			
			_.onClickReset();
			_.generateTags();
			_.onTagDismisserClick();
			_.initPlugins();
			_.onClickList();
			_.onClickGrid();
		}
		_.init()
    return _
}