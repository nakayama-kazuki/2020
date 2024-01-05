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
- ウェブストアから拡張機能を導入

といった対応をされているようです。

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-9.png' />

後者については力技 DOM 操作で実現できないこともないですが、コラボレーションツールや SaaS が提供する UX への副作用が気になります。加えてブラックボックス化された拡張機能だと安全性の観点で少々不安になりますね。

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
javascript:'use strict';(async function(){function n(a){let b=a.style.opacity;a.style.opacity=.99*Number.parseFloat(window.getComputedStyle(a).opacity);window.setTimeout(()=>{a.style.opacity=b},0)}function p(){return-1!==window.navigator.userAgent.toLowerCase().indexOf("firefox")}function q(a){return function(b){return b.nodeType===Node.ELEMENT_NODE&&b.nodeName.toUpperCase()===a.toUpperCase()?!0:!1}}function r(){let a=window.getSelection();for(let b=0;b<a.rangeCount;b++)a.getRangeAt(b).collapse()}function t(a){1==a.buttons&&e.update(a.target)}function u(a){r();e.stop()}function v(a){1!=a.buttons&&r();e.stop();a=a.target.closest("table");a.removeEventListener("mousemove",t);a.removeEventListener("mousedown",u);a.removeEventListener("mouseleave",v);console.log("selection stopped")}const g=Symbol(),w=Symbol(),h=(a=>{let b={};for(name in a)b[name]=String.fromCharCode(a[name]);return b})({TAB:9,LF:10,SP:32});class y{constructor(...a){this.__nodeNames=[];a.forEach(b=>{this.__nodeNames.push(b)});this.__attrName="x"+Math.random().toString(32).substring(2);this.__attrValue="x"+Math.random().toString(32).substring(2)}__attrSelector(){return"[data-"+this.__attrName+'="'+this.__attrValue+'"]'}createStyle(){let a=[];this.__nodeNames.forEach(b=>{a.push(b+this.__attrSelector()+"::selection");a.push(b+this.__attrSelector()+h.SP+"*::selection")});return a.join(",")+"{background-color: transparent !important;}"}disable(a){this.isDisabled(a)||(a.dataset[this.__attrName]=this.__attrValue,p()&&n(a))}isDisabled(a){return a.dataset[this.__attrName]===this.__attrValue}enable(a){this.isEnabled(a)||(delete a.dataset[this.__attrName],p()&&n(a))}isEnabled(a){return!a.dataset[this.__attrName]}}HTMLTableElement.prototype[g]=function(a){Array.from(this.rows).forEach(b=>{Array.from(b.cells).forEach(c=>{a(c)})})};class z extends y{constructor(){let a=["TD","TH"];super(...a);this.__nodeNames=a;this.__cache=null}get __table(){return this.__cache.startCell.closest("TABLE")}__calcBoundRect(){let a={},b=this.__cache.startCell,c=this.__cache.currentCell;a.offsetLeft=Math.min(b.offsetLeft,c.offsetLeft);a.offsetTop=Math.min(b.offsetTop,c.offsetTop);a.offsetWidth=Math.max(b.offsetLeft+b.offsetWidth,c.offsetLeft+c.offsetWidth)-a.offsetLeft;a.offsetHeight=Math.max(b.offsetTop+b.offsetHeight,c.offsetTop+c.offsetHeight)-a.offsetTop;return a}__inRect(a,b){return a.offsetLeft>b.offsetLeft||a.offsetTop>b.offsetTop||a.offsetLeft+a.offsetWidth<b.offsetLeft+b.offsetWidth||a.offsetTop+a.offsetHeight<b.offsetTop+b.offsetHeight?!1:!0}__update_1(){this.__table[g](a=>{this.disable(a)});this.enable(this.__cache.startCell)}__update_2(){let a=this.__calcBoundRect();this.__table[g](b=>{this.__inRect(a,b)?this.enable(b):this.disable(b)})}__inSameTable(a){return this.__table===a.closest("TABLE")}stop(){this.__cache&&(this.__table[g](a=>{this.enable(a)}),this.__cache=null)}update(a){let b=!1;for(let c=0;c<this.__nodeNames.length;c++)if(q(this.__nodeNames[c])(a)){b=!0;break}b&&(this.__cache?this.__cache.currentCell!==a&&(this.__inSameTable(a)?(this.__cache.currentCell=a,this.__update_2()):(this.stop(),this.update(a),console.log(" !! move to other table"))):(this.__cache={startCell:a,currentCell:a},this.__update_1()))}updating(){return!!this.__cache}}const e=new z;document.getElementsByTagName("*").item(0).appendChild(document.createElement("STYLE"));document.styleSheets[document.styleSheets.length-1].insertRule(e.createStyle());HTMLTableElement.prototype[w]=function(){let a=[];if(!e.updating())return a;let b=window.getSelection().getRangeAt(0),c=b.startContainer.parentElement.closest("TH, TD"),k=b.endContainer.parentElement.closest("TH, TD"),x=function(d){try{return[d.parentNode.rowIndex,d.cellIndex]}catch(l){return[-1,-1]}};console.log("range : ("+x(c).join(",")+") - ("+x(k).join(",")+")");let m=!1,f=-1;Array.prototype.slice.call(this.querySelectorAll("TH, TD")).forEach(d=>{let l=d.closest("TR").rowIndex;l>f&&(f=l,a[f]=[]);m?d===k?(a[f].push(k.innerText.substring(0,b.endOffset)),m=!1):e.isEnabled(d)&&a[f].push(d.innerText):d===c&&(a[f].push(c.innerText.substring(b.startOffset)),m=!0)});return a.filter(d=>d)};document.addEventListener("selectstart",a=>{!(a=a.composedPath().find(q("TABLE")))||0<a.getElementsByTagName("TABLE").length||(a.addEventListener("mousemove",t),a.addEventListener("mousedown",u),a.addEventListener("mouseleave",v),console.log("selection started"))});document.addEventListener("copy",a=>{let b=[];Array.prototype.slice.call(document.getElementsByTagName("TABLE")).forEach(c=>{b=b.concat(c[w]())});if(0<b.length){for(let c=0;c<b.length;c++)b[c]=b[c].join(h.TAB);(async c=>{await navigator.clipboard.writeText(c)})(b.join(h.LF));a.preventDefault()}})})();
```

[デモページ](https://pj-corridor.net/table-demo/demo.html) を用意したので UX をご確認ください。

元となるソースコードも [こちら](https://github.com/nakayama-kazuki/2020/blob/master/bookmarklets/copy-column-v2.txt) で公開してますので、必要に応じでカスタマイズしてご利用ください。なお Closure Compiler を利用する場合の留意点は以下の通りです。

- 2024/01/05 現在 Private class fields がサポートされていない
- 2024/01/05 現在 Public class fields は constructor 内で定義される必要がある
- @language_out ECMASCRIPT_2017 オプションを付けないとコンパイル後のコードの方が大きくなる場合がある

ちなみに「列のテキスト選択 + コピー」とはいいつつも、このような範囲の選択 + コピーも可能です。

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-6.png' />

先ほど挫折した writing-mode での「さしすせそ」選択はこうなります。

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-7.png' />

## 野良拡張機能

bookmarklet 実行のひと手間すら惜しい、そんな各位には野良拡張機能もおすすめです。まずは manifest.json を用意してください。

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

次いで、以下は Chrome における手順です（Firefox でも大筋の流れは一緒です）。

1. 適当なフォルダに manifest.json と [ソースコード](https://github.com/nakayama-kazuki/2020/blob/master/bookmarklets/copy-column-v2.txt)  を manifest.json 内に指定したファイル名（今回は copy-column.js）で保存
2. 設定画面（chrome://settings/）を開く
3. デベロッパーモードに変更する
4. パッケージ化されていない拡張機能を読み込む、から上のフォルダを指定する

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/ext.png' />

これで常に「列のテキスト選択 + コピー」を実行できるようになりました。

## ネイティブ実装提案

あらためて思うことですが 99% のユーザーは TABLE 要素でテキスト選択 + コピーをする時には

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-2.png' />

ではなく

<img src='https://raw.githubusercontent.com/nakayama-kazuki/2020/master/bookmarklets/column/img/50-5.png' />

の結果を期待しているはずです。

ならばこのニーズに対しては bookmarklet や拡張機能ではなく、ブラウザアプリケーションのネイティブ実装提案による解決を期待したいところです。さらに行選択 / 列選択 / 任意範囲選択のそれぞれに対応した UX は実装可能だと思います（実際にサンプルの bookmarklet は概ねそのような挙動になってます）。これができれば差別化要因となりブラウザ選択のモチベーションにもつながるはず … だと個人的には思っています。

そんなわけで、スルーされるとは思いつつベンダにネイティブ実装の提案をしてみます。続報（ないかもしれないけど）乞うご期待！

