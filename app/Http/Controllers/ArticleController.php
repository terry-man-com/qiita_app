<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
// Guzzle読み込み
use GuzzleHttp\Client;
use PhpParser\Node\Stmt\TryCatch;
use Symfony\Component\HttpFoundation\Response;

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
        // QIITA_URLの値を取得してURLを定義
        $url = config('qiita.url') . '/api/v2/tags/' . $tag_id . '/items?per_page=' . $per_page;
        // $optionsにトークンを指定
        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];
        $articles = $this->sendRequest($method, $url, $options, Response::HTTP_OK);
        // 自分の記事を取得
        $method   = 'GET';
        $per_page = 10;
        
        $url = config('qiita.url') . '/api/v2/authenticated_user/items?per_page=' . $per_page;

        $options = [
            'headers' =>[
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        $my_articles = $this->sendRequest($method, $url, $options, Response::HTTP_OK);

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

        $tag_array = explode(' ', $request->tags);
        $tags = array_map(function ($tag) {
            return ['name' => $tag];
        }, $tag_array);

        // 送信するデータを整形
        $data = [
            'title'   => $request->title,
            'body'    => $request->body,
            'private' => true,
            'tags'    => $tags
        ];

        $options = [
            'headers' => [
                'Content-Type'  => 'application/json',
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
            'json' => $data
        ];

        $response = $this->sendRequest($method, $url, $options, Response::HTTP_CREATED);

            // ステータスコードが201であれば成功
            if ($response) {
                return redirect()->route('articles.index')->with('flash_message', '記事の投稿に成功しました');
            } else {
                return back()->withErrors(['error' => '記事投稿に失敗しました']);
            }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(string $id)
    {
        $method = 'GET';

        $url = config('qiita.url') . '/api/v2/items/' . $id;

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ]
        ];

        $article = $this->sendRequest($method, $url, $options, Response::HTTP_OK);

        
        if ($article) {
                // 変換するクラスをインスタンス化して設定を追加
                $parser = new \cebe\markdown\GithubMarkdown();
                $parser->keepListStartNumber = true; // olタグの番号を初期化を有効する。
                $parser->enableNewlines = true; // 改行を有効にする。
                // MarkdownをHTML文字列に変換し、HTMLに変換(エスケープする)
                $html_string = $parser->parse($article->body);
                $article->html = new \Illuminate\Support\HtmlString($html_string);
            } else {
                return back();
            }

        $method = 'GET';

        $url = config('qiita.url') . '/api/v2/authenticated_user';

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ],
        ];

        $user = $this->sendRequest($method, $url, $options, Response::HTTP_OK);

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

        $article = $this->sendRequest($method, $url, $options, Response::HTTP_OK);

        if ($article) {
            // tagsを配列からスペース区切りに変換
            $tag_array = array_map(function ($tag) {
                return $tag->name;
            }, $article->tags);
            $article->tags = implode(' ', $tag_array);
            } else {
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

        $url = config('qiita.url') . '/api/v2/items/' . $id;

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
                'Authorization' => 'Bearer ' . config('qiita.token'),
                'Content-Type'  => 'application/json',
                'Accept'        => 'application/json'
            ],
            'json' => $data,
        ];

        $response = $this->sendRequest($method, $url, $options, Response::HTTP_OK);
            // ステータスコードが200であれば成功
            if ($response) {
                return redirect()->route('articles.index')->with('flash_message', '記事の更新に成功しました');
            } else {
                return back()->withErrors(['error' => '記事の更新に失敗しました']);
            }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $method = 'DELETE';

        $url = config('qiita.url') . '/api/v2/items/' . $id;

        $options = [
            'headers' => [
                'Authorization' => 'Bearer ' . config('qiita.token'),
            ]
        ];

        $response = $this->sendRequest($method, $url, $options, Response::HTTP_NO_CONTENT);
        if ($response == true) {
                return redirect()->route('articles.index')->with('flash_message', '記事を削除しました');
        } else {
            return redirect()->route('articles.index')->withErrors(['errors' => '記事の削除に失敗しました']);
        }
    }

    private function sendRequest($method, $url, $options, $expectedStatusCode)
    {
        $client = new Client();
        try {
            $response = $client->request($method, $url, $options);
            $statuCode = $response->getStatusCode();

            if ($statuCode == $expectedStatusCode) {
            if ($statuCode == Response::HTTP_NO_CONTENT) {
                return true;
            }

            $body = $response->getBody();
            return json_decode($body, false);
            }
            return null;
        } catch (\Throwable $th) {
            return null;
        }
    }
}
