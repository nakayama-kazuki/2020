# DoH + Set-Cookie で興味関心が丸裸！？

こんにちは ~~Cookie エンジニア~~ 広告エンジニアの中山です。<br />
プラットフォーマーのポリシーや法規制の強化に伴い、ますます制限の厳しくなる 3rd-party Cookie ですが、今回は DNS over HTTPS（以下 DoH）と Set-Cookie の組み合わせについて掘り下げてみたいと思います。<br />

![](01.jpg)

## DoH への賛同と懸念

DoH を利用することで User-Agent と DNS キャッシュサーバ間の通信を「盗聴」「改竄」「なりすまし」から守ることができます。<br />
プライバシー保護とセキュリティー向上への期待から、プラットフォーマーは DoH に賛同を示しています。<br />

- [Microsoft](https://techcommunity.microsoft.com/t5/Networking-Blog/Windows-will-improve-user-privacy-with-DNS-over-HTTPS/ba-p/1014229)
- [Mozilla](https://blog.mozilla.org/futurereleases/2019/09/06/whats-next-in-making-dns-over-https-the-default/)
- [Google](https://blog.chromium.org/2019/09/experimenting-with-same-provider-dns.html)

ところが ISP の Comcast はこの流れに [警戒感を表明](https://www.vice.com/en_us/article/9kembz/comcast-lobbying-against-doh-dns-over-https-encryption-browsing-data) しました。<br />
プラットフォーマーによる DNS の集中化が様々なリスクを引き起こす、という主張です。<br />

> The unilateral centralization of DNS raises serious policy issues relating to cybersecurity, privacy, antitrust, national security and law enforcement, network performance and service quality (including 5G), and other areas.

しかし、この主張は [プラットフォーマーの反撃](https://blog.mozilla.org/blog/2019/11/01/asking-congress-to-examine-isp-data-practices/) を受けます。<br />
Mozilla 曰く、むしろ ISP がデータを独占し、穏やかならぬ用途に活用しているのではないか、というわけです。<br />

> These developments have raised serious questions. How is your browsing data being used by those who provide your internet service? Is it being shared with others? And do consumers understand and agree to these practices? We think it's time Congress took a deeper look at ISP practices to figure out what exactly is happening with our data.

確かに DNS は興味関心情報のハニーポットです。<br />
邪悪な ISP ならばユーザーアカウントと名前解決要求を紐づけ、興味関心情報として蓄積～活用することも可能です。恐ろしや！<br />

では ISP の DNS を避けて DoH を利用すれば、このリスクから逃れることができるのでしょうか？<br />
[RFC 8484](https://tools.ietf.org/html/rfc8484)を確認してみましょう。<br />

> HTTP cookies SHOULD NOT be accepted by DOH clients unless they are explicitly required by a use case.

どうやら DoH では Cookie の利用は禁止されていないようです。<br />
実際に [cloudflare の Example](https://developers.cloudflare.com/1.1.1.1/dns-over-https/wireformat/) からは set-cookie が見つかります。<br />

'''http
HTTP/2 200
date: Fri, 23 Mar 2018 05:14:02 GMT
content-type: application/dns-message
content-length: 49
cache-control: max-age=0
set-cookie: \__cfduid=dd1fb65f0185fadf50bbb6cd14ecbc5b01521782042;
    expires=Sat, 23-Mar-19 05:14:02 GMT; path=/; domain=.cloudflare.com; HttpOnly
server: cloudflare-nginx
cf-ray: 3ffe69838a418c4c-SFO-DOG
'''

ということは DoH を利用する User-Agent がブラウザの HTTP Set-Cookie / HTTP Cookie メカニズムを踏襲する場合、邪悪なサービス提供者ならば Set-Cookie で付与した識別情報と名前解決要求を紐づけ、興味関心情報として蓄積～活用できそうです。なんということでしょう！<br />

## Set-Cookie は有効か？

では Firefox 71.0 を使い、以下の手順で実験してみましょう。<br />

### 1. 自前 DoH サービスを用意

今回は 127.0.0.1 上に DoH 応答を生成する自前 DoH サービス（doh.php）を用意しました。<br />
あわせて静的な HTML ファイル（hello.html）と Cookie の送受信を確認するコンテンツ（check-cookie.php）も用意します。<br />

``` {.dark}
https://127.0.0.1/[doh.php](★)
https://127.0.0.1/[hello.html](★)
https://127.0.0.1/[check-cookie.php](★)
```

### 2. Firefox の設定を変更

インターネット設定にて ...<br />

- DNS over HTTPS を有効にする。
- プロバイダーを使用せずに URL（自前 DoH サービス）を指定する。

![]（★02.png）

about:config にて ...<br />

- network.trr.bootstrapAddress に 127.0.0.1 を指定する。<br />これにより OS の名前解決をスキップして自前 DoH サービスのドメインが 127.0.0.1 に解決されます。
- network.trr.confirmationNS に skip を指定する。<br />これにより起動時の動作チェックを割愛します。

![]（★02.png）

なお network.trr に関する詳細は [mozilla wiki](https://wiki.mozilla.org/Trusted_Recursive_Resolver) をご確認ください。

### 3. Firefox で hello.html を閲覧する

``` {.dark}
https://my-test-web-service.com/hello.html
```
my-test-web-service.com の名前解決のために DoH 要求が自前 DoH サービスに送信されます。<br />

```http {.dark}
Host: i27.o.o.i
User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:71.0) Gecko/20100101 Firefox/71.0
Accept: application/dns-message
Accept-Language: ja,en-US;q=0.7,en;q=0.3
Accept-Encoding: gzip, deflate, br
Cache-Control: no-store
Content-Type: application/dns-message
Content-Length: 65
Connection: keep-alive

00,00,01,00,00,01,00,00,00,
00,00,01,18,73,6f,6d,65,2d,
6e,6f,6e,2d,65,78,69,73,74,
65,6e,74,2d,64,6f,6d,61,69,
6e,03,63,6f,6d,00,00,01,00,
01,00,00,29,10,00,00,00,00,
00,00,08,00,08,00,04,00,01,
00,00
```

自前 DoH サービスから Set-Cookie + DoH 応答
これが some-doh-provider.com ドメインの Cookie として扱われるのかどうかを後程確認します。
ちなみに今回は some-web-service.com も 127.0.0.1 に解決しています。

```http {.dark}
Content-Type: application/dns-message
Content-Length: 90
Cache-Control: max-age=0
X-Resolve: some-non-existent-domain.com --> 127.0.0.1
X-Sent-Cookie: (none)
Connection: Close
Set-Cookie: doh=48; Secure; HttpOnly

00,00,81,00,00,01,00,01,00,
00,00,00,18,73,6f,6d,65,2d,
6e,6f,6e,2d,65,78,69,73,74,
65,6e,74,2d,64,6f,6d,61,69,
6e,03,63,6f,6d,00,00,01,00,
01,18,73,6f,6d,65,2d,6e,6f,
6e,2d,65,78,69,73,74,65,6e,
74,2d,64,6f,6d,61,69,6e,03,
63,6f,6d,00,00,01,00,01,00,
00,00,80,00,04,7f,00,00,01
```

https://127.0.0.1/
は phpinfo を表示する設定にしていたので
https://some-web-service.com/
でも以下の通りです。

3. Firefox で自前 DoH サービスど同じドメインのページを閲覧する

例えば

https://some-doh-provider.com/check-cookie.php

が以下のようなコンテンツだった場合、送信された Cookie ヘッダを確認します。

<?php

header('Content-Type: text/plain');
header("Set-Cookie: www=true; Secure; HttpOnly");
$arr = apache_request_headers();
foreach ($arr as $fname => $value) {
	print "{$fname}: {$value}\n";
}

?>

（★結果）

Set-Cookie: doh=true; Secure; HttpOnly
は送信されませんでした。
一方で check-cookie.php で Set-Cookie した値は有効です。

（★DB の値）

Firefox 71.0 の実装では DoH 応答の Set-Cookie は無視されるため、
仮に DoH サービス提供者が邪悪であっても、興味関心情報の蓄積～活用は難しいということが確認できました。
このケースにおいては DoH の利用は従来よりもプライバシーセーフである、と言えそうです。
皆さんも他のブラウザを用いて実験してみてください。

* DoH の可能性

ところ Web アプリケーションの開発～テストの際にはしばしば hosts を変更します。
そして、たまに設定ミスや元に戻すのを忘れてハマる人を時々見かけます。
DoH 利用の可能性として、
同じ環境でテストをしているグループ向けの設定を自前 DoH で作って、
テスト実施者はブラウザの DoH の on/off で利用する環境を切り替える、
という運用をすれば（上述のミスも減って）生産性を高められるようなな～と思った次第です。

（よろしければ techscore のプライバシー関連記事もお読みください）
https://www.techscore.com/blog/tag/privacy/
