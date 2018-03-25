<?php
	ini_set('date.timezone', 'Asia/Taipei');

	$page = isset($_GET['p']) ? intval($_GET['p']) : 1;
	$num = count(array_diff(scandir(__DIR__ . '/data'), array('.', '..', '.gitignore')));

	$path = $_SERVER['REQUEST_URI'];
	$pos = strpos($path, '?');

	if ($pos !== false) {
		$path = substr($path, 0, $pos);
	}

	if ($page < 1) {
		header('Location: ' . $path);
		die();
	}

	if ($page > $num && $num !== 0) {
		header('Location: ' . $path . '?p=' . $num);
		die();
	}

	$cachePath = __DIR__ . "/cache/$page.html";
	$dataPath = __DIR__ . "/data/$page.json";

	if (file_exists($cachePath) === true && filemtime($cachePath) > filemtime($dataPath) && (isset($_GET['c']) === false || intval($_GET['c']) !== 0)) {
		echo file_get_contents($cachePath);
		die();
	}

	$sourceMap = array(
			'udn' => array(
					'title' => '聯合新聞網',
					'link' => 'http://udn.com'
				),
			'chinatimes' => array(
					'title' => '中時電子報',
					'link' => 'http://www.chinatimes.com'
				),
			'libertytimes' => array(
					'title' => '自由電子報',
					'link' => 'http://www.ltn.com.tw'
				),
			'appledaily' => array(
					'title' => '蘋果即時新聞',
					'link' => 'http://www.appledaily.com.tw'
				),
			'cna' => array(
					'title' => '中央通訊社',
					'link' => 'http://www.cna.com.tw'
				),
			'storm' => array(
					'title' => '風傳媒',
					'link' => 'http://www.storm.mg'
				),
			'newtalk' => array(
					'title' => '新頭殼',
					'link' => 'http://newtalk.tw'
				),
			'ettoday' => array(
					'title' => 'ETtoday',
					'link' => 'http://www.ettoday.net'
				),
			'nownews' => array(
					'title' => 'NOWnews',
					'link' => 'http://nownews.com'
				),
			'twreporter' => array(
					'title' => '報導者',
					'link' => 'https://www.twreporter.org'
				),
			'theinitium' => array(
					'title' => '端傳媒',
					'link' => 'https://theinitium.com'
				),
			'upmedia' => array(
					'title' => '上報',
					'link' => 'http://www.upmedia.mg'
				),
			'mirrormedia' => array(
					'title' => '鏡傳媒',
					'link' => 'https://www.mirrormedia.mg'
				),
			'setn' => array(
					'title' => '三立新聞網',
					'link' => 'http://www.setn.com'
				)
		);

	ob_start();
?>
<!DOCTYPE html>
<html>
<head>
<meta name="viewport" content="width=device-width, initial-scale=1" >
<meta name="format-detection" content="telephone=no" >
<meta charset="UTF-8" >
<title>台灣即時新聞</title>
<link rel="apple-touch-icon" href="realtime.png" >
<!--[if lt IE 9]>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html5shiv/3.7.2/html5shiv-printshiv.min.js" ></script>
<![endif]-->
<link href="https://cdnjs.cloudflare.com/ajax/libs/normalize/3.0.2/normalize.min.css" rel="stylesheet" >
<link href="css/index.min.css" rel="stylesheet" >
</head>
<body>
<?php
    $base = $page;
    $temp = $num - 5;

    if($base > $temp) {
        $base = $temp;
    }

    if($base < 5) {
        $base = 5;
    }

    $start = $base - 4;
    $end = $base + 5;
    
    if($end > $num) {
        $end = $num;
    }
?>
<header>
	<h1><a href="<?php echo $path ?>" >台灣即時新聞</a></h1>
	<p>這是第 <?php echo $page ?> 頁，共 <?php echo $num ?> 頁</p>
</header>
<?php
	$i = 0;

	foreach (json_decode(file_get_contents(__DIR__ . "/data/$page.json"), true) as $item) {
		$timestamp = $item['timestamp'];
		$description = $item['description'];
		$source = $sourceMap[$item['source']];

		if (isset($item['image'])) {
			if ($description !== '') {
				$description .= '<br>';
			}

			$description .= '<img src="' . $item['image'] . '">';
		}
?>
<article>
	<p><a href="<?php echo $source['link'] ?>" target="_blank" ><?php echo $source['title'] ?></a> <time datetime="<?php echo date('c', $timestamp) ?>" ><?php echo date('Y-m-d H:i:s', $timestamp) ?></time></p>
	<h1><a href="<?php echo $item['link'] ?>" target="_blank" ><?php echo $item['title'] ?></a></h1>
<?php
		if ($description !== '') {
?>
	<div><?php echo $description ?></div>
<?php
		}
?>
</article>
<?php
		$adMap = array(
				'10' => '6212292798',
				'20' => '7949280793',
				'30' => '3519081194',
				'40' => '9426013990'
			);

		if ($i % 10 === 0 && $i <= 40 && $i > 0) {
?>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<ins id="ad-<?php echo $i ?>" class="ad adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-1821434700708607"
     data-ad-slot="<?php echo $adMap[$i] ?>"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<?php
		}

		++$i;
	}
?>
<ol>
<?php
    if ($page !== 1) {
?>
	<li class="first" ><a href="<?php echo $path ?>" >第一頁</a></li>
	<li class="prev" ><a href="<?php echo $path .'?p=' . ($page - 1) ?>" >上一頁</a></li>
<?php
	}

    for ($i = $start; $i <= $end; ++$i) {
?>
	<li <?php if ($i === $page) { echo 'class="current"'; } ?> ><a href="<?php echo "$path?p=$i" ?>" ><?php echo $i ?></a></li>
<?php
	}

	if ($page !== $num) {
?>
	<li class="next" ><a href="<?php echo $path . '?p=' . ($page + 1) ?>" >下一頁</a></li>
	<li class="last" ><a href="<?php echo $path . '?p='. $num ?>" >最後頁</a></li>
<?php
	}
?>
</ol>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<ins id="ad-bottom" class="ad adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-1821434700708607"
     data-ad-slot="8820361999"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<!--[if lt IE 9]>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<![endif]-->
<!--[if gte IE 9]><!-->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/2.1.3/jquery.min.js"></script>
<!--[endif]-->
<script src="js/index.min.js"></script>
<?php
    if ($page !== 1) {
?>
<script src="js/index2.min.js"></script>
<?php
    }
?>
</script>
<script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','//www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-6851063-2', 'auto');
  ga('send', 'pageview');
</script>
</body>
</html>
<?php
	$html = preg_replace(
			array(
				'/\s/u',
				'/ {2,}/u',
				'/<br \/>/u',
				'/" >/u'
			),
			array(
				' ',
				' ',
				"<br>",
				'">'
			),
			ob_get_contents()
		);

	ob_end_clean();

	echo $html;

	file_put_contents($cachePath, $html);
?>
