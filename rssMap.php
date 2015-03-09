<?php
	ini_set('max_execution_time', 0);
	ini_set('memory_limit', -1);

	require(__DIR__ . '/phpQuery/phpQuery.php');

	class WorkerThread extends Thread {
		private $source;

		public function __construct ($source) {
			$this->source = $source;
		}

		public function run () {
			$source = $this->source;
			file_put_contents(__DIR__ . '/temp/r.' . $source, json_encode($this->$source()));
		}

		private function udn () {
			$url = 'http://udn.com/rssfeed/lists/2';
			$doc = phpQuery::newDocument(file_get_contents($url));
			$map = array();

			foreach ($doc['#rss_list .group'] as $group) {
				$group = pq($group);

				if ($group['h3']->text() !== '即時') {
					continue;
				}

				foreach ($group['dl dt a'] as $anchor) {
					$anchor = pq($anchor);
					$href = $anchor->attr('href');
					$label = $anchor->text();

					$map[] = array(
							'label' => $label,
							'url' => 'http://udn.com' . $href
						);
				}
			}

			return $map;
		}

		private function chinatimes () {
			$url = 'http://www.chinatimes.com/syndication/rss';
			$tokens = explode('<hr>', file_get_contents($url));
			$doc = phpQuery::newDocument($tokens[1]);

			$map = array();

			foreach ($doc['ul > li'] as $group) {
				$group = pq($group);
				$category = $group->html();
				$category = trim(substr($category, 0, strpos($category, ' ')));

				if ($category !== '即時新聞') {
					continue;
				}

				foreach ($group['.rssli'] as $li) {
					$li = pq($li);

					$map[] = array(
							'label' => $li['span']->eq(0)->text(),
							'url' => $li['a']->attr('href')
						);
				}
			}

			return $map;
		}

		private function appledaily () {
			$url = 'http://www.appledaily.com.tw/rss';
			$doc = phpQuery::newDocument(file_get_contents($url));

			$map = array();

			foreach ($doc['.each_level'] as $section) {
				$section = pq($section);

				if (str_replace(' ', '', $section['header > h1 > a']->text()) !== '即時新聞總覽') {
					continue;
				}

				foreach ($section['ul li a'] as $anchor) {
					$anchor = pq($anchor);

					$map[] = array(
							'label' => $anchor->text(),
							'url' => 'http://www.appledaily.com.tw/' . $anchor->attr('href')
						);
				}
			}

			return $map;
		}

		private function libertytimes () {
			$doc = phpQuery::newDocument(file_get_contents('http://news.ltn.com.tw/service?p=8'));
			$map = array();

			foreach ($doc['.Txml tr']->slice(1) as $tr) {
				$tr = pq($tr);
				$td = $tr['td'];

				$map[] = array(
						'label' => substr($td->eq(0)->text(), 3),
						'url' => $td->eq(2)->find('a')->attr('href')
					);
			}

			return $map;
		}

		private function cna () {
			$url = 'http://www.cna.com.tw/rss/index.aspx';
			$doc = phpQuery::newDocument(file_get_contents($url));

			$map = array();

			foreach ($doc['.subscribe li'] as $li) {
				$li = pq($li);
				$html = $li->html();
				$start = strpos($html, "\t") + 1;
				$label = substr($html, $start, strpos($html, '<a ') - $start);

				$map[] = array(
						'label' => $label,
						'url' => $li['a']->attr('href')
					);
			}

			return $map;
		}

		private function storm () {
			$url = 'http://www.storm.mg/feeds';
			$doc = phpQuery::newDocument(file_get_contents($url));

			$map = array();

			foreach ($doc['.subscribe-list > li > a'] as $anchor) {
				$anchor = pq($anchor);

				$map[] = array(
						'label' => $anchor->text(),
						'url' => 'http://www.storm.mg' . $anchor->attr('href')
					);
			}

			return $map;
		}

		private function newtalk () {
			$url = 'http://newtalk.tw/rss';
			$doc = phpQuery::newDocument(file_get_contents($url));

			$map = array();
		
			foreach ($doc['#rss .rss-item'] as $li) {
				$li = pq($li);
				$anchor = $li['a'];
				$map[] = array(
						'label' => $anchor->text(),
						'url' => $anchor->attr('href')
					);
			}

			return $map;
		}

		private function ettoday () {
			$url = 'http://www.ettoday.net/events/news-express/epaper.php';
			$doc = phpQuery::newDocument(file_get_contents($url));

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

		private function nownews () {
			$url = 'http://member.nownews.com/membersite/rss.php';
			$doc = phpQuery::newDocument(file_get_contents($url));

			$map = array();

			foreach ($doc['#rssnews tr'] as $tr) {
				$tr = pq($tr);
				$td = $tr['td'];
				$label = $td->eq(0)->text();

				if ($label !== '即時新聞') {
					continue;
				}

				$map[] = array(
						'label' => $label,
						'url' => $td->eq(1)->children('a')->attr('href')
					);
			}

			return $map;
		}
	}



	$start_time = time();

	
	$workers = array();
	$sources = array('udn', 'chinatimes', 'appledaily', 'libertytimes', 'cna', 'storm', 'newtalk', 'ettoday', 'nownews');

	foreach ($sources as $source) {
		$worker = new WorkerThread($source);
		$worker->start();
		$workers[] = $worker;
	}

	foreach ($workers as $worker) {
		$worker->join();
	}

	$map = array();

	foreach ($sources as $source) {
		$map[$source] = json_decode(file_get_contents(__DIR__ . '/temp/r.' . $source), true);
	}

	file_put_contents(__DIR__ . '/rssMap.json', json_encode($map));


	$spent_time = time() - $start_time;

	echo "Spent: $spent_time sec\n";
?>
