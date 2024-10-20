<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    <script src="https://kit.fontawesome.com/0645383f85.js" crossorigin="anonymous"></script>
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/github-markdown-css@3.0.1/github-markdown.min.css ">
    <title>Qiita_app</title>
</head>
<body>
    <header class="page-header">
        <p class="app-name"><a href="{{ route('articles.index') }}"><b>Qiita_app</b></a></p>
        <p class="post-button"><a href="{{ route('articles.create') }}" class="button"><i class="far fa-edit"></i>投稿する</a></p>
    </header>

    <main>
        @yield('content')
    </main>

    <footer>
        <div class="wrapper">
            <p><a href="{{ route('articles.index') }}">Qiita_app</a></p>
            <p><small>&copy; 2021 NEXT REVOLUTION</small></p>
        </div>
    </footer>
    <script src="{{ asset('js/app.js') }}"></script>
</body>
</html>