/*! Sortable 1.4.2 - MIT | git://github.com/rubaxa/Sortable.git */
!function(a){"use strict";"function"==typeof define&&define.amd?define(a):"undefined"!=typeof module&&"undefined"!=typeof module.exports?module.exports=a():"undefined"!=typeof Package?Sortable=a():window.Sortable=a()}(function(){"use strict";function a(a,b){if(!a||!a.nodeType||1!==a.nodeType)throw"Sortable: `el` must be HTMLElement, and not "+{}.toString.call(a);this.el=a,this.options=b=r({},b),a[L]=this;var c={group:Math.random(),sort:!0,disabled:!1,store:null,handle:null,scroll:!0,scrollSensitivity:30,scrollSpeed:10,draggable:/[uo]l/i.test(a.nodeName)?"li":">*",ghostClass:"sortable-ghost",chosenClass:"sortable-chosen",ignore:"a, img",filter:null,animation:0,setData:function(a,b){a.setData("Text",b.textContent)},dropBubble:!1,dragoverBubble:!1,dataIdAttr:"data-id",delay:0,forceFallback:!1,fallbackClass:"sortable-fallback",fallbackOnBody:!1};for(var d in c)!(d in b)&&(b[d]=c[d]);V(b);for(var f in this)"_"===f.charAt(0)&&(this[f]=this[f].bind(this));this.nativeDraggable=b.forceFallback?!1:P,e(a,"mousedown",this._onTapStart),e(a,"touchstart",this._onTapStart),this.nativeDraggable&&(e(a,"dragover",this),e(a,"dragenter",this)),T.push(this._onDragOver),b.store&&this.sort(b.store.get(this))}function b(a){v&&v.state!==a&&(h(v,"display",a?"none":""),!a&&v.state&&w.insertBefore(v,s),v.state=a)}function c(a,b,c){if(a){c=c||N,b=b.split(".");var d=b.shift().toUpperCase(),e=new RegExp("\\s("+b.join("|")+")(?=\\s)","g");do if(">*"===d&&a.parentNode===c||(""===d||a.nodeName.toUpperCase()==d)&&(!b.length||((" "+a.className+" ").match(e)||[]).length==b.length))return a;while(a!==c&&(a=a.parentNode))}return null}function d(a){a.dataTransfer&&(a.dataTransfer.dropEffect="move"),a.preventDefault()}function e(a,b,c){a.addEventListener(b,c,!1)}function f(a,b,c){a.removeEventListener(b,c,!1)}function g(a,b,c){if(a)if(a.classList)a.classList[c?"add":"remove"](b);else{var d=(" "+a.className+" ").replace(K," ").replace(" "+b+" "," ");a.className=(d+(c?" "+b:"")).replace(K," ")}}function h(a,b,c){var d=a&&a.style;if(d){if(void 0===c)return N.defaultView&&N.defaultView.getComputedStyle?c=N.defaultView.getComputedStyle(a,""):a.currentStyle&&(c=a.currentStyle),void 0===b?c:c[b];b in d||(b="-webkit-"+b),d[b]=c+("string"==typeof c?"":"px")}}function i(a,b,c){if(a){var d=a.getElementsByTagName(b),e=0,f=d.length;if(c)for(;f>e;e++)c(d[e],e);return d}return[]}function j(a,b,c,d,e,f,g){var h=N.createEvent("Event"),i=(a||b[L]).options,j="on"+c.charAt(0).toUpperCase()+c.substr(1);h.initEvent(c,!0,!0),h.to=b,h.from=e||b,h.item=d||b,h.clone=v,h.oldIndex=f,h.newIndex=g,b.dispatchEvent(h),i[j]&&i[j].call(a,h)}function k(a,b,c,d,e,f){var g,h,i=a[L],j=i.options.onMove;return g=N.createEvent("Event"),g.initEvent("move",!0,!0),g.to=b,g.from=a,g.dragged=c,g.draggedRect=d,g.related=e||b,g.relatedRect=f||b.getBoundingClientRect(),a.dispatchEvent(g),j&&(h=j.call(i,g)),h}function l(a){a.draggable=!1}function m(){R=!1}function n(a,b){var c=a.lastElementChild,d=c.getBoundingClientRect();return(b.clientY-(d.top+d.height)>5||b.clientX-(d.right+d.width)>5)&&c}function o(a){for(var b=a.tagName+a.className+a.src+a.href+a.textContent,c=b.length,d=0;c--;)d+=b.charCodeAt(c);return d.toString(36)}function p(a){var b=0;if(!a||!a.parentNode)return-1;for(;a&&(a=a.previousElementSibling);)"TEMPLATE"!==a.nodeName.toUpperCase()&&b++;return b}function q(a,b){var c,d;return function(){void 0===c&&(c=arguments,d=this,setTimeout(function(){1===c.length?a.call(d,c[0]):a.apply(d,c),c=void 0},b))}}function r(a,b){if(a&&b)for(var c in b)b.hasOwnProperty(c)&&(a[c]=b[c]);return a}var s,t,u,v,w,x,y,z,A,B,C,D,E,F,G,H,I,J={},K=/\s+/g,L="Sortable"+(new Date).getTime(),M=window,N=M.document,O=M.parseInt,P=!!("draggable"in N.createElement("div")),Q=function(a){return a=N.createElement("x"),a.style.cssText="pointer-events:auto","auto"===a.style.pointerEvents}(),R=!1,S=Math.abs,T=([].slice,[]),U=q(function(a,b,c){if(c&&b.scroll){var d,e,f,g,h=b.scrollSensitivity,i=b.scrollSpeed,j=a.clientX,k=a.clientY,l=window.innerWidth,m=window.innerHeight;if(z!==c&&(y=b.scroll,z=c,y===!0)){y=c;do if(y.offsetWidth<y.scrollWidth||y.offsetHeight<y.scrollHeight)break;while(y=y.parentNode)}y&&(d=y,e=y.getBoundingClientRect(),f=(S(e.right-j)<=h)-(S(e.left-j)<=h),g=(S(e.bottom-k)<=h)-(S(e.top-k)<=h)),f||g||(f=(h>=l-j)-(h>=j),g=(h>=m-k)-(h>=k),(f||g)&&(d=M)),(J.vx!==f||J.vy!==g||J.el!==d)&&(J.el=d,J.vx=f,J.vy=g,clearInterval(J.pid),d&&(J.pid=setInterval(function(){d===M?M.scrollTo(M.pageXOffset+f*i,M.pageYOffset+g*i):(g&&(d.scrollTop+=g*i),f&&(d.scrollLeft+=f*i))},24)))}},30),V=function(a){var b=a.group;b&&"object"==typeof b||(b=a.group={name:b}),["pull","put"].forEach(function(a){a in b||(b[a]=!0)}),a.groups=" "+b.name+(b.put.join?" "+b.put.join(" "):"")+" "};return a.prototype={constructor:a,_onTapStart:function(a){var b=this,d=this.el,e=this.options,f=a.type,g=a.touches&&a.touches[0],h=(g||a).target,i=h,k=e.filter;if(!("mousedown"===f&&0!==a.button||e.disabled)&&(h=c(h,e.draggable,d))){if(D=p(h),"function"==typeof k){if(k.call(this,a,h,this))return j(b,i,"filter",h,d,D)}else if(k&&(k=k.split(",").some(function(a){return a=c(i,a.trim(),d),a?(j(b,a,"filter",h,d,D),!0):void 0})))return void a.preventDefault();(!e.handle||c(i,e.handle,d))&&this._prepareDragStart(a,g,h)}},_prepareDragStart:function(a,b,c){var d,f=this,h=f.el,j=f.options,k=h.ownerDocument;c&&!s&&c.parentNode===h&&(G=a,w=h,s=c,t=s.parentNode,x=s.nextSibling,F=j.group,d=function(){f._disableDelayedDrag(),s.draggable=!0,g(s,f.options.chosenClass,!0),f._triggerDragStart(b)},j.ignore.split(",").forEach(function(a){i(s,a.trim(),l)}),e(k,"mouseup",f._onDrop),e(k,"touchend",f._onDrop),e(k,"touchcancel",f._onDrop),j.delay?(e(k,"mouseup",f._disableDelayedDrag),e(k,"touchend",f._disableDelayedDrag),e(k,"touchcancel",f._disableDelayedDrag),e(k,"mousemove",f._disableDelayedDrag),e(k,"touchmove",f._disableDelayedDrag),f._dragStartTimer=setTimeout(d,j.delay)):d())},_disableDelayedDrag:function(){var a=this.el.ownerDocument;clearTimeout(this._dragStartTimer),f(a,"mouseup",this._disableDelayedDrag),f(a,"touchend",this._disableDelayedDrag),f(a,"touchcancel",this._disableDelayedDrag),f(a,"mousemove",this._disableDelayedDrag),f(a,"touchmove",this._disableDelayedDrag)},_triggerDragStart:function(a){a?(G={target:s,clientX:a.clientX,clientY:a.clientY},this._onDragStart(G,"touch")):this.nativeDraggable?(e(s,"dragend",this),e(w,"dragstart",this._onDragStart)):this._onDragStart(G,!0);try{N.selection?N.selection.empty():window.getSelection().removeAllRanges()}catch(b){}},_dragStarted:function(){w&&s&&(g(s,this.options.ghostClass,!0),a.active=this,j(this,w,"start",s,w,D))},_emulateDragOver:function(){if(H){if(this._lastX===H.clientX&&this._lastY===H.clientY)return;this._lastX=H.clientX,this._lastY=H.clientY,Q||h(u,"display","none");var a=N.elementFromPoint(H.clientX,H.clientY),b=a,c=" "+this.options.group.name,d=T.length;if(b)do{if(b[L]&&b[L].options.groups.indexOf(c)>-1){for(;d--;)T[d]({clientX:H.clientX,clientY:H.clientY,target:a,rootEl:b});break}a=b}while(b=b.parentNode);Q||h(u,"display","")}},_onTouchMove:function(b){if(G){a.active||this._dragStarted(),this._appendGhost();var c=b.touches?b.touches[0]:b,d=c.clientX-G.clientX,e=c.clientY-G.clientY,f=b.touches?"translate3d("+d+"px,"+e+"px,0)":"translate("+d+"px,"+e+"px)";I=!0,H=c,h(u,"webkitTransform",f),h(u,"mozTransform",f),h(u,"msTransform",f),h(u,"transform",f),b.preventDefault()}},_appendGhost:function(){if(!u){var a,b=s.getBoundingClientRect(),c=h(s),d=this.options;u=s.cloneNode(!0),g(u,d.ghostClass,!1),g(u,d.fallbackClass,!0),h(u,"top",b.top-O(c.marginTop,10)),h(u,"left",b.left-O(c.marginLeft,10)),h(u,"width",b.width),h(u,"height",b.height),h(u,"opacity","0.8"),h(u,"position","fixed"),h(u,"zIndex","100000"),h(u,"pointerEvents","none"),d.fallbackOnBody&&N.body.appendChild(u)||w.appendChild(u),a=u.getBoundingClientRect(),h(u,"width",2*b.width-a.width),h(u,"height",2*b.height-a.height)}},_onDragStart:function(a,b){var c=a.dataTransfer,d=this.options;this._offUpEvents(),"clone"==F.pull&&(v=s.cloneNode(!0),h(v,"display","none"),w.insertBefore(v,s)),b?("touch"===b?(e(N,"touchmove",this._onTouchMove),e(N,"touchend",this._onDrop),e(N,"touchcancel",this._onDrop)):(e(N,"mousemove",this._onTouchMove),e(N,"mouseup",this._onDrop)),this._loopId=setInterval(this._emulateDragOver,50)):(c&&(c.effectAllowed="move",d.setData&&d.setData.call(this,c,s)),e(N,"drop",this),setTimeout(this._dragStarted,0))},_onDragOver:function(a){var d,e,f,g=this.el,i=this.options,j=i.group,l=j.put,o=F===j,p=i.sort;if(void 0!==a.preventDefault&&(a.preventDefault(),!i.dragoverBubble&&a.stopPropagation()),I=!0,F&&!i.disabled&&(o?p||(f=!w.contains(s)):F.pull&&l&&(F.name===j.name||l.indexOf&&~l.indexOf(F.name)))&&(void 0===a.rootEl||a.rootEl===this.el)){if(U(a,i,this.el),R)return;if(d=c(a.target,i.draggable,g),e=s.getBoundingClientRect(),f)return b(!0),void(v||x?w.insertBefore(s,v||x):p||w.appendChild(s));if(0===g.children.length||g.children[0]===u||g===a.target&&(d=n(g,a))){if(d){if(d.animated)return;r=d.getBoundingClientRect()}b(o),k(w,g,s,e,d,r)!==!1&&(s.contains(g)||(g.appendChild(s),t=g),this._animate(e,s),d&&this._animate(r,d))}else if(d&&!d.animated&&d!==s&&void 0!==d.parentNode[L]){A!==d&&(A=d,B=h(d),C=h(d.parentNode));var q,r=d.getBoundingClientRect(),y=r.right-r.left,z=r.bottom-r.top,D=/left|right|inline/.test(B.cssFloat+B.display)||"flex"==C.display&&0===C["flex-direction"].indexOf("row"),E=d.offsetWidth>s.offsetWidth,G=d.offsetHeight>s.offsetHeight,H=(D?(a.clientX-r.left)/y:(a.clientY-r.top)/z)>.5,J=d.nextElementSibling,K=k(w,g,s,e,d,r);if(K!==!1){if(R=!0,setTimeout(m,30),b(o),1===K||-1===K)q=1===K;else if(D){var M=s.offsetTop,N=d.offsetTop;q=M===N?d.previousElementSibling===s&&!E||H&&E:N>M}else q=J!==s&&!G||H&&G;s.contains(g)||(q&&!J?g.appendChild(s):d.parentNode.insertBefore(s,q?J:d)),t=s.parentNode,this._animate(e,s),this._animate(r,d)}}}},_animate:function(a,b){var c=this.options.animation;if(c){var d=b.getBoundingClientRect();h(b,"transition","none"),h(b,"transform","translate3d("+(a.left-d.left)+"px,"+(a.top-d.top)+"px,0)"),b.offsetWidth,h(b,"transition","all "+c+"ms"),h(b,"transform","translate3d(0,0,0)"),clearTimeout(b.animated),b.animated=setTimeout(function(){h(b,"transition",""),h(b,"transform",""),b.animated=!1},c)}},_offUpEvents:function(){var a=this.el.ownerDocument;f(N,"touchmove",this._onTouchMove),f(a,"mouseup",this._onDrop),f(a,"touchend",this._onDrop),f(a,"touchcancel",this._onDrop)},_onDrop:function(b){var c=this.el,d=this.options;clearInterval(this._loopId),clearInterval(J.pid),clearTimeout(this._dragStartTimer),f(N,"mousemove",this._onTouchMove),this.nativeDraggable&&(f(N,"drop",this),f(c,"dragstart",this._onDragStart)),this._offUpEvents(),b&&(I&&(b.preventDefault(),!d.dropBubble&&b.stopPropagation()),u&&u.parentNode.removeChild(u),s&&(this.nativeDraggable&&f(s,"dragend",this),l(s),g(s,this.options.ghostClass,!1),g(s,this.options.chosenClass,!1),w!==t?(E=p(s),E>=0&&(j(null,t,"sort",s,w,D,E),j(this,w,"sort",s,w,D,E),j(null,t,"add",s,w,D,E),j(this,w,"remove",s,w,D,E))):(v&&v.parentNode.removeChild(v),s.nextSibling!==x&&(E=p(s),E>=0&&(j(this,w,"update",s,w,D,E),j(this,w,"sort",s,w,D,E)))),a.active&&((null===E||-1===E)&&(E=D),j(this,w,"end",s,w,D,E),this.save())),w=s=t=u=x=v=y=z=G=H=I=E=A=B=F=a.active=null)},handleEvent:function(a){var b=a.type;"dragover"===b||"dragenter"===b?s&&(this._onDragOver(a),d(a)):("drop"===b||"dragend"===b)&&this._onDrop(a)},toArray:function(){for(var a,b=[],d=this.el.children,e=0,f=d.length,g=this.options;f>e;e++)a=d[e],c(a,g.draggable,this.el)&&b.push(a.getAttribute(g.dataIdAttr)||o(a));return b},sort:function(a){var b={},d=this.el;this.toArray().forEach(function(a,e){var f=d.children[e];c(f,this.options.draggable,d)&&(b[a]=f)},this),a.forEach(function(a){b[a]&&(d.removeChild(b[a]),d.appendChild(b[a]))})},save:function(){var a=this.options.store;a&&a.set(this)},closest:function(a,b){return c(a,b||this.options.draggable,this.el)},option:function(a,b){var c=this.options;return void 0===b?c[a]:(c[a]=b,void("group"===a&&V(c)))},destroy:function(){var a=this.el;a[L]=null,f(a,"mousedown",this._onTapStart),f(a,"touchstart",this._onTapStart),this.nativeDraggable&&(f(a,"dragover",this),f(a,"dragenter",this)),Array.prototype.forEach.call(a.querySelectorAll("[draggable]"),function(a){a.removeAttribute("draggable")}),T.splice(T.indexOf(this._onDragOver),1),this._onDrop(),this.el=a=null}},a.utils={on:e,off:f,css:h,find:i,is:function(a,b){return!!c(a,b,a)},extend:r,throttle:q,closest:c,toggleClass:g,index:p},a.create=function(b,c){return new a(b,c)},a.version="1.4.2",a});
Zepto.fn.flatpickr = function (config) {
	return _flatpickr(this, config);
};
Zepto.fn.swapWith = function(to) {
    return this.each(function() {
        var copy_to = $(to).clone(true);
        var copy_from = $(this).clone(true);
        $(to).replaceWith(copy_from);
        $(this).replaceWith(copy_to);
    });
};
$(document).ready(function(){Ui.init();});

/*! colorPicker - v1.0.0 2016-05-18 */
!function(a,b){"use strict";function c(a,c,d,f,g){if("string"==typeof c){var c=v.txt2color(c);d=c.type,n[d]=c[d],g=g!==b?g:c.alpha}else if(c)for(var h in c)a[d][h]="Lab"===d?k(c[h],l[d][h][0],l[d][h][1]):k(c[h]/l[d][h][1],0,1);return g!==b&&(a.alpha=k(+g,0,1)),e(d,f?a:b)}function d(a,b,c){var d=m.options.grey,e={};return e.RGB={r:a.r,g:a.g,b:a.b},e.rgb={r:b.r,g:b.g,b:b.b},e.alpha=c,e.equivalentGrey=r.round(d.r*a.r+d.g*a.g+d.b*a.b),e.rgbaMixBlack=i(b,{r:0,g:0,b:0},c,1),e.rgbaMixWhite=i(b,{r:1,g:1,b:1},c,1),e.rgbaMixBlack.luminance=h(e.rgbaMixBlack,!0),e.rgbaMixWhite.luminance=h(e.rgbaMixWhite,!0),m.options.customBG&&(e.rgbaMixCustom=i(b,m.options.customBG,c,1),e.rgbaMixCustom.luminance=h(e.rgbaMixCustom,!0),m.options.customBG.luminance=h(m.options.customBG,!0)),e}function e(a,b){var c,e,k,o=r,p=b||n,q=v,s=m.options,t=l,u=p.RND,w="",x="",y={hsl:"hsv",cmyk:"cmy",rgb:a},z=u.rgb;if("alpha"!==a){for(var A in t)if(!t[A][A]){a!==A&&"XYZ"!==A&&(x=y[A]||"rgb",p[A]=q[x+"2"+A](p[x])),u[A]||(u[A]={}),c=p[A];for(w in c)u[A][w]=o.round(c[w]*("Lab"===A?1:t[A][w][1]))}"Lab"!==a&&delete p._rgb,z=u.rgb,p.HEX=q.RGB2HEX(z),p.equivalentGrey=s.grey.r*p.rgb.r+s.grey.g*p.rgb.g+s.grey.b*p.rgb.b,p.webSave=e=f(z,51),p.webSmart=k=f(z,17),p.saveColor=z.r===e.r&&z.g===e.g&&z.b===e.b?"web save":z.r===k.r&&z.g===k.g&&z.b===k.b?"web smart":"",p.hueRGB=q.hue2RGB(p.hsv.h),b&&(p.background=d(z,p.rgb,p.alpha))}var B,C,D,E,F,G,H,I=p.rgb,J=p.alpha,K="luminance",L=p.background,M=i,N=h,O=j,P=g;return B=M(I,{r:0,g:0,b:0},J,1),B[K]=N(B,!0),p.rgbaMixBlack=B,C=M(I,{r:1,g:1,b:1},J,1),C[K]=N(C,!0),p.rgbaMixWhite=C,s.allMixDetails&&(B.WCAG2Ratio=O(B[K],0),C.WCAG2Ratio=O(C[K],1),s.customBG&&(D=M(I,s.customBG,J,1),D[K]=N(D,!0),D.WCAG2Ratio=O(D[K],s.customBG[K]),p.rgbaMixCustom=D),E=M(I,L.rgb,J,L.alpha),E[K]=N(E,!0),p.rgbaMixBG=E,F=M(I,L.rgbaMixBlack,J,1),F[K]=N(F,!0),F.WCAG2Ratio=O(F[K],L.rgbaMixBlack[K]),F.luminanceDelta=o.abs(F[K]-L.rgbaMixBlack[K]),F.hueDelta=P(L.rgbaMixBlack,F,!0),p.rgbaMixBGMixBlack=F,G=M(I,L.rgbaMixWhite,J,1),G[K]=N(G,!0),G.WCAG2Ratio=O(G[K],L.rgbaMixWhite[K]),G.luminanceDelta=o.abs(G[K]-L.rgbaMixWhite[K]),G.hueDelta=P(L.rgbaMixWhite,G,!0),p.rgbaMixBGMixWhite=G),s.customBG&&(H=M(I,L.rgbaMixCustom,J,1),H[K]=N(H,!0),H.WCAG2Ratio=O(H[K],L.rgbaMixCustom[K]),p.rgbaMixBGMixCustom=H,H.luminanceDelta=o.abs(H[K]-L.rgbaMixCustom[K]),H.hueDelta=P(L.rgbaMixCustom,H,!0)),p.RGBLuminance=N(z),p.HUELuminance=N(p.hueRGB),s.convertCallback&&s.convertCallback(p,a),p}function f(a,b){var c={},d=0,e=b/2;for(var f in a)d=a[f]%b,c[f]=a[f]+(d>e?b-d:-d);return c}function g(a,b,c){var d=r;return(d.max(a.r-b.r,b.r-a.r)+d.max(a.g-b.g,b.g-a.g)+d.max(a.b-b.b,b.b-a.b))*(c?255:1)/765}function h(a,b){for(var c=b?1:255,d=[a.r/c,a.g/c,a.b/c],e=m.options.luminance,f=d.length;f--;)d[f]=d[f]<=.03928?d[f]/12.92:r.pow((d[f]+.055)/1.055,2.4);return e.r*d[0]+e.g*d[1]+e.b*d[2]}function i(a,c,d,e){var f={},g=d!==b?d:1,h=e!==b?e:1,i=g+h*(1-g);for(var j in a)f[j]=(a[j]*g+c[j]*h*(1-g))/i;return f.a=i,f}function j(a,b){var c=1;return c=a>=b?(a+.05)/(b+.05):(b+.05)/(a+.05),r.round(100*c)/100}function k(a,b,c){return a>c?c:b>a?b:a}var l={rgb:{r:[0,255],g:[0,255],b:[0,255]},hsv:{h:[0,360],s:[0,100],v:[0,100]},hsl:{h:[0,360],s:[0,100],l:[0,100]},cmy:{c:[0,100],m:[0,100],y:[0,100]},cmyk:{c:[0,100],m:[0,100],y:[0,100],k:[0,100]},Lab:{L:[0,100],a:[-128,127],b:[-128,127]},XYZ:{X:[0,100],Y:[0,100],Z:[0,100]},alpha:{alpha:[0,1]},HEX:{HEX:[0,16777215]}},m={},n={},o={X:[.4124564,.3575761,.1804375],Y:[.2126729,.7151522,.072175],Z:[.0193339,.119192,.9503041],R:[3.2404542,-1.5371385,-.4985314],G:[-.969266,1.8760108,.041556],B:[.0556434,-.2040259,1.0572252]},p={r:.298954,g:.586434,b:.114612},q={r:.2126,g:.7152,b:.0722},r=a.Math,s=(a.parseInt,a.Colors=function(a){this.colors={RND:{}},this.options={color:"rgba(204, 82, 37, 0.8)",XYZMatrix:o,grey:p,luminance:q,valueRanges:l},t(this,a||{})}),t=function(a,d){var e,f,g=a.options;u(a);for(var h in d)d[h]!==b&&(g[h]=d[h]);e=g.XYZMatrix,d.XYZReference||(g.XYZReference={X:e.X[0]+e.X[1]+e.X[2],Y:e.Y[0]+e.Y[1]+e.Y[2],Z:e.Z[0]+e.Z[1]+e.Z[2]}),f=g.customBG,g.customBG="string"==typeof f?v.txt2color(f).rgb:f,n=c(a.colors,g.color,b,!0)},u=function(a){m!==a&&(m=a,n=a.colors)};s.prototype.setColor=function(a,d,f){return u(this),a?c(this.colors,a,d,b,f):(f!==b&&(this.colors.alpha=k(f,0,1)),e(d))},s.prototype.getColor=function(a){var c=this.colors,d=0;if(a){for(a=a.split(".");c[a[d]];)c=c[a[d++]];a.length!==d&&(c=b)}return c},s.prototype.setCustomBackground=function(a){return u(this),this.options.customBG="string"==typeof a?v.txt2color(a).rgb:a,c(this.colors,b,"rgb")},s.prototype.saveAsBackground=function(){return u(this),c(this.colors,b,"rgb",!0)},s.prototype.convertColor=function(a,b){var c=v,d=l,e=b.split("2"),f=e[0],g=e[1],h=/(?:RG|HS|CM|LA)/,i=h.test(f),j=h.test(g),k={LAB:"Lab"},m=function(a,b,c){var e={},f="Lab"===b?1:0;for(var g in a)e[g]=c?r.round(a[g]*(f||d[b][g][1])):a[g]/(f||d[b][g][1]);return e};return f=d[f]?f:k[f]||f.toLowerCase(),g=d[g]?g:k[g]||g.toLowerCase(),i&&"RGB2HEX"!==b&&(a=m(a,f)),a=f===g?a:c[f+"2"+g]?c[f+"2"+g](a,!0):"HEX"===g?c.RGB2HEX("RGB2HEX"===b?a:m("rgb"===f?a:c[f+"2rgb"](a,!0),"rgb",!0)):c["rgb2"+g](c[f+"2rgb"](a,!0),!0),j&&(a=m(a,g,!0)),a},s.prototype.toString=function(a,b){return v.color2text((a||"rgb").toLowerCase(),this.colors,b)};var v={txt2color:function(a){var b={},c=a.replace(/(?:#|\)|%)/g,"").split("("),d=(c[1]||"").split(/,\s*/),e=c[1]?c[0].substr(0,3):"rgb",f="";if(b.type=e,b[e]={},c[1])for(var g=3;g--;)f=e[g]||e.charAt(g),b[e][f]=+d[g]/l[e][f][1];else b.rgb=v.HEX2rgb(c[0]);return b.alpha=d[3]?+d[3]:1,b},color2text:function(a,b,c){var d=c!==!1&&r.round(100*b.alpha)/100,e="number"==typeof d&&c!==!1&&(c||1!==d),f=b.RND.rgb,g=b.RND.hsl,h="hex"===a&&e,i="hex"===a&&!h,j="rgb"===a||h,k=j?f.r+", "+f.g+", "+f.b:i?"#"+b.HEX:g.h+", "+g.s+"%, "+g.l+"%";return i?k:(h?"rgb":a)+(e?"a":"")+"("+k+(e?", "+d:"")+")"},RGB2HEX:function(a){return((a.r<16?"0":"")+a.r.toString(16)+(a.g<16?"0":"")+a.g.toString(16)+(a.b<16?"0":"")+a.b.toString(16)).toUpperCase()},HEX2rgb:function(a){return a=a.split(""),{r:+("0x"+a[0]+a[a[3]?1:0])/255,g:+("0x"+a[a[3]?2:1]+(a[3]||a[1]))/255,b:+("0x"+(a[4]||a[2])+(a[5]||a[2]))/255}},hue2RGB:function(a){var b=r,c=6*a,d=~~c%6,e=6===c?0:c-d;return{r:b.round(255*[1,1-e,0,0,e,1][d]),g:b.round(255*[e,1,1,1-e,0,0][d]),b:b.round(255*[0,0,e,1,1,1-e][d])}},rgb2hsv:function(a){var b,c,d,e=r,f=a.r,g=a.g,h=a.b,i=0;return h>g&&(g=h+(h=g,0),i=-1),c=h,g>f&&(f=g+(g=f,0),i=-2/6-i,c=e.min(g,h)),b=f-c,d=f?b/f:0,{h:1e-15>d?n&&n.hsl&&n.hsl.h||0:b?e.abs(i+(g-h)/(6*b)):0,s:f?b/f:n&&n.hsv&&n.hsv.s||0,v:f}},hsv2rgb:function(a){var b=6*a.h,c=a.s,d=a.v,e=~~b,f=b-e,g=d*(1-c),h=d*(1-f*c),i=d*(1-(1-f)*c),j=e%6;return{r:[d,h,g,g,i,d][j],g:[i,d,d,h,g,g][j],b:[g,g,i,d,d,h][j]}},hsv2hsl:function(a){var b=(2-a.s)*a.v,c=a.s*a.v;return c=a.s?1>b?b?c/b:0:c/(2-b):0,{h:a.h,s:a.v||c?c:n&&n.hsl&&n.hsl.s||0,l:b/2}},rgb2hsl:function(a,b){var c=v.rgb2hsv(a);return v.hsv2hsl(b?c:n.hsv=c)},hsl2rgb:function(a){var b=6*a.h,c=a.s,d=a.l,e=.5>d?d*(1+c):d+c-c*d,f=d+d-e,g=e?(e-f)/e:0,h=~~b,i=b-h,j=e*g*i,k=f+j,l=e-j,m=h%6;return{r:[e,l,f,f,k,e][m],g:[k,e,e,l,f,f][m],b:[f,f,k,e,e,l][m]}},rgb2cmy:function(a){return{c:1-a.r,m:1-a.g,y:1-a.b}},cmy2cmyk:function(a){var b=r,c=b.min(b.min(a.c,a.m),a.y),d=1-c||1e-20;return{c:(a.c-c)/d,m:(a.m-c)/d,y:(a.y-c)/d,k:c}},cmyk2cmy:function(a){var b=a.k;return{c:a.c*(1-b)+b,m:a.m*(1-b)+b,y:a.y*(1-b)+b}},cmy2rgb:function(a){return{r:1-a.c,g:1-a.m,b:1-a.y}},rgb2cmyk:function(a,b){var c=v.rgb2cmy(a);return v.cmy2cmyk(b?c:n.cmy=c)},cmyk2rgb:function(a,b){var c=v.cmyk2cmy(a);return v.cmy2rgb(b?c:n.cmy=c)},XYZ2rgb:function(a,b){var c=r,d=m.options.XYZMatrix,e=a.X,f=a.Y,g=a.Z,h=e*d.R[0]+f*d.R[1]+g*d.R[2],i=e*d.G[0]+f*d.G[1]+g*d.G[2],j=e*d.B[0]+f*d.B[1]+g*d.B[2],l=1/2.4;return d=.0031308,h=h>d?1.055*c.pow(h,l)-.055:12.92*h,i=i>d?1.055*c.pow(i,l)-.055:12.92*i,j=j>d?1.055*c.pow(j,l)-.055:12.92*j,b||(n._rgb={r:h,g:i,b:j}),{r:k(h,0,1),g:k(i,0,1),b:k(j,0,1)}},rgb2XYZ:function(a){var b=r,c=m.options.XYZMatrix,d=a.r,e=a.g,f=a.b,g=.04045;return d=d>g?b.pow((d+.055)/1.055,2.4):d/12.92,e=e>g?b.pow((e+.055)/1.055,2.4):e/12.92,f=f>g?b.pow((f+.055)/1.055,2.4):f/12.92,{X:d*c.X[0]+e*c.X[1]+f*c.X[2],Y:d*c.Y[0]+e*c.Y[1]+f*c.Y[2],Z:d*c.Z[0]+e*c.Z[1]+f*c.Z[2]}},XYZ2Lab:function(a){var b=r,c=m.options.XYZReference,d=a.X/c.X,e=a.Y/c.Y,f=a.Z/c.Z,g=16/116,h=1/3,i=.008856,j=7.787037;return d=d>i?b.pow(d,h):j*d+g,e=e>i?b.pow(e,h):j*e+g,f=f>i?b.pow(f,h):j*f+g,{L:116*e-16,a:500*(d-e),b:200*(e-f)}},Lab2XYZ:function(a){var b=r,c=m.options.XYZReference,d=(a.L+16)/116,e=a.a/500+d,f=d-a.b/200,g=b.pow(e,3),h=b.pow(d,3),i=b.pow(f,3),j=16/116,k=.008856,l=7.787037;return{X:(g>k?g:(e-j)/l)*c.X,Y:(h>k?h:(d-j)/l)*c.Y,Z:(i>k?i:(f-j)/l)*c.Z}},rgb2Lab:function(a,b){var c=v.rgb2XYZ(a);return v.XYZ2Lab(b?c:n.XYZ=c)},Lab2rgb:function(a,b){var c=v.Lab2XYZ(a);return v.XYZ2rgb(b?c:n.XYZ=c,b)}}}(window),function(a){"use strict";var b='^§app alpha-bg-w">^§slds">^§sldl-1">$^§sldl-2">$^§sldl-3">$^§curm">$^§sldr-1">$^§sldr-2">$^§sldr-4">$^§curl">$^§curr">$$^§opacity">|^§opacity-slider">$$$^§memo">^§raster">$^§raster-bg">$|$|$|$|$|$|$|$|$^§memo-store">$^§memo-cursor">$$^§panel">^§hsv">^hsl-mode §ß">$^hsv-h-ß §ß">H$^hsv-h-~ §~">-^§nsarrow">$$^hsl-h-@ §@">H$^hsv-s-ß §ß">S$^hsv-s-~ §~">-$^hsl-s-@ §@">S$^hsv-v-ß §ß">B$^hsv-v-~ §~">-$^hsl-l-@ §@">L$$^§hsl §hide">^hsv-mode §ß">$^hsl-h-ß §ß">H$^hsl-h-~ §~">-$^hsv-h-@ §@">H$^hsl-s-ß §ß">S$^hsl-s-~ §~">-$^hsv-s-@ §@">S$^hsl-l-ß §ß">L$^hsl-l-~ §~">-$^hsv-v-@ §@">B$$^§rgb">^rgb-r-ß §ß">R$^rgb-r-~ §~">-$^rgb-r-@ §ß">&nbsp;$^rgb-g-ß §ß">G$^rgb-g-~ §~">-$^rgb-g-@ §ß">&nbsp;$^rgb-b-ß §ß">B$^rgb-b-~ §~">-$^rgb-b-@ §ß">&nbsp;$$^§cmyk">^Lab-mode §ß">$^cmyk-c-ß §@">C$^cmyk-c-~ §~">-$^Lab-L-@ §@">L$^cmyk-m-ß §@">M$^cmyk-m-~ §~">-$^Lab-a-@ §@">a$^cmyk-y-ß §@">Y$^cmyk-y-~ §~">-$^Lab-b-@ §@">b$^cmyk-k-ß §@">K$^cmyk-k-~ §~">-$^Lab-x-@ §ß">&nbsp;$$^§Lab §hide">^cmyk-mode §ß">$^Lab-L-ß §@">L$^Lab-L-~ §~">-$^cmyk-c-@ §@">C$^Lab-a-ß §@">a$^Lab-a-~ §~">-$^cmyk-m-@ §@">M$^Lab-b-ß §@">b$^Lab-b-~ §~">-$^cmyk-y-@ §@">Y$^Lab-x-ß §@">&nbsp;$^Lab-x-~ §~">-$^cmyk-k-@ §@">K$$^§alpha">^alpha-ß §ß">A$^alpha-~ §~">-$^alpha-@ §ß">W$$^§HEX">^HEX-ß §ß">#$^HEX-~ §~">-$^HEX-@ §ß">M$$^§ctrl">^§raster">$^§cont">$^§cold">$^§col1">|&nbsp;$$^§col2">|&nbsp;$$^§bres">RESET$^§bsav">SAVE$$$^§exit">$^§resize">$^§resizer">|$$$'.replace(/\^/g,'<div class="').replace(/\$/g,"</div>").replace(/~/g,"disp").replace(/ß/g,"butt").replace(/@/g,"labl").replace(/\|/g,"<div>"),c="är^1,äg^1,äb^1,öh^1,öh?1,öh?2,ös?1,öv?1,üh^1,üh?1,üh?2,üs?1,ül?1,.no-rgb-r är?2,.no-rgb-r är?3,.no-rgb-r är?4,.no-rgb-g äg?2,.no-rgb-g äg?3,.no-rgb-g äg?4,.no-rgb-b äb?2,.no-rgb-b äb?3,.no-rgb-b äb?4{visibility:hidden}är^2,är^3,äg^2,äg^3,äb^2,äb^3{@-image:url(_patches.png)}.§slds div{@-image:url(_vertical.png)}öh^2,ös^1,öv^1,üh^2,üs^1,ül^1{@-image:url(_horizontal.png)}ös?4,öv^3,üs?4,ül^3{@:#000}üs?3,ül^4{@:#fff}är?1{@-color:#f00}äg?1{@-color:#0f0}äb?1{@-color:#00f}är^2{@|-1664px 0}är^3{@|-896px 0}är?1,äg?1,äb?1,öh^3,ös^2,öv?2Ü-2432Öär?2Ü-2944Öär?3Ü-4480Öär?4Ü-3202Öäg^2Äöh^2{@|-640px 0}äg^3{@|-384px 0}äg?2Ü-4736Öäg?3Ü-3968Öäg?4Ü-3712Öäb^2{@|-1152px 0}äb^3{@|-1408px 0}äb?2Ü-3456Öäb?3Ü-4224Öäb?4Ü-2688Ööh^2Äär^3Ääb?4Ü0}öh?4,üh?4Ü-1664Öös^1,öv^1,üs^1,ül^1Ääg^3{@|-256px 0}ös^3,öv?4,üs^3,ül?4Ü-2176Öös?2,öv^2Ü-1920Öüh^2{@|-768px 0}üh^3,üs^2,ül?2Ü-5184Öüs?2,ül^2Ü-5824Ö.S är^2{@|-128px -128Ö.S är?1Ääg?1Ääb?1Äöh^3Äös^2Äöv?2Ü-1408Ö.S är?2Ääb^3Ü-128Ö.S är?3Ü-896Ö.S är?4Ü-256Ö.S äg^2{@|-256px -128Ö.S äg?2Ü-1024Ö.S äg?3Ü-640Ö.S äg?4Ü-512Ö.S äb^2{@|-128px 0}.S äb?2Ü-384Ö.S äb?3Ü-768Ö.S öh?4Äüh?4Ü-1536Ö.S ös^1Äöv^1Äüs^1Äül^1{@|-512px 0}.S ös^3Äöv?4Äüs^3Äül?4Ü-1280Ö.S ös?2Äöv^2Ü-1152Ö.S üh^2{@|-1024px 0}.S üh^3Äüs^2Äül?2Ü-5440Ö.S üs?2Äül^2Ü-5696Ö.XXS ös^2,.XXS öv?2Ü-5120Ö.XXS ös^3,.XXS öv?4,.XXS üs^3,.XXS ül^3,.XXS ül?4Ü-5056Ö.XXS ös?2,.XXS öv^2Ü-4992Ö.XXS üs^2,.XXS ül?2Ü-5568Ö.XXS üs?2,.XXS ül^2Ü-5632Ö".replace(/Ü/g,"{@|0 ").replace(/Ö/g,"px}").replace(/Ä/g,",.S ").replace(/\|/g,"-position:").replace(/@/g,"background").replace(/ü/g,".hsl-").replace(/ö/g,".hsv-").replace(/ä/g,".rgb-").replace(/~/g," .no-rgb-}").replace(/\?/g," .§sldr-").replace(/\^/g," .§sldl-"),d='∑{@#bbb;font-family:monospace, "Courier New", Courier, mono;font-size:12¥line-ä15¥font-weight:bold;cursor:default;~412¥ä323¥?top-left-radius:7¥?top-Ü-radius:7¥?bottom-Ü-radius:7¥?bottom-left-radius:7¥ö@#444}.S{~266¥ä177px}.XS{~158¥ä173px}.XXS{ä105¥~154px}.no-alpha{ä308px}.no-alpha .§opacity,.no-alpha .§alpha{display:none}.S.no-alpha{ä162px}.XS.no-alpha{ä158px}.XXS.no-alpha{ä90px}∑,∑ div{border:none;padding:0¥float:none;margin:0¥outline:none;box-sizing:content-box}∑ div{|absolute}^s .§curm,«§disp,«§nsarrow,∑ .§exit,∑ ø-cursor,∑ .§resize{öimage:url(_icons.png)}∑ .do-drag div{cursor:none}∑ .§opacity,ø .§raster-bg,∑ .§raster{öimage:url(_bgs.png)}∑ ^s{~287¥ä256¥top:10¥left:10¥overflow:hidden;cursor:crosshair}.S ^s{~143¥ä128¥left:9¥top:9px}.XS ^s{left:7¥top:7px}.XXS ^s{left:5¥top:5px}^s div{~256¥ä256¥left:0px}.S ^l-1,.S ^l-2,.S ^l-3,.S ^l-4{~128¥ä128px}.XXS ^s,.XXS ^s ^l-1,.XXS ^s ^l-2,.XXS ^s ^l-3,.XXS ^s ^l-4{ä64px}^s ^r-1,^s ^r-2,^s ^r-3,^s ^r-4{~31¥left:256¥cursor:default}.S ^r-1,.S ^r-2,.S ^r-3,.S ^r-4{~15¥ä128¥left:128px}^s .§curm{margin:-5¥~11¥ä11¥ö|-36px -30px}.light .§curm{ö|-7px -30px}^s .§curl,^s .§curr{~0¥ä0¥margin:-3px -4¥border:4px solid;cursor:default;left:auto;öimage:none}^s .§curl,∑ ^s .§curl-dark,.hue-dark div.§curl{Ü:27¥?@† † † #fff}.light .§curl,∑ ^s .§curl-light,.hue-light .§curl{?@† † † #000}.S ^s .§curl,.S ^s .§curr{?~3px}.S ^s .§curl-light,.S ^s .§curl{Ü:13px}^s .§curr,∑ ^s .§curr-dark{Ü:4¥?@† #fff † †}.light .§curr,∑ ^s .§curr-light{?@† #000 † †}∑ .§opacity{bottom:44¥left:10¥ä10¥~287¥ö|0 -87px}.S .§opacity{bottom:27¥left:9¥~143¥ö|0 -100px}.XS .§opacity{left:7¥bottom:25px}.XXS .§opacity{left:5¥bottom:23px}.§opacity div{~100%;ä16¥margin-top:-3¥overflow:hidden}.§opacity .§opacity-slider{margin:0 -4¥~0¥ä8¥?~4¥?style:solid;?@#eee †}∑ ø{bottom:10¥left:10¥~288¥ä31¥ö@#fff}.S ø{ä15¥~144¥left:9¥bottom:9px}.XS ø{left:7¥bottom:7px}.XXS ø{left:5¥bottom:5px}ø div{|relative;float:left;~31¥ä31¥margin-Ü:1px}.S ø div{~15¥ä15px}∑ .§raster,ø .§raster-bg,.S ø .§raster,.S ø .§raster-bg{|absolute;top:0¥Ü:0¥bottom:0¥left:0¥~100%}.S ø .§raster-bg{ö|0 -31px}∑ .§raster{opacity:0.2;ö|0 -49px}.alpha-bg-b ø{ö@#333}.alpha-bg-b .§raster{opacity:1}ø ø-cursor{|absolute;Ü:0¥ö|-26px -87px}∑ .light ø-cursor{ö|3px -87px}.S ø-cursor{ö|-34px -95px}.S .light ø-cursor{ö|-5px -95px}∑ .§panel{|absolute;top:10¥Ü:10¥bottom:10¥~94¥?~1¥?style:solid;?@#222 #555 #555 #222;overflow:hidden;ö@#333}.S .§panel{top:9¥Ü:9¥bottom:9px}.XS .§panel{display:none}.§panel div{|relative}«§hsv,«§hsl,«§rgb,«§cmyk,«§Lab,«§alpha,.no-alpha «§HEX,«§HEX{~86¥margin:-1px 0px 1px 4¥padding:1px 0px 3¥?top-~1¥?top-style:solid;?top-@#444;?bottom-~1¥?bottom-style:solid;?bottom-@#222;float:Ö«§hsv,«§hsl{padding-top:2px}.S .§hsv,.S .§hsl{padding-top:1px}«§HEX{?bottom-style:none;?top-~0¥margin-top:-4¥padding-top:0px}.no-alpha «§HEX{?bottom-style:none}«§alpha{?bottom-style:none}.S .rgb-r .§hsv,.S .rgb-g .§hsv,.S .rgb-b .§hsv,.S .rgb-r .§hsl,.S .rgb-g .§hsl,.S .rgb-b .§hsl,.S .hsv-h .§rgb,.S .hsv-s .§rgb,.S .hsv-v .§rgb,.S .hsl-h .§rgb,.S .hsl-s .§rgb,.S .hsl-l .§rgb,.S .§cmyk,.S .§Lab{display:none}«§butt,«§labl{float:left;~14¥ä14¥margin-top:2¥text-align:center;border:1px solid}«§butt{?@#555 #222 #222 #555}«§butt:active{ö@#444}«§labl{?@†}«Lab-mode,«cmyk-mode,«hsv-mode,«hsl-mode{|absolute;Ü:0¥top:1¥ä50px}«hsv-mode,«hsl-mode{top:2px}«cmyk-mode{ä68px}.hsl-h .hsl-h-labl,.hsl-s .hsl-s-labl,.hsl-l .hsl-l-labl,.hsv-h .hsv-h-labl,.hsv-s .hsv-s-labl,.hsv-v .hsv-v-labl{@#f90}«cmyk-mode,«hsv-mode,.rgb-r .rgb-r-butt,.rgb-g .rgb-g-butt,.rgb-b .rgb-b-butt,.hsv-h .hsv-h-butt,.hsv-s .hsv-s-butt,.hsv-v .hsv-v-butt,.hsl-h .hsl-h-butt,.hsl-s .hsl-s-butt,.hsl-l .hsl-l-butt,«rgb-r-labl,«rgb-g-labl,«rgb-b-labl,«alpha-butt,«HEX-butt,«Lab-x-labl{?@#222 #555 #555 #222;ö@#444}.no-rgb-r .rgb-r-labl,.no-rgb-g .rgb-g-labl,.no-rgb-b .rgb-b-labl,.mute-alpha .alpha-butt,.no-HEX .HEX-butt,.cmy-only .Lab-x-labl{?@#555 #222 #222 #555;ö@#333}.Lab-x-disp,.cmy-only .cmyk-k-disp,.cmy-only .cmyk-k-butt{visibility:hidden}«HEX-disp{öimage:none}«§disp{float:left;~48¥ä14¥margin:2px 2px 0¥cursor:text;text-align:left;text-indent:3¥?~1¥?style:solid;?@#222 #555 #555 #222}∑ .§nsarrow{|absolute;top:0¥left:-13¥~8¥ä16¥display:none;ö|-87px -23px}∑ .start-change .§nsarrow{display:block}∑ .do-change .§nsarrow{display:block;ö|-87px -36px}.do-change .§disp{cursor:default}«§hide{display:none}«§cont,«§cold{|absolute;top:-5¥left:0¥ä3¥border:1px solid #333}«§cold{z-index:1;ö@#c00}«§cont{margin-Ü:-1¥z-index:2}«contrast .§cont{z-index:1;ö@#ccc}«orange .§cold{ö@#f90}«green .§cold{ö@#4d0}«§ctrl{|absolute;bottom:0¥left:0¥~100%;ö@#fff}.alpha-bg-b .§ctrl,«§bres,«§bsav{ö@#333}«§col1,«§col2,«§bres,«§bsav{?~1¥?style:solid;?@#555 #222 #222 #555;float:left;~45¥line-ä28¥text-align:center;top:0px}.§panel div div{ä100%}.S .§ctrl div{line-ä25px}.S «§bres,.S «§bsav{line-ä26px}∑ .§exit,∑ .§resize{Ü:3¥top:3¥~15¥ä15¥ö|0 -52px}∑ .§resize{top:auto;bottom:3¥cursor:nwse-resize;ö|-15px -52px}.S .§exit{ö|1px -52px}.XS .§resize,.XS .§exit{~10¥ä10¥Ü:0¥öimage:none}.XS .§exit{top:0px}.XS .§resize{bottom:0px}∑ .§resizer,∑ .§resizer div{|absolute;border:1px solid #888;top:-1¥Ü:-1¥bottom:-1¥left:-1¥z-index:2;display:none;cursor:nwse-resize}∑ .§resizer div{border:1px dashed #333;opacity:0.3;display:block;ö@#bbb}'.replace(/Ü/g,"right").replace(/Ö/g,"left}").replace(/∑/g,".§app").replace(/«/g,".§panel .").replace(/¥/g,"px;").replace(/\|/g,"position:").replace(/@/g,"color:").replace(/ö/g,"background-").replace(/ä/g,"height:").replace(/ø/g,".§memo").replace(/†/g,"transparent").replace(/\~/g,"width:").replace(/\?/g,"border-").replace(/\^/g,".§sld"),e="iVBORw0KGgoAAAANSUhEUgAABIAAAAABCAYAAACmC9U0AAABT0lEQVR4Xu2S3Y6CMBCFhyqIsjGBO1/B9/F5DC/pK3DHhVkUgc7Zqus2DVlGU/cnQZKTjznttNPJBABA149HyRf1iN//4mIBCg0jV4In+j9xJiuihly1V/Z9X88v//kNeDXVvyO/lK+IPR76B019+1Riab3H1zkmeqerKnL+Bzwxx6PAgZxaSQU8vB62T28pxcQeRQ2sHw6GxCOWHvP78zwHAARBABOfdYtd30rwxXOEPDF+dj2+91r6vV/id3k+/brrXmaGUkqKhX3i+ffSt16HQ/dorTGZTHrs7ev7Tl7XdZhOpzc651nfsm1bRFF0YRiGaJoGs9nsQuN/xafTCXEco65rzOdzHI9HJEmCqqqwXC6x3++RZRnKssRqtUJRFFiv19jtdthutyAi5Hl+Jo9VZg7+7f3yXuvZf5c3KaXYzByb+WIzO5ymKW82G/0BNcFhO/tOuuMAAAAASUVORK5CYII=",f="iVBORw0KGgoAAAANSUhEUgAAAAEAABfACAYAAABn2KvYAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAABHtJREFUeNrtnN9SqzAQxpOF1to6zuiVvoI+j6/gva/lA/kKeqUzjtX+QTi7SzSYBg49xdIzfL34+e1usoQQklCnmLwoCjImNwDQA2xRGMqNAYB+gPEH9IdCgIUA6Aem0P1fLoMQAPYNHYDoCKAv8OMHFgKgX2AjDPQDXn4t1l+gt/1fId//yWgE/hUJ+mAn8EyY5wCwXxhrbaHzn8E9iPlv79DdHxXTqciZ4KROnXRVZMF/6U2OPhcEavtAbZH1SM7wRDD7VoHZItCiyEQf4t6+MW9UOxaZybmdCGKqNrB9Eb5SfMg3wTyiagMtigTmWofiSDCOYNTSNz6sLDIoaCU9GWDd0tdhoMMsRm+r8U/EfB0GfjmLXiqzimDd0tdhoLMsI7la45+I+ToM/HIW0kfGVQTrlr7tA91kaUr//fxrKo8jUFB7VAn6AKpHJf+EKwAAAIYD/f7F7/8MVgMo7P+gBqDKr57Lf72V8x8AAMDgYIuvH4EAAAAMDQX6AACAQcI9GGMjDADA4MA/P2KlP8IEAAAYFCz6AACAgaLA8y8AAIN+CMYXoQAADA7u/UPYCAMAMDjI7z9S+SdwDFQX2C9Gh9GMEOWriz8/Pw1lWQZsi/L3R4czzP678Ve+P8f9nCv/C7hwLq99ah8NfKrU15zPB5pVcwtiJt9qGy0IfEE+jQa+Fn0VtI/fkxUPqBlEfRENeF+tqUpbGpi1iu8epwJzvV5XA4GpWC6XGz7F+/u766EgwJ+ckiTJKU3TnI6OjnI6OzvLZf6zMggt3dzckPhIoiTlSGpQ+eEsVegdz0fbCCi4fRs+Po+4yWdeDXiT+6pBSTeHple1pkz3FZ+avpyavoiPxgLN0B7yprY08PlyQTTm0+PWmkH7ynedNKraar4F/lRj1WpTtYh+ozL/cY2sAvZl0gcbZm0gSLBLvkxGoaogiy/HDXemQk2t5pUm8OAhH8/HH6e0mkJ9q9XKKQXfb07xfZnJbZrRxcVFVt6/t7e3Kc1ms5RGo1Eq5VIZuyl9fHw4k/M5xYeoKj64A7eqCt1ZeqWFVSl8NV9OTV3fmvP5qE9VmzSoEcsXpArK1UHen/hZbgL53BZSdyEXalGau/hU8TEW0u3VcoFPy3EDFrTgT+njydeZ0+l0UV7fu7u7iVzziQQmUm4iqRw4n/NxMxw4s/Mp1NSALxf4NEtQ10cjMDwSl+b+/j6hp6enVGb+jUvrn05iKobm6PboOt8vPISY5Pr6OqGXlxe3fOokoGtAbMUJZmqvYmaLQDP+sdrecOjtO/SXeH69P8Imutm5urqy9PDwYOny8tLS4+OjpfPzc0vPz8+WTk9PLb2+vlpZbCzN53NLx8fHVtYZS5PJxMoEZWWqsjKULY3HYytTi1Pex5OMldXKRVXxuLcy/20onmms3BBOxcr5qCrZtsrd45SPel8sGlOxGoGy0neynQ6VL9fsa1YtWlCrtj9G83G7PjdVush5n5q1iJWLZW6u21a1bUvbVnVzlru0pe3RdmlV1/23fZtbZv4Dx+7FBypx77kAAAAASUVORK5CYII=",g="iVBORw0KGgo^NSUhEUgAAB4^EACAI#DdoPxz#L0UlEQVR4Xu3cQWrDQBREwR7FF8/BPR3wXktnQL+KvxfypuEhvLJXcp06d/bXd71OPt+trIw95zr33Z1bk1/fudEv79wa++7OfayZ59wrO2PBzklcGQmAZggAAOBYgAYBmpWRAGg^BGgRofAENgAAN#I0CBA6w8AG^ECABgEa/QH§AI0CNDoDwAY^QIAGAVp/AM§AjQI0OgPAAY^QoEGARn8Aw§CNAjQ+gMABg#BCgQYCmGQmABgAAEKBBgEZ/AM§AjQI0PoDAAY^QoEGARn8AM^IAADQI0+gMABg#BCgQYDWHwAw^gAANAjT6A4AB^BGgQoNEfAD^C#0CtP4AgAE^EaBCgaUYCoAE#RoEKDRHwAw^gAANArT+AIAB^BGgQoNEfAAw^gQIMAjf4AgAE^EaBCg9QcAD^CBAgwCN/gBg§EaBGj0BwAM^IECDAK0/AG§ARoEaJqRAGg^BGgRo9AcAD^CBAgwCtPwBg§EaBGj0BwAD^CNAgQKM/AG§ARoEaP0BAAM^I0CBAoz8AG^ECABgEa/QEAAw^jQIEDrDwAY^QIAGAZpmJACaBw^RoEKD1BwAM^IECDAK0/AG§ARoEaPQHAAw^gQIMArT8AY§BGgRo/QEAAw^jQIECjPwBg§EaBGj9AQAD^CNAgQOsPABg#BAgAYBGv0BAANwCwAAGB6gYeckmpEAa^AEaBGj0BwAM^IECDAK0/AG§ARoEaPQHAAM^I0CBAoz8AY§BGgRo/QEAAw^jQIECjPwAY^QIAGARr9AQAD^CNAgQOsPABg#BAgAYBmmYkABoAAECABgEa/QEAAw^jQIEDrDwAY^QIAGARr9Ac§AjQI0OgPABg#BAgAYBWn8Aw§CNAjQ6A8ABg#BCgQYBGfwD§AI0CND6AwAG^EKBBgKYZCYAG#QoEGARn8Aw§CNAjQ+gMABg#BCgQYBGfwAw^gAANAjT6AwAG^EKBBgNYfAD^C#0CNPoDgAE^EaBCg0R8AM^IAADQK0/gCAAQ^RoEKBpRgKgAQAABGgQoNEfAD^C#0CtP4AgAE^EaBCg0R8AD^CBAgwCN/gCAAQ^RoEKD1BwAM^IECDAI3+AG§ARoEaPQHAAw^gQIMArT8AY§BGgRomsMAM^IAADQK0/gCAAQ^RoEKDRHwAw^gAANO7fQHwAw^gAANArT+AIAB^BGgQoNEfAGg^BGgRo9AcAD^CBAgwCtPwBg§EaBGj0BwAD^RIB+Ntg5iea5AD^DAIwI0CND6AwAG^EKBBgEZ/AKAB#EaBCg0R8AM^IAADQK0/gCAAQ^RoEKDRHwAM^IECDAI3+AIAB^BGgQoPUHAAw^gQIMAjf4AY§BGgRo9AcAD^CBAgwCtPwBg§EaBGiakQBo^ARoEaPQHAAw^gQIMArT8AY§BGgRo9AcAAw^jQIECjPwBg§EaBGj9AQAD^CNAgQKM/ABg#BAgAYBGv0BAAM^I0CBA6w8AG^ECABgGaZiQAGgAAQIAGARr9AQAD^CNAgQOsPABg#BAgAYBGv0Bw§CNAjQ6A8AG^ECABgFafwD§AI0CNDoDwAG^EKBBgEZ/AM§AjQI0PoDAAY^QoEGApjkMAAM^I0CBA6w8AG^ECABgEa/QEAAw^jQsIP+AIAB^BGgQoPUHAAw^gQIMAjf4AgAE#Bea/fK+3P5/3PJOvh8t1cO4nflmQAQoAEAAF9Aw/7JHfQHAAw^gQIMArT8AY§BGvwHNPoDAA0AACBAgwCN/gCAAQ^RoEKD1BwAM^IECDAI3+AG§ARoEaPQHAAw^gQIMArT8AY§BGgRo9AcAAw^jQIECjPwBg§EaBGj9AQAD^CNAgQNOMBEAD#I0CBAoz8AY§BGgRo/QEAAw^jQIECjPwAY^QIAGARr9AQAD^CNAgQOsPABg#BAgAYBGv0Bw§CNAjQ6A8AG^ECABgFafwD§AI0CNA0IwHQ^AjQI0OgPABg#BAgAYBWn8Aw§CNAjQ6A8ABg#BCgQYBGfwD§AI0CND6AwAG^EKBBgEZ/AD^C#0CNPoDAAY^QoEGA1h8AM^IAADQI0DQAG^EKBBgEZ/AM§AjQI0PoDAAY^QoEGA1h8AM^IAADQI0+gMABg#BCgQYDWHwAw^gAANArT+AIAB^BGgQoNEfAD^C#0CtP4AgAE^EaBCg9QcAD^CBAgwCN/gCAAQ^RoEKD1BwAM^IECDAK0/AG§ARoEaPQHAAw^gQIMArT8AY§BGgRo/QEAAw^jQIECjPwBgACDhFgC#07t9AfAD^C#0CtP4AgAE^EaBCg0R8Aa^AEaBGj0BwAM^IECDAK0/AG§ARoEaPQHAAM^I0CBAoz8AY§BGgRo/QEAAw^jQIECjPwAY^QIAGARr9AQAD^CNAgQOsPABg#BAgAYBmmYkABoAAECABgEa/QEAAw^jQIEDrDwAY^QIAGARr9Ac§AjQI0OgPABg#BAgAYBWn8Aw§CNAjQ6A8ABg#BCgQYBGfwD§AI0CND6AwAG^EKBBgKYZCYAG#QoEGARn8Aw§CNAjQ+gMABg#BCgQYBGfwAw^gAANAjT6AwAG^EKBBgNYfAD^C#0CNPoDgAE^EaBCg0R8AM^IAADQK0/gCAAQ^RoEKBpRgKgAQAABGgQoNEfAD^C#0CtP4AgAE^EaBCg0R8AD^CBAgwCN/gCAAQ^RoEKD1BwAM^IECDAI3+AG§ARoEaPQHAAw^gQIMArT8AY§BGgRommEAM^CBAgwCN/gCAAQ^RoEKD1BwAM^IECDAI3+AIAB^ARoEaPQHAAw^gQIMArT8AY§BGgRo9AcAGgAAQICGCNBfRfNcABg#BgeICGnVvoDwAY^QIAGAVp/AM§AjQI0OgPADQAAIAADQI0+gMABg#BCgQYDWHwAw^gAANAjT6A4AB^BGgQoNEfAD^C#0CtP4AgAE^EaBCg0R8AD^CBAgwCN/gCAAQ^RoEKD1BwAM^IECDAE0zEgAN#gQIMAjf4AgAE^EaBCg9QcAD^CBAgwCN/gBg§EaBGj0BwAM^IECDAK0/AG§ARoEaPQHAAM^I0CBAoz8AY§BGgRo/QEAAw^jQIEDTjARAAwAACNAgQKM/AG§ARoEaP0BAAM^I0CBAoz8AG^ECABgEa/QEAAw^jQIEDrDwAY^QIAGARr9Ac§AjQI0OgPABg#BAgAYBWn8Aw§CNAjQNIcBY§BGgRo/QEAAw^jQIECjPwBg§EadtAfAD^C#0CtP4AgAE^EaBCgAQABGgAA+AO2TAbHupOgH^ABJRU5ErkJggg==".replace(/§/g,"AAAAAA").replace(/\^/g,"AAAA").replace(/#/g,"AAA"),h="iVBORw0KGgoAAAANSUhEUgAAAGEAAABDCAMAAAC7vJusAAAAkFBMVEUAAAAvLy9ERERubm7///8AAAD///9EREREREREREREREQAAAD///8AAAD///8AAAD///8AAAD///8AAAD///8AAAD///8AAAD///8AAAD///8AAAD///8cHBwkJCQnJycoKCgpKSkqKiouLi4vLy8/Pz9AQEBCQkJDQ0NdXV1ubm58fHykpKRERERVVVUzMzPx7Ab+AAAAHXRSTlMAAAAAAAQEBQ4QGR4eIyMtLUVFVVVqapKSnJy7u9JKTggAAAFUSURBVHja7dXbUoMwEAbgSICqLYeW88F6KIogqe//dpoYZ0W4AXbv8g9TwkxmvtndZMrEwlw/F8YIRjCCEYxgBCOsFmzqGMEI28J5zzmt0Pc9rdDL0NYgMxIYC5KiKpKAzZphWtZlGm4SjlnkOV6UHeeEUx77rh/npw1dCrI9k9lnwUwF+UG9D3m4ftJJxH4SJdPtaawXcbr+tBaeFrxiur309cIv19+4ytGCU0031a5euPVigLYGqjlAqM4ShOQ+QAYQUO80AMMAAkUGGfMfR9Ul+kmvPq2QGxXKOQBAKdjUgk0t2NiCGEVP+rHT3/iCUMBT90YrPMsKsIWP3x/VolaonJEETchHCS8AYAmaUICQQwaAQnjoXgHAES7jLkEFaHO4bdq/k25HAIpgWY34FwAE5xjCffM+D2DV8B0gRsAZT7hr5gE8wdrJcU+CJqhcqQD7Cx5L7Ph4WnrKAAAAAElFTkSuQmCC",i="iVBORw0KGgoAAAANSUhEUgAAASAAAABvCAYAAABM+h2NAAAAGXRFWHRTb2Z0d2FyZQBBZG9iZSBJbWFnZVJlYWR5ccllPAAABORJREFUeNrs3VtTW1UYBuCEcxAI4YydWqTWdqr1V7T/2QsvvPDCCy9qjxZbamsrhZIQUHsCEtfafpmJe8qFjpUxfZ4Zuvt2feydJvAOARZUut1u5bRerl692nV913f99/f6QxWAU6KAAAUEKCAABQQoIAAFBCggAAUEKCAABQQoIAAFBCggAAUEKCAABQQoIEABASggQAEBKCBAAQEoIEABASggQAEBKCBAAQEoIGBQC+jatWvd07zxrv9+Xx8fAQEoIEABASggQAEBKCBAAQEoIEABAQoIQAEBCghAAQEKCEABAQOk2u36kS6AAgLetwJKL29toFRM1be+QrVq3rx58//KvM8BAadGAQEKCFBAAAoIGHwnfhneZ+/Nmzf/LufzrI+AAE/BAAUEoIAABQTwztgLZt68eXvBAE/BABQQoIAAFBAweOwFM2/evL1ggKdgAAoIUEAACggYPPaCmTdv3l4wwFMwAAUEKCAABQQMHnvBzJs3by8Y4CkYgAICFBCAAgIGz4lfBQNQQMDgFlCtVisaaHV1tThubW1VInciD0U+ysdnz54N5+PKysphOnRTHsvHlN9EHo/1l5FrkV9Enoz8W87b29tTOS8vLx9EnoncjlyPvBe5EbkZeT4fU96NvBDr2znv7Ows57y0tLQVeSXy08gf5mNfPhPrjyOfrVarlcXFxZ9yfv78+bl8TPlh5LU8n/KDyOuxfj/y+VjfyHl3d/dCKv28fi/yp/m4sLDwQ+SLke9GvhT5Tinfjnw5f4/F/Pz8rZybzeZn+ZjyzVK+EfnzUr4S+Xopf9/L+fxzc3M5d1qt1hf531Mu5k/IxzGf85VYL+fefHH+RqNRrO/t7RW3L+UbkS9Hvhk5/386Kd/qW8/5duRLMV/OdyJfzNebnZ0t7t92u53v/07K9yJfiLwROT9+ef7HyOux/iDyWuSHkT+K+eLtZX9//2xer9frjyOfyY9/Wn8S86v59qT1p7Ge315zLt4RU16K19+O9YXIu5HnYn435hux3opcj9yOPB3z+5E/iPXf43y1yMX778HBQS3f3pTz+28l5bHIr2N+LN3+zszMzGHkoh/S+mHMF98XlNaP8zHd/0W/pMe943NAwKlSQIACAhQQgAICFBCAAgIUEIACAhQQgAIC/n9GqtXqYbfbHa38+RtSu32llPdqdNL6aOSj+LfxyMVekLTem39Ryr/mPDQ0NBznzXtROikPRW6W8k7k3m9rzXthOsPDw73bUuylGRkZ6cR63nvTSfko8oPIr+Pnz96P/DLW816ezujoaN6DdtyX9+P8eS9QZ2xs7Hxf7qa8Xlr/JO6Ljcjrcf6cj1P+OO+N6V1/fHz8XLz+/Tjfubh+sZcorZ+N9Ycxfybyo8ircf6fc56YmFiJ1/8l8mLk7cjzkfP92U15Ns63G+u9nPcKdWq12lQ8Xu3Ixd6f9Pd8P3UmJycnUszzL2N9LM7/anNzs9V7Q2q32395w/q7ubdH6L/KrVbrpPxlKX9Vyl+X8jel/G0pf5f/aDabvXy9tH6ztH63lDdKebOUH5Xyk1LeKuWd/ry2tlap9P125Onp6Zf9eWpq6lW3b8f6zMzM6/71er3+ppSP+u/XNN/pz41Go+sjIMBTMEABASggQAEBKCBAAQEoIEABASggQAEB/CN/CDAAw78uW9AVDw4AAAAASUVORK5CYII=";a.ColorPicker={_html:b,_cssFunc:c,_cssMain:d,_horizontalPng:e,_verticalPng:f,_patchesPng:g,_iconsPng:h,_bgsPng:i}}(window),function(a,b){"use strict";function c(c,e){var j,k="",l="";for(var m in e)c.options[m]=e[m];Q=document.createStyleSheet!==b&&document.getElementById||!!a.MSInputMethodContext,R="undefined"!=typeof document.body.style.opacity,_=new Colors(c.options),delete c.options,bb=_.options,bb.scale=1,l=bb.CSSPrefix,c.color=_,S=bb.valueRanges,c.nodes=cb=g(f(c),c),q(bb.mode),d(c),u(),k=" "+bb.mode.type+"-"+bb.mode.z,cb.slds.className+=k,cb.panel.className+=k,bb.noHexButton&&C(cb.HEX_butt,l+"butt",l+"labl"),bb.size!==b&&p(b,bb.size),j={alphaBG:cb.alpha_labl,cmyOnly:cb.HEX_labl};for(var n in j)bb[n]!==b&&o({target:j[n],data:bb[n]});bb.noAlpha&&(cb.colorPicker.className+=" no-alpha"),c.renderMemory(bb.memoryColors),h(c),I=!0,i(b,"init"),N&&(d(N),w())}function d(a){Y=!0,M!==a&&(M=a,ab=a.color.colors,bb=a.color.options,cb=a.nodes,_=a.color,$={},v(ab))}function e(){var a=["L","S","XS","XXS"];bb.sizes={},cb.testNode.style.cssText="position:absolute;left:-1000px;top:-1000px;",document.body.appendChild(cb.testNode);for(var b=a.length;b--;)cb.testNode.className=bb.CSSPrefix+"app "+a[b],bb.sizes[a[b]]=[cb.testNode.offsetWidth,cb.testNode.offsetHeight];cb.testNode.removeNode?cb.testNode.removeNode(!0):document.body.removeChild(cb.testNode)}function f(a){var b=document.createElement("div"),c=bb.CSSPrefix,d="data:image/png;base64,",e=function(a,b){var c=document.createElement("style");c.setAttribute("type","text/css"),b&&c.setAttribute("id",b),c.styleSheet||c.appendChild(document.createTextNode(a)),document.getElementsByTagName("head")[0].appendChild(c),c.styleSheet&&(document.styleSheets[document.styleSheets.length-1].cssText=a)},f=function(a){O._cssFunc=O._cssFunc.replace(/§/g,c).replace("_patches.png",a?d+O._patchesPng:bb.imagePath+"_patches.png").replace("_vertical.png",a?d+O._verticalPng:bb.imagePath+"_vertical.png").replace("_horizontal.png",a?d+O._horizontalPng:bb.imagePath+"_horizontal.png"),e(O._cssFunc,"colorPickerCSS"),bb.customCSS||(O._cssMain=O._cssMain.replace(/§/g,c).replace("_bgs.png",a?d+O._bgsPng:bb.imagePath+"_bgs.png").replace("_icons.png",a?d+O._iconsPng:bb.imagePath+"_icons.png").replace(/opacity:(\d*\.*(\d+))/g,function(a,b){return R?"-moz-opacity: "+b+"; -khtml-opacity: "+b+"; opacity: "+b:'-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity='+db.round(100*+b)+')";filter: alpha(opacity='+db.round(100*+b)+")"}),e(O._cssMain))},g=document.createElement("img");return P?a.color.options.devPicker:(document.getElementById("colorPickerCSS")?a.cssIsReady=!0:(g.onload=g.onerror=function(){O._cssFunc&&f(1===this.width&&1===this.height),a.cssIsReady=!0},g.src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///ywAAAAAAQABAAACAUwAOw=="),(N=M)&&r(),b.insertAdjacentHTML("afterbegin",M?M.nodes.colorPicker.outerHTML||(new XMLSerializer).serializeToString(M.nodes.colorPicker):O._html.replace(/§/g,c)),b=b.children[0],b.style.cssText=bb.initStyle||"",(bb.appendTo||document.body).appendChild(b))}function g(a){var b,c,d=a.getElementsByTagName("*"),e={colorPicker:a},f=new RegExp(bb.CSSPrefix);e.styles={},e.textNodes={},e.memos=[],e.testNode=document.createElement("div");for(var g=0,h=d.length;h>g;g++)b=d[g],(c=b.className)&&f.test(c)?(c=c.split(" ")[0].replace(bb.CSSPrefix,"").replace(/-/g,"_"),/_disp/.test(c)?(c=c.replace("_disp",""),e.styles[c]=b.style,e.textNodes[c]=b.firstChild,b.contentEditable=!0):(/(?:hs|cmyk|Lab).*?(?:butt|labl)/.test(c)||(e[c]=b),/(?:cur|sld[^s]|opacity|cont|col)/.test(c)&&(e.styles[c]=/(?:col\d)/.test(c)?b.children[0].style:b.style))):/memo/.test(b.parentNode.className)&&e.memos.push(b);return e.panelCover=e.panel.appendChild(document.createElement("div")),e}function h(c,f){var g=f?G:F;g(cb.colorPicker,"mousedown",function(f){var g=f||a.event,h=E(g),n=(g.button||g.which)<2?g.target||g.srcElement:{},o=n.className;return d(c),J=n,i(b,"resetEventListener"),U="",n===cb.sldl_3||n===cb.curm?(J=cb.sldl_3,I=j,U="changeXYValue",C(cb.slds,"do-drag")):/sldr/.test(o)||n===cb.curl||n===cb.curr?(J=cb.sldr_4,I=k,U="changeZValue"):n===cb.opacity.children[0]||n===cb.opacity_slider?(J=cb.opacity,I=l,U="changeOpacityValue"):/-disp/.test(o)&&!/HEX-/.test(o)?(I=m,U="changeInputValue",(3===n.nextSibling.nodeType?n.nextSibling.nextSibling:n.nextSibling).appendChild(cb.nsarrow),K=o.split("-disp")[0].split("-"),K={type:K[0],z:K[1]||""},C(cb.panel,"start-change"),V=0):n!==cb.resize||bb.noResize?I=b:(bb.sizes||e(),J=cb.resizer,I=p,U="resizeApp"),I&&(W={pageX:h.X,pageY:h.Y},J.style.display="block",X=D(J),X.width=cb.opacity.offsetWidth,X.childWidth=cb.opacity_slider.offsetWidth,J.style.display="",I(g),F(Q?document.body:a,"mousemove",I),L=a[fb](w)),/-disp/.test(o)?void 0:B(g)}),g(cb.colorPicker,"click",function(a){d(c),o(a)}),g(cb.colorPicker,"dblclick",o),g(cb.colorPicker,"keydown",function(a){d(c),n(a)}),g(cb.colorPicker,"keypress",n),g(cb.colorPicker,"paste",function(a){return a.target.firstChild.data=a.clipboardData.getData("Text"),B(a)})}function i(c,d){var e=I;I&&(a[gb](L),G(Q?document.body:a,"mousemove",I),V&&(K={type:"alpha"},w()),("function"==typeof I||"number"==typeof I)&&delete bb.webUnsave,V=1,I=b,C(cb.slds,"do-drag",""),C(cb.panel,"(?:start-change|do-change)",""),cb.resizer.style.cssText="",cb.panelCover.style.cssText="",cb.memo_store.style.cssText="background-color: "+y(ab.RND.rgb)+"; "+A(ab.alpha),cb.memo.className=cb.memo.className.replace(/\s+(?:dark|light)/,"")+(ab["rgbaMix"+T[bb.alphaBG]].luminance<.22?" dark":" light"),K=b,s(),bb.actionCallback&&bb.actionCallback(c,U||e.name||d||"external"))}function j(b){var c=b||a.event,d=bb.scale,e=E(c),f=(e.X-X.left)*(4===d?2:d),g=(e.Y-X.top)*d,h=bb.mode;return ab[h.type][h.x]=z(f/255,0,1),ab[h.type][h.y]=1-z(g/255,0,1),t(),B(c)}function k(b){var c=b||a.event,d=E(c),e=(d.Y-X.top)*bb.scale,f=bb.mode;return ab[f.type][f.z]=1-z(e/255,0,1),t(),B(c)}function l(b){var c=b||a.event,d=E(c);return Y=!0,ab.alpha=z(db.round((d.X-X.left)/X.width*100),0,100)/100,t("alpha"),B(c)}function m(b){var c,d=b||a.event,e=E(d),f=W.pageY-e.Y,g=bb.delayOffset,h=K.type,i="alpha"===h;return V||db.abs(f)>=g?(V||(V=(f>0?-g:g)+ +J.firstChild.data*(i?100:1),W.pageY+=V,f+=V,V=1,C(cb.panel,"start-change","do-change"),cb.panelCover.style.cssText="position:absolute;left:0;top:0;right:0;bottom:0",document.activeElement.blur(),L=a[fb](w)),"cmyk"===h&&bb.cmyOnly&&(h="cmy"),i?(Y=!0,ab.alpha=z(f/100,0,1)):(c=S[h][K.z],ab[h][K.z]="Lab"===h?z(f,c[0],c[1]):z(f/c[1],0,1)),t(i?"alpha":h),B(d)):void 0
}function n(c){var d,e=c||a.event,f=e.which||e.keyCode,g=String.fromCharCode(f),h=document.activeElement,j=h.className.replace(bb.CSSPrefix,"").split("-"),k=j[0],l=j[1],m="alpha"===k,n="HEX"===k,o={k40:-1,k38:1,k34:-10,k33:10}["k"+f]/(m?100:1),p={HEX:/[0-9a-fA-F]/,Lab:/[\-0-9]/,alpha:/[\.0-9]/}[k]||/[0-9]/,q=S[k][k]||S[k][l],r=h.firstChild,s=H(h),u=r.data,w="0"!==u||n?u.split(""):[];return/^(?:27|13)$/.test(f)?(B(e),h.blur()):"keydown"===e.type?(o?d=z(db.round(1e6*(+u+o))/1e6,q[0],q[1]):/^(?:8|46)$/.test(f)&&(s.range||(s.range++,s.start-=8===f?1:0),w.splice(s.start,s.range),d=w.join("")||"0"),d!==b&&B(e,!0)):"keypress"===e.type&&(/^(?:37|39|8|46|9)$/.test(f)||B(e,!0),p.test(g)&&(w.splice(s.start,s.range,g),d=w.join("")),s.start++),13===f&&n?r.data.length%3===0||"0"===r.data?M.setColor("0"===r.data?"000":r.data,"rgb",ab.alpha,!0):(B(e,!0),h.focus()):(n&&d!==b&&(d=/^0+/.test(d)?d:parseInt(""+d,16)||0),void(d!==b&&""!==d&&+d>=q[0]&&+d<=q[1]&&(n&&(d=d.toString(16).toUpperCase()||"0"),m?ab[k]=+d:n||(ab[k][l]=+d/("Lab"===k?1:q[1])),t(m?"alpha":k),v(ab),I=!0,i(c,e.type),r.data=d,H(h,db.min(h.firstChild.data.length,s.start<0?0:s.start)))))}function o(c){var d,e,f=c||a.event,g=f.target||f.srcElement,h=g.className,j=g.parentNode,k=bb,l=ab.RND.rgb,m=bb.mode,n="",o=k.CSSPrefix,p=/(?:hs|rgb)/.test(j.className)&&/^[HSBLRG]$/.test(g.firstChild?g.firstChild.data:""),q=/dblc/.test(f.type),r="";if(!q||p){if(-1!==h.indexOf("-labl "+o+"labl"))C(cb[h.split("-")[0]],o+"hide",""),C(cb[j.className.split("-")[1]],o+"hide");else if(-1!==h.indexOf(o+"butt"))if(p)q&&2===bb.scale&&(n=/hs/.test(m.type)?"rgb":/hide/.test(cb.hsl.className)?"hsv":"hsl",n=n+"-"+n[m.type.indexOf(m.z)]),M.setMode(n?n:h.replace("-butt","").split(" ")[0]),r="modeChange";else if(/^[rgb]/.test(h))n=h.split("-")[1],C(cb.colorPicker,"no-rgb-"+n,(k["noRGB"+n]=!k["noRGB"+n])?b:""),r="noRGB"+n;else if(g===cb.alpha_labl)d=k.customBG,e=k.alphaBG,C(cb.colorPicker,"alpha-bg-"+e,"alpha-bg-"+(e=k.alphaBG=c.data||("w"===e?d?"c":"b":"c"===e?"b":"w"))),g.firstChild.data=e.toUpperCase(),cb.ctrl.style.backgroundColor=cb.memo.style.backgroundColor="c"!==e?"":"rgb("+db.round(255*d.r)+", "+db.round(255*d.g)+", "+db.round(255*d.b)+")",cb.raster.style.cssText=cb.raster_bg.previousSibling.style.cssText="c"!==e?"":A(d.luminance<.22?.5:.4),r="alphaBackground";else if(g===cb.alpha_butt)C(cb.colorPicker,"mute-alpha",(k.muteAlpha=!k.muteAlpha)?b:""),r="alphaState";else if(g===cb.HEX_butt)C(cb.colorPicker,"no-HEX",(k.HEXState=!k.HEXState)?b:""),r="HEXState";else if(g===cb.HEX_labl){var s="web save"===ab.saveColor;"web smart"===ab.saveColor||s?s?M.setColor(k.webUnsave,"rgb"):(k.webUnsave||(k.webUnsave=x(l)),M.setColor(ab.webSave,"rgb")):(k.webUnsave=x(l),M.setColor(ab.webSmart,"rgb")),r="webColorState"}else/Lab-x-labl/.test(h)&&(C(cb.colorPicker,"cmy-only",(k.cmyOnly=!k.cmyOnly)?b:""),r="cmykState");else if(g===cb.bsav)u(),r="saveAsBackground";else if(g===cb.bres){var w=x(l),y=ab.alpha;M.setColor(k.color),u(),M.setColor(w,"rgb",y),r="resetColor"}else if(j===cb.col1)ab.hsv.h-=ab.hsv.h>.5?.5:-.5,t("hsv"),r="shiftColor";else if(j===cb.col2)M.setColor(g.style.backgroundColor,"rgb",ab.background.alpha),r="setSavedColor";else if(j===cb.memo){var z=function(){cb.memos.blinker&&(cb.memos.blinker.style.cssText=cb.memos.cssText)},B=function(b){cb.memos.blinker=b,b.style.cssText="background-color:"+(ab.RGBLuminance>.22?"#333":"#DDD"),a.setTimeout(z,200)};if(g===cb.memo_cursor){z(),cb.memos.blinker=b,cb.testNode.style.cssText=cb.memo_store.style.cssText,cb.memos.cssText=cb.testNode.style.cssText;for(var D=cb.memos.length-1;D--;)if(cb.memos.cssText===cb.memos[D].style.cssText){B(cb.memos[D]);break}if(!cb.memos.blinker){for(var D=cb.memos.length-1;D--;)cb.memos[D+1].style.cssText=cb.memos[D].style.cssText;cb.memos[0].style.cssText=cb.memo_store.style.cssText}r="toMemory"}else z(),M.setColor(g.style.backgroundColor,"rgb",g.style.opacity||1),cb.memos.cssText=g.style.cssText,B(g),I=1,r="fromMemory"}r&&(v(ab),I=I||!0,i(c,r))}}function p(c,d){var e,f=c||a.event,g=f?E(f):{},h=d!==b,i=h?d:g.X-X.left+8,j=h?d:g.Y-X.top+8,k=[" S XS XXS"," S XS"," S",""],l=bb.sizes,m=h?d:j<l.XXS[1]+25?0:i<l.XS[0]+25?1:i<l.S[0]+25||j<l.S[1]+25?2:3,n=k[m],o=!1,p="";$.resizer!==n&&(o=/XX/.test(n),e=bb.mode,!o||/hs/.test(e.type)&&"h"!==e.z?e.original&&M.setMode(e.original):(p=e.type+"-"+e.z,M.setMode(/hs/.test(e.type)?e.type+"-s":"hsv-s"),bb.mode.original=p),cb.colorPicker.className=cb.colorPicker.className.replace(/\s+(?:S|XS|XXS)/g,"")+n,bb.scale=o?4:/S/.test(n)?2:1,bb.currentSize=m,$.resizer=n,Y=!0,w(),s()),cb.resizer.style.cssText="display: block;width: "+(i>10?i:10)+"px;height: "+(j>10?j:10)+"px;"}function q(a){var b={rgb_r:{x:"b",y:"g"},rgb_g:{x:"b",y:"r"},rgb_b:{x:"r",y:"g"},hsv_h:{x:"s",y:"v"},hsv_s:{x:"h",y:"v"},hsv_v:{x:"h",y:"s"},hsl_h:{x:"s",y:"l"},hsl_s:{x:"h",y:"l"},hsl_l:{x:"h",y:"s"}},c=a.replace("-","_"),d="\\b(?:rg|hs)\\w\\-\\w\\b";return C(cb.panel,d,a),C(cb.slds,d,a),a=a.split("-"),bb.mode={type:a[0],x:b[c].x,y:b[c].y,z:a[1]}}function r(){var a=/\s+(?:hue-)*(?:dark|light)/g,b="className";cb.curl[b]=cb.curl[b].replace(a,""),cb.curr[b]=cb.curr[b].replace(a,""),cb.slds[b]=cb.slds[b].replace(a,""),cb.sldr_2[b]=bb.CSSPrefix+"sldr-2",cb.sldr_4[b]=bb.CSSPrefix+"sldr-4",cb.sldl_3[b]=bb.CSSPrefix+"sldl-3";for(var c in cb.styles)c.indexOf("sld")||(cb.styles[c].cssText="");$={}}function s(){cb.styles.curr.cssText=cb.styles.curl.cssText,cb.curl.className=bb.CSSPrefix+"curl"+(Z.noRGBZ?" "+bb.CSSPrefix+"curl-"+Z.noRGBZ:""),cb.curr.className=bb.CSSPrefix+"curr "+bb.CSSPrefix+"curr-"+("h"===bb.mode.z?Z.HUEContrast:Z.noRGBZ?Z.noRGBZ:Z.RGBLuminance)}function t(a){v(_.setColor(b,a||bb.mode.type)),Y=!0}function u(a){return _.saveAsBackground(),cb.styles.col2.cssText="background-color: "+y(ab.background.RGB)+";"+A(ab.background.alpha),a&&v(ab),ab}function v(a){var c=db,d=Z,e=T[bb.alphaBG];d.hueDelta=c.round(100*a["rgbaMixBGMix"+e].hueDelta),d.luminanceDelta=c.round(100*a["rgbaMixBGMix"+e].luminanceDelta),d.RGBLuminance=a.RGBLuminance>.22?"light":"dark",d.HUEContrast=a.HUELuminance>.22?"light":"dark",d.contrast=d.luminanceDelta>d.hueDelta?"contrast":"",d.readabiltiy=a["rgbaMixBGMix"+e].WCAG2Ratio>=7?"green":a["rgbaMixBGMix"+e].WCAG2Ratio>=4.5?"orange":"",d.noRGBZ=bb["no"+bb.mode.type.toUpperCase()+bb.mode.z]?"g"===bb.mode.z&&a.rgb.g<.59||"b"===bb.mode.z||"r"===bb.mode.z?"dark":"light":b}function w(){if(I){if(!Y)return L=a[fb](w);Y=!1}var c,d,e,f,g=bb,h=g.mode,i=g.scale,l=g.CSSPrefix,m=ab,n=cb,o=n.styles,p=n.textNodes,q=S,r=K,s=Z,t=$,u=db,v=A,x=y,z=0,B=0,C=m[h.type][h.x],D=u.round(255*C/(4===i?2:i)),E=m[h.type][h.y],F=1-E,G=u.round(255*F/i),H=1-m[h.type][h.z],M=u.round(255*H/i),N=[C,E],O="rgb"===h.type,P="h"===h.z,Q="hsl"===h.type,R=Q&&"s"===h.z,T=I===j,U=I===k;O&&(N[0]>=N[1]?B=1:z=1,t.sliderSwap!==z&&(n.sldr_2.className=g.CSSPrefix+"sldr-"+(3-z),t.sliderSwap=z)),(O&&!U||P&&!T||!P&&!U)&&(o[P?"sldl_2":"sldr_2"][O?"cssText":"backgroundColor"]=O?v((N[z]-N[B])/(1-N[B]||0)):x(m.hueRGB)),P||(U||(o.sldr_4.cssText=v(O?N[B]:R?u.abs(1-2*F):F)),T||(o.sldl_3.cssText=v(Q&&"l"===h.z?u.abs(1-2*H):H)),Q&&(f=R?"sldr_4":"sldl_3",d=R?"r-":"l-",e=R?F>.5?4:3:H>.5?3:4,t[f]!==e&&(n[f].className=g.CSSPrefix+"sld"+d+e,t[f]=e))),U||(o.curm.cssText="left: "+D+"px; top: "+G+"px;"),T||(o.curl.top=M+"px"),r&&(o.curr.top=M+"px"),(r&&"alpha"===r.type||J===n.opacity)&&(o.opacity_slider.left=g.opacityPositionRelative?m.alpha*((X.width||n.opacity.offsetWidth)-(X.childWidth||n.opacity_slider.offsetWidth))+"px":100*m.alpha+"%"),o.col1.cssText="background-color: "+x(m.RND.rgb)+"; "+(g.muteAlpha?"":v(m.alpha)),o.opacity.backgroundColor=x(m.RND.rgb),o.cold.width=s.hueDelta+"%",o.cont.width=s.luminanceDelta+"%";for(c in p)d=c.split("_"),g.cmyOnly&&(d[0]=d[0].replace("k","")),e=d[1]?m.RND[d[0]][d[1]]:m.RND[d[0]]||m[d[0]],t[c]!==e&&(t[c]=e,p[c].data=e>359.5&&"HEX"!==c?0:e,"HEX"===c||g.noRangeBackground||(e=m[d[0]][d[1]]!==b?m[d[0]][d[1]]:m[d[0]],"Lab"===d[0]&&(e=(e-q[d[0]][d[1]][0])/(q[d[0]][d[1]][1]-q[d[0]][d[1]][0])),o[c].backgroundPosition=u.round(100*(1-e))+"% 0%"));d=m._rgb?[m._rgb.r!==m.rgb.r,m._rgb.g!==m.rgb.g,m._rgb.b!==m.rgb.b]:[],d.join("")!==t.outOfGammut&&(n.rgb_r_labl.firstChild.data=d[0]?"!":" ",n.rgb_g_labl.firstChild.data=d[1]?"!":" ",n.rgb_b_labl.firstChild.data=d[2]?"!":" ",t.outOfGammut=d.join("")),s.noRGBZ&&t.noRGBZ!==s.noRGBZ&&(n.curl.className=l+"curl "+l+"curl-"+s.noRGBZ,U||(n.curr.className=l+"curr "+l+"curr-"+s.noRGBZ),t.noRGBZ=s.noRGBZ),t.HUEContrast!==s.HUEContrast&&"h"===h.z?(n.slds.className=n.slds.className.replace(/\s+hue-(?:dark|light)/,"")+" hue-"+s.HUEContrast,U||(n.curr.className=l+"curr "+l+"curr-"+s.HUEContrast),t.HUEContrast=s.HUEContrast):t.RGBLuminance!==s.RGBLuminance&&(n.colorPicker.className=n.colorPicker.className.replace(/\s+(?:dark|light)/,"")+" "+s.RGBLuminance,U||"h"===h.z||s.noRGBZ||(n.curr.className=l+"curr "+l+"curr-"+s.RGBLuminance),t.RGBLuminance=s.RGBLuminance),(t.contrast!==s.contrast||t.readabiltiy!==s.readabiltiy)&&(n.ctrl.className=n.ctrl.className.replace(" contrast","").replace(/\s*(?:orange|green)/,"")+(s.contrast?" "+s.contrast:"")+(s.readabiltiy?" "+s.readabiltiy:""),t.contrast=s.contrast,t.readabiltiy=s.readabiltiy),t.saveColor!==m.saveColor&&(n.HEX_labl.firstChild.data=m.saveColor?"web save"===m.saveColor?"W":"M":"!",t.saveColor=m.saveColor),g.renderCallback&&g.renderCallback(m,h),I&&(L=a[fb](w))}function x(a){var b={};for(var c in a)b[c]=a[c];return b}function y(a,b){for(var c="",d=(b||"rgb").split(""),e=d.length;e--;)c=", "+a[d[e]]+c;return(b||"rgb")+"("+c.substr(2)+")"}function z(a,b,c){return a>c?c:b>a?b:a}function A(a){return a===b&&(a=1),R?"opacity: "+db.round(1e10*a)/1e10+";":"filter: alpha(opacity="+db.round(100*a)+");"}function B(b,c){return b.preventDefault?b.preventDefault():b.returnValue=!1,c||(a.getSelection?a.getSelection().removeAllRanges():document.selection.empty()),!1}function C(a,c,d){return a?a.className=d!==b?a.className.replace(new RegExp("\\s+?"+c,"g"),d?" "+d:""):a.className+" "+c:!1}function D(b){var c=b.getBoundingClientRect?b.getBoundingClientRect():{top:0,left:0},d=b&&b.ownerDocument,e=d.body,f=d.defaultView||d.parentWindow||a,g=d.documentElement||e.parentNode,h=g.clientTop||e.clientTop||0,i=g.clientLeft||e.clientLeft||0;return{left:c.left+(f.pageXOffset||g.scrollLeft)-i,top:c.top+(f.pageYOffset||g.scrollTop)-h}}function E(b){var c=a.document;return{X:b.pageX||b.clientX+c.body.scrollLeft+c.documentElement.scrollLeft,Y:b.pageY||b.clientY+c.body.scrollTop+c.documentElement.scrollTop}}function F(a,b,c){F.cache=F.cache||{_get:function(a,b,c,d){for(var e=F.cache[b]||[],f=e.length;f--;)if(a===e[f].obj&&""+c==""+e[f].func)return c=e[f].func,d||(e[f]=e[f].obj=e[f].func=null,e.splice(f,1)),c},_set:function(a,b,c){var d=F.cache[b]=F.cache[b]||[];return F.cache._get(a,b,c,!0)?!0:void d.push({func:c,obj:a})}},!c.name&&F.cache._set(a,b,c)||"function"!=typeof c||(a.addEventListener?a.addEventListener(b,c,!1):a.attachEvent("on"+b,c))}function G(a,b,c){"function"==typeof c&&(c.name||(c=F.cache._get(a,b,c)||c),a.removeEventListener?a.removeEventListener(b,c,!1):a.detachEvent("on"+b,c))}function H(c,d){var e={};if(d===b){if(a.getSelection){c.focus();var f=a.getSelection().getRangeAt(0),g=f.cloneRange();g.selectNodeContents(c),g.setEnd(f.endContainer,f.endOffset),e={end:g.toString().length,range:f.toString().length}}else{c.focus();var f=document.selection.createRange(),g=document.body.createTextRange();g.moveToElementText(c),g.setEndPoint("EndToEnd",f),e={end:g.text.length,range:f.text.length}}return e.start=e.end-e.range,e}if(-1==d&&(d=c.text().length),a.getSelection)c.focus(),a.getSelection().collapse(c.firstChild,d);else{var h=document.body.createTextRange();h.moveToElementText(c),h.moveStart("character",d),h.collapse(!0),h.select()}return d}var I,J,K,L,M,N,O=a.ColorPicker,P=!O,Q=!1,R=!1,S={},T={w:"White",b:"Black",c:"Custom"},U="",V=1,W={},X={},Y=!0,Z={},$={},_={},ab={},bb={},cb={},db=Math,eb="AnimationFrame",fb="request"+eb,gb="cancel"+eb,hb=["ms","moz","webkit","o"],ib=function(a){this.options={color:"rgba(204, 82, 37, 0.8)",mode:"rgb-b",fps:60,delayOffset:8,CSSPrefix:"cp-",allMixDetails:!0,alphaBG:"w",imagePath:""},c(this,a||{})};a.ColorPicker=ib,ib.addEvent=F,ib.removeEvent=G,ib.getOrigin=D,ib.limitValue=z,ib.changeClass=C,ib.prototype.setColor=function(a,b,c,e){d(this),K=!0,v(_.setColor.apply(_,arguments)),e&&this.startRender(!0)},ib.prototype.saveAsBackground=function(){return d(this),u(!0)},ib.prototype.setCustomBackground=function(a){return d(this),_.setCustomBackground(a)},ib.prototype.startRender=function(b){d(this),b?(I=!1,w(),this.stopRender()):(I=1,L=a[fb](w))},ib.prototype.stopRender=function(){d(this),a[gb](L),K&&(I=1,i(b,"external"))},ib.prototype.setMode=function(a){d(this),q(a),r(),w()},ib.prototype.destroyAll=function(){var a=this.nodes.colorPicker,b=function(a){for(var c in a)(a[c]&&"[object Object]"===a[c].toString()||a[c]instanceof Array)&&b(a[c]),a[c]=null,delete a[c]};this.stopRender(),h(this,!0),b(this),a.parentNode.removeChild(a),a=null},ib.prototype.renderMemory=function(a){var c=this.nodes.memos,d=[];"string"==typeof a&&(a=a.replace(/^'|'$/g,"").replace(/\s*/,"").split("','"));for(var e=c.length;e--;)a&&"string"==typeof a[e]&&(d=a[e].replace("rgba(","").replace(")","").split(","),a[e]={r:d[0],g:d[1],b:d[2],a:d[3]}),c[e].style.cssText="background-color: "+(a&&a[e]!==b?y(a[e])+";"+A(a[e].a||1):"rgb(0,0,0);")},F(Q?document.body:a,"mouseup",i);for(var jb=hb.length;jb--&&!a[fb];)a[fb]=a[hb[jb]+"Request"+eb],a[gb]=a[hb[jb]+"Cancel"+eb]||a[hb[jb]+"CancelRequest"+eb];a[fb]=a[fb]||function(b){return a.setTimeout(b,1e3/bb.fps)},a[gb]=a[gb]||function(b){return a.clearTimeout(b),L=null}}(window),function(a){a.jsColorPicker=function(b,c){var d=function(a,b){var c=this,d=c.input,e=c.patch,f=a.RND.rgb,g=a.RND.hsl,h=c.isIE8?(a.alpha<.16?"0":"")+Math.round(100*a.alpha).toString(16).toUpperCase()+a.HEX:"",i=f.r+", "+f.g+", "+f.b,j="rgba("+i+", "+a.alpha+")",k=1!==a.alpha&&!c.isIE8,l=d.getAttribute("data-colorMode");e.style.cssText="color:"+(a.rgbaMixCustom.luminance>.22?"#222":"#ddd")+";background-color:"+j+";filter:"+(c.isIE8?"progid:DXImageTransform.Microsoft.gradient(startColorstr=#"+h+",endColorstr=#"+h+")":""),d.value="HEX"!==l||k?"rgb"===l||"HEX"===l&&k?k?j:"rgb("+i+")":"hsl"+(k?"a(":"(")+g.h+", "+g.s+"%, "+g.l+"%"+(k?", "+a.alpha:"")+")":"#"+(c.isIE8?h:a.HEX),c.displayCallback&&c.displayCallback(a,b,c)},e=function(a){return a.value||a.getAttribute("value")||a.style.backgroundColor||"#FFFFFF"},f=function(a,b){var c=this,d=i.current;if("toMemory"===b){for(var e=d.nodes.memos,f="",g=0,h=[],j=0,k=e.length;k>j;j++)f=e[j].style.backgroundColor,g=e[j].style.opacity,g=Math.round(100*(""===g?1:g))/100,h.push(f.replace(/, /g,",").replace("rgb(","rgba(").replace(")",","+g+")"));h="'"+h.join("','")+"'",ColorPicker.docCookies("colorPickerMemos"+(c.noAlpha?"NoAlpha":""),h)}else if("resizeApp"===b)ColorPicker.docCookies("colorPickerSize",d.color.options.currentSize);else if("modeChange"===b){var l=d.color.options.mode;ColorPicker.docCookies("colorPickerMode",l.type+"-"+l.z)}},g=function(b,c){var g={klass:a.ColorPicker,input:b,patch:b,isIE8:!!document.all&&!document.addEventListener,margin:{left:-1,top:2},customBG:"#FFFFFF",color:e(b),initStyle:"display: none",mode:ColorPicker.docCookies("colorPickerMode")||"hsv-h",memoryColors:ColorPicker.docCookies("colorPickerMemos"+((c||{}).noAlpha?"NoAlpha":"")),size:ColorPicker.docCookies("colorPickerSize")||1,renderCallback:d,actionCallback:f};for(var h in c)g[h]=c[h];return new g.klass(g)},h=function(b,d,f){var h=f?"removeEventListener":"addEventListener",k=function(){var f=this,h=a.ColorPicker.getOrigin(f),k=d?Array.prototype.indexOf.call(j,this):0,l=i[k]||(i[k]=g(this,c)),m=l.color.options,n=l.nodes.colorPicker,o=m.appendTo||document.body,p=/static/.test(a.getComputedStyle(o).position),q=p?{left:0,top:0}:o.getBoundingClientRect(),r=0;m.color=e(b),n.style.cssText="position: absolute;"+(i[k].cssIsReady?"":"display: none;")+"left:"+(h.left+m.margin.left-q.left)+"px;top:"+(h.top+ +f.offsetHeight+m.margin.top-q.top)+"px;",d||(m.input=b,m.patch=b,l.setColor(e(b),void 0,void 0,!0),l.saveAsBackground()),i.current=i[k],o.appendChild(n),r=setInterval(function(){i.current.cssIsReady&&(r=clearInterval(r),n.style.display="block")},10)},l=function(a){var b=i.current,c=b?b.nodes.colorPicker:void 0,d=(b?b.color.options.animationSpeed:0,b&&function(a){for(;a;){if(-1!==(a.className||"").indexOf("cp-app"))return a;a=a.parentNode}return!1}(a.target)),e=Array.prototype.indexOf.call(j,a.target);d&&Array.prototype.indexOf.call(i,d)?a.target===b.nodes.exit&&(c.style.display="none",document.activeElement.blur()):-1!==e||c&&(c.style.display="none")};b[h]("focus",k),(!i.evt||f)&&(i.evt=!0,a[h]("mousedown",l))},i=a.jsColorPicker.colorPickers||[],j=document.querySelectorAll(b),k=new a.Colors({customBG:c.customBG,allMixDetails:!0});a.jsColorPicker.colorPickers=i;for(var l=0,m=j.length;m>l;l++){var n=j[l];if("destroy"===c)h(n,c&&c.multipleInstances,!0),i[l]&&i[l].destroyAll();else{var o=e(n),p=o.split("(");k.setColor(o),c&&c.init&&c.init(n,k.colors),n.setAttribute("data-colorMode",p[1]?p[0].substr(0,3):"HEX"),h(n,c&&c.multipleInstances,!1),c&&c.readOnly&&(n.readOnly=!0)}}return a.jsColorPicker.colorPickers},a.ColorPicker.docCookies=function(a,b,c){var d,e,f,g,h=encodeURIComponent,i=decodeURIComponent,j={};if(void 0===b){for(d=document.cookie.split(/;\s*/)||[],e=d.length;e--;)f=d[e].split("="),f[0]&&(j[i(f.shift())]=i(f.join("=")));return a?j[a]:j}c=c||{},(""===b||c.expires<0)&&(c.expires=-1),void 0!==c.expires&&(g=new Date,g.setDate(g.getDate()+c.expires)),document.cookie=h(a)+"="+h(b)+(g?"; expires="+g.toUTCString():"")+(c.path?"; path="+c.path:"")+(c.domain?"; domain="+c.domain:"")+(c.secure?"; secure":"")}}(this);
//# sourceMappingURL=../colorPicker.js.map

/*mini wysiwyg*/
!function(a){"use strict";var b=function(b){var c=a.Deferred(),d=new FileReader;return d.onload=function(a){c.resolve(a.target.result)},d.onerror=c.reject,d.onprogress=c.notify,d.readAsDataURL(b),c.promise()};a.fn.cleanHtml=function(){var b=a(this).html();return b&&b.replace(/(<br>|\s|<div><br><\/div>|&nbsp;)*$/,"")},a.fn.wysiwyg=function(c){var e,f,g,d=this,h=function(){f.activeToolbarClass&&a(f.toolbarSelector).find(g).each(function(){var b=a(this).data(f.commandRole);document.queryCommandState(b)?a(this).addClass(f.activeToolbarClass):a(this).removeClass(f.activeToolbarClass)})},i=function(a,b){var c=a.split(" "),d=c.shift(),e=c.join(" ")+(b||"");document.execCommand(d,0,e),h()},j=function(b){a.each(b,function(a,b){d.keydown(a,function(a){d.attr("contenteditable")&&d.is(":visible")&&(a.preventDefault(),a.stopPropagation(),i(b))}).keyup(a,function(a){d.attr("contenteditable")&&d.is(":visible")&&(a.preventDefault(),a.stopPropagation())})})},k=function(){var a=window.getSelection();if(a.getRangeAt&&a.rangeCount)return a.getRangeAt(0)},l=function(){e=k()},m=function(){var a=window.getSelection();if(e){try{a.removeAllRanges()}catch(a){document.body.createTextRange().select(),document.selection.empty()}a.addRange(e)}},n=function(c){d.focus(),a.each(c,function(c,d){/^image\//.test(d.type)?a.when(b(d)).done(function(a){i("insertimage",a)}).fail(function(a){f.fileUploadError("file-reader",a)}):f.fileUploadError("unsupported-file-type",d.type)})},o=function(a,b){m(),document.queryCommandSupported("hiliteColor")&&document.execCommand("hiliteColor",0,b||"transparent"),l(),a.data(f.selectionMarker,b)},p=function(b,c){b.find(g).click(function(){m(),d.focus(),i(a(this).data(c.commandRole)),l()}),b.find("[data-toggle=dropdown]").click(m),b.find("input[type=text][data-"+c.commandRole+"]").on("webkitspeechchange change",function(){var b=this.value;this.value="",m(),b&&(d.focus(),i(a(this).data(c.commandRole),b)),l()}).on("focus",function(){var b=a(this);b.data(c.selectionMarker)||(o(b,c.selectionColor),b.focus())}).on("blur",function(){var b=a(this);b.data(c.selectionMarker)&&o(b,!1)}),b.find("input[type=file][data-"+c.commandRole+"]").change(function(){m(),"file"===this.type&&this.files&&this.files.length>0&&n(this.files),l(),this.value=""})},q=function(){d.on("dragenter dragover",!1).on("drop",function(a){var b=a.originalEvent.dataTransfer;a.stopPropagation(),a.preventDefault(),b&&b.files&&b.files.length>0&&n(b.files)})};return f=a.extend({},a.fn.wysiwyg.defaults,c),g="a[data-"+f.commandRole+"],button[data-"+f.commandRole+"],input[type=button][data-"+f.commandRole+"]",j(f.hotKeys),f.dragAndDropImages&&q(),p(a(f.toolbarSelector),f),d.attr("contenteditable",!0).on("mouseup keyup mouseout",function(){l(),h()}),a(window).bind("touchend",function(a){var b=d.is(a.target)||d.has(a.target).length>0,c=k(),e=c&&c.startContainer===c.endContainer&&c.startOffset===c.endOffset;e&&!b||(l(),h())}),this},a.fn.wysiwyg.defaults={hotKeys:{"ctrl+b meta+b":"bold","ctrl+i meta+i":"italic","ctrl+u meta+u":"underline","ctrl+z meta+z":"undo","ctrl+y meta+y meta+shift+z":"redo","ctrl+l meta+l":"justifyleft","ctrl+r meta+r":"justifyright","ctrl+e meta+e":"justifycenter","ctrl+j meta+j":"justifyfull","shift+tab":"outdent",tab:"indent"},toolbarSelector:"[data-role=editor-toolbar]",commandRole:"edit",activeToolbarClass:"btn-info",selectionMarker:"edit-focus-marker",selectionColor:"darkgrey",dragAndDropImages:!0,fileUploadError:function(a,b){console.log("File upload error",a,b)}}}(window.$);

var gd={};gd.path="//"+window.location.hostname+"/";gd.object="";gd.element="";
gd.alpha= [0,'A','B','C','D','E','F','G','H','I','J','K','L','M','N','O','P','Q','R','S','T','U','V','W','X','Y','Z'];
gd.numalpha= {'A':1,'B':2,'C':3,'D':4,'E':5,'F':6,'G':7,'H':8,'I':9,'J':10,'K':11,'L':12,'M':13,'N':14,'O':15,'P':16,'Q':17,'R':18,'S':19,'T':20,'U':21,'V':22,'W':23,'X':24,'Y':25,'Z':26};
gd.fa_icons={"fa-glass":"f000","fa-music":"f001","fa-search":"f002","fa-envelope-o":"f003","fa-heart":"f004","fa-star":"f005","fa-star-o":"f006","fa-user":"f007","fa-film":"f008","fa-th-large":"f009","fa-th":"f00a","fa-th-list":"f00b","fa-check":"f00c","fa-times":"f00d","fa-search-plus":"f00e","fa-search-minus":"f010","fa-power-off":"f011","fa-signal":"f012","fa-cog":"f013","fa-trash-o":"f014","fa-home":"f015","fa-file-o":"f016","fa-clock-o":"f017","fa-road":"f018","fa-download":"f019","fa-arrow-circle-o-down":"f01a","fa-arrow-circle-o-up":"f01b","fa-inbox":"f01c","fa-play-circle-o":"f01d","fa-repeat":"f01e","fa-refresh":"f021","fa-list-alt":"f022","fa-lock":"f023","fa-flag":"f024","fa-headphones":"f025","fa-volume-off":"f026","fa-volume-down":"f027","fa-volume-up":"f028","fa-qrcode":"f029","fa-barcode":"f02a","fa-tag":"f02b","fa-tags":"f02c","fa-book":"f02d","fa-bookmark":"f02e","fa-print":"f02f","fa-camera":"f030","fa-font":"f031","fa-bold":"f032","fa-italic":"f033","fa-text-height":"f034","fa-text-width":"f035","fa-align-left":"f036","fa-align-center":"f037","fa-align-right":"f038","fa-align-justify":"f039","fa-list":"f03a","fa-outdent":"f03b","fa-indent":"f03c","fa-video-camera":"f03d","fa-picture-o":"f03e","fa-pencil":"f040","fa-map-marker":"f041","fa-adjust":"f042","fa-tint":"f043","fa-pencil-square-o":"f044","fa-share-square-o":"f045","fa-check-square-o":"f046","fa-arrows":"f047","fa-step-backward":"f048","fa-fast-backward":"f049","fa-backward":"f04a","fa-play":"f04b","fa-pause":"f04c","fa-stop":"f04d","fa-forward":"f04e","fa-fast-forward":"f050","fa-step-forward":"f051","fa-eject":"f052","fa-chevron-left":"f053","fa-chevron-right":"f054","fa-plus-circle":"f055","fa-minus-circle":"f056","fa-times-circle":"f057","fa-check-circle":"f058","fa-question-circle":"f059","fa-info-circle":"f05a","fa-crosshairs":"f05b","fa-times-circle-o":"f05c","fa-check-circle-o":"f05d","fa-ban":"f05e","fa-arrow-left":"f060","fa-arrow-right":"f061","fa-arrow-up":"f062","fa-arrow-down":"f063","fa-share":"f064","fa-expand":"f065","fa-compress":"f066","fa-plus":"f067","fa-minus":"f068","fa-asterisk":"f069","fa-exclamation-circle":"f06a","fa-gift":"f06b","fa-leaf":"f06c","fa-fire":"f06d","fa-eye":"f06e","fa-eye-slash":"f070","fa-exclamation-triangle":"f071","fa-plane":"f072","fa-calendar":"f073","fa-random":"f074","fa-comment":"f075","fa-magnet":"f076","fa-chevron-up":"f077","fa-chevron-down":"f078","fa-retweet":"f079","fa-shopping-cart":"f07a","fa-folder":"f07b","fa-folder-open":"f07c","fa-arrows-v":"f07d","fa-arrows-h":"f07e","fa-bar-chart":"f080","fa-twitter-square":"f081","fa-facebook-square":"f082","fa-camera-retro":"f083","fa-key":"f084","fa-cogs":"f085","fa-comments":"f086","fa-thumbs-o-up":"f087","fa-thumbs-o-down":"f088","fa-star-half":"f089","fa-heart-o":"f08a","fa-sign-out":"f08b","fa-linkedin-square":"f08c","fa-thumb-tack":"f08d","fa-external-link":"f08e","fa-sign-in":"f090","fa-trophy":"f091","fa-github-square":"f092","fa-upload":"f093","fa-lemon-o":"f094","fa-phone":"f095","fa-square-o":"f096","fa-bookmark-o":"f097","fa-phone-square":"f098","fa-twitter":"f099","fa-facebook":"f09a","fa-github":"f09b","fa-unlock":"f09c","fa-credit-card":"f09d","fa-rss":"f09e","fa-hdd-o":"f0a0","fa-bullhorn":"f0a1","fa-bell":"f0f3","fa-certificate":"f0a3","fa-hand-o-right":"f0a4","fa-hand-o-left":"f0a5","fa-hand-o-up":"f0a6","fa-hand-o-down":"f0a7","fa-arrow-circle-left":"f0a8","fa-arrow-circle-right":"f0a9","fa-arrow-circle-up":"f0aa","fa-arrow-circle-down":"f0ab","fa-globe":"f0ac","fa-wrench":"f0ad","fa-tasks":"f0ae","fa-filter":"f0b0","fa-briefcase":"f0b1","fa-arrows-alt":"f0b2","fa-users":"f0c0","fa-link":"f0c1","fa-cloud":"f0c2","fa-flask":"f0c3","fa-scissors":"f0c4","fa-files-o":"f0c5","fa-paperclip":"f0c6","fa-floppy-o":"f0c7","fa-square":"f0c8","fa-bars":"f0c9","fa-list-ul":"f0ca","fa-list-ol":"f0cb","fa-strikethrough":"f0cc","fa-underline":"f0cd","fa-table":"f0ce","fa-magic":"f0d0","fa-truck":"f0d1","fa-pinterest":"f0d2","fa-pinterest-square":"f0d3","fa-google-plus-square":"f0d4","fa-google-plus":"f0d5","fa-money":"f0d6","fa-caret-down":"f0d7","fa-caret-up":"f0d8","fa-caret-left":"f0d9","fa-caret-right":"f0da","fa-columns":"f0db","fa-sort":"f0dc","fa-sort-desc":"f0dd","fa-sort-asc":"f0de","fa-envelope":"f0e0","fa-linkedin":"f0e1","fa-undo":"f0e2","fa-gavel":"f0e3","fa-tachometer":"f0e4","fa-comment-o":"f0e5","fa-comments-o":"f0e6","fa-bolt":"f0e7","fa-sitemap":"f0e8","fa-umbrella":"f0e9","fa-clipboard":"f0ea","fa-lightbulb-o":"f0eb","fa-exchange":"f0ec","fa-cloud-download":"f0ed","fa-cloud-upload":"f0ee","fa-user-md":"f0f0","fa-stethoscope":"f0f1","fa-suitcase":"f0f2","fa-bell-o":"f0a2","fa-coffee":"f0f4","fa-cutlery":"f0f5","fa-file-text-o":"f0f6","fa-building-o":"f0f7","fa-hospital-o":"f0f8","fa-ambulance":"f0f9","fa-medkit":"f0fa","fa-fighter-jet":"f0fb","fa-beer":"f0fc","fa-h-square":"f0fd","fa-plus-square":"f0fe","fa-angle-double-left":"f100","fa-angle-double-right":"f101","fa-angle-double-up":"f102","fa-angle-double-down":"f103","fa-angle-left":"f104","fa-angle-right":"f105","fa-angle-up":"f106","fa-angle-down":"f107","fa-desktop":"f108","fa-laptop":"f109","fa-tablet":"f10a","fa-mobile":"f10b","fa-circle-o":"f10c","fa-quote-left":"f10d","fa-quote-right":"f10e","fa-spinner":"f110","fa-circle":"f111","fa-reply":"f112","fa-github-alt":"f113","fa-folder-o":"f114","fa-folder-open-o":"f115","fa-smile-o":"f118","fa-frown-o":"f119","fa-meh-o":"f11a","fa-gamepad":"f11b","fa-keyboard-o":"f11c","fa-flag-o":"f11d","fa-flag-checkered":"f11e","fa-terminal":"f120","fa-code":"f121","fa-reply-all":"f122","fa-star-half-o":"f123","fa-location-arrow":"f124","fa-crop":"f125","fa-code-fork":"f126","fa-chain-broken":"f127","fa-question":"f128","fa-info":"f129","fa-exclamation":"f12a","fa-superscript":"f12b","fa-subscript":"f12c","fa-eraser":"f12d","fa-puzzle-piece":"f12e","fa-microphone":"f130","fa-microphone-slash":"f131","fa-shield":"f132","fa-calendar-o":"f133","fa-fire-extinguisher":"f134","fa-rocket":"f135","fa-maxcdn":"f136","fa-chevron-circle-left":"f137","fa-chevron-circle-right":"f138","fa-chevron-circle-up":"f139","fa-chevron-circle-down":"f13a","fa-html5":"f13b","fa-css3":"f13c","fa-anchor":"f13d","fa-unlock-alt":"f13e","fa-bullseye":"f140","fa-ellipsis-h":"f141","fa-ellipsis-v":"f142","fa-rss-square":"f143","fa-play-circle":"f144","fa-ticket":"f145","fa-minus-square":"f146","fa-minus-square-o":"f147","fa-level-up":"f148","fa-level-down":"f149","fa-check-square":"f14a","fa-pencil-square":"f14b","fa-external-link-square":"f14c","fa-share-square":"f14d","fa-compass":"f14e","fa-caret-square-o-down":"f150","fa-caret-square-o-up":"f151","fa-caret-square-o-right":"f152","fa-eur":"f153","fa-gbp":"f154","fa-usd":"f155","fa-inr":"f156","fa-jpy":"f157","fa-rub":"f158","fa-krw":"f159","fa-btc":"f15a","fa-file":"f15b","fa-file-text":"f15c","fa-sort-alpha-asc":"f15d","fa-sort-alpha-desc":"f15e","fa-sort-amount-asc":"f160","fa-sort-amount-desc":"f161","fa-sort-numeric-asc":"f162","fa-sort-numeric-desc":"f163","fa-thumbs-up":"f164","fa-thumbs-down":"f165","fa-youtube-square":"f166","fa-youtube":"f167","fa-xing":"f168","fa-xing-square":"f169","fa-youtube-play":"f16a","fa-dropbox":"f16b","fa-stack-overflow":"f16c","fa-instagram":"f16d","fa-flickr":"f16e","fa-adn":"f170","fa-bitbucket":"f171","fa-bitbucket-square":"f172","fa-tumblr":"f173","fa-tumblr-square":"f174","fa-long-arrow-down":"f175","fa-long-arrow-up":"f176","fa-long-arrow-left":"f177","fa-long-arrow-right":"f178","fa-apple":"f179","fa-windows":"f17a","fa-android":"f17b","fa-linux":"f17c","fa-dribbble":"f17d","fa-skype":"f17e","fa-foursquare":"f180","fa-trello":"f181","fa-female":"f182","fa-male":"f183","fa-gratipay":"f184","fa-sun-o":"f185","fa-moon-o":"f186","fa-archive":"f187","fa-bug":"f188","fa-vk":"f189","fa-weibo":"f18a","fa-renren":"f18b","fa-pagelines":"f18c","fa-stack-exchange":"f18d","fa-arrow-circle-o-right":"f18e","fa-arrow-circle-o-left":"f190","fa-caret-square-o-left":"f191","fa-dot-circle-o":"f192","fa-wheelchair":"f193","fa-vimeo-square":"f194","fa-try":"f195","fa-plus-square-o":"f196","fa-space-shuttle":"f197","fa-slack":"f198","fa-envelope-square":"f199","fa-wordpress":"f19a","fa-openid":"f19b","fa-university":"f19c","fa-graduation-cap":"f19d","fa-yahoo":"f19e","fa-google":"f1a0","fa-reddit":"f1a1","fa-reddit-square":"f1a2","fa-stumbleupon-circle":"f1a3","fa-stumbleupon":"f1a4","fa-delicious":"f1a5","fa-digg":"f1a6","fa-pied-piper-pp":"f1a7","fa-pied-piper-alt":"f1a8","fa-drupal":"f1a9","fa-joomla":"f1aa","fa-language":"f1ab","fa-fax":"f1ac","fa-building":"f1ad","fa-child":"f1ae","fa-paw":"f1b0","fa-spoon":"f1b1","fa-cube":"f1b2","fa-cubes":"f1b3","fa-behance":"f1b4","fa-behance-square":"f1b5","fa-steam":"f1b6","fa-steam-square":"f1b7","fa-recycle":"f1b8","fa-car":"f1b9","fa-taxi":"f1ba","fa-tree":"f1bb","fa-spotify":"f1bc","fa-deviantart":"f1bd","fa-soundcloud":"f1be","fa-database":"f1c0","fa-file-pdf-o":"f1c1","fa-file-word-o":"f1c2","fa-file-excel-o":"f1c3","fa-file-powerpoint-o":"f1c4","fa-file-image-o":"f1c5","fa-file-archive-o":"f1c6","fa-file-audio-o":"f1c7","fa-file-video-o":"f1c8","fa-file-code-o":"f1c9","fa-vine":"f1ca","fa-codepen":"f1cb","fa-jsfiddle":"f1cc","fa-life-ring":"f1cd","fa-circle-o-notch":"f1ce","fa-rebel":"f1d0","fa-empire":"f1d1","fa-git-square":"f1d2","fa-git":"f1d3","fa-hacker-news":"f1d4","fa-tencent-weibo":"f1d5","fa-qq":"f1d6","fa-weixin":"f1d7","fa-paper-plane":"f1d8","fa-paper-plane-o":"f1d9","fa-history":"f1da","fa-circle-thin":"f1db","fa-header":"f1dc","fa-paragraph":"f1dd","fa-sliders":"f1de","fa-share-alt":"f1e0","fa-share-alt-square":"f1e1","fa-bomb":"f1e2","fa-futbol-o":"f1e3","fa-tty":"f1e4","fa-binoculars":"f1e5","fa-plug":"f1e6","fa-slideshare":"f1e7","fa-twitch":"f1e8","fa-yelp":"f1e9","fa-newspaper-o":"f1ea","fa-wifi":"f1eb","fa-calculator":"f1ec","fa-paypal":"f1ed","fa-google-wallet":"f1ee","fa-cc-visa":"f1f0","fa-cc-mastercard":"f1f1","fa-cc-discover":"f1f2","fa-cc-amex":"f1f3","fa-cc-paypal":"f1f4","fa-cc-stripe":"f1f5","fa-bell-slash":"f1f6","fa-bell-slash-o":"f1f7","fa-trash":"f1f8","fa-copyright":"f1f9","fa-at":"f1fa","fa-eyedropper":"f1fb","fa-paint-brush":"f1fc","fa-birthday-cake":"f1fd","fa-area-chart":"f1fe","fa-pie-chart":"f200","fa-line-chart":"f201","fa-lastfm":"f202","fa-lastfm-square":"f203","fa-toggle-off":"f204","fa-toggle-on":"f205","fa-bicycle":"f206","fa-bus":"f207","fa-ioxhost":"f208","fa-angellist":"f209","fa-cc":"f20a","fa-ils":"f20b","fa-meanpath":"f20c","fa-buysellads":"f20d","fa-connectdevelop":"f20e","fa-dashcube":"f210","fa-forumbee":"f211","fa-leanpub":"f212","fa-sellsy":"f213","fa-shirtsinbulk":"f214","fa-simplybuilt":"f215","fa-skyatlas":"f216","fa-cart-plus":"f217","fa-cart-arrow-down":"f218","fa-diamond":"f219","fa-ship":"f21a","fa-user-secret":"f21b","fa-motorcycle":"f21c","fa-street-view":"f21d","fa-heartbeat":"f21e","fa-venus":"f221","fa-mars":"f222","fa-mercury":"f223","fa-transgender":"f224","fa-transgender-alt":"f225","fa-venus-double":"f226","fa-mars-double":"f227","fa-venus-mars":"f228","fa-mars-stroke":"f229","fa-mars-stroke-v":"f22a","fa-mars-stroke-h":"f22b","fa-neuter":"f22c","fa-genderless":"f22d","fa-facebook-official":"f230","fa-pinterest-p":"f231","fa-whatsapp":"f232","fa-server":"f233","fa-user-plus":"f234","fa-user-times":"f235","fa-bed":"f236","fa-viacoin":"f237","fa-train":"f238","fa-subway":"f239","fa-medium":"f23a","fa-y-combinator":"f23b","fa-optin-monster":"f23c","fa-opencart":"f23d","fa-expeditedssl":"f23e","fa-battery-full":"f240","fa-battery-three-quarters":"f241","fa-battery-half":"f242","fa-battery-quarter":"f243","fa-battery-empty":"f244","fa-mouse-pointer":"f245","fa-i-cursor":"f246","fa-object-group":"f247","fa-object-ungroup":"f248","fa-sticky-note":"f249","fa-sticky-note-o":"f24a","fa-cc-jcb":"f24b","fa-cc-diners-club":"f24c","fa-clone":"f24d","fa-balance-scale":"f24e","fa-hourglass-o":"f250","fa-hourglass-start":"f251","fa-hourglass-half":"f252","fa-hourglass-end":"f253","fa-hourglass":"f254","fa-hand-rock-o":"f255","fa-hand-paper-o":"f256","fa-hand-scissors-o":"f257","fa-hand-lizard-o":"f258","fa-hand-spock-o":"f259","fa-hand-pointer-o":"f25a","fa-hand-peace-o":"f25b","fa-trademark":"f25c","fa-registered":"f25d","fa-creative-commons":"f25e","fa-gg":"f260","fa-gg-circle":"f261","fa-tripadvisor":"f262","fa-odnoklassniki":"f263","fa-odnoklassniki-square":"f264","fa-get-pocket":"f265","fa-wikipedia-w":"f266","fa-safari":"f267","fa-chrome":"f268","fa-firefox":"f269","fa-opera":"f26a","fa-internet-explorer":"f26b","fa-television":"f26c","fa-contao":"f26d","fa-500px":"f26e","fa-amazon":"f270","fa-calendar-plus-o":"f271","fa-calendar-minus-o":"f272","fa-calendar-times-o":"f273","fa-calendar-check-o":"f274","fa-industry":"f275","fa-map-pin":"f276","fa-map-signs":"f277","fa-map-o":"f278","fa-map":"f279","fa-commenting":"f27a","fa-commenting-o":"f27b","fa-houzz":"f27c","fa-vimeo":"f27d","fa-black-tie":"f27e","fa-fonticons":"f280","fa-reddit-alien":"f281","fa-edge":"f282","fa-credit-card-alt":"f283","fa-codiepie":"f284","fa-modx":"f285","fa-fort-awesome":"f286","fa-usb":"f287","fa-product-hunt":"f288","fa-mixcloud":"f289","fa-scribd":"f28a","fa-pause-circle":"f28b","fa-pause-circle-o":"f28c","fa-stop-circle":"f28d","fa-stop-circle-o":"f28e","fa-shopping-bag":"f290","fa-shopping-basket":"f291","fa-hashtag":"f292","fa-bluetooth":"f293","fa-bluetooth-b":"f294","fa-percent":"f295","fa-gitlab":"f296","fa-wpbeginner":"f297","fa-wpforms":"f298","fa-envira":"f299","fa-universal-access":"f29a","fa-wheelchair-alt":"f29b","fa-question-circle-o":"f29c","fa-blind":"f29d","fa-audio-description":"f29e","fa-volume-control-phone":"f2a0","fa-braille":"f2a1","fa-assistive-listening-systems":"f2a2","fa-american-sign-language-interpreting":"f2a3","fa-deaf":"f2a4","fa-glide":"f2a5","fa-glide-g":"f2a6","fa-sign-language":"f2a7","fa-low-vision":"f2a8","fa-viadeo":"f2a9","fa-viadeo-square":"f2aa","fa-snapchat":"f2ab","fa-snapchat-ghost":"f2ac","fa-snapchat-square":"f2ad","fa-pied-piper":"f2ae","fa-first-order":"f2b0","fa-yoast":"f2b1","fa-themeisle":"f2b2","fa-google-plus-official":"f2b3","fa-font-awesome":"f2b4"};

var totalLine = 0;
var moveElementType = 'old';
var old_template = '';
var interval = '';
var ftypeCreated = '';
var elementsToBeUpdated = [];
var element_history = 'elements';
var dragged_element = '';
var initialize = 0;
var paymentElementRestrict = false;
var captchaElementRestrict = false;

var typingTimer;                //timer identifier
var doneTypingInterval = 800;
var hasGlobalError = false;

var Ui = {
    init: function(){
        var request = window.location.href.replace(/#e/,'').split("/");
        gd.object=request[4];
        gd.element=request[5];
        Ui.scrollTo(gd.element);
        Ui.buildForm();

        var changeState = function() {
        	window.initialize += 1;
        	Ui.update();
        	if(window.initialize <= 1) {
        		Ui.scrollTo(gd.element);
        	}
        };

        if (window.onpopstate != undefined) {
		    window.onpopstate = changeState;
		} else {
		    window.onhashchange = changeState;
		}
    },
    scrollTo: function(element, timeout) {
    	timeout = timeout || 1000;
    	if(element) {
    		setTimeout(function() {
    			var parentDiv = $(".flex-container").find(".flex-col").get(0);
    			$parentDiv = $(parentDiv);
    			var $innerListItem = $(document).find("#f"+element);
    			if($innerListItem.length) {
					$parentDiv.scrollTop($parentDiv.scrollTop() + ($innerListItem.offset().top - 100));
    			}
    		}, timeout);
    	}
    },
    update:function(){
    	var request = window.location.href.replace(/#e/,'').split("/");
    	gd.object=request[4];
        gd.element=request[5];
        if(gd.element=='new' || gd.element=='source'){
        	if(gd.element.length > 3) {
        		$('#browsetheme').hide();
        	}
            $('#snew').show();
        } else if(gd.element == 'delivery') {
        	$(".formElementContainer").hide();
        	$("#displayHeaderContainer").hide();
        	$(".fc-logo").hide();
        	$(document).find('.fc-logo-add').hide();
        	$("#delivery").hide();
        	$("#deliveryActive").show();
        	$("#delivery button").addClass("active");
        	$("#deliveryContainer").show();
        } else {
            $('#snew').hide();

            $(".formElementContainer").show();
        	$("#displayHeaderContainer").show();
        	$("#delivery").show();
        	$("#deliveryActive").hide();
        	$(".fc-logo").show();
        	if($(".fc-logo").is(':empty')) {
        		$(document).find('.fc-logo-add').show();
        	} else {
        		$(document).find('.fc-logo-add').hide();
        	}
        	$("#delivery button").removeClass("active");
        	$("#deliveryContainer").hide();

            $("#sidebar li a").removeClass("active");
            $('a[href="#e'+request[5]+'"]').addClass("active");

            if(gd.element.length > 0){
            	if(gd.element == 'elementsEdit') {
            		Ui.populateElementsEdit();
            	}
            	if(gd.element.length == 16) {
            		$("#f"+gd.element).mousedown();
            	}
                $(".sel").hide();
                $('#s'+gd.element).show();
                $(".el").removeClass("selected");
                $('#f'+gd.element).addClass("selected");
            } else {
                $(".sel").hide();
                if(gd.fd.type=='ENDPOINT'){
                    $('#sendpoint').show();
                } else {
                    $('#selements').show();
                    $('#element').addClass('active');
                }
            }

            window.element_history = gd.element;
        }
    },
    populateElementsEdit: function() {
    	$(".elementsContainer").html("");
    	var pages = $(".ellist");
    	var elements = [];
    	$.each(pages, function(i, page) {
    		var lines = $(page).find('.formline');
    		$.each(lines, function(i, line) {
    			var els = $(line).find('.el');
    			$.each(els, function(i, el) {
    				var elm = {
    					id: $(el).attr('id').slice(1),
    					label: $(el).find("[prop=inputLabel]").val()
    				};

    				elements.push(elm);
    			});
    		});
    	});

    	var ul = $("<ul></ul>");

    	$.each(elements, function(i, element) {

    		var label = element.label ? element.label:'Field Label';
    		ul.append('<li><a href="javascript:;" class="button small round toggle" data-element-id="'+element.id+'">' + label +'</a></li>');

    	});

		ul.append('<li><a href="javascript:;" class="button small round toggle" data-element-id="confirmation">Success page</a></li>');

    	$(".elementsContainer").append(ul);

    	ul.on("click", "a", function() {
    		$("#f" + $(this).data('element-id')).trigger('mousedown');
    	});
    },
    buildForm : function() {
        var req = {};
        req.form_id=gd.object;
        Utils.reqdata('getForm',req,Ui.outputForm);
    },
    outputForm: function (json){
    	if(json.error && gd.element != 'new' && gd.element.substring(0, 3) != 'new' && gd.element != 'source') {
    		Ui.alert('error', 'This form does not exists');
    		$(".fcc").hide();
    		$("#sidebar").hide();
    		return;
    	}
        gd.fd=json;
        gd.elems=json.elements;
        Ui.makeform();
        Ui.actionBinding();
        Ui.update();
    },
    formHasPaidElements: function() {
    	var formContainer = $(document).find('.formElementContainer');
    	var elements = formContainer.find('.el');
    	var found_elements = [];
    	var has_conditional_logic = false;
    	elements.each(function() {
    		var gc = $(this).find('.gc[et]');
    		var element_name = gc.data('default-value');
    		var type = gc.attr('type');
    		var id = $(this).attr('id').slice(1);
    		var side = $(document).find("#s"+id);
    		if(window.paidElements.indexOf(type) != -1) {
    			found_elements.push(element_name);
    		}
    		if(side.find('[prop=enableLogic]').is(":checked") && (side.find('[prop=logicAction]').val() || side.find('[prop=logicField]').val() || side.find('[prop=logicCondition]').val() || side.find('[prop=logicValue]').val()) && has_conditional_logic == false) {
    			has_conditional_logic = true;
    			found_elements.push("Conditional Display");
    		}
    	});

		var settings = $(document).find("#ssettings");

		if(settings.find('[prop=notifySubmitter]').is(":checked")) {
			found_elements.push("Notify Respondents");
		}

		if(settings.find('[prop=isExternalData]').is(":checked")) {
			found_elements.push("Pass external data");
		}

		if(settings.find('[prop=usePassword]').is(":checked")) {
			found_elements.push("Password");
		}

    	return found_elements;
    },
    makeform:function(){
        var el=gd.elems;
        var line=0;
        if(gd.fd.type=='ENDPOINT'){
            gd.element='settings';
            $('.button-header').addClass('header-endpoint').html('<ul class="inline-list button-list flex-row"><li><a href="#eendpoint" id="formlet" class="button small round toggle active">Settings</a></li></ul>');
            $('.sidebar-position').append('<div class="sidebar-settings scroll"></div>');
            $('.sidebar-position .sidebar-settings').append('<div id="sendpoint" class="sel">'+tmpl.side['endpoint']+'</div>');
            if(gd.fd.active=='1') {
                $('.form-state-container').append('<div id="formState" class="form-state button small form-active">UnPublish</div>');
            } else {
                $('.form-state-container').append('<div id="formState" class="form-state button small">Publish</div>');
            }
            $('.formElementContainer').append(tmpl.endp.form);
            Ui.populatesidebar('endpoint');
            $('.fcc #delivery').hide();
        } else {
        if (typeof(gd.fd) !== 'undefined') { // needs to be checked

        	if(gd.fd.usePassword == "1") {
        		var passwordContainer = $(".passwordContainer");
        		passwordContainer.show();
        		passwordContainer.find('textarea[prop="passwordLabel"]').val(gd.fd.passwordLabel);
        		passwordContainer.find('[data-prop="passwordButtonLabel"]').html(gd.fd.passwordButtonLabel);
        	}

        	//display logo if have
        	if(gd.fd.logo) {
        		if(typeof gd.fd.logo == 'object') {
        			var logotpl = $('<div class="logoAction"><a href="javascript:;"><i class="fa fa-times-circle"></i> Remove</a></div><img src="https://s3.amazonaws.com/'+gd.fd.logo.bucket+'/'+gd.fd.logo.key+'?v='+Utils.insertid()+'" />');
        		} else {
        			var logotpl = $('<div class="logoAction"><a href="javascript:;"><i class="fa fa-times-circle"></i> Remove</a></div><img src="/logo/'+gd.fd.logo+'?v='+Utils.insertid()+'" />');
        		}
        		$(".fc-logo").append(logotpl);
        		$('.fc-logo-add').hide();
        	}

            $('.button-header').html('<div id="sideElement">\
            		<header>Fields</header>\
            		<ul class="inline-list button-list flex-row">\
            			<li><a href="#eelements" id="element" class="button small round toggle"><i class="fa fa-plus"></i> Add</a></li>\
            			<li><a href="#eelementsEdit" id="elementEdit" class="button small round toggle"><i class="fas fa-pencil-alt"></i> Edit</a></li>\
            		</ul>\
            	</div>\
            	<div id="sideForm">\
            		<header>The form</header>\
            		<ul class="inline-list button-list flex-row">\
            			<li style="width:55%"><a href="#esettings" id="formlet" class="button small round toggle active"><i class="fa fa-cog"></i> Settings</a></li>\
            			<li style="width:45%"><a href="#etheme" id="theme" class="button small round toggle"><i class="fa fa-pie-chart"></i> Color</a></li>\
            		</ul>\
            	</div>\
            </ul>');
            $('.sidebar-position').append('<div class="sidebar-settings scroll"></div>');
            if(gd.fd.active=='1') {
                $('.form-state-container').append('<div id="formState" class="form-state button small form-active">Unpublish</div>');
            } else {
                $('.form-state-container').append('<div id="formState" class="form-state button small">Publish</div>');
            }

            $('.sidebar-position .sidebar-settings').append('\
            	<div id="selements" class="sel">'+tmpl.side['elements']+'</div>\
            	<div id="selementsEdit" class="sel">'+tmpl.side['elementsEdit']+'</div>\
            	<div id="ssettings" class="sel">'+tmpl.side['settings']+'</div>\
            	<div id="sconfirmation" class="sel">'+tmpl.side['confirmation']+'</div>\
            	<div id="stheme" class="sel">'+tmpl.side['theme']+'</div>\
            ');

            if(!el) { //some forms has element = null
                el = [];
            }
            //sort the elements first by order property
            el.sort(function(a, b) {
                if (a.order == b.order) { return 0; }
                if (a.order > b.order) { return 1; } else { return -1; }
            });

            //get all the pages of this form
            if(gd.fd.pages && gd.fd.pages.constructor === Array && gd.fd.pages.length) {
                var pages = [];
                $.each(gd.fd.pages, function(i, page) {
                    if($.isEmptyObject(page._id)) {
                        //do nothing
                    } else {
                        var exists = false;
                        if(pages.length) {
                            exists = pages
                                .map(function(e){return e == page._id})
                                .reduce(function(pre, cur) {return pre || cur});
                        }
                        if(!exists) {
                            pages.push(page._id);
                        }
                    }
                });

                if(pages.length == 0) { pages = ["page1"]; }
            } else {
                var pages = ["page1"];
            }
			pages.push("success");
            var page_number = 0;
            var line_index = 0;
            var page_container = $(".formElementContainer");
            $.each(pages, function(i, page_id) { //we will group them by group
				if(page_id == 'success') {
					page_container = $(".formElementContainerSuccess");
					//console.log(page_container);
					page_container.html('');
					var page = $('<div id="formelements_success" data-page="'+page_id+'" class="submit_confirmation ellist"></div>');
				} else {
					var page = $('<div id="formelements_'+page_number+'" data-page="'+page_id+'" class="ellist"></div>');
				}

                page_container.append(page);

                //filter elements by page property.. will return an array of elements
                var page_elements = $.grep(el, function(e) {
                    return e.page == page_id || $.isEmptyObject(e.page);
                });

				if(page_id == 'success') {
					if(page_elements.length == 0) {
						var lText = '<center><h3>'+gd.fd.submitSuccessMessage+'</h3></center>';
						if(!gd.fd.submitSuccessMessage) {
							lText = '<center><h3>thank you for your submission</h3></center>';
						}

						var newid=Utils.insertid();

						var newEl = {
							'labelText': lText,
							'name':newid,
							'order':(window.totalLine+1)*1000,
							'page':'success',
							'size':12,
							'type':"LABEL",
							'_id':newid
						};

						var req = {};
				        req.form_id=gd.object;
				        req.el_id=newEl._id;
				        req.order=newEl.order;
				        req.size=newEl.size;
				        req.type=newEl.type;
				        req.page=newEl.page;
				        req.inputLabel='';
				        req.labelText=newEl.labelText;
				        req.method="POST";
				        Utils.reqdata('editFormElement',req);

						page_elements.push(newEl);
					}
				}

                //construction of the editor, will build a grid
                var construction = [];
                var row = [];
                for(var i = 0; i < page_elements.length; i++) {
                    var sizeCount = 0;
                    row.push(page_elements[i]);
                    for(var j = 0; j < row.length; j++) {
                        sizeCount += row[j].size;
                    }
                    if(sizeCount >= 12 || i == page_elements.length-1) { //page has 12 columns grid
                        construction.push(row);
                        row = [];
                    }
                }
                //display the construction of the page

                $.each(construction, function(index, cols) {
                    line_index++;
                    window.totalLine++;
                    var lineid="line"+line_index;

                    var line = $('<div class="formline" id="'+lineid+'"></div>');

                    //loop each column from construction
                    $.each(cols, function(key, col) {
                        var tpl = tmpl.frm[col.type];

                        if (typeof tpl == 'undefined') {
                            var tpl='no template loaded for ' + col.type;
                        }

                        var template = $('<div id="f'+col._id+'" class="el">'+tpl+'</div>');
                        var fieldset = template.find('fieldset');
                        var input = fieldset.find('input');

                        if(col.customValidationType) {
                        	template.attr('validation-type', col.customValidationType);
                        }

                        var label_textarea = template.find('textarea[prop="inputLabel"]');
                        if(col.type=='SECTION') {
                            label_textarea = template.find('textarea[prop="labelText"]');
                        }
                        var label_required = template.find('button[prop="required"]');
                        var instructionText = template.find('[prop="instructionText"]');

                        label_textarea.html(col.inputLabel);

                        if(col.type=='SECTION') {
                            label_textarea.html(col.labelText);
                        }

                        if(col.required) {
                            label_required.addClass("active");
                        }

                        if(col.instructionText) {
                            //instructionText.html(col.instructionText);
                        }

                        if(col.iconEnabled) {
                            fieldset.addClass('inline-edit-container');
                            input.addClass('inline-edit input-text');

                            fieldset.append('<i class="fa '+col.iconName+'"></i>');
                        }

                        //for checkbox and option boxes
                        if(col.type=='CHECKBOX' || col.type=='RADIO' || col.type=='SWITCH' || col.type=='PRODUCTS' || col.type=='INPUTTABLE') {
                            var option_container = template.find('[class="option_container"]');
                            var lists = col.optionsList;
                            if(col.type=='PRODUCTS') {
                            	lists = col.productsList;
                            	option_container.find('table').remove();
                            	var qty_lists = col.optionsList;
                            	var unit = 'currency';
                            	if(col.unit) {unit=col.unit;}
                            	template.find('[et=products]').attr('data-unit', unit);
                            }
                            if(col.type=='RADIO' || col.type=='CHECKBOX' || col.type=='SWITCH') {
                            	option_container.find('.foption').remove();
                            }
                            if(col.type=='INPUTTABLE') {
                            	lists = col.questionList;
                            	var ans_lists = col.answerList;
                            	option_container.find('tr.ans').remove();
                            	option_container.find('tr.foption').remove();
                            	var ans_template = $('<tr class="ans">\
                  					<td></td>\
                                </tr>');
                                var radios = '';

								var inputtype='radio';
								if(col.inputtype) {
									inputtype=col.inputtype;
								}

                                if(ans_lists && ans_lists.length) {
                                	$.each(ans_lists, function(i, ans) {
	                            		ans_template.append($('<td class="gray"><label class="option"><input type="text" class="inline-edit input-text table-input" value="'+ans.label+'"></label><div class="del"><i class="icon-trash"></i></div></td>'));
	                            		radios+='<td class="ans"><input type="'+inputtype+'" disabled class="radio_ans"></td>'
	                            	});
                                }
                            	ans_template.insertBefore(option_container.find('.new'));
                            }
                            if(lists != undefined) {
                            	if(col.type=='PRODUCTS') {
                            		var select = template.find('.select select.text');
                            	}
                                $.each(lists, function(i, option) {
                                	if(option) {
										var isDefault = option.default=='1' || option.default==true || option.default==1 ? true:false;
                                		var amount = option.value ? option.value:0;
	                                	var input = $('<input type="text" class="inline-edit input-text" />').val(option.label);
	                                	var input_product = $('<input type="text" class="inline-edit input-text product-input" />').val(option.label);
	                                	var input_table = $('<input type="text" class="inline-edit input-text table-input" />').val(option.label);
	                                	var input_switch = $('<input type="text" class="inline-edit input-text" style="margin-left:30px;width: 94%;" />').val(option.label);
	                                    if(col.type=='CHECKBOX') {
											var attr = '';
											if(isDefault) {
												attr = 'checked';
											}
	                                        var option_template = $('<div class="foption">\
	                                            <fieldset class="option-container">\
	                                                <label class="option">\
	                                                    <input type="checkbox" value="1" '+attr+' />\
	                                                    <i></i>\
	                                                </label>\
	                                                <button class="inline-edit red">\
	                                                    <i class="fm-icon-close-thick"></i>\
	                                                </button>\
	                                            </fieldset>\
	                                        </div>');

	                                        option_template.find('.option').append(input);
	                                    } else if(col.type=='PRODUCTS') {
	                              			var option_template = $('<tr class="foption option-container trow">\
	                              					<td>\
		                                                <label class="option">\
		                                                    <input type="checkbox" />\
		                                                    <i></i>\
		                                                </label>\
		                                                <span class="price">(<span class="symbol">$</span><span class="amount">'+amount+'</span> <span class="currency">'+gd.fd.currency+'</span>)</span>\
	                                                </td>\
	                                                <td class="tamt">\
	                                                	<span class="tcell"><select><option class="default_value_amt">Quantity</option></select></span>\
	                                                </td>\
	                                                <td class="tbtn">\
		                                                <button class="inline-edit red">\
		                                                    <i class="fm-icon-close-thick"></i>\
		                                                </button>\
	                                                </td>\
	                                            </tr>');

	                                        var option_template1 = $('<option>'+option.label+' (<span class="symbol">$</span>'+amount+' <span class="currency">'+gd.fd.currency+'</span>)</option>');
	                                        select.append(option_template1);
	                                        option_template.find('.option').append(input_product);

	                                    } else if(col.type=='INPUTTABLE') {
	                                    	var option_template = $('<tr class="foption option-container trow">\
	                              					<td class="gray">\
		                                                <label class="option">\
		                                                </label>\
		                                                <div class="del"><i class="icon-trash"></i></div>\
	                                                </td>\
	                                            </tr>');

	                                        option_template.find('.option').append(input_table);
	                                        option_template.append($(radios));
	                                    } else if(col.type=='SWITCH') {
											var attr = '';
											if(isDefault) {
												attr = 'checked';
											}
	                                    	var onLabel = 'ON';
	                                    	var offLabel = 'OFF';
	                                    	if(col.onLabel) {
	                                    		onLabel = col.onLabel;
	                                    	}
	                                    	if(col.offLabel) {
	                                    		offLabel = col.offLabel;
	                                    	}
	                                        var option_template = $('<div class="foption">\
	                                            <fieldset class="option-container">\
	                                                <label class="option switch">\
	                                                    <input type="checkbox" value="1" '+attr+' />\
	                                                    <span class="switch-container"><span class="switch-status on">'+onLabel+'</span><span class="switch-status off">'+offLabel+'</span><i></i></span>\
	                                                </label>\
	                                                <button class="inline-edit red">\
	                                                    <i class="fm-icon-close-thick"></i>\
	                                                </button>\
	                                            </fieldset>\
	                                        </div>');

	                                        option_template.find('.option').append(input_switch);

	                                        var sw_container = option_container.find('div.new').find('.switch-container');
	                                        sw_container.find('.on').html(onLabel);
	                                        sw_container.find('.off').html(offLabel);
	                                    } else {
											var attr = '';
											if(isDefault) {
												attr = 'checked';
											}
	                                        var option_template = $('<div class="foption">\
	                                            <fieldset class="option-container">\
	                                                <label class="option">\
														<input type="radio" name="radio_'+col._id+'" value="1" '+attr+'>\
	                                                    <i></i>\
	                                                </label>\
	                                                <button class="inline-edit red">\
	                                                    <i class="fm-icon-close-thick"></i>\
	                                                </button>\
	                                            </fieldset>\
	                                        </div>');

	                                        option_template.find('.option').append(input);
	                                    }
	                                    //option_container.append(option_template);

	                                    option_template.insertBefore(option_container.find('.new'));
                                	}
                                });

								if(col.type=='PRODUCTS') {
									if(col.useSelect) {
										template.find('.selectContainer').show();
										option_container.hide();
									} else {
										template.find('.selectContainer').hide();
										option_container.show();
									}

                            		if(col.enableAmount) {
                            			template.find('.tamt').show();
                            			template.find('.selectContainer').addClass('hasQty');
                            		} else {
                            			template.find('.tamt').hide();
                            			template.find('.selectContainer').removeClass('hasQty');
                            		}

                            		template.find('.default_value_amt').html(col.enableAmountLabel);

                            		if(qty_lists != undefined) {
		                            	var qty_option_container = template.find('.tcell select');
		                            	var qty_option_container2 = template.find('.selectContainer .qty select');
		                            	$.each(qty_lists, function(qi, qoption) {
		                                	if(qoption) {
		                                		var qty_option_template = $('<option value="'+qoption.value+'">'+qoption.label+'</option>');
		                                    	qty_option_container.append(qty_option_template);
		                                    	qty_option_container2.append(qty_option_template);
		                                	}
		                                });
		                            }
                            	}
                            }

                            if(col.otherOption) {
                            	option_container.find('.other').show();
                            }
                            if(col.otherOptionLabel) {
                            	option_container.find('.otherText').html(col.otherOptionLabel);
                            }
                        } else if(col.type=='SELECT') {
                            var option_container = template.find('fieldset select');
                            if(col.optionsList != undefined) {
                                $.each(col.optionsList, function(i, option) {
									if(option) {
										var isDefault = option.default=='1' || option.default==true || option.default==1 ? true:false;
										var attr = '';
										if(isDefault) {
											attr = 'selected';
										}
										var option_template = $('<option value="'+option.value+'" '+attr+'>'+option.label+'</option>');
	                                    option_container.append(option_template);
									}
                                });
                            }
                        } else if(col.type=='RANGE') {
                            var output_container = fieldset.find('.output-container');
                            if(col.rangeMax) {
                                output_container.find('span').html(col.rangeMax);
                            }
                        } else if(col.type=='NAME') {
                        	if(col.middleName && !col.nameTitle) {
                        		template.find('.firstname').removeClass('g6').addClass('g5');
                        		template.find('.lastname').removeClass('g6').addClass('g5');
                        		template.find('.titlename').hide();
                        		template.find('.middlename').show();
                        	} else if(!col.middleName && col.nameTitle) {
                        		template.find('.firstname').removeClass('g6').addClass('g5');
                        		template.find('.lastname').removeClass('g6').addClass('g5');
                        		template.find('.titlename').show();
                        		template.find('.middlename').hide();
                        	} else if(col.middleName && col.nameTitle) {
                        		template.find('.firstname').removeClass('g6').addClass('g4');
                        		template.find('.lastname').removeClass('g6').addClass('g4');
                        		template.find('.titlename').show();
                        		template.find('.middlename').show();
                        	}
                        } else if(col.type=='US_ADDRESS') {
                        	if(col.country) {
                        		template.find('.countrySelect').show();
                        	} else {
                        		template.find('.countrySelect').hide();
                        	}
                        	if(col.format=='OTHER') {
                        		template.find('.state_select').hide();
                        		template.find('.state_text').show();
                        	}
                        } else if(col.type=='PICTURE') {
                        	template.addClass('picture');
                        	if(col.picture) {
                        		template.find('.no_image').hide();
                        		var img_container = template.find('.image_container');
                        		img_container.show();
                        		var img = img_container.find('img');

                        		img.attr('src', '/images/' + col.picture);
                        		if(col.width) {
                        			img.css('width', col.width);
                        		}

                        		if(col.height) {
                        			img.css('height', col.height);
                        		}

                        	}
                        } else if(col.type=='FILE') {
                        	var blabel = col.fileButtonLabel;
                        	if(!blabel) {blabel='Choose File...';}
                        	template.find('.editable').html(blabel);
                        } else if(col.type=='DATE' || col.type=='DATETIME' || col.type=='TIME') {
		                	var $el = template.find('input[prop=placeholderText]');
		                	if(col.placeholderText) {
		                		$el.val(col.placeholderText);
		                	}

		                	var interval = col.interval ? col.interval:1;
		                	var hour12 = col.use12Notation ? col.use12Notation:false;
		                	var minDate = null;
		                	var maxDate = null;
		                	var format = window.user_date_format;

							if(col.dateFormat) {
								format = Utils.getDateFormat(col.dateFormat);
							}

		                	if(col.type=='DATETIME') {
		                		var noCalendar = false;
		                		var enableTime = true;
		                		var bdate = col.beginDate;
		                		var edate = col.endDate;
		                		if(bdate) {
		                			minDate = new Date(window.strtotime(bdate) * 1000);
		                		}
		                		if(edate) {
		                			maxDate = new Date(strtotime(edate) * 1000);
		                		}
		                	} else if(col.type=='DATE') {
		                		var noCalendar = false;
		                		var enableTime = false;
		                		var bdate = col.beginDate;
		                		var edate = col.endDate;
		                		if(bdate) {
		                			minDate = new Date(window.strtotime(bdate) * 1000);
		                		}
		                		if(edate) {
		                			maxDate = new Date(strtotime(edate) * 1000);
		                		}
		                	} else {
		                		var enableTime = true;
		                		var noCalendar = true;
		                	}

		                	if(col.type=='DATETIME' || col.type=='TIME') {
		                		if(hour12 == true) {
									var time_24hr = false;
									if(noCalendar) {
										var format = 'h:i K';
									} else {
										var format = format + ' h:i K';
									}
			                	} else {
			                		var time_24hr = true;
			                		if(noCalendar) {
			                			var format = 'H:i';
			                		} else {
			                			var format = format + ' H:i';
			                		}
			                	}
		                	}
		                	var defaultDate = null;
		                	if((col.type=='DATE' || col.type=='DATETIME') && col.defaultValue) {
		                		defaultDate = new Date(window.strtotime(col.defaultValue) * 1000);
		                	}

							var disabledDays = [];
							if(col.type=='DATETIME' || col.type=='DATE') {
								if(col.dM) { disabledDays.push(1); }
								if(col.dT) { disabledDays.push(2); }
								if(col.dW) { disabledDays.push(3); }
								if(col.dTH) { disabledDays.push(4); }
								if(col.dF) { disabledDays.push(5); }
								if(col.dSat) { disabledDays.push(6); }
								if(col.dSun) { disabledDays.push(0); }
							}

							setTimeout(function() {
		                		Ui.makeElementDateOrTime($el, {
			                		dateFormat:format,
									noCalendar:noCalendar,
									enableTime: enableTime,
									minuteIncrement:interval,
									time_24hr:time_24hr,
									minDate:minDate,
									maxDate:maxDate,
									allowInput: true,
									defaultDate: defaultDate,
									appendTo:$el.closest('fieldset').get(0),
									onClose: function(selectedDates, dateStr, instance) {
										$(instance.input).blur();
									},
									"disable": [
										function(date) {
											return disabledDays && disabledDays.indexOf(date.getDay().toString()) !== -1;
										}
									],
								    "locale": {
								        "firstDayOfWeek": 1 // start week on Monday
								    }
			                	});
		                	}, 500);
                        } else if(col.type=='TEXTAREA') {
                        	var container = template.find('.rcContainer');
                        	if(col.textMaxLength) {
                        		container.show();
                        		container.css('display','block');
                        		container.find('.maxChar').html(col.textMaxLength);
                        	} else {
                        		container.hide();
                        	}

                        	var height = 96;
						    if(col.textAreaHeight) {
						        height = col.textAreaHeight;
						    }
						    var $el = template.find('textarea[prop=placeholderText]');
						    $el.css('height', height + 'px');
                        } else if(col.type=='SIGNATURE') {
                        	var container = template.find('.canvasC');
                        	var canvas = container.find('canvas');
                        	if(col.width) {
                        		container.css('width', col.width+'px');
                        		canvas.css('width', col.width+'px');
                        	}
                        	if(col.height) {
                        		container.css('height', col.height+'px');
                        		canvas.css('height', col.height+'px');
                        	}
                        	if(col.clearLabel) {
                        		container.find('.actions .clear').html(col.clearLabel);
                        	}
                        } else if(col.type=='CALCULATION') {
							if(col.hidden) {
								template.find('.nohide').hide();
								template.find('.hide').show();
							} else {
								template.find('.nohide').show();
								template.find('.hide').hide();
							}
						} else if(col.type=='STRIPE') {
							if(col.captureCard) {
								template.find('.notcapture').hide();
								template.find('.capture').show();
							} else {
								template.find('.notcapture').show();
								template.find('.capture').hide();
							}

							if(col.captureLabel) {
								template.find('.capture').html(col.captureLabel);
							}
						} else if(col.type=='SECTION') {
							if(col.textSize) {
								template.find('textarea.ed').addClass('h'+col.textSize);
							}
						}

                        line.append(template);

                        Ui.makeside(col._id, col.type);
                        Ui.elpopulate(col);
                    });
                    page.append(line);

                    /*resize the input for product elements*/
                    $("input.product-input").each(function() {
                    	$(this).attr({width: 'auto', size: $(this).val().length});
                    });

					var elm = document.getElementById(lineid);
					Ui.makeElementSortable(elm);

                });

                line_index++;
                window.totalLine++;

				if(page_id == 'success') {
					Ui.addformline('line'+line_index, 'success');
				} else {
					Ui.addformline('line'+line_index, page_number);
				}

				if(page_id!='success') {
					//insert footer
	                //template footer
	                var footer = $('<div class="footer">\
	                    <div class="gr flush" style="margin:0">\
	                        <div class="gc g3 pad-double right">\
	                            <span contenteditable class="button button-blue nextButtonText editable" type="button" style="float: right;">Next</span>\
	                        </div>\
	                        <div class="gc g3 pad-double left" style="float:left">\
	                            <span contenteditable data-trigger="s_previousButtonText" data-prop="previousButtonText" class="button button-blue previousButtonText editable" type="button" style="float: left;">Previous</span>\
	                        </div>\
	                        <div class="gc g6 pad-double align-center left" style="width:100%;">\
	                            <span class="pagination" style="display:inline-block; margin-top:8px;"><span class="pagination_page footerPaginationPageText">Page</span> 1 <span class="pagination_of footerPaginationOfText">of</span> 2</span>\
	                        </div>\
	                    </div>\
	                </div>');

	                var previous_page = pages[page_number-1];
	                var next_page = pages[page_number+1];

	                if(previous_page == undefined) {
	                    footer.find(".left > .button").hide();
	                } else {
	                    footer.find(".left > .button").show();
	                }

	                if(next_page == undefined || next_page=='success') {
	                    footer
	                    .find(".right > .button")
	                    .removeClass('nextButtonText')
	                    .addClass('submitButtonText')
	                    .attr('data-trigger', 's_submitButtonText')
	                    .attr('data-prop', 'submitButtonText')
	                    .html('Submit');
	                } else {
	                    footer
	                    .find(".right > .button")
	                    .removeClass('submitButtonText')
	                    .addClass('nextButtonText')
	                    .attr('data-trigger', 's_nextButtonText')
	                    .attr('data-prop', 'nextButtonText')
	                    .html('Next');
	                }

	                if(gd.fd.submitButtonText) {
	                    footer.find('.button.submitButtonText').html(gd.fd.submitButtonText);
	                }
	                //TODO(elias): delete the fields in the DB "previousbuttontext" & "nextbuttontext" and migrate the data that was in there to pages
	                if(gd.fd.previousButtonText) {
	                    footer.find('.button.previousButtonText').html(gd.fd.previousButtonText);
	                }
	                if(gd.fd.nextButtonText) {
	                    footer.find('.button.nextButtonText').html(gd.fd.nextButtonText);
	                }

	                if(pages.length > 2) {
	                    footer.find(".pagination").html('<span class="pagination_page footerPaginationPageText">Page</span> ' + (page_number+1) + ' <span class="pagination_of footerPaginationOfText">of</span> ' + (pages.length-1));
	                } else {
	                    footer.find(".pagination").hide();
	                }

	                page.append(footer);

	                if(gd.fd.pages && gd.fd.pages.constructor === Array && gd.fd.pages.length) {
		                $.each(gd.fd.pages, function(i, p) {
		                	if(page_id == p._id) {
		                		if(typeof(p.nextButtonText) != 'undefined') {
		                			page.find('.button.nextButtonText').html(p.nextButtonText);
		                		}

		                		if(typeof(p.previousButtonText) != 'undefined') {
		                			page.find('.button.previousButtonText').html(p.previousButtonText);
		                		}
		                	}
		                });
		            }

		            page.find('.pagination_page').html(gd.fd.footerPaginationPageText);
		            page.find('.pagination_of').html(gd.fd.footerPaginationOfText);

	                page_number++;

	                var arrangePage = $('<div class="arrange"><i title="move page down" class="fa fa-arrow-circle-down down" aria-hidden="true"></i> <i title="move page up" class="fa fa-arrow-circle-up up" aria-hidden="true"></i></div>');
	                if(next_page == undefined || next_page == 'success') {
	                	arrangePage.find('.down').hide();
	                } else {
	                	arrangePage.find('.down').show();
	                }
	                if(previous_page == undefined) {
	                	arrangePage.find('.up').hide();
	                } else {
	                	arrangePage.find('.up').show();
	                }
	                page.append(arrangePage);
				}
            });

            //show delete page link
            if(pages.length > 1) {
                for(i = 1; i < pages.length; i++) {
					if(pages[i]!='success') {
						var ipage = $('[data-page="'+pages[i]+'"]');
	                    var deleteLink = $('<div class="delpage button small">\
	                        <i class="fa fa-trash"></i> Delete Page\
	                    </div>');

	                    ipage.append(deleteLink);
					}
                }
            }

            var confirmation_message = gd.fd.submitSuccessMessage;
            var confirmation_placeholder = 'thank you for your submission';
            var confirmation_prop = 'submitSuccessMessage';
            if(gd.fd.doRedirect) {
            	confirmation_message = gd.fd.redirectUrl;
            	confirmation_placeholder = 'http://example.com/';
            	confirmation_prop = 'redirectUrl';
				if(!confirmation_message) {
            		confirmation_message = '';
            	}
            } else {
            	if(!confirmation_message) {
            		confirmation_message = 'thank you for your submission';
            	}
            }

			var page_success_container = $(".new_form_page");
			var confirmation_container = page_success_container.find('#fconfirmation');
			confirmation_container.append('<div class="gc"><textarea class="ed autoheight" prop="'+confirmation_prop+'" placeholder="'+confirmation_placeholder+'">'+confirmation_message+'</textarea></div>');

            if(gd.fd.doRedirect) {
            	page_success_container.find('#sc_redirects').show();
            	page_success_container.find('textarea').removeClass('autoheight');
            } else {
            	page_success_container.find('#sc_redirects').hide();
				page_success_container.find('textarea:not(.text)').addClass('autoheight');
            }

            var sortable = Sortable.create(document.getElementById('sideA'),{animation: 350,ghostClass: "ghost",dataIdAttr: 'did',sort:false,group:{name: 'A', pull:'clone', put:false},onMove: function (evt) { Ui.setel(evt); }, onStart: function(evt) { Ui.showDropLines(evt); }, onEnd: function(evt) { Ui.removeDropLines(); }});
            var sortable = Sortable.create(document.getElementById('sideB'),{animation: 350,ghostClass: "ghost",dataIdAttr: 'did',sort:false,group:{name: 'A', pull:'clone', put:false},onMove: function (evt) { Ui.setel(evt); }, onStart: function(evt) { Ui.showDropLines(evt); }, onEnd: function(evt) { Ui.removeDropLines(); }});
            var sortable = Sortable.create(document.getElementById('sideC'),{animation: 350,ghostClass: "ghost",dataIdAttr: 'did',sort:false,group:{name: 'A', pull:'clone', put:false},onMove: function (evt) { Ui.setel(evt); }, onStart: function(evt) { Ui.showDropLines(evt); }, onEnd: function(evt) { Ui.removeDropLines(); }});
            var sortable = Sortable.create(document.getElementById('sideD'),{animation: 350,ghostClass: "ghost",dataIdAttr: 'did',sort:false,group:{name: 'A', pull:'clone', put:false},onMove: function (evt) { Ui.setel(evt); }, onStart: function(evt) { Ui.showDropLines(evt); }, onEnd: function(evt) { Ui.removeDropLines(); }});
            Ui.populatesidebar('theme');
            Ui.populatesidebar('settings');
            Ui.populatesidebar('confirmation');
            Ui.populatesidebar('elements');
            Ui.populatesidebar('elementsEdit');

            if(gd.fd.themeEnabled=='0' || gd.fd.themeEnabled=='' || !gd.fd.themeEnabled) {
                $("#stheme").find('fieldset').not('.customCSSFieldset').hide();
            }

			if(gd.fd.rtl == '1') {
				$(document).find("#displayHeaderContainer").attr('dir', 'rtl');
				$(document).find(".formElementContainer").attr('dir', 'rtl');
			}

			if(window.accountStatus!='FREE' && window.accountStatus!='PREVIEW') {
				$(document).find('.gopro').hide();
			}

        } else {
            $('.formElementContainer').append('something went wrong with this form , Do you have the rights to see it ?');
        }}
    },
    makeside:function(eid,etype){
        var fields =['inputLabel','validationMessage','instructionText','helpText','placeholderText','placeholderFirstText','placeholderMiddleText','placeholderLastText','placeholderAddress1Text','placeholderAddress2Text','placeholderCityText','placeholderStateText','placeholderZipText','placeholderCountryText','reqdis','defaultValue','optionsList','queryName','otherOption','otherOptionLabel','enableAmount','productsList','questionList','answerList','amountOptionsList', 'logic', 'calculation','disabledDays'];
        var stpl=tmpl.side[etype];
        if (typeof stpl == 'undefined') {var stpl='no Side template loaded for '+etype;}

        //duplicate button
        if(etype == 'STRIPE' || etype == 'PAYPAL' || etype == 'STRIPEPAYPAL' || etype == 'CAPTCHA') {
        	stpl += '<fieldset>\
	            <ul class="inline-list button-list" style="margin-left:7px">\
	                <li><button class="del_element button small red">Delete</button></li>\
	            </ul>\
	        </fieldset>';
        } else {
        	stpl += '<fieldset>\
	            <ul class="inline-list button-list" style="margin-left:7px">\
	                <li><button class="duplicate button small">Duplicate</button></li>\
	                <li><button class="del_element button small red">Delete</button></li>\
	            </ul>\
	        </fieldset>';
        }


        $('.sidebar-position .sidebar-settings').append('<div id="s'+eid+'" et="'+etype+'"class="sel" style="display:none">'+stpl+'</div>');
        for (l = 0; l < fields.length; l++) {
            $('#s'+eid+' fieldset.'+fields[l]).html(tmpl.fld[fields[l]]);
        }
    },
    redel:function(evt){
        // onadd called from sort lists
        var parent = $(evt.to).parent('.ellist');
        var page = parent.data('page');
        var page_number = parent.attr('id').slice(13); //remove formelements_ and get the number
        if($(evt.item).hasClass('disabled') && gd.fd.active=='1') {
            Ui.alert('error', 'Please use a non published form if you want to test that element or <a href="/settings/subscription/">upgrade your account</a>.');
            $(evt.item).remove();
            return false;
        } else if(window.paymentElementRestrict && ($(evt.item).hasClass('stripe') || $(evt.item).hasClass('paypal') || $(evt.item).hasClass('stripepaypal'))) {
        	Ui.alert('error', 'There can be only 1 payment element in a form please delete the other one.');
        	$(evt.item).remove();
        	return false;
        } else if(window.captchaElementRestrict && $(evt.item).hasClass('captcha')) {
        	Ui.alert('error', 'There can be only 1 captcha element in a form please delete the other one.');
        	$(evt.item).remove();
        	return false;
        } else {
        	var fieldset = $(evt.item);
        	fieldset.find('input').each(function(index, input) {
        		if($(input).attr('data-default-value')) {
        			if($(input).attr('type') == 'checkbox') {
        				$(input).attr('checked', true);
        			} else {
        				$(input).val($(input).attr('data-default-value'));
        			}
        		}
        	});

            var lineid=$(document).find('#'+evt.item.id).parent().attr("id");
            var newid=$(document).find('#'+evt.item.id).attr("id") ? $(document).find('#'+evt.item.id).attr("id").slice(1):null;
            if($('#'+evt.item.id).attr("did")){
              var type=$(document).find('#'+evt.item.id).attr("did").slice(4).toUpperCase();
            }

            var text_type = $('#'+evt.item.id).attr('data-text-type');
            var line_id = $(evt.to).attr('id');
            var line_number = line_id.slice(4);
            var order = line_number * 1000;

            if(!$(evt.to).hasClass('drop-line')) {
            	var default_value = '';
            	if(window.dragged_element) {
            		default_value = window.dragged_element.val();
            	}
                Ui.createformel(order, type, page, newid, text_type, default_value); //order,type,page,id

                setTimeout(function() {
                    Ui.balanceformline($(evt.to), page);
                    setTimeout(function() {
	                	Ui.submitUpdateElements(window.elementsToBeUpdated);
	                }, 1000);
                }, 300);
            }

            if(window.moveElementType=='new') {
                if($(evt.to).find('.el').length == 1) {
                    if($(evt.to).hasClass('drop-line')) {
                        setTimeout(function() {
                            $(evt.to).removeClass('drop-line');
                            var line_id = $(evt.to).attr('id');
                            var line_number = line_id.slice(4);
                            var order = line_number * 1000;
                            var default_value = '';
                            if(window.dragged_element) {
                            	default_value = window.dragged_element.val();
                            }
                            Ui.createformel(order, type, page, newid, text_type, default_value); //order,type,page,id

                            var container = $(evt.to).closest('.formElementContainer');
                            var page_containers = container.find('.ellist');

                            var $line_number = 1;
                            var $new_line_number = 1;
                            $.each(page_containers, function(i, $page_container) {
                                $page_container = $($page_container);
                                var $page = $page_container.data('page');
                                $.each($page_container.find('.formline'), function(i, line) {
                                    if($(line).find('.el').length > 0) {
                                        Ui.balanceformline($(line), $page, $line_number);
                                        $line_number++;
                                    }
                                });
                            });

                            setTimeout(function() {
                            	Ui.submitUpdateElements(window.elementsToBeUpdated);
                            }, 1000);

                            window.dragged_element = '';
                        }, 300);
                    } else {
                        newlineid = window.totalLine + 1;
                        Ui.addformline('line'+newlineid, page_number, 'new');
                        window.totalLine = newlineid;
                        window.moveElementType = 'old';
                    }
                }
            }
        }

        if($(evt.item).hasClass('stripe') || $(evt.item).hasClass('paypal') || $(evt.item).hasClass('stripepaypal')) {
        	Ui.determineCurrency();
        }
    },
    addNewElement: function(evt) {
        var oldlineid=evt.from.id;
        if(oldlineid=='sideA' || oldlineid=='sideB' || oldlineid=='sideC' || oldlineid=='sideD') {
            Ui.redel(evt);
        }
    },
    showDropLines:function(evt) {
    	$(document).find('.new_form_page').css('marginBottom', '1000px');
        var pages = $(".ellist");
        $.each(pages, function(i, page) {
			var pageid = $(page).attr('data-page');
            var lines = $(page).find('.formline');
            $.each(lines, function(i, line) {
                if($(line).find('.el').length) {
                    if($(line).attr('id') == evt.from.id && $(line).find('.el').length == 1) {

                    } else {
                        var template = $('<div class="formline drop-line" placeholder="Drag boxes here to add them to the form"></div>');
						if(pageid == 'success') {
							template = $('<div class="formline drop-line" placeholder="Drag display boxes here to add them to the success page"></div>');
						}
                        Ui.makeElementSortable(template.get(0));
                        template.insertBefore($(line));
                    }

                }
            });
        });
    },
    removeDropLines: function() {
    	$(document).find('.new_form_page').css('marginBottom', '400px');
        var dropLines = $('.drop-line');
        $.each(dropLines, function(i, dropLine) {
            if($(dropLine).find('.el').length == 0) {
                $(dropLine).remove();
            }
        });

        setTimeout(function() {
            //update their id
            var pages = $(".ellist");
            var $new_line_number = 1;
            $.each(pages, function(i, page) {
                var lines = $(page).find('.formline');
                $.each(lines, function(i, line) {
                    //if($(line).find('.el').length) {
                        $(line).attr('id', 'line'+$new_line_number);
                        Ui.makeElementSortable($(line).get(0));
                        $new_line_number++;
                    //}
                });
            });
        });

        if(window.interval) {
            clearInterval(window.interval);
        }
    },
    paymentElementExists: function() {
    	var pages = $(".ellist");
    	var exists = false;
    	var droppedCount = 0;
    	$.each(pages, function(i, page) {
            var lines = $(page).find('.formline');
            $.each(lines, function(i, line) {
            	var els = $(line).find('.el');
                if(els.length) {
                    $.each(els, function(i, el) {
                    	if($(el).hasClass('ghost')) {
                    		//
                    	} else {
                    		var type = $(el).find('.gc').first().attr('et');
	                    	if(type == 'paypal' || type == 'stripe' || type == 'stripepaypal') {
	                    		exists = true;
	                    	}
                    	}
                    });
                }
            });
        });

    	window.paymentElementRestrict = exists;
    	return exists;
    },
    captchaElementExists: function() {
    	var pages = $(".ellist");
    	var exists = false;
    	var droppedCount = 0;
    	$.each(pages, function(i, page) {
            var lines = $(page).find('.formline');
            $.each(lines, function(i, line) {
            	var els = $(line).find('.el');
                if(els.length) {
                    $.each(els, function(i, el) {
                    	if($(el).hasClass('ghost')) {
                    		//
                    	} else {
                    		var type = $(el).find('.gc').first().attr('et');
	                    	if(type == 'captcha') {
	                    		exists = true;
	                    	}
                    	}
                    });
                }
            });
        });

    	window.captchaElementRestrict = exists;
    	return exists;
    },
    makeElementSortable: function(elm) {
        Sortable.create(
            elm,
            {
                animation: 350,
                group:'A',
                filter: function(evt) {
                	var tElement = $(evt.toElement).closest('.div-textarea');
                	if(tElement.length) {
                		var element = tElement;
                	} else {
                		var element = $(evt.toElement);
                	}
                	if(element.hasClass('inline-edit') || element.hasClass('ed') || element.hasClass('div-textarea') || element.attr('prop')=='placeholderText') {
                		return true;
                	}
                	return false;
                },
                onAdd: function (evt) {
					var $item = $(evt.item);
					var type = $item.find('.gc').attr('type');
					if($(evt.to).closest('.formElementContainerSuccess').length && type!='LABEL' && type!='SECTION' && type!='PICTURE') {
						$item.remove();
						evt.preventDefault();
						return false;
					}

                    if($(evt.to).find('.el').length > 0) {
                        Ui.addNewElement(evt);
                    } else {
                        return false;
                    }

                    if(window.interval) {
                        clearInterval(window.interval);
                    }
                },
                onMove: function (evt) {
					var formContainer = $(evt.from).closest('.formElementContainer');
					var el = $(evt.from).find('.selected');
					var type = el.find('.gc').attr('type');
					if(formContainer.length) {
						if($(evt.to).closest('.formElementContainerSuccess').length && type!='LABEL' && type!='SECTION' && type!='PICTURE') {
							return false;
						} else {
							return true;
						}
					}

					return true;
                },
                onEnd: function(evt) {
                    Ui.removeDropLines();
                    Ui.endMove(evt);
                },
                onStart: function(evt) {
                    Ui.showDropLines(evt);
                }
            }
        );
    },

	makeElementSortable2: function(elm) {
        Sortable.create(
            elm,
            {
                animation: 350,
                group:'A',
                filter: function(evt) {
                	var tElement = $(evt.toElement).closest('.div-textarea');
                	if(tElement.length) {
                		var element = tElement;
                	} else {
                		var element = $(evt.toElement);
                	}
                	if(element.hasClass('inline-edit') || element.hasClass('ed') || element.hasClass('div-textarea') || element.attr('prop')=='placeholderText') {
                		return true;
                	}
                	return false;
                },
                onAdd: function (evt) {
                    if($(evt.to).find('.el').length > 0) {
                        Ui.addNewElement(evt);
                    } else {
                        return false;
                    }

                    if(window.interval) {
                        clearInterval(window.interval);
                    }
                },
                onMove: function (evt) {
                    return true;
                },
                onEnd: function(evt) {
                    Ui.removeDropLines();
                    Ui.endMove(evt);
                },
                onStart: function(evt) {
                    Ui.showDropLines(evt);
                }
            }
        );
    },

    addformline:function(lineid, page_number, type){
        type = type || "old";

		if(page_number == 'success') {
			setTimeout(function() {
				$('#formelements_' + page_number).append('<div class="formline" id="'+lineid+'" placeholder="Drag display boxes here to add them to the success page"></div>');
			},300);
		} else {
			if(type=='old') {
	            $('#formelements_' + page_number).append('<div class="formline" id="'+lineid+'" placeholder="Drag boxes here to add them to the form"></div>');
	        } else {
	            $('<div class="formline" id="'+lineid+'" placeholder="Drag boxes here to add them to the form"></div>').insertBefore('#formelements_' + page_number + ' .footer');
	        }
		}

		if(page_number == 'success') {
			setTimeout(function() {
				var elm = document.getElementById(lineid);
				Ui.makeElementSortable(elm);
			},300);
		} else {
			var elm = document.getElementById(lineid);
			Ui.makeElementSortable(elm);
		}

    },
    setel:function(evt, action){
    	action = action || 'dragged';

        if($(evt.dragged).hasClass('disabled') && gd.fd.active=='1') {
            //
        } else if(Ui.paymentElementExists() && ($(evt.dragged).hasClass('stripe') || $(evt.dragged).hasClass('paypal') || $(evt.dragged).hasClass('stripepaypal'))) {
        	//
        } else if(Ui.captchaElementExists() && $(evt.dragged).hasClass('captcha')) {
        	//
        } else {
            clearInterval(window.interval);
            if($($(evt.dragged).html()).is('div') == false && action=='dragged') {
            	window.old_template = $(evt.dragged).html();
            }
            var newid=Utils.insertid();
            var oldid=$(evt.dragged).attr("did").slice(4);
            var tpl=tmpl.frm[oldid.toUpperCase()];
            if (typeof tpl == 'undefined') {
                var tpl='no template loaded for '+oldid.toUpperCase();
            }
            var stpl=tmpl.side[oldid.toUpperCase()];
            if (typeof stpl == 'undefined') {
                var stpl='no Side template loaded for '+oldid.toUpperCase();
            }

            var template = $('<div>'+tpl+'</div>');
            var type = '';
            if($(evt.dragged).data('text-type')) {
                type = $(evt.dragged).data('text-type');
                var fieldset = template.find('fieldset');
                var input = fieldset.find('input');

                if(type=='email' || type=='phone' || type=='date') {
                    fieldset.addClass('inline-edit-container');
                    input.addClass('inline-edit input-text');
                }

                if(type=='email') {
                    input.attr('type', 'email');
                    fieldset.append('<i class="fa fa-envelope"></i>');
                } else if(type=='phone') {
                    fieldset.append('<i class="fa fa-phone"></i>');
                } else if(type=='date') {
                    fieldset.append('<i class="fa fa-calendar"></i>');
                } else if(type=='number') {
                    input.attr('type', 'number');
                }
            }
            if(oldid == 'datetime') {
            	var fieldset = template.find('fieldset');
            	var $el = fieldset.find('input[prop=placeholderText]').get(0);
            	Ui.makeElementDateOrTime($el, {
            		enableTime: true,
            		allowInput: true,
            		time_24hr: true,
            		appendTo: fieldset.get(0),
            		onClose: function(selectedDates, dateStr, instance) {
						$(instance.input).blur();
					},
					"locale": {
						"firstDayOfWeek": 1 // start week on Monday
					}
            	});
            }

            if(oldid == 'date') {
            	var fieldset = template.find('fieldset');
            	var $el = fieldset.find('input[prop=placeholderText]').get(0);
            	Ui.makeElementDateOrTime($el, {
            		allowInput: true,
            		appendTo: fieldset.get(0),
            		onClose: function(selectedDates, dateStr, instance) {
						$(instance.input).blur();
					},
					"locale": {
						"firstDayOfWeek": 1 // start week on Monday
					}
            	});
            }

            if(oldid == 'time') {
            	var fieldset = template.find('fieldset');
            	var $el = fieldset.find('input[prop=placeholderText]').get(0);
            	Ui.makeElementDateOrTime($el, {
            		enableTime: true,
            		noCalendar: true,
            		time_24hr: true,
            		allowInput: true,
            		appendTo: fieldset.get(0),
            		onClose: function(selectedDates, dateStr, instance) {
						$(instance.input).blur();
					},
					"locale": {
						"firstDayOfWeek": 1 // start week on Monday
					}
            	});
            }

            var default_value = $(evt.dragged).data('default-value');
            var tpl_label = template.find('textarea[prop=inputLabel]');
            if(tpl_label.length) {
            	tpl_label.val(default_value);
            	window.dragged_element = tpl_label;
            }
            $(evt.dragged).html(template);
            $(evt.dragged).attr("id","f"+newid);
            if(type) {
            	$(evt.dragged).attr("validation-type",type.toUpperCase());
            }
            Ui.makeside(newid,oldid.toUpperCase());
            Ui.movel(evt);
            if(action=='dragged') {
            	window.interval = setInterval(function() {
	                if($(evt.to).find('#f'+newid).length == 0 && window.old_template) {
	                    $(evt.dragged).html(window.old_template);
	                    $(evt.dragged).removeAttr('id');
	                    window.old_template = '';
	                }
	            }, 1);
            }
            return newid;
        }
        if($(evt.dragged).hasClass('stripe') || $(evt.dragged).hasClass('paypal') || $(evt.dragged).hasClass('stripepaypal')) {
        	Ui.determineCurrency();
        }
    },
    endMove: function(evt) {
    	var evt = evt || null;

    	if(evt) {
    		var $this = $(evt.item);
        	var container = $this.closest('.formElementContainer');
    	}

        var page_containers = $(document).find('.ellist').not(".pword");

        var $line_number = 1;
        var $new_line_number = 1;
        $.each(page_containers, function(i, $page_container) {
            $page_container = $($page_container);
            var $page = $page_container.data('page');
            var $page_number = $page_container.attr('id').slice(13);

            if(evt && $(evt.from).find('.el').length == 0) {
                $(evt.from).remove();
            }

            var $last_line = $page_container.find('.formline').last();
            var $last_line_number = $last_line.attr('id').slice(4);

            $.each($page_container.find('.formline'), function(i, line) {
                if($(line).find('.el').length > 0) {
                    if($(line).hasClass('drop-line')) {
                        $(line).removeClass('drop-line');
                    }
                    Ui.balanceformline($(line), $page, $line_number);
                    $line_number++;
                }
            });

            if(evt && $last_line.find('.el').length > 0) {
                Ui.addformline('line'+(parseInt($last_line_number)+1), $page_number, 'new');
            }

            setTimeout(function() {
                //update their id
                $.each($page_container.find('.formline'), function(i, line) {
                    $(line).attr('id', 'line'+$new_line_number);
                    $new_line_number++;
                });
            }, 100);

        });

        Ui.submitUpdateElements(window.elementsToBeUpdated);
    },
    movel:function(evt){
        var oldlineid=evt.from.id;
        var newlineid=evt.to.id;

        if(oldlineid=='sideA' || oldlineid=='sideB' || oldlineid=='sideC' || oldlineid=='sideD') {
            window.moveElementType = 'new';
        }
    },
    saveform:function(sortable){
      var order = sortable.toArray();
        alert('ccc');
        alert(JSON.stringify(sortable.options.group));
    },
    balanceformline:function(container, page, line_number){
        var line_id = container.attr('id');
        line_number = line_number || line_id.slice(4);
        var order = line_number * 1000;

        var elements_in_line = container.find('.el');

        var new_size = 12 / elements_in_line.length;
        $.each(elements_in_line, function(i, element) {
            var new_order = order + i;
            var req = {};
            req.form_id=gd.object;
            req.el_id=$(element).attr('id').slice(1);
            req.order=new_order;
            req.size=new_size;
            req.page=page;
            req.action="update";
            //Utils.reqdata('editFormElement',req);

            window.elementsToBeUpdated.push({
            	el_id: $(element).attr('id').slice(1),
            	order: new_order,
            	size:new_size,
            	page:page
            })
        });

        Ui.determineFormNeedsUpgrade();
        Ui.determineElementNeedsUpdate();
    },
    submitUpdateElements: function(elementsArray) {
    	var req = {};
    	req.form_id=gd.object;
    	req.updates=elementsArray;
    	req.action="update";
    	req.method="POST";
    	Utils.reqdata('editElements',req);

    	window.elementsToBeUpdated = [];
    },
    elpopulate:function(data, isFromActivateEl){
		isFromActivateEl = isFromActivateEl || false;
        // load all the data from the DB into the form
        if(data.required==true){
            $("#f"+data._id+" [prop=required]").addClass("active");
        }
        if(data.iconEnabled==true){
            $("#f"+data._id+" div.icon").addClass("icon-input");
            $("#f"+data._id+" div.icon").append('<i class="fa '+data.iconName+'"></i>')
        }
        if(data.type=="TEXT" && data.customValidationType!='NONE') {
        	$(".sidebar #s"+data._id).find('.validationMessage').show();
        }
		if(data.type=='FILE' && data.largeFile) {
			if(data.fileType) {
				var splitFileType = data.fileType.split('/');
				if(splitFileType[0] == 'image') {
					$(".sidebar #s"+data._id).find('.image_dimension').show();
				}
			}
		}
        var fields=[];
        var options=[];
        var check=["required","disabled","iconEnabled","enableLogic"];
        if(data.type=="TEXT"){
        	var check=["required","disabled","iconEnabled","enableLogic"];
            var fields=["inputLabel","instructionText","helpText","placeholderText","defaultValue","iconName","customValidationType","validationMessage", "queryName", "logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions", "regex"];
        } else if(data.type=="STARRATING"){
        	var check=["required","disabled","enableLogic"];
            var fields=["inputLabel","instructionText","helpText","placeholderText","defaultValue","queryName","logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions"];
        } else if(data.type=="LOOKUP"){
        	var check=["required","disabled","enableLogic", "autoSuggest"];
            var fields=["inputLabel","instructionText","helpText","placeholderText","defaultValue","queryName","logicAction", "logicField", "logicCondition", "logicValue", "lookupColumn", "notExistsErrorMessage", "conditionAndOr", "conditions"];
			var options=["optionsList"];
		} else if(data.type=="CALCULATION"){
        	var check=["required","disabled","enableLogic", "hidden"];
            var fields=["inputLabel","instructionText","helpText","placeholderText","defaultValue","queryName","logicAction", "dateFormat", "logicField", "logicCondition", "logicValue", "fieldLists", "conditionAndOr", "conditions", "calculationTotal"];
		} else if(data.type=="DATE"){
        	var check=["required","disabled","enableLogic", "dM","dT","dW","dTH","dF","dSat","dSun"];
            var fields=["inputLabel","instructionText","helpText","placeholderText","defaultValue","queryName", "dateFormat", "beginDate", "endDate", "logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions", "pickerLang"];
        } else if(data.type=="TIME"){
        	var check=["required","disabled","use12Notation","enableLogic"];
            var fields=["inputLabel","instructionText","helpText","placeholderText","defaultValue","queryName", "interval", "logicAction", "logicField", "logicCondition", "logicValue", "minTime", "maxTime", "conditionAndOr", "conditions"];
        } else if(data.type=="DATETIME"){
        	var check=["required","disabled","use12Notation","queryName","enableLogic", "dM","dT","dW","dTH","dF","dSat","dSun"];
            var fields=["inputLabel","instructionText","helpText","placeholderText","defaultValue","queryName", "beginDate", "endDate", "interval", "logicAction", "logicField", "logicCondition", "logicValue", "minTime", "maxTime", "conditionAndOr", "conditions", "pickerLang"];
        } else if(data.type=="FILE") {
            var fields=["inputLabel", "fileButtonLabel", "instructionText", "helpText","queryName", "logicAction", "logicField", "logicCondition", "logicValue", "unfinishUpload", "finishedUpload", "uploading","fileType","minSize","maxSize","fileSizeError","minHeight","maxHeight","minWidth","maxWidth","fileDimensionError", "conditionAndOr", "conditions"];
            var check=["required","disabled","sendAsAttachment","enableLogic", "largeFile","multipleFile"];
        } else if(data.type=="PICTURE") {
            var fields=["width", "height", "logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions"];
            var check=["enableLogic"];
        } else if(data.type=="STRIPE") {
            var fields=["label", "public_key", "secret_key", "amount", "currency", "paymentsPageLabel", "buttonLabel","paymentProcessButtonLabel", "cardNameLabel", "cardNumberLabel", "expiryDateLabel", "securityCodeLabel", "postCodeLabel","queryName", "logicAction", "logicField", "logicCondition", "logicValue", "calculationTotal", "fieldLists", "conditionAndOr", "conditions", "captureLabel", "idealLabel", "alipayLabel", "ach_credit_transferLabel", "bancontactLabel", "epsLabel", "giropayLabel", "multibancoLabel", "p24Label", "sepa_debitLabel", "sofortLabel"];
            var check=["postCode","enableLogic", "captureCard", "securityCode", "card", "ideal", "alipay", "ach_credit_transfer", "bancontact", "eps", "giropay", "multibanco", "p24", "sepa_debit", "sofort"];
        } else if(data.type=="PAYPAL") {
            var fields=["label", "email", "amount", "currency", "paymentsPageLabel", "buttonLabel","paymentProcessButtonLabel","queryName", "logicAction", "logicField", "logicCondition", "logicValue", "calculationTotal", "fieldLists", "conditionAndOr", "conditions"];
            var check=["enableLogic"];
        } else if(data.type=="STRIPEPAYPAL") {
            var fields=["label","labelStripe", "labelPaypal", "email", "public_key", "secret_key", "amount", "currency", "paymentsPageLabel", "buttonLabel", "paymentProcessButtonLabel", "cardNameLabel", "cardNumberLabel", "expiryDateLabel", "securityCodeLabel", "postCodeLabel","queryName", "logicAction", "logicField", "logicCondition", "logicValue", "calculationTotal", "fieldLists", "conditionAndOr", "conditions", "idealLabel", "alipayLabel", "ach_credit_transferLabel", "bancontactLabel", "epsLabel", "giropayLabel", "multibancoLabel", "p24Label", "sepa_debitLabel", "sofortLabel"];
            var check=["postCode","enableLogic", "securityCode", "card", "ideal", "alipay", "ach_credit_transfer", "bancontact", "eps", "giropay", "multibanco", "p24", "sepa_debit", "sofort"];
        } else if(data.type=="LABEL"){
            var fields=["labelText", "logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions"];
            var check=["enableLogic"];
        } else if(data.type=="SECTION"){
            var fields=["labelText", "textSize", "logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions"];
            var check=["enableLogic"];
        } else if(data.type=="SELECT"){
            var fields=["inputLabel","instructionText","helpText","placeholderText","queryName", "logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions"];
            var check=["required","disabled","enableLogic"];
            var options=["optionsList"];
        } else if(data.type=="RADIO" || data.type=="CHECKBOX"){
            var fields=["inputLabel","instructionText","helpText","queryName","otherOptionLabel", "logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions"];
            var check=["required","disabled","otherOption","enableLogic"];
            var options=["optionsList"];
        } else if(data.type=="PRODUCTS"){
            var fields=["inputLabel","instructionText","helpText","queryName","enableAmountLabel","unit", "logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions"];
            var check=["required","disabled","enableAmount","useSelect","enableLogic"];
            var options=["productsList", "optionsList"];
        } else if(data.type=="INPUTTABLE"){
            var fields=["inputLabel","instructionText","helpText","queryName","inputtype","logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions"];
            var check=["required","disabled","enableLogic"];
            var options=["questionList", "answerList"];
        } else if(data.type=="SWITCH") {
        	var fields=["inputLabel","instructionText","helpText","queryName", "onLabel", "offLabel","otherOptionLabel", "logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions"];
            var check=["required","disabled","otherOption","enableLogic"];
            var options=["optionsList"];
        } else if(data.type=="RANGE"){
            var fields=["inputLabel","instructionText","helpText","rangeMax","rangeMin","queryName", "logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions"];
            var check=["required","disabled","enableLogic"];
        } else if(data.type=="NAME"){
            var fields=["inputLabel","instructionText","helpText","placeholderTitleText","placeholderFirstText","placeholderMiddleText","placeholderLastText","queryName", "logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions"];
            var check=["required","disabled","nameTitle","middleName","enableLogic"];
        } else if(data.type=="TEXTAREA"){
            var fields=["inputLabel","instructionText","helpText","placeholderText","defaultValue","customValidationType","queryName","textMaxLength","textAreaHeight", "logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions"];
            var check=["required","disabled","enableLogic"];
        } else if(data.type=="US_ADDRESS") {
        	var fields=["inputLabel","instructionText","helpText", "format","placeholderAddress1Text","placeholderAddress2Text","placeholderCityText","placeholderStateText","placeholderZipText","placeholderCountryText", "defaultCountry", "queryName", "logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions"];
            var check=["required","disabled", "country","enableLogic"];
        } else if(data.type=="CAPTCHA") {
        	var fields=["captchaError"];
        } else if(data.type=="SIGNATURE") {
        	var fields=["label", "width", "height", "clearLabel", "logicAction", "logicField", "logicCondition", "logicValue", "conditionAndOr", "conditions"];
			var check=["enableLogic","required"];
        }

        for (l = 0; l < options.length; l++) {
	        if(data[options[l]]!=undefined){
	        	if(typeof data[options[l]] == 'object') {
	        		data[options[l]] = Object.keys(data[options[l]]).map(function (key) { return data[options[l]][key]; });
	        	}

	        	$(".sidebar #s"+data._id+" [prop="+options[l]+"]").html("");
	        	if(data.type=="SELECT") {
	        		$("#f"+data._id+" [prop="+options[l]+"]").html("<option></option>");
	        	}
				var hasDefaultValues = 0;
	          	for (op = 0; op < data[options[l]].length;op++){
		            var option=data[options[l]][op];
		            if(option) {
		            	if(option['label']==undefined){option['label']="";}
			            if(option['value']==undefined){option['value']="";}
			            if(data.type=="SWITCH"){

			            } else if((data.type=="RADIO")||(data.type=="CHECKBOX")){
			            	var input = $('<input prop="option_label" class="" type="text" />').val(option['label']);
			            	var template_container = $('<div prop="o'+op+'" class="option-container">\
			          			<label class="option">\
									<input type="radio" name="radio_'+data._id+'" value="1">\
			          				<i></i>\
			          			</label>\
			          			<button class="inline-edit red" tabindex="-1"><i class="fm-icon-close-thick"></i></button>\
			          		</div>');

			            	template_container.find('label.option').append(input);

							if(data.type=="RADIO") {
								if(option['default'] == '1' || option['default']==true || option['default']=='true') {
									hasDefaultValues++;
								}
							}

			              	$("#f"+data._id+" [prop="+options[l]+"]").append(template_container);
			            } else {

							var opt = {
								value:op,
								text:option['label'],
								oid:"o"+op
							};

							if(option['default'] == '1' || option['default']==true || option['default']=='true') {
								opt.selected = true;
							}
			              	$("#f"+data._id+" [prop="+options[l]+"]").append($('<option>',opt));
			            }

			            var displayNone = 'style="display:none;"';
			            var downStyle = '';
			            var upStyle = '';

			            if(op == 0) {
			            	upStyle = displayNone;
			            } else if(op == (data[options[l]].length - 1)) {
			            	downStyle = displayNone;
			            }

			            if(data.type=="INPUTTABLE" && options[l]!='answerList') {
			            	var option_template = $('<fieldset prop="o'+op+'">\
				            	<input class="g10 text small dark" prop="option_label" value="'+option['label']+'">\
				            	<button class="right inline-edit delete" tabindex="-1"><i class="icon-trash"></i></button>\
				            	<button class="right inline-edit moveup" tabindex="-1" '+upStyle+'><i class="icon-arrow-up"></i></button>\
				            	<button class="right inline-edit movedown" tabindex="-1" '+downStyle+'><i class="icon-arrow-down"></i></button>\
				            </fieldset>');

				            option_template.find('input[prop=option_label]').val(option['label']);
			            } else {
			            	var option_template = $('<fieldset prop="o'+op+'">\
				            	<input class="g5 text small dark" prop="option_label">\
				            	<input class="g5 text small dark marleft" prop="option_value">\
				            	<button class="right inline-edit delete" tabindex="-1"><i class="icon-trash"></i></button>\
				            	<button class="right inline-edit moveup" tabindex="-1" '+upStyle+'><i class="icon-arrow-up"></i></button>\
				            	<button class="right inline-edit movedown" tabindex="-1" '+downStyle+'><i class="icon-arrow-down"></i></button>\
				            </fieldset>');

				            option_template.find('input[prop=option_label]').val(option['label']);
				            option_template.find('input[prop=option_value]').val(option['value']);
			            }

			            option_template.find('[prop="option_label"]').val(option['label']);
			            option_template.find('[prop="option_value"]').val(option['value']);

			            $(".sidebar #s"+data._id+" [prop="+options[l]+"]").append(option_template);
		            }
		        }

				if(data.type=="RADIO" && hasDefaultValues) {
					 $(".sidebar #s"+data._id).find('.clearDefault').show();
				} else {
					$(".sidebar #s"+data._id).find('.clearDefault').hide();
				}
	      	}
      	}

        setTimeout(function() {
            for (l = 0; l < fields.length; l++) {
				if(undefined !=data[fields[l]]){
					if(isFromActivateEl == false) {
						if($("#f"+data._id+" [prop="+fields[l]+"]").is("div") || $("#f"+data._id+" [prop="+fields[l]+"]").is("span")){
					        $("#f"+data._id+" [prop="+fields[l]+"]").html(data[fields[l]]);
					    }
					    if($("#f"+data._id+" [prop="+fields[l]+"]").is("option")){
					        $("#f"+data._id+" [prop="+fields[l]+"]").html(data[fields[l]]);
					    } else {
					        $('#f'+data._id+' [prop="'+fields[l]+'"]').val(data[fields[l]]);
					        $('#f'+data._id+' [prop="'+fields[l]+'"]').html(data[fields[l]]);
					    }
					}
					$("#s"+data._id+" [prop="+fields[l]+"]").val(data[fields[l]]);
				}

				var temp_val = String(data[fields[l]]).replace(/\W/g, '');
				$(document).find('fieldset.'+fields[l]+'_'+temp_val).show();
            }
        }, 100);

        for (c = 0; c < check.length; c++) {
            if(true==data[check[c]]){
                $("#s"+data._id+" [prop="+check[c]+"]").prop("checked",true);
                $(document).find('.show_'+check[c]+'_1').show();
                $(document).find('.show_'+check[c]+'_0').hide();
            } else {
            	$(document).find('.show_'+check[c]+'_1').hide();
            	$(document).find('.show_'+check[c]+'_0').show();
            }
        }


    },
    populatesidebar:function(group){
        var props ={};
        props['theme']      	=["themeFormBackground","themeBrowserBackground","themeFont","themeEnabled","themeFormBorder","themeFieldBackground","themeFieldBorder","themeFieldActive","themeFieldHover","themeFieldError","themeFieldSelected","themeSubmitButton","themeSubmitButtonText","themeText", "themeDescriptionText", "themeFieldText", "themeCSS", "themeID", "customCSS"];
        props['settings']   	=["name","logo","description","displayHeader","notifyNewSubmissions","notifySubmitter","notifyUseTemplate","emailReply","emailFrom","email","submitButtonText","previousButtonText","nextButtonText", "inactiveMessage", "requiredMessage", "autoComplete", "footerPaginationPageText", "footerPaginationOfText", "currency", "isExternalData", "externalData", "enableCSRF", "autoFill","rtl","doRedirect","redirectUrl","trackGeoAndTimezone","responseStorage","usePassword","password","passwordLabel","passwordButtonLabel","invalidPassword","leavePrompt"];
		props['confirmation']	=["doRedirect","submitSuccessMessage","redirectUrl"];
		props['elements']   	=[];
        props['elementsEdit']   =[];
        props['endpoint']   	=["name","doRedirect","submitSuccessMessage","redirectUrl","notifyNewSubmissions","notifySubmitter","email","emailReply","emailFrom"];
        var pl=props[group].length;
        for (i = 0; i < pl; i++) {
            Ui.setprop(props[group][i],gd.fd[props[group][i]],'from db');
        }
    },
    setprop:function(name,value,source){
        if(source!='checked'){
	        if(value) {
	        	if(name=='name' || name=='description') {
	        		value = value.replace(/\\(.)/mg, "$1");
	        	}
        		$('#'+name).html(value);
	        	$('#s_'+name).val(value);
	        }

	        if(name == 'displayHeader' && !value) {
	        	$(document).find('#displayHeaderContainer').css('visibility', 'hidden');
	        }

	        if(name=='logo' && value) {
	        	$("#ssettings").find('.file').hide();
	        	var slogoc = $("#ssettings").find('.logoContainer');
	        	if(typeof value == 'object') {
        			var logotpl = $('<fieldset class="formlogo"><i class="fa fa-trash"></i><img src="https://s3.amazonaws.com/'+value.bucket+'/'+value.key+'?v='+Utils.insertid()+'" /></fieldset>');
        		} else {
        			var logotpl = $('<fieldset class="formlogo"><i class="fa fa-trash"></i><img src="/logo/'+value+'?v='+Utils.insertid()+'" /></fieldset>');
        		}
        		$(slogoc).append(logotpl);
	        }

	        if(name=='themeEnabled' && value) {
	        	$("#stheme").find('.customCSS').hide();
	        }
	        if(name=='customCSS' && value) {
	        	$("#stheme").find('.themeEnabled').hide();
	        	$(document).find('.warning_message_custom_css').show();
	        }
        }
        if($('#s_'+name).attr('type')=="checkbox"){
            if(value==1){
                $('#s_'+name).prop('checked', true);
                $(document).find('.show_'+name+'_0').hide();
                $(document).find('.show_'+name+'_1').show();
            } else {
                $('#s_'+name).prop('checked', false);
                $(document).find('.show_'+name+'_1').hide();
                $(document).find('.show_'+name+'_0').show();
            }
        }

        temp_val = String(value).replace(/\W/g, '');
        $(document).find('fieldset.'+name+'_'+temp_val).show();
    },
    activateEl:function(response, eid){
    	response = response || false;
    	eid = eid || false;
    	window.initialize = 2;

    	if(!response._id) {
			var id = eid;
			if(!id) {
				id = $(this).attr("id");
				if(id) {
					id = $(this).attr("id").substring(1);
				}
			}
    		if(id) {
				var el_id = id;
				parent.location.hash = "#e"+el_id;
	            var req = {};
	            req.form_id=gd.object;
	            req.element_id=el_id;

				var el_type = $(this).find('.gc').attr('type');

				if(el_type!='RADIO' && el_type!='CHECKBOX' && el_type!='SELECT' && el_type!='LOOKUP' && el_type!='PRODUCTS' && el_type!='SWITCH') {
					req.skip_datasource=true;
				}

	            Utils.reqdata('getFormElement',req, Ui.activateEl);
    		}
        } else {
        	var el_id = response._id;
        	var el = $(document).find('#f'+el_id);
        	var form_container = $(document).find('.formElementContainer');
	        var elements = [];
	        var pages = form_container.find('.ellist');
	        var stop = false;
	        pages.each(function() {
	        	if(stop) {
	        		return false;
	        	}
	        	var els = $(this).find('.el');
	        	els.each(function() {
	        		var eid = $(this).attr('id').substring(1);
	        		if(eid == el_id) {
	        			stop = true;
	        			return false;
	        		} else {
	        			var label = $(this).find('[prop=inputLabel]').val();
						var type = $(this).find('.gc').attr('type');
						if(type == 'INPUTTABLE') {
							var options = $(this).find('.foption');
							options.each(function() {
								var input = $(this).find('.table-input');
								var input_val = Utils.slugify(input.val());
								var el_name = eid+"['"+input_val+"']";
								elements.push({
									id: el_name,
									label: label + ' ' + input.val()
								});
							});
						} else {
							elements.push({
		        				id: eid,
		        				label: label
		        			});
						}
	        		}
	        	});
	        });

	    	//include external field
	        var settings = $(document).find("#ssettings");
	        if(settings.find('#s_isExternalData').is(":checked")) {
	        	var exts = settings.find('#s_externalData').val();
	        	var externals = exts.split(',');
	        	$.each(externals, function(i, external) {
	        		elements.push({
	        			id: external.trim(),
	        			label: external.trim()
	        		});
	        	});
	        }

			//include geo and timezone if necessary
			if(settings.find('#s_trackGeoAndTimezone').is(":checked")) {
				elements.push({id: 'Geo',label: 'Geo'});
				elements.push({id: 'Timezone',label: 'Timezone'});
			}

	        var sidebar = $(document).find('#s'+el_id);
	        if(response.enableLogic) {
	        	sidebar.find('.show_enableLogic_1').show();
	        } else {
	        	sidebar.find('.show_enableLogic_1').hide();
	        }

	        var select = sidebar.find('[prop=logicField]');
			//for calculation
			var select_calc = sidebar.find('.side_calculation').find('select.text');

	        select.html('<option value=""></option>');
	        select_calc.html('<option value=""></option>');
	        $.each(elements, function(i, el) {
	        	if(el.label) {
	        		var template = $('<option value="'+el.id+'">'+el.label+'</option>');
	    			select.append(template);
	    			select_calc.append(template);
	        	}
	        });

	        if(elements.length <= 0) {
	        	sidebar.find('fieldset.logic').hide();
	        } else {
	        	sidebar.find('fieldset.logic').show();
	        }

	        if(response.logicField) {
	        	select.val(response.logicField);
	        }

			Ui.showDeleteConditions(sidebar, response);

	        if(response.type=='INPUTTABLE' || response.type=='RADIO' || response.type=='CHECKBOX' || response.type=='SWITCH' || response.type=='PRODUCTS' || response.type=='SELECT'  || response.type=='LOOKUP') {
				if(response.columns) {
					var classList = 'optionsList';
					if(response.type=='PRODUCTS') {
						classList = 'productsList';
					}

					var l = response.columns[0];
					var v = response.columns[1] ? response.columns[1]:'Value';

					var selectColumns = sidebar.find('[prop=lookupColumn]');
					selectColumns.html('<option value=""></option>');
					var ctr=1;
			        $.each(response.columns, function(i, column) {
			        	if(column) {
							value = 'label';
							if(ctr==2) {
								value = 'value';
							} else if(ctr>2) {
								value = 'column_'+ctr;
							}
			        		var template = $('<option value="'+value+'">'+column+'</option>');
			    			selectColumns.append(template);
							ctr++;
			        	}
			        });

					var fieldset = sidebar.find('fieldset.' + classList);
					fieldset.find('.gr.g6 label').html(l);
					fieldset.find('.gr.g5 label').html(v);
					sidebar.find('a.datasourceLink').attr('href', '/gotodatasource/'+el_id+'/');
					sidebar.find('[prop=datasource_id]').val(response.datasource_id);
					sidebar.find('.datasource_link').show();
				} else {
					sidebar.find('.datasource_link').hide();
				}
	        	Ui.elpopulate(response, true);
	        } else if(response.type == 'PAYPAL' || response.type == 'STRIPE' || response.type == 'STRIPEPAYPAL' || response.type == 'CALCULATION') {
				if(response.type == 'CALCULATION') {
					sidebar.find('.calc_description').html('Add field values to Calculation field');
					sidebar.find('.calc_description2').html('Calculation');
				} else {
					sidebar.find('.calc_description').html('Add field values to Payment amount');
					sidebar.find('.calc_description2').html('Payment Amount');
				}
	        	Ui.elpopulate(response, true);
	        	Ui.populateCalculationField(response);
	        	Ui.calculationShowActions(el_id);
	        } else if(response.type == 'TEXT') {
				var field_icons = sidebar.find('.fontawesome-select');
				 field_icons.html('<option value="">Select</option>');
				$.each(gd.fa_icons, function(fa, val) {
					var fontText = fa.replace('fa-', '', val);
		  			fontText = fontText.replace('-', ' ', fontText);

					fontText = fontText.toLowerCase().replace(/\b[a-z]/g, function(letter) {
					    return letter.toUpperCase();
					});

					field_icons.append('<option value="'+fa+'">&#x'+val+' '+fontText);
				});
				Ui.elpopulate(response, true);
			} else if(response.type != 'LABEL') {
				Ui.elpopulate(response, true);
			}

			if(sidebar.find('[prop=enableLogic]').is(":checked")) {
				setTimeout(function() {
					Ui.submitConditionsChanges();
				}, 500);
			}
        }
    },
    createformel:function(order,type,page,id, text_type, default_value){
    	if(id=='elements') { return; }
        var req = {};
        req.form_id=gd.object;
        req.el_id=id;
        req.order=order;
        req.size=12;
        var side = $(document).find('#s'+id);
        var form = $(document).find('#f'+id);
        if(type){
            req.type=type;
        }

        if(text_type) {
            if(text_type=='email') {
                req.iconEnabled = true;
                req.iconName = 'fa-envelope';
                req.customValidationType = 'EMAIL';

                side.find('input[prop=iconEnabled]').prop('checked', true);
                side.find('select[prop=iconName]').val('fa-envelope');
                side.find('select[prop=customValidationType]').val('EMAIL');
                //side.find('select[prop=customValidationType]').trigger('change');
            } else if(text_type=='phone') {
                req.iconEnabled = true;
                req.iconName = 'fa-phone';
                req.customValidationType = 'PHONE';

                side.find('input[prop=iconEnabled]').prop('checked', true);
                side.find('select[prop=iconName]').val('fa-phone');
                side.find('select[prop=customValidationType]').val('PHONE');
                //side.find('select[prop=customValidationType]').trigger('change');
            } else if(text_type=='date') {
                req.iconEnabled = true;
                req.iconName = 'fa-calendar';
                req.customValidationType = 'DATE';
                side.find('input[prop=iconEnabled]').prop('checked', true);
                side.find('select[prop=iconName]').val('fa-calendar');
                side.find('select[prop=customValidationType]').val('DATE');
                //side.find('select[prop=customValidationType]').trigger('change');
            } else if(text_type=='number') {
                req.customValidationType = 'NUMBER';

                side.find('select[prop=customValidationType]').val('NUMBER');
                //side.find('select[prop=customValidationType]').trigger('change');
            }
            req.validationMessage = 'This must be a valid value.';
        } else {
        	if(type=='NAME') {
	        	req.placeholderFirstText='Firstname';
	        	req.placeholderLastText='Lastname';
	        	req.placeholderMiddleText='Middlename';
	        	req.placeholderTitleText='Title';
	        } else if(type=='STRIPE') {
	        	req.label='Cards Accepted';

				req.idealLabel='Pay with iDEAL';
				req.alipayLabel='Pay with Alipay';
				req.ach_credit_transferLabel='Pay with ACH Credit Transfer';
				req.bancontactLabel='Pay with Bancontact';
				req.epsLabel='Pay with EPS';
				req.giropayLabel='Pay with Giropay';
				req.multibancoLabel='Pay with Multibanco';
				req.p24Label='Pay with P24';
				req.sepa_debitLabel='Pay with SEPA Direct Debit';
				req.sofortLabel='Pay with SOFORT';

	        	req.buttonLabel='Buy now';
	        	req.paymentProcessButtonLabel='Processing...';
	        	req.paymentsPageLabel='This is a sample payment label';
	        	req.totalLabel='Total';
	        	req.cardNameLabel='Name on Card';
	        	req.cardNumberLabel='Card Number';
	        	req.expiryDateLabel='Expiry Date';
	        	req.securityCodeLabel='Security Code';
	        	req.postCodeLabel='ZIP/Postal Code';
	        	req.postCode=0;
	        	req.fieldLists=[{"field":""}];
	        	req.calculationTotal='';
				req.securityCode=1;
				req.captureLabel='Capturing credit card data for later processing';
				req.card=1;
	        } else if(type=='PAYPAL') {
	        	req.label='We accept paypal payments';
	        	req.buttonLabel='Buy now';
				req.paymentProcessButtonLabel='Processing...';
	        	req.paymentsPageLabel='This is a sample payment label';
	        	req.totalLabel='Total';
	        	req.fieldLists=[{"field":""}];
	        	req.calculationTotal='';
	        } else if(type=='STRIPEPAYPAL') {
	        	req.label='Please select paypal or credit/debit card';
	        	req.labelStripe='Pay with credit / debit card';

				req.idealLabel='Pay with iDEAL';
				req.alipayLabel='Pay with Alipay';
				req.ach_credit_transferLabel='Pay with ACH Credit Transfer';
				req.bancontactLabel='Pay with Bancontact';
				req.epsLabel='Pay with EPS';
				req.giropayLabel='Pay with Giropay';
				req.multibancoLabel='Pay with Multibanco';
				req.p24Label='Pay with P24';
				req.sepa_debitLabel='Pay with SEPA Direct Debit';
				req.sofortLabel='Pay with SOFORT';

	        	req.labelPaypal='Pay with Paypal';
	        	req.buttonLabel='Buy now';
				req.paymentProcessButtonLabel='Processing...';
	        	req.paymentsPageLabel='This is a sample payment label';
	        	req.totalLabel='Total';
	        	req.cardNameLabel='Name on Card';
	        	req.cardNumberLabel='Card Number';
	        	req.expiryDateLabel='Expiry Date';
	        	req.securityCodeLabel='Security Code';
	        	req.postCodeLabel='ZIP/Postal Code';
	        	req.postCode=0;
	        	req.fieldLists=[{"field":""}];
	        	req.calculationTotal='';
				req.securityCode=1;
				req.card=1;
	        } else if(type=='US_ADDRESS') {
	        	req.placeholderAddress1Text='Address 1';
	        	req.placeholderAddress2Text='Address 2';
	        	req.placeholderCityText='City';
	        	req.placeholderStateText='State';
	        	req.placeholderZipText='Zip Code';
	        	req.placeholderCountryText='Country';
	        	req.country=true;
	        	req.format='OTHER';
	        	side.find('input[prop=country]').prop('checked', true);
	        	side.find('select[prop=format]').val('OTHER');
	        } else if(type=='RADIO' || type=='CHECKBOX' || type=='SWITCH' || type=='LOOKUP') {
	        	req.otherOptionLabel = 'Other';
	        	req.optionsList=[{"label":"option 1"},{"label":"option 2"}];
				if(type=='LOOKUP') {
					req.lookupColumn='label';
					req.autoSuggest='1';
					req.notExistsErrorMessage='Invalid Value';
				} else if(type=='RADIO') {
					form.find('input[type=radio]').attr('name',id);
				}
	        } else if(type=='SELECT') {
	        	req.optionsList=[{"label":"option 1"},{"label":"option 2"}];
	        } else if(type=='PRODUCTS') {
	        	req.enableAmountLabel = 'Quantity';
	        	req.unit = 'currency';
	        	req.productsList = [{"label":"Product 1","value":"100"},{"label":"Product 2","value":"100"}];
	        } else if(type=='INPUTTABLE') {
				req.inputtype='radio';
	        	req.questionList=[{"label":"Question 1"},{"label":"Question 2"}];
	        	req.answerList=[{"label":"Bad"},{"label":"Good"},{"label":"Excellent"}];
	        } else if(type=='CAPTCHA') {
	        	req.captchaError='Please validate the captcha';
	        } else if(type=='SIGNATURE') {
	        	req.label='Signature';
	        	req.width=300;
	        	req.height=150;
	        	req.clearLabel='Clear';
	        } else if(type=='TEXTAREA') {
	        	req.maxLengthErrorMessage = 'This field exceeded the maximum characters allowed.';
	        	req.textAreaHeight = 96;
	        } else if(type=='FILE') {
				req.unfinishUpload = 'File is not ready yet.';
				req.finishedUpload = 'File Uploaded';
				req.uploading = 'Uploading';
				req.fileSizeError = 'File size did not meet the requirement.';
				req.fileDimensionError = 'Image dimension did not meet the requirement.';
				req.largeFile=1;
				req.multipleFile=1;
			} else if(type=='CALCULATION') {
				req.hidden=1;
			}
        }

        req.page=page;
        req.inputLabel=default_value;
        req.method="POST";
        Utils.reqdata('editFormElement',req);
    },
    deleteformel:function(){
        var $this = $(this);
        var page_container = $this.closest('.ellist');
        var page = page_container.data('page');
        var page_number = page_container.attr('id').slice(13);
        var id=$this.closest('.el').attr("id").slice(1);
        var req = {};
        req.form_id=gd.object;
        req.el_id=id;
        req.method="POST";
        req.action="delete";
        Utils.reqdata('editFormElement',req);
        $("#s"+id).remove();
        $("#f"+id).remove();

        var line_number = 1;
        $.each(page_container.find('.formline'), function(i, line) {
            if($(line).find('.el').length > 0) {
                Ui.balanceformline($(line), page, line_number);
                line_number++;
            } else {
            	$(line).remove();
            }
        });

        setTimeout(function() {
        	Ui.submitUpdateElements(window.elementsToBeUpdated);
        }, 100);

        setTimeout(function() {
        	Ui.removeDropLines();
        	Ui.addformline('line'+line_number, page_number, 'new');
        }, 500);

        setTimeout(function() {
        	window.location.href = gd.path + 'editor/'+ gd.object+"/#eelements";
        }, 1000);
    },
    updateformel:function(d){

		if(d.type == 'keyup') {
			clearTimeout(typingTimer);
		}

        var tel=$(this).closest('.sel');
        var id=tel.attr("id").slice(1);
        var et=tel.attr("et");
        var prop=$(this).attr("prop");
        var req = {};
        if(prop==null || prop=='' || gd.element=='elements' || prop=='logicCondition' || prop=='logicField' || prop=='logicValue') {
            return;
        }

        if(id == 'settings' || id == 'endpoint' || id == 'confirmation') {
        	$("#" + prop).html($(this).val());
        	$(document).find("#fconfirmation textarea[prop=" + prop + ']').val($(this).val());

        	if(prop=='displayHeader') {
        		if($(this).is(':checked')) {
        			$("#displayHeaderContainer").css('visibility', 'visible');
        		} else {
        			$("#displayHeaderContainer").css('visibility', 'hidden');
        		}
        	}

        	if($(this).attr('type')=="checkbox"){
	            if($(this).is(':checked')){
	                $('#s'+id+' .show_'+prop+'_0').hide();
	                $('#s'+id+' .show_'+prop+'_1').show();
	                if(prop=='notifyNewSubmissions') {
	                	if($("#s_email").val()=='') {
	                		$("#s_email").val(window.loginEmail);
	                		$("#s_email").trigger('keyup');
	                	}
	                } else if(prop=='notifySubmitter' || prop=='isExternalData') {
	                	var hasEmailElement = false;
	                	$.each($(".formElementContainer .el"), function(i, el) {
	                		var elm = $(el);
	                		if(elm.attr('validation-type') == 'EMAIL') {
	                			hasEmailElement = true;
	                			return;
	                		}
	                	});

	                	var error = false;

	                	if(hasEmailElement == false && prop=='notifySubmitter') {
	                		// Ui.alert('error', 'Please add atleast one email element in the form.');
	                		// error = true;
	                	}

	                	if(error) {
	                		setTimeout(function() {
		                		$("#s_"+prop).next(".switch-container").click();
		                	}, 100);
	                	}
	                } else if(prop=='doRedirect') {
	                	var settings = $(document).find('#sconfirmation');
						var redirectURL = settings.find('[prop="redirectUrl"]');
						redirectURL.trigger('change');
	                	var urlval = redirectURL.val();
	                	var sc = $(document).find('.submit_confirmation');
	                	sc.find('#sc_redirects').show();
	                	var ed = sc.find('textarea');
						ed.css('height', '21px');
						ed.removeClass('autoheight');
	                	ed.attr('placeholder', 'http://example.com/');
	                	ed.attr('prop', 'redirectUrl');
	                	ed.val(urlval);
	                } else if(prop=='rtl') {
						$(document).find("#displayHeaderContainer").attr('dir', 'rtl');
						$(document).find(".formElementContainer").attr('dir', 'rtl');
					} else if(prop=='usePassword') {
						$(document).find('.passwordContainer').show();
					}
	            } else {
	                $('#s'+id+' .show_'+prop+'_1').hide();
	                if(prop=='doRedirect') {
	                	var settings = $(document).find('#sconfirmation');
	                	var confval = settings.find('[prop=submitSuccessMessage]').val();
	                	var sc = $(document).find('.submit_confirmation');
	                	sc.find('#sc_redirects').hide();
	                	var ed = sc.find('textarea');
						ed.addClass('autoheight');
	                	ed.attr('placeholder', 'thank you for your submission');
	                	ed.attr('prop', 'submitSuccessMessage');
	                	ed.val(confval);
						ed.trigger('keydown');
	                } else if(prop=='rtl') {
						$(document).find("#displayHeaderContainer").attr('dir','');
						$(document).find(".formElementContainer").attr('dir', '');
					} else if(prop=='usePassword') {
						$(document).find('.passwordContainer').hide();
					}
	            }
	        } else {
				if(prop=='submitSuccessMessage') {
					$("textarea.autoheight").trigger('keydown');
				}
			}
        }

        if(id == 'theme') {
            if((gd.fd.themeEnabled == '' || gd.fd.themeEnabled == '0' || !gd.fd.themeEnabled) && prop != 'themeEnabled' && prop != 'customCSS') {
                return;
            }
            switch(prop) {
                case 'themeBrowserBackground':
                    $('.fcc, .flex-col').css('background', $(this).val() + ' !important');
                    break;
                case 'themeFont':
                	var font = $(this).val();
                	if(!font) {
                		font = "'Source Sans Pro','Helvetica Neue',Helvetica,Arial,sans-serif";
                	}
                	if($(this).val()) {
                		WebFont.load({
	                        google: {
	                          families: [font]
	                        }
	                    });
                	}
                    $('.fcc').css('font-family', font + ' !important');
                    $('.fcc button').css('font-family', font + ' !important');
                    $('.fcc textarea').css('font-family', font + ' !important');
                    break;
                case 'themeFormBackground':
                    $('.fcc .ellist, .fcc .ellist .el, .fcc .ellist .el textarea:not(.text), .fcc .ellist .el [prop=labelText], .fcc .ellist .el .option-container input[type=text]').not('.selected').css('background', $(this).val() + ' !important');
                    break;
                case 'themeFormBorder':
                    $('.fcc .ellist').css('border', '1px solid ' + $(this).val() + ' !important');
                    break;
                case 'themeFieldBackground':
                    $('.fcc .el fieldset:not(.option-container):not(.range):not(.file):not(.picture), .fcc .el textarea.text, .fcc .el input[prop=placeholderText], .fcc .el input.static, .fcc .el fieldset select, .fcc .el canvas.signature-pad')
                    .css('background', $(this).val() + ' !important');
                    break;
                case 'themeFieldBorder':
                    $('.fcc .el fieldset:not(.option-container):not(.range):not(.file):not(.picture), .fcc .el textarea.text, .fcc .el fieldset select, .fcc .el canvas.signature-pad')
                    .css('boxShadow', '0 0 0 1px ' + $(this).val() + ' !important');

                    $('.fcc .el fieldset.option-container i:not(.fm-icon-close-thick)').css('border', '1px solid ' + $(this).val() + ' !important');
                    break;
                case 'themeSubmitButton':
                    $('.fcc span.button, .fcc .file button[type=button]').css('background', $(this).val() + ' !important');
                    break;
                case 'themeSubmitButtonText':
                    $('.fcc span.button, .fcc .file button[type=button]').css('color', $(this).val() + ' !important');
                    break;
                case 'themeText':
                    $('.fcc, .fcc .el fieldset, .fcc .ellist .el textarea:not(.text), .fcc .ellist .el div.div-textarea, .fcc .el .option input.inline-edit').css('color', $(this).val() + ' !important');
                    break;
                case 'themeDescriptionText':
                    $('.fcc #description').css('color', $(this).val() + ' !important');
                    break;
                case 'themeFieldText':
                    $('.fcc .el [prop="placeholderText"]').css('color', $(this).val() + ' !important');
                    break;
                case 'themeEnabled':
                    setTimeout(function() {
                        window.location.reload();
                    }, 500);
                    break;
                default:
                    break;
            }


        }

        if((prop=="option_label")||(prop=="option_value")){
        	var list_type = $(this).closest('.optionsList').attr('prop');
            var tid=$(this).parent().attr("prop");
            var el_prop = $(this).closest('div[class=optionsList]');
            if(el_prop.attr('prop') == 'optionsList' && et == 'PRODUCTS') {
            	et = 'QTY';
            }
            var value=$(this).val();
            var val = value;
            if(et=='SELECT'){
                if(!$("#f"+gd.element+" [class=optionsList] [oid="+tid+"] ").is("option")){
                  $("#f"+gd.element+" [class=optionsList]").append($('<option>',{oid:tid}));
                }
            } else if(et=="CHECKBOX" || et=="RADIO" || et=="SWITCH" || et=="PRODUCTS" || et=="INPUTTABLE") {

                if(prop=='option_value') {
                    var label = $(this).prev();
                    if(label.val() != '') {
                        val = label.val();
                    }
                }

                var option_index = tid.slice(1);
                var option_container = $("#f"+gd.element).find('[class="option_container"]');
                if(et == 'INPUTTABLE') {
                	if(list_type == 'answerList') {
                		var option_selected = option_container.find('tr.ans td.gray').get(option_index);
                		if(option_selected) {
                			$(option_selected).html('<label class="option"><input type="text" class="inline-edit input-text table-input" value="'+val+'"></label><div class="del"><i class="icon-trash"></i></div>');
                		} else {
                			option_container.find('tr.ans').append($('<td class="gray"><label class="option"><input type="text" class="inline-edit input-text table-input" value="'+val+'"></label><div class="del"><i class="icon-trash"></i></div></td>'));
                			var rows = option_container.find('tr.foption');
                			rows.each(function() {
                				$(this).append($('<td class="ans"><input type="radio" disabled="" class="radio_ans"></td>'));
                			});
                		}
                	} else if(list_type == 'questionList') {
                		var option_selected = option_container.find('tr.foption').get(option_index);
                		if(option_selected) {
                			$(option_selected).find('td.gray input').val(val);
                		} else {
                			var copy = option_container.find('tr.foption').get(option_index - 1);
                			if(copy) {
                				copy = $(copy).clone();
	                			copy.find('td.gray input').val(val);
                			} else {
                				copy = $('<tr class="foption option-container trow"><td class="gray"><label class="option"><input type="text" class="inline-edit input-text table-input"></label><div class="del"><i class="icon-trash"></i></div></td></tr>');
                			}
                			copy.insertBefore(option_container.find('tr.new'));
                		}
                	}
                } else {
                	var option_selected = option_container.find('div').not('div.new').not('div.other').get(option_index);
	                if(option_selected == undefined) {
	                	var option_selected = option_container.find('tr.trow').not('div.new').not('div.other').get(option_index);
	                }
	                if(option_selected) {
	                    $(option_selected).find('input[type=text]').val(val);
	                    if(et=='PRODUCTS' && prop=="option_value") {
	                    	var amount = $(this).val() ? $(this).val():0;
	                    	$(option_selected).find('.price .amount').html(amount);

	                    	var select_container = $("#f"+gd.element).find('.select select');
	                    	var option = select_container.find('option').get(parseInt(option_index)+1);
	                    	$(option).html(val + ' (<span class="symbol">$</span>' +amount+ ' <span class="currency">'+gd.fd.currency+'</span>)');
	                    }
	                    if(et=='PRODUCTS' && prop=="option_label") {
	                    	var amount = $(this).next();
	                    	var amount = amount.val() ? amount.val():0;

	                    	var select_container = $("#f"+gd.element).find('.select select');
	                    	var option = select_container.find('option').get(parseInt(option_index)+1);
	                    	$(option).html(val + ' (<span class="symbol">$</span>' +amount+ ' <span class="currency">'+gd.fd.currency+'</span>)');
	                    }
	                } else {
	                	var input = $('<input type="text" class="inline-edit input-text" />').val(value);
	                	var input_product = $('<input type="text" class="inline-edit input-text product-input" />').val(value);
	                    var input_switch = $('<input type="text" class="inline-edit input-text" style="margin-left:30px;width: 94%;" />').val(value);
	                    if(et=="CHECKBOX") {
	                        var option_template = $('<div class="foption">\
	                            <fieldset class="option-container">\
	                                <label class="option">\
	                                    <input type="checkbox" value="1" />\
	                                    <i></i>\
	                                </label>\
	                                <button class="inline-edit red">\
	                                    <i class="fm-icon-close-thick"></i>\
	                                </button>\
	                            </fieldset>\
	                        </div>');

	                        option_template.find('.option').append(input);
	                    } else if(et=='PRODUCTS') {
	                    	var amount = 0;
	                    	if(prop=="option_value") {
	                    		amount = $(this).val();
	                    	}

	                        var option_template = $('<tr class="foption option-container trow">\
	          					<td>\
	                                <label class="option">\
	                                    <input type="checkbox" />\
	                                    <i></i>\
	                                </label>\
	                                <span class="price">(<span class="symbol">$</span><span class="amount">'+amount+'</span> <span class="currency">'+gd.fd.currency+'</span>)</span>\
	                            </td>\
	                            <td class="tamt">\
	                            	<span class="tcell"><select><option class="default_value_amt">Quantity</option></select></span>\
	                            </td>\
	                            <td class="tbtn">\
	                                <button class="inline-edit red">\
	                                    <i class="fm-icon-close-thick"></i>\
	                                </button>\
	                            </td>\
	                        </tr>');

	                        option_template.find('.option').append(input_product);

	                        var select_container = $("#f"+gd.element).find('.select select');
	                        var option_template1 = $('<option>'+val+' (<span class="symbol">$</span>'+amount+' <span class="currency">'+gd.fd.currency+'</span>)</option>');
	                        select_container.append(option_template1);

	                        /*resize the input for product elements*/
					        $("input.product-input").each(function() {
					        	$(this).attr({width: 'auto', size: $(this).val().length});
					        });
					        $("input.product-input").change(function() {
					        	$(this).attr({width: 'auto', size: $(this).val().length});
					        });
	                    } else if(et=="SWITCH") {
	                    	var sw_container = option_container.find('div.new').find('.switch-container');
	                        var onLabel = sw_container.find('.on').html();
	                        var offLabel = sw_container.find('.off').html();

	                        var option_template = $('<div class="foption">\
	                            <fieldset class="option-container">\
	                                <label class="option switch">\
	                                    <input type="checkbox" value="1" />\
	                                    <span class="switch-container"><span class="switch-status on">'+onLabel+'</span><span class="switch-status off">'+offLabel+'</span><i></i></span>\
	                                </label>\
	                                <button class="inline-edit red">\
	                                    <i class="fm-icon-close-thick"></i>\
	                                </button>\
	                            </fieldset>\
	                        </div>');
	                        option_template.find('.option').append(input_switch);

	                    } else {
	                        var option_template = $('<div class="foption">\
	                            <fieldset class="option-container">\
	                                <label class="option">\
										<input type="radio" name="radio_'+id+'" value="1">\
	                                    <i></i>\
	                                </label>\
	                                <button class="inline-edit red">\
	                                    <i class="fm-icon-close-thick"></i>\
	                                </button>\
	                            </fieldset>\
	                        </div>');
	                        option_template.find('.option').append(input);
	                    }

	                    //option_container.append(option_template);
	                    option_template.insertBefore(option_container.find('div.new'));
	                }
                }


            } else if(et=='QTY') {
            	var option_index = tid.slice(1);
            	if(prop=='option_label') {
            		var select_container = $("#f"+gd.element).find('.tcell select');
            		select_container.each(function() {
						var option = $(this).find('option').get(parseInt(option_index)+1);
                		$(option).html(val);
            		});
            	}
            }
            if(prop=="option_label"&&(et!="RADIO")&&(et!="CHECKBOX")&&(et!="PRODUCTS")) {
                $("#f"+gd.element+" [class=optionsList] [oid="+tid+"] ").html(value);
            } else {
                $("#f"+gd.element+" [class=optionsList] [oid="+tid+"] ").attr("value",value);
            }
            var prop=prop+"_"+tid;

			Ui.determineElementNeedsUpdate();

        } else if(prop=="submitButtonText" || prop=="previousButtonText" || prop=="nextButtonText") {
            var value=$(this).val();
            var fieldset = $(this).closest('fieldset');
            if(fieldset.hasClass('customFormButton')) {
            	var pageId = fieldset.data('pageid');
            	$('[data-page="'+pageId+'"]').find('[data-prop='+prop+']').html(value);
            	req.page = pageId;
            }
            $(".ellist .footer .button." + prop).html($(this).val());
        } else if(prop=="footerPaginationPageText" || prop=="footerPaginationOfText") {
        	var value=$(this).val();
        	$(".ellist .footer ." + prop).html($(this).val());
        } else if(prop=="fileButtonLabel") {
        	var value=$(this).val();
        	var el = $(document).find("#f" + id);
        	el.find('.editable').html(value);
        } else if(prop=="onLabel" || prop=="offLabel") {
        	var value=$(this).val();
        	var sw_container = $("#f" + id).find('.switch-container');
        	if(prop=="onLabel") {
        		sw_container.find('.on').html(value);
        	} else {
        		sw_container.find('.off').html(value);
        	}
        } else if(prop=="rangeMax") {
            var value=$(this).val();
            var container = $("#f" + id).find('.output-container');
            container.find('span').html(value);
        } else if(prop=='otherOptionLabel') {
        	var value=$(this).val();
    		var el = $(document).find("#f" + id);
    		el.find('.otherText').html(value);
        } else if(prop=='unit') {
        	var el = $(document).find("#f" + id);
        	var side = $(document).find("#s" + id);
        	var value=$(this).val();
        	el.find('[et=products]').attr('data-unit', $(this).val());
        } else if(prop=='enableAmountLabel') {
        	var el = $(document).find("#f" + id);
        	var value=$(this).val();
        	el.find('.default_value_amt').html($(this).val());
        } else if(prop=='textMaxLength') {
        	var el = $(document).find("#f" + id);
        	var value=$(this).val();
        	var container = el.find('.rcContainer');
        	if(value) {
        		container.show();
                container.find('.maxChar').html(value);
        	} else {
        		container.hide();
        	}
        } else if(prop=='textAreaHeight') {
        	var el = $(document).find("#f" + id);
        	var value=$(this).val();
        	var height = 96;
        	if(value) {
	            var height = value;
	            var $el = el.find('textarea[prop=placeholderText]');
	            $el.css('height', height + 'px');
        	}
        } else if(prop=='width') {
        	var el = $(document).find("#f" + id);
        	var value=$(this).val();
        	if(value) {
	            var canvasC = el.find('.canvasC');
	            canvasC.css('width', value + 'px');
	            var canvas = canvasC.find('canvas');
	            canvas.css('width', value + 'px');
        	}
        } else if(prop=='height') {
        	var el = $(document).find("#f" + id);
        	var value=$(this).val();
        	if(value) {
	            var canvasC = el.find('.canvasC');
	            canvasC.css('height', value + 'px');
	            var canvas = canvasC.find('canvas');
	            canvas.css('height', value + 'px');
        	}
        } else if(prop=='clearLabel') {
        	var el = $(document).find("#f" + id);
        	var value=$(this).val();
        	el.find('.actions .clear').html(value);
        } else if(prop=='fileType') {
			var el = $(document).find("#f" + id);
			var side = $(document).find("#s" + id);
			var value=$(this).val();

			var split = value.split('/');
			if(split[0] == 'image') {
				side.find('.image_dimension').show();
			} else {
				side.find('.image_dimension').hide();
			}
		} else if(prop=='datasource_id') {
			var value=$(this).val();
			location.reload();
		} else if(prop=='captureLabel') {
        	var el = $(document).find("#f" + id);
        	el.find('.capture').html($(this).val());
			var value=$(this).val();
        } else if(prop=='stripeType') {
			var side = $(document).find("#s" + id);
			var value=$(this).val();
			side.find('[class^=show_stripeType').hide();
			side.find('[class=show_stripeType_'+value).show();
			setTimeout(function() {
				Ui.activateEl(false, id);
			}, 500);
		} else if(prop=='inputtype') {
			var value=$(this).val();
			var el = $(document).find("#f" + id);
			el.find('.radio_ans').attr('type', value);
		} else if(prop=='textSize') {
			var side = $(document).find("#s" + id);
			var value=$(this).val();
			var el = $(document).find("#f" + id);

			el.find('textarea.ed').removeClass('hh1');
			el.find('textarea.ed').removeClass('hh2');
			el.find('textarea.ed').removeClass('hh3');
			el.find('textarea.ed').removeClass('hh4');
			el.find('textarea.ed').removeClass('hh5');
			el.find('textarea.ed').removeClass('hh6');

			if(value) {
				el.find('textarea.ed').addClass('h'+value);
			}
		} else if(prop=='passwordLabel' || prop=='passwordButtonLabel') {
			var value=$(this).val();
			var el = $(document).find("#esettingspassword");

			if(prop=='passwordLabel') {
				el.find('textarea[prop="passwordLabel"]').val(value);
			} else if(prop=='passwordButtonLabel') {
				el = $(document).find('.passwordContainer .footer');
				el.find('[data-prop="passwordButtonLabel"]').html(value);
			}
		} else {

			var exceptProp = prop != 'use12Notation' && prop != 'dM' && prop != 'dT' && prop != 'dW' && prop != 'dTH' && prop != 'dF' && prop != 'dSat' && prop != 'dSun';
            if($(this).attr('type')=="checkbox" && exceptProp){
                var value=$(this).attr("checked");
                if(value==true){
                	$('.show_'+prop+'_0').hide();
                	$('.show_'+prop+'_1').show();
                	if(prop=='middleName') {
                		var el = $(document).find("#f" + id);
                		var side = $(document).find("#s" + id);
                		if(side.find('.nameTitle').is(":checked")) {
                			el.find('.firstname').removeClass('g6').removeClass('g5').addClass('g4');
                			el.find('.lastname').removeClass('g6').removeClass('g5').addClass('g4');
                		} else {
                			el.find('.firstname').removeClass('g6').removeClass('g4').addClass('g5');
                			el.find('.lastname').removeClass('g6').removeClass('g4').addClass('g5');
                		}
                		el.find('.middlename').show();
                	} else if(prop=='nameTitle') {
                		var el = $(document).find("#f" + id);
                		var side = $(document).find("#s" + id);
                		if(side.find('.middleName').is(":checked")) {
                			el.find('.firstname').removeClass('g6').removeClass('g5').addClass('g4');
                			el.find('.lastname').removeClass('g6').removeClass('g5').addClass('g4');
                		} else {
                			el.find('.firstname').removeClass('g6').removeClass('g4').addClass('g5');
                			el.find('.lastname').removeClass('g6').removeClass('g4').addClass('g5');
                		}
                		el.find('.titlename').show();
                	} else if(prop=='country') {
                		var el = $(document).find("#f" + id);
                		el.find('.countrySelect').show();
                	} else if(prop=='iconEnabled') {
                		var el = $(document).find("#f" + id);
                		$(document).find('#s' +id+ ' .show_iconEnabled_1').show();
                		el.find('fieldset').addClass('inline-edit-container');
                		el.find('fieldset').find('i').show();
                	} else if(prop=='disabled' && $(document).find('#s' +id+ ' input[prop="required"]').is(":checked")) {
                		$(document).find('#s' +id+ ' label[prop="required"]').click();
						$(document).find('#f' +id+ ' button[prop="required"]').removeClass('active');
                	} else if(prop=='required' && $(document).find('#s' +id+ ' input[prop="disabled"]').is(":checked")) {
                		$(document).find('#s' +id+ ' label[prop="disabled"]').click();
                		$(document).find('#f' +id+ ' button[prop="required"]').addClass('active');
                	} else if(prop=='required') {
						$(document).find('#f' +id+ ' button[prop="required"]').addClass('active');
					} else if(prop=='otherOption') {
                		var el = $(document).find("#f" + id);
                		el.find('.other').show();
                	} else if(prop=='customCSS') {
                		value = 1;
                		if($(document).find('#s_themeEnabled').is(":checked")) {
                			setTimeout(function() {
                				$(document).find('#s_themeEnabled').click();
                			}, 300);
                		}

                		$(document).find('.themeEnabled').hide();
                		$(document).find('.warning_message_custom_css').show();
                	} else if(prop=='themeEnabled') {
                		if($(document).find('#s_customCSS').is(":checked")) {
                			setTimeout(function() {
                				$(document).find('#s_customCSS').click();
                			}, 300);
                		}
                	} else if(prop=='useSelect') {
                		var el = $(document).find("#f" + id);
                		el.find('.selectContainer').show();
                		el.find('.option_container').hide();
                	} else if(prop=='enableAmount') {
                		var el = $(document).find("#f" + id);
                		el.find('.tamt').show();
                		el.find('.selectContainer').addClass('hasQty');
                	} else if(prop=='enableLogic') {
	                	var error = false;
	                	var sbar = $(document).find('#s'+gd.element);
	                	et = $(document).find('#s'+gd.element).attr('et');

						$(document).find('#s'+gd.element).find('[prop="logicAction"]').trigger('change');

	                	if((accountStatus=='FREE' || accountStatus=='PREVIEW') && gd.fd.active=='1') {
	                		Ui.alert('error', 'Please use unpublished form or <a href="/settings/subscription/">upgrade your account</a> to enable Conditional Display');
	                		error = true;
	                	}

	                	if(error) {
	                		setTimeout(function() {
		                		sbar.find("#s_"+prop).next(".switch-container").click();
		                	}, 100);
	                	}
	                } else if(prop=='hidden') {
						var el = $(document).find("#f" + id);
						el.find('.nohide').hide();
						el.find('.hide').show();
					} else if(prop=='captureCard') {
						var el = $(document).find("#f" + id);
						el.find('.capture').show();
						el.find('.notcapture').hide();
					}

					if(prop!='required') {
						$(".selected [prop="+prop+"]").addClass("active");
					}
                } else {
                	$('.show_'+prop+'_0').show();
                	$('.show_'+prop+'_1').hide();
                	if(prop=='middleName') {
                		var el = $(document).find("#f" + id);
                		var side = $(document).find("#s" + id);
                		if(side.find('.nameTitle').is(":checked")) {
                			el.find('.firstname').removeClass('g6').removeClass('g4').addClass('g5');
                			el.find('.lastname').removeClass('g6').removeClass('g4').addClass('g5');
                		} else {
                			el.find('.firstname').removeClass('g4').removeClass('g5').addClass('g6');
                			el.find('.lastname').removeClass('g4').removeClass('g5').addClass('g6');
                		}
                		el.find('.middlename').hide();
                	} else if(prop=='nameTitle') {
                		var el = $(document).find("#f" + id);
                		var side = $(document).find("#s" + id);
                		if(side.find('.middleName').is(":checked")) {
                			el.find('.firstname').removeClass('g6').removeClass('g4').addClass('g5');
                			el.find('.lastname').removeClass('g6').removeClass('g4').addClass('g5');
                		} else {
                			el.find('.firstname').removeClass('g4').removeClass('g5').addClass('g6');
                			el.find('.lastname').removeClass('g4').removeClass('g5').addClass('g6');
                		}
                		el.find('.titlename').hide();
                	} else if(prop=='country') {
                		var el = $(document).find("#f" + id);
                		el.find('.countrySelect').hide();
                	} else if(prop=='iconEnabled') {
                		var el = $(document).find("#f" + id);
                		$(document).find('#s' +id+ ' .show_iconEnabled_1').hide();
                		el.find('fieldset').removeClass('inline-edit-container');
                		el.find('fieldset').find('i').hide();
                	} else if(prop=='otherOption') {
                		var el = $(document).find("#f" + id);
                		el.find('.other').hide();
                	} else if(prop=='customCSS') {
                		value = 0;
                		$(document).find('.themeEnabled').show();
                		$(document).find('.warning_message_custom_css').hide();
                	} else if(prop=='useSelect') {
                		var el = $(document).find("#f" + id);
                		el.find('.selectContainer').hide();
                		el.find('.option_container').show();
                	} else if(prop=='enableAmount') {
                		var el = $(document).find("#f" + id);
                		el.find('.tamt').hide();
                		el.find('.selectContainer').removeClass('hasQty');
                	} else if(prop=='required') {
						$(document).find('#f' +id+ ' button[prop="required"]').removeClass('active');
					} else if(prop=='hidden') {
						var el = $(document).find("#f" + id);
						el.find('.nohide').show();
						el.find('.hide').hide();
					} else if(prop=='captureCard') {
						var el = $(document).find("#f" + id);
						el.find('.capture').hide();
						el.find('.notcapture').show();
					}

					if(prop!='required') {
                    	$(".selected [prop="+prop+"]").removeClass("active");
					}
                }
            } else {
            	if(prop == 'customValidationType') {
            		var side = $("#s" + id);
            		var vm = side.find('.validationMessage');
            		var vm_input = vm.find('input');
            		if($(this).val() != 'NONE') {
            			vm.show();
            			if(vm_input.val() == '') {
            				vm_input.val('This must be a valid value.');
            				vm_input.trigger('change');
            			}

            			if($(this).val() != 'REGEX') {
            				side.find('.customValidationType_REGEX').hide();
            			}


            		} else {
            			vm.hide();
            		}
            	} else if(prop == 'width' || prop == 'height') {
            		var image_container = $("#f" + id).find('.image_container');
            		var image = image_container.find('img');
            		var side = $("#s" + id);
            		var width = side.find('input[prop=width]').val();
            		var height = side.find('input[prop=height]').val();
            		if(prop == 'width') {
            			width = $(this).val();
            		}
            		if(prop == 'height') {
            			height = $(this).val();
            		}

            		if(width) {
            			image.css('width', width);
            		} else {
            			image.css('width', 'auto');
            		}
            		if(height) {
            			image.css('height', height);
            		} else {
            			image.css('height', 'auto');
            		}

            		if(!width && !height) {
            			image.css('width', '100%');
            		}

            		image.css('maxWidth', '100%');
            	} else if(prop == 'iconName') {
            		var el = $(document).find("#f" + id);
            		var fieldset = el.find('fieldset');
            		if(fieldset.find('i').length) {
            			var i = fieldset.find('i');
            			var allClass = i.attr('class');
            			var existingClass = allClass.slice(3);
            			i.removeClass(existingClass);
            			i.addClass($(this).val());
            		} else {
            			fieldset.append('<i class="fa '+$(this).val()+'"></i>');
            		}
            	} else if(prop == 'customValidationType') {
            		$("#f" + id).attr('validation-type', $(this).val());
            	} else if(prop=='format') {
            		var el = $(document).find("#f" + id);
            		if($(this).val()=="OTHER") {
            			el.find('.state_text').show();
            			el.find('.state_select').hide();
            		} else {
						el.find('.state_text').hide();
            			el.find('.state_select').show();
            		}
            	} else if(prop=='themeID') {
            		setTimeout(function() {
            			window.location.reload();
            		}, 500);
            	} else if(prop=='dateFormat' || prop=='interval' || prop=='beginDate' || prop=='endDate' || prop == 'use12Notation' || prop == 'dM' || prop == 'dT' || prop == 'dW' || prop == 'dTH' || prop == 'dF' || prop == 'dSat' || prop == 'dSun') {
            		var fieldset = $(document).find("#f" + id);
                	var side = $(document).find("#s" + id);
                	var $el = fieldset.find('input[prop=placeholderText]');
                	var interval = side.find('input[prop=interval]').val();
					var elFormat = side.find('[prop=dateFormat]').val();
                	var hour12 = side.find('input[prop=use12Notation]').is(":checked");
                	var minDate = null;
                	var maxDate = null;
					var format = window.user_date_format;

					if(elFormat) {
						format = Utils.getDateFormat(elFormat);
					}

                	if($el.hasClass('datetimePicker')) {
                		var noCalendar = false;
                		var enableTime = true;
                		var bdate = side.find('input[prop=beginDate]').val();
                		var edate = side.find('input[prop=endDate]').val();
                		if(bdate) {
                			minDate = new Date(window.strtotime(bdate) * 1000);
                		}
                		if(edate) {
                			maxDate = new Date(strtotime(edate) * 1000);
                		}
                	} else if($el.hasClass('datePicker')) {
                		var noCalendar = false;
                		var enableTime = false;
                		var bdate = side.find('input[prop=beginDate]').val();
                		var edate = side.find('input[prop=endDate]').val();
                		if(bdate) {
                			minDate = new Date(window.strtotime(bdate) * 1000);
                		}
                		if(edate) {
                			maxDate = new Date(strtotime(edate) * 1000);
                		}
                	} else {
                		var enableTime = true;
                		var noCalendar = true;
                	}

					var disabledDays = [];
					if($el.hasClass('datetimePicker') || $el.hasClass('datePicker')) {
						if(side.find('input[prop=dM]').is(":checked")) { disabledDays.push(1); }
						if(side.find('input[prop=dT]').is(":checked")) { disabledDays.push(2); }
						if(side.find('input[prop=dW]').is(":checked")) { disabledDays.push(3); }
						if(side.find('input[prop=dTH]').is(":checked")) { disabledDays.push(4); }
						if(side.find('input[prop=dF]').is(":checked")) { disabledDays.push(5); }
						if(side.find('input[prop=dSat]').is(":checked")) { disabledDays.push(6); }
						if(side.find('input[prop=dSun]').is(":checked")) { disabledDays.push(0); }
					}

                	if(hour12 == true) {
						var time_24hr = false;
						if(noCalendar) {
							var format = 'h:i K';
						} else {
							var format = format + ' h:i K';
						}
                	} else if(enableTime) {
                		var time_24hr = true;
                		if(noCalendar) {
                			var format = 'H:i';
                		} else {
                			var format = format + ' H:i';
                		}
                	}

                	setTimeout(function() {
                		Ui.makeElementDateOrTime($el, {
	                		dateFormat:format,
							noCalendar:noCalendar,
							enableTime: enableTime,
							minuteIncrement:interval,
							time_24hr:time_24hr,
							minDate:minDate,
							maxDate:maxDate,
							allowInput: true,
							appendTo:$el.closest('fieldset').get(0),
							onClose: function(selectedDates, dateStr, instance) {
								$(instance.input).blur();
							},
							"disable": [
                                function(date) {
									return disabledDays && disabledDays.indexOf(date.getDay().toString()) !== -1;
                                }
                            ],
						    "locale": {
						        "firstDayOfWeek": 1 // start week on Monday
						    }
	                	});
                	}, 500);

            	}
                var value=$(this).val();
                if($(this).attr('type')=="checkbox") {
                	value=$(this).attr("checked");
                }
                // if($('.selected [prop='+prop+']').is("div") || $('.selected [prop='+prop+']').is("span")) {
                //     $('.selected [prop='+prop+']').html(value);
                // } else {
                    $('.selected [prop='+prop+']').val(value);
					$('.selected [prop='+prop+']').html(value);
                //}
            }
        }

        var temp_val = String(value).replace(/\W/g, '');
        $(document).find('fieldset.'+prop+'_'+temp_val).show();

        Ui.determineFormNeedsUpgrade();
		Ui.determineElementNeedsUpdate();
        Ui.determineProduct();
        Ui.determineCurrency();

        req.form_id=gd.object;
		if(prop == 'required') {
			req.el_id=id;
		} else {
			req.el_id=gd.element;
		}
        req.prop=prop;
        req.value=value;
        if(et) {
        	req.el_type=et;
        }
        if(list_type) {
        	req.list_type=list_type;
        }
        req.method="POST";

		if(d.type == 'keyup') {
			typingTimer = setTimeout(function() {
				Ui.submitReqChanges(req);
			}, doneTypingInterval);
		} else {
			Utils.reqdata('editFormElement',req);
			setTimeout(function() {
				Ui.endMove();
			}, 1000);
		}
    },
    duplicateformel:function(response) {
        response = response || false;
        if(!response._id) {
            var side = $(this).closest('.sel');
            var element_id = side.attr('id').slice(1);

            var req = {};
            req.form_id=gd.object;
            req.element_id=element_id;
            Utils.reqdata('getFormElement',req, Ui.duplicateformel);
        } else {
            var req = response;
            var old_id = 'f'+response._id;
            delete req._id;
            delete req.name;
            req.el_id = Utils.insertid();
            req.form_id=gd.object;
            req.method='POST';

			setTimeout(function() {
				$(document).find('#f'+req.el_id).mousedown();
			}, 500);

            $("#"+old_id).removeClass('selected');
            var cloned = $("#"+old_id).clone();
            var tpl = $('<div class="formline" id="line_copy"></div>');

            cloned.attr('id', 'f'+req.el_id);
            cloned.addClass('selected');

            if(req.type=='DATE' || req.type=='DATETIME' || req.type=='TIME') {
            	var $el = cloned.find('input[prop=placeholderText]');
            	$el.val(req.placeholderText);

            	var interval = req.interval ? req.interval:1;
            	var hour12 = req.use12Notation ? req.use12Notation:false;
            	var minDate = null;
            	var maxDate = null;
            	var format = window.user_date_format;

				if(req.dateFormat) {
					format = Utils.getDateFormat(req.dateFormat);
				}

            	if(req.type=='DATETIME') {
            		var noCalendar = false;
            		var enableTime = true;
            		var bdate = req.beginDate;
            		var edate = req.endDate;
            		if(bdate) {
            			minDate = new Date(window.strtotime(bdate) * 1000);
            		}
            		if(edate) {
            			maxDate = new Date(strtotime(edate) * 1000);
            		}
            	} else if(req.type=='DATE') {
            		var noCalendar = false;
            		var enableTime = false;
            		var bdate = req.beginDate;
            		var edate = req.endDate;
            		if(bdate) {
            			minDate = new Date(window.strtotime(bdate) * 1000);
            		}
            		if(edate) {
            			maxDate = new Date(strtotime(edate) * 1000);
            		}
            	} else {
            		var enableTime = true;
            		var noCalendar = true;
            	}

            	if(req.type=='DATETIME' || req.type=='TIME') {
            		if(hour12 == true) {
						var time_24hr = false;
						if(noCalendar) {
							var format = 'h:i K';
						} else {
							var format = format + ' h:i K';
						}
                	} else {
                		var time_24hr = true;
                		if(noCalendar) {
                			var format = 'H:i';
                		} else {
                			var format = format + ' H:i';
                		}
                	}
            	}

				var disabledDays = [];
				if(req.type=='DATETIME' || req.type=='DATE') {
					if(req.dM) { disabledDays.push(1); }
					if(req.dT) { disabledDays.push(2); }
					if(req.dW) { disabledDays.push(3); }
					if(req.dTH) { disabledDays.push(4); }
					if(req.dF) { disabledDays.push(5); }
					if(req.dSat) { disabledDays.push(6); }
					if(req.dSun) { disabledDays.push(0); }
				}

            	var defaultDate = null;
            	setTimeout(function() {
            		Ui.makeElementDateOrTime($el, {
                		dateFormat:format,
						noCalendar:noCalendar,
						enableTime: enableTime,
						minuteIncrement:interval,
						time_24hr:time_24hr,
						minDate:minDate,
						maxDate:maxDate,
						allowInput: true,
						defaultDate: defaultDate,
						appendTo:$el.closest('fieldset').get(0),
						onClose: function(selectedDates, dateStr, instance) {
							$(instance.input).blur();
						},
						"disable": [
							function(date) {
								return disabledDays && disabledDays.indexOf(date.getDay().toString()) !== -1;
							}
						],
						"locale": {
							"firstDayOfWeek": 1 // start week on Monday
						}
                	});
            	}, 500);
            }

            tpl.append(cloned);

            tpl.insertAfter($("#"+old_id).closest('.formline'));

            Utils.reqdata('editFormElement',req);

            setTimeout(function() {
                var container = $('.formElementContainer');
                var page_containers = container.find('.ellist');
                var $line_number = 1;
                var $new_line_number = 1;
                $.each(page_containers, function(i, $page_container) {
                    $page_container = $($page_container);
                    var $page = $page_container.data('page');
                    var $page_number = $page_container.attr('id').slice(13);

                    $.each($page_container.find('.formline'), function(i, line) {
                        if($(line).find('.el').length > 0) {
                            Ui.balanceformline($(line), $page, $line_number);
                            $line_number++;
                        }
                    });

                    setTimeout(function() {
                        //update their id
                        $.each($page_container.find('.formline'), function(i, line) {
                            $(line).attr('id', 'line'+$new_line_number);
                            $new_line_number++;
                        });

                        Ui.submitUpdateElements(window.elementsToBeUpdated);

                        //window.location.href = gd.path + 'editor/'+ gd.object+"/#e" + req.el_id;
                        //window.location.reload();
                        //
                        Ui.makeside(req.el_id, req.type);
                        req._id = req.el_id;
                        var el = $("#f"+req._id);
                        el.click();
                        var elm = el.closest('.formline');
                        Ui.makeElementSortable(elm.get(0));
                        Ui.elpopulate(req);

                    }, 100);

                });
            }, 100);

            setTimeout(function() {
				Ui.endMove();
			}, 1000);
        }
    },

	submitReqChanges: function(req) {
		Utils.reqdata('editFormElement',req);
	},

    updateformselected:function(d){

		if(d.type == 'keyup') {
			clearTimeout(typingTimer);
		}

    	if(d.which == 13 && $(this).hasClass('ed')) {
    		var str = $(this).val();
    		$(this).val(str.substring(0, str.length - 1));
    		return false;
    	}
        var id=$(this).closest('.el').attr("id").slice(1);
        var type = $(this).closest('.gc').attr('et');
        var prop=$(this).attr("prop");

        if(prop==null || prop=='' || ($(this).is("select") && prop=='optionsList')) {
            if($(this).closest('.option-container').length > 0 || $(this).closest('tr.ans').length > 0 || $(this).closest('fieldset.select').length > 0) {
            	//this means one of the options of the element has been changed
                var $div = $(this).closest('.option-container').parent();
                if($div.hasClass('option_container') || ($div.is("tbody") && $div.closest('.gc').attr('et') == 'products')) {
                	$div = $(this).closest('.option-container');
                }
                var option_index = $div.index();

				if($(this).attr('type') == 'checkbox' || $(this).attr('type') == 'radio' || $(this).is("select")) {
					if($(this).is("select")) {
						option_index = $(this).val();
					}
					var prop = 'option_default_o' + option_index;
					var req = {};
	                req.form_id=gd.object;
	                req.el_id=id;
	                req.prop=prop;
					if($(this).attr('type') == 'checkbox') {
						if($(this).is(":checked")) {
							req.value='1';
						} else {
							req.value='0';
						}
					} else if($(this).attr('type') == 'radio') {

						if($(this).is(":checked")) {
							req.value='1';
						} else {
							req.value='0';
						}

						if($(this).val()) {
							$("#s" + id + ' .clearDefault').show();
						}
					} else {
						req.value = 1;
					}

	                req.el_type=type;
	                req.method="POST";

					Utils.reqdata('editFormElement',req);
				} else {
					if(type == 'inputtable') {
	                	option_index = option_index - 1;
	                	if(option_index < 0) {
	                		$div = $(this).closest('.gray');
	                		option_index = $div.index() - 1;
	                		var list_type = 'answerList';
	                	} else {
	                		var list_type = 'questionList';
	                	}
	                }
	                var side_options = $("#s" + id + ' div[class=optionsList]').first();
	                if(type == 'inputtable') {
	                	side_options = $("#s" + id + ' fieldset[class='+list_type+']').find('div[class=optionsList]');
	                }
	                var option_selected = side_options.find('fieldset[prop=o'+option_index+']');
	                if(option_selected.length == 0) {
	                    var new_option = $('<fieldset prop="o'+option_index+'">\
	                        <input class="g5 text small dark" prop="option_label">\
	                        <input class="g5 text small dark marleft" prop="option_value">\
	                        <button class="right inline-edit delete" tabindex="-1"><i class="icon-trash"></i></button>\
	                        <button class="right inline-edit moveup" tabindex="-1"><i class="icon-arrow-up"></i></button>\
	                        <button class="right inline-edit movedown" tabindex="-1"><i class="icon-arrow-down"></i></button>\
	                    </fieldset>');

	                    if($(this).val()!='') {
	                    	side_options.append(new_option);
	                    }

	                    option_selected = new_option;

	                    $(this).on("blur keydown", function(event) {
	                    	if(event.which != 13 && event.which != 0) {
	                    		return;
	                    	}

	                        if($(this).val()) {
	                            var type = $(this).closest('.gc').attr('et');
	                            var input = $('<input type="text" class="inline-edit input-text" />').val($(this).val());
		                            var input_product = $('<input type="text" class="inline-edit input-text product-input" />').val($(this).val());
	                    		var input_switch = $('<input type="text" class="inline-edit input-text" style="margin-left:30px;width: 94%;" />').val($(this).val());

	                            var option_container = $(this).closest('.option_container');

	                            if(type=='checkbox') {
	                                var option_template = $('<div class="foption">\
	                                    <fieldset class="option-container">\
	                                        <label class="option">\
	                                            <input type="checkbox" value="1" />\
	                                            <i></i>\
	                                        </label>\
	                                        <button class="inline-edit red">\
	                                            <i class="fm-icon-close-thick"></i>\
	                                        </button>\
	                                    </fieldset>\
	                                </div>');

	                                option_template.find('.option').append(input);
	                            } else if(type=='products') {
	                                var option_template = $('<tr class="foption option-container trow">\
	                  					<td>\
	                                        <label class="option">\
	                                            <input type="checkbox" />\
	                                            <i></i>\
	                                        </label>\
	                                        <span class="price">(<span class="symbol">$</span><span class="amount">0</span> <span class="currency">'+gd.fd.currency+'</span>)</span>\
	                                    </td>\
	                                    <td class="tamt">\
	                                    	<span class="tcell"><select><option class="default_value_amt">Quantity</option></select></span>\
	                                    </td>\
	                                    <td class="tbtn">\
	                                        <button class="inline-edit red">\
	                                            <i class="fm-icon-close-thick"></i>\
	                                        </button>\
	                                    </td>\
	                                </tr>');

	                                option_template.find('.option').append(input_product);

	                                var select_container = $("#f"+gd.element).find('.select select');
			                        var option_template1 = $('<option>'+$(this).val()+' (<span class="symbol">$</span>0 <span class="currency">'+gd.fd.currency+'</span>)</option>');
			                        select_container.append(option_template1);

	                                /*resize the input for product elements*/
							        $("input.product-input").each(function() {
							        	$(this).attr({width: 'auto', size: $(this).val().length});
							        });

							        Ui.determineProduct();
							        Ui.determineCurrency();
	                            } else if(type=='switch') {
	                            	var sw_container = option_container.find('div.new').find('.switch-container');
	                        		var onLabel = sw_container.find('.on').html();
	                        		var offLabel = sw_container.find('.off').html();

	                                var option_template = $('<div class="foption">\
	                                    <fieldset class="option-container">\
	                                        <label class="option switch">\
	                                            <input type="checkbox" value="1" />\
	                                            <span class="switch-container"><span class="switch-status on">'+onLabel+'</span><span class="switch-status off">'+offLabel+'</span><i></i></span>\
	                                        </label>\
	                                        <button class="inline-edit red">\
	                                            <i class="fm-icon-close-thick"></i>\
	                                        </button>\
	                                    </fieldset>\
	                                </div>');

	                                option_template.find('.option').append(input_switch);
	                            } else {
	                                var option_template = $('<div class="foption">\
	                                    <fieldset class="option-container">\
	                                        <label class="option">\
												<input type="radio" name="radio_'+id+'" value="1">\
	                                            <i></i>\
	                                        </label>\
	                                        <button class="inline-edit red">\
	                                            <i class="fm-icon-close-thick"></i>\
	                                        </button>\
	                                    </fieldset>\
	                                </div>');

	                                option_template.find('.option').append(input);
	                            }


	                            var this_element = $(option_container).find('div.new');
	                            option_template.insertBefore($(option_container).find('div.new'));

	                            this_element.find('input').val('');
	                        }
	                    });
	                }
	                option_selected.find('input[prop=option_label]').val($(this).val());

	                var prop = 'option_label_o' + option_index;
	                var req = {};
	                req.form_id=gd.object;
	                req.el_id=id;
	                req.prop=prop;
	                req.value=$(this).val();
	                req.el_type=type;
	                req.method="POST";
	                if(list_type) {
	                	req.list_type=list_type;
	                }
	                if(req.value!='') {

						if(d.type == 'keyup') {
							typingTimer = setTimeout(function() {
								Ui.submitReqChanges(req);
							}, doneTypingInterval);
						} else {
							Utils.reqdata('editFormElement',req);
						}

	                }

	                Ui.modifyOptionButtons(side_options.find('fieldset'));
				}
            }
        } else {
            if($(this).is("div")){
                var value=$(this).html();
            } else {
              var value=$(this).val();
            }
            $('#s'+id+' [prop='+prop+']').val(value);
            var req = {};
            req.form_id=gd.object;
            req.el_id=id;
            req.prop=prop;
            req.value=value;
            req.method="POST";

			if(d.type == 'keyup') {
				typingTimer = setTimeout(function() {
					Ui.submitReqChanges(req);
				}, doneTypingInterval);
			} else {
				Utils.reqdata('editFormElement',req);
			}
        }
    },
    addoption:function(){
    	var newid="o"+$(this).parent().prev('div[class=optionsList]').find('fieldset').length;
    	var type="text";
    	if($(this).closest(".sel").attr('et') == 'PRODUCTS') {
    		type="number";
    	}
    	if($(this).closest(".sel").attr('et') == 'INPUTTABLE') {
    		var optionfield=$('<fieldset prop="'+newid+'"><input type="text" class="g10 text small dark" prop="option_label"><button class="right inline-edit delete" tabindex="-1"><i class="icon-trash"></i></button> <button class="right inline-edit moveup" tabindex="-1"><i class="icon-arrow-up"></i></button> <button class="right inline-edit movedown" tabindex="-1"><i class="icon-arrow-down"></i></button> </fieldset>');
    	} else {
    		var optionfield=$('<fieldset prop="'+newid+'"><input type="text" class="g5 text small dark" prop="option_label"><input type="'+type+'" class="g5 text small dark marleft" prop="option_value"><button class="right inline-edit delete"><i class="icon-trash"></i></button> <button class="right inline-edit moveup"><i class="icon-arrow-up"></i></button> <button class="right inline-edit movedown"><i class="icon-arrow-down"></i></button> </fieldset>');
    	}
      	var prop=$(this).attr("prop");
      	var newopt = $(this).parent().prev('div[class=optionsList]').append(optionfield);
      	optionfield.find('input[prop='+prop+']').focus();
      	Ui.modifyOptionButtons($(this).parent().prev("div[class=optionsList]").find("fieldset"));
    },
    changeoption:function(){
      	var oid=$(this).parent().attr("prop");
      	var el_type = $(this).closest('.sel').attr('et');

      	if ($(this).hasClass("delete")) {
      		var action="delete";
      	} else if ($(this).hasClass("moveup")) {
      		var action="moveup";
      	} else if ($(this).hasClass("movedown")) {
      		var action="movedown";
      	}

      	var $parent = $(this).closest('[class=optionsList]');
      	var list_type = $parent.attr('prop');

		if(el_type == 'PRODUCTS' && list_type=='optionsList') {
			el_type='QTY';
		}

      	Utils.reqdata('editFormElement',{form_id:gd.object,el_id:gd.element,prop:"option_label_"+oid,action:action,el_type:el_type, list_type:list_type});

      	var $fieldset = $(this).closest('fieldset');


      	var option_index = oid.slice(1);
        var option_container = $(document).find('#f'+gd.element+' [class="option_container"]');
        if(list_type == 'answerList') {
        	var option_selected = option_container.find('tr.ans td.gray').get(option_index);
        } else {
        	var option_selected = option_container.find('.foption').get(option_index);
        }

        var $option_selected = $(option_selected);

      	if(action == 'movedown') {
      		$fieldset.next('fieldset').after($fieldset);
      		if(list_type == 'answerList') {
      			$option_selected.next('.gray').after($option_selected);
      		} else if(el_type == 'QTY' && list_type=='optionsList') {
				//
			} else {
      			$option_selected.next('.foption').after($option_selected);
      		}
      	} else if(action == 'moveup') {
      		$fieldset.prev('fieldset').before($fieldset);
      		if(list_type == 'answerList') {
      			$option_selected.prev('.gray').before($option_selected);
      		} else if(el_type == 'QTY' && list_type=='optionsList') {
				//
			} else {
      			$option_selected.prev('.foption').before($option_selected);
      		}
      	} else if(action == 'delete') {
      		$fieldset.remove();
      		if(list_type == 'answerList') {
      			$option_selected.remove();
      			var rows = option_container.find('.foption');
      			rows.each(function() {
      				$(this).find('td.ans').first().remove();
      			});
      		} else if(el_type == 'QTY' && list_type=='optionsList') {
				//
			} else {
      			$option_selected.remove();
      		}
      	}

      	Ui.modifyOptionButtons($parent.find('fieldset'));
    },
    modifyOptionButtons: function(options) {
    	var ctr = 0;
      	$.each(options, function(i, f) {
      		$(f).attr('prop', 'o'+ctr);
      		$(f).find('button.moveup').show();
  			$(f).find('button.movedown').show();
  			if(i == 0) {
  				$(f).find('button.moveup').hide();
  			} else if(i == (options.length - 1)) {
  				$(f).find('button.movedown').hide();
  			}
      		ctr = ctr + 1;
      	});
    },
    createForm:function(ftype){
    	var request = window.location.href.replace(/#e/,'').split("/");
    	window.ftypeCreated = ftype;
    	if(ftype == 'ENDPOINT') {
    		var fname =$('#snew input#endpointname').val();
    	} else {
    		var fname =$('#snew input#formname').val();
    	}

        var req = {};
        var element = gd.element;
        if(element=='source' && request[6]!='') {
        	req.sourceform = request[6];
        }
        req.form_id=gd.object;
        req.name=fname;
        req.type=ftype;
        Utils.reqdata('createForm',req,Ui.formCreated); // request the data end send to output
    },
    formCreated:function(){
    	if(window.ftypeCreated == 'ENDPOINT') {
    		window.location.href = gd.path + 'editor/'+ gd.object+"/#eendpoint";
    	} else {
    		window.location.href = gd.path + 'editor/'+ gd.object+"/#eelements";
    	}
    	setTimeout(function() {
    		window.location.reload();
    	}, 500);
    },
    updateFormState: function() {
        var req = {};
        req.form_id=gd.object;
        req.update_type='state';
        var state = $("#formStateContainer #formState");
        if(state.hasClass("form-active")) {
            req.active = 0;
            gd.fd.active = 0;
        } else {
            req.active = 1;
            gd.fd.active = 1;
        }

        Utils.reqdata('saveForm',req,Ui.formStateUpdated);
    },
    formStateUpdated: function(response) {
        if(!response.updated) {
            Ui.alert('error', response.message);
        } else {
            Ui.alert('success', response.message);
        }

        var state = $("#formStateContainer #formState");
        if(response.active) {
            state.html("Unpublish");
            state.addClass("form-active");
        } else {
            state.html("Publish");
            state.removeClass("form-active");
        }
    },
    alert: function(type, message) {
        type = type || "error";
        message = message || "";

        $(".alert-container").addClass(type);
        $(".alert-container").html('<div class="content">'+message+'</div><span class="close"><i class="fa fa-times-circle"></i></span>');
        $(".alert-container").show();

        $(".alert-container").find('span.close').on("click", function() {
            $(".alert-container").removeClass("error");
            $(".alert-container").removeClass("success");
            $(".alert-container").html("");
            $(".alert-container").hide();
        });
    },
    createNewPage: function() {
        var req = {};
        req.form_id=gd.object;
        req.page=Utils.insertid();
        Utils.reqdata('saveFormPage',req,Ui.formPageCreated);
    },
    formPageCreated: function(response) {
        window.location.href = gd.path + 'editor/'+ gd.object+"/";
    },
    deletePage: function() {
        if(confirm('Are you sure you want to delete the page and it\'s elements?')) {
            var page = $(this).closest('.ellist');
            var page_id = page.data('page');

            var req = {};
            req.form_id=gd.object;
            req.page=page_id;
            req.action="delete";
            Utils.reqdata('saveFormPage',req,Ui.formPageDeleted);

            var container = page.closest('.formElementContainer');
            var page_containers = container.find('.ellist');
            var $line_number = 1;
            var $new_line_number = 1;
            $.each(page_containers, function(i, $page_container) {
                $page_container = $($page_container);
                var $page = $page_container.data('page');
                var $page_number = $page_container.attr('id').slice(13);

                $.each($page_container.find('.formline'), function(i, line) {
                    if($(line).find('.el').length > 0) {
                        Ui.balanceformline($(line), $page, $line_number);
                        $line_number++;
                    }
                });

                setTimeout(function() {
                    //update their id
                    $.each($page_container.find('.formline'), function(i, line) {
                        $(line).attr('id', 'line'+$new_line_number);
                        $new_line_number++;
                    });

                    page.remove();

                    window.location.reload();

                }, 100);

            });
        }
    },
    formPageDeleted: function(response) {
    },
    logoDeleted: function(response) {
    	$(".fc-logo").html("");
	    var slogoc = $("#ssettings").find('.logoContainer');
	    slogoc.find('.file').show();
	    slogoc.find('.formlogo').remove();
    },
    makeElementDateOrTime: function(el, config) {
    	config = config || null;
    	el.flatpickr(config);
    },
    determineProduct: function() {
    	setTimeout(function() {
	    	$(document).find('[et=products]').not('[data-unit=currency').find('span.symbol').html('');
	        $(document).find('[et=products]').not('[data-unit=currency').each(function() {
	        	$(this).find('span.currency').html($(this).data('unit'));
	        	var id = $(this).closest('.el').attr('id').slice(1);
	        	$("#s"+id).find('.product_unit').html($(this).data('unit'));
	        });

	        //for qty
	        $(document).find('[et=products]').each(function() {
	        	var id = $(this).closest('.el').attr('id').slice(1);
	        	var side = $("#s"+id);
	        	if(side.find('[prop=enableAmount]').is(":checked")) {
	        		$(this).find('td.tamt').show();
	        	} else {
	        		$(this).find('td.tamt').hide();
	        	}
	        });
    	}, 100);
    },
    determineCurrency: function() {
    	setTimeout(function() {
	    	var settings = $(document).find("#ssettings");
	    	var currency = settings.find('#s_currency').val();
	    	$(".ellist").find('span.currency').html(currency);
			if(currency == 'USD') {
				$(document).find("span.symbol").html("$");
			} else {
				$(document).find("span.symbol").html("");
			}
		}, 1);
    },
    determineFormNeedsUpgrade: function() {
    	var elements = Ui.formHasPaidElements();
    	var hasPaidElements = elements.length > 0;
		var aStatus = window.accountStatus.split('-');
		aStatus = aStatus[0];

		var settings = $(document).find("#ssettings");

		var hasProFeature = false;
		if(settings.find('[prop=responseStorage]').val() != 'standard') {
			hasProFeature = true;
		}

    	if(hasPaidElements && (aStatus == 'FREE' || aStatus=='PREVIEW') && gd.fd.active==0) {
    		var elementsHtml='';
    		$.each(elements, function(idx, element) {
    			elementsHtml+='<i class="fa fa-circle-thin" aria-hidden="true"></i> <b>'+element+'</b> &nbsp;&nbsp;&nbsp;';
    		});

			if(hasProFeature) {
				elementsHtml+='<i class="fa fa-circle-thin" aria-hidden="true"></i> <b>Response Storage</b> &nbsp;&nbsp;&nbsp;';
			}

    		setTimeout(function() {
    			Ui.alert('error','You are using advanced features, they are active for you to test , but to publish the form for external users you will need to <a href="/settings/subscription/">upgrade your account</a>. Advanced features used: <br>'+elementsHtml);
	        	$('.form-state-container').html('');
	        	$('.form').css('marginTop', '4rem');
	        	$('.form-menu .right').css('width', '400px');
	        	$('.form-state-container').append('<a href="/settings/subscription/" id="formState" style="width:190px;" class="form-state button small">Upgrade to be able to publish</a>');
    		}, 500);
			window.hasGlobalError = true;
        	return true;
        } else if(aStatus != 'PRO' && hasProFeature) {
			var elementsHtml='';
			elementsHtml+='<i class="fa fa-circle-thin" aria-hidden="true"></i> <b>Response Storage</b> &nbsp;&nbsp;&nbsp;';

			setTimeout(function() {
    			Ui.alert('error','You are using advanced features, they are active for you to test , but to publish the form for external users you will need to <a href="/settings/subscription/">upgrade your account to Professional</a>. <br>Advanced features used: <br>'+elementsHtml);
	        	$('.form-state-container').html('');
	        	$('.form').css('marginTop', '4rem');
	        	$('.form-menu .right').css('width', '400px');
	        	$('.form-state-container').append('<a href="/settings/subscription/" id="formState" style="width:190px;" class="form-state button small">Upgrade to be able to publish</a>');
    		}, 500);
			window.hasGlobalError = true;
			return true;
		} else {
        	//if(hasPaidElements && window.accountStatus == 'FREE') {
        		$(".alert-container").hide();
        	//}
        	$('.form-state-container').html('');
        	$('.form').css('marginTop', '2rem');
        	$('.form-menu .right').css('width', '300px');
        	if(gd.fd.active=='1') {
        		$('.form-state-container').append('<div id="formState" class="form-state button small form-active">Unpublish</div>');
        	} else {
        		$('.form-state-container').append('<div id="formState" class="form-state button small">Publish</div>');
        	}
			window.hasGlobalError = false;
        	return false;
        }
    },
	determineElementNeedsUpdate: function() {
		var form_container = $(document).find('.formElementContainer');
		var elements = [];
		var pages = form_container.find('.ellist');

		var optionsElementsError = [];
		var stripeElementsError = [];

		pages.each(function() {
			var els = $(this).find('.el');
			els.each(function() {
				var label = $(this).find('[prop=inputLabel]').val();
				var type = $(this).find('.gc').attr('type');

				var sidebar = $(document).find('#s'+$(this).attr('id').slice(1));

				if(type == 'SWITCH' || type == 'CHECKBOX' || type == 'RADIO' || type == 'SELECT') {
					var optionListContainer = sidebar.find('div.optionsList');
					var fieldsets = optionListContainer.find('fieldset[prop]');
					var hasSameValue = false;
					var values = [];
					fieldsets.each(function() {
						var val = $(this).find('[prop=option_value]').val();
						if($.trim(val) == '') {
							val = $(this).find('[prop=option_label]').val();
						}

						if($.trim(val)) {
							if(values.indexOf(val) > -1) {
								hasSameValue = true;
								return;
							} else {
								values.push(val);
							}
						}
					});

					if(hasSameValue) {
						optionsElementsError.push(label);
					}
				} else if(type == 'STRIPE' || type == 'STRIPEPAYPAL') {
					//setTimeout(function() {
						var pkey = sidebar.find('[prop=public_key]');
						var skey = sidebar.find('[prop=secret_key]');

						if($.trim(pkey.val()) == '' || $.trim(skey.val()) == '') {
							stripeElementsError.push('Stripe');
						}
					//}, 100);
				}
			});
		});

		var error='';

		if(optionsElementsError.length) {
			var elementsHtml='';
			$.each(optionsElementsError, function(idx, element) {
    			elementsHtml+='<i class="fa fa-circle-thin" aria-hidden="true"></i> <b>'+element+'</b> &nbsp;&nbsp;&nbsp;';
    		});

			error+='Warning: If you don\'t have distinct options, you will not know what response was selected in theses fields: <br>'+elementsHtml+'<br><br>';
		}

		if(stripeElementsError.length) {
			error+='Warning: Copy your public key and secret key from stripe:API to formlets to be able to use the element';
		}

		if(error) {
			setTimeout(function() {
    			Ui.alert('error',error);
	        	$('.form').css('marginTop', '4rem');
	        	$('.form-menu .right').css('width', '400px');
    		}, 500);
			return true;
		} else {
			if(window.hasGlobalError == false) {
				$(".alert-container").hide();
	        	$('.form').css('marginTop', '2rem');
	        	$('.form-menu .right').css('width', '300px');
			}

        	return false;
		}
	},
    populateCalculationField: function(response) {
    	var sidebar = $(document).find('#s'+response._id);
    	var side_cal = sidebar.find('.side_calculation');

    	if(response.fieldLists && response.fieldLists.length) {
    		side_cal.html("");
    		var idx = 0;
    		$.each(response.fieldLists, function(field) {
    			var label = Utils.toLetters(idx);
    			var template = $('<div class="fieldList"> <div class="g2 f label">'+label+'</div> <div class="g8 f field"><select class="text"><option></option></select></div> <div class="g2 f action"><span class="remove"><i class="fa fa-trash"></i></span><span class="add"><i class="fa fa-plus-square"></i></span></div>');
    			side_cal.append(template);
    			idx++;
    		});
    	}

    	var fields = side_cal.find('.fieldList');

    	var el = $(document).find('#f'+response._id);
    	var form_container = el.closest('.formElementContainer');
        var elements = [];
        var pages = form_container.find('.ellist');
        var stop = false;
        pages.each(function() {
        	if(stop) {
        		return false;
        	}
        	var els = $(this).find('.el');
        	els.each(function() {
        		var eid = $(this).attr('id').substring(1);
        		if(eid == response._id) {
        			stop = true;
        			return false;
        		} else {
					var label = $(this).find('[prop=inputLabel]').val();
					var type = $(this).find('.gc').attr('type');
					if(type == 'INPUTTABLE') {
						var options = $(this).find('.foption');
						options.each(function() {
							var input = $(this).find('.table-input');
							var input_val = Utils.slugify(input.val());
							var el_name = eid+"['"+input_val+"']";
							elements.push({
								id: el_name,
								label: label + ' ' + input.val()
							});
						});
					} else {
	        			elements.push({
	        				id: eid,
	        				label: label
	        			});
					}
        		}
        	});
        });

    	var template = '';
    	$.each(elements, function(i, el) {
        	if(el.label) {
        		template += '<option value="'+el.id+'">'+el.label+'</option>';
        	}
        });

    	var ctr=0;
        fields.each(function() {
        	var select = $(this).find('select.text');
        	select.html('<option></option>');
        	select.append($(template));
        	if(response.fieldLists) {
        		var f = response.fieldLists[ctr];
        		if(f) {
        			select.val(f.field);
        		}
        	}
        	ctr++
        });
    },
    calculationShowActions: function(id) {
    	var sidebar = $(document).find('#s'+id);
    	var side_cal = sidebar.find('.side_calculation');
    	var fields = side_cal.find('.fieldList');
    	fields.each(function() {
    		var idx = $(this).index();
    		var label = Utils.toLetters(idx);
    		$(this).find('.label').html(label);
    		if($(this).is(":last-child") && $(this).is(":first-child")) {
    			$(this).find('.action .remove').hide();
    			$(this).find('.action .add').show();
    		} else if($(this).is(":last-child")) {
    			$(this).find('.action .remove').show();
    			$(this).find('.action .add').show();
    		} else {
    			$(this).find('.action .remove').show();
    			$(this).find('.action .add').hide();
    		}
    	});
    },
    calculationAddField: function() {
    	var side = $(this).closest('.sel');
    	var side_cal = side.find('.side_calculation');
    	var fields = side_cal.find('.fieldList');
    	var newIdx = fields.length;

    	var totalField = side_cal.closest('.calculation').find('input[prop=calculationTotal]');
    	var old_total_value = totalField.val();

    	var label = Utils.toLetters(newIdx);
    	var template = $('<div class="fieldList"> <div class="g2 f label">'+label+'</div> <div class="g8 f field"><select class="text"><option></option></select></div> <div class="g2 f action"><span class="remove"><i class="fa fa-trash"></i></span><span class="add"><i class="fa fa-plus-square"></i></span></div>');

		var el = $(document).find('#f'+side.attr('id').slice(1));
    	var form_container = el.closest('.formElementContainer');
        var elements = [];
        var pages = form_container.find('.ellist');
		var stop = false;
        pages.each(function() {
			if(stop) {
        		return false;
        	}
        	var els = $(this).find('.el');
        	els.each(function() {
        		var eid = $(this).attr('id').substring(1);

				if(eid == side.attr('id').slice(1)) {
        			stop = true;
        			return false;
        		} else {
        			var label = $(this).find('[prop=inputLabel]').val();
					var type = $(this).find('.gc').attr('type');
					if(type == 'INPUTTABLE') {
						var options = $(this).find('.foption');
						options.each(function() {
							var input = $(this).find('.table-input');
							var input_val = Utils.slugify(input.val());
							var el_name = eid+"['"+input_val+"']";
							elements.push({
								id: el_name,
								label: label + ' ' + input.val()
							});
						});
					} else {
						elements.push({
							id: eid,
							label: label
						});
					}
        		}
        	});
        });

    	$.each(elements, function(i, el) {
        	if(el.label) {
        		template.find('select.text').append('<option value="'+el.id+'">'+el.label+'</option>');
        	}
        });

    	side_cal.append(template);
    	Ui.calculationShowActions(side.attr('id').slice(1));

    	setTimeout(function() {
    		$(document).find("#f"+side.attr('id').slice(1)).click();
    	}, 500);
    },
    calculationRemoveField: function() {
		var side = $(this).closest('.sel');
		$(this).closest('.fieldList').remove();
		Ui.populateCalculationField(side.attr('id').slice(1));
		Ui.calculationShowActions(side.attr('id').slice(1));
		Ui.calculationSubmitUpdate(side.attr('id').slice(1));
    },
    calculationSubmitUpdate: function(id) {
    	var sidebar = $(document).find('#s'+id);
    	var side_cal = sidebar.find('.side_calculation');
    	var fields = side_cal.find('.fieldList');

    	var fieldList = [];
    	fields.each(function() {
    		var val = $(this).find('select.text').val();
    		var field = {"field":val};
    		fieldList.push(field);
    	});

    	var req = {};
    	req.form_id=gd.object;
        req.el_id=id;
    	req.prop='fieldLists';
    	req.value=fieldList;
    	req.method="POST";
        Utils.reqdata('editFormElement',req);
    },
    arrangePage: function(button) {
    	var action = '';
    	if(button.hasClass('down')) {
    		action = 'down';
    	} else if(button.hasClass('up')) {
    		action = 'up';
    	}
    	var page = button.closest('.ellist');
    	var swap = '';
    	if(action == 'up') {
    		swap = page.prev('.ellist');
    	} else {
    		swap = page.next('.ellist');
    	}

    	page.swapWith(swap);

    	var newPageConstruction = [];
    	var pages = $(document).find('.formElementContainer .ellist');
    	pages.each(function() {
    		var page = $(this).attr('data-page');
    		var nextButtonText = 'Next';
    		var previousButtonText = 'Previous';
    		var footer = $(this).find('.footer');
    		var nextC = footer.find('[data-prop=nextButtonText]');
    		var prevC = footer.find('[data-prop=previousButtonText]');
    		if(nextC.length) {
				nextButtonText = nextC.html();
    		}
    		if(prevC.length) {
    			previousButtonText = prevC.html();
    		}

    		newPageConstruction.push({"_id":page, "nextButtonText":nextButtonText, "previousButtonText":previousButtonText});
    	});

    	var req = {};
    	req.form_id=gd.object;
    	req.pages=newPageConstruction;
    	req.method="POST";
        Utils.reqdata('updatePages',req);

        var container = $(document).find('.formElementContainer');
        var page_containers = container.find('.ellist');
        var $line_number = 1;
        var $new_line_number = 1;
        $.each(page_containers, function(i, $page_container) {
            $page_container = $($page_container);
            var $page = $page_container.data('page');
            var $page_number = $page_container.attr('id').slice(13);

            $.each($page_container.find('.formline'), function(i, line) {
                if($(line).find('.el').length > 0) {
                    Ui.balanceformline($(line), $page, $line_number);
                    $line_number++;
                }
            });

            setTimeout(function() {
            	Ui.submitUpdateElements(window.elementsToBeUpdated);
                window.location.reload();
            }, 100);

        });
    },
	addConditional:function() {
		var sidebar = $(this).closest('.sel');
		var logicContainer = $(this).closest('fieldset.logic');
		var conditions = logicContainer.find('.conditions');
		var clist = conditions.find('.clist').get(0);
		clist = $(clist);
		var clonedCList = clist.clone();
		clonedCList.find('[prop="logicValue"]').val('');
		conditions.append(clonedCList);
		Ui.showDeleteConditions(sidebar);
		Ui.submitConditionsChanges();
	},
	removeConditional: function() {
		var sidebar = $(this).closest('.sel');
		var clist = $(this).closest('.clist');
		clist.remove();
		Ui.showDeleteConditions(sidebar);
		Ui.submitConditionsChanges();
	},
	showDeleteConditions: function(sidebar, response) {
		response = response || null;

		var lists = sidebar.find('.conditions .clist');

		if(response && response.conditions) {
			setTimeout(function() {
				var clist = lists.get(0);
				clist = $(clist);

				sidebar.find('.conditions').html('');

				var json_conditions = JSON.parse(response.conditions);
				$.each(json_conditions, function(idx, condition) {
					var clonedCList = clist.clone(true);
					clonedCList.find('[prop="logicField"]').val(condition.if);
					clonedCList.find('[prop="logicCondition"]').val(condition.state);
					clonedCList.find('[prop="logicValue"]').val(condition.value);

					sidebar.find('.conditions').append(clonedCList);
				});

				lists = sidebar.find('.conditions .clist');

				if(lists.length > 1) {
					sidebar.find('.deleteCondition').show();
				} else {
					sidebar.find('.deleteCondition').hide();
				}
			}, 500);
		} else {
			if(lists.length > 1) {
				sidebar.find('.deleteCondition').show();
			} else {
				sidebar.find('.deleteCondition').hide();
			}
		}
	},
	submitConditionsChanges: function() {
		var tel=$(document).find('#s'+gd.element);
		var lists = tel.find('.conditions .clist');
		var value = [];
		lists.each(function() {
			var v = {};
			v.if = $(this).find('[prop="logicField"]').val();
			v.state = $(this).find('[prop="logicCondition"]').val();
			v.value = $(this).find('[prop="logicValue"]').val();

			value.push(v);
		});

		var req = {};
		var tel=$(document).find('#s'+gd.element);
		var et=tel.attr("et");
		req.form_id=gd.object;
		req.el_id=gd.element;
		req.prop="conditions";
		req.value=value;
		req.el_type=et;
		req.method="POST";
		Utils.reqdata('editFormElement',req);
	},
    actionBinding : function(){
        $(document).on("click",".new_page",Ui.createNewPage);
        $(document).on("click",".delpage",Ui.deletePage);
		$('.ellist').on("mousedown","button[prop=required]",function(e) {
            e.preventDefault();
			if($(this).hasClass('active')) {
				$(this).removeClass('active');
			} else {
				$(this).addClass('active');
			}
			var el_id = $(this).closest('.el').attr('id').slice(1);
			$('#s'+el_id + ' input[prop=required]').click();
        });
        $(document).on("mousedown",".el",Ui.activateEl);
        $('#snew').on("click","#createnewform",function(){Ui.createForm('FULL')});
        $('#snew').on("click","#createnewendpoint",function(){Ui.createForm('ENDPOINT')});
        $(document).on("change click","#sidebar input:not([type=checkbox]):not([prop=option_label]):not([prop=option_value])",Ui.updateformel);
        $(document).on("keyup blur","#sidebar input",Ui.updateformel);
        $(document).on("change blur","input.jscolor",Ui.updateformel);
        $('#sidebar').on("focus","input.addoption",Ui.addoption);
        $('#sidebar').on("keyup blur","textarea",Ui.updateformel);
        $(document).on("change", "#sidebar input[type=checkbox]", Ui.updateformel);
        $(document).on("change", '#sidebar select', Ui.updateformel);
        $(document).on("click", "#sidebar [class=optionsList] button", Ui.changeoption);
        $('#sidebar').on("click","button.duplicate",Ui.duplicateformel);
        $('#sidebar').on("click","button.del_element",function(e) {
        	e.preventDefault();
        	var el_id = $(this).closest('.sel').attr('id').slice(1);
        	$("#f" + el_id).find('button.delete').click();
        });
        $('.ellist').on("keyup blur","textarea",Ui.updateformselected);
		$('.ellist').on("keyup blur","div.div-textarea",Ui.updateformselected);
        $('.ellist').on("keyup blur","input:not(.other_text):not([type=radio])",Ui.updateformselected);
        $('.ellist').on("change",".foption input[type=radio]",Ui.updateformselected);
        $('.ellist').on("change",".foption input[type=checkbox]",Ui.updateformselected);
        $('.ellist').on("change","fieldset.select [prop=optionsList]",Ui.updateformselected);

		$('#sidebar').on("click", ".clearDefault", function(e) {
			e.preventDefault();
        	var el_id = $(this).closest('.sel').attr('id').slice(1);
			var form = $("#f"+el_id);
			var input = form.find('.foption input[type=radio]:checked');
			if(input.length) {
				input.removeAttr('checked');
				input.prop('checked', false);
				input.trigger('change');
				$(this).hide();
			}
		});

        $('.ellist').on("click",".delete",Ui.deleteformel);
        $('.ellist').on("click",".duplicate", function() {
        	var parent = $(this).closest('.el');
            var element_id = parent.attr('id').slice(1);

            var req = {};
            req.form_id=gd.object;
            req.element_id=element_id;
            Utils.reqdata('getFormElement',req, Ui.duplicateformel);
        });
        $('.ellist').on("click", "button.logic", function() {
        	var parent = $(this).closest('.el');
            var element_id = parent.attr('id').slice(1);
            var side = $(document).find('#s'+element_id);
            var logic = side.find('[prop=enableLogic]');
            if(logic.is(":checked") == false) {
            	logic.parent().find('.switch-container').click();
            	setTimeout(function() {
            		side.closest('.scroll').scrollTop(1000);
            	}, 0);
            } else {
            	side.closest('.scroll').scrollTop(1000);
            }

        });

		var textarea = $(document).find('textarea.autoheight');
		if(textarea.length) {
			textarea.on('keydown', autosize);
		}

		function autosize(){
			var el = this;
			setTimeout(function() {
				el.style.cssText = 'height:21px; padding:0;';
				var height = el.scrollHeight;
				el.style.cssText = 'height:' + height + 'px';
				var container = document.getElementById('fconfirmation');
				var container_height = height+40;
				container.style.cssText = 'height:' + container_height + 'px';
			},0);
		}

		$("textarea.autoheight").trigger('keydown');

        $(document).on('focus', '.submit_confirmation textarea', function() {
        	var el = $(this).closest('.el');
        	$(document).find('a#formlet').trigger('click');
        });
        $(document).on('keyup', '.submit_confirmation textarea', function() {
        	var prop = $(this).attr('prop');
        	var settings = $(document).find('#sconfirmation');
        	var field = settings.find('[prop='+prop+']');
        	field.val($(this).val());
        });
        $(document).on('blur', '.submit_confirmation textarea', function() {
        	var el = $(this).closest('.el');
        	var prop = $(this).attr('prop');
        	var settings = $(document).find('#sconfirmation');
        	var field = settings.find('[prop='+prop+']');
        	field.val($(this).val());
        	field.trigger('keyup');
        });

        $(document).on("click", ".ellist .arrange .fa", function(e) {
        	Ui.arrangePage($(this));
        });

        $(document).on("click", "#selements .el", function(e) {

        	var $this = $(this).clone();

        	var last_page = $(document).find('.ellist').not('.submit_confirmation').last();
        	var formline = last_page.find('.footer').prev('.formline');

        	var setel = {};
        	setel.dragged = $this.get(0);
        	setel.from = {id:'sideA'};
        	setel.to = formline;
        	setel.to.id = '';

        	formline.append($this);

        	var newid = Ui.setel(setel, 'click');
        	if(newid) {
        		Ui.scrollTo(newid, 1);
        	}

        	var redel = {};
        	redel.to = formline;
        	redel.item = $this.get(0);
        	redel.item.id = $this.attr('id');

        	Ui.redel(redel);

			setTimeout(function() {
				Ui.removeDropLines();
			}, 300);

        });


        $('.ellist').on("click",".option-container button.red",function(e) {
            e.preventDefault();
            var id=$(this).closest('.el').attr("id").slice(1);
            var $div = $(this).closest('fieldset.option-container').parent();
            var option_index = $div.index();
            if(option_index<0) {
            	option_index = $(this).closest('.option-container').index();
            }

            var side_options = $("#s" + id + " div[class=optionsList]");
            var option_selected = side_options.find('fieldset[prop=o'+option_index+']');
            option_selected.find('button.delete').click();
        });
        $('#formStateContainer').on("click", ".form-state", Ui.updateFormState);

        $("#sidebar").on("click", ".side_change_image", function(e) {
        	e.preventDefault();
        	var el_id = $(this).closest('.sel').attr('id').slice(1);
        	$("#f"+el_id).find(".upload_picture").click();
        });

        $('.ellist').on("click", ".upload_picture", function(e) {
        	e.preventDefault();
        	var input = $(this).prev();
        	var image_container = $(this).closest('fieldset.picture').find('.image_container');
        	var no_image_container = $(this).closest('fieldset.picture').find('.no_image');
        	input.click();

        	input.on("change", function(file) {
        		var filesizemb = file.target.files[0].size/1000/1000;
        		if(filesizemb > window.max_upload_size){
        			Ui.alert('error', 'Max image size is ' + window.max_upload_size + 'MB');
        		} else {
	        		if(this.files && this.files[0]) {
	        			if(!window.isPreviewUser) {
	        				var img = image_container.find('img');
			        		var reader = new FileReader();
			        		reader.onload = function(e) {
			        			img.attr('src', e.target.result);
			        		}

			        		reader.readAsDataURL(this.files[0]);
			        		image_container.show();
			        		no_image_container.hide();
	        			}
		        		var req = {};
				        req.form_id=gd.object;
				        req.el_id=gd.element;
				        req.prop='picture';
				        req.value=file.target.files[0];
				        Utils.reqdataFile('editFormElement',req);
	        		}
        		}


        	});
        });

        $('.ellist').on("click", ".inputTable .new a", function(e) {
        	var $this = $(this);
        	var id=$this.closest('.el').attr("id").slice(1);
        	var sidebar = $(document).find("#s"+id);
        	var fieldset = sidebar.find('fieldset.questionList');
        	var input = fieldset.find('.addoption input');
        	setTimeout(function() {
        		input.focus();
        		var options = fieldset.find('[prop=questionList]').find('fieldset');
	        	var last_option = options.get(options.length - 1);
	        	$(last_option).find('input').trigger('keyup');
	        	var last_option_form = $this.closest('.option_container').find('.foption').get(options.length - 1);
	        	$(last_option_form).find('input.inline-edit').focus();
        	}, 100);
        });

        $('.ellist').on("click", "div[et=inputtable] .newColumn a", function(e) {
        	var $this = $(this);
        	var id=$this.closest('.el').attr("id").slice(1);
        	var sidebar = $(document).find("#s"+id);
        	var fieldset = sidebar.find('fieldset.answerList');
        	var input = fieldset.find('.addoption input');
        	setTimeout(function() {
        		input.focus();
        		var options = fieldset.find('[prop=answerList]').find('fieldset');
	        	var last_option = options.get(options.length - 1);
	        	$(last_option).find('input').trigger('keyup');
	        	var last_option_form = $this.closest('.el').find('tr.ans td.gray').get(options.length - 1);
	        	$(last_option_form).find('input.inline-edit').focus();
        	}, 100);
        });

        $('.ellist').on("click", ".inputTable .del", function(e) {
        	e.preventDefault();
        	var list = 'questionList';
        	if($(this).closest('.ans').length) {
        		list = 'answerList';
        	}
        	var id=$(this).closest('.el').attr("id").slice(1);
        	var $div = $(this).closest('.option-container');
            var option_index = $div.index();
            if(option_index<0) {
            	option_index = $(this).closest('.gray').index();
            }

            option_index = option_index - 1;

            var side_options = $("#s" + id + " div[prop="+list+"]");
            var option_selected = side_options.find('fieldset[prop=o'+option_index+']');
            option_selected.find('button.delete').click();
        })

        //for upload form logo
        var fileContainer = $('#sidebar').find(".file");
        if(fileContainer.length > 0) {
    		var button = fileContainer.find("button");
    		var input = fileContainer.find('input[type="file"]');
    		button.on("click", function(e) {
    			e.preventDefault();
    			input.click();
    		});

    		input.on("change", function(e) {
    			var req = {};
    			req.form_id=gd.object;
    			req.el_id="settings";
    			req.prop="logo";
    			req.value=e.target.files[0];
    			Utils.reqdataFile('editFormElement',req);
    		});
    	}

    	$(document).on("click", ".fcc .fc-logo-add a", function() {
    		var settings = $(document).find("#ssettings");
    		settings.find('fieldset.file button').click();
    	});

    	//delete logo
    	$(document).on("click", ".fcc .fc-logo .logoAction a", function() {
    		var settings = $(document).find("#ssettings");
    		settings.find('.formlogo i').click();
    	})
    	$(document).on("click", "#sidebar .formlogo i", function() {
    		var req = {};
	        req.form_id=gd.object;
	        req.el_id="settings";
	        req.prop="logo";
	        req.value="";
	        Utils.reqdata('editFormElement',req, Ui.logoDeleted);

	        $(document).find('.fc-logo-add').show();
    	});

    	$(document).on("click", ".fcc #delivery", function() {
	    	window.location.href = gd.path + 'editor/'+ gd.object+"/#edelivery";
    	});

    	$(document).on('selectstart', '.div-textarea', function () {
    		var $this = $(this);
    		var container = $this.closest('.gc');
    		var el_container = $this.closest('.el');

    		var controls = $('<div class="btn-toolbar eControls" data-role="editor-toolbar" data-target=".div-textarea">\
    			<ul>\
        			<li><button class="control-bold" data-edit="bold"><i class="fa fa-bold"></i></button></li>\
        			<li><button class="control-italic" data-edit="italic"><i class="fa fa-italic"></i></button></li>\
        			<li><button class="control-heading2" data-edit="heading h2"><i class="fa fa-header"></i></button></li>\
        			<li><button class="control-alignleft" data-edit="justifyleft"><i class="fa fa-align-left"></i></button></li>\
        			<li><button class="control-aligncenter" data-edit="justifycenter"><i class="fa fa-align-center"></i></button></li>\
        			<li><button class="control-alignright"  data-edit="justifyright"><i class="fa fa-align-right"></i></button></li>\
        			<li>\
        				<button class="control-link dropdown-toggle" data-toggle="dropdown"><i class="fa fa-link"></i></button>\
        				<div class="dropdown-menu input-append">\
        					<input class="span2" placeholder="URL" type="text" data-edit="createLink" data-com.agilebits.onepassword.user-edited="yes">\
			    			<button class="btn" type="button">Add</button>\
        				</div>\
        			</li>\
        			<li><button class="control-removelink"  data-edit="unlink" title="Remove Link"><i class="fa fa-scissors"></i></button></li>\
    			</ul>\
    		</div>');

	        $(document).one('mouseup keyup', function() {
        		container.find(".eControls").remove();
        		container.append(controls);
        		$this.wysiwyg();
	        });

	        container.on('click', '.control-link', function() {
	        	if($(this).next('.dropdown-menu').hasClass('shown')) {
	        		$(this).next('.dropdown-menu').removeClass('shown');
	        	} else {
	        		$(this).next('.dropdown-menu').addClass('shown');
	        	}
	        });

	        $(document).on("click", function(e) {
	        	if(el_container.attr('id') != $(e.target).closest('.el').attr('id')) {
	        		container.find(".eControls").remove();
	        	}
	        });
	    });

	    $(document).on("click", ".eControls button", function() {
	    	$('.div-textarea').trigger("change");
	    });

	    $('body').on("click", function(evt) {
    		if($("#deliveryContainer").css('display') != 'none' && $(evt.target).closest('#deliveryContainer').length == 0) {
    			window.location.href = gd.path + 'editor/'+ gd.object+"/#e"+window.element_history;
    		}
    		var side = $(document).find('#s' + window.element_history);
    		if(side.length) {
    			var logic = side.find('[prop=enableLogic]');
    			if(logic.length && logic.is(":checked")) {

    				if(!side.find('[prop=logicAction]').val() && !side.find('[prop=logicField]').val() && !side.find('[prop=logicCondition]').val() && !side.find('[prop=logicValue]').val()) {

    					var request = window.location.href.replace(/#e/,'').split("/");
				    	gd.object=request[4];
				        gd.element=request[5];
				        var closestSide = $(evt.target).closest('.sel');

						if(logic.length && logic.is(":checked")) {
							if(closestSide.length == 0 || (closestSide.length && closestSide.attr('id').slice(1) != gd.element)) {
								if($(evt.target).closest('.sidebar').length) {
									//
								} else {
									var buttonLogic = false;
									if($(evt.target).closest('button').hasClass('logic') || $(evt.target).hasClass('logic')) {
										buttonLogic = true;
									}

									if(buttonLogic == false) {
										logic.closest('.enableLogic').find('.switch-container').trigger('click');
										Ui.determineFormNeedsUpgrade();
										Ui.determineElementNeedsUpdate();
									}
								}
							}
						}
    				}
    			}

    		}
    	});

    	$("#deliveryContainer button.close").on("click", function() {
    		$('body').click();
    	});

    	$(".editable").on("click", function() {
    		if($(this).hasClass("passwordButtonLabel")) {
    			//
    		} else {
    			window.location.href = gd.path + 'editor/'+ gd.object+"/#esettings";
    		}
    		
    	}).on("keyup", function() {
    		var trigger_id = $(this).data('trigger');
    		var prop = $(this).data('prop');
    		var page = $(this).closest('.ellist').data('page');

    		var request = window.location.href.replace(/#e/,'').split("/");

    		//$("[data-trigger="+trigger_id+"]").not($(this)).html($(this).html());
    		$("#s"+request[5]+" #"+trigger_id).val($(this).html());

    		$('.sel').find('[data-pageid="'+page+'"').find('[prop="'+prop+'"]').val($(this).html());

    		var req = {};
	        req.form_id=gd.object;
	        req.el_id=request[5];
	        req.prop=prop;
	        req.page=page;
	        req.value=$(this).html();
	        Utils.reqdata('editFormElement',req);

    	}).keypress(function(e){ return e.which != 13; });

    	$(".jscolor").on("keyup", function() {
    		$(this).css('background', $(this).val());
    	});

		//hack to show the time input right away on datetime picker
		$(document).on("click", ".datetimePicker", function() {
			var fieldset = $(this).closest('fieldset');
			setTimeout(function() {
				fieldset.find('.flatpickr-calendar').addClass('showTimeInput');
			}, 500);
		});

    	$('body').attr("spellcheck",false);

    	/*resize the input for product elements*/
        $(document).on("input keyup", "input.product-input", function() {
        	$(this).attr({width: 'auto', size: $(this).val().length});
        });

        //
        if(gd.fd.currency != 'USD') {
        	$("span.symbol").html('');
        } else {
        	$("span.symbol").html('$');
        }

        $(document).on("mouseover", ".el", function(e) {
        	var formContainer = $(this).closest('.formElementContainer');
        	var index = $('.formElementContainer .el').index($(this));
        	var settings = $(document).find("#ssettings");
        	var hasExternal = false;
	        if(settings.find('#s_isExternalData').is(":checked")) {
	        	var exts = settings.find('#s_externalData').val();
	        	if(exts) {
	        		hasExternal = true;
	        	}
	        }
        	if(index == 0 && hasExternal==false) {
        		$(this).find('button.logic').hide();
        	} else {
        		$(this).find('button.logic').show();
        	}
        });
        setTimeout(function() {
        	$(document).find('.selected').trigger('mouseover');
        }, 50);

        Ui.determineFormNeedsUpgrade();

		setTimeout(function() {
			Ui.determineElementNeedsUpdate();
		},500);

        Ui.determineProduct();

        /*CALCULATION*/
        $(document).on('click', '.side_calculation .action .add', Ui.calculationAddField);
        $(document).on('click', '.side_calculation .action .remove', Ui.calculationRemoveField);
        $(document).on('change', '.side_calculation .field select.text', function() {
        	var id = $(this).closest('.sel').attr('id').slice(1);

	    	var fieldLists = $(this).closest('.fieldList');
	    	var field = $(this).closest('.field');
	    	var idx = $('#s' + id + ' .fieldList .field').index(field);

	    	var label = Utils.toLetters(idx);

	    	var totalField = $(this).closest('.calculation').find('input[prop=calculationTotal]');
    		var old_total_value = totalField.val();

    		if(old_total_value == '') {
    			totalField.val(label);
    			totalField.trigger('change');
    		} else {
    			var arr = old_total_value.split('');
    			if(arr.indexOf(label) < 0) {
    				totalField.val(old_total_value + ' + ' + label);
					setTimeout(function() {
						totalField.trigger('change');
					}, 500);
    			}
    		}

        	Ui.calculationSubmitUpdate(id);
        });

		/*CONDITIONAL*/
		$(document).on("click", "#sidebar [class=logic] .newCondition a", Ui.addConditional);
		$(document).on("click", "#sidebar [class=logic] .deleteCondition a", Ui.removeConditional);
		$(document).on("change", '[prop="logicField"], [prop="logicCondition"], [prop="logicValue"]', Ui.submitConditionsChanges);

		setTimeout(function() {
			var formlines = $(document).find("div.formline");
			formlines.each(function() {
				$(this).on("click", function() {
					if($.trim($(this).html()).length == 0) {
						window.location.href = gd.path + 'editor/'+ gd.object+"/#eelements";
					}
				});
			});
		}, 500);

		$(document).on("click", ".copy_clipboard", function() {
			var $this = $(this);
			var code = $this.closest('.code');
			var text = code.find('code').html();
			var decoded = $('<textarea/>').html(text).text();

			var copy = Utils.copyClipboard(decoded);
			if(copy) {
				$this.html('Copied!');
				setTimeout(function() {
				$this.html('Copy to clipboard');
				}, 1000);
			}
		});

		$(document).on("click", ".passwordContainer", function() {
			if(gd.element != 'settings') {
				window.location.href = gd.path + 'editor/'+ gd.object+"/#esettings";
			}
		});
    }
}

var Utils = {};
Utils.insertid = function (){
    var list=['a', 'b', 'c', 'd', 'e', 'f', 'g', 'h', 'i', 'j', 'k', 'l', 'm', 'n', 'o', 'p', 'q', 'r', 's', 't', 'u', 'v', 'w', 'x', 'y', 'z', '1', '2', '3', '4', '5', '6', '7', '8', '9','A', 'B', 'C', 'D', 'E', 'F', 'G', 'H', 'I', 'J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S', 'T', 'U', 'V', 'W', 'X', 'Y', 'Z'];
    var id = "";
    for (l = 0; l < 16; l++) {
        var rand = Math.floor((Math.random()*58)+1);
        id += list[rand];
    }
    return id;
}
Utils.reqdata = function(func,req,callback){
    var request = new XMLHttpRequest();
    request.onreadystatechange = function() {
		if(request.readyState == 4) {
			if (request.status == 200) {
	    		if(request.responseText == '{"status":"loggedout"}') {
	    			window.location.reload();
	    		}
	    		if(callback) {
	    			callback(JSON.parse(request.responseText));
	    		}
	    	} else {
				Ui.alert('error', 'Could not connect to formlets server, please reload');
			}
		}

    };
    if(req.method == 'POST') {
    	var params = 'method='+func+'&'+'json='+encodeURIComponent(JSON.stringify(req));

		var url = '/__api/json/';
		if(req.form_id) {
			url='/__api/json/'+req.form_id+'/';
		}

	    request.open('POST', url, true);
	    request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
	    request.send(params);
    } else {
    	var url='/__api/json/?method='+func+'&'+'json='+encodeURIComponent(JSON.stringify(req));
	    request.open('GET', url);
	    request.send();
    }
}
Utils.reqdataFile = function(func,req){
	var formData = new FormData();
	formData.append('method', func);
	formData.append('json', []);
	formData.append('upload', true);
	formData.append('form_id', req.form_id);
	formData.append('el_id', req.el_id);
	formData.append('prop', req.prop);
	formData.append('file', req.value);

	var url = '/__api/json/';
	if(req.form_id) {
		url='/__api/json/'+req.form_id+'/';
	}

	$.ajax({
	    url: '/__api/json/',
	    data: formData,
	    type: 'POST',
	    cache: false,
	    // THIS MUST BE DONE FOR FILE UPLOADING
	    contentType: false,
	    processData: false,
	    // ... Other options like success and etc
	    success: function(data) {
	    	if(req.prop == 'logo') {
	    		$(".fc-logo").html("");
		    	var logotpl = $('<div class="logoAction"><a href="javascript:;"><i class="fa fa-times-circle"></i> Remove</a></div><img src="/logo/'+req.form_id+'_logo.jpg?v='+Utils.insertid()+'" />');
	        	$(".fc-logo").append(logotpl);
	        	$(document).find('.fc-logo-add').hide();

	        	$("#ssettings").find('.file').hide();
	        	var slogoc = $("#ssettings").find('.logoContainer');
	        	var logotpl = $('<fieldset class="formlogo"><i class="fa fa-trash"></i><img src="/logo/'+req.form_id+'_logo.jpg?v='+Utils.insertid()+'" /></fieldset>');
	    		$(slogoc).append(logotpl);
	    	}
	    },
	    error: function(data) {
	    	var response = JSON.parse(data.response);
	    	var error = response.Error;
	    	Ui.alert('error', error);
	    }
	});
}

Utils.toLetters = function(num, afterZ) {
	afterZ = afterZ || false;
	if(afterZ == false) {
		num = num+1;
	}
    var mod = num % 26;
    var pow = num / 26 | 0;
    var out = mod ? String.fromCharCode(64 + mod) : (pow--, 'Z');
    return pow ? Utils.toLetters(pow, true) + out : out;
}

Utils.slugify = function(str) {
    str = str.replace(/^\s+|\s+$/g, ''); // trim
    str = str.toLowerCase();

    // remove accents, swap ñ for n, etc
    var from = "àáäâèéëêìíïîòóöôùúüûñç·/_,:;";
    var to   = "aaaaeeeeiiiioooouuuunc------";
    for (var i=0, l=from.length ; i<l ; i++) {
        str = str.replace(new RegExp(from.charAt(i), 'g'), to.charAt(i));
    }

    str = str.replace(/[^a-z0-9\/_|+ -]/g, '') // remove invalid chars
        .replace(/\s+/g, '-') // collapse whitespace and replace by -
        .replace(/-+/g, '-') // collapse dashes
		.replace(/^-+/, '')  // Trim - from start of text
		.replace(/-+$/, ''); // Trim - from end of text

    return str;
}

Utils.copyClipboard = function(text) {
	if (window.clipboardData && window.clipboardData.setData) {
        // IE specific code path to prevent textarea being shown while dialog is visible.
        return clipboardData.setData("Text", text);
    } else if (document.queryCommandSupported && document.queryCommandSupported("copy")) {
        var textarea = document.createElement("textarea");
        textarea.textContent = text;
        textarea.style.position = "fixed";  // Prevent scrolling to bottom of page in MS Edge.
        document.body.appendChild(textarea);
        textarea.select();
        try {
            return document.execCommand("copy");  // Security exception may be thrown by some browsers.
        } catch (ex) {
            console.warn("Copy to clipboard failed.", ex);
            return false;
        } finally {
            document.body.removeChild(textarea);
        }
    }
}

Utils.getDateFormat = function(format) {

    var dateformat = 'm/d/Y';
    if(format) {
        if(format == 'MM/DD/YYYY') {
            dateformat = 'm/d/Y';
        } else if(format == 'DD/MM/YYYY') {
            dateformat = 'd/m/Y';
        } else if(format == 'DD-MM-YYYY') {
			dateformat = 'd-m-Y';
		} else {
            dateformat = 'Y-m-d';
        }
    } else {
        dateformat = 'm/d/Y';
    }

    return dateformat;
}

String.prototype.capitalize = function(){
    return this.charAt(0).toUpperCase() + this.slice(1)
}
