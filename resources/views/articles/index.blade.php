@extends('layouts.main')

@section('content')
    <div class="article-contents">
        <aside class="aside-left">
            <nav id="left-nav">
                <ul class="left-conte">
                    <div class="home">
                        <li><i class="fas fa-home"></i></li>
                        <li><a href="{{ route('articles.index') }}" class="home-link">ホーム</a></li>
                    </div>
                    <li class="left-nav-li"><a href="#">タイムライン</a></li>
                    <li class="left-nav-li"><a href="#">トレンド</a></li>
                    <li class="left-nav-li"><a href="#">カレンダーフィード</a></li>
                </ul>
            </nav>
            <div id="left-hamburger">
                    <span class="left-inner_line" id="left-line1"></span>
                    <span class="left-inner_line" id="left-line2"></span>
                    <span class="left-inner_line" id="left-line3"></span>
            </div>
        </aside>

        <article>
            @if (!empty($articles))
                <ul>
                    @foreach ($articles as $article)
                    <li class="aritcles">
                        <p>
                            {{ $article->title }}
                        </p>
                    </li>
                    @endforeach
                </ul>
            @endif
        </article>

        <aside class="aside-right">
            <nav id="right-nav">
                <ul class="right-content">
                    <li class="right-nav-li"><a href="#">お知らせ</a></li>
                    <li class="right-nav-li"><a href="#">おすすめの記事</a></li>
                    <li class="right-nav-li"><a href="#">記事ランキング</a></li>
                </ul>
            </nav>

            <div id="right-hamburger">
                <span class="right-inner_line" id="right-line1"></span>
                <span class="right-inner_line" id="right-line2"></span>
                <span class="right-inner_line" id="right-line3"></span>
            </div>
        </aside>
    </div>
@endsection