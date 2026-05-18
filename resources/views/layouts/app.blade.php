<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	{{-- 各ページのタイトルを差し込む。未指定の場合は「ゆーとのvlog」を表示 --}}
	<title>@yield('title', 'ゆーとのvlog')</title>
</head>

<body>
	{{-- ヘッダー：サイト名とナビゲーションを表示 --}}
	<header>
		<h1>ゆーとのvlog</h1>
		<nav>
			{{-- ホームページへのリンク --}}
			<a href="/home">ホーム</a>
		</nav>
	</header>

	{{-- メインコンテンツ：各ページの内容がここに差し込まれる --}}
	<main>
		@yield('content')
	</main>

	{{-- フッター：コピーライトを表示 --}}
	<footer>
		<p>&copy; 2025 ゆーとのvlog</p>
	</footer>
</body>

</html>