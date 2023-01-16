# 列のテキスト選択 + コピーをしたいのだが

こんにちは、エンジニアの中山です。

ウェブベースのコラボレーションツールや SaaS で表（TABLE 要素）が使われることがよくありますが、列（縦方向）のテキスト選択 + コピーをしたくなったことはありませんか？例えば Confluence に表を挿入して情報を整理したときなどで、そうしたニーズは少なからずあるように思えます。というわけで、今回は列のテキスト選択 + コピーにチャレンジしてみます。

## ひらがな五十音表

TABLE 要素で作ったひらがな五十音表から

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-1.png' />

営業トークの「さしすせそ」（もしくは味付けの「さしすせそ」）を選択 + コピーしてみます。

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-2.png' />

やはり www みなさまご想像通りの結果です。文書の要素走査順序で「さ」から「そ」までの選択範囲となりました。

## writing-mode

行のテキスト選択ならば可能ということで …

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-8.png' />

CSS の [writing-mode](https://www.w3.org/TR/css-writing-modes-3/) を適用して

```
TABLE { writing-mode : vertical-lr; }
```

行と列を入れ替えてみてはどうだろうか？

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-3.png' />

これで行（入れ替え前の列）のテキスト選択を …

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-4.png' />

できない www 先ほどと同様、文書の要素走査順序で「さ」から「そ」までの選択範囲となりました。

## Excel or 拡張機能？

列のテキスト選択 + コピー … のニーズは確実にあるはずですが、ネットで検索したり周囲の意見をたずねてみたところ、みなさん

- Excel にはりつけてから列選択
- ブラウザに拡張機能を導入する

といった対応をされているようです。

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-9.png' />

あとは力技の bookmarklet で実現できないこともないですが、選択時に新たなスタイルを適用する方式の場合、コラボレーションツールや SaaS が提供する UX への副作用が気になります。加えてブラックボックス化された拡張機能だと安全性の観点で少々不安になりますね。

## ::selection

もう少し安全かつ副作用の少ない方法を探求してみましょう。疑似要素の ::selection を使って、選択したセル **以外のセル** に

```
TD::selection { background-color: transparent; }
```

を適用してみるのはどうでしょうか？

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-5.png' />

おおっ！いい感じで列選択を表現できました。この状態から copy イベントのリスナで

```
document.addEventListener('copy', ev => {

    /* (omitted) */

    let range = window.getSelection().getRangeAt(0);
    let start = range.startContainer;
    let end = range.endContainer;

    /* (omitted) */

});
```

として start ～ end の範囲でスタイルに応じたデータを取得すれば「列のテキスト選択 + コピー」は実現できそうですね。

… というわけで、上記方針で細部（TD, TH 両方の考慮やネストした要素への対応）含めて実装した bookmarklet がこちらです。

```
javascript:!function(e,t){function n(){return Symbol()}const o=n(),s=n(),r=n(),a="selection",i="disabled",l=(e=>{let t={};for(name in e)t[name]=String.fromCharCode(e[name]);return t})({TAB:9,LF:10,SP:32});t.getElementsByTagName("*").item(0).appendChild(t.createElement("STYLE"));let d="[data-"+a+'="'+i+'"]',c="TH"+d+"::selection,TH"+d+l.SP+"*::selection,TD"+d+"::selection,TD"+d+l.SP+"*::selection{background-color: transparent !important;}";function u(...e){return function(t){if(t.nodeType===Node.ELEMENT_NODE)for(let n of e)if(t.nodeName.toUpperCase()===n)return!0;return!1}}t.styleSheets[t.styleSheets.length-1].insertRule(c),HTMLTableElement.prototype[s]=function(){this[o]||(this[o]={},this[o].debug=function(e){console.log(e)},this[o].handleMouseMove=function(e){1==e.buttons&&u("TH","TD")(e.target)&&e.target.dataset[a]===i&&(this[o].debug("added target"),delete e.target.dataset[a])}.bind(this),this[o].handleMouseLeave=function(t){1!=t.buttons&&e.getSelection().getRangeAt(0).collapse(),Array.prototype.slice.call(this.querySelectorAll("TH, TD")).forEach((e=>{delete e.dataset[a]})),-1!==e.navigator.userAgent.toLowerCase().indexOf("firefox")&&(this[o].debug("use trick for Firefox"),function(t){let n=t.style.opacity;t.style.opacity=.99*Number.parseFloat(e.getComputedStyle(t).opacity),window.setTimeout((()=>{t.style.opacity=n}),0)}(this)),this.removeEventListener("mousemove",this[o].handleMouseMove),this.removeEventListener("mouseleave",this[o].handleMouseLeave),this[o].debug("stopped"),this[o].started=!1}.bind(this),this[o].started=!1),this[o].started||(this.addEventListener("mousemove",this[o].handleMouseMove),this.addEventListener("mouseleave",this[o].handleMouseLeave)),Array.prototype.slice.call(this.querySelectorAll("TH, TD")).forEach((e=>{e.dataset[a]=i})),this[o].debug("started"),this[o].started=!0},HTMLTableElement.prototype[r]=function(){let t=[];if(!this[o]||!this[o].started)return t;let n=e.getSelection().getRangeAt(0),s=n.startContainer.parentElement.closest("TH, TD"),r=n.endContainer.parentElement.closest("TH, TD"),l=function(e){try{return[e.parentNode.rowIndex,e.cellIndex]}catch(e){return[-1,-1]}};this[o].debug("range : ("+l(s).join(",")+") - ("+l(r).join(",")+")");let d=!1,c=-1;return Array.prototype.slice.call(this.querySelectorAll("TH, TD")).forEach((e=>{let o=e.closest("TR").rowIndex;o>c&&(c=o,t[c]=[]),d?e===r?(t[c].push(r.innerText.substring(0,n.endOffset)),d=!1):e.dataset[a]!==i&&t[c].push(e.innerText):e===s&&(t[c].push(s.innerText.substring(n.startOffset)),d=!0)})),t.filter((e=>e))},t.addEventListener("selectstart",(e=>{let t=e.composedPath().find(u("TABLE"));t&&(t.getElementsByTagName("TABLE").length>0||t[s]())})),t.addEventListener("copy",(e=>{let n=[];if(Array.prototype.slice.call(t.getElementsByTagName("TABLE")).forEach((e=>{n=n.concat(e[r]())})),n.length>0){for(let e=0;e<n.length;e++)n[e]=n[e].join(l.TAB);!async function(e){await navigator.clipboard.writeText(e)}(n.join(l.LF))}}))}(window,document);void(0);
```

[デモページ](https://pj-corridor.net/table-demo/demo.html) を用意したので UX をご確認ください。

元となるソースコードも [こちら](https://github.com/nakayama-kazuki/2020/blob/master/bookmarklets/copy-column-v2.txt) で公開してますので、必要に応じでカスタマイズしてご利用ください。ちなみに「列のテキスト選択 + コピー」とはいいつつも、このような範囲の選択 + コピーも可能です。

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-6.png' />

先ほど挫折した writing-mode での「さしすせそ」選択はこうなります。

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-7.png' />

## ブラウザ拡張

bookmarklet 実行のひと手間すら惜しい、そんな各位にはブラウザ拡張もおすすめです。まずは manifest.json を用意してください。

```
{
    "name" : "copy-column",
    "description" : "customize selecting & copying table cells",
    "version" : "1.0.0",
    "manifest_version" : 3,
    "content_scripts" : [
        {
            "matches" : ["*://*/*"],
            "js" : ["copy-column.js"],
            "run_at" : "document_idle",
            "all_frames" : true
        }
    ]
}
```

次いで、以下は Chrome における手順です（Firefox でも大筋は一緒です）。

1. 適当なフォルダに manifest.json と [ソースコード](https://github.com/nakayama-kazuki/2020/blob/master/bookmarklets/copy-column-v2.txt)  を manifest.json 内に指定したファイル名（今回は copy-column.js）で保存
2. Chrome の設定画面を開く
3. デベロッパーモードに変更する
4. パッケージ化されていない拡張機能を読み込む、で上のフォルダを指定する

これで常に「列のテキスト選択 + コピー」を実行できるようになりました。

## ネイティブ実装提案

あらためて思うことですが 99% のユーザーは TABLE 要素でテキスト選択 + コピーをする時には

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-2.png' />

ではなく

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-5.png' />

の結果を期待しているはずです。

ならばこのニーズに対しては bookmarklet やブラウザ拡張ではなく、ブラウザアプリケーションのネイティブ実装提案による解決を期待したいところです。さらに行選択 / 列選択 / 任意範囲選択のそれぞれに対応した UX は実装可能だと思います（実際にサンプルの bookmarklet は概ねそのような挙動になってます）。これができれば差別化要因となりブラウザ選択のモチベーションにもつながるはず … だと個人的には思っています。

そんなわけで、スルーされるとは思いつつベンダにネイティブ実装の提案をしてみます。続報（ないかもしれないけど）乞うご期待！

