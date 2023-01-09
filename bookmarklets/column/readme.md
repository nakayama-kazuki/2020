# 列のテキスト選択 + コピーしたくなったことはありませんか？

こんにちは、エンジニアの中山です。

ウェブベースのコラボレーションツールや SaaS で表（TABLE 要素）が使われることがよくありますが、列（縦方向）のテキスト選択 + コピーしたくなったことはありませんか？例えば Confluence に表を挿入して情報を整理したときなどで、そういったニーズが多いのではないかと思います。というわけで、今回は列方向のテキスト選択 + コピーにチャレンジしてみましょう。

## ひらがな五十音表

TABLE 要素で作ったひらがな五十音表から

<img src='https://github.com/nakayama-kazuki/2020/tree/master/bookmarklets/column/img/50-1.png?raw=true' />

味付けの「さしすせそ」（もしくは営業トークの「さしすせそ」）を選択 + コピーしてみます。

<img src='https://github.com/nakayama-kazuki/2020/tree/master/bookmarklets/column/img/50-2.png?raw=true' />

みなさまご想像通りの結果です www 選択範囲は DOM の走査順序となります。

## writing-mode

ろころで CSS の [writing-mode](https://www.w3.org/TR/css-writing-modes-3/) を適用すると

```
TABLE { writing-mode : vertical-lr; }
```

こんな感じに行と列を入れ替えることができます。

<img src='https://github.com/nakayama-kazuki/2020/tree/master/bookmarklets/column/img/50-3.png?raw=true' />

これで行（入れ替え前の列）のテキスト選択を …

<img src='https://github.com/nakayama-kazuki/2020/tree/master/bookmarklets/column/img/50-4.png?raw=true' />

できない www やはり選択範囲は DOM の走査順序となります。

## 世の皆さんはどうしている？

列のテキスト選択 + コピー … のニーズは確実にあるはずですが、ネットで検索 / 周囲の意見をたずねてみたところ、概ね

- ブラウザにアドオンを導入する
- エクセルにはりつける

といった対応のようです。あとは力業の bookmarklet で実現できないこともないですが、選択時に DOM を操作して見た目を変えるのは … 微妙な印象が拭えませんね。またブラックボックス化されたアドオンだと安全性の観点で不安を感じます ^^;

## ::selection 疑似要素

もう少々実現方法を探求してみましょう。疑似要素の ::selection を使って選択した列以外のセルのスタイルを非選択状態同様に変更してみるというのはどうでしょうか？

<img src='https://github.com/nakayama-kazuki/2020/tree/master/bookmarklets/column/img/50-5.png?raw=true' />

おおっ！いい感じで選択できました。この状態から copy イベントでスタイルに応じたデータを取得することで「列のテキスト選択 + コピー」は実現できそうですね。こちらがその bookmarklet になります。

```
javascript:!function(e,t){const a="selection",n="disabled";0===t.styleSheets.length&&document.getElementsByTagName("SCRIPT").item(0).parentNode.appendChild(document.createElement("STYLE"));let s=String.fromCharCode(32),o="[data-"+a+'="'+n+'"]',r="TH"+o+"::selection,TH"+o+s+"*::selection,TD"+o+"::selection,TD"+o+s+"*::selection{background-color: transparent !important;}";function i(...e){return function(t){if(t.nodeType===Node.ELEMENT_NODE)for(let a of e)if(t.nodeName.toUpperCase()===a)return!0;return!1}}t.styleSheets[0].insertRule(r),HTMLTableElement.prototype.startCustomSelect=function(){this.getElementsByTagName("TABLE").length>0||(this._exData||(this._exData={},this._exData.debug=function(e){console.log(e)},this._exData.handleMouseMove=function(e){1==e.buttons&&i("TH","TD")(e.target)&&e.target.dataset[a]===n&&(this._exData.debug("added target"),delete e.target.dataset[a])}.bind(this),this._exData.handleMouseLeave=function(t){1!=t.buttons&&e.getSelection().getRangeAt(0).collapse(),Array.prototype.slice.call(this.querySelectorAll("TH, TD")).forEach((e=>{delete e.dataset[a]})),this.removeEventListener("mousemove",this._exData.handleMouseMove),this.removeEventListener("mouseleave",this._exData.handleMouseLeave),this._exData.debug("stopped"),this._exData.started=!1}.bind(this),this._exData.started=!1),this._exData.started||(this.addEventListener("mousemove",this._exData.handleMouseMove),this.addEventListener("mouseleave",this._exData.handleMouseLeave)),Array.prototype.slice.call(this.querySelectorAll("TH, TD")).forEach((e=>{e.dataset[a]=n})),this._exData.debug("started"),this._exData.started=!0)},HTMLTableElement.prototype.getSelectedData=function(){let t=[];if(!this._exData||!this._exData.started)return t;let s=e.getSelection().getRangeAt(0),o=s.startContainer.parentElement.closest("TH, TD"),r=s.endContainer.parentElement.closest("TH, TD"),i=function(e){try{return[e.parentNode.rowIndex,e.cellIndex]}catch(e){return[-1,-1]}};this._exData.debug("range : ("+i(o).join(",")+") - ("+i(r).join(",")+")");let l=!1;return Array.prototype.slice.call(this.querySelectorAll("TH, TD")).forEach((e=>{l?e===r?(t.push(r.innerText.substring(0,s.endOffset)),l=!1):e.dataset[a]!==n&&t.push(e.innerText):e===o&&(t.push(o.innerText.substring(s.startOffset)),l=!0)})),t},t.addEventListener("selectstart",(e=>{let t=e.composedPath().find(i("TABLE"));t&&t.startCustomSelect()})),t.addEventListener("copy",(e=>{let a=[];Array.prototype.slice.call(t.getElementsByTagName("TABLE")).forEach((e=>{a=a.concat(e.getSelectedData())})),a.length>0&&async function(e){await navigator.clipboard.writeText(e)}(a.join("\n"))}))}(window,document);void(0);
```

ソースコードは [こちら](https://github.com/nakayama-kazuki/2020/blob/master/bookmarklets/copy-column-v2.txt) ですので、必要に応じでカスタマイズしてご利用ください。ちなにみ「列のテキスト選択 + コピー」とはいいつつ、実際にはこんなことも可能です。

<img src='https://github.com/nakayama-kazuki/2020/tree/master/bookmarklets/column/img/50-6.png?raw=true' />

もう必要ありませんが writing-mode で vertical-lr を指定した場合はこんな結果になります。

<img src='https://github.com/nakayama-kazuki/2020/tree/master/bookmarklets/column/img/50-7.png?raw=true' />

## ブラウザアプリケーションによるサポート

あらためて思うところですが 99% のユーザーは

<img src='https://github.com/nakayama-kazuki/2020/tree/master/bookmarklets/column/img/50-2.png?raw=true' />

ではなく

<img src='https://github.com/nakayama-kazuki/2020/tree/master/bookmarklets/column/img/50-5.png?raw=true' />

な結果を期待していると思います。
また、これは排他的ではなくブラウザアプリケーションの実装次第で行選択 / 列選択 / 任意範囲選択をそれぞれ実行することは可能だと思います（実際にサンプルの bookmarklet は概ねそのような挙動になっています）。また、これは差別化要因となりブラウザ選択のモチベーションにもつながるはずです。

そんなわけでスルーされるとは思いつつブラウザベンダに提案してみます。続報（ないかもしれないけど）乞うご期待！

