<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Guzzle読み込み
use GuzzleHttp\Client;

class ArticleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $method = 'GET';
        $tag_id = 'PHP';
        $per_page = 10;

        $url = config('qiita.url') . '/api/v2/tags/' . $tag_id . '/items?per_page=' . $per_page;

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $articles = json_decode($body, false);
        } catch (\Throwable $th) {
            $articles = null;
        }
        
        $method = 'GET';
        $per_page = 10;

        $url = config('qiita.url') . '/api/v2/authenticated_user/items?per_page=' . $per_page;

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'), 
            ],
        ];

        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $my_articles = json_decode($body, false);
        } catch (\Throwable $th) {
            $my_articles = null;
        }

        return view('articles.index')->with(compact('articles', 'my_articles'));
    }


    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        return view('articles.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $method = 'POST';
         // QIITA_URLの値を取得してURLを定義
        $url = config('qiita.url') . '/api/v2/items';

         // スペース区切りの文字列を配列に変換し、JSON形式に変換         
        $tag_array = explode(' ', $request->tags);
        $tags = array_map(function ($tag) {
            return ['name' => $tag];
        }, $tag_array);
        // 送信するデータを整形

        $data = [
            'title' => $request->title,
            'body' => $request->body,
            'private' => true,
            'tags' => $tags
        ];
        $options = [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
            'json' => $data,
        ];
        // Client(接続する為のクラス)を生成
        $client = new Client();

        try {
            // データを送信する
            $client->request($method, $url, $options);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
           // GuzzleHttpで発生したエラーの場合はcatchする
            return back()->withErrors(['error' => '記事投稿に失敗しました']);
        }
        return redirect()->route('articles.index')->with('flash_message', '記事の投稿に成功しました' );
    }


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $method = 'GET';

        // QIITA_URLの値を取得してURLを定義
        $url = config('qiita.url') . '/api/v2/items/' . $id;

        //Client(接続する為のクラス)を生成
        $client = new Client();

        //$optionにトークンを指定
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),//アクセスキーを指定
            ],
        ];

        try {
            // データを取得し、JSON形式からPHPの変数に変換
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $article = json_decode($body, false);

            // 変換クラスをインスタンス化して設定に追加
            $parser = new \cebe\markdown\GithubMarkdown();
            $parser->keepListStarNumber = true;
            $parser->enableNewlines = true;

            // MarkdownをHTMl文字列に変換し、HTMLに変換(エスケープする)
            $html_string = $parser->parse($article->body);
            $article->html = new \Illuminate\Support\HtmlString($html_string);
        } catch (\Throwable $th) {
            return back();
        }

        $method = 'GET';

        $url = config('qiita.url') . '/api/v2/authenticated_user/';

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $user = json_decode($body, false);
        } catch (\Throwable $th) {
            return back();
        }

        return view('articles.show')->with(compact('article', 'user'));
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        $method = 'GET';

        $url = config('qiita.url') . '/api/v2/items/' . $id;

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
            $body = $response->getBody();
            $article = json_decode($body, false);

            $tag_array = array_map(function ($tag) {
                return $tag->name;
            }, $article->tags);
            $article->tags = implode(' ', $tag_array);
        } catch (\Throwable $th){
            return back();
        }
        
        return view('articles.edit')->with(compact('article'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        $method = 'PATCH';

        $url = config('qiita.url') . '/api/v2/items/'. $id;

        $tag_array = explode(' ', $request->tags);
        $tags = array_map(function ($tag) {
            return ['name' => $tag];
        }, $tag_array);

        $data = [
            'title' => $request->title,
            'body' => $request->body,
            'private' => true,
            'tags' => $tags
        ];

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
            'json' => $data,
        ];

        $client = new Client();

        try {
            $response = $client->request($method, $url, $options);
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            return back()->withErrors(['error' => '記事の更新に失敗しました']);
        }

        return redirect()->route('articles.index')->with('flash_message', '記事の更新に成功しました');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
    }
}
