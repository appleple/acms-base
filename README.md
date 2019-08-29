# Base


## ダウンロード・設置

ここにディレクトリを設置してください。

extension/plugins/Base

## 拡張アプリインストール

**管理ページ > 拡張アプリ** に移動すると The BASE が一覧にあります。ある事を確認できたら
The BASEのインストールボタンを押してください。


## The BASE アプリ登録

アプリのインストールが完了すると、管理ページの左側メニューに **拡張メニュー** に **The BASE**という項目が増えていると思います。
このページにAPIの設定をしていきます。まずThe BASE側でアプリの登録を行います。コールバックURLはアプリの登録で使いますのでコピーしておきます。

次に、[BASE Developers](https://developers.thebase.in/) から BASE APIを使えるようにするため、申請してください。
申請が通ったらログインします。ログインするとアプリ新規作成画面がありますので、以下のように設定してアプリを作成します。

* コールバックURL: a-blog cmsのThe BASE管理画面に表示されているコールバックURLを入力
* 利用権限: ユーザー情報を見る / 商品情報を見る にチェック
* 検索APIの利用: 利用するにチェック


![](https://developer.a-blogcms.jp/archives/009/201610/large-0f8906cda5ec5d0e9b5804360e27cedd.png)

## The BASE 設定画面（a-blog cms側）

アプリ登録が完了するとアプリ用と検索用の **client_id** と **client_secret** が発行されていると思います。
この値をコピーしてa-blog cmsの管理画面 > The BASE の設定画面に設定していきます。

* キャッシュタイム: 毎回APIのアクセスをすると制限に引っかかるので、キャッシュ時間を設定します。
* クライアントID: The BASE側で発行したclient_id
* クライアントシークレット: The BASE側で発行したclient_secret
* クライアントID（検索用）	: The BASE側で発行した検索用のclient_id
* クライアントシークレット（検索用）: The BASE側で発行した検索用のclient_secret

以上に情報を入力したら保存します。

保存後、再度同じページにアクセスして **認証** ボタンをおして認証します。ボタンを押すと、The BASE側で認証画面が出てくると思います。
メールアドレスとパスワードをいれて認証してください。認証後、自動的にリダイレクトされa-blog cmsの管理画面に戻ってきます。
**認証に成功しました** と表示されていれば連携成功です。

![](https://developer.a-blogcms.jp/archives/009/201610/large-2268cd51e95971907d6b20223de685b6.png)

## モジュールを利用してみる

連携が成功したので実際にa-blog cmsのモジュールを利用してみます。以下３つのモジュールが用意されています。

* 商品一覧（Base_Items）
* 商品詳細（Base_Detail）
* 商品検索（Base_Search）

モジュールID化してご利用ください。また、商品詳細や商品検索は対応するURLコンテキストを取得できるようにモジュールIDの設定を行ってください。

スニペットはモジュールの表示設定画面にあります。

### 商品一覧（Base_Items）

ショップの商品を一覧で表示するためのモジュール

* 並び替え項目: 並び順で使われるフィールドを指定
* 並び順: 昇順・降順
* 表示数: 表示数
* オフセット: 開始位置を指定

![](https://developer.a-blogcms.jp/archives/009/201610/tiny-5a46fe40a2aaa535e5952d44d0517fd1.png)

![](https://developer.a-blogcms.jp/archives/009/201610/large-e3dd0507b81e52b5cae5f11fc45b0fc2.png)

![](https://developer.a-blogcms.jp/archives/009/201610/large-5e1f071784ad43b9e40a7e42a287d267.png)


### 商品検索（Base_Search）

* 検索ワード: 検索ワードにはa-blog cmsのキーワードURLコンテキストを使用
* 並び替え項目: 並び順で使われるフィールドを指定
* 並び順: 昇順・降順
* 表示数: 表示数
* オフセット: 開始位置を指定
* ショップID: ショップIDで絞り込み可能。空だとThe BASEにある別ショップの商品も検索対象に
* 検索対象: 検索を行うフィールドを指定

![](https://developer.a-blogcms.jp/archives/009/201610/large-2feeb81baf21cdc44f66bc0ca21a8189.png)

![](https://developer.a-blogcms.jp/archives/009/201610/large-6a322d47568422f70a7613cc47d54976.png)

![](https://developer.a-blogcms.jp/archives/009/201610/large-ae05d9973e94e180eae0935aeddbd72e.png)

The BASE は無料で簡単にネットショップを開設できるサービスです。本格的なECサイトは難しいですが、簡単にショップを開設し、a-blog cmsで商品の紹介を行う事ができるようになります。
