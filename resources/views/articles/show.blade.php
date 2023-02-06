@extends('layouts.main')

@section('content')
    <div class="article-show">
        <aside class="aside-lgtm"> <!-- 脚注や用語の説明の際に使う -->
            <div class="show-lgtm">
                <span>LG<br></span>
            </div>
            <p class="show-like-count">{{ $article->likes_count }}</p>
        </aside>

        <article class="article-show-content"> <!-- ユーザーアカウントと投稿日を記載 -->
            <div class="user-wrapper">
                <img src={{ $article->user->profile_image_url }} class="user-img">
                {{ $article->user->name }}さん
                <p>投稿日{{ \Carbon\Carbon::parse($article->created_at)->setTimezone('Asia/Tokyo')->format('Y年m月d日　H時i分s秒');}}</p>
            </div>

            <h1>{{ $article->title }}</h1>
            <p class="article-tag"><!-- タグとコンテンツ -->
                <i class="fas fa-tags size"></i>
                @foreach ($article->tags as $article_tag)
                    <span class="mgr">
                        {{ $article_tag->name }}
                        @if (next($article->tags))
                            @php echo ',' @endphp
                        @endif
                    </span>
                @endforeach
            </p>
            <div class="markdown-body">
                <p class="article-body">{{ $article->html }}</p>
            </div>
        </article>
    </div>
    @if ($article->user->permanent_id == $user->permanent_id)
        <div class="my-article-button">
            <button type="button" class="edit-button" onclick="location.href='{{ route('articles.edit', $article->id) }}'">編集する</button>
            <button type="submit" class=""></button>
        </div>
    @endif
        <button type="button" class="show-return-button" onclick="location.href='{{ route('articles.index')}}'">一覧に戻る</button>
@endsection