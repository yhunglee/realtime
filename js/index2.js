	(function() {
		if (typeof(Storage) == 'undefined') {
			return;
		}

		var lastViewed = sessionStorage.lastViewed;

		if (lastViewed === undefined) {
			return;
		}

		var tokens = lastViewed.split('\n'),
			date = Date.parse(tokens[0]),
			source = tokens[1],
			title = tokens[2];

		var $body = $('body'),
			$articles = $body.children('article'),
			m = $articles.length - 1;

		for (var i = 0; i < m; ++i) {
			var $article = $articles.eq(i),
				$p = $article.children('p'),
				date2 = Date.parse($p.children('time').attr('datetime'));

			if (date2 < date) {
				return;
			}

			if (date2 === date && $p.children('a').text() === source && $article.find('h1 > a').text() === title) {
				$body.scrollTop($articles[i + 1].offsetTop);
				return;
			}
		}
	})();
