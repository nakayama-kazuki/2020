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

- エクセルにはりつけてから列選択
- ブラウザに拡張機能を導入する

といった対応をされているようです。あとは力業の bookmarklet で実現できないこともないですが、選択時に新たなスタイルを適用する方式の場合、コラボレーションツールや SaaS が提供する UX への副作用が気になります。加えてブラックボックス化された拡張機能だと安全性の観点で少々の不安になりますね。

## ::selection

もう少々安全かつ副作用の少ない方法を探求してみましょう。疑似要素の ::selection を使って選択した列以外に

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
javascript:!function(e,t){const a="selection",n="disabled";0===t.styleSheets.length&&document.getElementsByTagName("SCRIPT").item(0).parentNode.appendChild(document.createElement("STYLE"));let s=String.fromCharCode(32),o="[data-"+a+'="'+n+'"]',r="TH"+o+"::selection,TH"+o+s+"*::selection,TD"+o+"::selection,TD"+o+s+"*::selection{background-color: transparent !important;}";function i(...e){return function(t){if(t.nodeType===Node.ELEMENT_NODE)for(let a of e)if(t.nodeName.toUpperCase()===a)return!0;return!1}}t.styleSheets[0].insertRule(r),HTMLTableElement.prototype.startCustomSelect=function(){this.getElementsByTagName("TABLE").length>0||(this._exData||(this._exData={},this._exData.debug=function(e){console.log(e)},this._exData.handleMouseMove=function(e){1==e.buttons&&i("TH","TD")(e.target)&&e.target.dataset[a]===n&&(this._exData.debug("added target"),delete e.target.dataset[a])}.bind(this),this._exData.handleMouseLeave=function(t){1!=t.buttons&&e.getSelection().getRangeAt(0).collapse(),Array.prototype.slice.call(this.querySelectorAll("TH, TD")).forEach((e=>{delete e.dataset[a]})),this.removeEventListener("mousemove",this._exData.handleMouseMove),this.removeEventListener("mouseleave",this._exData.handleMouseLeave),this._exData.debug("stopped"),this._exData.started=!1}.bind(this),this._exData.started=!1),this._exData.started||(this.addEventListener("mousemove",this._exData.handleMouseMove),this.addEventListener("mouseleave",this._exData.handleMouseLeave)),Array.prototype.slice.call(this.querySelectorAll("TH, TD")).forEach((e=>{e.dataset[a]=n})),this._exData.debug("started"),this._exData.started=!0)},HTMLTableElement.prototype.getSelectedData=function(){let t=[];if(!this._exData||!this._exData.started)return t;let s=e.getSelection().getRangeAt(0),o=s.startContainer.parentElement.closest("TH, TD"),r=s.endContainer.parentElement.closest("TH, TD"),i=function(e){try{return[e.parentNode.rowIndex,e.cellIndex]}catch(e){return[-1,-1]}};this._exData.debug("range : ("+i(o).join(",")+") - ("+i(r).join(",")+")");let l=!1;return Array.prototype.slice.call(this.querySelectorAll("TH, TD")).forEach((e=>{l?e===r?(t.push(r.innerText.substring(0,s.endOffset)),l=!1):e.dataset[a]!==n&&t.push(e.innerText):e===o&&(t.push(o.innerText.substring(s.startOffset)),l=!0)})),t},t.addEventListener("selectstart",(e=>{let t=e.composedPath().find(i("TABLE"));t&&t.startCustomSelect()})),t.addEventListener("copy",(e=>{let a=[];Array.prototype.slice.call(t.getElementsByTagName("TABLE")).forEach((e=>{a=a.concat(e.getSelectedData())})),a.length>0&&async function(e){await navigator.clipboard.writeText(e)}(a.join("\n"))}))}(window,document);void(0);
```

元となる [ソースコードはこちら](https://github.com/nakayama-kazuki/2020/blob/master/bookmarklets/copy-column-v2.txt) にありますので、必要に応じでカスタマイズしてご利用ください。ちなにみ「列のテキスト選択 + コピー」とはいいつつも、このような範囲の選択 + コピーも可能です。

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-6.png' />

先ほど挫折した writing-mode での「さしすせそ」選択はこうなります。

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-7.png' />

## ネイティブ実装提案

あらためて思うことですが 99% のユーザーは TABLE 要素でテキスト選択 + コピーをする時には

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-2.png' />

ではなく

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-5.png' />

の結果を期待しているはずです。

また、選択範囲は排他的ではなくブラウザアプリケーションの実装次第で行選択 / 列選択 / 任意範囲選択をそれぞれ実現することは可能だと思います（実際にサンプルの bookmarklet は概ねそのような挙動です）。さらに、これは差別化要因となりブラウザ選択のモチベーションにもつながるはずです。

ですので、スルーされるとは思いつつブラウザベンダにネイティブ実装の提案をしてみます。続報（ないかもしれないけど）乞うご期待！

