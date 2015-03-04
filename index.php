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
		$date = date('Y-m-d H:i:s', $item['timestamp']);
		$description = $item['description'];
		$source = $sourceMap[$item['source']];

		if (isset($item['image'])) {
			$description .= '<br><img src="' . $item['image'] . '">';
		}
?>
<article>
	<p><a href="<?php echo $source['link'] ?>" target="_blank" ><?php echo $source['title'] ?></a> <time datetime="<?php echo $date ?>" ><?php echo $date ?></time></p>
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
		if ($i === 10) {
?>
<script async src="//pagead2.googlesyndication.com/pagead/js/adsbygoogle.js"></script>
<!-- Realtime Mid RWD -->
<ins id="ad-mid" class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-1821434700708607"
     data-ad-slot="6212292798"
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
<!-- Realtime Bottom RWD -->
<ins id="ad-bottom" class="adsbygoogle"
     style="display:block"
     data-ad-client="ca-pub-1821434700708607"
     data-ad-slot="8820361999"
     data-ad-format="auto"></ins>
<script>
(adsbygoogle = window.adsbygoogle || []).push({});
</script>
<script>
(function() {
	var images = document.images,
		n = images.length;

	for (var i = 0; i < n; ++i) {
		var image = images[i];

		image.onerror = function () {
			this.style.display = 'none';
		};

		if (image.complete === true) {
			image.src = image.src;
		}
	}

	if (mobileCheck()) {
		var links = document.getElementsByTagName('a'),
			n = links.length;

		for (var i = 0; i < n; ++i) {
			links[i].target = '_self';
		}
	}

	function mobileCheck() {
  		var check = false;

		(function(a,b){if(/(android|bb\d+|meego).+mobile|avantgo|bada\/|blackberry|blazer|compal|elaine|fennec|hiptop|iemobile|ip(hone|od)|iris|kindle|lge |maemo|midp|mmp|mobile.+firefox|netfront|opera m(ob|in)i|palm( os)?|phone|p(ixi|re)\/|plucker|pocket|psp|series(4|6)0|symbian|treo|up\.(browser|link)|vodafone|wap|windows ce|xda|xiino/i.test(a)||/1207|6310|6590|3gso|4thp|50[1-6]i|770s|802s|a wa|abac|ac(er|oo|s\-)|ai(ko|rn)|al(av|ca|co)|amoi|an(ex|ny|yw)|aptu|ar(ch|go)|as(te|us)|attw|au(di|\-m|r |s )|avan|be(ck|ll|nq)|bi(lb|rd)|bl(ac|az)|br(e|v)w|bumb|bw\-(n|u)|c55\/|capi|ccwa|cdm\-|cell|chtm|cldc|cmd\-|co(mp|nd)|craw|da(it|ll|ng)|dbte|dc\-s|devi|dica|dmob|do(c|p)o|ds(12|\-d)|el(49|ai)|em(l2|ul)|er(ic|k0)|esl8|ez([4-7]0|os|wa|ze)|fetc|fly(\-|_)|g1 u|g560|gene|gf\-5|g\-mo|go(\.w|od)|gr(ad|un)|haie|hcit|hd\-(m|p|t)|hei\-|hi(pt|ta)|hp( i|ip)|hs\-c|ht(c(\-| |_|a|g|p|s|t)|tp)|hu(aw|tc)|i\-(20|go|ma)|i230|iac( |\-|\/)|ibro|idea|ig01|ikom|im1k|inno|ipaq|iris|ja(t|v)a|jbro|jemu|jigs|kddi|keji|kgt( |\/)|klon|kpt |kwc\-|kyo(c|k)|le(no|xi)|lg( g|\/(k|l|u)|50|54|\-[a-w])|libw|lynx|m1\-w|m3ga|m50\/|ma(te|ui|xo)|mc(01|21|ca)|m\-cr|me(rc|ri)|mi(o8|oa|ts)|mmef|mo(01|02|bi|de|do|t(\-| |o|v)|zz)|mt(50|p1|v )|mwbp|mywa|n10[0-2]|n20[2-3]|n30(0|2)|n50(0|2|5)|n7(0(0|1)|10)|ne((c|m)\-|on|tf|wf|wg|wt)|nok(6|i)|nzph|o2im|op(ti|wv)|oran|owg1|p800|pan(a|d|t)|pdxg|pg(13|\-([1-8]|c))|phil|pire|pl(ay|uc)|pn\-2|po(ck|rt|se)|prox|psio|pt\-g|qa\-a|qc(07|12|21|32|60|\-[2-7]|i\-)|qtek|r380|r600|raks|rim9|ro(ve|zo)|s55\/|sa(ge|ma|mm|ms|ny|va)|sc(01|h\-|oo|p\-)|sdk\/|se(c(\-|0|1)|47|mc|nd|ri)|sgh\-|shar|sie(\-|m)|sk\-0|sl(45|id)|sm(al|ar|b3|it|t5)|so(ft|ny)|sp(01|h\-|v\-|v )|sy(01|mb)|t2(18|50)|t6(00|10|18)|ta(gt|lk)|tcl\-|tdg\-|tel(i|m)|tim\-|t\-mo|to(pl|sh)|ts(70|m\-|m3|m5)|tx\-9|up(\.b|g1|si)|utst|v400|v750|veri|vi(rg|te)|vk(40|5[0-3]|\-v)|vm40|voda|vulc|vx(52|53|60|61|70|80|81|83|85|98)|w3c(\-| )|webc|whit|wi(g |nc|nw)|wmlb|wonu|x700|yas\-|your|zeto|zte\-/i.test(a.substr(0,4)))check = true})(navigator.userAgent||navigator.vendor||window.opera);

		return check;
	}
})();
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
