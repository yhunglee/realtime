<?php
	ini_set('max_execution_time', 0);
	ini_set('memory_limit', -1);

	require(__DIR__ . '/phpQuery/phpQuery.php');

	$proxies = json_decode(file_get_contents('proxies.json'), true);

	class RssWorker {
		private $source;

		public function __construct ($source) {
			$this->source = $source;
		}

		public function run () {
			$source = $this->source;
			file_put_contents(__DIR__ . '/temp/r.' . $source, json_encode($this->$source()));
		}

		private function libertytimes () {
			$doc = phpQuery::newDocument(file_get_contents('http://news.ltn.com.tw/service/8'));
			$map = array();

			foreach ($doc['.ltnrss tr']->slice(1) as $tr) {
				$tr = pq($tr);
				$td = $tr['td'];

				$map[] = array(
						'label' => $td->eq(0)->text(),
						'url' => $td->eq(2)->find('a')->attr('href')
					);
			}

			return $map;
		}

		private function cna () {
			$url = 'http://rss.cna.com.tw/rsscna/';
			$doc = phpQuery::newDocument(mb_convert_encoding(file_get_contents($url), 'UTF-8', 'BIG5'));

			$map = array();

			foreach ($doc['td.tab_2 > a#p_menu'] as $anchor) {
				$anchor = pq($anchor);
				$href = $anchor->attr('href');

				if (substr($href, -9) === '_opml.xml' ||
					substr($href, -5) === '/opml') {
					continue;
				}

				if (substr($href, 0, 4) !== 'http') {
					$href = 'http://rss.cna.com.tw/' . $href;
				}

				$map[] = array(
						'label' => $anchor->text(),
						'url' => $href
					);
			}

			return $map;
		}

		private function storm () {
			$url = 'http://www.storm.mg/feeds';
			$doc = phpQuery::newDocument(file_get_contents($url));

			$map = array();

			foreach ($doc['#subNavs_accContent > a'] as $anchor) {
				$anchor = pq($anchor);

				$map[] = array(
						'label' => $anchor->text(),
						'url' => $anchor->attr('href')
					);
			}

			return $map;
		}

		private function newtalk () {
			$url = 'http://newtalk.tw/rss';
			$doc = phpQuery::newDocument(file_get_contents($url));

			$map = array();
		
			foreach ($doc['.rss-list li'] as $li) {
				$li = pq($li);
				$anchor = $li['a'];
				$label = trim($anchor->text());

				if ($label !== '全部' &&
					$label !== '評論' &&
					$label !== '開講無疆界' &&
					$label !== '公民連線'
					) {
					continue;
				}

				$map[] = array(
						'label' => $label,
						'url' => $anchor->attr('href')
					);
			}

			return $map;
		}

		private function ettoday () {
			global $proxies;

			$url = 'https://www.ettoday.net/events/news-express/epaper.php';

			$html = file_get_contents($url);

			if ($html === false) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
				curl_setopt($ch, CURLOPT_PROXY, $proxies[rand(0, count($proxies) - 1)]);
				$html = curl_exec($ch);
			}

			$doc = phpQuery::newDocument($html);

			$map = array();

			foreach ($doc['.block_z1 ol > li'] as $li) {
				$li = pq($li);

				$anchor = $li['a']->eq(0);
				$title = $anchor->attr('title');
				$label = substr($title, 0, strpos($title, 'RSS'));

				if ($label !== '即時新聞') {
					continue;
				}

				$map[] = array(
						'label' => $label,
						'url' => $anchor->attr('href')
					);
			}

			return $map;
		}
	}



	$start_time = time();

	$sources = array('libertytimes', 'cna', 'storm', 'newtalk', 'ettoday');

	foreach ($sources as $source) {
        $pid = pcntl_fork();

        if ($pid === 0) {
			(new RssWorker($source))->run();
			die();
        }
	}

	while (pcntl_wait($status) !== -1);

	$map = array();

	foreach ($sources as $source) {
		$map[$source] = json_decode(file_get_contents(__DIR__ . '/temp/r.' . $source), true);
	}

	$map['udn'] = array(
		array(
			'label' => '聯合新聞網',
			'url' => 'https://udn.com/rssfeed/latest'
		)
	);

	$map['twreporter'] = array(
		array(
			'label' => '報導者',
			'url' => 'https://www.twreporter.org/a/rss2.xml'
		)
	);

	$map['theinitium'] = array(
		array(
			'label' => '端傳媒',
			'url' => 'http://feeds.initium.news/theinitium'
		)
	);

	$map['upmedia'] = array(
		array(
			'label' => '上報',
			'url' => 'http://www.upmedia.mg/createRSS.php?Type=all'
		)
	);

	$map['mirrormedia'] = array(
		array(
			'label' => '鏡傳媒',
			'url' => 'https://www.mirrormedia.mg/rss/rss.xml'
		)
	);

	file_put_contents(__DIR__ . '/rssMap.json', json_encode($map, JSON_PRETTY_PRINT));


	$spent_time = time() - $start_time;

	echo "Spent: $spent_time sec\n";
?>
