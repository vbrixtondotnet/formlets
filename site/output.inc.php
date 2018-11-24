<?php

include_once('../libs/autoload.php');
include_once('../libs/aws/aws-autoloader.php');

use google\appengine\api\cloud_storage\CloudStorageTools;
use Aws\Ses\SesClient;
use Aws\Ses\Exception\SesException;

error_reporting(E_ALL & ~E_NOTICE);

abstract class output extends output_admin {

function _benchit(){
  if($_GET["bench"]=="y"){
  echo '<p class="bench" style="color:#c0c0c0; padding-left:20px;">Execution took '.round( benchit() - $GLOBALS['bench_start'], 4 ).' seconds and '.count($GLOBALS['bench_sql_list']).' SQL queries.</p>';
  for($s=0;$s<count($GLOBALS['bench_sql_list']);$s++){
  	if (!$sql_list_open) {
  		echo '<ol class="bench_sql" style="color:#c0c0c0;">';
  		$sql_list_open = 1;
  	}
  	echo '<li><b>'.($s+1)."</b>: ".$GLOBALS['bench_sql_list'][$s];
  	if ($GLOBALS['bench_sql_starttime'][$s]) {
  		echo '<br />Benched from '.$GLOBALS['bench_sql_starttime'][$s].' to '.$GLOBALS['bench_sql_stoptime'][$s].' seconds.';
  	}
  	echo '</li>';
  }
  if ($sql_list_open) {
  	echo '</ol>';
  }

  for($s=0;$s<count($GLOBALS['bench_methods_list']);$s++){
  	if (!$sql_list_open) {
  		echo '<ol class="bench_sql" style="color:#c0c0c0;">';
  		$sql_list_open = 1;
  	}
  	echo '<li>'.$GLOBALS['bench_methods_list'][$s];
  	if ($GLOBALS['bench_methods_starttime'][$s]) {
  		echo '<br />Benched from '.$GLOBALS['bench_methods_starttime'][$s].' to '.$GLOBALS['bench_methods_stoptime'][$s].' seconds.';
  	}
  	echo '</li>';
  }
  if ($sql_list_open) {
  	echo '</ol>';
  }
  echo '<p class="bench" style="color:#c0c0c0; padding-left:20px;">Memory usage at this point is: '.((memory_get_usage(1)/1024)/1024).' MB.<br />Memory usage detailed: '.memory_get_usage(1).' Bytes.</p>';
  echo '<pre style="color:#c0c0c0;padding-left:20px;">
  ';
  print_r($this->objconfig);
  echo '</pre>';
  }

}



function _displayFile(){
  if(!$this->urlpart[2]) {
    $this->Output404();exit;
  }
	$file=str_replace('..','',$this->urlpart[2]);
	$filename = $file;
	if(!empty($_GET['f'])) {
		$filename = $_GET['f'];
	}

    $parts=explode('.',$file);
    $ex=$parts[count($parts)-1];
    $ex = strtolower($ex);
    switch($ex){
        case "gif": $ctype="image/gif"; break;
        case "png": $ctype="image/png"; break;
        case "jpeg":
        case "jpg": $ctype="image/jpeg"; break;
        case "zip": $ctype="application/zip"; break;
        case "xls": $ctype="application/excel"; break;
        case "txt": $ctype="text/text"; break;
        case "rtf": $ctype="text/richtext"; break;
        case "pdf": $ctype="application/pdf"; break;
        case "wmv": $ctype="video/msvideo"; break;
        case "mp3": $ctype="audio/mpeg3"; break;
        case "mp4": $ctype="video/mp4"; break;
        case "mov": $ctype="video/quicktime"; break;
        case "docx":
        case "doc": $ctype="application/msword"; break;
        case "pdf": $ctype="application/pdf"; break;
        case "eps": $ctype="application/postscript"; break;
        case "flv": $ctype="video/x-flv"; break;
        default:
            $ctype="download";
    }

    	if($ctype && $ctype!="download"){
    		if($this->urlpart[1]=='file'){
                if($GLOBALS['conf']['env'] == 'production') {
                    $opt = [
                        'content_type' => $ctype . '; charset=utf-8',
                        'save_as'=>rawurlencode($filename)
                    ];
                    CloudStorageTools::serve($GLOBALS['conf']['filepath_fileupload'].'/'.$file, $opt);
                } else {
                    header('Content-type: ' . $ctype . '; charset=utf-8');
                    header("Content-Disposition: inline; filename=".$filename);
                    echo file_get_contents($GLOBALS['conf']['filepath_fileupload'].'/'.$file);
                }
    		} elseif($this->urlpart[1]=='logo' || $this->urlpart[1]=='images'){
                header('Content-type: ' . $ctype . '; charset=utf-8');
                header("Content-Disposition: inline; filename=".$filename);
    			echo file_get_contents($GLOBALS['conf']['filepath_img'].'/'.$file);
    		} else {
                header('Content-type: ' . $ctype . '; charset=utf-8');
                header("Content-Disposition: inline; filename=".$filename);
    			echo file_get_contents($GLOBALS['conf']['filepath_support'].'/'.$file);
    		}
    	} elseif($ctype && $ctype=="download"){
            $filepath = $GLOBALS['conf']['filepath_fileupload'].'/'.$file;
            header('Content-Description: File Transfer');
    		    header('Content-type: application/octet-stream; charset=utf-8');
    		    header("Content-Disposition: attachment; filename=".$filename);
            header('Content-Transfer-Encoding: binary');
          //  header('Expires: 0');
            header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
            header('Pragma: public');
            //header('Content-Length: ' . filesize($filepath));
            ob_clean();
          //  flush();
          if($GLOBALS['conf']['env'] == 'production') {
            $opt = [
                'content_type' => $ctype . '; charset=utf-8',
                'save_as'=>rawurlencode($filename)
            ];
            CloudStorageTools::serve($filepath, $opt);
          } else {
           readfile($filepath);
         }
          // gs://formlets-1260-fileupload/fzMewAPkNfp9bRvgSqf9i9NDssO6QjVe.webm
          //echo $filepath;

    	} else {
    	  header("HTTP/1.0 404 Not Found");
    	}
    	exit;



}

function OutputBase64() {
    $submission = $this->lo->getSubmissions(array('id'=>$this->urlpart[2]));

    header('Content-Disposition: inline; filename="Signature.png"');
    header("Content-type: image/png");

    $sdata = str_replace('\\','',$submission[0]['data']);
    $submission[0]['data'] = json_decode($sdata,true);
    if(!$submission[0]['data']) {
        $submission[0]['data']=json_decode($submission[0]['data'],true);
    }
    $base64 = '';
    $datas = $submission[0]['data'];
    foreach($datas as $data) {
        if($data['_id'] == $this->urlpart[3]) {
            $base64 = $data['value'];
        }
    }

    $data = explode( ',', $base64 );

    echo base64_decode($data[1]);
}

function OutputFile(){
	$this->_displayFile();
}

function OutputLogo(){
	$this->_displayFile();
}

function OutputImages(){
	$this->_displayFile();
}

function OutputSupportimg() {
	$this->_displayFile();
}

function OutputEmbed() {
	header("Content-Type: application/javascript");
	header("Access-Control-Allow-Origin: *");
	$uri2 = $this->urlpart[2];
	$uri2 = explode('.', $uri2);
	$formid = $uri2[0];
	$form=$this->lo->getForm(array("form_id"=>$formid));

	$scripts = array();
	$styles = array();
	$html = file_get_contents($GLOBALS['protocol']."://".$_SERVER['HTTP_HOST']."/forms/".$formid."/");
	libxml_use_internal_errors(true); //Prevents Warnings, remove if desired
	$dom = new DOMDocument();
	$dom->loadHTML($html);

	$xpath = new DOMXPath($dom);
	$script_tags = $xpath->query('//body//script[not(@src)]');
	foreach ($script_tags as $script) {
	    $scripts[] = $script->nodeValue;
	}

	$style_tags = $xpath->query('//style');
	foreach ($style_tags as $style) {
	    $styles[] = $style->nodeValue;
	}

	$body = "";
	while (($r = $dom->getElementsByTagName("script")) && $r->length) {
        $r->item(0)->parentNode->removeChild($r->item(0));
    }
    //$dom = $dom->saveHTML();
	foreach($dom->getElementsByTagName("body")->item(0)->childNodes as $child) {
	    $body .= $dom->saveHTML($child);
	}

	$body = trim(preg_replace('/\t+/', ' ', $body));
	$body = trim(preg_replace('/\s+/', ' ', $body));

	$scripts = implode('',$scripts);
	$scripts = trim(preg_replace('/\t+/', ' ', $scripts));
	$scripts = trim(preg_replace('/\s+/', ' ', $scripts));

	$styles = implode('',$styles);
	$styles = trim(preg_replace('/\t+/', ' ', $styles));
	$styles = trim(preg_replace('/\s+/', ' ', $styles));

?>
/* Zepto v1.1.6 - zepto event ajax form ie - zeptojs.com/license */
var Zepto=function(){function L(t){return null==t?String(t):j[S.call(t)]||"object"}function Z(t){return"function"==L(t)}function _(t){return null!=t&&t==t.window}function $(t){return null!=t&&t.nodeType==t.DOCUMENT_NODE}function D(t){return"object"==L(t)}function M(t){return D(t)&&!_(t)&&Object.getPrototypeOf(t)==Object.prototype}function R(t){return"number"==typeof t.length}function k(t){return s.call(t,function(t){return null!=t})}function z(t){return t.length>0?n.fn.concat.apply([],t):t}function F(t){return t.replace(/::/g,"/").replace(/([A-Z]+)([A-Z][a-z])/g,"$1_$2").replace(/([a-z\d])([A-Z])/g,"$1_$2").replace(/_/g,"-").toLowerCase()}function q(t){return t in f?f[t]:f[t]=new RegExp("(^|\\s)"+t+"(\\s|$)")}function H(t,e){return"number"!=typeof e||c[F(t)]?e:e+"px"}function I(t){var e,n;return u[t]||(e=a.createElement(t),a.body.appendChild(e),n=getComputedStyle(e,"").getPropertyValue("display"),e.parentNode.removeChild(e),"none"==n&&(n="block"),u[t]=n),u[t]}function V(t){return"children"in t?o.call(t.children):n.map(t.childNodes,function(t){return 1==t.nodeType?t:void 0})}function B(n,i,r){for(e in i)r&&(M(i[e])||A(i[e]))?(M(i[e])&&!M(n[e])&&(n[e]={}),A(i[e])&&!A(n[e])&&(n[e]=[]),B(n[e],i[e],r)):i[e]!==t&&(n[e]=i[e])}function U(t,e){return null==e?n(t):n(t).filter(e)}function J(t,e,n,i){return Z(e)?e.call(t,n,i):e}function X(t,e,n){null==n?t.removeAttribute(e):t.setAttribute(e,n)}function W(e,n){var i=e.className||"",r=i&&i.baseVal!==t;return n===t?r?i.baseVal:i:void(r?i.baseVal=n:e.className=n)}function Y(t){try{return t?"true"==t||("false"==t?!1:"null"==t?null:+t+""==t?+t:/^[\[\{]/.test(t)?n.parseJSON(t):t):t}catch(e){return t}}function G(t,e){e(t);for(var n=0,i=t.childNodes.length;i>n;n++)G(t.childNodes[n],e)}var t,e,n,i,C,N,r=[],o=r.slice,s=r.filter,a=window.document,u={},f={},c={"column-count":1,columns:1,"font-weight":1,"line-height":1,opacity:1,"z-index":1,zoom:1},l=/^\s*<(\w+|!)[^>]*>/,h=/^<(\w+)\s*\/?>(?:<\/\1>|)$/,p=/<(?!area|br|col|embed|hr|img|input|link|meta|param)(([\w:]+)[^>]*)\/>/gi,d=/^(?:body|html)$/i,m=/([A-Z])/g,g=["val","css","html","text","data","width","height","offset"],v=["after","prepend","before","append"],y=a.createElement("table"),x=a.createElement("tr"),b={tr:a.createElement("tbody"),tbody:y,thead:y,tfoot:y,td:x,th:x,"*":a.createElement("div")},w=/complete|loaded|interactive/,E=/^[\w-]*$/,j={},S=j.toString,T={},O=a.createElement("div"),P={tabindex:"tabIndex",readonly:"readOnly","for":"htmlFor","class":"className",maxlength:"maxLength",cellspacing:"cellSpacing",cellpadding:"cellPadding",rowspan:"rowSpan",colspan:"colSpan",usemap:"useMap",frameborder:"frameBorder",contenteditable:"contentEditable"},A=Array.isArray||function(t){return t instanceof Array};return T.matches=function(t,e){if(!e||!t||1!==t.nodeType)return!1;var n=t.webkitMatchesSelector||t.mozMatchesSelector||t.oMatchesSelector||t.matchesSelector;if(n)return n.call(t,e);var i,r=t.parentNode,o=!r;return o&&(r=O).appendChild(t),i=~T.qsa(r,e).indexOf(t),o&&O.removeChild(t),i},C=function(t){return t.replace(/-+(.)?/g,function(t,e){return e?e.toUpperCase():""})},N=function(t){return s.call(t,function(e,n){return t.indexOf(e)==n})},T.fragment=function(e,i,r){var s,u,f;return h.test(e)&&(s=n(a.createElement(RegExp.$1))),s||(e.replace&&(e=e.replace(p,"<$1></$2>")),i===t&&(i=l.test(e)&&RegExp.$1),i in b||(i="*"),f=b[i],f.innerHTML=""+e,s=n.each(o.call(f.childNodes),function(){f.removeChild(this)})),M(r)&&(u=n(s),n.each(r,function(t,e){g.indexOf(t)>-1?u[t](e):u.attr(t,e)})),s},T.Z=function(t,e){return t=t||[],t.__proto__=n.fn,t.selector=e||"",t},T.isZ=function(t){return t instanceof T.Z},T.init=function(e,i){var r;if(!e)return T.Z();if("string"==typeof e)if(e=e.trim(),"<"==e[0]&&l.test(e))r=T.fragment(e,RegExp.$1,i),e=null;else{if(i!==t)return n(i).find(e);r=T.qsa(a,e)}else{if(Z(e))return n(a).ready(e);if(T.isZ(e))return e;if(A(e))r=k(e);else if(D(e))r=[e],e=null;else if(l.test(e))r=T.fragment(e.trim(),RegExp.$1,i),e=null;else{if(i!==t)return n(i).find(e);r=T.qsa(a,e)}}return T.Z(r,e)},n=function(t,e){return T.init(t,e)},n.extend=function(t){var e,n=o.call(arguments,1);return"boolean"==typeof t&&(e=t,t=n.shift()),n.forEach(function(n){B(t,n,e)}),t},T.qsa=function(t,e){var n,i="#"==e[0],r=!i&&"."==e[0],s=i||r?e.slice(1):e,a=E.test(s);return $(t)&&a&&i?(n=t.getElementById(s))?[n]:[]:1!==t.nodeType&&9!==t.nodeType?[]:o.call(a&&!i?r?t.getElementsByClassName(s):t.getElementsByTagName(e):t.querySelectorAll(e))},n.contains=a.documentElement.contains?function(t,e){return t!==e&&t.contains(e)}:function(t,e){for(;e&&(e=e.parentNode);)if(e===t)return!0;return!1},n.type=L,n.isFunction=Z,n.isWindow=_,n.isArray=A,n.isPlainObject=M,n.isEmptyObject=function(t){var e;for(e in t)return!1;return!0},n.inArray=function(t,e,n){return r.indexOf.call(e,t,n)},n.camelCase=C,n.trim=function(t){return null==t?"":String.prototype.trim.call(t)},n.uuid=0,n.support={},n.expr={},n.map=function(t,e){var n,r,o,i=[];if(R(t))for(r=0;r<t.length;r++)n=e(t[r],r),null!=n&&i.push(n);else for(o in t)n=e(t[o],o),null!=n&&i.push(n);return z(i)},n.each=function(t,e){var n,i;if(R(t)){for(n=0;n<t.length;n++)if(e.call(t[n],n,t[n])===!1)return t}else for(i in t)if(e.call(t[i],i,t[i])===!1)return t;return t},n.grep=function(t,e){return s.call(t,e)},window.JSON&&(n.parseJSON=JSON.parse),n.each("Boolean Number String Function Array Date RegExp Object Error".split(" "),function(t,e){j["[object "+e+"]"]=e.toLowerCase()}),n.fn={forEach:r.forEach,reduce:r.reduce,push:r.push,sort:r.sort,indexOf:r.indexOf,concat:r.concat,map:function(t){return n(n.map(this,function(e,n){return t.call(e,n,e)}))},slice:function(){return n(o.apply(this,arguments))},ready:function(t){return w.test(a.readyState)&&a.body?t(n):a.addEventListener("DOMContentLoaded",function(){t(n)},!1),this},get:function(e){return e===t?o.call(this):this[e>=0?e:e+this.length]},toArray:function(){return this.get()},size:function(){return this.length},remove:function(){return this.each(function(){null!=this.parentNode&&this.parentNode.removeChild(this)})},each:function(t){return r.every.call(this,function(e,n){return t.call(e,n,e)!==!1}),this},filter:function(t){return Z(t)?this.not(this.not(t)):n(s.call(this,function(e){return T.matches(e,t)}))},add:function(t,e){return n(N(this.concat(n(t,e))))},is:function(t){return this.length>0&&T.matches(this[0],t)},not:function(e){var i=[];if(Z(e)&&e.call!==t)this.each(function(t){e.call(this,t)||i.push(this)});else{var r="string"==typeof e?this.filter(e):R(e)&&Z(e.item)?o.call(e):n(e);this.forEach(function(t){r.indexOf(t)<0&&i.push(t)})}return n(i)},has:function(t){return this.filter(function(){return D(t)?n.contains(this,t):n(this).find(t).size()})},eq:function(t){return-1===t?this.slice(t):this.slice(t,+t+1)},first:function(){var t=this[0];return t&&!D(t)?t:n(t)},last:function(){var t=this[this.length-1];return t&&!D(t)?t:n(t)},find:function(t){var e,i=this;return e=t?"object"==typeof t?n(t).filter(function(){var t=this;return r.some.call(i,function(e){return n.contains(e,t)})}):1==this.length?n(T.qsa(this[0],t)):this.map(function(){return T.qsa(this,t)}):n()},closest:function(t,e){var i=this[0],r=!1;for("object"==typeof t&&(r=n(t));i&&!(r?r.indexOf(i)>=0:T.matches(i,t));)i=i!==e&&!$(i)&&i.parentNode;return n(i)},parents:function(t){for(var e=[],i=this;i.length>0;)i=n.map(i,function(t){return(t=t.parentNode)&&!$(t)&&e.indexOf(t)<0?(e.push(t),t):void 0});return U(e,t)},parent:function(t){return U(N(this.pluck("parentNode")),t)},children:function(t){return U(this.map(function(){return V(this)}),t)},contents:function(){return this.map(function(){return o.call(this.childNodes)})},siblings:function(t){return U(this.map(function(t,e){return s.call(V(e.parentNode),function(t){return t!==e})}),t)},empty:function(){return this.each(function(){this.innerHTML=""})},pluck:function(t){return n.map(this,function(e){return e[t]})},show:function(){return this.each(function(){"none"==this.style.display&&(this.style.display=""),"none"==getComputedStyle(this,"").getPropertyValue("display")&&(this.style.display=I(this.nodeName))})},replaceWith:function(t){return this.before(t).remove()},wrap:function(t){var e=Z(t);if(this[0]&&!e)var i=n(t).get(0),r=i.parentNode||this.length>1;return this.each(function(o){n(this).wrapAll(e?t.call(this,o):r?i.cloneNode(!0):i)})},wrapAll:function(t){if(this[0]){n(this[0]).before(t=n(t));for(var e;(e=t.children()).length;)t=e.first();n(t).append(this)}return this},wrapInner:function(t){var e=Z(t);return this.each(function(i){var r=n(this),o=r.contents(),s=e?t.call(this,i):t;o.length?o.wrapAll(s):r.append(s)})},unwrap:function(){return this.parent().each(function(){n(this).replaceWith(n(this).children())}),this},clone:function(){return this.map(function(){return this.cloneNode(!0)})},hide:function(){return this.css("display","none")},toggle:function(e){return this.each(function(){var i=n(this);(e===t?"none"==i.css("display"):e)?i.show():i.hide()})},prev:function(t){return n(this.pluck("previousElementSibling")).filter(t||"*")},next:function(t){return n(this.pluck("nextElementSibling")).filter(t||"*")},html:function(t){return 0 in arguments?this.each(function(e){var i=this.innerHTML;n(this).empty().append(J(this,t,e,i))}):0 in this?this[0].innerHTML:null},text:function(t){return 0 in arguments?this.each(function(e){var n=J(this,t,e,this.textContent);this.textContent=null==n?"":""+n}):0 in this?this[0].textContent:null},attr:function(n,i){var r;return"string"!=typeof n||1 in arguments?this.each(function(t){if(1===this.nodeType)if(D(n))for(e in n)X(this,e,n[e]);else X(this,n,J(this,i,t,this.getAttribute(n)))}):this.length&&1===this[0].nodeType?!(r=this[0].getAttribute(n))&&n in this[0]?this[0][n]:r:t},removeAttr:function(t){return this.each(function(){1===this.nodeType&&t.split(" ").forEach(function(t){X(this,t)},this)})},prop:function(t,e){return t=P[t]||t,1 in arguments?this.each(function(n){this[t]=J(this,e,n,this[t])}):this[0]&&this[0][t]},data:function(e,n){var i="data-"+e.replace(m,"-$1").toLowerCase(),r=1 in arguments?this.attr(i,n):this.attr(i);return null!==r?Y(r):t},val:function(t){return 0 in arguments?this.each(function(e){this.value=J(this,t,e,this.value)}):this[0]&&(this[0].multiple?n(this[0]).find("option").filter(function(){return this.selected}).pluck("value"):this[0].value)},offset:function(t){if(t)return this.each(function(e){var i=n(this),r=J(this,t,e,i.offset()),o=i.offsetParent().offset(),s={top:r.top-o.top,left:r.left-o.left};"static"==i.css("position")&&(s.position="relative"),i.css(s)});if(!this.length)return null;var e=this[0].getBoundingClientRect();return{left:e.left+window.pageXOffset,top:e.top+window.pageYOffset,width:Math.round(e.width),height:Math.round(e.height)}},css:function(t,i){if(arguments.length<2){var r,o=this[0];if(!o)return;if(r=getComputedStyle(o,""),"string"==typeof t)return o.style[C(t)]||r.getPropertyValue(t);if(A(t)){var s={};return n.each(t,function(t,e){s[e]=o.style[C(e)]||r.getPropertyValue(e)}),s}}var a="";if("string"==L(t))i||0===i?a=F(t)+":"+H(t,i):this.each(function(){this.style.removeProperty(F(t))});else for(e in t)t[e]||0===t[e]?a+=F(e)+":"+H(e,t[e])+";":this.each(function(){this.style.removeProperty(F(e))});return this.each(function(){this.style.cssText+=";"+a})},index:function(t){return t?this.indexOf(n(t)[0]):this.parent().children().indexOf(this[0])},hasClass:function(t){return t?r.some.call(this,function(t){return this.test(W(t))},q(t)):!1},addClass:function(t){return t?this.each(function(e){if("className"in this){i=[];var r=W(this),o=J(this,t,e,r);o.split(/\s+/g).forEach(function(t){n(this).hasClass(t)||i.push(t)},this),i.length&&W(this,r+(r?" ":"")+i.join(" "))}}):this},removeClass:function(e){return this.each(function(n){if("className"in this){if(e===t)return W(this,"");i=W(this),J(this,e,n,i).split(/\s+/g).forEach(function(t){i=i.replace(q(t)," ")}),W(this,i.trim())}})},toggleClass:function(e,i){return e?this.each(function(r){var o=n(this),s=J(this,e,r,W(this));s.split(/\s+/g).forEach(function(e){(i===t?!o.hasClass(e):i)?o.addClass(e):o.removeClass(e)})}):this},scrollTop:function(e){if(this.length){var n="scrollTop"in this[0];return e===t?n?this[0].scrollTop:this[0].pageYOffset:this.each(n?function(){this.scrollTop=e}:function(){this.scrollTo(this.scrollX,e)})}},scrollLeft:function(e){if(this.length){var n="scrollLeft"in this[0];return e===t?n?this[0].scrollLeft:this[0].pageXOffset:this.each(n?function(){this.scrollLeft=e}:function(){this.scrollTo(e,this.scrollY)})}},position:function(){if(this.length){var t=this[0],e=this.offsetParent(),i=this.offset(),r=d.test(e[0].nodeName)?{top:0,left:0}:e.offset();return i.top-=parseFloat(n(t).css("margin-top"))||0,i.left-=parseFloat(n(t).css("margin-left"))||0,r.top+=parseFloat(n(e[0]).css("border-top-width"))||0,r.left+=parseFloat(n(e[0]).css("border-left-width"))||0,{top:i.top-r.top,left:i.left-r.left}}},offsetParent:function(){return this.map(function(){for(var t=this.offsetParent||a.body;t&&!d.test(t.nodeName)&&"static"==n(t).css("position");)t=t.offsetParent;return t})}},n.fn.detach=n.fn.remove,["width","height"].forEach(function(e){var i=e.replace(/./,function(t){return t[0].toUpperCase()});n.fn[e]=function(r){var o,s=this[0];return r===t?_(s)?s["inner"+i]:$(s)?s.documentElement["scroll"+i]:(o=this.offset())&&o[e]:this.each(function(t){s=n(this),s.css(e,J(this,r,t,s[e]()))})}}),v.forEach(function(t,e){var i=e%2;n.fn[t]=function(){var t,o,r=n.map(arguments,function(e){return t=L(e),"object"==t||"array"==t||null==e?e:T.fragment(e)}),s=this.length>1;return r.length<1?this:this.each(function(t,u){o=i?u:u.parentNode,u=0==e?u.nextSibling:1==e?u.firstChild:2==e?u:null;var f=n.contains(a.documentElement,o);r.forEach(function(t){if(s)t=t.cloneNode(!0);else if(!o)return n(t).remove();o.insertBefore(t,u),f&&G(t,function(t){null==t.nodeName||"SCRIPT"!==t.nodeName.toUpperCase()||t.type&&"text/javascript"!==t.type||t.src||window.eval.call(window,t.innerHTML)})})})},n.fn[i?t+"To":"insert"+(e?"Before":"After")]=function(e){return n(e)[t](this),this}}),T.Z.prototype=n.fn,T.uniq=N,T.deserializeValue=Y,n.zepto=T,n}();window.Zepto=Zepto,void 0===window.$&&(window.$=Zepto),function(t){function l(t){return t._zid||(t._zid=e++)}function h(t,e,n,i){if(e=p(e),e.ns)var r=d(e.ns);return(s[l(t)]||[]).filter(function(t){return!(!t||e.e&&t.e!=e.e||e.ns&&!r.test(t.ns)||n&&l(t.fn)!==l(n)||i&&t.sel!=i)})}function p(t){var e=(""+t).split(".");return{e:e[0],ns:e.slice(1).sort().join(" ")}}function d(t){return new RegExp("(?:^| )"+t.replace(" "," .* ?")+"(?: |$)")}function m(t,e){return t.del&&!u&&t.e in f||!!e}function g(t){return c[t]||u&&f[t]||t}function v(e,i,r,o,a,u,f){var h=l(e),d=s[h]||(s[h]=[]);i.split(/\s/).forEach(function(i){if("ready"==i)return t(document).ready(r);var s=p(i);s.fn=r,s.sel=a,s.e in c&&(r=function(e){var n=e.relatedTarget;return!n||n!==this&&!t.contains(this,n)?s.fn.apply(this,arguments):void 0}),s.del=u;var l=u||r;s.proxy=function(t){if(t=j(t),!t.isImmediatePropagationStopped()){t.data=o;var i=l.apply(e,t._args==n?[t]:[t].concat(t._args));return i===!1&&(t.preventDefault(),t.stopPropagation()),i}},s.i=d.length,d.push(s),"addEventListener"in e&&e.addEventListener(g(s.e),s.proxy,m(s,f))})}function y(t,e,n,i,r){var o=l(t);(e||"").split(/\s/).forEach(function(e){h(t,e,n,i).forEach(function(e){delete s[o][e.i],"removeEventListener"in t&&t.removeEventListener(g(e.e),e.proxy,m(e,r))})})}function j(e,i){return(i||!e.isDefaultPrevented)&&(i||(i=e),t.each(E,function(t,n){var r=i[t];e[t]=function(){return this[n]=x,r&&r.apply(i,arguments)},e[n]=b}),(i.defaultPrevented!==n?i.defaultPrevented:"returnValue"in i?i.returnValue===!1:i.getPreventDefault&&i.getPreventDefault())&&(e.isDefaultPrevented=x)),e}function S(t){var e,i={originalEvent:t};for(e in t)w.test(e)||t[e]===n||(i[e]=t[e]);return j(i,t)}var n,e=1,i=Array.prototype.slice,r=t.isFunction,o=function(t){return"string"==typeof t},s={},a={},u="onfocusin"in window,f={focus:"focusin",blur:"focusout"},c={mouseenter:"mouseover",mouseleave:"mouseout"};a.click=a.mousedown=a.mouseup=a.mousemove="MouseEvents",t.event={add:v,remove:y},t.proxy=function(e,n){var s=2 in arguments&&i.call(arguments,2);if(r(e)){var a=function(){return e.apply(n,s?s.concat(i.call(arguments)):arguments)};return a._zid=l(e),a}if(o(n))return s?(s.unshift(e[n],e),t.proxy.apply(null,s)):t.proxy(e[n],e);throw new TypeError("expected function")},t.fn.bind=function(t,e,n){return this.on(t,e,n)},t.fn.unbind=function(t,e){return this.off(t,e)},t.fn.one=function(t,e,n,i){return this.on(t,e,n,i,1)};var x=function(){return!0},b=function(){return!1},w=/^([A-Z]|returnValue$|layer[XY]$)/,E={preventDefault:"isDefaultPrevented",stopImmediatePropagation:"isImmediatePropagationStopped",stopPropagation:"isPropagationStopped"};t.fn.delegate=function(t,e,n){return this.on(e,t,n)},t.fn.undelegate=function(t,e,n){return this.off(e,t,n)},t.fn.live=function(e,n){return t(document.body).delegate(this.selector,e,n),this},t.fn.die=function(e,n){return t(document.body).undelegate(this.selector,e,n),this},t.fn.on=function(e,s,a,u,f){var c,l,h=this;return e&&!o(e)?(t.each(e,function(t,e){h.on(t,s,a,e,f)}),h):(o(s)||r(u)||u===!1||(u=a,a=s,s=n),(r(a)||a===!1)&&(u=a,a=n),u===!1&&(u=b),h.each(function(n,r){f&&(c=function(t){return y(r,t.type,u),u.apply(this,arguments)}),s&&(l=function(e){var n,o=t(e.target).closest(s,r).get(0);return o&&o!==r?(n=t.extend(S(e),{currentTarget:o,liveFired:r}),(c||u).apply(o,[n].concat(i.call(arguments,1)))):void 0}),v(r,e,u,a,s,l||c)}))},t.fn.off=function(e,i,s){var a=this;return e&&!o(e)?(t.each(e,function(t,e){a.off(t,i,e)}),a):(o(i)||r(s)||s===!1||(s=i,i=n),s===!1&&(s=b),a.each(function(){y(this,e,s,i)}))},t.fn.trigger=function(e,n){return e=o(e)||t.isPlainObject(e)?t.Event(e):j(e),e._args=n,this.each(function(){e.type in f&&"function"==typeof this[e.type]?this[e.type]():"dispatchEvent"in this?this.dispatchEvent(e):t(this).triggerHandler(e,n)})},t.fn.triggerHandler=function(e,n){var i,r;return this.each(function(s,a){i=S(o(e)?t.Event(e):e),i._args=n,i.target=a,t.each(h(a,e.type||e),function(t,e){return r=e.proxy(i),i.isImmediatePropagationStopped()?!1:void 0})}),r},"focusin focusout focus blur load resize scroll unload click dblclick mousedown mouseup mousemove mouseover mouseout mouseenter mouseleave change select keydown keypress keyup error".split(" ").forEach(function(e){t.fn[e]=function(t){return 0 in arguments?this.bind(e,t):this.trigger(e)}}),t.Event=function(t,e){o(t)||(e=t,t=e.type);var n=document.createEvent(a[t]||"Events"),i=!0;if(e)for(var r in e)"bubbles"==r?i=!!e[r]:n[r]=e[r];return n.initEvent(t,i,!0),j(n)}}(Zepto),function(t){function h(e,n,i){var r=t.Event(n);return t(e).trigger(r,i),!r.isDefaultPrevented()}function p(t,e,i,r){return t.global?h(e||n,i,r):void 0}function d(e){e.global&&0===t.active++&&p(e,null,"ajaxStart")}function m(e){e.global&&!--t.active&&p(e,null,"ajaxStop")}function g(t,e){var n=e.context;return e.beforeSend.call(n,t,e)===!1||p(e,n,"ajaxBeforeSend",[t,e])===!1?!1:void p(e,n,"ajaxSend",[t,e])}function v(t,e,n,i){var r=n.context,o="success";n.success.call(r,t,o,e),i&&i.resolveWith(r,[t,o,e]),p(n,r,"ajaxSuccess",[e,n,t]),x(o,e,n)}function y(t,e,n,i,r){var o=i.context;i.error.call(o,n,e,t),r&&r.rejectWith(o,[n,e,t]),p(i,o,"ajaxError",[n,i,t||e]),x(e,n,i)}function x(t,e,n){var i=n.context;n.complete.call(i,e,t),p(n,i,"ajaxComplete",[e,n]),m(n)}function b(){}function w(t){return t&&(t=t.split(";",2)[0]),t&&(t==f?"html":t==u?"json":s.test(t)?"script":a.test(t)&&"xml")||"text"}function E(t,e){return""==e?t:(t+"&"+e).replace(/[&?]{1,2}/,"?")}function j(e){e.processData&&e.data&&"string"!=t.type(e.data)&&(e.data=t.param(e.data,e.traditional)),!e.data||e.type&&"GET"!=e.type.toUpperCase()||(e.url=E(e.url,e.data),e.data=void 0)}function S(e,n,i,r){return t.isFunction(n)&&(r=i,i=n,n=void 0),t.isFunction(i)||(r=i,i=void 0),{url:e,data:n,success:i,dataType:r}}function C(e,n,i,r){var o,s=t.isArray(n),a=t.isPlainObject(n);t.each(n,function(n,u){o=t.type(u),r&&(n=i?r:r+"["+(a||"object"==o||"array"==o?n:"")+"]"),!r&&s?e.add(u.name,u.value):"array"==o||!i&&"object"==o?C(e,u,i,n):e.add(n,u)})}var i,r,e=0,n=window.document,o=/<script\b[^<]*(?:(?!<\/script>)<[^<]*)*<\/script>/gi,s=/^(?:text|application)\/javascript/i,a=/^(?:text|application)\/xml/i,u="application/json",f="text/html",c=/^\s*$/,l=n.createElement("a");l.href=window.location.href,t.active=0,t.ajaxJSONP=function(i,r){if(!("type"in i))return t.ajax(i);var f,h,o=i.jsonpCallback,s=(t.isFunction(o)?o():o)||"jsonp"+ ++e,a=n.createElement("script"),u=window[s],c=function(e){t(a).triggerHandler("error",e||"abort")},l={abort:c};return r&&r.promise(l),t(a).on("load error",function(e,n){clearTimeout(h),t(a).off().remove(),"error"!=e.type&&f?v(f[0],l,i,r):y(null,n||"error",l,i,r),window[s]=u,f&&t.isFunction(u)&&u(f[0]),u=f=void 0}),g(l,i)===!1?(c("abort"),l):(window[s]=function(){f=arguments},a.src=i.url.replace(/\?(.+)=\?/,"?$1="+s),n.head.appendChild(a),i.timeout>0&&(h=setTimeout(function(){c("timeout")},i.timeout)),l)},t.ajaxSettings={type:"GET",beforeSend:b,success:b,error:b,complete:b,context:null,global:!0,xhr:function(){return new window.XMLHttpRequest},accepts:{script:"text/javascript, application/javascript, application/x-javascript",json:u,xml:"application/xml, text/xml",html:f,text:"text/plain"},crossDomain:!1,timeout:0,processData:!0,cache:!0},t.ajax=function(e){var a,o=t.extend({},e||{}),s=t.Deferred&&t.Deferred();for(i in t.ajaxSettings)void 0===o[i]&&(o[i]=t.ajaxSettings[i]);d(o),o.crossDomain||(a=n.createElement("a"),a.href=o.url,a.href=a.href,o.crossDomain=l.protocol+"//"+l.host!=a.protocol+"//"+a.host),o.url||(o.url=window.location.toString()),j(o);var u=o.dataType,f=/\?.+=\?/.test(o.url);if(f&&(u="jsonp"),o.cache!==!1&&(e&&e.cache===!0||"script"!=u&&"jsonp"!=u)||(o.url=E(o.url,"_="+Date.now())),"jsonp"==u)return f||(o.url=E(o.url,o.jsonp?o.jsonp+"=?":o.jsonp===!1?"":"callback=?")),t.ajaxJSONP(o,s);var C,h=o.accepts[u],p={},m=function(t,e){p[t.toLowerCase()]=[t,e]},x=/^([\w-]+:)\/\//.test(o.url)?RegExp.$1:window.location.protocol,S=o.xhr(),T=S.setRequestHeader;if(s&&s.promise(S),o.crossDomain||m("X-Requested-With","XMLHttpRequest"),m("Accept",h||"*/*"),(h=o.mimeType||h)&&(h.indexOf(",")>-1&&(h=h.split(",",2)[0]),S.overrideMimeType&&S.overrideMimeType(h)),(o.contentType||o.contentType!==!1&&o.data&&"GET"!=o.type.toUpperCase())&&m("Content-Type",o.contentType||"application/x-www-form-urlencoded"),o.headers)for(r in o.headers)m(r,o.headers[r]);if(S.setRequestHeader=m,S.onreadystatechange=function(){if(4==S.readyState){S.onreadystatechange=b,clearTimeout(C);var e,n=!1;if(S.status>=200&&S.status<300||304==S.status||0==S.status&&"file:"==x){u=u||w(o.mimeType||S.getResponseHeader("content-type")),e=S.responseText;try{"script"==u?(1,eval)(e):"xml"==u?e=S.responseXML:"json"==u&&(e=c.test(e)?null:t.parseJSON(e))}catch(i){n=i}n?y(n,"parsererror",S,o,s):v(e,S,o,s)}else y(S.statusText||null,S.status?"error":"abort",S,o,s)}},g(S,o)===!1)return S.abort(),y(null,"abort",S,o,s),S;if(o.xhrFields)for(r in o.xhrFields)S[r]=o.xhrFields[r];var N="async"in o?o.async:!0;S.open(o.type,o.url,N,o.username,o.password);for(r in p)T.apply(S,p[r]);return o.timeout>0&&(C=setTimeout(function(){S.onreadystatechange=b,S.abort(),y(null,"timeout",S,o,s)},o.timeout)),S.send(o.data?o.data:null),S},t.get=function(){return t.ajax(S.apply(null,arguments))},t.post=function(){var e=S.apply(null,arguments);return e.type="POST",t.ajax(e)},t.getJSON=function(){var e=S.apply(null,arguments);return e.dataType="json",t.ajax(e)},t.fn.load=function(e,n,i){if(!this.length)return this;var a,r=this,s=e.split(/\s/),u=S(e,n,i),f=u.success;return s.length>1&&(u.url=s[0],a=s[1]),u.success=function(e){r.html(a?t("<div>").html(e.replace(o,"")).find(a):e),f&&f.apply(r,arguments)},t.ajax(u),this};var T=encodeURIComponent;t.param=function(e,n){var i=[];return i.add=function(e,n){t.isFunction(n)&&(n=n()),null==n&&(n=""),this.push(T(e)+"="+T(n))},C(i,e,n),i.join("&").replace(/%20/g,"+")}}(Zepto),function(t){t.fn.serializeArray=function(){var e,n,i=[],r=function(t){return t.forEach?t.forEach(r):void i.push({name:e,value:t})};return this[0]&&t.each(this[0].elements,function(i,o){n=o.type,e=o.name,e&&"fieldset"!=o.nodeName.toLowerCase()&&!o.disabled&&"submit"!=n&&"reset"!=n&&"button"!=n&&"file"!=n&&("radio"!=n&&"checkbox"!=n||o.checked)&&r(t(o).val())}),i},t.fn.serialize=function(){var t=[];return this.serializeArray().forEach(function(e){t.push(encodeURIComponent(e.name)+"="+encodeURIComponent(e.value))}),t.join("&")},t.fn.submit=function(e){if(0 in arguments)this.bind("submit",e);else if(this.length){var n=t.Event("submit");this.eq(0).trigger(n),n.isDefaultPrevented()||this.get(0).submit()}return this}}(Zepto),function(t){"__proto__"in{}||t.extend(t.zepto,{Z:function(e,n){return e=e||[],t.extend(e,t.fn),e.selector=n||"",e.__Z=!0,e},isZ:function(e){return"array"===t.type(e)&&"__Z"in e}});try{getComputedStyle(void 0)}catch(e){var n=getComputedStyle;window.getComputedStyle=function(t){try{return n(t)}catch(e){return null}}}}(Zepto);
/*
 * Zepto.fn.autoResize 1.0
 *
 */
!function(a){"use strict";function e(b){b||(b={}),this.filter(e.resizableFilterSelector).each(function(){new f(a(this),b)})}function f(b,d){c(b)&&c(d)&&("original"===d.minHeight&&(d.minHeight=b.height()),this.config={},a.extend(this.config,e.defaults,d),this.el=b,this.previousScrollTop=null,this.value=b.val(),this.createClone(),this.injectClone(),this.bind())}var b={onResize:function(){},animate:{duration:200,complete:function(){}},minHeight:"original",bottomPadding:0,maxHeight:1e3},c=function(a){return void 0!==a&&null!==a};e.cloneCSSProperties=["lineHeight","textDecoration","letterSpacing","fontSize","fontFamily","fontStyle","fontWeight","textTransform","textAlign","direction","wordSpacing","fontSizeAdjust","padding"],e.cloneCSSValues={position:"absolute",top:-9999,left:-9999,opacity:0,overflowY:"hidden",resize:"none"},e.resizableFilterSelector="textarea",e.defaults=b,e.AutoResizer=f,a.fn.autoResize=e,f.prototype={bind:function(){var b=a.proxy(function(){return this.check(),!0},this);this.unbind(),this.el.on("keyup.autoResize input.autoResize paste.autoResize click.autoResize change.autoResize",b),this.check(null,!0)},unbind:function(){this.el.off(".autoResize")},createClone:function(){var c,b=this.el;parseInt(b.css("lineHeight"),10);b.attr("scrollHeight"),c=b.clone().height("89px"),this.clone=c,a.each(e.cloneCSSProperties,function(a,d){c.css(d,b.css(d))}),c.removeAttr("name").removeAttr("id").removeAttr("class").attr("tabIndex",-1).css(e.cloneCSSValues)},check:function(a,b){var c=this.config,d=this.clone,e=this.el,f=e.val();d.height(0).val(f).scrollTop(1e4);var g=d.scrollTop()+c.bottomPadding;if(this.previousScrollTop!==g){if(this.previousScrollTop=g,g>=c.maxHeight)return void e.css("overflowY","");e.css("overflowY","hidden"),g<c.minHeight&&(g=c.minHeight),c.onResize.call(e),c.animate&&!b&&e.animate?e.animate({height:g},c.animate):e.height(g)}},injectClone:function(){e.cloneContainer||(e.cloneContainer=a("<div />",{id:"ZEPTO_AUTORESIZER_CLONE_CONTAINER"}).appendTo("body")),e.cloneContainer.append(this.clone)}}}(Zepto);
/*
 * jQuery's $.fn.data() for Zepto
 *
 */
!function(a){function g(f,g){var i=f[e],j=i&&b[i];if(void 0===g)return j||h(f);if(j){if(g in j)return j[g];var k=d(g);if(k in j)return j[k]}return c.call(a(f),g)}function h(c,f,g){var h=c[e]||(c[e]=++a.uuid),j=b[h]||(b[h]=i(c));return void 0!==f&&(j[d(f)]=g),j}function i(b){var c={};return a.each(b.attributes||f,function(b,e){0==e.name.indexOf("data-")&&(c[d(e.name.replace("data-",""))]=a.zepto.deserializeValue(e.value))}),c}var b={},c=a.fn.data,d=a.camelCase,e=a.expando="Zepto"+ +new Date,f=[];a.fn.data=function(b,c){return void 0===c?a.isPlainObject(b)?this.each(function(c,d){a.each(b,function(a,b){h(d,a,b)})}):0 in this?g(this[0],b):void 0:this.each(function(){h(this,b,c)})},a.data=function(b,c,d){return a(b).data(c,d)},a.hasData=function(c){var d=c[e],f=d&&b[d];return!!f&&!a.isEmptyObject(f)},a.fn.removeData=function(c){return"string"==typeof c&&(c=c.split(/\s+/)),this.each(function(){var f=this[e],g=f&&b[f];g&&a.each(c||g,function(a){delete g[c?d(this):a]})})},["remove","empty"].forEach(function(b){var c=a.fn[b];a.fn[b]=function(){var a=this.find("*");return"remove"===b&&(a=a.add(this)),a.removeData(),c.call(this)}})}(Zepto);

/* mask.js */
"use strict";!function(a,b,c){"function"==typeof define&&define.amd?define(["jquery"],a):"object"==typeof exports?module.exports=a(require("jquery")):a(b||c)}(function(a){var b=function(b,c,d){var e={invalid:[],getCaret:function(){try{var a,c=0,d=b.get(0),f=document.selection,g=d.selectionStart;return f&&-1===navigator.appVersion.indexOf("MSIE 10")?(a=f.createRange(),a.moveStart("character",-e.val().length),c=a.text.length):(g||"0"===g)&&(c=g),c}catch(a){}},setCaret:function(a){try{if(b.is(":focus")){var c,d=b.get(0);d.setSelectionRange?d.setSelectionRange(a,a):(c=d.createTextRange(),c.collapse(!0),c.moveEnd("character",a),c.moveStart("character",a),c.select())}}catch(a){}},events:function(){b.on("keydown.mask",function(a){b.data("mask-keycode",a.keyCode||a.which),b.data("mask-previus-value",b.val())}).on(a.jMaskGlobals.useInput?"input.mask":"keyup.mask",e.behaviour).on("paste.mask drop.mask",function(){setTimeout(function(){b.keydown().keyup()},100)}).on("change.mask",function(){b.data("changed",!0)}).on("blur.mask",function(){g===e.val()||b.data("changed")||b.trigger("change"),b.data("changed",!1)}).on("blur.mask",function(){g=e.val()}).on("focus.mask",function(b){!0===d.selectOnFocus&&a(b.target).select()}).on("focusout.mask",function(){d.clearIfNotMatch&&!h.test(e.val())&&e.val("")})},getRegexMask:function(){for(var b,d,e,g,h,i,a=[],j=0;j<c.length;j++)b=f.translation[c.charAt(j)],b?(d=b.pattern.toString().replace(/.{1}$|^.{1}/g,""),e=b.optional,g=b.recursive,g?(a.push(c.charAt(j)),h={digit:c.charAt(j),pattern:d}):a.push(e||g?d+"?":d)):a.push(c.charAt(j).replace(/[-\/\\^$*+?.()|[\]{}]/g,"\\$&"));return i=a.join(""),h&&(i=i.replace(new RegExp("("+h.digit+"(.*"+h.digit+")?)"),"($1)?").replace(new RegExp(h.digit,"g"),h.pattern)),new RegExp(i)},destroyEvents:function(){b.off(["input","keydown","keyup","paste","drop","blur","focusout",""].join(".mask "))},val:function(a){var e,c=b.is("input"),d=c?"val":"text";return arguments.length>0?(b[d]()!==a&&b[d](a),e=b):e=b[d](),e},calculateCaretPosition:function(a,c){var d=c.length,e=b.data("mask-previus-value")||"",f=e.length;return 8===b.data("mask-keycode")&&e!==c?a-=c.slice(0,a).length-e.slice(0,a).length:e!==c&&(a>=f?a=d:a+=c.slice(0,a).length-e.slice(0,a).length),a},behaviour:function(c){c=c||window.event,e.invalid=[];var d=b.data("mask-keycode");if(-1===a.inArray(d,f.byPassKeys)){var g=e.getMasked(),h=e.getCaret();return setTimeout(function(a,b){e.setCaret(e.calculateCaretPosition(a,b))},10,h,g),e.val(g),e.setCaret(h),e.callbacks(c)}},getMasked:function(a,b){var p,q,g=[],h=void 0===b?e.val():b+"",i=0,j=c.length,k=0,l=h.length,m=1,n="push",o=-1;d.reverse?(n="unshift",m=-1,p=0,i=j-1,k=l-1,q=function(){return i>-1&&k>-1}):(p=j-1,q=function(){return i<j&&k<l});for(var r;q();){var s=c.charAt(i),t=h.charAt(k),u=f.translation[s];u?(t.match(u.pattern)?(g[n](t),u.recursive&&(-1===o?o=i:i===p&&(i=o-m),p===o&&(i-=m)),i+=m):t===r?r=void 0:u.optional?(i+=m,k-=m):u.fallback?(g[n](u.fallback),i+=m,k-=m):e.invalid.push({p:k,v:t,e:u.pattern}),k+=m):(a||g[n](s),t===s?k+=m:r=s,i+=m)}var v=c.charAt(p);return j!==l+1||f.translation[v]||g.push(v),g.join("")},callbacks:function(a){var f=e.val(),h=f!==g,i=[f,a,b,d],j=function(a,b,c){"function"==typeof d[a]&&b&&d[a].apply(this,c)};j("onChange",!0===h,i),j("onKeyPress",!0===h,i),j("onComplete",f.length===c.length,i),j("onInvalid",e.invalid.length>0,[f,a,b,e.invalid,d])}};b=a(b);var h,f=this,g=e.val();c="function"==typeof c?c(e.val(),void 0,b,d):c,f.mask=c,f.options=d,f.remove=function(){var a=e.getCaret();return e.destroyEvents(),e.val(f.getCleanVal()),e.setCaret(a),b},f.getCleanVal=function(){return e.getMasked(!0)},f.getMaskedVal=function(a){return e.getMasked(!1,a)},f.init=function(g){if(g=g||!1,d=d||{},f.clearIfNotMatch=a.jMaskGlobals.clearIfNotMatch,f.byPassKeys=a.jMaskGlobals.byPassKeys,f.translation=a.extend({},a.jMaskGlobals.translation,d.translation),f=a.extend(!0,{},f,d),h=e.getRegexMask(),g)e.events(),e.val(e.getMasked());else{d.placeholder&&b.attr("placeholder",d.placeholder),b.data("mask")&&b.attr("autocomplete","off");for(var i=0,j=!0;i<c.length;i++){var k=f.translation[c.charAt(i)];if(k&&k.recursive){j=!1;break}}j&&b.attr("maxlength",c.length),e.destroyEvents(),e.events();var l=e.getCaret();e.val(e.getMasked()),e.setCaret(l)}},f.init(!b.is("input"))};a.maskWatchers={};var c=function(){var c=a(this),e={},f="data-mask-",g=c.attr("data-mask");if(c.attr(f+"reverse")&&(e.reverse=!0),c.attr(f+"clearifnotmatch")&&(e.clearIfNotMatch=!0),"true"===c.attr(f+"selectonfocus")&&(e.selectOnFocus=!0),d(c,g,e))return c.data("mask",new b(this,g,e))},d=function(b,c,d){d=d||{};var e=a(b).data("mask"),f=JSON.stringify,g=a(b).val()||a(b).text();try{return"function"==typeof c&&(c=c(g)),"object"!=typeof e||f(e.options)!==f(d)||e.mask!==c}catch(a){}},e=function(a){var c,b=document.createElement("div");return a="on"+a,c=a in b,c||(b.setAttribute(a,"return;"),c="function"==typeof b[a]),b=null,c};a.fn.mask=function(c,e){e=e||{};var f=this.selector,g=a.jMaskGlobals,h=g.watchInterval,i=e.watchInputs||g.watchInputs,j=function(){if(d(this,c,e))return a(this).data("mask",new b(this,c,e))};return a(this).each(j),f&&""!==f&&i&&(clearInterval(a.maskWatchers[f]),a.maskWatchers[f]=setInterval(function(){a(document).find(f).each(j)},h)),this},a.fn.masked=function(a){return this.data("mask").getMaskedVal(a)},a.fn.unmask=function(){return clearInterval(a.maskWatchers[this.selector]),delete a.maskWatchers[this.selector],this.each(function(){var b=a(this).data("mask");b&&b.remove().removeData("mask")})},a.fn.cleanVal=function(){return this.data("mask").getCleanVal()},a.applyDataMask=function(b){b=b||a.jMaskGlobals.maskElements,(b instanceof a?b:a(b)).filter(a.jMaskGlobals.dataMaskAttr).each(c)};var f={maskElements:"input,td,span,div",dataMaskAttr:"*[data-mask]",dataMask:!0,watchInterval:300,watchInputs:!0,useInput:!/Chrome\/[2-4][0-9]|SamsungBrowser/.test(window.navigator.userAgent)&&e("input"),watchDataMask:!1,byPassKeys:[9,16,17,18,36,37,38,39,40,91],translation:{0:{pattern:/\d/},9:{pattern:/\d/,optional:!0},"#":{pattern:/\d/,recursive:!0},A:{pattern:/[a-zA-Z0-9]/},S:{pattern:/[a-zA-Z]/}}};a.jMaskGlobals=a.jMaskGlobals||{},f=a.jMaskGlobals=a.extend(!0,{},f,a.jMaskGlobals),f.dataMask&&a.applyDataMask(),setInterval(function(){a.jMaskGlobals.watchDataMask&&a.applyDataMask()},f.watchInterval)},window.jQuery,window.Zepto);

/*! flatpickr v2.5.6, @license MIT */
function Flatpickr(e,t){function n(e){return e.bind(pe)}function a(e){pe.config.noCalendar&&!pe.selectedDates.length&&(pe.selectedDates=[pe.now]),me(e),pe.selectedDates.length&&(!pe.minDateHasTime||"input"!==e.type||e.target.value.length>=2?(i(),re()):setTimeout(function(){i(),re()},1e3))}function i(){if(pe.config.enableTime){var e=(parseInt(pe.hourElement.value,10)||0)%(pe.amPM?12:24),t=(parseInt(pe.minuteElement.value,10)||0)%60,n=pe.config.enableSeconds?parseInt(pe.secondElement.value,10)||0:0;pe.amPM&&(e=e%12+12*("PM"===pe.amPM.textContent)),pe.minDateHasTime&&0===ge(pe.latestSelectedDateObj,pe.config.minDate)&&(e=Math.max(e,pe.config.minDate.getHours()))===pe.config.minDate.getHours()&&(t=Math.max(t,pe.config.minDate.getMinutes())),pe.maxDateHasTime&&0===ge(pe.latestSelectedDateObj,pe.config.maxDate)&&(e=Math.min(e,pe.config.maxDate.getHours()))===pe.config.maxDate.getHours()&&(t=Math.min(t,pe.config.maxDate.getMinutes())),o(e,t,n)}}function r(e){var t=e||pe.latestSelectedDateObj;t&&o(t.getHours(),t.getMinutes(),t.getSeconds())}function o(e,t,n){pe.selectedDates.length&&pe.latestSelectedDateObj.setHours(e%24,t,n||0,0),pe.config.enableTime&&!pe.isMobile&&(pe.hourElement.value=pe.pad(pe.config.time_24hr?e:(12+e)%12+12*(e%12==0)),pe.minuteElement.value=pe.pad(t),pe.config.time_24hr||(pe.amPM.textContent=e>=12?"PM":"AM"),pe.config.enableSeconds&&(pe.secondElement.value=pe.pad(n)))}function l(e){var t=e.target.value;e.delta&&(t=(parseInt(t)+e.delta).toString()),4===t.length&&(pe.currentYearElement.blur(),/[^\d]/.test(t)||I(t))}function c(e,t,n){if(t instanceof Array)return t.forEach(function(t){return c(e,t,n)});e.addEventListener(t,n),pe._handlers.push({element:e,event:t,handler:n})}function s(e){return function(t){return 1===t.which&&e(t)}}function d(){if(pe._handlers=[],pe.config.wrap&&["open","close","toggle","clear"].forEach(function(e){Array.prototype.forEach.call(pe.element.querySelectorAll("[data-"+e+"]"),function(t){return c(t,"mousedown",s(pe[e]))})}),pe.isMobile)return G();pe.debouncedResize=fe(P,50),pe.triggerChange=function(){ee("Change")},pe.debouncedChange=fe(pe.triggerChange,300),"range"===pe.config.mode&&pe.daysContainer&&c(pe.daysContainer,"mouseover",function(e){return L(e.target)}),c(window.document.body,"keydown",A),pe.config.static||c(pe._input,"keydown",A),pe.config.inline||pe.config.static||c(window,"resize",pe.debouncedResize),window.ontouchstart&&c(window.document,"touchstart",Y),c(window.document,"mousedown",s(Y)),c(pe._input,"blur",Y),pe.config.clickOpens&&c(pe._input,"focus",j),pe.config.noCalendar||(c(pe.prevMonthNav,"mousedown",s(pe.prevMonthFn=function(){return E(-1)})),c(pe.nextMonthNav,"mousedown",s(pe.nextMonthFn=function(){return E(1)})),pe.monthNav.addEventListener("wheel",function(e){return e.preventDefault()}),c(pe.monthNav,"wheel",fe(le,10)),c(pe.monthNav,"mousedown",s(ce)),c(pe.currentYearElement,"focus",function(){pe.currentYearElement.select()}),c(pe.currentYearElement,["input","increment"],l),c(pe.daysContainer,"mousedown",s(J)),pe.config.animate&&(c(pe.daysContainer,["webkitAnimationEnd","animationend"],u),c(pe.monthNav,["webkitAnimationEnd","animationend"],f))),pe.config.enableTime&&(c(pe.timeContainer,["wheel","input","increment"],a),c(pe.timeContainer,"mousedown",s(m)),c(pe.timeContainer,["wheel","increment"],pe.debouncedChange),c(pe.timeContainer,"input",pe.triggerChange),c(pe.hourElement,"focus",function(){return pe.hourElement.select()}),c(pe.minuteElement,"focus",function(){return pe.minuteElement.select()}),pe.secondElement&&c(pe.secondElement,"focus",function(){return pe.secondElement.select()}),pe.amPM&&c(pe.amPM,"mousedown",s(function(e){a(e),pe.triggerChange(e)})))}function u(e){if(pe.daysContainer.childNodes.length>1)switch(e.animationName){case"slideLeft":pe.daysContainer.lastChild.classList.remove("slideLeftNew"),pe.daysContainer.removeChild(pe.daysContainer.firstChild),pe.days=pe.daysContainer.firstChild;break;case"slideRight":pe.daysContainer.firstChild.classList.remove("slideRightNew"),pe.daysContainer.removeChild(pe.daysContainer.lastChild),pe.days=pe.daysContainer.firstChild}}function f(e){switch(e.animationName){case"slideLeftNew":case"slideRightNew":pe.navigationCurrentMonth.classList.remove("slideLeftNew"),pe.navigationCurrentMonth.classList.remove("slideRightNew");for(var t=pe.navigationCurrentMonth;t.nextSibling&&/curr/.test(t.nextSibling.className);)pe.monthNav.removeChild(t.nextSibling);for(;t.previousSibling&&/curr/.test(t.previousSibling.className);)pe.monthNav.removeChild(t.previousSibling);pe.oldCurMonth=null}}function g(e){e=e?pe.parseDate(e):pe.latestSelectedDateObj||(pe.config.minDate>pe.now?pe.config.minDate:pe.config.maxDate&&pe.config.maxDate<pe.now?pe.config.maxDate:pe.now);try{pe.currentYear=e.getFullYear(),pe.currentMonth=e.getMonth()}catch(t){console.error(t.stack),console.warn("Invalid date supplied: "+e)}pe.redraw()}function m(e){~e.target.className.indexOf("arrow")&&p(e,e.target.classList.contains("arrowUp")?1:-1)}function p(e,t,n){var a=n||e.target.parentNode.childNodes[0],i=te("increment");i.delta=t,a.dispatchEvent(i)}function h(e){var t=se("div","numInputWrapper"),n=se("input","numInput "+e),a=se("span","arrowUp"),i=se("span","arrowDown");return n.type="text",n.pattern="\\d*",t.appendChild(n),t.appendChild(a),t.appendChild(i),t}function D(){var e=window.document.createDocumentFragment();pe.calendarContainer=se("div","flatpickr-calendar"),pe.calendarContainer.tabIndex=-1,pe.config.noCalendar||(e.appendChild(y()),pe.innerContainer=se("div","flatpickr-innerContainer"),pe.config.weekNumbers&&pe.innerContainer.appendChild(T()),pe.rContainer=se("div","flatpickr-rContainer"),pe.rContainer.appendChild(x()),pe.daysContainer||(pe.daysContainer=se("div","flatpickr-days"),pe.daysContainer.tabIndex=-1),b(),pe.rContainer.appendChild(pe.daysContainer),pe.innerContainer.appendChild(pe.rContainer),e.appendChild(pe.innerContainer)),pe.config.enableTime&&e.appendChild(k()),ue(pe.calendarContainer,"rangeMode","range"===pe.config.mode),ue(pe.calendarContainer,"animate",pe.config.animate),pe.calendarContainer.appendChild(e);var t=pe.config.appendTo&&pe.config.appendTo.nodeType;if(pe.config.inline||pe.config.static){if(pe.calendarContainer.classList.add(pe.config.inline?"inline":"static"),pe.config.inline&&!t)return pe.element.parentNode.insertBefore(pe.calendarContainer,pe._input.nextSibling);if(pe.config.static){var n=se("div","flatpickr-wrapper");return pe.element.parentNode.insertBefore(n,pe.element),n.appendChild(pe.element),pe.altInput&&n.appendChild(pe.altInput),void n.appendChild(pe.calendarContainer)}}(t?pe.config.appendTo:window.document.body).appendChild(pe.calendarContainer)}function v(e,t,n,a){var i=O(t,!0),r=se("span","flatpickr-day "+e,t.getDate());return r.dateObj=t,r.$i=a,r.setAttribute("aria-label",pe.formatDate(t,"F j, Y")),0===ge(t,pe.now)&&(pe.todayDateElem=r,r.classList.add("today")),i?(r.tabIndex=-1,ne(t)&&(r.classList.add("selected"),pe.selectedDateElem=r,"range"===pe.config.mode&&(ue(r,"startRange",0===ge(t,pe.selectedDates[0])),ue(r,"endRange",0===ge(t,pe.selectedDates[1]))))):(r.classList.add("disabled"),pe.selectedDates[0]&&t>pe.minRangeDate&&t<pe.selectedDates[0]?pe.minRangeDate=t:pe.selectedDates[0]&&t<pe.maxRangeDate&&t>pe.selectedDates[0]&&(pe.maxRangeDate=t)),"range"===pe.config.mode&&(ae(t)&&!ne(t)&&r.classList.add("inRange"),1===pe.selectedDates.length&&(t<pe.minRangeDate||t>pe.maxRangeDate)&&r.classList.add("notAllowed")),pe.config.weekNumbers&&"prevMonthDay"!==e&&n%7==1&&pe.weekNumbers.insertAdjacentHTML("beforeend","<span class='disabled flatpickr-day'>"+pe.config.getWeek(t)+"</span>"),ee("DayCreate",r),r}function C(e,t){var n=e+t||0,a=void 0!==e?pe.days.childNodes[n]:pe.selectedDateElem||pe.todayDateElem||pe.days.childNodes[0],i=function(){a=a||pe.days.childNodes[n],a.focus(),"range"===pe.config.mode&&L(a)};if(void 0===a&&0!==t)return t>0?(pe.changeMonth(1),n%=42):t<0&&(pe.changeMonth(-1),n+=42),w(i);i()}function w(e){if(pe.config.animate)return setTimeout(e,pe._.daysAnimDuration+1);e()}function b(e){var t=(new Date(pe.currentYear,pe.currentMonth,1).getDay()-pe.l10n.firstDayOfWeek+7)%7,n="range"===pe.config.mode;pe.prevMonthDays=pe.utils.getDaysinMonth((pe.currentMonth-1+12)%12),pe.selectedDateElem=void 0,pe.todayDateElem=void 0;var a=pe.utils.getDaysinMonth(),i=window.document.createDocumentFragment(),r=pe.prevMonthDays+1-t,o=0;for(pe.config.weekNumbers&&pe.weekNumbers.firstChild&&(pe.weekNumbers.textContent=""),n&&(pe.minRangeDate=new Date(pe.currentYear,pe.currentMonth-1,r),pe.maxRangeDate=new Date(pe.currentYear,pe.currentMonth+1,(42-t)%a));r<=pe.prevMonthDays;r++,o++)i.appendChild(v("prevMonthDay",new Date(pe.currentYear,pe.currentMonth-1,r),r,o));for(r=1;r<=a;r++,o++)i.appendChild(v("",new Date(pe.currentYear,pe.currentMonth,r),r,o));for(var l=a+1;l<=42-t;l++,o++)i.appendChild(v("nextMonthDay",new Date(pe.currentYear,pe.currentMonth+1,l%a),l,o));n&&1===pe.selectedDates.length&&i.childNodes[0]?(pe._hidePrevMonthArrow=pe._hidePrevMonthArrow||pe.minRangeDate>i.childNodes[0].dateObj,pe._hideNextMonthArrow=pe._hideNextMonthArrow||pe.maxRangeDate<new Date(pe.currentYear,pe.currentMonth+1,1)):ie();var c=se("div","dayContainer");if(c.appendChild(i),pe.config.animate&&void 0!==e)for(;pe.daysContainer.childNodes.length>1;)pe.daysContainer.removeChild(pe.daysContainer.firstChild);else M(pe.daysContainer);return e>=0?pe.daysContainer.appendChild(c):pe.daysContainer.insertBefore(c,pe.daysContainer.firstChild),pe.days=pe.daysContainer.firstChild,pe.daysContainer}function M(e){for(;e.firstChild;)e.removeChild(e.firstChild)}function y(){var e=window.document.createDocumentFragment();pe.monthNav=se("div","flatpickr-month"),pe.prevMonthNav=se("span","flatpickr-prev-month"),pe.prevMonthNav.innerHTML=pe.config.prevArrow,pe.currentMonthElement=se("span","cur-month"),pe.currentMonthElement.title=pe.l10n.scrollTitle;var t=h("cur-year");return pe.currentYearElement=t.childNodes[0],pe.currentYearElement.title=pe.l10n.scrollTitle,pe.config.minDate&&(pe.currentYearElement.min=pe.config.minDate.getFullYear()),pe.config.maxDate&&(pe.currentYearElement.max=pe.config.maxDate.getFullYear(),pe.currentYearElement.disabled=pe.config.minDate&&pe.config.minDate.getFullYear()===pe.config.maxDate.getFullYear()),pe.nextMonthNav=se("span","flatpickr-next-month"),pe.nextMonthNav.innerHTML=pe.config.nextArrow,pe.navigationCurrentMonth=se("span","flatpickr-current-month"),pe.navigationCurrentMonth.appendChild(pe.currentMonthElement),pe.navigationCurrentMonth.appendChild(t),e.appendChild(pe.prevMonthNav),e.appendChild(pe.navigationCurrentMonth),e.appendChild(pe.nextMonthNav),pe.monthNav.appendChild(e),Object.defineProperty(pe,"_hidePrevMonthArrow",{get:function(){return this.__hidePrevMonthArrow},set:function(e){this.__hidePrevMonthArrow!==e&&(pe.prevMonthNav.style.display=e?"none":"block"),this.__hidePrevMonthArrow=e}}),Object.defineProperty(pe,"_hideNextMonthArrow",{get:function(){return this.__hideNextMonthArrow},set:function(e){this.__hideNextMonthArrow!==e&&(pe.nextMonthNav.style.display=e?"none":"block"),this.__hideNextMonthArrow=e}}),ie(),pe.monthNav}function k(){pe.calendarContainer.classList.add("hasTime"),pe.config.noCalendar&&pe.calendarContainer.classList.add("noCalendar"),pe.timeContainer=se("div","flatpickr-time"),pe.timeContainer.tabIndex=-1;var e=se("span","flatpickr-time-separator",":"),t=h("flatpickr-hour");pe.hourElement=t.childNodes[0];var n=h("flatpickr-minute");if(pe.minuteElement=n.childNodes[0],pe.hourElement.tabIndex=pe.minuteElement.tabIndex=-1,pe.hourElement.value=pe.pad(pe.latestSelectedDateObj?pe.latestSelectedDateObj.getHours():pe.config.defaultHour),pe.minuteElement.value=pe.pad(pe.latestSelectedDateObj?pe.latestSelectedDateObj.getMinutes():pe.config.defaultMinute),pe.hourElement.step=pe.config.hourIncrement,pe.minuteElement.step=pe.config.minuteIncrement,pe.hourElement.min=pe.config.time_24hr?0:1,pe.hourElement.max=pe.config.time_24hr?23:12,pe.minuteElement.min=0,pe.minuteElement.max=59,pe.hourElement.title=pe.minuteElement.title=pe.l10n.scrollTitle,pe.timeContainer.appendChild(t),pe.timeContainer.appendChild(e),pe.timeContainer.appendChild(n),pe.config.time_24hr&&pe.timeContainer.classList.add("time24hr"),pe.config.enableSeconds){pe.timeContainer.classList.add("hasSeconds");var a=h("flatpickr-second");pe.secondElement=a.childNodes[0],pe.secondElement.value=pe.latestSelectedDateObj?pe.pad(pe.latestSelectedDateObj.getSeconds()):"00",pe.secondElement.step=pe.minuteElement.step,pe.secondElement.min=pe.minuteElement.min,pe.secondElement.max=pe.minuteElement.max,pe.timeContainer.appendChild(se("span","flatpickr-time-separator",":")),pe.timeContainer.appendChild(a)}return pe.config.time_24hr||(pe.amPM=se("span","flatpickr-am-pm",["AM","PM"][pe.hourElement.value>11|0]),pe.amPM.title=pe.l10n.toggleTitle,pe.amPM.tabIndex=-1,pe.timeContainer.appendChild(pe.amPM)),pe.timeContainer}function x(){pe.weekdayContainer||(pe.weekdayContainer=se("div","flatpickr-weekdays"));var e=pe.l10n.firstDayOfWeek,t=pe.l10n.weekdays.shorthand.slice();return e>0&&e<t.length&&(t=[].concat(t.splice(e,t.length),t.splice(0,e))),pe.weekdayContainer.innerHTML="\n\t\t<span class=flatpickr-weekday>\n\t\t\t"+t.join("</span><span class=flatpickr-weekday>")+"\n\t\t</span>\n\t\t",pe.weekdayContainer}function T(){return pe.calendarContainer.classList.add("hasWeeks"),pe.weekWrapper=se("div","flatpickr-weekwrapper"),pe.weekWrapper.appendChild(se("span","flatpickr-weekday",pe.l10n.weekAbbreviation)),pe.weekNumbers=se("div","flatpickr-weeks"),pe.weekWrapper.appendChild(pe.weekNumbers),pe.weekWrapper}function E(e,t,n){t=void 0===t||t;var a=t?e:e-pe.currentMonth,i=!pe.config.animate||!1===n;if(!(a<0&&pe._hidePrevMonthArrow||a>0&&pe._hideNextMonthArrow)){if(pe.currentMonth+=a,(pe.currentMonth<0||pe.currentMonth>11)&&(pe.currentYear+=pe.currentMonth>11?1:-1,pe.currentMonth=(pe.currentMonth+12)%12,ee("YearChange")),b(i?void 0:a),i)return ee("MonthChange"),ie();var r=pe.navigationCurrentMonth;if(a<0)for(;r.nextSibling&&/curr/.test(r.nextSibling.className);)pe.monthNav.removeChild(r.nextSibling);else if(a>0)for(;r.previousSibling&&/curr/.test(r.previousSibling.className);)pe.monthNav.removeChild(r.previousSibling);if(pe.oldCurMonth=pe.navigationCurrentMonth,pe.navigationCurrentMonth=pe.monthNav.insertBefore(pe.oldCurMonth.cloneNode(!0),a>0?pe.oldCurMonth.nextSibling:pe.oldCurMonth),a>0?(pe.daysContainer.firstChild.classList.add("slideLeft"),pe.daysContainer.lastChild.classList.add("slideLeftNew"),pe.oldCurMonth.classList.add("slideLeft"),pe.navigationCurrentMonth.classList.add("slideLeftNew")):a<0&&(pe.daysContainer.firstChild.classList.add("slideRightNew"),pe.daysContainer.lastChild.classList.add("slideRight"),pe.oldCurMonth.classList.add("slideRight"),pe.navigationCurrentMonth.classList.add("slideRightNew")),pe.currentMonthElement=pe.navigationCurrentMonth.firstChild,pe.currentYearElement=pe.navigationCurrentMonth.lastChild.childNodes[0],ie(),pe.oldCurMonth.firstChild.textContent=pe.utils.monthToStr(pe.currentMonth-a),void 0===pe._.daysAnimDuration){var o=window.getComputedStyle(pe.daysContainer.lastChild),l=o.getPropertyValue("animation-duration")||o.getPropertyValue("-webkit-animation-duration");pe._.daysAnimDuration=parseInt(/(\d+)s/.exec(l)[1])}}}function N(e){pe.input.value="",pe.altInput&&(pe.altInput.value=""),pe.mobileInput&&(pe.mobileInput.value=""),pe.selectedDates=[],pe.latestSelectedDateObj=null,pe.showTimeInput=!1,pe.redraw(),!1!==e&&ee("Change")}function F(){pe.isOpen=!1,pe.isMobile||(pe.calendarContainer.classList.remove("open"),pe._input.classList.remove("active")),ee("Close")}function S(e){e=e||pe;for(var t=pe._handlers.length;t--;){var n=pe._handlers[t];n.element.removeEventListener(n.event,n.handler)}e.mobileInput?(e.mobileInput.parentNode&&e.mobileInput.parentNode.removeChild(e.mobileInput),e.mobileInput=void 0):e.calendarContainer&&e.calendarContainer.parentNode&&e.calendarContainer.parentNode.removeChild(e.calendarContainer),e.altInput&&(e.input.type="text",e.altInput.parentNode&&e.altInput.parentNode.removeChild(e.altInput),e.altInput=void 0),e.input&&(e.input.type=e.input._type,e.input.classList.remove("flatpickr-input"),e.input.removeAttribute("readonly"),e.input.value=""),e.config=void 0,e.input._flatpickr=void 0}function _(e){return!(!pe.config.appendTo||!pe.config.appendTo.contains(e))||pe.calendarContainer.contains(e)}function Y(e){if(pe.isOpen&&!pe.config.inline){var t=_(e.target),n=e.target===pe.input||e.target===pe.altInput||pe.element.contains(e.target)||e.path&&e.path.indexOf&&(~e.path.indexOf(pe.input)||~e.path.indexOf(pe.altInput));("blur"===e.type?n&&e.relatedTarget&&!_(e.relatedTarget):!n&&!t)&&(e.preventDefault(),pe.close(),"range"===pe.config.mode&&1===pe.selectedDates.length&&(pe.clear(),pe.redraw()))}}function I(e){if(!(!e||pe.currentYearElement.min&&e<pe.currentYearElement.min||pe.currentYearElement.max&&e>pe.currentYearElement.max)){var t=parseInt(e,10),n=pe.currentYear!==t;pe.currentYear=t||pe.currentYear,pe.config.maxDate&&pe.currentYear===pe.config.maxDate.getFullYear()?pe.currentMonth=Math.min(pe.config.maxDate.getMonth(),pe.currentMonth):pe.config.minDate&&pe.currentYear===pe.config.minDate.getFullYear()&&(pe.currentMonth=Math.max(pe.config.minDate.getMonth(),pe.currentMonth)),n&&(pe.redraw(),ee("YearChange"))}}function O(e,t){if(pe.config.minDate&&ge(e,pe.config.minDate,void 0!==t?t:!pe.minDateHasTime)<0||pe.config.maxDate&&ge(e,pe.config.maxDate,void 0!==t?t:!pe.maxDateHasTime)>0)return!1;if(!pe.config.enable.length&&!pe.config.disable.length)return!0;for(var n,a=pe.parseDate(e,null,!0),i=pe.config.enable.length>0,r=i?pe.config.enable:pe.config.disable,o=0;o<r.length;o++){if((n=r[o])instanceof Function&&n(a))return i;if(n instanceof Date&&n.getTime()===a.getTime())return i;if("string"==typeof n&&pe.parseDate(n,null,!0).getTime()===a.getTime())return i;if("object"===(void 0===n?"undefined":_typeof(n))&&n.from&&n.to&&a>=n.from&&a<=n.to)return i}return!i}function A(e){var t=e.target===pe._input,n=_(e.target),r=pe.config.allowInput,o=pe.isOpen&&(!r||!t),l=pe.config.inline&&t&&!r;if("Enter"===e.key&&r&&t)return pe.setDate(pe._input.value,!0,e.target===pe.altInput?pe.config.altFormat:pe.config.dateFormat),e.target.blur();if(n||o||l){var c=pe.timeContainer&&pe.timeContainer.contains(e.target);switch(e.key){case"Enter":c?re():J(e);break;case"Escape":e.preventDefault(),pe.close();break;case"ArrowLeft":case"ArrowRight":if(e.preventDefault(),pe.daysContainer){var s="ArrowRight"===e.key?1:-1;e.ctrlKey?(E(s,!0),w(function(){C(e.target.$i,0)})):C(e.target.$i,s)}else pe.config.enableTime&&!c&&pe.hourElement.focus();break;case"ArrowUp":case"ArrowDown":e.preventDefault();var d="ArrowDown"===e.key?1:-1;pe.daysContainer?e.ctrlKey?(I(pe.currentYear-d),C(e.target.$i,0)):c||C(e.target.$i,7*d):pe.config.enableTime&&(c||pe.hourElement.focus(),a(e));break;case"Tab":e.target===pe.hourElement?(e.preventDefault(),pe.minuteElement.select()):e.target===pe.minuteElement&&pe.amPM&&(e.preventDefault(),pe.amPM.focus());break;case"a":e.target===pe.amPM&&(pe.amPM.textContent="AM",i(),re());break;case"p":e.target===pe.amPM&&(pe.amPM.textContent="PM",i(),re())}ee("KeyDown",e)}}function L(e){if(1===pe.selectedDates.length&&e.classList.contains("flatpickr-day")){for(var t=e.dateObj,n=pe.parseDate(pe.selectedDates[0],null,!0),a=Math.min(t.getTime(),pe.selectedDates[0].getTime()),i=Math.max(t.getTime(),pe.selectedDates[0].getTime()),r=!1,o=a;o<i;o+=pe.utils.duration.DAY)if(!O(new Date(o))){r=!0;break}for(var l=pe.days.childNodes[0].dateObj.getTime(),c=0;c<42;c++,l+=pe.utils.duration.DAY){(function(o,l){var c=o<pe.minRangeDate.getTime()||o>pe.maxRangeDate.getTime(),s=pe.days.childNodes[l];if(c)return pe.days.childNodes[l].classList.add("notAllowed"),["inRange","startRange","endRange"].forEach(function(e){s.classList.remove(e)}),"continue";if(r&&!c)return"continue";["startRange","inRange","endRange","notAllowed"].forEach(function(e){s.classList.remove(e)});var d=Math.max(pe.minRangeDate.getTime(),a),u=Math.min(pe.maxRangeDate.getTime(),i);e.classList.add(t<pe.selectedDates[0]?"startRange":"endRange"),n<t&&o===n.getTime()?s.classList.add("startRange"):n>t&&o===n.getTime()&&s.classList.add("endRange"),o>=d&&o<=u&&s.classList.add("inRange")})(l,c)}}}function P(){!pe.isOpen||pe.config.static||pe.config.inline||U()}function j(e){if(pe.isMobile)return e&&(e.preventDefault(),e.target.blur()),setTimeout(function(){pe.mobileInput.click()},0),void ee("Open");pe.isOpen||pe._input.disabled||pe.config.inline||(pe.isOpen=!0,pe.calendarContainer.classList.add("open"),U(),pe._input.classList.add("active"),ee("Open"))}function H(e){return function(t){var n=pe.config["_"+e+"Date"]=pe.parseDate(t),a=pe.config["_"+("min"===e?"max":"min")+"Date"],i=t&&n instanceof Date;i&&(pe[e+"DateHasTime"]=n.getHours()||n.getMinutes()||n.getSeconds()),pe.selectedDates&&(pe.selectedDates=pe.selectedDates.filter(function(e){return O(e)}),pe.selectedDates.length||"min"!==e||r(n),re()),pe.daysContainer&&(B(),i?pe.currentYearElement[e]=n.getFullYear():pe.currentYearElement.removeAttribute(e),pe.currentYearElement.disabled=a&&n&&a.getFullYear()===n.getFullYear())}}function R(){var e=["utc","wrap","weekNumbers","allowInput","clickOpens","time_24hr","enableTime","noCalendar","altInput","shorthandCurrentMonth","inline","static","enableSeconds","disableMobile"],t=["onChange","onClose","onDayCreate","onKeyDown","onMonthChange","onOpen","onParseConfig","onReady","onValueUpdate","onYearChange"];pe.config=Object.create(Flatpickr.defaultConfig);var a=_extends({},pe.instanceConfig,JSON.parse(JSON.stringify(pe.element.dataset||{})));pe.config.parseDate=a.parseDate,pe.config.formatDate=a.formatDate,_extends(pe.config,a),!a.dateFormat&&a.enableTime&&(pe.config.dateFormat=pe.config.noCalendar?"H:i"+(pe.config.enableSeconds?":S":""):Flatpickr.defaultConfig.dateFormat+" H:i"+(pe.config.enableSeconds?":S":"")),a.altInput&&a.enableTime&&!a.altFormat&&(pe.config.altFormat=pe.config.noCalendar?"h:i"+(pe.config.enableSeconds?":S K":" K"):Flatpickr.defaultConfig.altFormat+" h:i"+(pe.config.enableSeconds?":S":"")+" K"),Object.defineProperty(pe.config,"minDate",{get:function(){return this._minDate},set:H("min")}),Object.defineProperty(pe.config,"maxDate",{get:function(){return this._maxDate},set:H("max")}),pe.config.minDate=a.minDate,pe.config.maxDate=a.maxDate;for(var i=0;i<e.length;i++)pe.config[e[i]]=!0===pe.config[e[i]]||"true"===pe.config[e[i]];for(var r=0;r<t.length;r++)pe.config[t[r]]=de(pe.config[t[r]]||[]).map(n);for(var o=0;o<pe.config.plugins.length;o++){var l=pe.config.plugins[o](pe)||{};for(var c in l)(pe.config[c]||~t.indexOf(c))instanceof Array?pe.config[c]=de(l[c]).map(n).concat(pe.config[c]):void 0===a[c]&&(pe.config[c]=l[c])}ee("ParseConfig")}function W(){"object"!==_typeof(pe.config.locale)&&void 0===Flatpickr.l10ns[pe.config.locale]&&console.warn("flatpickr: invalid locale "+pe.config.locale),pe.l10n=_extends(Object.create(Flatpickr.l10ns.default),"object"===_typeof(pe.config.locale)?pe.config.locale:"default"!==pe.config.locale?Flatpickr.l10ns[pe.config.locale]||{}:{})}function U(){if(void 0!==pe.calendarContainer){var e=pe.calendarContainer.offsetHeight,t=pe.calendarContainer.offsetWidth,n=pe.config.position,a=pe._input,i=a.getBoundingClientRect(),r=window.innerHeight-i.bottom+a.offsetHeight,o="above"===n||"below"!==n&&r<e&&i.top>e,l=window.pageYOffset+i.top+(o?-e-2:a.offsetHeight+2);if(ue(pe.calendarContainer,"arrowTop",!o),ue(pe.calendarContainer,"arrowBottom",o),!pe.config.inline){var c=window.pageXOffset+i.left,s=window.document.body.offsetWidth-i.right,d=c+t>window.document.body.offsetWidth;ue(pe.calendarContainer,"rightMost",d),pe.config.static||(pe.calendarContainer.style.top=l+"px",d?(pe.calendarContainer.style.left="auto",pe.calendarContainer.style.right=s+"px"):(pe.calendarContainer.style.left=c+"px",pe.calendarContainer.style.right="auto"))}}}function B(){pe.config.noCalendar||pe.isMobile||(x(),ie(),b())}function J(e){if(e.preventDefault(),e.stopPropagation(),e.target.classList.contains("flatpickr-day")&&!e.target.classList.contains("disabled")&&!e.target.classList.contains("notAllowed")){var t=pe.latestSelectedDateObj=new Date(e.target.dateObj.getTime()),n=t.getMonth()!==pe.currentMonth&&"range"!==pe.config.mode;if(pe.selectedDateElem=e.target,"single"===pe.config.mode)pe.selectedDates=[t];else if("multiple"===pe.config.mode){var a=ne(t);a?pe.selectedDates.splice(a,1):pe.selectedDates.push(t)}else"range"===pe.config.mode&&(2===pe.selectedDates.length&&pe.clear(),pe.selectedDates.push(t),0!==ge(t,pe.selectedDates[0],!0)&&pe.selectedDates.sort(function(e,t){return e.getTime()-t.getTime()}));if(i(),n){var o=pe.currentYear!==t.getFullYear();pe.currentYear=t.getFullYear(),pe.currentMonth=t.getMonth(),o&&ee("YearChange"),ee("MonthChange")}b(),pe.minDateHasTime&&pe.config.enableTime&&0===ge(t,pe.config.minDate)&&r(pe.config.minDate),re(),pe.config.enableTime&&setTimeout(function(){return pe.showTimeInput=!0},50),"range"===pe.config.mode&&(1===pe.selectedDates.length?(L(e.target),pe._hidePrevMonthArrow=pe._hidePrevMonthArrow||pe.minRangeDate>pe.days.childNodes[0].dateObj,pe._hideNextMonthArrow=pe._hideNextMonthArrow||pe.maxRangeDate<new Date(pe.currentYear,pe.currentMonth+1,1)):(ie(),pe.close())),ee("Change"),n?w(function(){return pe.selectedDateElem.focus()}):C(e.target.$i,0),pe.config.enableTime&&setTimeout(function(){return pe.hourElement.select()},451),"single"!==pe.config.mode||pe.config.enableTime||pe.close()}}function K(e,t){pe.config[e]=t,pe.redraw(),g()}function $(e,t){if(e instanceof Array)pe.selectedDates=e.map(function(e){return pe.parseDate(e,t)});else if(e instanceof Date||!isNaN(e))pe.selectedDates=[pe.parseDate(e,t)];else if(e&&e.substring)switch(pe.config.mode){case"single":pe.selectedDates=[pe.parseDate(e,t)];break;case"multiple":pe.selectedDates=e.split("; ").map(function(e){return pe.parseDate(e,t)});break;case"range":pe.selectedDates=e.split(pe.l10n.rangeSeparator).map(function(e){return pe.parseDate(e,t)})}pe.selectedDates=pe.selectedDates.filter(function(e){return e instanceof Date&&O(e,!1)}),pe.selectedDates.sort(function(e,t){return e.getTime()-t.getTime()})}function z(e,t,n){if(!e)return pe.clear(t);$(e,n),pe.showTimeInput=pe.selectedDates.length>0,pe.latestSelectedDateObj=pe.selectedDates[0],pe.redraw(),g(),r(),re(),t&&ee("Change")}function V(){function e(e){for(var t=e.length;t--;)"string"==typeof e[t]||+e[t]?e[t]=pe.parseDate(e[t],null,!0):e[t]&&e[t].from&&e[t].to&&(e[t].from=pe.parseDate(e[t].from),e[t].to=pe.parseDate(e[t].to));return e.filter(function(e){return e})}pe.selectedDates=[],pe.now=new Date,pe.config.disable.length&&(pe.config.disable=e(pe.config.disable)),pe.config.enable.length&&(pe.config.enable=e(pe.config.enable));var t=pe.config.defaultDate||pe.input.value;t&&$(t,pe.config.dateFormat);var n=pe.selectedDates.length?pe.selectedDates[0]:pe.config.minDate&&pe.config.minDate.getTime()>pe.now?pe.config.minDate:pe.config.maxDate&&pe.config.maxDate.getTime()<pe.now?pe.config.maxDate:pe.now;pe.currentYear=n.getFullYear(),pe.currentMonth=n.getMonth(),pe.selectedDates.length&&(pe.latestSelectedDateObj=pe.selectedDates[0]),pe.minDateHasTime=pe.config.minDate&&(pe.config.minDate.getHours()||pe.config.minDate.getMinutes()||pe.config.minDate.getSeconds()),pe.maxDateHasTime=pe.config.maxDate&&(pe.config.maxDate.getHours()||pe.config.maxDate.getMinutes()||pe.config.maxDate.getSeconds()),Object.defineProperty(pe,"latestSelectedDateObj",{get:function(){return pe._selectedDateObj||pe.selectedDates[pe.selectedDates.length-1]||null},set:function(e){pe._selectedDateObj=e}}),pe.isMobile||Object.defineProperty(pe,"showTimeInput",{get:function(){return pe._showTimeInput},set:function(e){pe._showTimeInput=e,pe.calendarContainer&&ue(pe.calendarContainer,"showTimeInput",e),U()}})}function Z(){pe.utils={duration:{DAY:864e5},getDaysinMonth:function(e,t){return e=void 0===e?pe.currentMonth:e,t=void 0===t?pe.currentYear:t,1===e&&(t%4==0&&t%100!=0||t%400==0)?29:pe.l10n.daysInMonth[e]},monthToStr:function(e,t){return t=void 0===t?pe.config.shorthandCurrentMonth:t,pe.l10n.months[(t?"short":"long")+"hand"][e]}}}function q(){["D","F","J","M","W","l"].forEach(function(e){pe.formats[e]=Flatpickr.prototype.formats[e].bind(pe)}),pe.revFormat.F=Flatpickr.prototype.revFormat.F.bind(pe),pe.revFormat.M=Flatpickr.prototype.revFormat.M.bind(pe)}function Q(){if(pe.input=pe.config.wrap?pe.element.querySelector("[data-input]"):pe.element,!pe.input)return console.warn("Error: invalid input element specified",pe.input);pe.input._type=pe.input.type,pe.input.type="text",pe.input.classList.add("flatpickr-input"),pe._input=pe.input,pe.config.altInput&&(pe.altInput=se(pe.input.nodeName,pe.input.className+" "+pe.config.altInputClass),pe._input=pe.altInput,pe.altInput.placeholder=pe.input.placeholder,pe.altInput.type="text",pe.input.type="hidden",!pe.config.static&&pe.input.parentNode&&pe.input.parentNode.insertBefore(pe.altInput,pe.input.nextSibling)),pe.config.allowInput||pe._input.setAttribute("readonly","readonly")}function G(){var e=pe.config.enableTime?pe.config.noCalendar?"time":"datetime-local":"date";pe.mobileInput=se("input",pe.input.className+" flatpickr-mobile"),pe.mobileInput.step="any",pe.mobileInput.tabIndex=1,pe.mobileInput.type=e,pe.mobileInput.disabled=pe.input.disabled,pe.mobileInput.placeholder=pe.input.placeholder,pe.mobileFormatStr="datetime-local"===e?"Y-m-d\\TH:i:S":"date"===e?"Y-m-d":"H:i:S",pe.selectedDates.length&&(pe.mobileInput.defaultValue=pe.mobileInput.value=pe.formatDate(pe.selectedDates[0],pe.mobileFormatStr)),pe.config.minDate&&(pe.mobileInput.min=pe.formatDate(pe.config.minDate,"Y-m-d")),pe.config.maxDate&&(pe.mobileInput.max=pe.formatDate(pe.config.maxDate,"Y-m-d")),pe.input.type="hidden",pe.config.altInput&&(pe.altInput.type="hidden");try{pe.input.parentNode.insertBefore(pe.mobileInput,pe.input.nextSibling)}catch(e){}pe.mobileInput.addEventListener("change",function(e){pe.setDate(e.target.value,!1,pe.mobileFormatStr),ee("Change"),ee("Close")})}function X(){if(pe.isOpen)return pe.close();pe.open()}function ee(e,t){var n=pe.config["on"+e];if(n)for(var a=0;n[a]&&a<n.length;a++)n[a](pe.selectedDates,pe.input&&pe.input.value,pe,t);"Change"===e&&(pe.input.dispatchEvent(te("change")),pe.input.dispatchEvent(te("input")))}function te(e){var t=pe._[e+"Event"];return void 0!==t?t:pe._supportsEvents?pe._[e+"Event"]=new Event(e,{bubbles:!0}):(pe._[e+"Event"]=document.createEvent("Event"),pe._[e+"Event"].initEvent(e,!0,!0),pe._[e+"Event"])}function ne(e){for(var t=0;t<pe.selectedDates.length;t++)if(0===ge(pe.selectedDates[t],e))return""+t;return!1}function ae(e){return!("range"!==pe.config.mode||pe.selectedDates.length<2)&&(ge(e,pe.selectedDates[0])>=0&&ge(e,pe.selectedDates[1])<=0)}function ie(){pe.config.noCalendar||pe.isMobile||!pe.monthNav||(pe.currentMonthElement.textContent=pe.utils.monthToStr(pe.currentMonth)+" ",pe.currentYearElement.value=pe.currentYear,pe._hidePrevMonthArrow=pe.config.minDate&&(pe.currentYear===pe.config.minDate.getFullYear()?pe.currentMonth<=pe.config.minDate.getMonth():pe.currentYear<pe.config.minDate.getFullYear()),pe._hideNextMonthArrow=pe.config.maxDate&&(pe.currentYear===pe.config.maxDate.getFullYear()?pe.currentMonth+1>pe.config.maxDate.getMonth():pe.currentYear>pe.config.maxDate.getFullYear()))}function re(){if(!pe.selectedDates.length)return pe.clear();pe.isMobile&&(pe.mobileInput.value=pe.selectedDates.length?pe.formatDate(pe.latestSelectedDateObj,pe.mobileFormatStr):"");var e="range"!==pe.config.mode?"; ":pe.l10n.rangeSeparator;pe.input.value=pe.selectedDates.map(function(e){return pe.formatDate(e,pe.config.dateFormat)}).join(e),pe.config.altInput&&(pe.altInput.value=pe.selectedDates.map(function(e){return pe.formatDate(e,pe.config.altFormat)}).join(e)),ee("ValueUpdate")}function oe(e){return Math.max(-1,Math.min(1,e.wheelDelta||-e.deltaY))}function le(e){e.preventDefault()
;var t=pe.currentYearElement.parentNode.contains(e.target);if(e.target===pe.currentMonthElement||t){var n=oe(e);t?(I(pe.currentYear+n),e.target.value=pe.currentYear):pe.changeMonth(n,!0,!1)}}function ce(e){"arrowUp"===e.target.className?pe.changeYear(pe.currentYear+1):"arrowDown"===e.target.className&&pe.changeYear(pe.currentYear-1)}function se(e,t,n){var a=window.document.createElement(e);return t=t||"",n=n||"",a.className=t,void 0!==n&&(a.textContent=n),a}function de(e){return e instanceof Array?e:[e]}function ue(e,t,n){if(n)return e.classList.add(t);e.classList.remove(t)}function fe(e,t,n){var a=void 0;return function(){var i=this,r=arguments;clearTimeout(a),a=setTimeout(function(){a=null,n||e.apply(i,r)},t),n&&!a&&e.apply(i,r)}}function ge(e,t,n){return e instanceof Date&&t instanceof Date&&(!1!==n?new Date(e.getTime()).setHours(0,0,0,0)-new Date(t.getTime()).setHours(0,0,0,0):e.getTime()-t.getTime())}function me(e){e.preventDefault();var t="keydown"===e.type,n=(e.type,e.type,e.target);if(pe.amPM&&e.target===pe.amPM)return e.target.textContent=["AM","PM"]["AM"===e.target.textContent|0];var a=Number(n.min),i=Number(n.max),r=Number(n.step),o=parseInt(n.value,10),l=e.delta||(t?38===e.which?1:-1:Math.max(-1,Math.min(1,e.wheelDelta||-e.deltaY))||0),c=o+r*l;if(void 0!==n.value&&2===n.value.length){var s=n===pe.hourElement,d=n===pe.minuteElement;c<a?(c=i+c+!s+(s&&!pe.amPM),d&&p(null,-1,pe.hourElement)):c>i&&(c=n===pe.hourElement?c-i-!pe.amPM:a,d&&p(null,1,pe.hourElement)),pe.amPM&&s&&(1===r?c+o===23:Math.abs(c-o)>r)&&(pe.amPM.textContent="PM"===pe.amPM.textContent?"AM":"PM"),n.value=pe.pad(c)}}var pe=this;return pe._={},pe._.afterDayAnim=w,pe.changeMonth=E,pe.changeYear=I,pe.clear=N,pe.close=F,pe._createElement=se,pe.destroy=S,pe.isEnabled=O,pe.jumpToDate=g,pe.open=j,pe.redraw=B,pe.set=K,pe.setDate=z,pe.toggle=X,function(){e._flatpickr&&(e._flatpickr=void 0),e._flatpickr=pe,pe.element=e,pe.instanceConfig=t||{},pe.parseDate=Flatpickr.prototype.parseDate.bind(pe),pe.formatDate=Flatpickr.prototype.formatDate.bind(pe),q(),R(),W(),Q(),V(),Z(),pe.isOpen=!1,pe.isMobile=!pe.config.disableMobile&&!pe.config.inline&&"single"===pe.config.mode&&!pe.config.disable.length&&!pe.config.enable.length&&!pe.config.weekNumbers&&/Android|webOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini/i.test(navigator.userAgent),pe.isMobile||D(),d(),(pe.selectedDates.length||pe.config.noCalendar)&&(pe.config.enableTime&&r(pe.config.noCalendar?pe.latestSelectedDateObj||pe.config.minDate:null),re()),pe.config.weekNumbers&&(pe.calendarContainer.style.width=pe.daysContainer.clientWidth+pe.weekWrapper.clientWidth+"px"),pe.showTimeInput=pe.selectedDates.length>0||pe.config.noCalendar,pe.isMobile||U(),ee("Ready")}(),pe}function _flatpickr(e,t){for(var n=Array.prototype.slice.call(e),a=[],i=0;i<n.length;i++)try{n[i]._flatpickr=new Flatpickr(n[i],t||{}),a.push(n[i]._flatpickr)}catch(e){console.warn(e,e.stack)}return 1===a.length?a[0]:a}function flatpickr(e,t){return _flatpickr(window.document.querySelectorAll(e),t)}var _extends=Object.assign||function(e){for(var t=1;t<arguments.length;t++){var n=arguments[t];for(var a in n)Object.prototype.hasOwnProperty.call(n,a)&&(e[a]=n[a])}return e},_typeof="function"==typeof Symbol&&"symbol"==typeof Symbol.iterator?function(e){return typeof e}:function(e){return e&&"function"==typeof Symbol&&e.constructor===Symbol&&e!==Symbol.prototype?"symbol":typeof e};Flatpickr.defaultConfig={mode:"single",position:"auto",animate:-1===window.navigator.userAgent.indexOf("MSIE"),utc:!1,wrap:!1,weekNumbers:!1,allowInput:!1,clickOpens:!0,time_24hr:!1,enableTime:!1,noCalendar:!1,dateFormat:"Y-m-d",altInput:!1,altInputClass:"flatpickr-input form-control input",altFormat:"F j, Y",defaultDate:null,minDate:null,maxDate:null,parseDate:null,formatDate:null,getWeek:function(e){var t=new Date(e.getTime());t.setHours(0,0,0,0),t.setDate(t.getDate()+3-(t.getDay()+6)%7);var n=new Date(t.getFullYear(),0,4);return 1+Math.round(((t.getTime()-n.getTime())/864e5-3+(n.getDay()+6)%7)/7)},enable:[],disable:[],shorthandCurrentMonth:!1,inline:!1,static:!1,appendTo:null,prevArrow:"<svg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 17 17'><g></g><path d='M5.207 8.471l7.146 7.147-0.707 0.707-7.853-7.854 7.854-7.853 0.707 0.707-7.147 7.146z' /></svg>",nextArrow:"<svg version='1.1' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink' viewBox='0 0 17 17'><g></g><path d='M13.207 8.472l-7.854 7.854-0.707-0.707 7.146-7.146-7.146-7.148 0.707-0.707 7.854 7.854z' /></svg>",enableSeconds:!1,hourIncrement:1,minuteIncrement:5,defaultHour:12,defaultMinute:0,disableMobile:!1,locale:"default",plugins:[],onClose:[],onChange:[],onDayCreate:[],onMonthChange:[],onOpen:[],onParseConfig:[],onReady:[],onValueUpdate:[],onYearChange:[],onKeyDown:[]},Flatpickr.l10ns={en:{weekdays:{shorthand:["Sun","Mon","Tue","Wed","Thu","Fri","Sat"],longhand:["Sunday","Monday","Tuesday","Wednesday","Thursday","Friday","Saturday"]},months:{shorthand:["Jan","Feb","Mar","Apr","May","Jun","Jul","Aug","Sep","Oct","Nov","Dec"],longhand:["January","February","March","April","May","June","July","August","September","October","November","December"]},daysInMonth:[31,28,31,30,31,30,31,31,30,31,30,31],firstDayOfWeek:0,ordinal:function(e){var t=e%100;if(t>3&&t<21)return"th";switch(t%10){case 1:return"st";case 2:return"nd";case 3:return"rd";default:return"th"}},rangeSeparator:" to ",weekAbbreviation:"Wk",scrollTitle:"Scroll to increment",toggleTitle:"Click to toggle"}},Flatpickr.l10ns.default=Object.create(Flatpickr.l10ns.en),Flatpickr.localize=function(e){return _extends(Flatpickr.l10ns.default,e||{})},Flatpickr.setDefaults=function(e){return _extends(Flatpickr.defaultConfig,e||{})},Flatpickr.prototype={formats:{Z:function(e){return e.toISOString()},D:function(e){return this.l10n.weekdays.shorthand[this.formats.w(e)]},F:function(e){return this.utils.monthToStr(this.formats.n(e)-1,!1)},H:function(e){return Flatpickr.prototype.pad(e.getHours())},J:function(e){return e.getDate()+this.l10n.ordinal(e.getDate())},K:function(e){return e.getHours()>11?"PM":"AM"},M:function(e){return this.utils.monthToStr(e.getMonth(),!0)},S:function(e){return Flatpickr.prototype.pad(e.getSeconds())},U:function(e){return e.getTime()/1e3},W:function(e){return this.config.getWeek(e)},Y:function(e){return e.getFullYear()},d:function(e){return Flatpickr.prototype.pad(e.getDate())},h:function(e){return e.getHours()%12?e.getHours()%12:12},i:function(e){return Flatpickr.prototype.pad(e.getMinutes())},j:function(e){return e.getDate()},l:function(e){return this.l10n.weekdays.longhand[e.getDay()]},m:function(e){return Flatpickr.prototype.pad(e.getMonth()+1)},n:function(e){return e.getMonth()+1},s:function(e){return e.getSeconds()},w:function(e){return e.getDay()},y:function(e){return String(e.getFullYear()).substring(2)}},formatDate:function(e,t){var n=this;return void 0!==this.config&&void 0!==this.config.formatDate?this.config.formatDate(e,t):t.split("").map(function(t,a,i){return n.formats[t]&&"\\"!==i[a-1]?n.formats[t](e):"\\"!==t?t:""}).join("")},revFormat:{D:function(){},F:function(e,t){e.setMonth(this.l10n.months.longhand.indexOf(t))},H:function(e,t){e.setHours(parseFloat(t))},J:function(e,t){e.setDate(parseFloat(t))},K:function(e,t){var n=e.getHours();12!==n&&e.setHours(n%12+12*/pm/i.test(t))},M:function(e,t){e.setMonth(this.l10n.months.shorthand.indexOf(t))},S:function(e,t){e.setSeconds(t)},U:function(e,t){return new Date(1e3*parseFloat(t))},W:function(e,t){return t=parseInt(t),new Date(e.getFullYear(),0,2+7*(t-1),0,0,0,0,0)},Y:function(e,t){e.setFullYear(t)},Z:function(e,t){return new Date(t)},d:function(e,t){e.setDate(parseFloat(t))},h:function(e,t){e.setHours(parseFloat(t))},i:function(e,t){e.setMinutes(parseFloat(t))},j:function(e,t){e.setDate(parseFloat(t))},l:function(){},m:function(e,t){e.setMonth(parseFloat(t)-1)},n:function(e,t){e.setMonth(parseFloat(t)-1)},s:function(e,t){e.setSeconds(parseFloat(t))},w:function(){},y:function(e,t){e.setFullYear(2e3+parseFloat(t))}},tokenRegex:{D:"(\\w+)",F:"(\\w+)",H:"(\\d\\d|\\d)",J:"(\\d\\d|\\d)\\w+",K:"(\\w+)",M:"(\\w+)",S:"(\\d\\d|\\d)",U:"(.+)",W:"(\\d\\d|\\d)",Y:"(\\d{4})",Z:"(.+)",d:"(\\d\\d|\\d)",h:"(\\d\\d|\\d)",i:"(\\d\\d|\\d)",j:"(\\d\\d|\\d)",l:"(\\w+)",m:"(\\d\\d|\\d)",n:"(\\d\\d|\\d)",s:"(\\d\\d|\\d)",w:"(\\d\\d|\\d)",y:"(\\d{2})"},pad:function(e){return("0"+e).slice(-2)},parseDate:function(e,t,n){if(!e)return null;var a=e;if(e instanceof Date)e=new Date(e.getTime());else if(void 0!==e.toFixed)e=new Date(e);else{var i=t||this.config.dateFormat;if("today"===(e=String(e).trim()))e=new Date,n=!0;else if(/Z$/.test(e)||/GMT$/.test(e))e=new Date(e);else if(this.config&&this.config.parseDate)e=this.config.parseDate(e,i);else{for(var r=this.config&&this.config.noCalendar?new Date((new Date).setHours(0,0,0,0)):new Date((new Date).getFullYear(),0,1,0,0,0,0),o=void 0,l=0,c=0,s="";l<i.length;l++){var d=i[l],u="\\"===d,f="\\"===i[l-1]||u;if(this.tokenRegex[d]&&!f){s+=this.tokenRegex[d];var g=new RegExp(s).exec(e);g&&(o=!0)&&(r=this.revFormat[d](r,g[++c])||r)}else u||(s+=".")}e=o?r:null}}return e instanceof Date?(this.config&&this.config.utc&&!e.fp_isUTC&&(e=e.fp_toUTC()),!0===n&&e.setHours(0,0,0,0),e):(console.warn("flatpickr: invalid date "+a),console.info(this.element),null)}},"undefined"!=typeof HTMLElement&&(HTMLCollection.prototype.flatpickr=NodeList.prototype.flatpickr=function(e){return _flatpickr(this,e)},HTMLElement.prototype.flatpickr=function(e){return _flatpickr([this],e)}),"undefined"!=typeof jQuery&&(jQuery.fn.flatpickr=function(e){return _flatpickr(this,e)}),Date.prototype.fp_incr=function(e){return new Date(this.getFullYear(),this.getMonth(),this.getDate()+parseInt(e,10))},Date.prototype.fp_isUTC=!1,Date.prototype.fp_toUTC=function(){var e=new Date(this.getUTCFullYear(),this.getUTCMonth(),this.getUTCDate(),this.getUTCHours(),this.getUTCMinutes(),this.getUTCSeconds());return e.fp_isUTC=!0,e},"undefined"!=typeof module&&(module.exports=Flatpickr);

<?php if($form['themeFont']){ ?>
/*
 * Copyright 2016 Small Batch, Inc.
 *
 * Licensed under the Apache License, Version 2.0 (the "License"); you may not
 * use this file except in compliance with the License. You may obtain a copy of
 * the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS, WITHOUT
 * WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied. See the
 * License for the specific language governing permissions and limitations under
 * the License.
 */
/* Web Font Loader v1.6.16 - (c) Adobe Systems, Google. License: Apache 2.0 */
(function(){function aa(a,b,c){return a.call.apply(a.bind,arguments)}function ba(a,b,c){if(!a)throw Error();if(2<arguments.length){var d=Array.prototype.slice.call(arguments,2);return function(){var c=Array.prototype.slice.call(arguments);Array.prototype.unshift.apply(c,d);return a.apply(b,c)}}return function(){return a.apply(b,arguments)}}function n(a,b,c){n=Function.prototype.bind&&-1!=Function.prototype.bind.toString().indexOf("native code")?aa:ba;return n.apply(null,arguments)}var p=Date.now||function(){return+new Date};function r(a,b){this.F=a;this.k=b||a;this.H=this.k.document}var ca=!!window.FontFace;r.prototype.createElement=function(a,b,c){a=this.H.createElement(a);if(b)for(var d in b)b.hasOwnProperty(d)&&("style"==d?a.style.cssText=b[d]:a.setAttribute(d,b[d]));c&&a.appendChild(this.H.createTextNode(c));return a};function s(a,b,c){a=a.H.getElementsByTagName(b)[0];a||(a=document.documentElement);a.insertBefore(c,a.lastChild)}
function t(a,b,c){b=b||[];c=c||[];for(var d=a.className.split(/\s+/),e=0;e<b.length;e+=1){for(var f=!1,g=0;g<d.length;g+=1)if(b[e]===d[g]){f=!0;break}f||d.push(b[e])}b=[];for(e=0;e<d.length;e+=1){f=!1;for(g=0;g<c.length;g+=1)if(d[e]===c[g]){f=!0;break}f||b.push(d[e])}a.className=b.join(" ").replace(/\s+/g," ").replace(/^\s+|\s+$/,"")}function u(a,b){for(var c=a.className.split(/\s+/),d=0,e=c.length;d<e;d++)if(c[d]==b)return!0;return!1}
function v(a){if("string"===typeof a.fa)return a.fa;var b=a.k.location.protocol;"about:"==b&&(b=a.F.location.protocol);return"https:"==b?"https:":"http:"}function x(a,b,c){function d(){l&&e&&f&&(l(g),l=null)}b=a.createElement("link",{rel:"stylesheet",href:b,media:"all"});var e=!1,f=!0,g=null,l=c||null;ca?(b.onload=function(){e=!0;d()},b.onerror=function(){e=!0;g=Error("Stylesheet failed to load");d()}):setTimeout(function(){e=!0;d()},0);s(a,"head",b)}
function y(a,b,c,d){var e=a.H.getElementsByTagName("head")[0];if(e){var f=a.createElement("script",{src:b}),g=!1;f.onload=f.onreadystatechange=function(){g||this.readyState&&"loaded"!=this.readyState&&"complete"!=this.readyState||(g=!0,c&&c(null),f.onload=f.onreadystatechange=null,"HEAD"==f.parentNode.tagName&&e.removeChild(f))};e.appendChild(f);setTimeout(function(){g||(g=!0,c&&c(Error("Script load timeout")))},d||5E3);return f}return null};function z(){this.S=0;this.K=null}function A(a){a.S++;return function(){a.S--;B(a)}}function C(a,b){a.K=b;B(a)}function B(a){0==a.S&&a.K&&(a.K(),a.K=null)};function D(a){this.ea=a||"-"}D.prototype.d=function(a){for(var b=[],c=0;c<arguments.length;c++)b.push(arguments[c].replace(/[\W_]+/g,"").toLowerCase());return b.join(this.ea)};function E(a,b){this.Q=a;this.M=4;this.L="n";var c=(b||"n4").match(/^([nio])([1-9])$/i);c&&(this.L=c[1],this.M=parseInt(c[2],10))}E.prototype.getName=function(){return this.Q};function da(a){return F(a)+" "+(a.M+"00")+" 300px "+G(a.Q)}function G(a){var b=[];a=a.split(/,\s*/);for(var c=0;c<a.length;c++){var d=a[c].replace(/['"]/g,"");-1!=d.indexOf(" ")||/^\d/.test(d)?b.push("'"+d+"'"):b.push(d)}return b.join(",")}function I(a){return a.L+a.M}
function F(a){var b="normal";"o"===a.L?b="oblique":"i"===a.L&&(b="italic");return b}function ea(a){var b=4,c="n",d=null;a&&((d=a.match(/(normal|oblique|italic)/i))&&d[1]&&(c=d[1].substr(0,1).toLowerCase()),(d=a.match(/([1-9]00|normal|bold)/i))&&d[1]&&(/bold/i.test(d[1])?b=7:/[1-9]00/.test(d[1])&&(b=parseInt(d[1].substr(0,1),10))));return c+b};function fa(a,b){this.a=a;this.j=a.k.document.documentElement;this.O=b;this.f="wf";this.e=new D("-");this.da=!1!==b.events;this.u=!1!==b.classes}function ga(a){a.u&&t(a.j,[a.e.d(a.f,"loading")]);J(a,"loading")}function K(a){if(a.u){var b=u(a.j,a.e.d(a.f,"active")),c=[],d=[a.e.d(a.f,"loading")];b||c.push(a.e.d(a.f,"inactive"));t(a.j,c,d)}J(a,"inactive")}function J(a,b,c){if(a.da&&a.O[b])if(c)a.O[b](c.getName(),I(c));else a.O[b]()};function ha(){this.t={}}function ia(a,b,c){var d=[],e;for(e in b)if(b.hasOwnProperty(e)){var f=a.t[e];f&&d.push(f(b[e],c))}return d};function L(a,b){this.a=a;this.h=b;this.m=this.a.createElement("span",{"aria-hidden":"true"},this.h)}function M(a,b){var c=a.m,d;d="display:block;position:absolute;top:-9999px;left:-9999px;font-size:300px;width:auto;height:auto;line-height:normal;margin:0;padding:0;font-variant:normal;white-space:nowrap;font-family:"+G(b.Q)+";"+("font-style:"+F(b)+";font-weight:"+(b.M+"00")+";");c.style.cssText=d}function N(a){s(a.a,"body",a.m)}L.prototype.remove=function(){var a=this.m;a.parentNode&&a.parentNode.removeChild(a)};function O(a,b,c,d,e,f){this.G=a;this.J=b;this.g=d;this.a=c;this.v=e||3E3;this.h=f||void 0}O.prototype.start=function(){function a(){p()-d>=c.v?c.J(c.g):b.fonts.load(da(c.g),c.h).then(function(b){1<=b.length?c.G(c.g):setTimeout(a,25)},function(){c.J(c.g)})}var b=this.a.k.document,c=this,d=p();a()};function P(a,b,c,d,e,f,g){this.G=a;this.J=b;this.a=c;this.g=d;this.h=g||"BESbswy";this.s={};this.v=e||3E3;this.Z=f||null;this.D=this.C=this.A=this.w=null;this.w=new L(this.a,this.h);this.A=new L(this.a,this.h);this.C=new L(this.a,this.h);this.D=new L(this.a,this.h);M(this.w,new E(this.g.getName()+",serif",I(this.g)));M(this.A,new E(this.g.getName()+",sans-serif",I(this.g)));M(this.C,new E("serif",I(this.g)));M(this.D,new E("sans-serif",I(this.g)));N(this.w);N(this.A);N(this.C);N(this.D)}
var Q={ia:"serif",ha:"sans-serif"},R=null;function S(){if(null===R){var a=/AppleWebKit\/([0-9]+)(?:\.([0-9]+))/.exec(window.navigator.userAgent);R=!!a&&(536>parseInt(a[1],10)||536===parseInt(a[1],10)&&11>=parseInt(a[2],10))}return R}P.prototype.start=function(){this.s.serif=this.C.m.offsetWidth;this.s["sans-serif"]=this.D.m.offsetWidth;this.ga=p();ja(this)};function ka(a,b,c){for(var d in Q)if(Q.hasOwnProperty(d)&&b===a.s[Q[d]]&&c===a.s[Q[d]])return!0;return!1}
function ja(a){var b=a.w.m.offsetWidth,c=a.A.m.offsetWidth,d;(d=b===a.s.serif&&c===a.s["sans-serif"])||(d=S()&&ka(a,b,c));d?p()-a.ga>=a.v?S()&&ka(a,b,c)&&(null===a.Z||a.Z.hasOwnProperty(a.g.getName()))?T(a,a.G):T(a,a.J):la(a):T(a,a.G)}function la(a){setTimeout(n(function(){ja(this)},a),50)}function T(a,b){setTimeout(n(function(){this.w.remove();this.A.remove();this.C.remove();this.D.remove();b(this.g)},a),0)};function U(a,b,c){this.a=a;this.p=b;this.P=0;this.ba=this.Y=!1;this.v=c}var V=null;U.prototype.V=function(a){var b=this.p;b.u&&t(b.j,[b.e.d(b.f,a.getName(),I(a).toString(),"active")],[b.e.d(b.f,a.getName(),I(a).toString(),"loading"),b.e.d(b.f,a.getName(),I(a).toString(),"inactive")]);J(b,"fontactive",a);this.ba=!0;ma(this)};
U.prototype.W=function(a){var b=this.p;if(b.u){var c=u(b.j,b.e.d(b.f,a.getName(),I(a).toString(),"active")),d=[],e=[b.e.d(b.f,a.getName(),I(a).toString(),"loading")];c||d.push(b.e.d(b.f,a.getName(),I(a).toString(),"inactive"));t(b.j,d,e)}J(b,"fontinactive",a);ma(this)};function ma(a){0==--a.P&&a.Y&&(a.ba?(a=a.p,a.u&&t(a.j,[a.e.d(a.f,"active")],[a.e.d(a.f,"loading"),a.e.d(a.f,"inactive")]),J(a,"active")):K(a.p))};function na(a){this.F=a;this.q=new ha;this.$=0;this.T=this.U=!0}na.prototype.load=function(a){this.a=new r(this.F,a.context||this.F);this.U=!1!==a.events;this.T=!1!==a.classes;oa(this,new fa(this.a,a),a)};
function pa(a,b,c,d,e){var f=0==--a.$;(a.T||a.U)&&setTimeout(function(){var a=e||null,l=d||null||{};if(0===c.length&&f)K(b.p);else{b.P+=c.length;f&&(b.Y=f);var h,k=[];for(h=0;h<c.length;h++){var m=c[h],w=l[m.getName()],q=b.p,H=m;q.u&&t(q.j,[q.e.d(q.f,H.getName(),I(H).toString(),"loading")]);J(q,"fontloading",H);q=null;null===V&&(V=window.FontFace?(q=/Gecko.*Firefox\/(\d+)/.exec(window.navigator.userAgent))?42<parseInt(q[1],10):!0:!1);q=V?new O(n(b.V,b),n(b.W,b),b.a,m,b.v,w):new P(n(b.V,b),n(b.W,b),
b.a,m,b.v,a,w);k.push(q)}for(h=0;h<k.length;h++)k[h].start()}},0)}function oa(a,b,c){var d=[],e=c.timeout;ga(b);var d=ia(a.q,c,a.a),f=new U(a.a,b,e);a.$=d.length;b=0;for(c=d.length;b<c;b++)d[b].load(function(b,c,d){pa(a,f,b,c,d)})};function qa(a,b,c){this.N=a?a:b+ra;this.o=[];this.R=[];this.ca=c||""}var ra="//fonts.googleapis.com/css";function sa(a,b){for(var c=b.length,d=0;d<c;d++){var e=b[d].split(":");3==e.length&&a.R.push(e.pop());var f="";2==e.length&&""!=e[1]&&(f=":");a.o.push(e.join(f))}}
qa.prototype.d=function(){if(0==this.o.length)throw Error("No fonts to load!");if(-1!=this.N.indexOf("kit="))return this.N;for(var a=this.o.length,b=[],c=0;c<a;c++)b.push(this.o[c].replace(/ /g,"+"));a=this.N+"?family="+b.join("%7C");0<this.R.length&&(a+="&subset="+this.R.join(","));0<this.ca.length&&(a+="&text="+encodeURIComponent(this.ca));return a};function ta(a){this.o=a;this.aa=[];this.I={}}
var ua={latin:"BESbswy",cyrillic:"&#1081;&#1103;&#1046;",greek:"&#945;&#946;&#931;",khmer:"&#x1780;&#x1781;&#x1782;",Hanuman:"&#x1780;&#x1781;&#x1782;"},va={thin:"1",extralight:"2","extra-light":"2",ultralight:"2","ultra-light":"2",light:"3",regular:"4",book:"4",medium:"5","semi-bold":"6",semibold:"6","demi-bold":"6",demibold:"6",bold:"7","extra-bold":"8",extrabold:"8","ultra-bold":"8",ultrabold:"8",black:"9",heavy:"9",l:"3",r:"4",b:"7"},wa={i:"i",italic:"i",n:"n",normal:"n"},xa=/^(thin|(?:(?:extra|ultra)-?)?light|regular|book|medium|(?:(?:semi|demi|extra|ultra)-?)?bold|black|heavy|l|r|b|[1-9]00)?(n|i|normal|italic)?$/;
ta.prototype.parse=function(){for(var a=this.o.length,b=0;b<a;b++){var c=this.o[b].split(":"),d=c[0].replace(/\+/g," "),e=["n4"];if(2<=c.length){var f;var g=c[1];f=[];if(g)for(var g=g.split(","),l=g.length,h=0;h<l;h++){var k;k=g[h];if(k.match(/^[\w-]+$/))if(k=xa.exec(k.toLowerCase()),null==k)k="";else{var m;m=k[1];if(null==m||""==m)m="4";else{var w=va[m];m=w?w:isNaN(m)?"4":m.substr(0,1)}k=k[2];k=[null==k||""==k?"n":wa[k],m].join("")}else k="";k&&f.push(k)}0<f.length&&(e=f);3==c.length&&(c=c[2],f=
[],c=c?c.split(","):f,0<c.length&&(c=ua[c[0]])&&(this.I[d]=c))}this.I[d]||(c=ua[d])&&(this.I[d]=c);for(c=0;c<e.length;c+=1)this.aa.push(new E(d,e[c]))}};function ya(a,b){this.a=a;this.c=b}var za={Arimo:!0,Cousine:!0,Tinos:!0};ya.prototype.load=function(a){var b=new z,c=this.a,d=new qa(this.c.api,v(c),this.c.text),e=this.c.families;sa(d,e);var f=new ta(e);f.parse();x(c,d.d(),A(b));C(b,function(){a(f.aa,f.I,za)})};function W(a,b){this.a=a;this.c=b;this.X=[]}W.prototype.B=function(a){var b=this.a;return v(this.a)+(this.c.api||"//f.fontdeck.com/s/css/js/")+(b.k.location.hostname||b.F.location.hostname)+"/"+a+".js"};
W.prototype.load=function(a){var b=this.c.id,c=this.a.k,d=this;b?(c.__webfontfontdeckmodule__||(c.__webfontfontdeckmodule__={}),c.__webfontfontdeckmodule__[b]=function(b,c){for(var g=0,l=c.fonts.length;g<l;++g){var h=c.fonts[g];d.X.push(new E(h.name,ea("font-weight:"+h.weight+";font-style:"+h.style)))}a(d.X)},y(this.a,this.B(b),function(b){b&&a([])})):a([])};function X(a,b){this.a=a;this.c=b}X.prototype.B=function(a){return(this.c.api||"https://use.typekit.net")+"/"+a+".js"};X.prototype.load=function(a){var b=this.c.id,c=this.a.k;b?y(this.a,this.B(b),function(b){if(b)a([]);else if(c.Typekit&&c.Typekit.config&&c.Typekit.config.fn){b=c.Typekit.config.fn;for(var e=[],f=0;f<b.length;f+=2)for(var g=b[f],l=b[f+1],h=0;h<l.length;h++)e.push(new E(g,l[h]));try{c.Typekit.load({events:!1,classes:!1,async:!0})}catch(k){}a(e)}},2E3):a([])};function Y(a,b){this.a=a;this.c=b}Y.prototype.B=function(a,b){var c=v(this.a),d=(this.c.api||"fast.fonts.net/jsapi").replace(/^.*http(s?):(\/\/)?/,"");return c+"//"+d+"/"+a+".js"+(b?"?v="+b:"")};
Y.prototype.load=function(a){function b(){if(e["__mti_fntLst"+c]){var d=e["__mti_fntLst"+c](),g=[],l;if(d)for(var h=0;h<d.length;h++){var k=d[h].fontfamily;void 0!=d[h].fontStyle&&void 0!=d[h].fontWeight?(l=d[h].fontStyle+d[h].fontWeight,g.push(new E(k,l))):g.push(new E(k))}a(g)}else setTimeout(function(){b()},50)}var c=this.c.projectId,d=this.c.version;if(c){var e=this.a.k;y(this.a,this.B(c,d),function(c){c?a([]):b()}).id="__MonotypeAPIScript__"+c}else a([])};function Aa(a,b){this.a=a;this.c=b}Aa.prototype.load=function(a){var b,c,d=this.c.urls||[],e=this.c.families||[],f=this.c.testStrings||{},g=new z;b=0;for(c=d.length;b<c;b++)x(this.a,d[b],A(g));var l=[];b=0;for(c=e.length;b<c;b++)if(d=e[b].split(":"),d[1])for(var h=d[1].split(","),k=0;k<h.length;k+=1)l.push(new E(d[0],h[k]));else l.push(new E(d[0]));C(g,function(){a(l,f)})};var Z=new na(window);Z.q.t.custom=function(a,b){return new Aa(b,a)};Z.q.t.fontdeck=function(a,b){return new W(b,a)};Z.q.t.monotype=function(a,b){return new Y(b,a)};Z.q.t.typekit=function(a,b){return new X(b,a)};Z.q.t.google=function(a,b){return new ya(b,a)};var $={load:n(Z.load,Z)};"function"===typeof define&&define.amd?define(function(){return $}):"undefined"!==typeof module&&module.exports?module.exports=$:(window.WebFont=$,window.WebFontConfig&&Z.load(window.WebFontConfig));}());
<?php } ?>


	function loadjscssfile(filename, filetype){
	    if (filetype=="js"){ //if filename is a external JavaScript file
	        var fileref=document.createElement('script')
	        fileref.setAttribute("type","text/javascript")
	        fileref.setAttribute("src", filename)
	    }
	    else if (filetype=="css"){ //if filename is an external CSS file
	        var fileref=document.createElement("link")
	        fileref.setAttribute("rel", "stylesheet")
	        fileref.setAttribute("type", "text/css")
	        fileref.setAttribute("href", filename)
	    }
	    if (typeof fileref!="undefined") {
	        document.getElementsByTagName("head").item(0).appendChild(fileref);
	    }
	}

	<?php if($form['customCSS'] == 0) { ?>
		loadjscssfile("<?php echo $GLOBALS['protocol'] ?>://<?php echo $_SERVER['HTTP_HOST'] ?>/static/css/form.css", "css")
		loadjscssfile("<?php echo $GLOBALS['protocol'] ?>://<?php echo $_SERVER['HTTP_HOST'] ?>/static/css/datepicker/flatpickr.min.css", "css")
		loadjscssfile("<?php echo $GLOBALS['protocol'] ?>://<?php echo $_SERVER['HTTP_HOST'] ?>/static/css/font-awesome.min.css", "css")
		var styleElement = document.createElement("style");
	  	styleElement.type = "text/css";
	  	styleElement.appendChild(document.createTextNode("<?php echo addslashes($styles); ?>"));
	  	document.getElementsByTagName("head")[0].appendChild(styleElement);
	<?php } ?>
	document.write("<?php echo addslashes($body); ?>");

	<?php echo $scripts; ?>
<?php
}

function array2csv(array &$array){
   if (count($array) == 0){
     return null;
   }
   ob_start();
   $df = fopen("php://output", 'w');
   //fputcsv($df, array_keys(reset($array)));
   foreach($array as $row){
      fputcsv($df, $row);
   }
   fclose($df);
   return ob_get_clean();
}

function download_send_headers($filename){
    // disable caching
    $now = gmdate("D, d M Y H:i:s");
    header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
    header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
    header("Last-Modified: {$now} GMT");

    // force download
    header("Content-Type: application/force-download");
    header("Content-Type: application/octet-stream");
    header("Content-Type: application/download");

    // disposition / encoding on response body
    header("Content-Disposition: attachment;filename={$filename}");
    header("Content-Transfer-Encoding: binary");
}

function OutputCsv(){
	$form=$this->lo->getForm(array("form_id"=>$this->urlpart[2]));
	$submissions = $this->lo->getSubmissions(array("formid"=>$this->urlpart[2], "all"=>true, "response"=>$this->urlpart[3]));
	//echo json_encode($submissions);exit;
	$array = array();
	//header
	$header = array('Date Submitted');
	$header_for_checking = array('Date Submitted');
	$header_for_checking_type = array('NONE');

	//$first_data = json_decode(str_replace('\"','"',$submissions[0]["data"]),true);

    $elements = $this->lo->getFormElement(array('form_id'=>$this->urlpart[2]));

    $exceptElements = array('LABEL', 'SECTION', 'PICTURE', 'STRIPE', 'PAYPAL', 'STRIPEPAYPAL', 'INPUTTABLE');
    foreach($elements as $element) {
        if(!in_array($element['type'], $exceptElements)) {
            $label = $this->pl->getElementLabel($elements, $element['_id']);
    		$label = $label ?: $element['label'];

    		$header[] = $label;
    		$header_for_checking[] = $element['_id'];
    		$header_for_checking_type[] = $element['type'];
        }
    }

    //var_dump($header);exit;

    $data = str_replace('\\r\\n', ' ', $submissions[0]["data"]);
	$first_data = json_decode(str_replace('\\','',$data),true);
	if(!$first_data) {
    	$first_data = json_decode(stripslashes($data), true);
    	if(!$first_data) {
    		$first_data  = json_decode($submissions[0]["data"], true);
    	}
    }

    foreach($first_data as $idx=>$data) {
        $field = $data['field'] ?: $data['_id'];

        if(!in_array($field, $header_for_checking)) {
            $label = $this->pl->getElementLabel($elements, $field);
    		$label = $data['label'] ?: $label;

    		$header[] = $label;
            $header_for_checking[] = $field;
        }
	}

    $array[] = $header;

    $dateformat = $this->pl->getUserDateFormat($this->lUser);
    $timeformat = $this->pl->getUserTimeFormat($this->lUser);

	foreach($submissions as $submission){
        $encrypted = $submission['encrypted'];
		$submittedAt = date($dateformat.' '.$timeformat, strtotime($submission["dateCreated"]));

		if($this->lUser['timezone']) {
			$tz = $this->lUser['timezone'];
			$timestamp = strtotime($submission["dateCreated"]);
			$dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
			$dt->setTimestamp($timestamp); //adjust the object to correct timestamp
			$submittedAt = $dt->format($dateformat . ' ' . $timeformat);
		}

		$data_array = array($submittedAt);

        for($x=1;$x<count($header);$x++) {
            $data_array[]="";
        }

		$datas = json_decode(str_replace('\"','"',$submission["data"]),true);
		$submission["data"] = str_replace('\\r\\n', ' ', $submission["data"]);
		$datas = json_decode(str_replace('\\','',$submission["data"]),true);
		if(!$datas) {
	    	$datas = json_decode($submission["data"], true);
	    }

		foreach($datas as $data){
            if($encrypted) {
                $data['value'] = $this->pl->decrypt($data['value']);
            }
            $field = $data['field'] ?: $data['_id'];

			if(is_array($data['value'])){
				$value = implode(', ', $data['value']);
			} else {
				$parts=explode('.',$data['value']);
              	if(count($parts) > 1 && strlen($parts[0]) == 32 && strlen($parts[1]) < 5){
              		if(isset($data['org_name'])) {
              			if(empty($data['org_name'])) {
              				$value = "N / A";
              			} else {
              				$value = $GLOBALS['protocol'] . '://'.$_SERVER['HTTP_HOST'].'/file/'.$data['value'].'/?f='.$data['org_name'];
              			}
              		} else {
              			$value = $GLOBALS['protocol'] . '://'.$_SERVER['HTTP_HOST'].'/file/'.$data['value'].'/';
              		}

              	} else {
              		$value = $data['value'] ? stripslashes($data['value']):"N / A";
              	}
			}

            if(in_array($field, $header_for_checking)) {
                $k = array_search($field, $header_for_checking);
                $data_array[$k] = $value;
            } else if($data['label'] == 'Payment Status') {
                $k = array_search('Payment Status', $header);
                $data_array[$k] = $value;
            }
		}

		$array[] = $data_array;
	}

	$this->download_send_headers($this->pl->slugify($form["name"]) . '_Submissions_' . date("Y-m-d") . ".csv");
	echo $this->array2csv($array);exit;
}

function OutputPdf(){

	require_once('../libs/tcpdf/examples/config/tcpdf_config_alt.php');
	require_once('../libs/tcpdf/tcpdf.php');

	$form=$this->lo->getForm(array("form_id"=>$this->urlpart[2]));
	$submission = $this->lo->getSubmissions(array("formid"=>$this->urlpart[2], "id"=>$this->urlpart[3]));
	//$data=json_decode(str_replace('\"','"',$submission[0]["data"]),true);
	$submission[0]["data"] = str_replace('\\r\\n', '<br>', $submission[0]["data"]);
	$data=json_decode(str_replace('\\','',$submission[0]["data"]),true);
	if(!$data) {
    	$data = json_decode($submission[0]["data"], true);
    }
	//print_r($data);exit;
	// create new PDF document
	$pdf = new TCPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
	// set font
	$pdf->SetFont('helvetica', '', 10);
	// remove default header/footer
	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(false);
	// add a page
	$pdf->AddPage();

	$submissionData = '';
	for($x=0; $x<count($data);$x++){
		$label = $this->pl->getElementLabel($form['elements'], $data[$x]['field']);
		$label = $label ?: $data[$x]['label'];
		if(is_array($data[$x]['value'])){
	      	$submissionData.='<tr><td><b>'.$label.'</b>: '.implode(', ', $data[$x]['value']).'</td></tr>';
	    } else if($this->pl->is_base64($data[$x]['value'])) {
			$submissionData.='<tr><td><b>'.$label.'</b>: <img src="'.$data[$x]['value'].'"></td></tr>';
		} else {
			$parts=explode('.',$data[$x]['value']);
	      	if(count($parts) > 1 && strlen($parts[0]) == 32 && strlen($parts[1]) < 5){
	      		if(isset($data[$x]['org_name'])) {
	      			$submissionData.='<tr><td><b>'.stripslashes($label).'</b>: <a href="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/file/'.$data[$x]['value'].'/?f='.$data[$x]['org_name'].'" target="_blank">'.$data[$x]['org_name'].'</a></td></tr>';
	      		} else {
	      			$submissionData.='<tr><td><b>'.stripslashes($label).'</b>: <a href="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/file/'.$data[$x]['value'].'/" target="_blank">'.$data[$x]['value'].'</a></td></tr>';
	      		}
	      	} else {
	      		$submissionData.='<tr><td><b>'.stripslashes($label).'</b>: '.stripslashes($data[$x]['value']).'</td></tr>';
	      	}
      	}
	}

    $dateformat = $this->pl->getUserDateFormat($this->lUser);
    $timeformat = $this->pl->getUserTimeFormat($this->lUser);

	$submittedAt = date($dateformat.' '.$timeformat, strtotime($submission[0]['dateCreated']));

	if($this->lUser['timezone']) {
		$tz = $this->lUser['timezone'];
		$timestamp = strtotime($submission[0]['dateCreated']);
		$dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
		$dt->setTimestamp($timestamp); //adjust the object to correct timestamp
		$submittedAt = $dt->format($dateformat . ' ' . $timeformat);
	}

	$html = '
	<table width="100%">
		<tr>
			<td class="container white-background">
				<div>
				<table>
					<tr>
						<td>
							<h1>'.stripslashes($form['name']).'</h1>
							<p style="color:gray">'.stripslashes($form['description']).'</p>
							<p style="color:gray">' . $submittedAt . '</p>
						</td>
					</tr>
					<tr class="padding"><td class="padding"></td></tr>
					<tr>
						<td>
							<table>'.$submissionData.'</table>
						</td>
					</tr>
				</table>
				</div>
			</td>
		</tr>
	</table>';

	$output = $html;

	//echo $html;exit;

	// output the HTML content
	$pdf->writeHTML($output, true, false, true, false, '');

	// reset pointer to the last page
	$pdf->lastPage();

	// ---------------------------------------------------------

	//Close and output PDF document
	$pdf->Output($form['name'] . '.pdf', 'I');

}

function OutputDemo() {
	$formid = $this->urlpart[2];
	$type = $this->urlpart[3];

?>
<!DOCTYPE html>
<html>
<head>
	<title>Your Site Title</title>
	<style>
		#form {
			width:700px;
			border:1px solid #000;
		}
	</style>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body>
	<h1>HTML Code</h1>
	<hr>

	<code>
		&lt;!DOCTYPE html&gt;<br />
		&lt;html&gt;<br />
		&lt;head&gt;<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&lt;title&gt;YOUR SITE TITLE&lt;/title&gt;<br />
			<?php if($type=='iframe') { ?>
			&nbsp;&nbsp;&nbsp;&nbsp;&lt;style&gt;<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;#form {<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;width:700px;<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;border:1px solid #000;<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;}<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&lt;/style&gt;<br />
			<?php } ?>
		&lt;/head&gt;<br />
		&lt;body&gt;<br />
		<?php if($type=='iframe') { ?>
			&nbsp;&nbsp;&nbsp;&nbsp;&lt;script type="text/javascript" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js/iframeResizer.min.js"&gt;&lt;/script&gt;<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&lt;div id="form"&gt;<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&lt;iframe class="formlets-iframe" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $formid; ?>/?iframe=true&nofocus=y" frameborder="0" width="100%"&gt;&lt;/iframe&gt;<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&lt;/div&gt;<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&lt;script type="text/javascript" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js/iframe.js"&gt;&lt;/script&gt;<br />
		<?php } else if($type=='modal') { ?>
			&nbsp;&nbsp;&nbsp;&nbsp;&lt;script type="text/javascript" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js/iframeResizer.min.js"&gt;&lt;/script&gt;<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&lt;a target="_blank" onclick="FormletOpen('<?php echo $formid; ?>');" id="<?php echo $formid; ?>" href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $formid; ?>/?iframe=true"&gt;INSERT TEXT HERE&lt;/a&gt;<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&lt;script type="text/javascript" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js/modal.js"&gt;&lt;/script&gt;<br />
			&nbsp;&nbsp;&nbsp;&nbsp;&lt;script type="text/javascript">Formlet("<?php echo $formid; ?>");&lt;/script&gt;<br />
		<?php } ?>
		&lt;/body&gt;<br />
		&lt;/html&gt;<br />
	</code>

	<h1>Output Below</h1>
	<hr>
	<?php if($type=='iframe') { ?>
	<script type="text/javascript" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js/iframeResizer.min.js"></script>
	<div id="form">
		<iframe class="formlets-iframe" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $formid; ?>/?iframe=true" frameborder="0" width="100%"></iframe>
    </div>
	<script type="text/javascript" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js/iframe.js"></script>
	<?php } else if($type=='modal') { ?>
	<script type="text/javascript" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js/iframeResizer.min.js"></script>
	<a target="_blank" onclick="FormletOpen('<?php echo $formid; ?>');" id="<?php echo $formid; ?>" href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $formid; ?>/?iframe=true">INSERT TEXT HERE</a>
	<script type="text/javascript" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js/modal.js"></script>
	<script type="text/javascript">Formlet("<?php echo $formid; ?>");</script>
	<?php } ?>
</body>
</html>
<?php
}

// for google sitemap
function Outputgooglesitemap(){
  for($l=0;$l<count($this->sitemap);$l++){
  	echo $GLOBALS['protocol'] . '://'.$_SERVER['HTTP_HOST'].'/'.$this->sitemap[$l].'/';
  }
}
//

//
function Outputcron(){
	if(($this->urlpart[2]=="daily")){
		$this->lo->cleanFormUpdateLogs(1);
		$this->lo->cleanMessageQueue(1);
    $this->lo->cleanEmailLogs(1);
		$this->lo->cleanSysSession();
    	$time = microtime();
 		$content=$this->sendMarketingEmail();
 		$this->pl->sendMail(array('body'=>"<html>".$time."<pre>".$this->pl->Xssenc($content)."</pre></html>",'from'=>'hello@formlets.com','to'=>'hello@formlets.com','subject'=>'Daily cron result'));
    	echo "ok";
 	} else if(($this->urlpart[2]=="15min")){
     	$time = microtime();
 		$content=$this->sendWelcomeEmail();
	    if($content){
	 		$this->pl->sendMail(array('body'=>"<html>".$time."<pre>".$this->pl->Xssenc($content)."</pre></html>",'from'=>'hello@formlets.com','to'=>'hello@formlets.com','subject'=>'Sent welcome email'));
	    }
		echo "ok";
 	} else if(($this->urlpart[2]=="5min")) {
        //get blocked messages from sendgrid
        $username = $GLOBALS['conf']['sendgrid']['user'];
        $password = $GLOBALS['conf']['sendgrid']['key'];
        $qry_str = "?api_user=".$username."&api_key=".$password."&date=1";
        $ch = curl_init();

        // Set query data here with the URL
        curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/api/blocks.get.json' . $qry_str);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_TIMEOUT, 3);
        $content = trim(curl_exec($ch));
        curl_close($ch);

        $responses = json_decode($content);
      //  if($responses){
      //    $this->pl->sendMail(array('body' => print_r($responses,1), 'from' => 'hello@formlets.com', 'to' => 'filip@oxopia.com', 'subject' => 'Cron done'));
      //  }
        foreach($responses as $response) {
            $arr = array(
                'email' => $response->email,
                'date' => $response->created
            );

            $messages = $this->lo->getMessageQueue($arr);

            if(count($messages)) {


                foreach($messages as $message) {
                    $data = json_decode($message['data']);

                    $client = SesClient::factory(array(
                        'version'=> 'latest',
                        'region' => $GLOBALS['conf']['ses']['region'],
                        'credentials'=> [
                            'key'    => $GLOBALS['conf']['ses']['key'],
                            'secret' => $GLOBALS['conf']['ses']['secret'],
                        ]
                    ));

                    try {
                        if($data->subject && $data->html) {
                            $result = $client->sendEmail([
                                'Destination' => [
                                    'ToAddresses' => [
                                        $response->email,
                                    ],
                                ],
                                'Message' => [
                                    'Body' => [
                                        'Html' => [
                                            'Charset' => 'UTF-8',
                                            'Data' => $data->html,
                                        ],
                                    ],
                                    'Subject' => [
                                        'Charset' => 'UTF-8',
                                        'Data' => $data->subject,
                                    ],
                                ],
                                'Source' => 'hello@formlets.com',
                            ]);

                            $messageId = $result->get('MessageId');
                            //echo("Email sent! Message ID: $messageId"."\n");
                        }
                    } catch (SesException $error) {
                        //echo("The email was not sent. Error message: ".$error->getAwsErrorMessage()."\n");
                    }

                    $this->lo->deleteMessageQueue(array('id'=>$message['_id']));
                }
            }

            $qry_str = "api_user=".$username."&api_key=".$password."&email=".$response->email;
            $ch = curl_init();
            // Set query data here with the URL
            curl_setopt($ch, CURLOPT_URL, 'https://api.sendgrid.com/api/blocks.delete.json');

            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_TIMEOUT, 3);
            // Set request method to POST
            curl_setopt($ch, CURLOPT_POST, 1);

            // Set query data here with CURLOPT_POSTFIELDS
            curl_setopt($ch, CURLOPT_POSTFIELDS, $qry_str);

            $content = trim(curl_exec($ch));
            curl_close($ch);

        }

        //echo json_encode($responses);
    } else {
   		$this->Output404();
 	}
}
//

function OutputWelcomeEmail(){
  $content="Hi,<br>
  <br>
  Thank you for registering your Formlets account<br>
  If you have any questions, remarks or feature requests<br>
  please just hit reply<br>
  <br>
  If you run a business, you are invited to register for a paying account.<br>
  It is no obligation, but it would be appreciated.<br>
<br>
  If you wish to use multiple forms, please don't register multiple accounts, <br>
  a paying account offers the possibility to create multiple forms in one account<br>
  If you are a non-profit or on a tight budget, please contact us, <br>
  we are always open to support you with your project.<br>

  <br>
  We are available for any question you might have<br>
  <br>
  Kind regards<br>
  Filip<br>
  Formlets";

return $content;
}


function OutputMailNewNovalidation(){

$content="Hi,<br>
<br>
Just a personal thank you for registering a Formlets account<br>
<br>
We saw you did not validate the email of the account you created.<br>
if you just missed it , it is needed to activate our service.<br>
<br>
If you think Formlets is nothing for you ...<br>
If you could <b>just hit reply and let us know why</b>... you would be of great help
<br><br>
At formlets we strongly believe in Customer feedback<br>
Your reply goes straight to my personal mailbox<br>
<br>
Thank you<br>
Filip<br>
Formlets<br>
";

return $content;
}



//
function OutputHeaderMenu(){
  if($GLOBALS["conf"]["env"]=="production"){
    $host=$GLOBALS['protocol'] . "://".$_SERVER["HTTP_HOST"]."/";
  } else {
    $host=$GLOBALS['level'];
  }
?>
    <nav class="navbar navbar-expand-lg navbar-light">
        <a class="navbar-brand" href="/"><img src="/static/img/logo-black.svg" alt="" width="60"><span class="my-auto"><b>form</b>lets</span></a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation" onClick="toggleMenu()">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav">
                <?php if($this->urlpart[1]!='index') { ?>
                    <li class="nav-item <?php if($this->urlpart[1]=='index') {echo'active';} ?>">
                        <a href="/" class="nav-link">Home</a>
                    </li>
                <?php } ?>
                <li class="nav-item <?php if($this->urlpart[1]=='features') {echo'active';} ?>">
                    <a href="<?php echo $host;?>features/" class="nav-link">Features</a>
                </li>
                <li class="nav-item <?php if($this->urlpart[1]=='pricing') {echo'active';} ?>">
                    <a href="<?php echo $host;?>pricing/" class="nav-link">Pricing</a>
                </li>
                <li class="nav-item <?php if($this->urlpart[1]=='support') {echo'active';} ?>">
                    <a href="<?php echo $host;?>support/" class="nav-link">Support</a>
                </li>
                <?php if(isset($this->lUser)) { ?>
                    <li class="nav-item nav-btn">
                        <a href="<?php echo $host;?>form/" class="btn btn-outline-dark">Login to Form Manager</a>
                    </li>
                <?php } else { ?>
                    <li class="nav-item nav-btn <?php if($this->urlpart[1]=='signup') {echo'active';} ?>">
                        <a href="<?php echo $host;?>signup/" class="btn btn-outline-dark">Signup</a>
                    </li>
                    <li class="nav-item nav-btn <?php if($this->urlpart[1]=='login') {echo'active';} ?>">
                        <a href="<?php echo $host;?>login/?red=/form/" class="btn btn-outline-dark">Login</a>
                    </li>
                <?php } ?>
            </ul>
        </div>
    </nav>
<?php
}
//


// used by login signin - lost password

function OutputHeader($prop=null){
?>
<!DOCTYPE html>
<html>
<head>
	<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
	new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
	j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
	'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
	})(window,document,'script','dataLayer','GTM-5F5XK3B');</script>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $prop['title'];?></title>
	<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['level'];?>static/css/form.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
	<?php if($this->urlpart[1] != 'signup' && $this->urlpart[1] != 'login') { ?>
	<link rel="stylesheet" href="<?php echo $GLOBALS['level'];?>static/css/font-awesome.min.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
	<?php } ?>
	<?php if($this->urlpart[1] == 'signup' || $this->urlpart[1] == 'newpassword') { ?>
	<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['level'];?>static/css/signup.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
	<?php } ?>
	<style type="text/css">
		<?php if($this->urlpart[1] == 'newpassword') { ?>
			#pswd_info {
				bottom: -10px;
			}
		<?php } ?>
	</style>
</head>
<body class="link">
	<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5F5XK3B"
	height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
  <div class="centered small-12 large-6 fcc" style="max-width: 600px;">
<?php
}
//


//

function OutputMarketingHeader($prop=null){
?>
<!DOCTYPE html>
<html class="wf-loading">
<head>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-5F5XK3B');</script>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $prop['title'];?></title>
    <link rel="icon" type="image/x-icon" href="/static/img/favicon.ico" />
	<meta name="description" content="<?php echo $prop['descr'];?>">

  <link rel="stylesheet" type="text/css" href="/static/css/bootstrap.min.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
  <link rel="stylesheet" type="text/css" href="/static/css/font-awesome.min.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
<link rel="stylesheet" type="text/css" href="/static/css/marketing.min.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">


</head>
<body>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5F5XK3B"
height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>
<section id="Header-Container">
    <div class="overlay"></div>
    <header>
        <div class="container">
        <?php
            $this->OutputHeaderMenu();
        ?>
        </div>
    </header>
    <div class="hero-content">
        <div class="container">
          <div class="row">
            <div class="col-lg my-auto hero-text">
              <h1 class="inner-header"><?php echo $prop['title'];?></h1>
            </div>
          </div>
        </div>
    </div>
</section>
<?php
}
//

function OutputMarketingFooter2() {
?>
<section id="Footer-Container">
    <div class="container">
        <div class="row">
        <div class="col-md">
            <div class="social">
                <ul>
                    <li><a href="https://www.facebook.com/formlets" class="fa fa-facebook-square"></a></li>
                    <li><a href="https://www.twitter.com/formlets" class="fa fa-twitter"></a></li>
                </ul>
            </div>
            <span class="copy">Copyright &copy; Formlets - Oxopia 2018.</span>
        </div>
        <div class="col-md">
            <div class="footer-links">
                <ul>
                    <?php if(!$this->lUser) { ?>
                        <li><a href="/signup/">Signup</a></li>
                        <li><a href="/login/?red=/form/">Login</a></li>
                        <li><a href="/pricing/">Pricing</a></li>
                    <?php } ?>
                    <li><a href="/support/">Support</a></li>
                    <li><a href="/privacy/">Privacy</a></li>
                    <li><a href="/terms/">Terms</a></li>
                </ul>
            </div>
        </div>
        </div>
    </div>
</section>

<script>
var init = 1;
function toggleMenu() {
    var nav = document.getElementById('navbarSupportedContent');
    if (nav.style.display === "none" || window.init==1) {
        nav.style.display = "block";
        window.init++;
    } else {
        nav.style.display = "none";
    }
}
</script>
</body>
</html>
<?php
}

function OutputMarketingFooter(){
  $m='marketingfooter'; // this is the name for the translation files
?>
<!-- <footer>
	<ul class="inline-list">
	<?php if(!$this->lUser) { ?>
		<li><a href="<?php echo $GLOBALS['level'];?>signup/" class="button-small border"><?php echo $this->pl->trans($m,'Sign Up');?></a></li>
		<li><a href="<?php echo $GLOBALS['level'];?>login/?red=/form/" class="button-small"><?php echo $this->pl->trans($m,'Login');?></a></li>
		<li><a href="<?php echo $GLOBALS['level'];?>pricing/" class="button-small"><?php echo $this->pl->trans($m,'Pricing');?></a></li>
	<?php } ?>
		<li><a href="<?php echo $GLOBALS['level'];?>support/" class="button-small"><?php echo $this->pl->trans($m,'Support');?></a></li>
		<li><a href="<?php echo $GLOBALS['level'];?>privacy/" class="button-small"><?php echo $this->pl->trans($m,'Privacy');?></a></li>
		<li><a href="<?php echo $GLOBALS['level'];?>terms/" class="button-small"><?php echo $this->pl->trans($m,'Terms');?></a></li>
	</ul><center>
</footer> -->

<footer>
          <div class="container-fluid">
            <div class="row p-3">
              <div class="logo-copy col-12 order-2 order-sm-2 order-md-2 order-lg-1 col-lg-6 col-xl-6">
                <div class="footer-logo">
                  <a href="/">
                    <img src="/static/img/logo-black.svg" alt="">
                    <span><strong>form</strong>lets</span>
                  </a>
                </div>
                <div class="copy pl-3">
                  <span>&copy; Copyright 2018. All rights reserved.</span>
                </div>
              </div>
              <div class="social-links col-12 order-1 order-sm-1 order-md-1 order-lg-1 col-md-push-12 col-lg-6 col-xl-6">
                <div class="links">
                  <ul>
                    <li><a href="<?php echo $GLOBALS['level'];?>support/" ><?php echo $this->pl->trans($m,'Support');?></a></li>
                    <li><a href="<?php echo $GLOBALS['level'];?>privacy/" ><?php echo $this->pl->trans($m,'Privacy');?></a></li>
                    <li><a href="<?php echo $GLOBALS['level'];?>terms/" ><?php echo $this->pl->trans($m,'Terms');?></a></li>
                  </ul>
                </div>
                <div class="social">
                  <ul>
                    <li><a href="https://www.facebook.com/formlets"><i class="fab fa-facebook-square"></i></a></li>
                    <li><a href="https://www.twitter.com/formlets"><i class="fab fa-twitter"></i></a></li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </footer>
<?php
  $this->OutputFooter();
}



//
function OutputFooter(){
	//9hrs., 50 OvertheCountry
?></div>
<script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/common.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
<script>
$('body').on('mouseenter mouseleave','.dropdown',function(e){
  var _d=$(e.target).closest('.dropdown');
  var _dm=_d.find('.dropdown-menu');
  _d.addClass('show');
  _dm.addClass('show');
  setTimeout(function(){
    _d[_d.is(':hover')?'addClass':'removeClass']('show');
    _dm[_d.is(':hover')?'addClass':'removeClass']('show');
  },300);
});
</script>

<?php if(($this->pl->transgen)&&($GLOBALS['conf']['env']<>'production')){
?><div style="background-color:red;">Please add these parameters to the translation files:<br>
<?php echo $this->pl->transgen;?>
</div><?php
}
$this->_benchit();
?>
<!-- <?php echo $this->clientip;
?>
-->
</body></html>
<?php
}
//

abstract function sendStripeConfEmail($request);

//
function output__Api(){
    if (($this->urlpart[2] == "_stripeWebHook")){
        // Set your secret key: remember to change this to your live secret key in production
        // See your keys here: https://dashboard.stripe.com/account/apikeys
        require_once('../libs/stripe-php-3.20.0/init.php');

        \Stripe\Stripe::setApiKey($GLOBALS["conf"]["stripe_secret_key"]);
        //var_dump(\Stripe\Event::retrieve("card_19LASKIsVTXtNnUmVUoferbr"));
        //keys will be set on config files

        // Retrieve the request's body and parse it as JSON
        $input = @file_get_contents("php://input");
        $request = json_decode($input, true);
        if($request['type']=='customer.subscription.updated' || $request['type']=='customer.subscription.created' || $request['type']=='customer.subscription.deleted' || $request['type']=='invoice.payment_failed'){
        	$this->sendStripeConfEmail($request);
        }
        $this->lo->_StripeWebHook($request);

    } else {
    	$request = json_decode($_REQUEST['json'], true);
    	if($_REQUEST['upload']) {
            if ($this->pl->isPreviewUser($this->lAccount)) {
                header("HTTP/1.1 403 Permission Denied");
                header('Content-type: application/json');
                $array['Error']='You cannot upload any files when you are in preview mode. <a href="/settings/account/">Register to be able to upload files</a>';
                echo json_encode($array);exit;
            }
    		$request = $_REQUEST;
    		$request['value'] = $_FILES['file'];
    	}
    	$request['domain']= $this->st_domain;
    	$request['user_id']	=$this->uid;
        $request['user_account']=$this->account; // so we should know now what this user his plan is , no need to lookup session data in logic anymore
    	if (in_array($_REQUEST['method'], $this->lo->methodlist)){
    	    if ($this->urlpart[2] == "json"){
                $request['accountId'] = $this->lAccount['_id'];
                $request['user_id'] = $this->lAccountOwner['_id'];
                header('Content-type: application/json');
    	        echo json_encode($this->lo->{$_REQUEST['method']}($request));
    	    } else {
    	        echo $this->lo->{$_REQUEST['method']}($request);
            }
    	} else {
    	    $array['Error']='Method not found';
    	    echo json_encode($array);
    	}
    }

     $data = $_SERVER['REQUEST_URI']."<br>".print_r($request, true);
     for($s=0;$s<count($GLOBALS['bench_sql_list']);$s++) {
         $data.='<br><b>'.($s+1).'</b>: '.$GLOBALS['bench_sql_list'][$s];
     }
    // $this->pl->sendMail(array('body'=>$data,'from'=>'hello@formlets.com','to'=>'dev@formlets.com', 'cc'=>'elias@oxopia.com', 'subject'=>'API sql log '.$this->pl->Xssenc($this->form['name'])));

	exit;
}

function outputStripeMailHeader(){
?>
	<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
	<html xmlns="http://www.w3.org/1999/xhtml">
	<head>
		<style>
			@import url(https://fonts.googleapis.com/css?family=Source+Sans+Pro);

			* {
				margin: 0;
				padding: 0;
				font-family: "Source Sans Pro", "Helvetica Neue", "Helvetica", Helvetica, Arial, sans-serif;
				font-size: 100%;
				line-height: 1.6;
			}
			img {
				max-width: 100%;
			}
			body {
				-webkit-font-smoothing: antialiased;
				-webkit-text-size-adjust: none;
				width: 100%!important;
				height: 100%;
			}
			/* -------------------------------------
					COLORS
			------------------------------------- */
			body {
				background-color: #F5F5F5;
				color: #4F5855;
			}
			.white-background {
				background-color: #FFF;
			}
			.lightGray {
				color: #C5CAC5;
			}

			table.body-wrap .container {
				border: 2px solid #D6D7D6;
				border-radius: 3px;
			}
			a {
				color: #4baec2;
			}
			/* -------------------------------------
					ELEMENTS
			------------------------------------- */

			.btn-primary {
				text-decoration: none;
				color: #FFF;
				background-color: #4BAEC2;
				border: solid #4BAEC2;
				border-width: 10px 20px;
				line-height: 2;
				font-weight: bold;
				margin-right: 10px;
				text-align: center;
				cursor: pointer;
				display: inline-block;
				border-radius: 25px;
			}
			.btn-secondary {
				text-decoration: none;
				color: #FFF;
				background-color: #d6d7d6;
				border: solid #d6d7d6;
				border-width: 10px 20px;
				line-height: 2;
				font-weight: bold;
				margin-right: 10px;
				text-align: center;
				cursor: pointer;
				display: inline-block;
				border-radius: 25px;
			}
			.last {
				margin-bottom: 0;
			}
			.first {
				margin-top: 0;
			}
			.padding {
				padding: 10px 0;
			}

			.submission-data:first-child td {
				border-top: 1px solid #d6d7d6;
			}
			.submission-data td {
				padding: 5px 0;
				border-bottom: 1px solid #d6d7d6;
			}
			.submission-label {
				width: 35%;
				font-weight: 700;
			}
			.submission-value {
				width: 65%;
			}
			/* -------------------------------------
					BODY
			------------------------------------- */
			table {
				border-collapse: collapse;
			}
			table.body-wrap {
				width: 100%;
				padding: 20px;
			}
			/* -------------------------------------
					FOOTER
			------------------------------------- */
			table.footer-wrap {
				width: 100%;
				clear: both!important;
			}
			.footer-wrap .container p {
				font-size: 12px;
				color: #666;

			}
			table.footer-wrap a {
				color: #999;
			}
			/* -------------------------------------
					TYPOGRAPHY
			------------------------------------- */
			h1, h2, h3 {
				font-family: "Source Sans Pro", "Helvetica Neue", Helvetica, Arial, "Lucida Grande", sans-serif;
				line-height: 1.1;
				margin-bottom: 15px;
				margin: 40px 0 10px;
				line-height: 1.2;
				font-weight: 200;
			}
			h1 {
				font-weight: 500;
				margin: 18px;
				font-size: 36px;
			}
			h1 a {
				text-decoration: none;
			}
			h2 {
				font-size: 28px;
			}
			h3 {
				margin: 12px;
				font-size: 14px;
				font-weight: 500;
				text-transform: uppercase;
				letter-spacing: 0.08em;
			}
			p, ul, ol {
				margin-bottom: 10px;
				font-weight: normal;
				font-size: 14px;
			}
			ul li, ol li {
				margin-left: 5px;
				list-style-position: inside;
			}
			/* ---------------------------------------------------
					RESPONSIVENESS
					Nuke it from orbit. It's the only way to be sure.
			------------------------------------------------------ */
			/* Set a max-width, and make it display as block so it will automatically stretch to that width, but will also shrink down on a phone or something */
			.container {
				margin-top: 10px;
				display: block!important;
				max-width: 600px;
				margin: 0 auto!important; /* makes it centered */
				clear: both!important;
			}
			/* Set the padding on the td rather than the div for Outlook compatibility */
			.body-wrap .container {
				padding: 20px;
			}
			/* This should also be a block element, so that it will fill 100% of the .container */
			.content {
				max-width: 600px;
				margin: 0 auto;
				display: block;
			}
			/* Let's make sure tables in the content area are 100% wide */
			.content table {
				width: 100%;
			}
		</style>
	</head>
	<body>
<?php
}

function outputStripeMailFooter(){
?>
	<!-- footer -->
	<table class="footer-wrap">
		<tr>
			<td></td>
			<td class="container">
			</td>
			<td></td>
		</tr>
	</table>
	<!-- /footer -->

<?php
}

function outputEmailTemplateSubmissionMail($data, $form=null) {
	ob_start();

	if($data['templateHTML']) {
	    $templateHTML = $data['templateHTML'];
	    $templateHTML = str_replace('{email_subject}', $data['subject'], $templateHTML);
        $templateHTML = str_replace('{email_date}', date('m/d/Y, h:i a'), $templateHTML);
        $templateHTML = str_replace('{email_body}', $data['template'], $templateHTML);
        echo $templateHTML;
	} else {
	    $this->outputStripeMailHeader();
?>
        <table class="body-wrap">
            <tr>
                <td></td>
                <td class="container white-background">
                    <!-- content -->
                    <div class="content">
                    <table>
                        <tr>
                            <td align="center">
                                <h1><?php echo $data['subject']; ?></h1>
                                <p class="lightGray"><?php echo date('m/d/Y, h:i a') ?></p>
                            </td>
                        </tr>
                        <tr class="padding"><td class="padding"></td></tr>
                        <tr>
                            <td>
                                <?php echo $data['template']; ?>
                            </td>
                        </tr>
                    </table>
                    </div>
                    <!-- /content -->
                </td>
                <td></td>
            </tr>
        </table>
<?php
        $this->outputStripeMailFooter();
	}
	return ob_get_clean();
}

function OutputEmailTemplateViewLimit($form, $owner, $percentage) {
	ob_start();
	$this->outputStripeMailHeader();
?>
	Hi <?php echo $owner['firstName'] ?>, <br> <br>

    Your form named: <b><?php echo $form['name'] ?></b> reached <?php echo $percentage ?>% of its view limit. <br> <br>

    To remove this view limit, please <a href="<?php echo $GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/settings/subscription/'; ?>">upgrade here</a>.<br> <br>

    Thanks,<br> <br>

    Formlets Team
<?php
	$this->outputStripeMailFooter();
	return ob_get_clean();
}

function outputSubmissionMail($form, $data, $user=null){
    if(!count($user)) {
        $user = $this->lo->_getUsers(array('id'=>$form['owner']));
        if(!count($user)) {
            $user = $this->lo->_getUsers(array('accountId'=>$form['owner']));
        }
    }
    $form_owner = $user[0];

    $dateformat = $this->pl->getUserDateFormat($form_owner);
    $timeformat = $this->pl->getUserTimeFormat($form_owner);

	$submittedAt = date($dateformat.' '.$timeformat, strtotime("now"));

	if($form_owner['timezone']) {
		$tz = $form_owner['timezone'];
		$timestamp = strtotime("now");
		$dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
		$dt->setTimestamp($timestamp); //adjust the object to correct timestamp
		$submittedAt = $dt->format($dateformat . ' ' . $timeformat);
	}

	ob_start();
	$this->outputStripeMailHeader();
?>
	<table class="body-wrap">
		<tr>
			<td></td>
			<td class="container white-background">
				<!-- content -->
				<div class="content">
				<table>
					<tr>
						<td align="center">
							<h3>New Response</h3>
							<h1><?php echo stripslashes($form['name']); ?></h1>
							<p class="lightGray"><?php echo $submittedAt; ?></p>
						</td>
					</tr>
					<tr class="padding"><td class="padding"></td></tr>
					<tr>
						<td>
							<?php if($data) { ?>
							<table>
								<?php	foreach($data as $d){
									if(isset($d['label']) && isset($d['value'])) {?>
								<tr class="submission-data">
									<td class="submission-label"><?php echo htmlentities($d['label']); ?></td>
									<td class="submission-value">
									<?php
                  					if(isset($d['org_name'])) {
                                        $parts = explode(';;', $d['value']);
                                        if(count($parts) > 1) {
                                            $org_names = explode(';;', $d['org_name']);
                                            $ctr=0;
                                            echo '<ul>';
                                            foreach($parts as $file) {
                                                echo '<li><a href="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/file/'.$file.'/?f='.$org_names[$ctr].'" target="_blank">'.htmlentities($org_names[$ctr]).'</a></li>';
                                                $ctr++;
                                            }
                                            echo '</ul>';
                                        } else {
                                            echo '<a href="'.$GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/file/'.$d['value'].'/?f='.$d['org_name'].'" target="_blank">'.htmlentities($d['org_name']).'</a>';
                                        }

                  					} else if($this->pl->is_base64($d['value'])) {
					                    echo '<img src="'.$d['value'].'">';
					                } else {
                  						if(strtolower($d['type']) == 'inputtable') {
                  							$data = stripslashes(htmlentities($d['value']));
	                              			$filter = explode('), ', $data);
	                              			foreach($filter as $f) {
	                              				$filter2 = explode(' (', $f);

	                              				$question = $filter2[0];
	                              				$answer = str_replace(')', '', $filter2[1]);

	                              				echo '<strong>'.$question.'</strong>: ' . ' ' . $answer . '<br>';
	                              			}
                  						} else {
                  							echo htmlentities(stripslashes($d['value']));
                  						}
                  					}
                  					?>
                  					</td>
								</tr>
								<?php }} ?>
							</table>
							<?php } else { ?>
							No Data
							<?php } ?>
						</td>
					</tr>
				</table>
				</div>
				<!-- /content -->
			</td>
			<td></td>
		</tr>
	</table>
<?php
	$this->outputStripeMailFooter();
	return ob_get_clean();
}


function outputStripeMail($thelist, $data){
ob_start();
$this->outputStripeMailHeader();
?>
        <table class="body-wrap">
        <tr>
        <td></td>
        <td class="container white-background">
          <div class="content">
          <table>
            <tr>
              <td align="center" class="padding">
              <?php
              if($data['current_plan']['name']=='PRO-MONTHLY-OXOPIA'){
              ?>
                <img src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/img/plans_pro.png" alt="Formlets" data-inline-ignore>
              <?php
          	  } else {
          	  ?>
          	    <img src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/img/plans_basic.png" alt="Formlets" data-inline-ignore>
          	  <?php
          	  }
          	  ?>
              </td>
            </tr>
            <tr>
              <td align="center">
                <h1>
                <?php
                	if($data['type'] == 'customer.subscription.updated' || $data['type'] == 'invoice.payment_failed'){
                		if($data['update_type']=='upgrade'){
                			echo 'Account Upgraded';
                		} else if($data['update_type']=='payment_failed') {
                            echo 'Payment Failed';
                        } else {
                			echo 'Account Downgraded';
                		}
                	} else {
                		echo 'Account Upgraded';
                	}
                ?>
                </h1>
                <h3 class="lightGray"><?php echo $thelist["name"]; ?></h3>
              </td>
            </tr>
            <tr class="padding"><td class="padding"></td></tr>
            <tr>
              <td>
              	<?php
                if($data['update_type']=='payment_failed') {
                ?>
                    <p>
                        Unfortunately, your most recent invoice payment was declined.<br>
                        This could be due to a change in your card number, your card expiring,<br>
                        cancellation of your credit card, or the card issuer not recognizing the<br>
                        payment and therefore taking action to prevent it.
                    </p>
                    <p>
                        Please update your payment information as soon as possible <a href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/settings/subscription/">here</a>.
                    </p>
                <?php
                } else {
                  	if($data['type']=='customer.subscription.created' || ($data['type']=='customer.subscription.updated' && $data['update_type']=='upgrade')){
                  	?>
                    	<p>You have successfully upgraded your Formlets account to <?php echo $thelist["name"]; ?> status.</p>
                    	<p>You can now take advantage of these features:</p>
                    <?php
                	} else {
                	?>
                		<p>You have downgraded your Formlets account to <?php echo $thelist["name"]; ?> status.</p>
                		<p>You can now only take advantage of these features:</p>
                	<?php
                	}
                	?>

                    <ul>
                    <?php foreach($thelist["descr"] as $descr){ ?>
                        <li><?php echo $descr; ?></li>
                    <?php } ?>
                    </ul>
                <?php } ?>
                <p>Thanks! <br>
                <span class="lightGray">The Formlets Team</span>
                </p>
              </td>
            </tr>
              </table>
              </div>
            </td>
            <td></td>
          </tr>
        </table>
<?php
$this->outputStripeMailFooter();
return ob_get_clean();
}

function Outputpricing(){


  $cs=array('USD'=>'$','EUR'=>'€');
  $ms=array('MONTHLY'=>'/month','YEARLY'=>'/year');
  if($cs[$_GET['cur']]){$cur=$_GET['cur'];}
  if($ms[$_GET['mode']]){$mode=$_GET['mode'];}
  $m="pricing";
  if(!$cur){$cur="USD";}
  if(!$mode){$mode="MONTHLY";}

  $usLink = '/pricing/?cur=USD&mode='.$mode;
  $eurLink = '/pricing/?cur=EUR&mode='.$mode;
  $modeM = '/pricing/?cur='.$cur.'&mode=MONTHLY';
  $modeY = '/pricing/?cur='.$cur.'&mode=YEARLY';

   // $this->OutputHeader(array("title"=>"Index page","descr"=>"Description"));

   $this->OutputMarketingHeader(array("title"=>"Pricing","descr"=>""));
?>

<script>
function changeMode() {
    <?php if($mode == 'MONTHLY') { ?>
    var link = '<?php echo $modeY; ?>';
    <?php } else { ?>
    var link = '<?php echo $modeM; ?>';
    <?php } ?>

    window.location.href=link;
}

function changeCur() {
    <?php if($cur == 'USD') { ?>
    var link = '<?php echo $eurLink; ?>';
    <?php } else { ?>
    var link = '<?php echo $usLink; ?>';
    <?php } ?>

    window.location.href=link;
}
</script>

<section id="Content-Container">

      <div class="pricing">
        <div class="container">
          <div class="row">
            <div class="col-lg pricing-header">
              <img src="/static/img/moneyback.png" alt="">
              <p>
                We offer a <strong>full refund</strong> if you wish to cancel in the <strong>first 30 days</strong> of your new plan <br>
                You are free to cancel at any time when you see fit. By downgrading back to the free personal plan, all your forms will be kept <br>
                The Personal plan is Free forever
              </p>

            </div>
          </div>
          <div class="pricing-table">
            <div class="pricing-switch center">
              <ul class="inline">
                <li>Pay yearly:</li>
                <li>
                  <form>
                    <input id="mode" class="switch" type="checkbox" <?php if($mode == 'YEARLY'){ echo 'checked';} ?> onchange="changeMode()">
                    <label for="mode" class="switch"></label>
                  </form>
                </li>
              </ul>
              <ul class="inline">
                <li>Pay in Euro:</li>
                <li>
                    <form>
                    <input id="cur" class="switch" type="checkbox" <?php if($cur == 'EUR'){ echo 'checked';} ?> onchange="changeCur()">
                    <label for="cur" class="switch"></label>
                    </form>
                </li>
              </ul>
            </div>

            <ul class="pricelist">

                <?php
        	  	for ($a=0;$a<count($this->availableplans["list"]);$a++){
        	    $thelist=$this->availableplans["list"][$a];
        		    if($thelist['status']=="active"){
        		?>
                    <li class="price-item" <?php if($thelist['display'] == false) { echo 'style="display:none;"'; } ?>>
                      <span>
                        <?php echo $thelist['name'];?>
                        <?php if($thelist['plan']=="FREE"){?>
                            <div class="prices">
                              <span class="usd monthly">Free</span>
                            </div>
                        <?php } else { ?>
                            <div class="prices">
                              <span class="usd monthly"><?php echo $cs[$cur].$thelist['stripe_plans'][$cur][$mode]; ?></span>
                              <em class="monthly"><?php echo $ms[$mode]; ?></em>
                            </div>
                        <?php } ?>
                      </span>
                      <a href="/signup/?ref=<?php echo $thelist['name']; ?>&cur=<?php echo $cur; ?>&mode=<?php echo $mode; ?>" class="price-btn">Start now</a>
                      <ul>
                          <?php foreach($thelist['descr'] as $descr){?>
                              <li><?php echo $descr; ?></li>
                          <?php } ?>
                      </ul>

                    </li>
        		    <?php
        			}
        	    }
        	    ?>
            </ul>
          </div>
        </div>

      </div>

      <div class="parallax">
        <div class="parallax-overlay"></div>
        <div class="container">
          <div class="row">
            <div class="col-lg my-auto parallax-text">
              <h3>Need help in setting up the forms? Register a Paying account.</h3>
              <span>We will setup or transfer your forms for you. <strong>A free service exclusive for Paying accounts.</strong></span>
            </div>
          </div>
        </div>
      </div>
    </section>
<?php
$this->OutputMarketingFooter2();
}
//


function OutputIndex(){
	$m = "index";
   // $this->OutputHeader(array("title"=>"Index page","descr"=>"Description"));
?>
<!DOCTYPE html>
<html class="wf-loading">
<head>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-5F5XK3B');</script>
	<meta charset="utf-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title><?php echo $this->pl->trans($m,'Beautiful, Free Forms for your website - Very Easy to use - Formlets'); ?></title>
    <link rel="icon" type="image/x-icon" href="/static/img/favicon.ico" />
	<meta name="description" content="<?php echo $this->pl->trans($m,'Make beautiful, effective forms to share or add to your website'); ?>">
	<meta name="twitter:card" content="<?php echo $this->pl->trans($m,'summary'); ?>" />
	<meta name="twitter:site" content="@formlets" />
	<meta name="twitter:title" content="<?php echo $this->pl->trans($m,'Make beautiful, effective forms to share or add to your website'); ?>" />
	<meta name="twitter:description" content="<?php echo $this->pl->trans($m,'Setup Forms, connect the data'); ?>" />
	<meta name="twitter:image" content="<?php echo $GLOBALS['level'];?>static/img/twitter.png" />
	<meta name="twitter:url" content="<?php echo $GLOBALS['protocol']; ?>://www.formlets.com" />
	<meta property="og:type"   content="<?php echo $this->pl->trans($m,'website'); ?>" />
	<meta property="og:title" content="<?php echo $this->pl->trans($m,'Make beautiful, effective forms to share or add to your website'); ?>" />
	<meta property="og:description" content="<?php echo $this->pl->trans($m,'Setup Forms, connect the data'); ?>" />
	<meta property="og:type" content="<?php echo $this->pl->trans($m,'website'); ?>" />
	<meta property="og:url" content="<?php echo $GLOBALS['protocol']; ?>://www.formlets.com/" />
	<meta property="og:image" content="<?php echo $GLOBALS['level'];?>static/img/twitter.png" />
	<meta name="verifyownership" content="79982cab1429a8023b81e18093b9e3d7"/>
  <link rel="stylesheet" type="text/css" href="/static/css/bootstrap.min.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
  <link rel="stylesheet" type="text/css" href="/static/css/font-awesome.min.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
<link rel="stylesheet" type="text/css" href="/static/css/marketing.min.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
</head>
<body>
<noscript><iframe src="https://www.googletagmanager.com/ns.html?id=GTM-5F5XK3B" height="0" width="0" style="display:none;visibility:hidden"></iframe></noscript>

<section id="Header-Container">
    <div class="overlay"></div>
    <header>
        <div class="container">
            <?php $this->OutputHeaderMenu(); ?>
        </div>
    </header>
    <div class="hero-content">
        <div class="container">
            <div class="row">
                <div class="col-lg my-auto hero-text">
                    <h1>Make beautiful, powerful forms with the #1 online form creator</h1>
                    <p>Capture unlimited responses, receive every form submit in your mailbox or CSV.</p>
                    <p>Make Contact forms, Order forms, Surveys or many others.</p>
                    <div class="cta-container">
                        <a href="/try/" class="btn btn1 btn-lg">Start now with a free form</a>
                        <span>No need to signup to try</span>
                    </div>
                    <picture>
                        <source media="(max-width: 767px)" srcset="/static/img/Formlets-HeroSmall.png">
                        <source media="(min-width: 768px)" srcset="/static/img/Formlets-Hero.png">
                        <img src="/static/img/Formlets-Hero.png" alt="">
                    </picture>
                </div>
            </div>
        </div>
    </div>

</section>

<section id="Content-Container">
    <div class="trusted-by">
        <div class="container">
            <div class="row">
                <div class="my-auto col-lg-3 trusted-title">
                    <h2>Trusted By</h2>
                    <span></span>
                </div>
                <div class="col-lg-9 trusted-logos">
                    <ul>
                        <li><a href=""><img src="/static/img/Allstate-Logo2.png" alt=""></a></li>
                        <li><a href=""><img src="/static/img/BritishGas-Logo.png" alt=""></a></li>
                        <li><a href=""><img src="/static/img/Swedbank-Logo.png" alt=""></a></li>
                        <li><a href=""><img src="/static/img/MalaysiaAir-Logo.png" alt=""></a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <div class="parallax">
        <div class="parallax-overlay"></div>
        <div class="container">
            <div class="row">
                <div class="col-lg my-auto parallax-text">
                    <h3>Looking for a specific feature? <a href="/features/">Check our feature page.</a></h3>
                    <h3>Do you have a question</h3>
                    <span class="parallax-text"><a href="https://www.formlets.com/forms/571d42690acd41d175f57137/">Post us a message</a> or send a mail to hello@formlets.com</span>
                    <div class="cta-container">
                        <a href="/try/" class="btn btn1 btn-lg">Start now with a free form</a>
                        <span>No need to signup to try</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
<?php
$this->OutputMarketingFooter2();
}
//

function OutputPrivacy(){
	$m = "privacy";
  $this->OutputMarketingHeader(array("title"=>"Privacy Policy","descr"=>"We take your privacy seriously. Privacy Policy Formlets Privacy Policy How and Why We Collect and Use Information Cookies Child Protection Links to Th"));
?>

<section id="Content-Container">
      <div class="privacy">
        <div class="container">
          <div class="row sub-innerH">
              <img src="<?php echo $GLOBALS['level'];?>static/img/head.svg" style="display:inline">
            <h2>We take your privacy seriously</h2>
          </div>
          <div class="row">
            <div class="col-lg-4">
              <ul class="side-nav">
                <li class="sidenav-title">Privacy Policy</li>
                <li><a href="#FormletsPrivacy">Formlets Privacy Policy</a></li>
                <li><a href="#HowandWhy">How and Why We Collect and Use Information</a></li>
                <li><a href="#Cookies">Cookies</a></li>
                <li><a href="#ChildProtection">Child Protection</a></li>
                <li><a href="#ThirdPartySites">Links to Third Party Sites</a></li>
                <li><a href="#DataSecurity">Our Commitment to Data Security</a></li>
                <li><a href="#SaleChangeofControl">Sale or Change of Control</a></li>
                <li><a href="#HowContactUs">How to Contact Us</a></li>
              </ul>
            </div>
            <div class="col-lg-8">
                <div id="FormletsPrivacy">
                  <h2>Formlets Privacy Policy</h2>
                  <p>
                    Formlets (“we” or “us”) created this privacy policy to give you confidence as you visit and use the Formlets.com website (the “Site”),
                    and to demonstrate our commitment to fair information practices and the protection of privacy. We reserve the right, at any time, to add to,
                    change, update, or modify this privacy policy, simply by posting such change, update, or modification on the Site and without any other notice to you.
                    Any such change, update, or modification will be effective immediately upon posting on the Site. It is your responsibility to review this privacy policy
                    from time to time to ensure that you continue to agree with all of its terms.
                  </p>
                </div>
                <div id="HowandWhy">
                  <h2>How and Why We Collect and Use Information</h2>
                  <p>
                    Our servers automatically recognize visitors’ domain names and IP addresses (the number assigned to computers on the Internet).
                    No personal information about you is revealed in this process. The Site may also gather anonymous “traffic data” that does not personally identify you,
                    but that may be helpful for marketing purposes or for improving the services we offer. We may use anonymous information to analyze Site traffic,
                    but we do not examine this information for individually identifying information. In addition, we may use anonymous IP addresses to help diagnose problems with our server,
                    to administer the Site, or to display the content according to your preferences. Traffic and transaction information may also be shared with business partners
                    and advertisers on an aggregate and anonymous basis.
                  </p>
                </div>
                <div id="Cookies">
                  <h2>Cookies</h2>
                  <p>
                    We may use cookies to improve your experience using the Site. These cookies do not generally allow us to personally identify a specific user.
                  </p>
                </div>
                <div id="ChildProtection">
                  <h2>Child Protection</h2>
                  <p>
                    We do not offer services to, or target, persons under the age of 13. In compliance with the Children's Online Privacy Protection Act,
                    we will purge any information we receive from people we believe to be children under 13 from our database and cancel the corresponding accounts.
                  </p>
                </div>
                <div id="ThirdPartySites">
                  <h2>Links to Third Party Sites</h2>
                  <p>
                    If you follow any of the links you find on this Site, our privacy policy does not extend to third party sites.
                  </p>
                </div>
                <div id="DataSecurity">
                  <h2>Our Commitment to Data Security</h2>
                  <p>
                    To prevent unauthorized access and maintain data accuracy, we use industry standard methods to safeguard and secure the information we collect online,
                    although you recognize that data transmitted online or stored in a facility to which online access is provided cannot be made to be 100% secure.
                    We believe in protecting your information just as we would want our information protected.
                  </p>
                  <p>
                    In the event that personal information is compromised as a result of a breach of security,
                    we will promptly notify those persons whose personal information has been compromised as may be required by applicable law.
                  </p>
                </div>
                <div id="SaleChangeofControl">
                  <h2>Sale or Change of Control</h2>
                  <p>
                    If Formlets or substantially all of its assets are acquired, your information will be one of the assets transferred to the acquirer.
                  </p>
                </div>
                <div id="HowContactUs">
                  <h2>How to Contact Us</h2>
                  <p>
                    if you have any questions about our Privacy Policy, email us at <a href="">hello@formlets.com</a>
                  </p>
                </div>
            </div>
          </div>
        </div>
      </div>
    </section>
<?php
$this->OutputMarketingFooter2();
}
//


//
function Outputterms(){
	$m = "terms";
  $this->OutputMarketingHeader(array("title"=>$this->pl->trans($m,'Terms and Conditions'),"descr"=>"Terms of Service Account Terms Payment and Refund Terms Cancellation and Termination Modifications to the Service and Prices Copyright and Content Ow"));
?>
<section id="Content-Container">
      <div class="terms">
        <div class="container">
          <div class="row">
            <div class="col-lg-4 side-nav">
              <ul class="side-nav">
                <li class="sidenav-title">Terms of Service</li>
                <li><a href="#AccountTerms">Account Terms</a></li>
                <li><a href="#PaymentRefund">Payment and Refund Terms</a></li>
                <li><a href="#Cancellation">Cancellation and Termination</a></li>
                <li><a href="#Modifications">Modifications to the Service and Prices</a></li>
                <li><a href="#Copyright">Copyright and Content Ownership</a></li>
                <li><a href="#GeneralConditions">General Conditions</a></li>
                <li><a href="#HowContactUs">How to Contact Us</a></li>
              </ul>
            </div>
            <div class="col-lg-8">
                <p>
                  By using the formlets.com website(“Service”) you are agreeing to be bound by the following terms and conditions (“Terms of Service”).
                </p>
                <p>
                  Formlets reserves the right to update and change the Terms of Service from time to time without notice. Any new features that augment or enhance the current Service,
                  including the release of new tools and resources, shall be subject to the Terms of Service.
                  Continued use of the Service after any such changes shall constitute your consent to such changes.
                </p>
                <p>
                  Violation of any of the terms below will result in the termination of your Account. While Formlets prohibits such conduct and Content on the Service,
                  you understand and agree that Formlets cannot be responsible for the Content posted on the Service and you nonetheless may be exposed to such materials.
                  You agree to use the Service at your own risk.
                </p>
                <div id="AccountTerms">
                  <h2>Account Terms</h2>
                  <ul class="terms-list">
                    <li>You must be 16 years or older to use this Service.</li>
                    <li>You must be a human. Accounts registered by “bots” or other automated methods are not permitted.</li>
                    <li>You are responsible for maintaining the security of your account.
                      Formlets cannot and will not be liable for any loss or damage from your failure to comply with this security obligation.</li>
                    <li>You may not use the Service for any illegal or unauthorized purpose. You must not, in the use of the Service,
                      violate any laws in your jurisdiction (including but not limited to copyright laws).</li>
                  </ul>
                </div>
                <div id="PaymentRefund">
                  <h2>Payment and Refund Terms</h2>
                  <ul class="terms-list">
                    <li>All paid plans must enter a valid credit card. Free accounts are not required to provide credit card details.</li>
                    <li>The Service is billed in advance on a monthly basis and is non-refundable. There will be no refunds or credits for partial months of service, upgrade/downgrade refunds,
                      or refunds for months unused with an open account. In order to treat everyone equally, no exceptions will be made.</li>
                    <li>All fees are exclusive of all taxes, levies, or duties imposed by taxing authorities,
                      and you shall be responsible for payment of all such taxes, levies, or duties, excluding only United States (federal or state) taxes.</li>
                  </ul>
                </div>
                <div id="Cancellation">
                  <h2>Cancellation and Termination</h2>
                  <ul class="terms-list">
                    <li>You are solely responsible for properly canceling service. An email or phone request to cancel your account is not considered cancellation.</li>
                    <li>If you cancel Service before the end of your current paid up month, your cancellation will take effect immediately and you will not be charged again.</li>
                    <li>Formlets, in its sole discretion, has the right to suspend or terminate your account and refuse any and all current or future use of the Service,
                      or any other Formlets service, for any reason at any time. Such termination of the Service will result in the deactivation or deletion of your Account or your access to your Account.
                      Formlets reserves the right to refuse service to anyone for any reason at any time.</li>
                  </ul>
                </div>
                <div id="Modifications">
                  <h2>Modifications to the Service and Prices</h2>
                  <ul class="terms-list">
                    <li>Formlets reserves the right at any time and from time to time to modify or discontinue, temporarily or permanently, the Service (or any part thereof) with or without notice.</li>
                    <li>Prices of all Services, including but not limited to monthly subscription plan fees to the Service, are subject to change upon 30 days notice from us.
                      Such notice may be provided at any time by sending a notice to the primary email address specified in your Formlets account or by placing a prominent notice on our site.</li>
                    <li>Formlets shall not be liable to you or to any third party for any modification, price change, suspension or discontinuance of the Service.</li>
                  </ul>
                </div>
                <div id="Copyright">
                  <h2>Copyright and Content Ownership</h2>
                  <ul class="terms-list">
                    <li>We claim no intellectual property rights over the material you provide to the Service.</li>
                    <li>The look and feel of the Service is © 2016 Formlets. All rights reserved. The name and logos for Formlets are property of Formlets, Inc. All rights reserved.
                      You may not duplicate, copy, or reuse any portion of the HTML/CSS, Javascript, or visual design elements or concepts without express written permission from Formlets.
                      You may not use the Formlets name with express written permission from Formlets.</li>
                  </ul>
                </div>
                <div id="GeneralConditions">
                  <h2>General Conditions</h2>
                  <ul class="terms-list">
                    <li>Your use of the Service is at your sole risk. The service is provided on an “as is” and “as available” basis.</li>
                    <li>You understand that Formlets uses third party vendors and hosting partners to provide the necessary hardware,
                      software, networking, storage, and related technology required to run the Service.</li>
                    <li>You must not modify, adapt or hack the Service or modify another website so as to falsely imply that it is associated with the Service, Formlets, or any other Formlets service.</li>
                    <li>Verbal, physical, written or other abuse (including threats of abuse or retribution) of any Formlets customer, employee, member, or officer will result in immediate account termination.</li>
                    <li>Formlets does not warrant that (i) the service will meet your specific requirements, (ii) the service will be uninterrupted, timely, secure, or error-free,
                      (iii) the results that may be obtained from the use of the service will be accurate or reliable, (iv) the quality of any products, services, information,
                      or other material purchased or obtained by you through the service will meet your expectations, and (v) any errors in the Service will be corrected.</li>
                    <li>You expressly understand and agree that Formlets shall not be liable for any direct, indirect, incidental, special, consequential or exemplary damages, including but not limited to,
                      damages for loss of profits, goodwill, use, data or other intangible losses (even if Formlets has been advised of the possibility of such damages),
                      resulting from: (i) the use or the inability to use the service; (ii) the cost of procurement of substitute goods and services resulting from any goods, data, information or services
                      purchased or obtained or messages received or transactions entered into through or from the service; (iii) unauthorized access to or alteration of your transmissions or data;
                      (iv) statements or conduct of any third party on the service; (v) or any other matter relating to the service.</li>
                    <li>The failure of Formlets to exercise or enforce any right or provision of the Terms of Service shall not constitute a waiver of such right or provision.
                      The Terms of Service constitutes the entire agreement between you and Formlets and govern your use of the Service, superceding any prior agreements between you and Formlets
                      (including, but not limited to, any prior versions of the Terms of Service).</li>
                  </ul>
                </div>
                <div id="HowContactUs">
                  <h2>How to Contact Us</h2>
                  <ul class="terms-list">
                    <li>
                    Formlets is run by <br>
                      Oxopia NV<br>
                      Korsele 18 bus 2<br>
                      9667 Horebeke<br>
                      Belgium<br>
                      VATnr BE0889696965<br>
                      <br>

                    if you have any questions about our Terms of Services, email us at <a href="mailto:hello@formlets.com">hello@formlets.com</a>
</li>
                  </ul>
                </div>
            </div>
          </div>
        </div>
      </div>

    </section>
<?php
$this->OutputMarketingFooter2();
}
//

function _PasswordValidationScript() {
?>
<script>
	function checkForm(form) {
		var error = false;
		var errors = "";
    if (form.password.value != "") {
        if (form.password.value.length < 8) {
            errors += "<li>Your password must be at least eight characters long</li>";
            error = true;
        }
        re = /[0-9]/;
        if (!re.test(form.password.value)) {
            errors += "<li>Your password must contain at least one number (0-9)</li>";
            error = true;
        }
        re = /[a-z]/;
        if (!re.test(form.password.value)) {
            errors += "<li>Your password must contain at least one lowercase letter (a-z)</li>";
            error = true;
        }
        re = /[A-Z]/;
        if (!re.test(form.password.value)) {
            errors += "<li>Your password must contain at least one uppercase letter (A-Z)</li>";
            error = true;
        }
        re = /[-!@#$%^&*()_+|~=`{}\[\]:";'<>?,.\/]/;
        if (!re.test(form.password.value)) {
            errors += "<li>Your password must contain at least one special character ([-!@#$%^&*()_+|~=`{})</li>";
            error = true;
        }

	    } else {
	        error = false;
	    }

	    if(error) {
	    	//form.password.focus();
	    	document.getElementById('errors').innerHTML = errors;
            document.getElementById('errors').style.display = "block";
	    } else {
	    	document.getElementById('errors').innerHTML = "";
	    	document.getElementById('errors').style.display = "none";
	    }

	    return !error;
	}
</script>
<?php
}

//
function OutputSignup(){
	$m = "signup";
  $this->OutputMarketingHeader(array("title"=>"Signup","descr"=>""));
 ?>

 <section id="Content-Container">
  <div class="signup-login">
    <div class="container">
      <div class="row justify-content-md-center">
        <div class="col-md-8 signup">
          <h3><?php if(($_GET['ref']=='Free')||(!$_GET['ref'])){ echo "Start now with a free form";} else {echo "Signup";}?></h3>
        <?php if(isset($_GET['red'])) { ?>
      		<form action="<?php echo $GLOBALS['level'];?>signup/?red=<?php echo $_GET['red']; ?>" method="POST" id="signup-form" onsubmit="return checkForm(this);">
      	<?php } else { ?>
      		<form action="<?php echo $GLOBALS['level'];?>signup/?ref=<?php echo $_GET['ref']; ?>" method="POST" id="signup-form" onsubmit="return checkForm(this);">
      	<?php } ?>

        <?php if($this->errorMessage){?>
		<div class="error alert alert-danger">
			<span class="help"><?php echo $this->errorMessage;?></span>
		</div>
	    <?php } ?>

            <div class="row">
              <div class="form-group col-md-6">
                <label for="FirstName"><?php echo $this->pl->trans($m,'First name'); ?></label>
                <input type="text" class="form-control" placeholder="<?php echo $this->pl->trans($m,'First name'); ?>" id="FirstName" name="firstname" validate-required>
              </div>
              <div class="form-group col-md-6">
                  <label><?php echo $this->pl->trans($m,'Last name'); ?></label>
  				<input class="form-control" autocomplete="off" placeholder="<?php echo $this->pl->trans($m,'Last name'); ?>"  validate-required type="text" name="lastname" value="">
              </div>
            </div>

            <div class="form-group">
              <label><?php echo $this->pl->trans($m,'Email Address'); ?></label>
              <input class="form-control" placeholder="<?php echo $this->pl->trans($m,'This will be used to login to Formlets'); ?>" validate-email validate-required type="email" name="email" value="<?php echo $_GET['email'];?>">
            </div>
            <div class="form-group">
              <label><?php echo $this->pl->trans($m,'Set Password'); ?></label>
              <input class="form-control" id="pwd" onkeyup="checkForm(this.form)" onchange="checkForm(this.form)" onblur="checkForm(this.form)" placeholder="<?php echo $this->pl->trans($m,'This will be used to login to Formlets'); ?>" validate-password validate-required type="password" name="password" value="">
            </div>
            <div class="form-group">
    			<ul id="errors" class="alert alert-danger"></ul>
    		</div>
            <button type="Submit" class="btn btn1">Sign me up</button>
            <small id="NoCC" class="form-text text-muted">No credit card required.</small>
            <span class="agree-terms-privacy">By clicking the button above, you agree to our <a href="/terms/">Terms of Services</a> and <a href="/privacy/">Privacy Policy</a></span>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php
$this->_PasswordValidationScript();
$this->OutputMarketingFooter2();
}
//


//
function Output404(){
    header($_SERVER["SERVER_PROTOCOL"]." 404 Not Found");
    $this->OutputMarketingHeader(array("title"=>"404 Page not found","descr"=>""));
?>
<section id="Content-Container">
    <div class="signup-login">
        <div class="container">
            <div class="row text-center">
                <div class="col-12" style="margin-bottom:40px;"><span style="font-size:75px">😞</span></div>
                <div class="col-12"><h2>The link you clicked may be broken<br> or the page may have been removed.</h2></div>

                <div class="col-12">visit the <a href="/">homepage</a> or <a href="https://www.formlets.com/forms/571d42690acd41d175f57137/">contact us</a> about the problem.</div>

            </div>
            <br><br><br><br><br><br><br><br><br><br><br><br>
        </div>
    </div>
</section>
<?php
$this->OutputMarketingFooter2();
}

function OutputUsageLimit(){
    $this->OutputMarketingHeader(array("title"=>"Usage Limit","descr"=>""));
?>
<section id="Content-Container">
    <div class="signup-login">
        <div class="container">
            <div class="row text-center">
                <div class="col-12" style="margin-bottom:40px;"><span style="font-size:75px">😞</span></div>
                <div class="col-12"><h2>This form has reached the limits of the allocated resources,<br>the owner is advised to upgrade.</h2></div>

            </div>
            <br><br><br><br><br><br><br><br><br><br><br><br>
        </div>
    </div>
</section>
<?php
$this->OutputMarketingFooter2();
}
//

//
function OutputTable($data,$title,$type=''){
  echo "<h3>".$title."</h3><table>";
  for ($c=0;$c<count($data);$c++){
  if($c==0){
    echo "<tr>";
    foreach($data[$c] as $i => $value){
      echo "<th>".Ucfirst(strtolower($i))."</th>";
    }
    echo "</tr>";
  }
  echo "<tr>";
  		$ctr=0;
      foreach($data[$c] as $i => $value){
      	if($type=='phishing' && $ctr==0) {
            $fid = $value;
      		echo '<td><a href="/admin/users/?type=formId&keyword='.$value.'">'.stripslashes($value).'</a></td>';
      	} else {
      		echo "<td>".stripslashes($value)."</td>";
      	}
      	$ctr++;
    }

    if($type=='phishing') {
        echo '<td><a href="/forms/'.$fid.'/" target="_blank">Public Form</a></td>';
    }
    echo "</tr>";
  }
  echo "</table>";

}
//


function outputEditFormTemplates() {
	$data = $this->lo->getFormTemplates(array('id' => $this->urlpart[3]))[0];
?>
	<div class="support_head">
        <div style="float:left">
            <a class="back_button" href="<?php echo $GLOBALS['level'];?>admin/templates/"> Back To List </a> &nbsp;
            <a class="delete_button" onclick="return confirm('Are you sure you want to delete the user and all his data?');" href="<?php echo $this->pl->set_csrfguard($GLOBALS['level'].'admin/templates/'.$data['_id'].'/delete/','deleteuser')?>" title="Delete">Delete</a>
        </div>
    </div>
    <div class="support_info">
        <h2>Edit Template</h2>
        <div class="form-div">
            <form accept-charset="UTF-8" enctype="multipart/form-data" action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST">
              <input name="publish" type="checkbox" value="1" <?php echo $data['published'] ? 'checked':'';?>/> Publish
              <label>Form</label><br>
                <a href="/forms/<?php echo $data['sourceform']; ?>/" target="_blank"><?php echo $data['sourceform']; ?></a>
              <br><br>
              <label>Name <span class="required">*</span></label>
                <input placeholder="Enter Name" type="text" name="name" value="<?php echo $data['name'];?>" required>
              <br><br>
              <label>Description <span class="required">*</span></label>
                <textarea class="textarea" name="description"><?php echo $data['description'];?></textarea>
                <br/><br/>
              <!--   <label>Image 1</label>
                <input name="image" type="file" /> -->
                <label class="label" for="image">Image</label>
                <input name="img1" type="file" />
                <?php if ($data['img1']){?>
                  <input name="hidden_img1" type="hidden" value="<?php echo $data['img1'];?>"/>
                  <input name="remove_img1" type="checkbox" value="true"/> Remove image 1
                  <img src="/supportimg/<?php echo $data['img1'];?>"><?php } ?>
                <br><br>
              <label>Image 2</label>
              <?php if ($data['img2']){?>
                  <input name="hidden_img2" type="hidden" value="<?php echo $data['img2'];?>"/>
                  <input name="remove_img2" type="checkbox" value="true"/> Remove image 2
                <img src="/supportimg/<?php echo $data['img2'];?>"><?php } ?>
              <input name="img2" type="file" />
              <br><br>
              <label>Image 3</label>
              <?php if ($data['img3']){?>
                  <input name="hidden_img3" type="hidden" value="<?php echo $data['img3'];?>"/>
                  <input name="remove_img3" type="checkbox" value="true"/> Remove image 3
                <img src="/supportimg/<?php echo $data['img3'];?>"><?php } ?>
              <input name="img3" type="file" />
              <br><br>
                <input type="submit" class="save_button" name="submit_templates_form" value="Save"> &nbsp;
                <a class="cancel_button" href="<?php echo $GLOBALS['level']. 'admin/templates/';?>"> Cancel </a>
            </form>
        </div>
     </div>
<?php
}

function OutputFormtemplates() {
	$m = "formtemplates";
	if($this->lUser) {
		$this->InsideHeader();

        $this->InsideCardWrapperOpen();
	}
?>
<?php if(!$this->lUser) {
    $this->OutputMarketingHeader(array("title"=>'Form Templates',"descr"=>""));
?>

<?php } ?>
<?php if(!$this->urlpart[2]) { ?>
	<script src="//code.jquery.com/jquery-3.2.1.min.js" integrity="sha256-hwg4gsxgFZhOsEEamdOYGBf13FyQuiTwlAQgxVSNgt4=" crossorigin="anonymous"></script>
	<script type="text/javascript">
		jQuery(document).ready(function ($) {

			$('.slider').each(function() {
				var slideCount = $(this).find('ul li').length;
				var slideWidth = $(this).find('ul li').width();
				var slideHeight = $(this).find('ul li').height();
				var sliderUlWidth = slideCount * slideWidth;

				$(this).css({ width: slideWidth, height: slideHeight });

				if(slideCount > 1) {
					$(this).find('ul').css({ width: sliderUlWidth, marginLeft: - slideWidth });
				} else {
					$(this).find('a.control_prev').hide();
					$(this).find('a.control_next').hide();
				}

				$(this).find('ul li:last-child').prependTo($(this).find('ul'));

				$(this).find('a.control_prev').click(function () {
			        var slider = $(this).closest('.slider');
			        slider.find('ul').animate({
			            left: + slideWidth
			        }, 200, function () {
			            slider.find('ul li:last-child').prependTo(slider.find('ul'));
			            slider.find('ul').css('left', '');
			        });
			    });

			    $(this).find('a.control_next').click(function () {
			        var slider = $(this).closest('.slider');
			        slider.find('ul').animate({
			            left: - slideWidth
			        }, 200, function () {
			            slider.find('ul li:first-child').appendTo(slider.find('ul'));
			            slider.find('ul').css('left', '');
			        });
			    });
			});
		    //$('.slider ul li:last-child').prependTo('.slider ul');



		});

	</script>
	<section id="pricing" class="gc gr flush align-center pricing-container well-dark">
		<div class="gc large-12 xl-8 centered pad-double">
			<div class="gc g12 pad price-header">
		  		<span><?php echo $this->pl->trans($m,'Formlets built in form')." <b>".$this->pl->trans($m,'Templates'); ?></b>.</span>
			</div>
				<div class="gc medium-6 large-3 small-12 pad">
				  	<div class="template-item" style="position:relative">
              			<strong><?php echo $this->pl->trans($m,'Empty Canvas'); ?></strong>
              			<br /><br /><br />
				    	<?php echo $this->pl->trans($m,'Don\'t use a template and make a form by dragging the elements you need'); ?>
				    	<div class="actions">
				    		<?php if(!$this->lUser) { ?>
				    			<a href="<?php echo $GLOBALS['level'];?>signup/?red=/editor/<?php echo $this->pl->insertId();?>/source/blank/" class="create_form" style=""><?php echo $this->pl->trans($m,'Create Form'); ?></a>
				    		<?php } else { ?>
				    			<a href="<?php echo $GLOBALS['level'];?>editor/<?php echo $this->pl->insertId();?>/source/blank/" class="create_form" style=""><?php echo $this->pl->trans($m,'Create Form'); ?></a>
				    		<?php } ?>
				    	</div>

					</div>
				</div>
			<?php
			$templates = $this->lo->getFormTemplates(array('published'=>1));
			foreach($templates as $template) {
			?>
				<div class="gc medium-6 large-3 small-12 pad">
				  	<div class="template-item" style="position:relative">
              			<strong><?php echo ucfirst($template['name']); ?></strong>
				    	<div class="slider">
						  	<a href="javascript:;" class="control_next">></a>
						  	<a href="javascript:;" class="control_prev"><</a>
						  	<ul>
						  		<?php if(!$template['img1'] && !$template['img2'] && !$template['img3']) { ?>
						  			<li><a href="/formtemplates/<?php echo $template['_id']; ?>/" class="details"><img src="/static/img/twitter.png" /></a></li>
						  		<?php } else { ?>
						  			<?php if($template['img1']) { ?>
						  				<li><a href="/formtemplates/<?php echo $template['_id']; ?>/" class="details"><img src="/supportimg/<?php echo $template['img1']; ?>/" /></a></li>
						  			<?php } ?>
						  			<?php if($template['img2']) { ?>
						  				<li><img src="/supportimg/<?php echo $template['img2']; ?>" /></li>
						  			<?php } ?>
						  			<?php if($template['img3']) { ?>
						  				<li><img src="/supportimg/<?php echo $template['img3']; ?>" /></li>
						  			<?php } ?>
						  		<?php } ?>
						  	</ul>
						</div>
				    	<div class="actions">
				    		<?php if(!$this->lUser) { ?>
				    			<a href="<?php echo $GLOBALS['level'];?>signup/?red=/editor/<?php echo $this->pl->insertId();?>/source/<?php echo $template['sourceform']; ?>/" class="create_form" style=""><?php echo $this->pl->trans($m,'Create Form'); ?></a>
				    		<?php } else { ?>
				    			<a href="<?php echo $GLOBALS['level'];?>editor/<?php echo $this->pl->insertId();?>/source/<?php echo $template['sourceform']; ?>/" class="create_form" style=""><?php echo $this->pl->trans($m,'Create Form'); ?></a>
				    		<?php } ?>
				    		<a href="/formtemplates/<?php echo $template['_id']; ?>" class="details"><?php echo $this->pl->trans($m,'Details'); ?></a>
				    	</div>

					</div>
				</div>
			<?php
			}
			?>
		</div>
	</section>
<?php } else {
	$template = $this->lo->getFormTemplates(array('id'=>$this->urlpart[2]));
?>
	<section id="pricing" class="gc gr flush align-center pricing-container well-dark" style="position: relative;">
		<div class="backtogallery"><a href="/formtemplates/"><?php echo $this->pl->trans($m,'Back to template gallery'); ?></a></div>
		<div class="gc large-12 xl-8 centered pad-double" style="margin-top: 50px;">
			<div class="gc g4 pad">
				<div class="template-info">
			  		<span><h2><?php echo $template[0]['name']; ?></h2></span>
			  		<p><?php echo $template[0]['description']; ?></p>
		  		</div>
		  		<div style="text-align: left;margin-top: 20px">
		  		<?php if(!$this->lUser) { ?>
		  			<a href="<?php echo $GLOBALS['level'];?>signup/?red=/editor/<?php echo $this->pl->insertId();?>/source/<?php echo $template[0]['sourceform']; ?>/" class="button create_form button-large" style=""><?php echo $this->pl->trans($m,'Use this template'); ?></a>
		  		<?php } else { ?>
		  			<a href="<?php echo $GLOBALS['level'];?>editor/<?php echo $this->pl->insertId();?>/source/<?php echo $template[0]['sourceform']; ?>/" class="button create_form button-large" style=""><?php echo $this->pl->trans($m,'Use this template'); ?></a>
		  		<?php } ?>
				</div>
			</div>

			<div class="gc g8 align-center" style="margin-bottom: 50px;margin-top: 15px">
				<div class="preview">
          <?php echo $this->pl->trans($m,'Demo form'); ?>
					<script type="text/javascript" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js/iframeResizer.min.js"></script>
					<iframe id="formlets-iframe" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $template[0]['sourceform']; ?>/?iframe=true" frameborder="0" width="100%"></iframe>
					<script type="text/javascript" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js/iframe.js"></script>
				</div>
			</div>
		</div>
	</section>
<?php } ?>
<?php
	if(!$this->lUser) {
		$this->OutputMarketingFooter2();
	} else {
        $this->InsideCardWrapperClose();
    }
}

//
function Outputf(){
  $this->Outputforms();
}
//

function search_in_array($array, $key, $value)
{
    $results = array();

    if (is_array($array)) {
        if (isset($array[$key]) && ($array[$key] == $value || $array[$key] == '' || !isset($array[$key]))) {
            $results[] = $array;
        }

        foreach ($array as $subarray) {
            $results = array_merge($results, $this->search_in_array($subarray, $key, $value));
        }
    }

    return $results;
}

function OutputGenerateuploadurl() {
    echo $this->_generateUploadUrl();
}

function _generateUploadUrl() {
    $options = array(
        'gs_bucket_name' => $GLOBALS['conf']['gs_bucket_name'],
        'url_expiry_time_seconds' => 14400 // 4 hours
    );
    $url = $GLOBALS['protocol'].'://'.$_SERVER['HTTP_HOST'].'/fileaccept.php';
    if($GLOBALS['conf']['env'] == 'production') {
        $upload_url = CloudStorageTools::createUploadUrl($url, $options);
    } else {
        $upload_url = $url;
    }

return json_encode(array('url'=>$upload_url));
}

//
function Outputforms(){
	if($_GET['payment'] && $_POST) {
		exit;
	}
	$m = "forms";
  // lets lookup the data before the processor
$form=$this->form;

$upload_url="";
// if($this->pl->formHasElement($form, 'FILE') && $GLOBALS['conf']['env'] == 'production') {
//     $uploadurl = $this->_generateUploadUrl();
//     $upload_url = json_decode($uploadurl, true)['url'];
// }

if(isset($this->form['error'])) {
	$this->Output404();exit;
}

$user = $this->user;
$form_owner = $user[0];
if(!$form_owner) {
    //$this->Output404();exit;
}

$submisssion = array();

if($this->urlpart[3]) {
	$submission = $this->lo->getSubmissions(array('id'=>$this->urlpart[3], 'related_form_id'=>$form['_id'], 'owner'=>$form_owner));
}

$is_print = false;
if($_GET['response'] && $_GET['action'] == 'print') {
	$submission = $this->lo->getSubmissions(array('id'=>$_GET['response']));
	$is_print = true;
}

if($form_owner['blocked']) {
    $this->Output404();exit;
}

$this->fowner = $form_owner;
$formcssid = 'css'.substr($form['_id'], 0, 8);
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo stripslashes($form['name']);?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta name="description" content="<?php echo $form['description'] ? stripslashes($form['description']): $GLOBALS['ref']['meta']['description']; ?>">
	<meta name="twitter:card" content="summary" />
	<meta name="twitter:site" content="@formlets" />
	<meta name="twitter:title" content="<?php echo stripslashes($form['name']);?>" />
	<meta name="twitter:description" content="<?php echo $form['description'] ? stripslashes($form['description']): $GLOBALS['ref']['meta']['description']; ?>" />
	<meta name="twitter:url" content="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $form['_id']; ?>/" />
	<meta property="og:type"   content="website" />
	<meta property="og:title" content="<?php echo $form['name'];?>" />
	<meta property="og:description" content="<?php echo $form['description'] ? strip_tags($this->correct_label($form['description'])): $GLOBALS['ref']['meta']['description']; ?>" />
	<meta property="og:url" content="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $form['_id']; ?>/" />
	<link rel="icon" type="image/x-icon" href="<?php echo $GLOBALS['level'];?>static/img/favicon.ico" />
    <script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/formlets.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
    <style>
    html, body {
        -webkit-font-smoothing: antialiased;
        -moz-osx-font-smoothing: grayscale;
        color: #333;
        font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
        font-weight: 500;
        font-size: .95rem;
        line-height: 1.25em;
    }
    </style>
    <script>
    var upload_url = '<?php echo $upload_url; ?>';
    </script>
    <?php if($this->pl->formHasElement($form, array('DATE','DATETIME','TIME'))) { ?>
		<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['level'];?>static/css/datepicker/flatpickr.min.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
	<?php } ?>
    <?php if($this->pl->formHasElement($form, 'LOOKUP')) { ?>
        <link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['level'];?>static/css/autocomplete.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
        <script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/autocomplete.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
    <?php } ?>
	<?php if($this->pl->formHasElement($form, 'CAPTCHA')) { ?>
		<script src="https://www.google.com/recaptcha/api.js"></script>
	<?php } ?>
	<?php if($this->pl->formHasElement($form, 'SIGNATURE')) { ?>
		<script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/signature_pad.min.js"></script>
	<?php } ?>
	<?php if($form['customCSS'] == 0) { ?>
	  	<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['level'];?>static/css/form.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
	    <?php if($_GET['action'] == 'print') { ?>
	    <link rel="stylesheet" type="text/css" media="print" href="<?php echo $GLOBALS['level'];?>static/css/formprint.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
	    <?php  } ?>
	    <link rel="stylesheet" href="<?php echo $GLOBALS['level'];?>static/css/font-awesome.min.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
	    <?php if($_GET['iframe'] == 'true') { ?>
	      <script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/iframeResizer.contentWindowV2.min.js"></script>
		<?php  } ?>
	    <?php if($this->lUser){ ?>
	   		<!-- <script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/zepto.js"></script> -->
		<?php } ?>
	    <?php if($GLOBALS['sess']['error_message'] || $GLOBALS['sess']['success_message']){ ?>
	    <script>
	    	$(document).ready(function(){
		     	$(".alert-container").find('span.close').on("click", function(){
		     		$(".alert-container").removeClass("error");
		            $(".alert-container").removeClass("success");
		            $(".alert-container").html("");
		            $(".alert-container").hide();
		     	});
	    	});
	    </script>
		<?php } ?>

		<?php

		if($form['themeEnabled']){
		  		if($form['themeID']) {
					$tid = $form['themeID'];
					$theme = null;
					$uid = $form_owner['_id'];
					$theme = $this->lo->getTheme(array("id"=>$tid));
					if(count($theme)) {
						$theme = $theme[0];
					} else {
						$theme = $form;
					}
				} else {
					$theme = $form;
				}
			if($theme['themeFont']) {
		?>
				<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=<?php echo $theme['themeFont']; ?>">
		<?php
			}
		}
		?>

		<style>
		body {
			line-height: 1.25;
		}
		a {
		    color: inherit;
		    text-decoration: none
		}
		<?php if($this->lUser) { ?>
			.alert-container {
			    position: absolute;
			    right:20px;
			    bottom:20px;
			    padding:15px;
			    z-index: 9999;
			}
			.alert-container span.close {
			    position: absolute;
			    top: -8px;
			    left: -8px;
			    font-size: 20px;
			    z-index: 2000;
			    cursor: pointer;
			}
			.alert-container.error {
			    background: #ffe4db;
			    color: #d1603d;
			}
			.alert-container.success {
			    background: #f3fed4;
			    color: #9cc12f;
			}
		<?php } ?>
		#<?php echo $formcssid; ?> input[readonly=true], textarea[readonly], select[readonly] {
			background: #f0f0f2;
			background-color: rgb(240,240,242);
		}
  		<?php if($theme['themeBrowserBackground']) { ?>
			body,
			.fcc,
			#<?php echo $formcssid; ?>,
			[class~="stage"] {
				background: <?php echo $theme['themeBrowserBackground'];?> !important;
			}
		<?php } ?>
		<?php if($theme['themeFormBackground'] || $theme['themeFormBorder'] || $theme['themeFont']) { ?>
			#<?php echo $formcssid; ?> .fc {
				<?php if($theme['themeFormBackground']) { ?>
				background: <?php echo $theme['themeFormBackground'];?> !important;
				<?php } ?>
				<?php if($theme['themeFormBorder']) { ?>
				border-color: <?php echo $theme['themeFormBorder'];?> !important;
				<?php } ?>
				<?php if($theme['themeFont']) { ?>
				font-family: <?php echo $theme['themeFont'];?> !important;
				<?php } ?>
			}
		<?php } ?>
		#<?php echo $formcssid; ?> .fc button:not([fm-button~="inline-edit"]),
		#<?php echo $formcssid; ?> .fc input[type="submit"],
		#<?php echo $formcssid; ?> .fc button.button,
		#<?php echo $formcssid; ?> .switch input[type="checkbox"]:checked+.switch-container {
			<?php if($theme['themeSubmitButton']) { ?>
			background-color: <?php echo $theme['themeSubmitButton'];?> !important;
			<?php } ?>
			<?php if(strlen($theme['themeSubmitButtonText']) == '7'){ ?>
				color: <?php echo $theme['themeSubmitButtonText'];?> !important;
			<?php } else { ?>
				color: #ffffff !important;
			<?php } ?>
		}
		<?php if($theme['themeFieldBackground'] || $theme['themeFieldBorder'] || $theme['themeFont']) { ?>
			#<?php echo $formcssid; ?> .fc .text,
			#<?php echo $formcssid; ?> .fc canvas.signature-pad {
				<?php if($theme['themeFieldBackground']) { ?>
				background: <?php echo $theme['themeFieldBackground'];?> !important;
				<?php } ?>
				<?php if($theme['themeFieldBorder']) { ?>
				border-color: <?php echo $theme['themeFieldBorder'];?> !important;
				box-shadow: 0 0 0 1px <?php echo $theme['themeFieldBorder'];?> !important;
				<?php } ?>
				<?php if($theme['themeFont']) { ?>
				font-family: <?php echo $theme['themeFont'];?> !important;
				<?php } ?>
			}
		<?php } ?>
		<?php if($theme['themeFieldHover']) { ?>
			#<?php echo $formcssid; ?> .fc .text:hover {
				border-color: <?php echo $theme['themeFieldHover'];?> !important;
				box-shadow: 0 0 0 1px <?php echo $theme['themeFieldHover'];?> !important;
			}
		<?php } ?>
		<?php if($theme['themeFieldActive']) { ?>
			#<?php echo $formcssid; ?> .fc .text:focus {
				border-color: <?php echo $theme['themeFieldActive'];?> !important;
				box-shadow: 0 0 0 1px <?php echo $theme['themeFieldActive'];?> !important;
			}
		<?php } ?>
		<?php if($theme['themeFieldBorder']) { ?>
			#<?php echo $formcssid; ?> .fc input + i {
				border-color: <?php echo $theme['themeFieldBorder'];?> !important;
			}
			#<?php echo $formcssid; ?> fieldset[type="INPUTTABLE"] input + i {
				border-color: #d6d7d6 !important;
			}
		<?php } ?>
		<?php if($theme['themeFieldHover']) { ?>
			#<?php echo $formcssid; ?> .fc input + i:hover {
				border-color: <?php echo $theme['themeFieldHover'];?> !important;
			}
		<?php } ?>
		<?php if($theme['themeFieldActive']) { ?>
			#<?php echo $formcssid; ?> .fc input:focus + i {
				border-color: <?php echo $theme['themeFieldActive'];?> !important;
			}
		<?php } ?>
		#<?php echo $formcssid; ?> .fc input + i:after {
			-webkit-box-shadow: none !important;
			box-shadow: none !important;
		}
		<?php if($theme['themeFieldSelected']) { ?>
			#<?php echo $formcssid; ?> .fc input:checked + i {
				background: <?php echo $theme['themeFieldSelected'];?> !important;
				border-color: <?php echo $theme['themeFieldSelected'];?> !important;
			}
		<?php } ?>
		#<?php echo $formcssid; ?> .fc input + i:after {
			text-shadow: none !important;
		}
		<?php if($theme['themeFont'] || $theme['themeText'] || $theme['themeDescriptionText'] || $theme['themeFieldText']) { ?>
			<?php if($theme['themeFont']) { ?>
				#<?php echo $formcssid; ?> .fc .input-group-help {
					background: inherit;
					font-family: <?php echo $theme['themeFont'];?> !important;
				}
				#<?php echo $formcssid; ?> .fc .help {
					background: inherit;
					font-family: <?php echo $theme['themeFont'];?> !important;
				}
			<?php } ?>
			<?php if($theme['themeDescriptionText']) { ?>
				#<?php echo $formcssid; ?> #description {
					color: <?php echo $theme['themeDescriptionText'];?> !important;
				}
			<?php } ?>
			#<?php echo $formcssid; ?> label,
			#<?php echo $formcssid; ?> h1,#<?php echo $formcssid; ?> h2,#<?php echo $formcssid; ?> h3,#<?php echo $formcssid; ?> h4,#<?php echo $formcssid; ?> h5,
			#<?php echo $formcssid; ?> p:not(.help){
				<?php if($theme['themeText']) { ?>
				color: <?php echo $theme['themeText'];?> !important;
				<?php } ?>
				<?php if($theme['themeFont']) { ?>
				font-family: <?php echo $theme['themeFont'];?> !important;
				<?php } ?>
			}
			#<?php echo $formcssid; ?> .fc input, #<?php echo $formcssid; ?> .fc textarea, #<?php echo $formcssid; ?> .fc select {
				<?php if($theme['themeFieldText']) { ?>
				color: <?php echo $theme['themeFieldText'];?> !important;
				<?php } ?>
				<?php if($theme['themeFont']) { ?>
				font-family: <?php echo $theme['themeFont'];?> !important;
				<?php } ?>
			}
			<?php if($theme['themeFieldText']) { ?>
				#<?php echo $formcssid; ?> *::-webkit-input-placeholder {
				    color: <?php echo $theme['themeFieldText'];?> !important;
				    opacity:.7 !important;
				}
				#<?php echo $formcssid; ?> *:-moz-placeholder {
				    /* FF 4-18 */
				    color: <?php echo $theme['themeFieldText'];?> !important;
				    opacity:.7 !important;
				}
				#<?php echo $formcssid; ?> *::-moz-placeholder {
				    /* FF 19+ */
				    color: <?php echo $theme['themeFieldText'];?> !important;
				    opacity:.7 !important;
				}
				#<?php echo $formcssid; ?> *:-ms-input-placeholder {
				    /* IE 10+ */
				    color: <?php echo $theme['themeFieldText'];?> !important;
				    opacity:.7 !important;
				}
				#<?php echo $formcssid; ?> .icon-left>i::after {
				    border-right: 1px solid <?php echo $theme['themeFieldText'];?> !important;
				}
			<?php } ?>
			#<?php echo $formcssid; ?> .fc i.fa {
				<?php if($theme['themeText']) { ?>
				color: <?php echo $theme['themeText'];?> !important;
				<?php } ?>
			}
			#<?php echo $formcssid; ?> .fcc h1, #<?php echo $formcssid; ?> h2 {
				<?php if($theme['themeText']) { ?>
				color: <?php echo $theme['themeText'];?> !important;
				<?php } ?>
				<?php if($theme['themeFont']) { ?>
				font-family: <?php echo $theme['themeFont'];?> !important;
				<?php } ?>
			}
			#<?php echo $formcssid; ?> .fcc hr {
				<?php if($theme['themeText']) { ?>
				color: <?php echo $theme['themeText'];?> !important;
				<?php } ?>
				<?php if($theme['themeFont']) { ?>
				font-family: <?php echo $theme['themeFont'];?> !important;
				<?php } ?>
			}
		<?php } ?>
		<?php if($theme['themeFieldSelected'] || $theme['themeFieldActive']) { ?>
			<?php if($theme['themeFieldSelected']) { ?>
				#<?php echo $formcssid; ?> .fcc input[type="range"]::-webkit-slider-thumb {
					background: <?php echo $theme['themeFieldSelected'];?>;
				}
				#<?php echo $formcssid; ?> .fcc input[type="range"]::-moz-range-thumb {
					background: <?php echo $theme['themeFieldSelected'];?>;
				}
				#<?php echo $formcssid; ?> .fcc input[type="range"]::-ms-thumb {
					background: <?php echo $theme['themeFieldSelected'];?>;
				}
			<?php } ?>
			<?php if($theme['themeFieldActive']) { ?>
				#<?php echo $formcssid; ?> .fcc input[type="range"]::-webkit-slider-thumb:active {
					-webkit-box-shadow: 0 0 0 4px white, 0 0 0 6px <?php echo $theme['themeFieldActive'];?>;
					box-shadow: 0 0 0 4px white, 0 0 0 6px <?php echo $theme['themeFieldActive'];?>;
				}
			<?php } ?>
			#<?php echo $formcssid; ?> .fcc input[type="range"]::-moz-range-thumb:active {
				<?php if($theme['themeFieldSelected']) { ?>
				background: <?php echo $theme['themeFieldSelected'];?>;
				<?php } ?>
				<?php if($theme['themeFieldActive']) { ?>
				-webkit-box-shadow: 0 0 0 4px white, 0 0 0 6px <?php echo $theme['themeFieldActive'];?>;
				box-shadow: 0 0 0 4px white, 0 0 0 6px <?php echo $theme['themeFieldActive'];?>;
				<?php } ?>
			}
			#<?php echo $formcssid; ?> .fcc input[type="range"]::-ms-thumb:active {
				<?php if($theme['themeFieldSelected']) { ?>
				background: <?php echo $theme['themeFieldSelected'];?>;
				<?php } ?>
				<?php if($theme['themeFieldActive']) { ?>
				-webkit-box-shadow: 0 0 0 4px white, 0 0 0 6px <?php echo $theme['themeFieldActive'];?>;
				box-shadow: 0 0 0 4px white, 0 0 0 6px <?php echo $theme['themeFieldActive'];?>;
				<?php } ?>
			}
		<?php } ?>
		<?php if($theme['themeFieldError']) { ?>
			#<?php echo $formcssid; ?> .req-error select,
			#<?php echo $formcssid; ?> .req-error input,
			#<?php echo $formcssid; ?> .req-error textarea,
			#<?php echo $formcssid; ?> .req-error input:focus,
			#<?php echo $formcssid; ?> .req-error input:active,
			#<?php echo $formcssid; ?> .req-error input:hover,
			#<?php echo $formcssid; ?> .req-error textarea:hover {
				border-color: <?php echo $theme['themeFieldError'];?> !important;
				box-shadow: 0 0 0 1px <?php echo $theme['themeFieldError'];?> !important;
			}
			#<?php echo $formcssid; ?> .error .input-group-help,
			#<?php echo $formcssid; ?> .req-error .input-group-help,
			#<?php echo $formcssid; ?> .error label,
			#<?php echo $formcssid; ?> .req-error label,
			#<?php echo $formcssid; ?> .req-error label i,
			#<?php echo $formcssid; ?> .help.field-error {
				color: <?php echo $theme['themeFieldError'];?> !important;
			}
		<?php
		}
		echo $theme['themeCSS'] ?: '';
		?>

		#<?php echo $formcssid; ?> .icon-left>i::after {
		    padding-right: 0px;
		}
		#<?php echo $formcssid; ?> .fc [disabled] {
			background: #f0f0f2;
			border-color: #f0f0f2;
		}

		#<?php echo $formcssid; ?> .fc label[disabled] {
			background: inherit;
		}

		</style>
	<?php } ?>
  <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-60128463-3', 'auto');
  ga('send', 'pageview');
</script>
</head>
<?php
$dir='';
if($form['rtl'] == '1') {
    $dir = 'rtl';
}
?>
<body dir="<?php echo $dir; ?>" <?php if(!$_GET['iframe']): ?> class="link" <?php else: ?> style="overflow-x:hidden; padding: 16px;" <?php endif; ?>>
		<?php if(!$_GET['iframe']) { ?>
		<div class="flush centered">

		<?php
		if($GLOBALS['sess']['error_message']){
			echo '<div class="alert-container error"><div class="content">'.$GLOBALS['sess']['error_message'].'</div><span class="close"><i class="fa fa-times-circle"></i></span></div>';
            $this->pl->save_session('error_message', '');
		}
		if($GLOBALS['sess']['success_message']){
			echo '<div class="alert-container success"><div class="content">'.$GLOBALS['sess']['success_message'].'</div><span class="close"><i class="fa fa-times-circle"></i></span></div>';
            $this->pl->save_session('success_message', '');
		}
		?>

		<?php } ?>
		<?php
  		$owner = $form['owner'];
  		if((!$this->lUser && $form['active']<>'1') || ($this->lUser && $owner<>$form['owner'] && $form['active']<>'1')){
  		?><div class="fcc centered" style="text-align: center;">
	  			<img src="<?php echo $GLOBALS['level']."static/img/form-icon-error.png";?>" alt="Formlets" />
	  			<br /><br />
	  			<h1><?php echo $form['name']; ?></h1>
	  			<h3 class="help"><?php echo $form['inactiveMessage'] ?: 'Form is not active.' ?></h3>
	  		</div><?php
  		} else {
  			if($form['active']<>'1' && !$is_print){
  		?><div class="fcc centered" style="font-family: 'Source Sans Pro' !important;text-align: center;background: #f8f2c6 !important;margin:0px;padding:10px;color:#000 !important;">
  					<h3 style="font-family: 'Source Sans Pro' !important;color:#000 !important"><?php echo $this->pl->trans($m,'You can see this form because you are logged in, to allow other people to view it'); ?> <a href="<?php echo $this->pl->set_csrfguard('/form/activate/'.$form['_id'].'/','activateform');?>"><?php echo $this->pl->trans($m,'publish this form'); ?></a> </h3>
  				</div>
  		<?php
  			}
  		?>
  			<?php if(!$_GET['iframe']) { ?>
  				<div id="<?php echo $formcssid; ?>" class="gc fcc deployed centered small-12 medium-8 large-6 clearfix">
  			<?php } else { ?>
  				<div id="<?php echo $formcssid; ?>" class="fcc iframe deployed">
  			<?php } ?>
	            <?php
                if($form['logo']){
                    if(!is_object($form['logo']) && json_decode($form['logo'])) {
                        $form['logo'] = json_decode($form['logo']);
                    }
                ?>
	            	<?php if(is_object($form['logo'])){ ?>
	                	<div class="fc-logo"><img src="https://s3.amazonaws.com/<?php echo $form['logo']->bucket;?>/<?php echo $form['logo']->key;?>"></div>
	            	<?php } else { ?>
	            		<div class="fc-logo"><img src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/logo/<?php echo $form['logo']; ?>"></div>
	            	<?php } ?>
	            <?php }?>
	            <?php
	            if ($form['displayHeader'] && $this->usePassword==false){
	            ?>
				<h1><?php
				echo stripslashes($form['name']); if ($form['description']){?><span id="description" class="help"><?php echo $this->correct_label($form['description']);?></span><?php
	          	}
	     		?></h1>
	     		<?php
		     	}

		     	if($_GET['response'] && $_GET['action'] == 'print') {

                    $dateformat = $this->pl->getUserDateFormat($form_owner);
                    $timeformat = $this->pl->getUserTimeFormat($form_owner);

                    $submittedAt = date($dateformat . ' ' . $timeformat, strtotime($submission[0]['dateCreated']));

                    echo 'Submission Date: <strong>'.$submittedAt.'</strong>';
                ?>
                	<a href="javascript:;" class="print_page" title="Print this form"><i class="fa fa-print" aria-hidden="true"></i></a>
                <?php
		     	}
		     	?>

		     	<?php if(!$_GET['iframe']) { ?>
				<div class="fc gc pad">
				<?php } else { ?>
				<div class="fc gc pad" style="padding:0;border:0;box-shadow: none;">
				<?php } ?>
					<?php if ($this->display=="success"){?>
						<div class="gr flush">
							<div class="gc g12 pad-double">
                                <?php
                                foreach($_POST as $name=>$post) {
                                    $val = !is_array($post) ? htmlentities($post):implode(',',$post);
                                    echo '<input type="hidden" name="'.$name.'" value="'.$val.'" />';
                                }
                                $elements = $this->search_in_array($form['elements'], 'page', 'success');
                                if(count($elements)) {
                                    $lw = 0;
        							foreach($elements as $element) {
        								if($lw >= 12 || $lw == 0) {
        								?>
        									<div class="gr flush">
        										<div class="gc pad-compact">
        								<?php
        									$lw=0;
        								}
        								//if($element["type"]<>'NAME' && $element["type"]<>'US_ADDRESS') {
        				  				?>
        									<div <?php if($element["type"]=='NAME' || $element["type"]=='US_ADDRESS') echo 'style="padding:0px"'; ?> <?php if($_GET['iframe']) { echo 'style=""'; } ?> class="gc pad-half medium-<?php echo $element['size'] < 3 && $element['size'] > 2 ? '2':$element['size'];  if($element["type"]=='SECTION'){?> section-break-container<?php } ?>">
        								<?php
        								//}

        								$addAttr = '';
        								if($element['type']=='PRODUCTS') {
        									$unit = 'currency';
        									if(!empty($element['unit'])) {
        										$unit = $element['unit'];
        									}
        									$addAttr='data-unit="'.$unit.'"';
        								}

                                        $logic='';

                                        if(($this->pl->isFreeAccount($this->fowner) == false && $element['enableLogic']) || ($form['active']<>'1' && $element['enableLogic'])) {
                                            if(!isset($element['logicAction'])) {
                                                $element['logicAction'] = 'show';
                                            }
                                            if(isset($element['logicAction'])) {
                                                $andor = $element['conditionAndOr']?:'ANY';
                                                $cdn = '';
                                                if($element['conditions']) {
                                                    $conditions = json_decode($element['conditions'], true);
                                                    $cdn.='[';
                                                    $ctr=1;
                                                    foreach($conditions as $condition) {
                                                        $val = str_replace("\"", ";;dq;;", $condition['value']);
                                                        if($condition['if'] && $condition['state']) {
                                                            $cdn.='{';
                                                                $cdn.='`if`:`'.$condition['if'].'`,';
                                                                $cdn.='`state`:`'.$condition['state'].'`,';
                                                                $cdn.='`value`:`'.$val.'`';
                                                            $cdn.='}';
                                                            if($ctr < count($conditions)) {
                                                                $cdn.=',';
                                                            }
                                                        }
                                                        $ctr++;
                                                    }
                                                    $cdn.=']';
                                                } else {
                                                    $cdn = 'null';
                                                }

                                                $valE = str_replace("\"", ";;dq;;", $element['logicValue']);
        										$logic.='{';
        										$logic.='`action`:`'.$element['logicAction'].'`,';
        										$logic.='`field`:`'.$element['logicField'].'`,';
        										$logic.='`condition`:`'.$element['logicCondition'].'`,';
        										$logic.='`value`:`'.$valE.'`,';
        										$logic.='`andor`:`'.$andor.'`,';
        										$logic.='`conditions`:'.$cdn;
        										$logic.='}';
        									}
        									$addAttr.=' data-logic="'.$logic.'"';
        								}

        								$calculation='';
        								if(($element['calculationTotal'] && $element['fieldLists'])){
                                            $addAttr.=' data-calc-fields='.json_encode($element['fieldLists']);
        								}
        								?>
        									<fieldset <?php echo $addAttr; ?> type="<?php echo $element["type"]; ?>" id="<?php echo $element['_id'];?>" <?php if($this->req_error[$element['_id']]){?> class="req-error"<?php } ?>>
        									    <?php if(($this->pl->isFreeAccount($this->fowner) == false && $element['calculationTotal'] && $element['fieldLists']) || ($form['active']<>'1' && $element['calculationTotal'])) { ?>
        									    	<input type="hidden" class="totalHidden" name="total" />
        									    <?php } ?>
        									    <?php
                                                if($element['type'] == 'LABEL' || $element['type'] == 'SECTION') {
                                                    echo $this->{"elForm".$element["type"]}($element, $submission, $this->submittedData, $this->fowner);
                                                } else {
                                                    echo $this->{"elForm".$element["type"]}($element, $submission, $this->fowner);
                                                }


        									 if($this->req_error[$element['_id']]){ ?>
        									    	<div class="help" class="req-error"><?php echo $this->pl->trans($m,'This field is required'); ?>.</div>
        									   <?php } ?>
        									</fieldset>
        								<?php //if(($element["type"]<>'NAME')&&($element["type"]<>'US_ADDRESS')) { ?>
        									</div>
        								<?php //}

        								$lw = $lw + $element['size'];
        								if($lw >= 12 || $lw == 0) {
        								?>
        										</div>
        									</div>
        								<?php
        								}
        							}
                                } else {
                                    echo '<div class="align-center">';
                                ?>
    								<label>
                                    <?php
    								if($this->form['submitSuccessMessage']) {
                                        echo nl2br(stripcslashes($this->form['submitSuccessMessage']));
    								} else {
    								?>
                                    <?php echo $this->pl->trans($m,'thank you for your submission'); ?>
                                    <?php
    								}
    								?>
                                    </label>
                                <?php
                                    echo '</div>';
                                }
                                ?>
							</div>
						</div>
					<?php } else if($this->display=="payment"){ ?>
						<div class="gr flush">
							<div class="gc g12 pad-double">
								<?php if($this->payment_element['type'] == 'PAYPAL') { ?>
								<div class="align-center">
								<?php
						  		if(count($this->payment_element['products'])) {
						  		?>
						  		<div class="g12">
						  			<ul>
						  			<?php foreach($this->payment_element['products'] as $products) { ?>
						  				<?php foreach($products as $product) { ?>
						  					<li><?php echo $product; ?></li>
						  				<?php } ?>
						  			<?php } ?>
						  			</ul>
						  		</div>
						  		<?php } ?>
								</div>
								<div class="g12"><p><?php echo $this->payment_element['paymentsPageLabel']; ?></p></div>

								<br />
								<div class="align-center">
									<?php
							  		$amount = $this->amount_pay ?: $this->payment_element['amount'];
                                    $amount = round($amount, 2);
							  		$symbol = '$';
							  		if($form['currency'] != 'USD') {$symbol='';}
							  		?>
									<div class="g12 gray-background payment-total"><strong><?php echo $this->payment_element['totalLabel']; ?>: <?php echo $symbol.$amount . " " . $form['currency']; ?></strong></div>
									<br />
                                    <?php if(isset($GLOBALS['conf']['paypal_sandbox_mode']) && $GLOBALS['conf']['paypal_sandbox_mode'] == true) { ?>
                                        <form action="https://www.sandbox.paypal.com/cgi-bin/webscr" method="post" id="payment-form">
                                    <?php } else { ?>
                                        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" id="payment-form">
                                    <?php } ?>

									  	<input type="hidden" name="cmd" value="_xclick">
                                        <input type="hidden" name="notify_url" value="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $form['_id'] ?>/?payment=notify&submission_id=<?php echo $this->sToSess['submission_id']; ?>&paid_field_id=<?php echo $this->sToSess['paid_field_id']; ?>" />
									  	<input type="hidden" name="return" value="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $form['_id'] ?>/?payment=success&submission_id=<?php echo $this->sToSess['submission_id']; ?>&paid_field_id=<?php echo $this->sToSess['paid_field_id']; ?>&paypal=true">
									  	<input type="hidden" name="cancel_return" value="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $form['_id'] ?>/?payment=cancel">
									  	<input type="hidden" name="business" value="<?php echo $this->payment_element['email'] ?>">
									  	<input type="hidden" name="item_name" value="<?php echo $form['name'] ?>">
									  	<input type="hidden" name="item_number" value="<?php echo $form['_id'] ?>">
									  	<input type="hidden" name="currency_code" value="<?php echo $form['currency'] ?>">
									  	<input type="hidden" name="amount" value="<?php echo $this->amount_pay ?: $this->payment_element['amount'] ?>">
    									<button type="submit" id="paypal-button"><?php echo $this->payment_element['buttonLabel'] ?></button>
									</form>
                                    <script>
                                    <?php
                                    $plabel = $this->payment_element['paymentProcessButtonLabel'];
                                    if(!$plabel) { $plabel = 'Processing...'; }
                                    ?>

                                    var process_label = '<?php echo htmlentities($plabel); ?>';

                                    var form = document.getElementById('payment-form');
                                    form.addEventListener('submit', function(event) {
                                      var button = document.getElementById('paypal-button');
                                      button.innerHTML = process_label;
                                      button.setAttribute("disabled", "disabled");
                                    });
                                    </script>
								</div>
                                <?php } else if($this->payment_element['type'] == 'STRIPE' || array_key_exists($this->payment_element['type'], $GLOBALS['ref']['STRIPE_ADDITIONAL_METHODS'])) { ?>
									<style>
									/**
									 * The CSS shown here will not be introduced in the Quickstart guide, but shows
									 * how you can use CSS to style your Element's container.
									 */
									.StripeElement {
									  background-color: white;
									  padding: 8px 12px;
									  border-radius: 4px;
									  border: 1px solid transparent;
									  box-shadow: 0 1px 3px 0 #e6ebf1;
									  -webkit-transition: box-shadow 150ms ease;
									  transition: box-shadow 150ms ease;
									}

									.StripeElement--focus {
									  box-shadow: 0 1px 3px 0 #cfd7df;
									}

									.field {
										color: #32325d;
									    line-height: 24px;
									    font-family: "Helvetica Neue", Helvetica, sans-serif;
									    -webkit-font-smoothing: antialiased;
									    font-size: 16px;
									}

									.field::placeholder{
										color: #aab7c4
									}

									.StripeElement--invalid {
									  border-color: #fa755a;
									}

									.StripeElement--webkit-autofill {
									  background-color: #fefde5 !important;
									}
									#payment-form .gc{
										margin: 10px 0px;
									}

                                    #card-errors {
                                        color:#fa755a;
                                    }
									</style>
									<script src="https://js.stripe.com/v3/"></script>

									<form action="/charge" method="post" id="payment-form">
										<div class="align-center">
										<?php
								  		if(count($this->payment_element['products'])) {
								  		?>
								  		<div class="g12">
								  			<ul>
								  			<?php foreach($this->payment_element['products'] as $products) { ?>
								  				<?php foreach($products as $product) { ?>
								  					<li><?php echo $product; ?></li>
								  				<?php } ?>
								  			<?php } ?>
								  			</ul>
								  		</div>
								  		<?php } ?>
										</div>
										<div class="gc medium-12">
								  			<p><?php echo $this->payment_element['paymentsPageLabel']; ?></p>
								  		</div>
                                        <?php if($this->payment_element['captureCard'] && ($this->payment_element['stripeType'] == 'card' || !$this->payment_element['stripeType'])) { } else { ?>
									  	<div class="align-center">
									  		<div class="gc medium-12 gray-background payment-total">
									  		<?php
									  		$amount = $this->amount_pay ?: $this->payment_element['amount'];
                                            $amount = round($amount, 2);
									  		$symbol = '$';
									  		if($form['currency'] != 'USD') {$symbol='';}
									  		?>
									  			<strong><?php echo $this->payment_element['totalLabel']; ?>: <?php echo $symbol.$amount . " " . $form['currency']; ?></strong>
									  		</div>
								  		</div>
                                        <?php } ?>
                                        <?php if($this->payment_element['type'] == 'ideal') { ?>
                                            <div id="card-errors" role="alert"></div>
                                        <?php } else if($this->payment_element['type'] == 'alipay') { ?>
                                            <div id="card-errors" role="alert"></div>
                                        <?php } else if($this->payment_element['type'] == 'ach_credit_transfer') { ?>
                                            <div id="card-errors" role="alert"></div>
                                        <?php } else if($this->payment_element['type'] == 'bancontact') { ?>
                                            <div class="gc medium-12">
                                                <label>
                                                    <span>Name</span>
                                                    <input name="cardholder-name" class="field StripeElement" placeholder="Jane Doe" />
                                                </label>
                                            </div>
                                            <div id="card-errors" role="alert"></div>
                                        <?php } else if($this->payment_element['type'] == 'eps') { ?>
                                            <div class="gc medium-12">
                                                <label>
                                                    <span>Name</span>
                                                    <input name="cardholder-name" class="field StripeElement" placeholder="Jane Doe" />
                                                </label>
                                            </div>
                                            <div id="card-errors" role="alert"></div>
                                        <?php } else if($this->payment_element['type'] == 'giropay') { ?>
                                            <div class="gc medium-12">
                                                <label>
                                                    <span>Name</span>
                                                    <input name="cardholder-name" class="field StripeElement" placeholder="Jane Doe" />
                                                </label>
                                            </div>
                                            <div id="card-errors" role="alert"></div>
                                        <?php } else if($this->payment_element['type'] == 'multibanco') { ?>
                                            <div id="card-errors" role="alert"></div>
                                        <?php } else if($this->payment_element['type'] == 'p24') { ?>
                                            <div id="card-errors" role="alert"></div>
                                        <?php } else if($this->payment_element['type'] == 'sepa_debit') { ?>
                                            <div id="card-errors" role="alert"></div>
                                        <?php } else if($this->payment_element['type'] == 'sofort') { ?>
                                            <div id="card-errors" role="alert"></div>
                                        <?php } else { ?>
                                            <div class="form-row">
    									  		<div class="gc medium-12">
    									  			<label>
    											      	<span><?php echo $this->payment_element['cardNameLabel']; ?></span>
    											      	<input name="cardholder-name" class="field StripeElement" placeholder="Jane Doe" />
    											    </label>
    									  		</div>
    									  		<div class="gc medium-12">
    									  			<label>
    											      	<span><?php echo $this->payment_element['cardNumberLabel']; ?></span>
    											      	<div id="card-element">
    												      	<!-- a Stripe Element will be inserted here. -->
    												    </div>
    											    </label>
    									  		</div>
                                                <?php if((isset($this->payment_element['securityCode']) && $this->payment_element['securityCode']) || !isset($this->payment_element['securityCode'])) { ?>
        									  		<div class="gc medium-6">
        									  			<label>
        											      	<span><?php echo $this->payment_element['expiryDateLabel']; ?></span>
        											      	<div id="expiry-element">
        												      	<!-- a Stripe Element will be inserted here. -->
        												    </div>
        											    </label>
        									  		</div>
        									  		<div class="gc medium-6">
        									  			<label>
        											      	<span><?php echo $this->payment_element['securityCodeLabel']; ?></span>
        											      	<div id="code-element">
        												      	<!-- a Stripe Element will be inserted here. -->
        												    </div>
        											    </label>
        									  		</div>
                                                <?php } else { ?>
                                                    <div class="gc medium-12">
        									  			<label>
        											      	<span><?php echo $this->payment_element['expiryDateLabel']; ?></span>
        											      	<div id="expiry-element">
        												      	<!-- a Stripe Element will be inserted here. -->
        												    </div>
        											    </label>
        									  		</div>
                                                <?php } ?>
    									  		<?php if($this->payment_element['postCode']) { ?>
    										  		<div class="gc medium-12">
    										  			<label>
    												      	<span><?php echo $this->payment_element['postCodeLabel']; ?></span>
    												      	<div id="zip-element">
    													      	<!-- a Stripe Element will be inserted here. -->
    													    </div>
    												    </label>
    										  		</div>
    									  		<?php } ?>
    									  		<div class="gc medium-12">
    										    	<!-- Used to display form errors -->
    										    	<div id="card-errors" role="alert"></div>
    									    	</div>
    									  	</div>
                                        <?php } ?>


									  	<br /><br />
									  	<div class="align-center">
									  		<button id="stripe-button"><?php echo $this->payment_element['buttonLabel'] ?></button>
									  	</div>
									</form>

									<script type="text/javascript">
										// Create a Stripe client
										var stripe = Stripe('<?php echo $this->payment_element['public_key'] ?>');

                                        <?php
                                        $plabel = $this->payment_element['paymentProcessButtonLabel'];
                                        if(!$plabel) { $plabel = 'Processing...'; }
                                        ?>

                                        var process_label = '<?php echo htmlentities($plabel); ?>';
                                        var button_label = '<?php echo $this->payment_element['buttonLabel']; ?>';

                                        <?php if(in_array($this->payment_element['type'], ['ideal', 'alipay','ach_credit_transfer','bancontact','eps', 'giropay'])) { ?>

                                            var form = document.getElementById('payment-form');
    										form.addEventListener('submit', function(event) {
    										  event.preventDefault()

                                              var nameField = $(document).find("input[name=cardholder-name]");
                                              var name;
                                              if(nameField.length) {
                                                  name = nameField.val();
                                              }

                                              var button = document.getElementById('stripe-button');
                                              button.innerHTML = process_label;
                                              button.setAttribute("disabled", "disabled");

                                              var opt = {
                                                  type: '<?php echo $this->payment_element['type']; ?>',
                                                  amount: <?php echo $amount*100; ?>,
                                                  currency: '<?php echo strtolower($form['currency']); ?>',
                                                  redirect: {
                                                    return_url: "<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $form['_id'] ?>/?stripe=custom&type=<?php echo $this->payment_element['type']; ?>&amount=<?php echo $amount; ?>&el_id=<?php echo $this->payment_element['_id']; ?>&submission_id=<?php echo $this->sToSess['submission_id']; ?>&paid_field_id=<?php echo $this->sToSess['paid_field_id']; ?>",
                                                  }
                                              };

                                              if($.trim(name)) {
                                                  opt.owner = {
                                                      name: name
                                                  };
                                              }

                                              stripe.createSource(opt).then(function(result) {
                                                  if(result.error){
                                                      $("#card-errors").html(result.error.message);
                                                      button.innerHTML = button_label;
                                                      button.removeAttribute('disabled');
                                                  } else {
                                                      window.location.href=result.source.redirect.url;
                                                  }
                                                  // handle result.error or result.source
                                              });
                                            });
                                        <?php } else if($this->payment_element['type'] == 'giropay') { ?>
                                        <?php } else if($this->payment_element['type'] == 'multibanco') { ?>
                                        <?php } else if($this->payment_element['type'] == 'p24') { ?>
                                        <?php } else if($this->payment_element['type'] == 'sepa_debit') { ?>
                                        <?php } else if($this->payment_element['type'] == 'sofort') { ?>
                                        <?php } else { ?>
                                            // Create an instance of Elements
    										var elements = stripe.elements();

    										// Custom styling can be passed to options when creating an Element.
    										// (Note that this demo uses a wider set of styles than the guide below.)
    										var style = {
    										  base: {
    										    color: '#32325d',
    										    lineHeight: '24px',
    										    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
    										    fontSmoothing: 'antialiased',
    										    fontSize: '16px',
    										    '::placeholder': {
    										      color: '#aab7c4'
    										    }
    										  },
    										  invalid: {
    										    color: '#fa755a',
    										    iconColor: '#fa755a'
    										  }
    										};

    										function stripeTokenHandler(token) {
    											var xmlhttp = new XMLHttpRequest();   // new HttpRequest instance

    											xmlhttp.onreadystatechange = function() {
    											    if (xmlhttp.readyState == XMLHttpRequest.DONE) {
    											        if(xmlhttp.responseText == '{"error":false}') {
    											        	window.location.href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $form['_id'] ?>/?payment=success&submission_id=<?php echo $this->sToSess['submission_id']; ?>&paid_field_id=<?php echo $this->sToSess['paid_field_id']; ?>";
    											        } else {
                                                            var error = JSON.parse(xmlhttp.responseText);
                                                            $("#card-errors").html(error.error_message.message);
                                                            $("#stripe-button").removeAttr('disabled');
                                                        }
    											    }
    											}

    											xmlhttp.open("POST", "<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $form['_id'] ?>/?payment=success&stripe=true&amount=<?php echo $amount; ?>");
    											xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
    											xmlhttp.send("token="+token.id+"&type=stripe&captureCard=<?php echo $this->payment_element['captureCard']; ?>&el_id=<?php echo $this->payment_element['_id']; ?>");
    										}

    										// Create an instance of the card Element
    										var card = elements.create('cardNumber', {style: style});
    										var expiry = elements.create('cardExpiry', {style: style});
                                            <?php if(isset($this->payment_element['securityCode']) && $this->payment_element['securityCode']) { ?>
    										     var code = elements.create('cardCvc', {style: style});
                                            <?php } else if(!isset($this->payment_element['securityCode'])) { ?>
                                                 var code = elements.create('cardCvc', {style: style});
                                            <?php } ?>
    										<?php if($this->payment_element['postCode']) { ?>
    										var zip = elements.create('postalCode', {style: style});
    										<?php } ?>

    										// Add an instance of the card Element into the `card-element` <div>
    										card.mount('#card-element');
    										expiry.mount('#expiry-element');
                                            <?php if((isset($this->payment_element['securityCode']) && $this->payment_element['securityCode']) || !isset($this->payment_element['securityCode'])) { ?>
    										code.mount('#code-element');
                                            <?php } ?>
    										<?php if($this->payment_element['postCode']) { ?>
    										zip.mount('#zip-element');
    										<?php } ?>

    										// Handle real-time validation errors from the card Element.
    										card.addEventListener('change', function(event) {
    										  var displayError = document.getElementById('card-errors');
    										  if (event.error) {
    										    displayError.textContent = event.error.message;
    										  } else {
    										    displayError.textContent = '';
    										  }
    										});

    										// Handle form submission
    										var form = document.getElementById('payment-form');
    										form.addEventListener('submit', function(event) {
    										  event.preventDefault();
    										  var extraDetails = {
    										    name: form.querySelector('input[name=cardholder-name]').value,
    										  };

                                              var button = document.getElementById('stripe-button');
                                              button.innerHTML = process_label;
                                              button.setAttribute("disabled", "disabled");

    										  stripe.createToken(card, extraDetails).then(function(result) {
    										    if (result.error) {
    										      // Inform the user if there was an error
    										      var errorElement = document.getElementById('card-errors');
    										      errorElement.textContent = result.error.message;
                                                  button.removeAttribute("disabled");
    										    } else {
    										      // Send the token to your server

    										      stripeTokenHandler(result.token);
    										    }
    										  });
    										});
                                        <?php } ?>
									</script>
								<?php } ?>
							</div>
						</div>
					<?php } else if($form['type']<>"ENDPOINT"){ ?>

            <?php if($this->usePassword) { ?>
              <form id="form" data-leave-propmt="<?php echo $form['leavePrompt'] ? 'on':'off'; ?>" action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST" autocomplete="<?php echo $form['autoComplete'] ? 'on':'off'; ?>" enctype="multipart/form-data" class="clearfix form-password" novalidate="novalidate" accept-charset="utf-8">
                    <input type="hidden" name="type" value="password" />
                    <div class="gr flush">
                        <div class="gc pad-compact">
                            <div class="gc pad-half medium-12">
                                <fieldset type="TEXT" id="fieldset_password" class="<?php if($_GET['invalid']=='pw') {echo 'req-error';} ?>">
                                    <label><?php echo $this->passwordLabel; ?></label>
                                    <div class="controls-container">
                                        <input class="text " type="password" data-inputmask="" validate-required="" name="password" placeholder="">
                                    </div>
                                    <?php if($_GET['invalid']=='pw') { ?>
                                        <div class="help field-error"><?php echo $this->invalidPassword; ?></div>
                                    <?php } ?>
                                </fieldset>
                            </div>
                        </div>
                    </div>

                    <div class="gr flush">
                        <div class="gc g3 pad-double right" style="width:auto;">
                            <button class="button button-blue" style="float: right; " id="submitButton" type="button">Submit</button>
                        </div>
                        <div class="gc g3 pad-double left">
                        </div>
                        <div class="gc g6 pad-double align-center left">
                        </div>
                    </div>
              </form>
            <?php } else { ?>
					<form id="form" data-leave-prompt="<?php echo $form['leavePrompt'] ? 'on':'off'; ?>" action="<?php echo $_SERVER['REQUEST_URI'];?>" method="POST" autocomplete="<?php echo $form['autoComplete'] ? 'on':'off'; ?>" enctype="multipart/form-data" class="clearfix" novalidate="novalidate" accept-charset="utf-8">
                    <input type="hidden" name="form_id" value="<?php echo $this->urlpart[2] ?>">
                    <?php
                    if($form['trackGeoAndTimezone']) {
                        $location = $this->pl->getUserLocation();
                    ?>
                        <input type="hidden" name="Geo" value="<?php echo $location['country_name']; ?>" />
                        <input type="hidden" name="Timezone" value="<?php echo $location['time_zone']; ?>" />
                    <?php
                    }
                    ?>
					<?php
						$pages = ["page1"];
						if($form['pages'] && is_array($form['pages']) && count($form['pages'])) {
							$pages = [];
							foreach($form['pages'] as $page) {
								if($page) {
									$elements = $this->search_in_array($form['elements'], 'page', $page->_id);
									if(count($elements)) {
										$pages[] = $page->_id;
									}
								}
							}

							if(count($pages) == 0) {
								$pages = ["page1"];
							}
						}

						$pcount = 0;
						foreach($pages as $page) {
							$nextText="Next";
							$prevText="Previous";

							if($form['nextButtonText']){$nextText=$form['nextButtonText'];}
							if($form['previousButtonText']){$prevText=$form['previousButtonText'];}

							if($form['pages'] && is_array($form['pages']) && count($form['pages'])) {
								foreach($form['pages'] as $p) {
									if($page == $p->_id) {
										if($p->nextButtonText){$nextText=$p->nextButtonText;}
										if($p->previousButtonText){$prevText=$p->previousButtonText;}
									}
								}
							}
						?>
							<div data-next="<?php echo $nextText; ?>" data-prev="<?php echo $prevText; ?>" class="page <?php if($pcount > 0 && !$is_print) echo 'hidden'; ?>" id="page_<?php echo $pcount ?>">

						<?php

							$elements = $this->search_in_array($form['elements'], 'page', $page);

							$lw = 0;
							foreach($elements as $element) {
								if($lw >= 12 || $lw == 0) {
								?>
									<div class="gr flush">
										<div class="gc pad-compact">
								<?php
									$lw=0;
								}
								//if($element["type"]<>'NAME' && $element["type"]<>'US_ADDRESS') {
				  				?>
									<div <?php if($element["type"]=='NAME' || $element["type"]=='US_ADDRESS') echo 'style="padding:0px"'; ?> <?php if($_GET['iframe']) { echo 'style=""'; } ?> class="gc <?php if($element['hidden']) {echo 'hidden';} ?> pad-half medium-<?php echo $element['size'] < 3 && $element['size'] > 2 ? '2':$element['size'];  if($element["type"]=='SECTION'){?> section-break-container<?php } ?>">
								<?php
								//}

								$addAttr = '';
								if($element['type']=='PRODUCTS') {
									$unit = 'currency';
									if(!empty($element['unit'])) {
										$unit = $element['unit'];
									}
									$addAttr='data-unit="'.$unit.'"';
								}
								$logic='';
								if(($this->pl->isFreeAccount($this->fowner) == false && $element['enableLogic']) || ($form['active']<>'1' && $element['enableLogic'])) {
                                    if(!isset($element['logicAction'])) {
                                        $element['logicAction'] = 'show';
                                    }
                                    if(isset($element['logicAction'])) {
                                        $andor = $element['conditionAndOr']?:'ANY';
                                        $cdn = '';
                                        if($element['conditions']) {
                                            $conditions = json_decode($element['conditions'], true);
                                            $cdn.='[';
                                            $ctr=1;
                                            foreach($conditions as $condition) {
                                                $val = str_replace("\"", ";;dq;;", $condition['value']);
                                                if($condition['if'] && $condition['state']) {
                                                    $cdn.='{';
                                                        $cdn.='`if`:`'.$condition['if'].'`,';
                                                        $cdn.='`state`:`'.$condition['state'].'`,';
                                                        $cdn.='`value`:`'.$val.'`';
                                                    $cdn.='}';
                                                    if($ctr < count($conditions)) {
                                                        $cdn.=',';
                                                    }
                                                }
                                                $ctr++;
                                            }
                                            $cdn.=']';
                                        } else {
                                            $cdn = 'null';
                                        }

                                        $valE = str_replace("\"", ";;dq;;", $element['logicValue']);
										$logic.='{';
										$logic.='`action`:`'.$element['logicAction'].'`,';
										$logic.='`field`:`'.$element['logicField'].'`,';
										$logic.='`condition`:`'.$element['logicCondition'].'`,';
										$logic.='`value`:`'.$valE.'`,';
										$logic.='`andor`:`'.$andor.'`,';
										$logic.='`conditions`:'.$cdn;
										$logic.='}';
									}
									$addAttr.=' data-logic="'.$logic.'"';
								}
								$calculation='';
								if(($element['calculationTotal'] && $element['fieldLists'])){
									$addAttr.=' data-calc="'.$element['calculationTotal'].'"';
									$addAttr.=' data-calc-fields='.json_encode($element['fieldLists']);
								}

								?>
									<fieldset <?php echo $addAttr; ?> type="<?php echo $element["type"]; ?>" id="<?php echo $element['_id'];?>" <?php if($this->req_error[$element['_id']]){?> class="req-error"<?php } ?>>
									    <?php if(($this->pl->isFreeAccount($this->fowner) == false && $element['calculationTotal'] && $element['fieldLists']) || ($form['active']<>'1' && $element['calculationTotal'])) { ?>
									    	<input type="hidden" class="totalHidden" name="total" />
									    <?php } ?>
									    <?php echo $this->{"elForm".$element["type"]}($element, $submission, $this->fowner);
									 if($this->req_error[$element['_id']]){ ?>
									    	<div class="help" class="req-error"><?php echo $this->pl->trans($m,'This field is required'); ?>.</div>
									   <?php } ?>
									</fieldset>
								<?php //if(($element["type"]<>'NAME')&&($element["type"]<>'US_ADDRESS')) { ?>
									</div>
								<?php //}

                                $lw = $lw + $element['size'];
								
								if($lw >= 12 || $lw == 0) {
								?>
										</div>
									</div>
								<?php
								}
							}
						?>
							</div>
						<?php
							$pcount++;
						}

						if(count($form['elements']) == 0) {
						?>
						<div class="page" id="page_0">
							<label><?php echo $this->pl->trans($m,'This form does not contain any fields yet/anymore'); ?></label>
						</div>
						<?php
						}

					?>
						<script> var pagecount=<?php echo $pcount;?>;</script>
						<?php if($form['elements'] && !$is_print){ ?>
							<div class="gr flush">
								<div class="gc g3 pad-double right" <?php if($pcount==1) {echo 'style="width:auto;"';} ?>>
									<button class="button-blue" type="button" style="float: right; <?php if($pcount==1){?>display: none;<?php } ?>"  onclick="cPage(event,'next', pagecount)" id="nextPageButton"><?php if($form['nextButtonText']){ echo $form['nextButtonText'];} else { echo $this->pl->trans($m,'Next'); } ?></button>
                                    <button class="button button-blue" style="float: right; <?php if($pcount>1){?>display: none;<?php } ?>" id="submitButton" type="button">
                                        <?php if($form['submitButtonText']){ echo $form['submitButtonText'];} else { echo $this->pl->trans($m,'Submit'); } ?>
                                    </button>
								</div>
								<div class="gc g3 pad-double left">
									<button class="button-blue" type="button" id="previousPageButton" style="float: left; display: none;" onclick="cPage(event,'prev', pagecount)"><?php if($form['previousButtonText']){ echo $form['previousButtonText'];} else { echo $this->pl->trans($m,'Previous'); } ?></button>
								</div>
								<div class="gc g6 pad-double align-center left">
									<?php if ($pcount > 1){?>
										<span id="pageNr" style="display:inline-block; margin-top:8px;"><?php echo $form['footerPaginationPageText']; ?> 1 <?php echo $form['footerPaginationOfText'].' '.$pcount; ?></span>
									<?php } ?>
								</div>
							</div>
						<?php
						}
						?>
					</form>
            <?php } ?>
					<?php
					}
					?>
					</div>
			</div>
		<?php
		}
		if(!$_GET['iframe']) {
		?>
		</div>
		<?php
		}
		if ($this->pl->isFreeAccount($form_owner) && ($form['active']=='1' || ($this->lUser && $form['owner']==$owner))){?>
		<footer class="<?php if(!$_GET['iframe']): ?>small-12 medium-8 large-6<?php endif; ?>">
			<p>
				<a target="_top" href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>?f=y"><img src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/img/powered_by.svg" alt="Create an online form"></a>
			</p>
		</footer>
	<?php } ?>
	  	<?php if($form['themeFont']){ ?>
	  		<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.16/webfont.js"></script>
			<script>
			  WebFont.load({
			    google: {
			      families: ['<?php echo $form['themeFont'];?>']
			    }
			  });
			</script>
		<?php } ?>
			<script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/zepto.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
			<script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/mask.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
            <?php if($this->pl->formHasElement($form, array('DATE','DATETIME','TIME'))) { ?>
                <script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/datepicker/strtotime.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
                <script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/datepicker/flatpickr.min.js"></script>
                <?php
                $langs = $this->pl->getDatePickerLanguages($form);
                foreach($langs as $lang) {
                ?>
                    <script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/datepicker/lang/<?php echo $lang; ?>.js"></script>
                <?php
                }
                ?>
            <?php } ?>
            <script type="text/javascript">
			<?php
            $format = $this->pl->getUserDateFormat($form_owner);
			?>

            var date_format = '<?php echo $format ?>';
            var global_required_message = '<?php echo $form['requiredMessage'] ?: 'This field is required'; ?>';

			</script>

            <script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/forms.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>

			<script type="text/javascript">
				$(document).ready(function(){
	               <?php if($_GET['v'] <> '1' && $_GET['nofocus'] <> 'y') { ?>
	              	    $('form').find('input,textarea,select').first().focus();
	               <?php } ?>
				    var maxUploadSize = <?php echo $GLOBALS['ref']['MAX_UPLOAD_SIZE']; ?>;
				    $("fieldset").each(function(){
				    	var f = $(this);
				    	var fileContainer = f.find(".file");
				    	if(fileContainer.length > 0){
				    		var button = fileContainer.find("button");
				    		var input = fileContainer.find('input[type="file"]');
				    		button.on("click", function(e){
				    			e.preventDefault();
                                if(input.is('[large-file]')) {
                                    $.get('/generateuploadurl', function(data) {
                                        obj = JSON.parse(data);
                                        window.upload_url = obj.url;
                                    });
                                }
				    			input.click();
				    		});
				    		input.on("change", function(e){
				    			f.removeClass('req-error');
				    			if(f.find('.help.filehelp').length > 0){
			    					f.find('.help.filehelp').remove();
			    				}

                                var filename = [];
                                for(var x=0;x<e.target.files.length;x++) {
                                    var filesizemb = e.target.files[x].size/1000/1000;
    				    			filename.push(e.target.files[x].name);
                                    var error = false;
    				    			if(filesizemb > maxUploadSize && !$(this).is("[large-file]")){
                                        error=true;
    				    				f.addClass('req-error');
    				    				f.append('<div class="help filehelp">Max file upload is '+maxUploadSize+' MB, you attempted to upload '+filesizemb.toFixed(1)+' MB</div>');
    				    				input.val('');
    				    			} else {
    				    				f.removeClass('req-error');
    				    				if(f.find('.help.filehelp').length > 0){
    				    					f.find('.help.filehelp').remove();
    				    				}
    				    			}
                                }

                                //fileContainer.find(".filename").html(filename.join(', '));
				    		});
				    	}
				    });

				    <?php if($this->pl->formHasElement($form, 'SIGNATURE')) { ?>
						var canvas = $("canvas[class=signature-pad]");
						var default_value = '';
						if(canvas.attr('default')) {
							default_value = canvas.attr('default');
						}
						canvas.each(function() {
							var $this = $(this);
							var fieldset = $this.closest('fieldset');
							var id=fieldset.attr('id');
							var input = fieldset.find('input[name="'+id+'"]');
							var signaturePad = new SignaturePad($(this).get(0), {
                                <?php if($theme['themeFieldBackground']){ ?>
                                backgroundColor: "<?php echo $theme['themeFieldBackground']; ?>"
                                <?php } ?>
                            });

							if(default_value) {
								signaturePad.fromDataURL(default_value);
							}

							signaturePad.onBegin = function() {
								fieldset.find('.actions').show();
							};

							signaturePad.onEnd = function() {
								input.val(signaturePad.toDataURL('image/png'));
							};

                            <?php if($theme['themeFieldText']){ ?>
                            signaturePad.penColor = '<?php echo $theme['themeFieldText']; ?>';
                            <?php } ?>

							var clear = fieldset.find('.actions .clear').get(0);

							clear.addEventListener('click', function (event) {
							  	signaturePad.clear();
							  	input.val('');
							});
						});
					<?php } ?>
				});
			</script>

			<script>
			var page=0;

			function cPage(evt,w, pagecount){
				var i,pb,sb,nb, gp;
				if (w=="next"){
					if(checkValidation('submit')) {
		                $('html, body').scrollTop($(".req-error").offset().top);
						return;
					}
					page=(page+1);
				} else {
					page=(page-1);
				}
				gp = document.getElementsByClassName("page");
				for (i = 0; i < gp.length; i++){
					gp[i].className = "page hidden";
				}
				gp[page].className = "page";
				pb=document.getElementById("previousPageButton");
				sb=document.getElementById("submitButton");
				nb=document.getElementById("nextPageButton");
				pn=document.getElementById("pageNr");
				if(pagecount>0){
					pn.innerHTML="<?php echo $form['footerPaginationPageText']; ?> "+(page+1)+" <?php echo $form['footerPaginationOfText']; ?> " + pagecount;
				}
				if(pagecount>0){
					pb.style.display = "block";
					if(page==pagecount-1){
						sb.style.display = "block";nb.style.display = "none";
					} else if(page<pagecount){
						nb.style.display = "block";sb.style.display = "none";
						if(page==0){
							pb.style.display = "none";
						}
					}
				} else if(page==0){
					pb.style.display = "none";
				}
	            <?php if($_GET['v'] <> '1' && $_GET['nofocus'] <> 'y') { ?>
				$(function(){
					$('.page').not(".hidden").find('input,textarea,select').first().focus();
					$("html, body").scrollTop(0);
				});
	            <?php } ?>

	            var next = $(gp[page]).data('next');
	            var prev = $(gp[page]).data('prev');

	            $("#nextPageButton").html(next);
	            $("#previousPageButton").html(prev);
			}

			$(function() {
	        <?php if(($_GET['v']<>'1') && ($_GET['nofocus']<>'y')) { ?>
				$('.page').not(".hidden").find('input,textarea,select').first().focus();
				$("html, body").scrollTop(0);
	      	<?php } ?>
				$("#submitButton").on("click", function(e) {
					e.preventDefault();
					if(checkValidation('submit') == false && check_captcha(null)) {
						$("#form").submit();
					} else {
						$('html, body').scrollTop($(".req-error").offset().top);
					}
				});

                $("#form").on("change keyup", "input, select", function(e) {
                    if (e.keyCode == 13) {
                        if($("#nextPageButton").css("display")!=='none') {
                            $("#nextPageButton").click();
                        } else {
                            $("#submitButton").click();
                        }
                    }
                });

				$("#form").on("change keyup", "input, textarea, select", function(e) {
					var name = $(this).attr('name');
					if($(this).hasClass('dirty')) {
						checkValidation('change');
					}

					$('[name="'+name+'"]').addClass('dirty');
				});

				var next = $("#page_0").data('next');
				$("#nextPageButton").html(next);
			});
		</script>

		<?php if($_GET['action'] == 'print') { ?>

		<script type="text/javascript">
			$(".print_page").on("click", function() {
				window.print();
			});
		</script>

		<?php } ?>

        <!-- for large upload -->
        <script>

        function asyncUpload(fileselected, file_id, file, ctr) {

            var fileitem_append = $('<li id="f_'+file_id+'_fileitem_'+ctr+'"><span class="filename">'+fileselected.file.name+'</span><span class="fileid"></span><span class="indicator"></span><span class="removeBtn">X</span></li>');
            $(document).find('#'+file_id+'_files_container').append(fileitem_append);

            var fileitem = $(document).find('#f_'+file_id+'_fileitem_'+ctr);

            if(fileselected.error == 'yes') {
                fileitem.addClass("error");
                fileitem.find('.indicator').html(fileselected.message);
                var cancel = fileitem.find('.removeBtn');
                cancel.on("click", function() {
                    fileitem.remove();
                });
            } else {
                var formData = new FormData();

                formData.append('files[]', fileselected.file, removeDiacritics(fileselected.file.name));
                formData.append('file_names[]', fileselected.file.name);

                formData.append('formId', '<?php echo $this->urlpart[2]; ?>');
                var elementId = $(document).find('input#'+file_id).closest('fieldset').attr('id');
                formData.append('elementId', elementId);

                // Set up the request.
                var xhr = new XMLHttpRequest();
                // Set up a handler for when the request finishes.
                xhr.onload = function () {
                  if (xhr.status == 200) {
                    // File(s) uploaded.
                  } else {
                    //alert('An error occurred!');
                  }
                };

                if(xhr.upload) {
                    // progress bar
                    xhr.upload.addEventListener("progress", function(e) {
                        var pc = parseInt((e.loaded / e.total * 100));
                        if(pc) {
                            pc = pc-1;
                        }
                        //$(document).find('#'+file_id+'_indicator').html(file.attr('uploading') + ' ' + pc+'%');
                        fileitem.find('.indicator').html(file.attr('uploading') + ' ' + pc+'%');
                    }, true);
                }

                xhr.onreadystatechange = function() {
                    if (xhr.readyState == XMLHttpRequest.DONE) {
                        if(xhr.responseText) {
                            try {
                                obj = JSON.parse(xhr.responseText);
                                if(obj.error) {
                                } else {
                                    // $(document).find('#'+file_id+'_file').val(obj.file);
                                    // $(document).find('#'+file_id+'_filename').val(obj.filename);
                                    $(document).find('input#'+file_id).val("");
                                    //$(document).find('#'+file_id+'_indicator').html(file.attr('finishupload'));

                                    var input_file = $(document).find('#'+file_id+'_file');
                                    var input_filename = $(document).find('#'+file_id+'_filename');

                                    if(input_file.val()) {
                                        input_file.val(input_file.val()+';;'+obj.file);
                                    } else {
                                        input_file.val(obj.file)
                                    }

                                    if(input_filename.val()) {
                                        input_filename.val(input_filename.val()+';;'+obj.filename);
                                    } else {
                                        input_filename.val(obj.filename)
                                    }

                                    fileitem.find('.fileid').html(obj.file);
                                    fileitem.find('.indicator').html(file.attr('finishupload'));
                                    $(document).find('input#'+file_id).closest('fieldset').find('.help.field-error').html('');
                                }
                            } catch(err) {
                                // $(document).find('#'+file_id+'_file').val();
                                // $(document).find('#'+file_id+'_filename').val();
                                $(document).find('input#'+file_id).val("");
                                //$(document).find('#'+file_id+'_indicator').html('An error occured, please refresh the page.');
                                fileitem.find('.indicator').html('An error occured, please refresh the page.');
                                $(document).find('#'+file_id+'_filename_container').html('');
                                $(document).find('input#'+file_id).closest('fieldset').find('.help.field-error').html('');
                            }
                        }
                    }
                }

                // Open the connection.
                xhr.open('POST', window.upload_url, true);

                xhr.send(formData);

                //console.log(xhr);
                var cancel = fileitem.find('.removeBtn');
                cancel.on("click", function() {
                    xhr.abort();
                    var fileitem = $(this).closest('li');
                    var fileid = fileitem.find('.fileid').html();
                    if(fileid) {
                        var filecontainer = $(this).closest('.file');
                        var hidden_file = filecontainer.find('.hidden_file').val();
                        var hidden_filename = filecontainer.find('.hidden_filename').val();

                        var hidden_file_arr = hidden_file.split(';;');
                        var hidden_filename_arr = hidden_filename.split(';;');


                        var idx = hidden_file_arr.indexOf(fileid);
                        if(idx > -1) {
                            hidden_file_arr.splice(idx, 1);
                            hidden_filename_arr.splice(idx, 1);

                            //delete the file from server
                            $.ajax({
                                type: "GET",
                                url: '/deletefile/'+fileid+'/'
                            });
                        }

                        filecontainer.find('.hidden_file').val(hidden_file_arr.join(';;'));
                        filecontainer.find('.hidden_filename').val(hidden_filename_arr.join(';;'));
                    }
                    fileitem.remove();
                });
            }
        }

        $(document).ready(function() {
            var files = $(document).find('input[large-file]');

            $(document).on("change", "input[large-file]", function() {
                file = $(this);
                var file_id = $(this).attr('id');

                var uploadFileError = 'no';
                var errorMessage = '';

                // var xBtn = $('<span class="removeFile">X</span>');
                // $(document).find('#'+file_id+'_filename_container').append(xBtn);

                var filesselected=[];
                for(var x=0;x<this.files.length;x++) {
                    var f = this.files[x];

                    var fieldset = file.closest('fieldset');

                    var filesizemb = f.size/1000/1000;
                    var filename = f.name;

                    var fileSizeError = file.attr('fileSizeError');
                    var fileDimensionError = file.attr('fileDimensionError');
                    var filetype='all';
                    var fileaccept = file.attr('accept').split('/');
                    if(fileaccept[0] == 'image') {
                        filetype='image';
                    }
                    var minSize = file.attr('minSize') ? file.attr('minSize'):0;
                    var maxSize = file.attr('maxSize') ? file.attr('maxSize'):0;
                    var minHeight = file.attr('minHeight') ? file.attr('minHeight'):0;
                    var maxHeight = file.attr('maxHeight') ? file.attr('maxHeight'):0;
                    var minWidth = file.attr('minWidth') ? file.attr('minWidth'):0;
                    var maxWidth = file.attr('maxWidth') ? file.attr('maxWidth'):0;

                    fieldset.find('.help.field-error').remove();
                    if(filetype=='image') {
                        var file1, img;
                        var _URL = window.URL || window.webkitURL;
                        if ((file1 = f)) {
                            img = new Image();
                            img.onload = function() {
                                var width = this.width;
                                var height = this.height;

                                var errorFileDimension = 0;
                                if(minHeight && parseFloat(minHeight) > height) {
                                    errorFileDimension++;
                                }
                                if(maxHeight && parseFloat(maxHeight) < height) {
                                    errorFileDimension++;
                                }
                                if(minWidth && parseFloat(minWidth) > width) {
                                    errorFileDimension++;
                                }
                                if(maxWidth && parseFloat(maxWidth) < width) {
                                    errorFileDimension++;
                                }

                                var errorFileSize = 0;
                                if((minSize && filesizemb < parseFloat(minSize)) || (maxSize && filesizemb > parseFloat(maxSize))) {
                                    errorFileSize++;
                                }

                                if(errorFileDimension) {
                                    // fieldset.addClass('req-error');
                                    // fieldset.append('<div class="help field-error">'+fileDimensionError+'</div>');
                                    errorMessage = fileDimensionError;
                                    //file.val('');
                                    uploadFileError='yes';
                                } else if(errorFileSize) {
                                    // fieldset.addClass('req-error');
                                    // fieldset.append('<div class="help field-error">'+fileSizeError+'</div>');
                                    errorMessage = fileSizeError;
                                    //file.val('');
                                    uploadFileError='yes';
                                } else {
                                    uploadFileError='no';
                                }

                                return asyncUploaduploadFileError;
                            };

                            img.src = _URL.createObjectURL(file1);
                        }
                    } else {
                        if((minSize && filesizemb < parseFloat(minSize)) || (maxSize && filesizemb > parseFloat(maxSize))) {
                            // fieldset.addClass('req-error');
                            // fieldset.append('<div class="help field-error">'+fileSizeError+'</div>');
                            errorMessage = fileSizeError;
                            //file.val('');
                            uploadFileError='yes';
                        } else {
                            uploadFileError='no';
                        }
                    }

                    filesselected.push({
                        file:f,
                        error:uploadFileError,
                        message: errorMessage
                    });
                }

                file.val('');

                setTimeout(function() {
                    // if(window.uploadFileError) {
                    //     fieldset.addClass('req-error');
                    //     fileitem.addClass('error');
                    //     var cancel = fileitem.find('.removeBtn');
                    //     cancel.on("click", function() {
                    //         fileitem.remove();
                    //     });
                    // } else {

                        var file_cont = file.closest('div.file');
                        var files_cont = file_cont.find('.files_container');

                        var lists = files_cont.find('li');
                        var x=lists.length;
                        for(ctr=0;ctr<filesselected.length;ctr++) {
                            asyncUpload(filesselected[ctr], file_id, file, x);
                            x++;
                        }
                    //}

                    // xBtn.on("click", function() {
                    //     xhr.abort();
                    //     $(document).find('#'+file_id+'_file').val("");
                    //     $(document).find('#'+file_id+'_filename').val("");
                    //     $(document).find('input#'+file_id).val("");
                    //     $(document).find('#'+file_id+'_indicator').html('');
                    //     $(document).find('input#'+file_id).closest('fieldset').find('.help.field-error').html('');
                    //     $(document).find('#'+file_id+'_filename_container').html('');
                    // });
                },500);


            });
        });
        </script>
        <?php
        $this->_benchit();
        ?>
</body>
</html>
<?php
}

/*
Array ( [_id] => 56gap1b49sj9 [firstname] => Filip [lastname] => Vandegehuchte [account] => fffrdedede [email] => filip@cbel.com [password_token] => [password_token_sent_at] => 2015-07-04 22:58:23 [sign_in_count] => 23 [current_sign_in_at] => 2016-03-01 19:27:03 [current_sign_in_ip] => 127.0.0.1 [created_at] => 2015-06-09 17:54:58 [updated_at] => 2015-06-09 17:55:55 [admin] => 1 [accountname] => Cbel bvba )
*/

function InsideCardWrapperOpen() {
    $class='';
    if(($this->pl->isPreviewUser($this->lAccount) || $this->lUser['emailVerified']<>1) && $this->urlpart[1]<>'settings') {
        $class.='has_permanent_notification';
    }
?>
<div class="wrapper mt-80 <?php echo $class; ?>">
    <div id="content">
        <div class="row pt-4">
            <div class="col-12 col-sm-12">
                <div class="row mb-4">
                    <div class="col-12 col-md-12">
                        <div class="card formlets-border-light formlets-shadow mb-2">
                            <div class="card-body">
<?php
}

function InsideCardWrapperClose() {
?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
}

function InsideHeader(){
	$m = "insideheader";
?>
<!DOCTYPE html>
<html class="wf-formlets-n4-active wf-fontawesome-n4-inactive wf-active">
    <head>
<script>(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','GTM-5F5XK3B');</script>
        <title>Formlets </title>
        <link rel="icon" type="image/x-icon" href="/static/img/favicon.ico" />
		<?php if($this->urlpart[1]=='formtemplates'){ ?>
    	<link rel="stylesheet" type="text/css" href="/static/css/old_marketing.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
    	<?php } ?>
        <?php if($this->urlpart[1]=="admin"){?>
        <link rel="stylesheet" href="<?php echo $GLOBALS['level'];?>static/css/support.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
        <?php } elseif($this->urlpart[1]=="editor" || $this->urlpart[1]=="themes"){?>
        <link rel="stylesheet" href="<?php echo $GLOBALS['level'];?>static/css/form.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
        <?php }?>
        <link rel="stylesheet" href="<?php echo $GLOBALS['level'];?>static/css/style.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">

        <link rel="stylesheet" href="<?php echo $GLOBALS['level'];?>static/css/inside/plugins.min.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
        <link rel="stylesheet" href="<?php echo $GLOBALS['level'];?>static/css/inside/main.min.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
        <?php if($this->urlpart[2] <> 'subscription' && $this->urlpart[3] <> 'plan') { ?>
        <link rel="stylesheet" href="<?php echo $GLOBALS['level'];?>static/css/inside/checkbox.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
        <?php }?>
        <link href="https://use.fontawesome.com/releases/v5.0.6/css/all.css" rel="stylesheet">

        <?php if($this->urlpart[1]=="advancethemes") { ?>
        <link rel="stylesheet" href="<?php echo $GLOBALS['level'];?>static/css/form.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
        <?php } ?>
        <?php if($this->urlpart[1] == 'email'){ ?>

    		<link rel="stylesheet" href="<?php echo $GLOBALS['level'];?>static/css/email.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
    		<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.1.1/jquery.min.js"></script>
    		<?php if($this->urlpart[4] == 'template'){ ?>
    		<?php } ?>
    	<?php } ?>
        <?php if($this->urlpart[1]<>'editor' && $this->urlpart[1]<>'email'){ ?>
       		<script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/zepto.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
    	<?php } ?>

        <?php if($this->urlpart[1] == 'datasource') { ?>
            <script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/rubaxa.min.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
        <?php } ?>

        <?php if($this->urlpart[1] == 'stats') { ?>
            <script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/chart.min.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
        <?php } ?>

    	<?php if($this->urlpart[1] == 'editor' || $this->urlpart[1] == 'themes' || $this->urlpart[1] == 'advancethemes'){ ?>
       		<?php if($this->urlpart[1] == 'editor') { ?>
                <link rel="stylesheet" href="<?php echo $GLOBALS['level'];?>static/css/font-awesome.min.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
       			<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['level'];?>static/css/datepicker/flatpickr.min.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
       			<script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/datepicker/flatpickr.min.js"></script>
       		<?php } ?>
       		<script src="https://ajax.googleapis.com/ajax/libs/webfont/1.6.26/webfont.js"></script>
       		<?php
       		$form=$this->form;
       		$isTheme = ($this->urlpart[1]=='themes' || $this->urlpart[1]=='advancethemes') && $this->urlpart[2]<>'' && $this->urlpart[3]=='edit';
       		if($form['themeEnabled']){
       			if($isTheme || $form['themeID']) {
       				$tid = $form['themeID'] ?: $this->urlpart[2];
       				$theme = null;
       				$theme = $this->lo->listThemes(array("uid"=>$this->uid, "id"=>$tid));
					if(count($theme)) {
						$form = $theme[0];
					}
       			}
       		?>
       		<?php if($form['themeFont']){ ?>
       			<link rel="stylesheet" type="text/css" href="https://fonts.googleapis.com/css?family=<?php echo $form['themeFont']; ?>">
       		<?php } ?>
       		<style>


					.fcc, .flex-col {
						<?php if($form['themeBrowserBackground']){ ?>
						background: <?php echo $form['themeBrowserBackground']; ?> !important;
						<?php } ?>
						<?php if($form['themeFont']){ ?>
						font-family: <?php echo $form['themeFont']; ?> !important;
						<?php } ?>
					}
					<?php if($form['themeFont']){ ?>
					.fcc button, .fcc textarea {
						font-family: <?php echo $form['themeFont']; ?> !important;
					}
					<?php } ?>
					<?php if($form['themeFormBackground']){ ?>
					.fcc .ellist,
					.fcc .ellist .el,
					.fcc .ellist .el textarea:not(.text),
					.fcc .ellist .el [prop=labelText],
					.fcc .ellist .el .option-container input[type=text] {
						background: <?php echo $form['themeFormBackground']; ?> !important;
					}
					<?php } ?>
					<?php if($form['themeFormBorder']){ ?>
					.fcc .ellist {
						border: 1px solid <?php echo $form['themeFormBorder']; ?> !important;
					}
					<?php } ?>
					<?php if($form['themeFieldBackground'] || $form['themeFieldBorder']){ ?>
					.fcc .el fieldset:not(.option-container):not(.range):not(.picture),
					.fcc .el textarea.text,
                    .fcc .el canvas.signature-pad,
					<?php if(!$form['themeFieldBorder']){ ?>
					.fcc .el input[prop=placeholderText],
					.fcc .el input.static,
					<?php } ?>
					.fcc .el fieldset select {
						<?php if($form['themeFieldBackground']){ ?>
						background: <?php echo $form['themeFieldBackground']; ?> !important;
						<?php } ?>
						<?php if($form['themeFieldBorder']){ ?>
						box-shadow: 0 0 0 1px <?php echo $form['themeFieldBorder']; ?> !important;
						<?php } ?>
					}
					<?php } ?>

					<?php if($form['themeFieldHover']){ ?>
					.fcc .el fieldset:not(.option-container):not(.range):not(.picture):hover,
					.fcc .el textarea.text:hover,
					.fcc .el fieldset select:hover {
						box-shadow: 0 0 0 1px <?php echo $form['themeFieldHover']; ?> !important;
					}

					.fcc .el fieldset.option-container i:not(.fm-icon-close-thick):hover {
						border: 1px solid <?php echo $form['themeFieldHover']; ?> !important;
					}
					<?php } ?>

					<?php if($form['themeFieldActive']){ ?>
					.fcc .el fieldset:not(.option-container):not(.range):focus,
					.fcc .el textarea.text:focus,
					.fcc .el fieldset select:focus {
						box-shadow: 0 0 0 1px <?php echo $form['themeFieldHover']; ?> !important;
					}

					.fcc .el fieldset.option-container i:not(.fm-icon-close-thick):focus {
						border: 1px solid <?php echo $form['themeFieldHover']; ?> !important;
					}
					<?php } ?>

					<?php if($form['themeFieldBorder']){ ?>
						.fcc .el fieldset.option-container i:not(.fm-icon-close-thick) {
							border: 1px solid <?php echo $form['themeFieldBorder']; ?> !important;
						}
					<?php } ?>
					.fcc .el fieldset.range,
					.fcc .el fieldset.option-container {
						box-shadow: none !important;
					}

					.fcc .formElementContainer span.button,  .fcc .formElementContainer .file button[type=button]{
						<?php if($form['themeSubmitButton']){ ?>
						background: <?php echo $form['themeSubmitButton']; ?> !important;
						<?php } ?>
						<?php if($form['themeSubmitButtonText']){ ?>
						color: <?php echo $form['themeSubmitButtonText']; ?> !important;
						<?php } ?>
					}
					<?php if($form['themeText']){ ?>
					.fcc,
					.fcc .el fieldset,
					.fcc .ellist .el textarea:not(.text),
					.fcc .ellist .el div.div-textarea,
                    .fcc .el .option input.inline-edit {
						color: <?php echo $form['themeText']; ?> !important;
					}
					<?php } ?>

					<?php if($form['themeDescriptionText']){ ?>
					.fcc #description {
						color: <?php echo $form['themeDescriptionText']; ?> !important;
					}
					<?php } ?>

					<?php if($form['themeFieldText']){ ?>
						.fcc .el [prop="placeholderText"] {
							color: <?php echo $form['themeFieldText']; ?> !important;
						}
						.icon-left>i::after {
						    border-right: 1px solid <?php echo $form['themeFieldText'];?> !important;
						}
					<?php } ?>
			</style>
			<?php }
			?>
    	<?php } ?>

        <?php if($this->urlpart[1] <> 'editor') { ?>
        <style>
            [type=radio] + label, [type=checkbox] + label {
              position: relative;
              padding-left: 25px;
              cursor: pointer; }

            [type=radio], [type=checkbox] {
              position: absolute;
              left: -9999px;
              visibility: hidden; }
        </style>
        <?php } ?>

        <?php if(($GLOBALS['sess']['error_message'] || $GLOBALS['sess']['success_message']) && $this->urlpart[1]<>'editor'){ ?>
        <script>
        	$(document).ready(function(){
		        $(document).find('.alert-container span.close').on("click", function(){
		     		$(document).find(".alert-container").removeClass("error");
		            $(document).find(".alert-container").removeClass("success");
		            $(document).find(".alert-container").html("");
		            $(document).find(".alert-container").hide();
		     	});
        	});
        </script>
    	<?php } ?>
    </head>
<body <?php if($this->urlpart[1] == 'formtemplates') {echo 'style="overflow-y: scroll;"';} if($this->urlpart[1] == 'editor') {echo 'style="overflow:hidden;"';} ?>>

    <!-- header-->
        <div id="header-fix" class="header fixed-top">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-lg-8 col-xl-7 align-self-center">
                        <div class="site-logo">
                            <a href="/form/">
                              <img src="/static/img/logo-black.svg" alt="" class="img-fluid" />
                            </a>
                        </div>
                        <div id="mobile-menu" class="mobile-menu">
                            <a href="<?php echo $GLOBALS['level'];?>settings/account/">
                            <?php
                                if($this->lUser['lastName']){
                                    echo $this->lUser['firstName']." ".$this->lUser['lastName'];
                                } else if($this->lUser['email'] && !$this->pl->isPreviewUser($this->lAccount)){
                                    echo $this->lUser['email'];
                                } else if($this->pl->isPreviewUser($this->lAccount)) {
                                    echo 'Anonymous';
                                }
                            ?>
                            </a>
                            &nbsp;&nbsp;&nbsp;
                            <a href="javascript:;"><i class="fas fa-bars"></i></a>
                        </div>
                        <nav class="navbar navbar-links navbar-expand-lg">
                          <ul class="navbar-nav ml-auto">
                              <?php
                              foreach($this->interfaces_menu as $key=>$menu) {
                                  $path1 = $this->urlpart[1] . '/' . $this->urlpart[2];
                              	  if($this->urlpart[2]){
                              		  $path1.='/';
                              	  }
                              	  $path = $this->urlpart[1] . '/';

                                  if(($menu=='my account' && ($path1=='settings/integration/' || $path1=='settings/account/')) || ($key==$path && !is_array($menu)) || ($key == $path1) || ($menu=='my forms' && $this->urlpart[1]=='editor')){
                              ?>
                                  	<li class="nav-item align-self-center p-2 active"><a href="<?php echo $GLOBALS['level'].$key;?>" class="nav-link text-white"><span><?php echo ucfirst($menu);?></span></a></li>
                                  <?php
                                  } else {
                                  ?>
                      				<li class="nav-item align-self-center p-2"><a href="<?php echo $GLOBALS['level'].$key;?>" class="nav-link text-white"><span><?php echo ucfirst($menu);?></span></a></li>
                              <?php
                                  }
                              }
                              ?>
                                <li class="nav-item align-self-center p-2 logoutlink"><a href="/logout/" class="nav-link text-white"><span>Logout</span></a></li>
                          </ul>
                        </nav>
                    </div>
                    <div class="col-12 col-lg-4 col-xl-5 d-none d-lg-inline-block my-auto">
                        <nav class="navbar navbar-expand-lg p-0">
                            <ul class="navbar-nav notification ml-auto d-inline-flex">
                                <!-- <li class="nav-item dropdown">
                                    <a href="<?php echo $GLOBALS['level'];?>settings/account/" class="nav-link pl-3 pr-3" data-toggle="dropdown" aria-expanded="false">
                                        <div class="media">
                                            <div class="media-body align-self-center">
                                                <p class="mb-1 text-white d-inline-block">
                                                    <?php
                            	                        if($this->lUser['lastName']){
                            	                          	echo $this->lUser['firstName']." ".$this->lUser['lastName'];
                            	                        } else if($this->lUser['email'] && !$this->pl->isPreviewUser($this->lAccount)){
                            	                          	echo $this->lUser['email'];
                            	                        } else if($this->pl->isPreviewUser($this->lAccount)) {
                            	                        	echo 'Anonymous';
                            	                        }
                                                    ?>
                                                </p>
                                            </div>
                                        </div>
                                    </a>
                                </li> -->

                                <li class="nav-item align-self-center p-2">
                                  <a href="<?php echo $GLOBALS['level'];?>settings/account/" class="nav-link align-self-center">
                                    <div class="logout">
                                      <p class="logout-body mb-0 text-white d-inline-block">
                                          <?php
                                              if($this->lUser['lastName']){
                                                  echo $this->lUser['firstName']." ".$this->lUser['lastName'];
                                              } else if($this->lUser['email'] && !$this->pl->isPreviewUser($this->lAccount)){
                                                  echo $this->lUser['email'];
                                              } else if($this->pl->isPreviewUser($this->lAccount)) {
                                                  echo 'Anonymous';
                                              }
                                          ?>
                                      </p>
                                    </div>

                                  </a>
                                </li>

                                <?php
                                if (isset($this->lUser['admin']['rights']) && $this->lUser['admin']['rights']==15) {
                                		if($this->lUser['admin']['userId']!=$this->lUser['_id']) {
                                ?>
                                	<li class="nav-item align-self-center p-2"><a class="nav-link align-self-center" href="<?php echo $this->pl->set_csrfguard($GLOBALS['level'].'admin/users/release/'.$this->uid.'/','releaseuser');?>">X</a></li>
                                <?php
                            			}
                                }

                                $accounts = $this->accounts;
                                $validAccounts = array();
                                foreach($accounts as $account) {
                                    if(!$account['blocked']) {
                                        $validAccounts[] = $account;
                                    }
                                }
                                if(count($validAccounts) > 1) {
                                ?>
                                    <li style="margin-right: 15px">
                                        <a href="javascript:;"><?php echo $this->lAccount['companyName']?:'Unnamed Account'; ?></a>
                                        <ul>
                                            <?php foreach($validAccounts as $account) { ?>
                                                <?php if($account['_id']<>$this->lAccount['_id']) { ?>
                                                    <li><a href="/switch/<?php echo $account['_id']; ?>/"><?php echo $account['companyName']?:'Unnamed Account'; ?></a></li>
                                                <?php } ?>
                                            <?php } ?>
                                        </ul>
                                    </li>
                                <?php
                                } else {
                                    if(!$this->hasOwnAccount) {
                                ?>
                                    <li><a href="/newaccount/">Create your own Account</a></li>
                                <?php
                                    }
                                }
                                ?>

                                <?php if(isset($this->lUser['admin']['rights']) && $this->lUser['admin']['rights']==15) {	?>
                                    <li class="nav-item align-self-center p-2 dropdown">
                                      <a href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" class="nav-link dropdown-toggle align-self-center">
                                          <p class="logout-body mb-0 text-white d-inline-block">
                                              Admin</p>
                                      </a>
                                      <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                                            <?php
              		            			foreach($this->adminmenu as $k=>$ur) {
              		            				if(is_numeric($k)) {
              		            					$uri = $ur;
              		            				} else {
              		            					$uri = $k;
              		            				}
              		            			?>
              		            				<a class="dropdown-item" href="<?php echo $GLOBALS['level'].'admin/'.$uri;?>/"><?php echo ucfirst($ur);?></a>
              		            			<?php } ?>
                                      </div>
                                    </li>
                                <?php } ?>

                                <li class="nav-item align-self-center p-2">
                                  <a href="<?php echo $this->pl->set_csrfguard($GLOBALS['level'].'logout/','logout');?>" class="nav-link align-self-center">
                                    <div class="logout">
                                      <span class="text-white d-inline-block"><i class="fas fa-sign-out-alt"></i></span>
                                      <p class="logout-body mb-0 text-white d-inline-block">Logout</p>
                                    </div>

                                  </a>
                                </li>
                            </ul>
                        </nav>
                    </div>
                </div>
            </div>
        </div>
        <!-- End header-->
<?php
    $has_permanent = false;
	 if($this->pl->isPreviewUser($this->lAccount) && $this->urlpart[1]<>'settings') {
         $has_permanent = true;
?>
		<div class="permanent_notification">&nbsp; <?php echo $this->pl->trans($m, 'You are in preview mode'); ?>. <a href="/settings/account/"><?php echo $this->pl->trans($m, 'Register to be able to publish forms'); ?></a></div>
<?php
	} else if($this->lUser['emailVerified']<>1 && $this->urlpart[1]<>'settings') {
        $has_permanent = true;
?>
		<div class="permanent_notification">&nbsp; <?php echo $this->pl->trans($m, 'Please check your mailbox (or spam folder) to validate your account before you can publish forms'); ?>. <a href="/resendemailvalidation/"><?php echo $this->pl->trans($m, 'Resend account validation email'); ?></a></div>
<?php
	}

    if($has_permanent) {
        $style="margin-top:-22px";
    }

	if($GLOBALS['sess']['error_message']){
		echo '<div class="alert-container alert-minus-bottom error" style="'.$style.'"><div class="content">'.$GLOBALS['sess']['error_message'].'</div><span class="close"><i class="fa fa-times-circle"></i></span></div>';
        $this->pl->save_session('error_message', '');
	}
	if($GLOBALS['sess']['success_message']){
		echo '<div class="alert-container alert-minus-bottom success" style="'.$style.'"><div class="content">'.$GLOBALS['sess']['success_message'].'</div><span class="close"><i class="fa fa-times-circle"></i></span></div>';
        $this->pl->save_session('success_message', '');
	}


}
//

//
function Insidefooter(){
?>
 </body>
 </html>
<?php
}
//


//
function OutputTemplate(){
   $this->InsideHeader();
echo "under construction";
  $this->InsideFooter();
}
//

function Outputemailok(){
    $this->OutputMarketingHeader(array("title"=>"Email Validated","descr"=>""));
?>
<section id="Content-Container">
    <div class="support">
        <div class="container">
            <br><br><br><br>
            <h3>Thank you for validating your email address</h3>
            <br><br><br><br><br><br><br><br><br><br>
            <br><br><br><br><br><br>
        </div>
    </div>
</section>
<?php
$this->OutputMarketingFooter2();
}

function OutputResendemailvalidation(){
    $this->OutputMarketingHeader(array("title"=>"Email validation sent","descr"=>""));
?>
    <section id="Content-Container">
    <div class="support">
        <div class="container">
            <br><br><br><br>
            <h3>Thank you for requesting a new email validation , it should arrive in your mailbox shortly</h3>
            <br><br><br><br><br><br><br><br><br><br>
            <br><br><br><br><br><br>
        </div>
    </div>
    </section>
<?php
$this->OutputMarketingFooter2();
}

//
function InsideSettingsheader($class){
	$m = "insidesettingsheader";
?>

	<?php if($this->urlpart[1]<>'team' && !$this->pl->isPreviewUser($this->lAccount)){ ?>
	<ul class="submenu_settings">
		<li><a href="/settings/account/" class="btn btn-sm btn-info <?php echo $this->urlpart[2]=='account' ? 'active':'' ?>"><?php echo $this->pl->trans($m, 'Settings'); ?></a></li>
		<li><a href="/settings/usage/" class="btn btn-sm btn-info <?php echo $this->urlpart[2]=='usage' ? 'active':'' ?>"><?php echo $this->pl->trans($m, 'Usage'); ?></a></li>
		<li><a href="/settings/integration/" class="btn btn-sm btn-info <?php echo $this->urlpart[2]=='integration' ? 'active':'' ?>"><?php echo $this->pl->trans($m, 'Integrations'); ?></a></li>
		<?php if($this->pl->canUser($this->lAccount, 'manage_account') && !$this->pl->isPreviewUser($this->lAccount)) { ?>
			<li><a href="/settings/subscription/" class="btn btn-sm btn-info <?php echo $this->urlpart[2]=='subscription' ? 'active':'' ?>"><?php echo $this->pl->trans($m, 'Subscription'); ?></a></li>
		<?php } ?>
	</ul>
	<?php } ?><?php
}
//


//
function InsideSettingsFooter(){
?>
<?php
$this->InsideFooter();
}
//

function OutputTeam(){

    $this->InsideHeader();

    $this->InsideCardWrapperOpen();

    $this->InsidesettingsHeader('settings');

	$this->outputTeamMembers();

	$this->InsideSettingsFooter();

    $this->InsideCardWrapperClose();

    $this->OutputMarketingFooter();
}

function outputTeamMembers(){
	$m='team';

    //get members
    $members = $this->lo->_getUsers(array('accountId'=>$this->lAccount['_id']));
    $owner = $members[0];
    $maxMembers = $this->lAccount['accountStatus'] == 'PRO-MONTHLY-OXOPIA' ? 10:'UNLIMITED';

    if($this->pl->isFreeAccount($this->lAccount)) {
        $maxMembers = 0;
    }

    $members_count = count($members) - 1;

    if($this->urlpart[2] == 's' && $this->urlpart[3]) {
        $teamMember = $this->lo->_getUsers(array('id'=>$this->urlpart[3]))[0];
        $permissions = array();
        if($teamMember['permissions']){
          $permissions = json_decode(str_replace('\"','"',$teamMember['permissions']), true);
        }

        $forms = $this->lo->_listForms(array('uid'=>$this->lAccountOwner['_id']));
    }

    $readonly="";
    if (!$this->pl->canUser($this->lAccount, 'manage_account')) {
        $readonly = "readonly";
    }
?>
        <?php if($this->pl->isPreviewUser($this->lAccount)) { ?>
            <h2>This module is for registered users only.</h2>
        <?php } else { ?>

            <div class="row Team-Content">
              <div class="col text-center">
                <h3><?php echo $this->lUser['companyName']; ?> Users Invite</h3>
                <div class="d-inline-block">
                    <form action="/team/" method="POST">
                        <input type="hidden" name="accountid" value="<?php echo $this->lAccount['_id']; ?>" />
        				<input type="hidden" name="max_members" value="<?php echo $maxMembers ?>" />
                        <div class="form-group pull-left pr-4">
                            <input type="email" name="email" class="form-control" placeholder="Enter Email">
                        </div>
                        <div class="form-group pull-left pr-4">
                            <input type="checkbox" id="checkbox1" name="read_access" value="1" checked>
                            <label for="checkbox1" class="redial-dark mb-0">Give read access to all forms</label>
                        </div>
                        <button name="invite_team" class="btn btn-primary"><?php echo $this->pl->trans($m,'Invite'); ?></button>
                    </form>
                </div>
                <div class="text-left">
                  <table id="data-table" class="table table-striped" cellspacing="0" width="100%">
                      <thead>
                          <tr>
                              <th width="20%" scope="col"><?php echo $this->pl->trans($m,'Account'); ?></th>
                              <th width="10%" scope="col"><?php echo $this->pl->trans($m,'Role'); ?></th>
                              <th width="10%" scope="col"><?php echo $this->pl->trans($m,'Status'); ?></th>
                              <th width="40%" scope="col"><?php echo $this->pl->trans($m,'General Permission'); ?></th>
                              <th width="20%" scope="col"></th>
                          </tr>
                      </thead>
                      <tbody>
                          <?php foreach($members as $member) { ?>
                          <tr>
                              <td data-label="Account"><?php echo $member['email']; ?></td>
                              <td data-label="Role"><?php echo $member['_id']==$owner['_id'] ? 'Admin':'Member' ?></td>
                              <td data-label="Status"><?php echo $member['emailVerified']=='0' || $member['blocked'] ? 'Pending':'Joined' ?></td>
                              <td data-label="General Permission">
                                  <?php if($member['_id']<>$owner['_id']) { ?>
                                      <form action="<?php echo $_SERVER["REQUEST_URI"];?>" method="POST">
                                          <input type="hidden" name="member" value="<?php echo $member['_id']; ?>" />
                                          <div class="form-group pull-left pr-4">
                                              <input id="<?php echo $member['_id']; ?>_read" type="checkbox" class="general_permission_checkbox <?php echo $readonly; ?>" name="general_permission[]" value="<?php echo $GLOBALS['ref']['rights']['read']; ?>" <?php if($this->pl->canUser($member, 'read')) {echo 'checked';} ?> />
                                              <label for="<?php echo $member['_id']; ?>_read" class="redial-dark mb-0"><?php echo $this->pl->trans($m,'Read'); ?></label>
                                          </div>
                                          <?php if ($GLOBALS['ref']['plan_lists'][$this->account['index']]['team'] == false) { ?>
                                              <div class="form-group pull-left pr-4">
                                                  <input disabled id="<?php echo $member['_id']; ?>_create" type="checkbox" class="general_permission_checkbox <?php echo $readonly; ?>" name="general_permission[]" value="0" <?php if($this->pl->canUser($member, 'create')) {echo 'checked';} ?> />
                                                  <label for="<?php echo $member['_id']; ?>_create" class="redial-dark mb-0"><?php echo $this->pl->trans($m,'Create'); ?></label>
                                              </div>
                                              <div class="form-group pull-left pr-4">
                                                  <input disabled id="<?php echo $member['_id']; ?>_edit" type="checkbox" class="general_permission_checkbox <?php echo $readonly; ?>" name="general_permission[]" value="0" <?php if($this->pl->canUser($member, 'edit')) {echo 'checked';} ?> />
                                                  <label for="<?php echo $member['_id']; ?>_edit" class="redial-dark mb-0"><?php echo $this->pl->trans($m,'Edit'); ?></label>
                                              </div>
                                              <div class="form-group pull-left pr-4">
                                                  <input disabled id="<?php echo $member['_id']; ?>_delete" type="checkbox" class="general_permission_checkbox <?php echo $readonly; ?>" name="general_permission[]" value="0" <?php if($this->pl->canUser($member, 'delete')) {echo 'checked';} ?> />
                                                  <label for="<?php echo $member['_id']; ?>_delete" class="redial-dark mb-0"><?php echo $this->pl->trans($m,'Delete'); ?></label>
                                              </div>
                                          <?php } else { ?>
                                              <div class="form-group pull-left pr-4">
                                                  <input id="<?php echo $member['_id']; ?>_create" type="checkbox" class="general_permission_checkbox <?php echo $readonly; ?>" name="general_permission[]" value="<?php echo $GLOBALS['ref']['rights']['create']; ?>" <?php if($this->pl->canUser($member, 'create')) {echo 'checked';} ?> />
                                                  <label for="<?php echo $member['_id']; ?>_create" class="redial-dark mb-0"><?php echo $this->pl->trans($m,'Create'); ?></label>
                                              </div>
                                              <div class="form-group pull-left pr-4">
                                                  <input id="<?php echo $member['_id']; ?>_edit" type="checkbox" class="general_permission_checkbox <?php echo $readonly; ?>" name="general_permission[]" value="<?php echo $GLOBALS['ref']['rights']['edit']; ?>" <?php if($this->pl->canUser($member, 'edit')) {echo 'checked';} ?> />
                                                  <label for="<?php echo $member['_id']; ?>_edit" class="redial-dark mb-0"><?php echo $this->pl->trans($m,'Edit'); ?></label>
                                              </div>
                                              <div class="form-group pull-left pr-4">
                                                  <input id="<?php echo $member['_id']; ?>_delete" type="checkbox" class="general_permission_checkbox <?php echo $readonly; ?>" name="general_permission[]" value="<?php echo $GLOBALS['ref']['rights']['delete']; ?>" <?php if($this->pl->canUser($member, 'delete')) {echo 'checked';} ?> />
                                                  <label for="<?php echo $member['_id']; ?>_delete" class="redial-dark mb-0"><?php echo $this->pl->trans($m,'Delete'); ?></label>
                                              </div>
                                          <?php } ?>

                                      </form>
                                  <?php } ?>
                              </td>
                              <td>
                  					<?php if($member['_id']<>$owner['_id']) { ?>
                                          <?php if ($this->pl->canUser($this->lAccount, 'manage_account')) { ?>
                  						    <a onclick="return confirm('<?php echo $this->pl->trans($m,'are you sure you want to delete that member'); ?>?')" href="<?php echo $this->pl->set_csrfguard('/team/delete-member/'.$member['_id'].'/','deleteteam');?>" class="btn btn-default"><?php echo $this->pl->trans($m,'Delete'); ?></a>
                                          <?php } ?>
                  						<a class="btn btn-default" href="/team/s/<?php echo $member['_id']; ?>/"><?php echo $this->pl->trans($m,'Detailed Permissions'); ?></a>
                  					<?php } ?>
          				      </td>
                          </tr>
                          <?php
          					if(($this->urlpart[2]=='s') && ($this->urlpart[3] == $member['_id'])) {
          				?>
          						<tr>
          							<td colspan="5">
          								<form action="<?php echo $_SERVER["REQUEST_URI"];?>" method="POST">
          									<table class="table table-striped data-table" cellspacing="0" width="100%">
          										<thead>
          											<tr>
          												<th style="color:#18292F"><?php echo $this->pl->trans($m,'Forms'); ?></th>
          												<th style="color:#18292F"><?php echo $this->pl->trans($m,'Permission'); ?></th>
          											</tr>
          										</thead>
          										<tbody>
          											<?php foreach($forms as $form){ ?>
                                                          <input type="hidden" name="form[]" value="<?php echo $form['_id']; ?>" />
          												<tr>
          													<td width="50%"><?php echo $form['name']; ?></td>
          													<td>
                                                                  <div class="form-group pull-left pr-4">
                                                                      <input id="c_<?php echo $form['_id']; ?>_read" type="checkbox" class="<?php echo $readonly; ?>" name="permission[<?php echo $form['_id']; ?>][]" value="<?php echo $GLOBALS['ref']['rights']['read']; ?>" <?php if($permissions[$form['_id']] && $permissions[$form['_id']] & $GLOBALS['ref']['rights']['read']) {echo 'checked';} ?> />
                                                                      <label for="c_<?php echo $form['_id']; ?>_read" class="redial-dark mb-0"><?php echo $this->pl->trans($m,'Read'); ?></label>
                                                                  </div>
                                                                  <?php if ($GLOBALS['ref']['plan_lists'][$this->account['index']]['team'] == false) { ?>
                                                                      <div class="form-group pull-left pr-4">
                                                                          <input disabled id="c_<?php echo $form['_id']; ?>_edit" type="checkbox" class="<?php echo $readonly; ?>" name="permission[<?php echo $form['_id']; ?>][]" value="0" <?php if($permissions[$form['_id']] && $permissions[$form['_id']] & $GLOBALS['ref']['rights']['edit']) {echo 'checked';} ?> />
                                                                          <label for="c_<?php echo $form['_id']; ?>_edit" class="redial-dark mb-0"><?php echo $this->pl->trans($m,'Edit'); ?></label>
                                                                      </div>
                                                                      <div class="form-group pull-left pr-4">
                                                                          <input disabled id="c_<?php echo $form['_id']; ?>_delete" type="checkbox" class="<?php echo $readonly; ?>" name="permission[<?php echo $form['_id']; ?>][]" value="0" <?php if($permissions[$form['_id']] && $permissions[$form['_id']] & $GLOBALS['ref']['rights']['delete']) {echo 'checked';} ?> />
                                                                          <label for="c_<?php echo $form['_id']; ?>_delete" class="redial-dark mb-0"><?php echo $this->pl->trans($m,'Delete'); ?></label>
                                                                      </div>
                                                                  <?php } else { ?>
                                                                      <div class="form-group pull-left pr-4">
                                                                          <input id="c_<?php echo $form['_id']; ?>_edit" type="checkbox" class="<?php echo $readonly; ?>" name="permission[<?php echo $form['_id']; ?>][]" value="<?php echo $GLOBALS['ref']['rights']['edit']; ?>" <?php if($permissions[$form['_id']] && $permissions[$form['_id']] & $GLOBALS['ref']['rights']['edit']) {echo 'checked';} ?> />
                                                                          <label for="c_<?php echo $form['_id']; ?>_edit" class="redial-dark mb-0"><?php echo $this->pl->trans($m,'Edit'); ?></label>
                                                                      </div>
                                                                      <div class="form-group pull-left pr-4">
                                                                          <input id="c_<?php echo $form['_id']; ?>_delete" type="checkbox" class="<?php echo $readonly; ?>" name="permission[<?php echo $form['_id']; ?>][]" value="<?php echo $GLOBALS['ref']['rights']['delete']; ?>" <?php if($permissions[$form['_id']] && $permissions[$form['_id']] & $GLOBALS['ref']['rights']['delete']) {echo 'checked';} ?> />
                                                                          <label for="c_<?php echo $form['_id']; ?>_delete" class="redial-dark mb-0"><?php echo $this->pl->trans($m,'Delete'); ?></label>
                                                                      </div>
                                                                  <?php } ?>
          													</td>
          												</tr>
          											<?php } ?>
          										</tbody>
          									</table>
          									<a class="button red btn btn-dafault" href="/team/"><?php echo $this->pl->trans($m,'Cancel'); ?></a>
          									<button name="save_permission" class="btn btn-primary"><?php echo $this->pl->trans($m,'Save'); ?></button>
          								</form>
          							</td>
          						</tr>
                          <?php
          					}
          				} ?>
                      </tbody>
                  </table>
                </div>

              </div>

            </div>
        <?php } ?>
  <script type="text/javascript">
    $(function(){
        $("#create_team").on("click", function(e){
            e.preventDefault();
            $("#create_team_form").toggle();
            $("#create_team_form").find("input").first().focus();
        });

        $("#invite_team").on("click", function(e){
            e.preventDefault();
            $("#invite_team_form").toggle();
            $("#invite_team_form").find("input").first().focus();
        });
        var gpc_changed = false;
        $(".general_permission_checkbox").on("change", function() {
            //if(gpc_changed) { return; }
            var input = $(this);
            var isChecked = input.is(":checked");
            if(input.hasClass('readonly')) {
                $(document).find('.alert-container').remove();
                var alertC = $('<div class="alert-container alert-minus-bottom error"><div class="content">Permission not saved. You have no right to change permissions</div><span class="close"><i class="fa fa-times-circle"></i></span></div>');
                alertC.insertAfter('header');

                alertC.find('span.close').on("click", function(){
                    alertC.remove();
                });
            } else {
                var form = $(this).closest('form');
                $.ajax({
                    type: "POST",
                    url: form.attr( 'action' ),
                    data: form.serialize(),
                    success: function( response ) {
                    }
                });
            }

        });

        $("input.disabled").each(function(e) {
            //e.preventDefault();
            var $this = $(this);
            var label = $(this).closest('label');
            label.on("click", function() {
                $(document).find('.alert-container').remove();
                var alertC = $('<div class="alert-container alert-minus-bottom error"><div class="content">Please upgrade to <a href="/settings/subscription/">PRO</a> to enable more permissions.</div><span class="close"><i class="fa fa-times-circle"></i></span></div>');
                alertC.insertAfter('header');

                alertC.find('span.close').on("click", function(){
                    alertC.remove();
                });
            });
        });

    });
  </script>
  <?php

}

function paymentPlan() {
    $cs=array('USD'=>'$','EUR'=>'€');
    $ms=array('MONTHLY'=>'/ month','YEARLY'=>'/ year');
    if($cs[$_GET['cur']]){$cur=$_GET['cur'];}
    if($ms[$_GET['mode']]){$mode=$_GET['mode'];}
    $m="pricing";
    if(!$cur){$cur="USD";}
    if(!$mode){$mode="MONTHLY";}

    $usLink = '/settings/account/plan/?cur=USD';
    $eurLink = '/settings/account/plan/?cur=EUR';

    $plid = $this->plid;
?>
<style>
/**
 * The CSS shown here will not be introduced in the Quickstart guide, but shows
 * how you can use CSS to style your Element's container.
 */
.StripeElement {
    height: 35px;
  border-radius: 0px;
  border-color: #e6e6e6;
  color: #676767;
  transition: .2s ease-out;
  padding: .5rem 1.2rem;
  font-size: 0.875rem;
  border: 1px solid #ced4da;
}

.StripeElement--invalid {
    border: 1px solid #fa755a !important;
  border-color: #fa755a;
  box-shadow: none;
}

.StripeElement--webkit-autofill {
  background-color: #fefde5 !important;
}

.field {
    color: #32325d;
    line-height: 24px;
    font-family: "Helvetica Neue", Helvetica, sans-serif;
    -webkit-font-smoothing: antialiased;
    font-size: 0.875rem;
}

.field::placeholder{
    color: #aab7c4
}

#payment-form .gr{
    margin: 10px 0px;
}

#card-errors {
    color:#fa755a;
}

#payment-form label span {
    margin-bottom: 2px;
}

#paymentContainer {
    margin: 5% auto;
    left: 0;
    right: 0;
    overflow: hidden;
}
</style>
<div class="row Account-Content">
  <div class="col-12">
      <div class="tab-content" id="myTabContent">

        <div>
            <script src="https://js.stripe.com/v3/"></script>
            <form action="<?php echo $_SERVER["REQUEST_URI"];?>&st=ok" method="POST" id="payment-form">
                <input type="hidden" id="publishable_key" value="<?php echo $GLOBALS["conf"]["stripe_publishable_key"]; ?>">
                <h3 class="py-2">Payment for Subscribing to <span><?php echo $this->availableplans["list"][$this->urlpart[4]]['name'];?> <?php echo $cur." ".$mode;?></span></h3>
                <center><button class="btn btn-sm btn-success" disabled style="width:100px">Total: <?php echo $cs[$cur].$this->availableplans["list"][$this->urlpart[4]]['stripe_plans'][$cur][$mode]; ?></button></center>
                <div class='row creditCardDetails pb-4'>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="formlets-font-weight-800 formlets-dark">Cardholder's Name*</label>
                            <input name="cardholder-name" type="text" class="form-control bg-transparent" placeholder="Jane Doe" />
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="formlets-font-weight-800 formlets-dark">Card Number*</label>
                            <div id="card-element">
                              <!-- a Stripe Element will be inserted here. -->
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="formlets-font-weight-800 formlets-dark">Expiry Date*</label>
                            <div id="expiry-element">
                                <!-- a Stripe Element will be inserted here. -->
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="formlets-font-weight-800 formlets-dark">CVV/CVC*</label>
                            <div id="code-element">
                                <!-- a Stripe Element will be inserted here. -->
                            </div>
                            <small class="form-text">3-4 Digits Code</small>
                        </div>
                    </div>
                    <div class="col-12">
                        <div class="form-group">
                            <label class="formlets-font-weight-800 formlets-dark">Postal Code*</label>
                            <div id="zip-element">
                                <!-- a Stripe Element will be inserted here. -->
                            </div>
                        </div>
                    </div>
                    <div class="col-12">
                        <div id="card-errors" role="alert"></div>
                    </div>
                    <div class="col-12">Payment processing by Stripe</div>
                </div>
                <button type="submit" class="btn btn-primary btn-sm rounded-0 px-5 float-sm-right mt-sm-0 mt-3 submit"> Submit </button>
            </form>
        </div>
      </div>
  </div>
</div>
<script>
// Create a Stripe client
var pKey = document.getElementById('publishable_key').value;
var stripe = Stripe(pKey);

// Create an instance of Elements
var elements = stripe.elements();

// Custom styling can be passed to options when creating an Element.
// (Note that this demo uses a wider set of styles than the guide below.)
var style = {
  base: {
    color: '#32325d',
    lineHeight: '18px',
    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
    fontSmoothing: 'antialiased',
    fontSize: '0.875rem',
    '::placeholder': {
      color: '#aab7c4'
    }
  },
  invalid: {
    color: '#fa755a',
    iconColor: '#fa755a'
  }
};

// Create an instance of the card Element
var card = elements.create('cardNumber', {style: style});
var expiry = elements.create('cardExpiry', {style: style});
var code = elements.create('cardCvc', {style: style});
var zip = elements.create('postalCode', {style: style});

// Add an instance of the card Element into the `card-element` <div>
card.mount('#card-element');
expiry.mount('#expiry-element');
code.mount('#code-element');
zip.mount('#zip-element');

// Handle real-time validation errors from the card Element.
card.addEventListener('change', function(event) {
  var displayError = document.getElementById('card-errors');
  if (event.error) {
    displayError.textContent = event.error.message;
  } else {
    displayError.textContent = '';
  }
});

var $form = $('#payment-form');

function stripeTokenHandler(token, coupon) {

    var token = token.id;

    // Insert the token ID into the form so it gets submitted to the server:
    $form.append($('<input type="hidden" name="stripeToken">').val(token));
    $form.append($('<input type="hidden" name="couponCode">').val(coupon));
    <?php if($plid=="card"){ ?>
        $form.append($('<input type="hidden" name="submitType">').val("changeCard"));
    <?php } ?>
    // Submit the form:
    $form.get(0).submit();
}

// Handle form submission
var form = document.getElementById('payment-form');

form.addEventListener('submit', function(event) {
  event.preventDefault();
  var coupon = '';
  <?php if($plid<>"card" && isset($_GET['coupon'])){ ?>
      coupon = form.querySelector('input[name=coupon-code]').value;
  <?php } ?>
  var extraDetails = {
    name: form.querySelector('input[name=cardholder-name]').value,
  };
  $form.find('.submit').prop('disabled', true);
  $form.find('.submit').html('Processing...');

  if($.trim(extraDetails.name)=='') {
      $('input[name=cardholder-name]').addClass('StripeElement--invalid');
      $form.find('.submit').prop('disabled', false);
      $form.find('.submit').html('Submit');
      var errorElement = document.getElementById('card-errors');
      errorElement.textContent = 'Name on Card is incomplete';
  } else {
      stripe.createToken(card, extraDetails).then(function(result) {
        if (result.error) {
          // Inform the user if there was an error
          var errorElement = document.getElementById('card-errors');
          errorElement.textContent = result.error.message;
          $form.find('.submit').prop('disabled', false);
        } else {
          // Send the token to your server
          stripeTokenHandler(result.token, coupon);
        }
      });
  }

});
</script>
<?php
}

function selectPlan() {
    $cs=array('USD'=>'$','EUR'=>'€');
    $ms=array('MONTHLY'=>'/ month','YEARLY'=>'/ year');
    if($cs[$_GET['cur']]){$cur=$_GET['cur'];}
    if($ms[$_GET['mode']]){$mode=$_GET['mode'];}
    $m="pricing";
    if(!$cur){$cur="USD";}
    if(!$mode){$mode="MONTHLY";}

    $usLink = '/settings/account/plan/?cur=USD';
    $eurLink = '/settings/account/plan/?cur=EUR';

    $plid = $this->plid;
?>
<style>
[type=radio] + label:before {
  content: '';
  position: absolute;
  top: 2px;
  left: 0;
  width: 17px;
  height: 17px;
  z-index: 0;
  border-radius: 100%;
  border: 1.5px solid #e6e6e6;
  transition: .2s; }
  [type=radio]:checked + label:before {
    width: 17px;
    height: 17px;
    border-width: 2px;
    background-color: #18292F;
    border: 1.5px solid #e6e6e6
    -webkit-transform: rotate(40deg);
    transform-origin: 100% 100%; }
.form-check {
    min-height: 120px;
}
.features {
    position: absolute;
    right: 0;
    top: 0;
    margin: 15px 10px;
}
.features li {
    text-align: right;
}
</style>
<div class="row Account-Content">
  <div class="col-12">
      <div class="tab-content" id="myTabContent">
          <div>
              <div class="pull-right" style="width:100%;margin-bottom:10px;text-align:right">
                  <label style="margin-right:20px;">Pay with</label>
                  <div class="btn-group btn-currency" role="group">
                      <a href="<?php echo $usLink; ?>" class="btn btn-success <?php if($cur == 'USD'){ echo "active"; } ?>">USD</a>
                      <a href="<?php echo $eurLink; ?>" class="btn btn-success <?php if($cur == 'EUR'){ echo "active"; } ?>">EURO</a>
                  </div>
              </div>
              <h3 class="py-2">Select your Plan</h3>
              <form action="<?php echo $_SERVER["REQUEST_URI"];?>" method="POST" id="payment-form" no-validate="true">
                  <div class='row pb-4'>
                      <div class="col-12">
                        <div class="choosePlan">
                            <div class="form-group">
                                <?php
                                for ($a=0;$a<count($this->availableplans["list"]);$a++){
                                $thelist=$this->availableplans["list"][$a];
                                    if($thelist['status']=="active"){
                                ?>
                                <div class="form-check">
                                    <?php if($thelist['plan']=="FREE"){?>
                                        <h6 class="m-0"><?php echo $thelist['name'];?></h6>
                                        <div>
                                          <input class="form-check-input" type="radio" name="formletsPlan" id="<?php echo $thelist['plan']; ?>" value="<?php echo $a; ?>">
                                          <label class="form-check-label" for="<?php echo $thelist['plan']; ?>">
                                            <span>FREE</span>
                                          </label>
                                        </div>
                                    <?php } else { ?>
                                        <h6 class="m-0"><?php echo $thelist['name'];?></h6>
                                        <div>
                                          <input class="form-check-input" type="radio" name="formletsPlan" id="<?php echo $thelist['plan']; ?>_MONTHLY" value="<?php echo $a.'_'.$cur.'_MONTHLY'; ?>">
                                          <label class="form-check-label" for="<?php echo $thelist['plan']; ?>_MONTHLY">
                                            <span><?php echo $cs[$cur].$thelist['stripe_plans'][$cur]['MONTHLY']; ?> <?php echo $ms['MONTHLY']; ?></span>
                                          </label>
                                        </div>
                                        <div>
                                          <input class="form-check-input" type="radio" name="formletsPlan" id="<?php echo $thelist['plan']; ?>_YEARLY" value="<?php echo $a.'_'.$cur.'_YEARLY'; ?>">
                                          <label class="form-check-label" for="<?php echo $thelist['plan']; ?>_YEARLY">
                                          <?php
                                          $perM = number_format($thelist['stripe_plans'][$cur]['YEARLY']/12,2)+0;

                                          $nodiscountY = $thelist['stripe_plans'][$cur]['MONTHLY']*12;
                                          $discount = $nodiscountY - $thelist['stripe_plans'][$cur]['YEARLY'];
                                          $discountP = $discount/$nodiscountY*100;
                                          $discountP = number_format($discountP, 2) + 0;
                                          ?>
                                            <span><?php echo $cs[$cur].$thelist['stripe_plans'][$cur]['YEARLY']; ?> <?php echo $ms['YEARLY']; ?> = <?php echo $cs[$cur]; ?><?php echo $perM; ?> per month or a discount of <?php echo $discountP; ?>%</span>
                                          </label>
                                        </div>
                                    <?php } ?>

                                    <div class="features">
                                        <?php foreach($thelist['smalldescr'] as $descr){?>
                                            <li><?php echo $descr; ?></li>
                                        <?php } ?>
                                    </div>
                                </div>
                                <?php
                                    }
                                }
                                ?>


                            </div>
                            <div class="alert alert-danger" id="noplanalert" style="margin:0;display:none;">Please select your plan.</div>
                        </div>

                      </div>

                  </div>

                  <button type="submit" id="continue" class="btn btn-primary btn-sm rounded-0 px-5 float-sm-right mt-sm-0 mt-3">Continue</button>
              </form>
          </div>

      </div>
  </div>
</div>
<script>
$(function() {
    $("#continue").on('click', function(e) {
        e.preventDefault();

        var selectedPlan = $("input[name='formletsPlan']:checked").val();
        if(selectedPlan) {
            $("#payment-form").submit();
        } else {
            $("#noplanalert").show();
        }
    });

    $("input[name='formletsPlan']").on("change", function() {
        $("#noplanalert").hide();
    });
});
</script>
<?php
}

//
function OutputSettings(){
	$m = "settings";

    if($this->urlpart[2]=='subscription'){
		$this->OutputSubscription();
		exit;
	}

    if($this->urlpart[2]=='usage'){
		$this->OutputUsage();
		exit;
	}

    $this->InsideHeader();

    $this->InsideCardWrapperOpen();

	$this->InsidesettingsHeader('settings');
		if($this->urlpart[2] === 'integration'){
	?>
        <br>
        <table id="data-table" class="table table-striped" width="100%">
            <thead>
                <tr>
                    <th align="left" width="20%" scope="col"><?php echo $this->pl->trans($m,'NAME'); ?></th>
                    <th style="text-align:left" width="20%" scope="col"><?php echo $this->pl->trans($m,'LINK'); ?></th>
                    <th style="text-align:left" scope="col"><?php echo $this->pl->trans($m,'DESCRIPTION'); ?></th>
                </tr>
            </thead>
            <tbody>
                <tr>
					<td data-label="Name">Zapier</td>
					<td data-label="Link"><a target="_new" href="https://zapier.com/developer/invite/24924/bdd5b8386c1196c327aab7990c0aa897/"><?php echo $this->pl->trans($m,'View Invitation'); ?></a></td>
					<td data-label="Description"><?php echo $this->pl->trans($m,'Zapier is an web automation app. With Zapier you can build Zaps which can automate parts of your business or life. A Zap is a blueprint for a task you want to do over and over.'); ?></td>
				</tr>
                <tr>
                    <td colspan="3">
                        <script src="https://zapier.com/apps/embed/widget.js?services=formlets"></script>
                    </td>
                </tr>
            </tbody>
        </table>
	<?php
		} elseif($this->urlpart[2] === 'account'){
			$user=$this->lo->_getUsers(array('id'=>$this->uid))[0];

			if($GLOBALS['sess']['form_user_session']) {
				$user = $GLOBALS['sess']['form_user_session'];
				$user['post_email'] = $GLOBALS['sess']['form_user_session']['email'];
				$user['email'] = $this->lo->_getUsers(array('id'=>$this->uid))[0]['email'];
			}

			$email = !$this->pl->isPreviewUser($this->lAccount) ? $user['email']:'';
			if($GLOBALS['sess']['form_user_session']) {
				$email = $user['post_email'];
			}

            $canUpdateCompany = $this->pl->canUser($this->lAccount, 'manage_account');
	?>
        <script src="/static/js/formvalidator.js"></script>

		<?php if(!$this->pl->isPreviewUser($this->lAccount)) { ?>
			<script type="text/javascript" src="https://www.formlets.com/static/js/iframeResizer.min.js"></script>
			<a target="_blank" class="button" style="float: right;margin-top: -40px;" onclick="FormletOpen('svQlKH3R4B3WVJyN');" id="svQlKH3R4B3WVJyN" href="https://www.formlets.com/forms/svQlKH3R4B3WVJyN/?iframe=true"><?php echo $this->pl->trans($m,'DELETE MY FORMLETS ACCOUNT FOREVER'); ?></a>
			<script type="text/javascript" src="https://www.formlets.com/static/js/modal.js"></script>
			<script type="text/javascript">Formlet("svQlKH3R4B3WVJyN");</script>
		<?php } ?>

        <?php
        if($this->urlpart[3] == 'plan' && !$this->urlpart[4]) {
            $this->selectPlan();
        } else if($this->urlpart[3] == 'plan' && $this->urlpart[4]) {
            $this->paymentPlan();
        } else {
        ?>

        <div class="row Account-Content">
            <div class="col-12">
                    <div class="tab-content" id="myTabContent">
                        <div>
                            <br>
                        <h3 class="py-2">Account Information</h3>

        				<form action="<?php echo $_SERVER["REQUEST_URI"];?>" method="POST" autocomplete="off" onsubmit="return checkForm(this);" data-validate>
                            <div class='row pb-4'>
                              <div class="col-6">
                                  <div class="form-group">
                                      <label class="formlets-font-weight-800 formlets-dark">First Name*</label>
                                      <input type="text" class="form-control bg-transparent" name="firstName" value="<?php echo $user['firstName']; ?>" required />
                                  </div>
                              </div>
                              <div class="col-6">
                                  <div class="form-group">
                                      <label class="formlets-font-weight-800 formlets-dark">Last Name*</label>
                                      <input type="text" class="form-control bg-transparent" name="lastName" value="<?php echo $user['lastName']; ?>" required />
                                  </div>
                              </div>
                              <div class="col-12">
                                  <div class="form-group">
                                      <label class="formlets-font-weight-800 formlets-dark">Email*</label>
                                      <input type="email" class="form-control bg-transparent" name="email" value="<?php echo $email; ?>" placeholder="" required />
                                  </div>
                              </div>
                              <div class="col-12">
                                  <div class="form-group">
                                      <label class="formlets-font-weight-800 formlets-dark">Phone</label>
                                      <input type="text" class="form-control bg-transparent" name="phone" value="<?php echo $user['phone']; ?>" autocomplete="off" placeholder="" />
                                  </div>
                              </div>
                              <?php if(!$this->pl->isPreviewUser($this->lAccount)) { ?>
                              <div class="col-12">
                                  <div class="form-group">
                                      <label class="formlets-font-weight-800 formlets-dark">Old Password</label>
                                      <input type="password" class="form-control bg-transparent" name="old_password" placeholder="" />
                                      <small class="form-text">Leave this blank if you don't update your password.</small>
                                  </div>
                              </div>
                              <div class="col-12">
                                  <div class="form-group">
                                      <label class="formlets-font-weight-800 formlets-dark">New Password</label>
                                      <input <?php if($this->pl->isPreviewUser($this->lAccount)) {echo 'required';} ?> type="password" onkeyup="checkForm(this.form)" onchange="checkForm(this.form)" onblur="checkForm(this.form)" name="password" autocomplete="new-password" class="form-control bg-transparent" placeholder="" />
                                      <small class="form-text">Leave this blank if you don't update your password.</small>
                                  </div>
                              </div>
                          <?php } else { ?>
                              <div class="col-12">
                                  <div class="form-group">
                                      <label class="formlets-font-weight-800 formlets-dark">New Password</label>
                                      <input <?php if($this->pl->isPreviewUser($this->lAccount)) {echo 'required';} ?> type="password" onkeyup="checkForm(this.form)" onchange="checkForm(this.form)" onblur="checkForm(this.form)" name="password" autocomplete="new-password" class="form-control bg-transparent" placeholder="" />
                                  </div>
                              </div>
                          <?php } ?>
      						  <div class="col-12">
      							  <ul id="errors"></ul>
      						  </div>
                              <div class="col-6">
                                  <div class="form-group">
                                      <label class="formlets-font-weight-800 formlets-dark">Date Format</label>
                                      <select name="date_format" class="form-control">
          								<?php
          								foreach($this->dateFormats as $format){?>
          									<option <?php if($format == $user['dateformat']){ echo 'selected'; } ?>><?php echo $format ?></option>
          								<?php } ?>
          							</select>
                                  </div>
                              </div>
                              <div class="col-6">
                                  <div class="form-group">
                                      <label class="formlets-font-weight-800 formlets-dark d-block">Time Format</label>
                                      <div class="form-check-inline">
                                        <input class="form-check-input" type="radio" name="timeformat" value="1" <?php if($user['use12hr'] == 1){ echo 'checked'; } ?> id="timeformat1">
                                        <label class="form-check-label" for="timeformat1">
                                          12 Hour
                                        </label>
                                      </div>
                                      <div class="form-check-inline">
                                        <input class="form-check-input" type="radio" name="timeformat" value="0" <?php if($user['use12hr'] == 0){ echo 'checked'; } ?> id="timeformat0">
                                        <label class="form-check-label" for="timeformat0">
                                          24 Hour
                                        </label>
                                      </div>
                                  </div>
                              </div>

                              <div class="col-12">
                                  <div class="form-group">
                                        <label class="formlets-font-weight-800 formlets-dark">Time Zone</label>
                                        <select class="form-control" name="timezone">
                                            <option value="">Select time zone</option>
            								<?php
            								$timezones = DateTimeZone::listIdentifiers(DateTimeZone::ALL);
            								foreach($timezones as $tz){
            								?>
            									<option <?php if($tz == $user['timezone']){ echo 'selected'; } ?>><?php echo $tz ?></option>
            								<?php } ?>
                                        </select>
                                  </div>
                              </div>
                          </div>
                          <div class="row pb-4">
                              <h5 class="pl-2">Company Details</h5>
                                <div class="col-12">
                                    <div class="form-group">
                                        <label class="formlets-font-weight-800 formlets-dark">Company Name</label>
                                        <input type="text" class="form-control bg-transparent" name="companyName" value="<?php echo $this->lAccount['companyName']; ?>" <?php if($canUpdateCompany == false) { echo "readonly"; } ?> />
                                    </div>
                                </div>
                                <div class="col-12">
                                    By clicking the button below, you agree to our <a href="/terms/">Terms of Services</a> and <a href="/privacy/">Privacy Policy</a>
                                </div>
                          </div>
                        </div>
                            <?php if(!$this->pl->isPreviewUser($this->lAccount)) { ?>
                                <div class="row">
                                    <div class="col-12">
                                        <button name="infoUpdate" class="btn btn-info btn-sm rounded-3 px-5 float-sm-right mt-sm-0 mt-3"><?php echo $this->pl->trans($m,'Save'); ?></button>
                                    </div>
                                </div>
                            <?php } else { ?>
                                <div class="row">
                                    <div class="col-12">
                                        <button name="infoUpdate" class="btn btn-info btn-sm rounded-3 px-5 float-sm-right mt-sm-0 mt-3"><?php echo $this->pl->trans($m,'Continue'); ?></button>
                                    </div>
                                </div>
                            <?php } ?>
        	    		</form>
                    </div>
            </div>
		</div>
        <?php } ?>
        <script>
        	validate.init();
        </script>
	<?php
		    $GLOBALS['sess']['form_user_session']='';
			$this->_PasswordValidationScript();
		}


    if(isset($this->errorMessage)){?>
    <div class="error" style="text-align: center;color:red;">
      <span class="help"><?php echo $this->errorMessage;?></span>
    </div>
    <?php }

	?>

<?php
    $this->InsideCardWrapperClose();
	$this->OutputMarketingFooter();
}

function emailTemplatePreview() {
    $templateSelected = $this->lo->listTemplates(array("uid"=>$this->uid, "id"=>$this->urlpart[3]));
    $templateHTML = $GLOBALS['ref']['default_autoresponder'];
    if($templateSelected[0]['template']) {
        $templateHTML = $templateSelected[0]['template'];
    }

    echo $templateHTML;
}

function emailPreview() {

    if($_GET['iframe'] == 'true') {
        $this->emailTemplatePreview();exit;
    }

    $m = "email";

    $this->InsideHeader('email');

    $this->InsideCardWrapperOpen();
?>
    <div class="row mb-2">
        <div class="col-6">
            <a href="/email/<?php echo $this->urlpart[2]; ?>/<?php echo $this->urlpart[3]; ?>/edit/"><i class="fa fa-arrow-left"></i> Back to Editor</a>
        </div>
    </div>

    <iframe width="100%" height="800px" src="/email/<?php echo $this->urlpart[2]; ?>/<?php echo $this->urlpart[3]; ?>/preview/?iframe=true"></iframe>

    <div class="row mb-2">
        <div class="col-6">
            <a href="/email/<?php echo $this->urlpart[2]; ?>/<?php echo $this->urlpart[3]; ?>/edit/"><i class="fa fa-arrow-left"></i> Back to Editor</a>
        </div>
    </div>
<?php

    $this->InsideCardWrapperClose();

     $this->OutputMarketingFooter();
}

function OutPutEmail() {
	$m = "email";

	if($this->urlpart[2] && $this->urlpart[3] &&  $this->urlpart[4]== 'preview') {
	    $this->emailPreview();exit;
	}

	$forms=$this->lo->_listForms(array("uid"=>$this->lAccountOwner['_id']));
	$templates = $this->lo->listTemplates(array("uid"=>$this->lAccountOwner['_id']));

	$formSelected = null;
	if($this->urlpart[2]) {
		foreach($forms as $f) {
			if($f['_id'] == $this->urlpart[2]) {
				$formSelected = $f;
				break;
			}
		}
	}
	$templateSelected = null;
	if($this->urlpart[2] && $this->urlpart[3] &&  $this->urlpart[4]== 'edit') {
		$templateSelected = $this->lo->listTemplates(array("uid"=>$this->uid, "id"=>$this->urlpart[3]));
		if(count($templateSelected)) {
			$templateSelected = $templateSelected[0];

			$e_from = json_decode($templateSelected['email_from']);
			if(count($e_from)) {
			    $e_from = $e_from[0];
			} else {
			    $e_from=NULL;
			}

			if(!$e_from) {
			    $e_from = 'hello@formlets.com';
			}

			if($e_from) {
			    $email_verified = $this->checkSESEmailVerified($e_from);
                if($email_verified == 0) {
                    $this->pl->save_session('error_message', 'The address in the from field is not verified, please check your email '.$e_from.' and look for a subject "Amazon Web Services - ..." and confirm it');
                }
			}
		} else {
			$templateSelected = null;
		}
	}
	$this->InsideHeader('email');

    $this->InsideCardWrapperOpen();
?>
    <style>

    form label {
        margin-bottom: 0;
        margin-top:0.5rem;
    }
    </style>
    <form action="" method="POST" id="editorForm" onsubmit="return copyEditorContent()">
			<?php if($this->urlpart[2]=='') { ?>
				<div class="flush">
					<div class="gr g12">
						<div class="right">
							<a href="/email/new/" class="small greenbg btn btn-success"><?php echo $this->pl->trans($m,'Create New Autoresponder'); ?></a>
						</div>
					</div>
                    <br /><br />
					<?php if(count($templates)) { ?>

                        <div class="row Responses-Content">
                            <div class="col">
                                <table id="data-table" class="table table-striped" cellspacing="0" width="100%">
                                    <thead>
                                        <tr>
                                            <th width="30%" scope="col"><?php echo $this->pl->trans($m,'Name'); ?></th>
                                            <th width="30%" scope="col"><?php echo $this->pl->trans($m,'Form'); ?></th>
                                            <th width="20%" scope="col"><?php echo $this->pl->trans($m,'Subject'); ?></th>
                                            <th width="20%" scope="col"><?php echo $this->pl->trans($m,'Action'); ?></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        foreach($templates as $template) {
                                            $form = $this->lo->getForm(array('form_id'=>$template['form_id']));
                                            if($this->pl->canUser($this->lAccount, 'read', $form)) {
                                		?>
                                            <tr>
                                                <td data-label="Name"><a href="/email/<?php echo $template['form_id']; ?>/<?php echo $template['_id']; ?>/edit/"><?php echo stripslashes($template['name']) ?></a></td>
                                                <td data-label="Form"><a href="/editor/<?php echo $template['form_id']; ?>/#eelements"><?php echo stripslashes($template['form_name']) ?></td>
                                                <td data-label="Subject"><?php echo stripslashes($template['subject']) ?></td>
                                                <td data-label="Action">
                                                    <?php if($this->pl->canUser($this->lAccount, 'edit', $form)) { ?>
                	  									<?php if($template['notifySubmitter'] && $template['notifyUseTemplate']) { ?>
                	  										<a class="form-action" href="<?php echo $this->pl->set_csrfguard('/email/'.$template['form_id'].'/'.$template['_id'].'/disable/','disablemailtemplate');?>"><i class="fas fa-ban"></i> <?php echo $this->pl->trans($m,'Disable'); ?></a>
                	  									<?php } else { ?>
                	  										<a class="form-action" href="<?php echo $this->pl->set_csrfguard('/email/'.$template['form_id'].'/'.$template['_id'].'/enable/','enablemailtemplate');?>"><i class="fas fa-check"></i> <?php echo $this->pl->trans($m,'Enable'); ?></a>
                	  									<?php } ?>
                  									<?php } ?>
                  										<a class="form-action" href="/email/<?php echo $template['form_id']; ?>/<?php echo $template['_id']; ?>/edit/"><i class="fas fa-eye"></i> <?php echo $this->pl->trans($m,'View'); ?></a>
                                                        <?php if($this->pl->canUser($this->lAccount, 'delete', $form)) { ?>
                  										<a class="form-action delete" href="<?php echo $this->pl->set_csrfguard('/email/'.$template['form_id'].'/'.$template['_id'].'/delete/','deletemailtemplate');?>" onclick="return confirm('<?php echo $this->pl->trans($m,'Are you sure you want to delete that template'); ?>')"><i class="fas fa-times"></i> <?php echo $this->pl->trans($m,'Delete'); ?></a>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php
                                            }
                                		}

                                		?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
					<?php } else { ?>
                        <center>
    						<h3><?php echo $this->pl->trans($m,'No Autoresponders added yet.'); ?></h3>
                            <?php if($this->canAccess == false) { ?>
                                <?php echo $this->pl->trans($m,'Upgrade to a paying account to activate this functionality'); ?><br><br>
                                <img alt="autoresponder" width="800" src="../static/img/autoresponder.png">
                            <?php } ?>
                        </center>
					<?php } ?>
				</div>
		<?php } ?>
		<?php if($this->urlpart[2]<>'' && $this->urlpart[2]=='new') { ?>
		<div class="gr centered form-list-dashboard">
				<div class="flush">
					<div class="col-12">
                        <label><?php echo $this->pl->trans($m,'Select Form'); ?></label>
                        <select name="formselect" class="form-control">
                            <option></option>
                            <?php foreach($forms as $form) { ?>
                                <option value="<?php echo $form['_id']; ?>" <?php if($formSelected && $formSelected['_id'] == $form['_id']) echo "selected"; ?>><?php echo stripslashes($form['name']) ?></option>
                            <?php } ?>
                        </select>
				    </div>
				</div>
			</div>
		<?php } ?>
			<?php if($this->urlpart[2]<>'' && $this->urlpart[2]<>'new') { ?>
				<div class="row Autoresponders-Content">
					<div class="col-md-4 py-2 px-4 left-section">
            <div class="row">
              <div class="col-12">
                <label><?php echo $this->pl->trans($m,'Responder Name'); ?><span class="required">*</span></label>
                <input class="form-control" type="text" name="name" value="<?php echo $templateSelected ? $templateSelected['name']:'' ?>" />
              </div>
            </div>
            <div class="row">
              <div class="col-12">
                <label><?php echo $this->pl->trans($m,'Select Form'); ?><span class="required">*</span></label>
                <select name="form" class="form-control">
                  <option></option>
                  <?php foreach($forms as $form) { ?>
                    <option value="<?php echo $form['_id']; ?>" <?php if($formSelected && $formSelected['_id'] == $form['_id']) echo "selected"; ?>><?php echo $form['name']; ?></option>
                  <?php } ?>
                </select>
              </div>
            </div>
                        <div class="row">
                            <script>
                            function showHideCondition() {
                                if(document.getElementById('conditionBlock').style.display == 'block') {
                                    document.getElementById('conditionBlock').style.display = 'none';
                                } else {
                                    document.getElementById('conditionBlock').style.display = 'block';
                                }
                            }
                            </script>
							<div class="col-12">
								<br> <a href="javascript:;" onClick="showHideCondition()" style="color:#42A3B8;">Define send condition</a>
							</div>

                            <div class="col-12 my-3" id="conditionBlock" style="background:#fff;display:none;">
                                <div class="row">
                                    <div class="col-6">
                                        <label>Action</label>
                                        <select name="conditionAction" class="form-control">
                                            <option></option>
                                            <option value="send" <?php if($templateSelected && $templateSelected['conditionAction'] == 'send') echo "selected"; ?>>Send</option>
                                            <option value="dontsend" <?php if($templateSelected && $templateSelected['conditionAction'] == 'dontsend') echo "selected"; ?>>Do not Send</option>
                                        </select>
                                    </div>
                                    <div class="col-6">
                                        <label>When Field</label>
                                        <?php
                						if($formSelected) {
                							$elements = json_decode(str_replace('\\','',$formSelected['elements']), true);
                							if(!$elements) {
                								$elements = json_decode($formSelected['elements'], true);
                							}

                							usort($elements, function($a, $b) {
                    					  		if($a['order'] == $b['order']) {
                    					  			return 0;
                    					  		}

                    					  		return $a['order'] < $b['order'] ? -1:1;
                    					  	});
                                        }
                                        ?>
                                        <select name="conditionField" class="form-control">
                                            <option></option>
                                        <?php
                                        $opts = array();
                                        foreach($elements as $el) {
                                            $label = $el['queryName']?:$el['inputLabel'];
                                            if(!$label) { $label = $el['label']; }

                                            if($label) {
                                                if($el['type'] == 'NAME') {
                                                    $opts[] = $label;
            										if($el['nameTitle']) {
            											$opts[] = $label.' Title';
            										}
            										$opts[] = $label.' Firstname';
            										$opts[] = $label.' Lastname';
            										if($el['middleName']) {
            											$opts[] = $label.' Middlename';
            										}
            									} else if($el['type'] == 'US_ADDRESS') {
            										$opts[] = $label;
            										$opts[] = $label.' Address1';
            										$opts[] = $label.' Address2';
            										$opts[] = $label.' City';
            										$opts[] = $label.' State';
            										$opts[] = $label.' Zip';
            										if($el['country']) {
            											$opts[] = $label.' Country';
            										}
            									} else {
            										$opts[] = $label;
            									}
                                            }
                                        }

                                        foreach($opts as $opt) {
                                            if($templateSelected && $templateSelected['conditionField'] == $opt) {
                                                echo '<option selected>'.$opt.'</option>';
                                            } else {
                                                echo '<option>'.$opt.'</option>';
                                            }

                                        }
                                        ?>

                                        </select>
                                    </div>
                                    <div class="col-6 pb-3">
                                        <label>State</label>
                                            <select name="conditionOperand" class="form-control">
                                            <option></option>
                                            <option value="=" <?php if($templateSelected && $templateSelected['conditionOperand'] == '=') echo "selected"; ?>>Equals</option>
                                            <option value=">" <?php if($templateSelected && $templateSelected['conditionOperand'] == '>') echo "selected"; ?>>Greater than</option>
                                            <option value="<" <?php if($templateSelected && $templateSelected['conditionOperand'] == '<') echo "selected"; ?>>Lesser than</option>
                                            <option value="!=" <?php if($templateSelected && $templateSelected['conditionOperand'] == '!=') echo "selected"; ?>>Not equal</option>
                                            <option value=">=" <?php if($templateSelected && $templateSelected['conditionOperand'] == '>=') echo "selected"; ?>>Greater than or equal</option>
                                            <option value="<=" <?php if($templateSelected && $templateSelected['conditionOperand'] == '<=') echo "selected"; ?>>Lesser than or equal</option>
                                        </select>
                                    </div>
                                    <div class="col-6 pb-3">
                                        <label>Value</label>
                                        <input type="text" class="form-control" name="conditionValue" value="<?php echo $templateSelected ? $templateSelected['conditionValue']:'' ?>" />
                                    </div>
                                </div>
                            </div>
						</div>

						<?php
						if($formSelected) {
						?>
						<div style="margin-bottom: 25px">
                            <div class="field f_submission"><span class="f"><?php echo $this->pl->trans($m,'SUBMISSION ID'); ?></span></div>
                            <div class="field f_submission_date"><span class="f"><?php echo $this->pl->trans($m,'SUBMISSION DATE'); ?></span></div>
							<div class="field f_submission_subject"><span class="f"><?php echo $this->pl->trans($m,'SUBMISSION SUBJECT'); ?></span></div>
						</div>
						<br /><br />
						<?php
						}
						?>

						<label><?php echo $this->pl->trans($m,'Form Fields'); ?></label>

						<div class="fieldContainer" id="fieldContainer">
						<?php
						if($formSelected) {
							$elements = json_decode(str_replace('\\','',$formSelected['elements']), true);
							if(!$elements) {
								$elements = json_decode($formSelected['elements'], true);
							}

							usort($elements, function($a, $b) {
					  		if($a['order'] == $b['order']) {
					  			return 0;
					  		}

					  		return $a['order'] < $b['order'] ? -1:1;
					  	});

							foreach($elements as $el) {
								if($el['inputLabel'] || $el['queryName'] || $el['label']) {
									$label = $el['queryName']?:$el['inputLabel'];
                                    if(!$label) { $label = $el['label']; }
									if($el['type'] == 'NAME') {
										echo '<div class="field"><span class="f">'.$label.'</span></div>';
										if($el['nameTitle']) {
											echo '<div class="field"><span class="f">'.$label.' Title</span></div>';
										}
										echo '<div class="field"><span class="f">'.$label.' Firstname</span></div>';
										echo '<div class="field"><span class="f">'.$label.' Lastname</span></div>';
										if($el['middleName']) {
											echo '<div class="field"><span class="f">'.$label.' Middlename</span></div>';
										}
									} else if($el['type'] == 'US_ADDRESS') {
										echo '<div class="field"><span class="f">'.$label.'</span></div>';
										echo '<div class="field"><span class="f">'.$label.' Address1</span></div>';
										echo '<div class="field"><span class="f">'.$label.' Address2</span></div>';
										echo '<div class="field"><span class="f">'.$label.' City</span></div>';
										echo '<div class="field"><span class="f">'.$label.' State</span></div>';
										echo '<div class="field"><span class="f">'.$label.' Zip</span></div>';
										if($el['country']) {
											echo '<div class="field"><span class="f">'.$label.' Country</span></div>';
										}
									} else {
										echo '<div class="field"><span class="f">'.$label.'</span></div>';

										$form_field=$this->lo->getFormElement(array(
                                            'user_id'=>$this->lAccountOwner['_id'],
                                            'form_id'=>$formSelected['_id'],
                                            'element_id'=>$el['_id']
                                        ));

										if(!empty($form_field['columns']) && count($form_field['columns']) > 2) {
										    $link = $this->lo->getDatasourcelink(array('formId'=>$formSelected['_id'], 'elementId'=>$el['_id']));
										    if(count($link)) {
                                                $datasource = $this->lo->getDatasource(array('id'=>$link[0]['datasourceId']));
                                                $datasource = $datasource[0];
                                                $columns = json_decode($datasource['columns'], true);
                                                for($x=2;$x<count($columns);$x++) {
                                                    echo '<div class="field"><span class="f">'.$columns[$x].'</span></div>';
                                                }
										    }
										}
									}
								} else if($el['type'] == 'PAYPAL' || $el['type'] == 'STRIPE' || $el['type'] == 'STRIPEPAYPAL') {
							?>
								<div class="field"><span class="f">Total</span></div>
							<?php
								}
							}
						} else {
							echo $this->pl->trans($m,'Please select form above');
						}
						?>
						</div>
					</div>
					<div class="col-md-8 py-2 px-4 right-section">
					    <?php if($this->urlpart[4] == 'edit') { ?>
<!--					    <div>-->
<!--                            <a href="/email/--><?php //echo $this->urlpart[2]; ?><!--/--><?php //echo $this->urlpart[3]; ?><!--/template/"><i class="fa fa-edit"></i> Edit Template</a>-->
<!--                            <a href="/email/--><?php //echo $this->urlpart[2]; ?><!--/--><?php //echo $this->urlpart[3]; ?><!--/preview/" style="margin-left: 15px"><i class="fa fa-search"></i> Preview Template</a>-->
<!--					    </div>-->
					    <?php } ?>
					<?php
					if($formSelected) {
						$email_from = array();
						if($templateSelected) {
							$email_from = json_decode($templateSelected['email_from']);
                            $email_replyTo = json_decode($templateSelected['email_reply_to']);
						}
						if(count($email_from)) {
							$email_from = implode(', ', $email_from);
						}
                        if(count($email_replyTo)) {
							$email_replyTo = implode(', ', $email_replyTo);
						}
					}

					$email_from= $templateSelected ? $email_from:'hello@formlets.com';

					if (filter_var($email_from, FILTER_VALIDATE_EMAIL) == false) {
                        $email_from = 'hello@formlets.com';
                    }

					if($GLOBALS['sess']['old_input']) {
						$email_from = $GLOBALS['sess']['old_input']['emailfrom'];
						$email_replyTo = $GLOBALS['sess']['old_input']['emailReplyTo'];
					}
					?>
						<div class="row">
							<div class="col-12">
								<label><?php echo $this->pl->trans($m,'From'); ?> <span class="required">*</span> (Amazon Mail service will request authorization for sending in name of the email you add here, don't put in emails you don't control)</label>
								<div class="editor" id="emailfrom" contenteditable="true"><?php echo $email_from ?></div>
								<input class="form-control" type="hidden" name="emailfrom" id="emailfromHidden" value="<?php echo $email_replyTo ?>" />
							</div>
                            <div class="col-12">
								<label><?php echo $this->pl->trans($m,'Reply to'); ?></label>
								<div class="editor" id="emailReplyTo" contenteditable="true"><?php echo $email_replyTo ?></div>
								<input class="form-control" type="hidden" name="emailReplyTo" id="emailReplyToHidden" value="<?php echo $email_replyTo ?>" />
							</div>
						</div>
						<?php
					if($formSelected) {
						$email_to = array();
						if($templateSelected) {
							$email_to = json_decode($templateSelected['email_to']);
						}
						if(count($email_to)) {
							$tos = implode(', ', $email_to);
						}
					}

					$tos= $templateSelected ? $tos:'';

					if($GLOBALS['sess']['old_input']) {
						$tos = $GLOBALS['sess']['old_input']['recipient'];
					}
					?>
						<div class="row">
							<div class="col-12">
								<label><?php echo $this->pl->trans($m,'To (separated by comma'); ?>) <span class="required">*</span></label>
								<div class="editor" id="recipient" contenteditable="true"><?php echo $tos ?></div>
								<input class="form-control" type="hidden" name="recipient" id="recipientHidden" value="<?php echo $tos ?>" />
							</div>
						</div>
						<?php
					if($formSelected) {
						$email_cc = array();
						if($templateSelected) {
							$email_cc = json_decode($templateSelected['email_cc']);
						}
						if(count($email_cc)) {
							$ccs = implode(', ', $email_cc);
						}
					}
					$ccs= $templateSelected ? $ccs:'';
					if($GLOBALS['sess']['old_input']) {
						$ccs = $GLOBALS['sess']['old_input']['cc'];
					}
					?>
						<div class="row">
							<div class="col-6">
								<label><?php echo $this->pl->trans($m,'CC (separated by comma'); ?>) </label>

								<div class="editor" id="cc" contenteditable="true"><?php echo $ccs ?></div>
								<input class="form-control" type="hidden" name="cc" id="ccHidden" value="<?php echo $ccs ?>" />

							</div>
                    <?php
					if($formSelected) {
						$email_bcc = array();
						if($templateSelected) {
							$email_bcc = json_decode($templateSelected['email_bcc']);
						}
						if(count($email_bcc)) {
							$bccs = implode(', ', $email_bcc);
						}
					}
					$bccs= $templateSelected ? $bccs:'';
					if($GLOBALS['sess']['old_input']) {
						$bccs = $GLOBALS['sess']['old_input']['bcc'];
					}
					?>
							<div class="col-6">
								<label><?php echo $this->pl->trans($m,'BCC (separated by comma'); ?>) </label>

								<div class="editor" id="bcc" contenteditable="true"><?php echo $bccs ?></div>
								<input class="form-control" type="hidden" name="bcc" id="bccHidden" value="<?php echo $bccs ?>" />

							</div>
						</div>
						<?php
						$subject = $templateSelected ? $templateSelected['subject']:'';
						if($GLOBALS['sess']['old_input']) {
						$subject = $GLOBALS['sess']['old_input']['subject'];
					}
						?>
						<div class="row">
							<div class="col-12">
								<label><?php echo $this->pl->trans($m,'Subject'); ?><span class="required">*</span></label>
								<div class="editor" id="subject" contenteditable="true"><?php echo $subject ?></div>
								<input class="form-control" type="hidden" name="subject" id="subjectHidden" value="<?php echo $subject ?>" />
							</div>
						</div>
						<label><?php echo $this->pl->trans($m,'Body'); ?><span class="required">*</span></label>
						<div class="btn-toolbar" data-role="editor-toolbar" data-target="#editor">
				      <div class="btn-group">
				        <a class="btn dropdown-toggle" data-toggle="dropdown" title="" data-original-title="Font"><i class="fa fa-font"></i></a>
				          <ul class="dropdown-menu">
				          <li><a data-edit="fontName Serif" style="font-family:'Serif'">Serif</a></li><li><a data-edit="fontName Sans" style="font-family:'Sans'">Sans</a></li><li><a data-edit="fontName Arial" style="font-family:'Arial'">Arial</a></li><li><a data-edit="fontName Arial Black" style="font-family:'Arial Black'">Arial Black</a></li><li><a data-edit="fontName Courier" style="font-family:'Courier'">Courier</a></li><li><a data-edit="fontName Courier New" style="font-family:'Courier New'">Courier New</a></li><li><a data-edit="fontName Comic Sans MS" style="font-family:'Comic Sans MS'">Comic Sans MS</a></li><li><a data-edit="fontName Helvetica" style="font-family:'Helvetica'">Helvetica</a></li><li><a data-edit="fontName Impact" style="font-family:'Impact'">Impact</a></li><li><a data-edit="fontName Lucida Grande" style="font-family:'Lucida Grande'">Lucida Grande</a></li><li><a data-edit="fontName Lucida Sans" style="font-family:'Lucida Sans'">Lucida Sans</a></li><li><a data-edit="fontName Tahoma" style="font-family:'Tahoma'">Tahoma</a></li><li><a data-edit="fontName Times" style="font-family:'Times'">Times</a></li><li><a data-edit="fontName Times New Roman" style="font-family:'Times New Roman'">Times New Roman</a></li><li><a data-edit="fontName Verdana" style="font-family:'Verdana'">Verdana</a></li></ul>
				        </div>
				      <div class="btn-group">
				        <a class="btn dropdown-toggle" data-toggle="dropdown" title="" data-original-title="Font Size"><i class="fa fa-text-height"></i>&nbsp;</a>
				          <ul class="dropdown-menu">
				          <li><a data-edit="fontSize 5"><font size="5"><?php echo $this->pl->trans($m,'Huge'); ?></font></a></li>
				          <li><a data-edit="fontSize 3"><font size="3"><?php echo $this->pl->trans($m,'Normal'); ?></font></a></li>
				          <li><a data-edit="fontSize 1"><font size="1"><?php echo $this->pl->trans($m,'Small'); ?></font></a></li>
				          </ul>
				      </div>
				      <div class="btn-group">
				        <a class="btn" data-edit="bold" title="" data-original-title="Bold (Ctrl/Cmd+B)"><i class="fa fa-bold"></i></a>
				        <a class="btn" data-edit="italic" title="" data-original-title="Italic (Ctrl/Cmd+I)"><i class="fa fa-italic"></i></a>
				        <a class="btn" data-edit="strikethrough" title="" data-original-title="Strikethrough"><i class="fa fa-strikethrough"></i></a>
				        <a class="btn" data-edit="underline" title="" data-original-title="Underline (Ctrl/Cmd+U)"><i class="fa fa-underline"></i></a>
				      </div>
				      <div class="btn-group">
				        <a class="btn btn-info" data-edit="justifyleft" title="" data-original-title="Align Left (Ctrl/Cmd+L)"><i class="fa fa-align-left"></i></a>
				        <a class="btn" data-edit="justifycenter" title="" data-original-title="Center (Ctrl/Cmd+E)"><i class="fa fa-align-center"></i></a>
				        <a class="btn" data-edit="justifyright" title="" data-original-title="Align Right (Ctrl/Cmd+R)"><i class="fa fa-align-right"></i></a>
				        <a class="btn" data-edit="justifyfull" title="" data-original-title="Justify (Ctrl/Cmd+J)"><i class="fa fa-align-justify"></i></a>
				      </div>
				      <div class="btn-group">
						  <a class="btn dropdown-toggle" data-toggle="dropdown" title="" data-original-title="Hyperlink"><i class="fa fa-link"></i></a>
						    <div class="dropdown-menu input-append">
							    <input class="span2" placeholder="URL" type="text" data-edit="createLink">
							    <button class="btn" type="button">Add</button>
				        </div>
				        <a class="btn" data-edit="unlink" title="" data-original-title="Remove Hyperlink"><i class="fa fa-cut"></i></a>
				      </div>
              <div class="btn-group btn-actions" style="position: absolute;right:20px">
                <a class="btn" href="javascript:;" id="html_editor"><i class="fa fa-code"></i> HTML</a>
                <a class="btn" href="javascript:;" id="simple_editor" style="display:none;"><i class="fas fa-align-justify"></i> Simple</a>
                <?php if($templateSelected) { ?>
                    <a href="/email/<?php echo $this->urlpart[2]; ?>/<?php echo $this->urlpart[3]; ?>/preview/" class="btn"><i class="fa fa-search"></i> Preview</a>
                <?php } ?>
              </div>
				      <input type="text" data-edit="inserttext" id="voiceBtn" x-webkit-speech="" style="display: none;">
				    </div>
				    <?php
						$templateHTML = $templateSelected ? $templateSelected['template']:$GLOBALS['ref']['default_autoresponder'];
						if($GLOBALS['sess']['old_input']) {
						    $template = $GLOBALS['sess']['old_input']['template'];
					    }

                        if (preg_match('~<body[^>]*>(.*?)</body>~si', $templateHTML, $body)) {
                            $template = $body[1];
                        }
					?>
						<div id="editor" class="editor" contenteditable="true" style="margin-bottom: 50px"><?php echo $template ?></div>
                        <textarea name="template" id="editorHidden" style="display:none;"></textarea>
						<textarea name="templateHTML" id="editorHTMLHidden" class="form-control" style="height:260px !important;display:none;margin-bottom: 50px;margin-top: 40px;"><?php echo $templateHTML ?></textarea>
					</div>
				</div>
            <?php if($this->pl->canUser($this->lAccount, 'edit', $formSelected)) { ?>
					<div class="right">
						<button type="submit" class="btn btn-success"><?php echo $this->pl->trans($m,'Save Responder'); ?></button>
					</div>
            <?php } ?>
			<?php } ?>
    </form>
  	<script type="text/javascript" src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js"></script>
  	<script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/jquery.hotkeys.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
  	<script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/email-template.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
  	<script type="text/javascript">

  		$(document).ready(function() {
  			$('#editor').wysiwyg();
  		});
  	</script>
<?php

    $this->InsideCardWrapperClose();

     $this->OutputMarketingFooter();
}

function OutputUsage() {
    $m = "usage";

    $yearMonth = date("Ym");

    $forms=$this->lo->listFormUsage(array("uid"=>$this->lAccountOwner['_id'], "accountId"=>$this->lAccount['_id'], "yearMonth"=>$yearMonth));

    if($this->urlpart[3]) {
        $this->OutputUsageDetails();exit;
    }

    $this->InsideHeader();

    $this->InsideCardWrapperOpen();

    $this->InsidesettingsHeader('account');
?>
<br>
<h3>Form usage for this month</h3>
<div class="centered form-list-dashboard">
    <table id="data-table" class="table table-striped" width="100%">
        <thead>
            <tr>
                <th align="left" scope="col">Form Name</th>
                <th style="text-align:right" scope="col">Views</th>
                <th style="text-align:right" scope="col">Response</th>
            </tr>
        </thead>
        <tbody>
            <?php $totViews=0;$totResponse=0; foreach($forms as $form) { ?>
                <tr>
                    <td data-label="Form Name"><a href="/settings/usage/<?php echo $form['_id']; ?>/"><?php echo $form['name']; ?></a></td>
                    <td style="text-align:right" data-label="Views"><?php echo $form['pageViewCount']; ?></td>
                    <td style="text-align:right" data-label="Responses"><?php echo $form['responseCount']; ?></td>
                </tr>
            <?php $totViews+=$form['pageViewCount']; $totResponse+=$form['responseCount']; } ?>
        </tbody>
        <thead>
            <tr>
                <th align="left">Total</th>
                <th style="text-align:right"><?php echo $totViews; ?></th>
                <th style="text-align:right"><?php echo $totResponse; ?></th>
            </tr>
        </thead>
    </table>
</div>
<?php
$this->InsideCardWrapperClose();
$this->OutputMarketingFooter();
}

function OutputUsageDetails() {
    $this->InsideHeader();

    $this->InsideCardWrapperOpen();

    $this->InsidesettingsHeader('account');
    $formid = $this->urlpart[3];
    $usages = $this->lo->listFormUsage(array(
        'accountId'=>$this->lAccount['_id'],
        'uid'=>$this->lUser['_id'],
        'formId'=>$formid
    ));

    if(count($usages)) {
        $formname = $usages[0]['name'];
    } else {
        $form = $this->lo->getForm(array('form_id'=>$formid));
        $formname = $form['name'];
    }

?>
<br>
<h3>Usage history for <strong><?php echo $formname; ?></strong></h3>
    <div class="gr centered form-list-dashboard">
        <?php if(count($usages)) { ?>
            <table id="data-table" class="table" width="100%">
                <thead>
                    <tr>
                        <th align="left">Year and Month</th>
                        <th style="text-align:right">Views</th>
                        <th style="text-align:right">Response</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $totViews=0;$totResponse=0; foreach($usages as $usage) {
                    $date = DateTime::createFromFormat('Ym', $usage['yearMonth'])->getTimestamp();
                    $formatted = date('Y-m', $date);
                ?>
                    <tr>
                        <td><?php echo $formatted; ?></td>
                        <td style="text-align:right"><?php echo $usage['pageViewCount']; ?></td>
                        <td style="text-align:right"><?php echo $usage['responseCount']; ?></td>
                    </tr>
                <?php $totViews+=$usage['pageViewCount']; $totResponse+=$usage['responseCount']; } ?>
                </tbody>
                <thead>
                    <tr>
                        <th align="left">Total</th>
                        <th style="text-align:right"><?php echo $totViews; ?></th>
                        <th style="text-align:right"><?php echo $totResponse; ?></th>
                    </tr>
                </thead>
            </table>
    <?php } else {
    } ?>
    </div>
<?php
    $this->InsideCardWrapperClose();
    $this->OutputMarketingFooter();
}


function OutputSubscription(){
	$m = "subscription";

	if(!$this->pl->canUser($this->lAccount, 'manage_account')) {
		exit;
	}

    $this->InsideHeader();

    $this->InsideCardWrapperOpen();

    $this->InsidesettingsHeader('account');

/*      print_r($this->lUser);
*/
?>

<div class="centered form-list-dashboard container" id="pricing" style="position: relative;">
<?php
$cs=array('USD'=>'$','EUR'=>'€');
$ms=array('MONTHLY'=>'/month','YEARLY'=>'/year');

if(!$this->pl->isFreeAccount($this->lAccount)) {
    $cur=$this->account['cur'];
    $mode=$this->account['interval'];
    if(!$cur){$cur="USD";}
    if(!$mode){$mode="MONTHLY";}

    if($ms[$_GET['mode']]){$mode=$_GET['mode'];}
} else {
    if($cs[$_GET['cur']]){$cur=$_GET['cur'];}
    if($ms[$_GET['mode']]){$mode=$_GET['mode'];}

    if(!$cur){$cur="USD";}
    if(!$mode){$mode="MONTHLY";}
}

$usLink = '/settings/subscription/?cur=USD&mode='.$mode.'&view=true';
$eurLink = '/settings/subscription/?cur=EUR&mode='.$mode.'&view=true';
$modeM = '/settings/subscription/?cur='.$cur.'&mode=MONTHLY&view=true';
$modeY = '/settings/subscription/?cur='.$cur.'&mode=YEARLY&view=true';
?>

<script>
function changeMode() {
    <?php if($mode == 'MONTHLY') { ?>
    var link = '<?php echo $modeY; ?>';
    <?php } else { ?>
    var link = '<?php echo $modeM; ?>';
    <?php } ?>

    window.location.href=link;
}

function changeCur() {
    <?php if($cur == 'USD') { ?>
    var link = '<?php echo $eurLink; ?>';
    <?php } else { ?>
    var link = '<?php echo $usLink; ?>';
    <?php } ?>

    window.location.href=link;
}
</script>

		<div>
            <div class="upgrade row">
                <div class="col-sm-6 center">
					<ul class="plan-current">
	              	<?php if($this->pl->isFreeAccount($this->lAccount)){?>
	                    <li>
	                    	<h1><?php echo $this->pl->trans($m,'Current Plan: Free')."</h1>".$this->pl->trans($m,'Upgrade now to unlock new features!'); ?>
                            <?php if($this->subscriptionStatus) { echo '<strong>('.strtoupper(str_replace("_", " ", $this->subscriptionStatus)).')</strong>'; } ?>
	                    </li>
	              	<?php } else { ?>
						<li>
							<h1><?php echo $this->pl->trans($m,'Current Plan:'); ?> <?php echo $this->account['name'];?> (<?php echo $this->account['interval'];?>)</h1>
                            <?php if($this->subscriptionStatus && ($this->subscriptionStatus=='unpaid' || $this->subscriptionStatus=='past_due')) { ?>
                                <span style="color: red">Your account payment is paste due. Maybe your Credit card has expired, please update your credit card details to keep your account active</span>
                            <?php } else { ?>
                                Your subscription will expire on <?php echo date('m/d/Y H:i', strtotime($this->lAccount['planExpiration']));?>
                            <?php } ?>
                        </li>
						<?php if($this->lAccount['ccBrand'] && $this->lAccount['ccLast4']){ ?>
						<li>
							<h6>
								<?php echo $this->pl->trans($m,'Your Card details:'); ?> <i class="<?php echo $this->ccIcon($this->lAccount['ccBrand']) ?>" style="font-size:25px;"></i> <?php echo $this->lAccount['ccBrand'] . ' x-' . $this->lAccount['ccLast4']; ?>
								<a href="<?php echo $GLOBALS['level'];?>settings/subscription/change/card/"><?php echo $this->pl->trans($m,'Change'); ?></a>
							</h6>
						</li>
						<?php } ?>
	          		<?php } ?>
					</ul>
				</div>
                <div class="col-sm-6 center">
                    <div class="col-lg pricing-header">
                      <img src="/static/img/moneyback.png" alt="">
                      <p>
                        We offer a <strong>full refund</strong> if you wish to cancel in the <strong>first 30 days</strong> of your new plan <br>
                        You are free to cancel at any time when you see fit. By downgrading back to the free personal plan, all your forms will be kept <br>
                        The Personal plan is Free forever
                      </p>
                    </div>
                </div>
            </div>
			<div class="row">
                 <div class="pricing-table" style="width:100%;">
                     <div class="pricing-switch center">
                       <ul class="inline">
                         <li>Pay yearly:</li>
                         <li>
                           <form>
                             <input id="mode" class="switch" type="checkbox" <?php if($mode == 'YEARLY'){ echo 'checked';} ?> onchange="changeMode()">
                             <label for="mode" class="switch"></label>
                           </form>
                         </li>
                       </ul>
                       <?php if($this->pl->isFreeAccount($this->lAccount)){?>
                       <ul class="inline">
                         <li>Pay in Euro:</li>
                         <li>
                             <form>
                             <input id="cur" class="switch" type="checkbox" <?php if($cur == 'EUR'){ echo 'checked';} ?> onchange="changeCur()">
                             <label for="cur" class="switch"></label>
                             </form>
                         </li>
                       </ul>
                       <?php } ?>
                     </div>
                     <ul class="pricelist">
                         <?php
                         for ($a=0;$a<count($this->availableplans["list"]);$a++){
                             $thelist=$this->availableplans["list"][$a];

                             if($thelist['plan']<>'FREE') {
                                 if($cur=='USD'){
                                   $theplan=$thelist['plan']."-".$mode."-OXOPIA";
                                 } else {
                                   $theplan=$thelist['plan']."-".$mode."-OXOPIA-".$cur;
                                 }
                             } else {
                                 $theplan="FREE";
                             }

                             if($thelist['status']=="active"){
                         ?>
                             <li class="price-item" <?php if($thelist['display'] == false) { echo 'style="display:none;"'; } ?>>
                               <span>
                                 <?php echo $thelist['name'];?>
                                 <?php if($thelist['plan']=="FREE"){?>
                                     <div class="prices">
                                       <span class="usd monthly">Free</span>
                                     </div>
                                 <?php } else { ?>
                                     <div class="prices">
                                       <span class="usd monthly"><?php echo $cs[$cur].$thelist['stripe_plans'][$cur][$mode]; ?></span>
                                       <em class="monthly"><?php echo $ms[$mode]; ?></em>
                                     </div>
                                 <?php } ?>
                               </span>

                               <?php
                               if (strtolower($this->account['plan']) == strtolower($thelist['plan'])) {
                                   if($cs[$cur].$thelist['stripe_plans'][$cur][$mode].$ms[$mode] != $cs[$this->account['cur']].$thelist['stripe_plans'][$this->account['cur']][$this->account['interval']].$ms[$this->account['interval']] && $thelist['plan'] != "FREE") {
                               ?>
                                    <a class="price-btn subscribed"><i class="icon-check-thick"></i>Subscribed to <?php echo $cs[$this->account['cur']].$thelist['stripe_plans'][$this->account['cur']][$this->account['interval']].$ms[$this->account['interval']]; ?></a>
                               <?php
                                   } else {
                               ?>
                                    <a class="price-btn subscribed"><i class="icon-check-thick"></i>Subscribed</a>
                               <?php
                                   }
                               } else {
                                   if ((strtolower($this->account['plan'])!=strtolower($thelist['plan']))||(strtolower($this->account['interval']) != strtolower($mode))){ ?>
                                       <?php if($this->lAccount["stripeCustomerId"]){ ?>
                                           <a class="price-btn" onclick="return confirm('Are you sure you want to change your current plan?')" href="<?php echo $GLOBALS['level'];?>settings/subscription/change/<?php echo $a;?>/?cur=<?php echo $cur; ?>&mode=<?php echo $mode; ?>">
                                               <?php echo $this->pl->trans($m,'Change to'); ?> <?php echo $thelist['name'];?>
                                           </a>
                                       <?php } elseif($thelist['plan'] <> "FREE"){ ?>
                                           <a class="price-btn" href="<?php echo $GLOBALS['level'];?>settings/subscription/change/<?php echo $a;?>/?cur=<?php echo $cur; ?>&mode=<?php echo $mode; ?>">
                                               <?php echo $this->pl->trans($m,'Change to'); ?> <?php if($this->account['plan'] == $thelist['plan']){echo $mode;} else {echo $thelist['name'];}?>
                                           </a>
                                       <?php } elseif($thelist['plan'] == "FREE") { ?>
                                           <a class="price-btn" href="<?php echo $GLOBALS['level'];?>settings/subscription/change/<?php echo $a;?>/?cur=<?php echo $cur; ?>&mode=<?php echo $mode; ?>">
                                               <?php echo $this->pl->trans($m,'Change to'); ?> <?php echo $thelist['name']; ?>
                                           </a>
                                       <?php } ?>
                                   <?php } ?>
                               <?php } ?>
                               <ul>
                                   <?php foreach($thelist['descr'] as $descr){?>
                                       <li><?php echo $descr; ?></li>
                                   <?php } ?>
                               </ul>

                             </li>
                             <?php
                             }
                         }
                         ?>
                     </ul>
                </div>
             </div>

				</div>
<?php if ($this->urlpart[3]=='change' && $this->urlpart[4] && !$_GET['success']=='y'){$plid=$this->urlpart[4];?>

<?php if(($plid<>"card" && $this->availableplans["list"][$plid]['price_usd']<>"Free") || $plid=="card"){?>
    <style>
    /**
     * The CSS shown here will not be introduced in the Quickstart guide, but shows
     * how you can use CSS to style your Element's container.
     */
    .StripeElement {
      background-color: white;
      height: 40px;
      padding: 10px 12px;
      border-radius: 4px;
      border: 0px !important;
      border-color: #cececf;
      box-shadow: 0 0 0 1px #d9d9d9;
      -webkit-transition: box-shadow 150ms ease;
      transition: box-shadow 150ms ease;
      min-width: 180px;
    }

    .StripeElement:focus {
        box-shadow: 0 0 0 1px #4baec2;
        outline: none;
    }

    .StripeElement--focus {
      //box-shadow: 0 0px 1px 0 #000;
      box-shadow: 0 0 0 1px #4baec2;
    }

    .StripeElement--invalid {
        border: 1px solid #fa755a !important;
      border-color: #fa755a;
      box-shadow: none;
    }

    .StripeElement--webkit-autofill {
      background-color: #fefde5 !important;
    }

    .field {
        color: #32325d;
        line-height: 24px;
        font-family: "Helvetica Neue", Helvetica, sans-serif;
        -webkit-font-smoothing: antialiased;
        font-size: 16px;
    }

    .field::placeholder{
        color: #aab7c4
    }

    #payment-form .gr{
        margin: 10px 0px;
    }

    #card-errors {
        color:#fa755a;
    }

    #payment-form label span {
        margin-bottom: 2px;
    }
    </style>
	<div class="backdrop">&nbsp;</div>
        <div id="paymentContainer">
        	<div class="close_payment"><a href="/settings/subscription/"><i class="fa fa-close"></i></a></div>
	        <?php if($plid<>"card"){ ?>
		        <div class="center row" style="background: #fff;">
				     <div class="col-12" style="background-color: #9cc12f;color:white;padding:10px" class="green small span">Change plan to <span><?php echo $this->availableplans["list"][$plid]['name'];?> <?php echo $cur." ".$mode;?></span></div>
				</div>
			<?php } else { ?>
				<div class="center row" style="background: #fff;">
				     <div class="col-12" style="background-color: #9cc12f;color:white;padding:10px" class="green small span"><?php echo $this->pl->trans($m,'Change Card Details'); ?></div>
				</div>
			<?php } ?>
	        <div id="paymentDetails" class="el pad-double">
                  <script src="https://js.stripe.com/v3/"></script>
	              <form action="<?php echo $_SERVER["REQUEST_URI"];?>&st=ok" method="POST" id="payment-form">
                      <input type="hidden" id="publishable_key" value="<?php echo $GLOBALS["conf"]["stripe_publishable_key"]; ?>">
                      <div class="">
                          <div class="row">
                              <div class="col-12">
                                  <label>
                                      <span>Name on Card</span>
                                      <input name="cardholder-name" autocomplete="cc-name" class="field StripeElement" style="margin-top:3px;" placeholder="Jane Doe" />
                                  </label>
                              </div>
                          </div>
                          <div class="row">
                              <div class="col-12">
                                  <label for="card-element">
                                    Credit or debit card
                                  </label>
                                  <div id="card-element">
                                    <!-- a Stripe Element will be inserted here. -->
                                  </div>
                              </div>
                          </div>
                          <div class="row" style="margin-top:10px;">
                              <div class="col-4">
                                  <label>
                                      <span>Expiry</span>
                                      <div id="expiry-element">
                                          <!-- a Stripe Element will be inserted here. -->
                                      </div>
                                  </label>
                              </div>
                              <div class="col-4">
                                  <label>
                                      <span>CVC</span>
                                      <div id="code-element">
                                          <!-- a Stripe Element will be inserted here. -->
                                      </div>
                                  </label>
                              </div>

                              <div class="col-4">
                                  <label>
                                      <span>Postal Code</span>
                                      <div id="zip-element">
                                          <!-- a Stripe Element will be inserted here. -->
                                      </div>
                                  </label>
                              </div>
                          </div>
                          <?php if($plid<>"card" && isset($_GET['coupon'])){ ?>
                              <input type="hidden" value="<?php echo $_GET['coupon']; ?>" name="coupon-code" autocomplete="cc-coupon" class="field StripeElement" style="margin-top:3px;" />
                          <?php } ?>
                          <!-- Used to display form errors -->
                          <div class="row">
                              <div class="col-12">
                                  <div id="card-errors" role="alert"></div>
                              </div>
                          </div>
                          <div class="row" style="margin-top:30px;">
                              <div class="col-12">
                              <?php if($plid<>"card"){ ?>
                                  <button class="submit"><?php echo $this->pl->trans($m,'Change Plan to'); ?> <?php echo ($this->availableplans["list"][$plid]['name']);?></button>
                              <?php } else { ?>
                                  <button name="changeCard" class="submit"><?php echo $this->pl->trans($m,'Update'); ?></button>
                              <?php } ?>
                              </div>
                          </div>
                      </div>
	            </form>
	        </div>
        </div>
    </div>

<script>
// Create a Stripe client
var pKey = document.getElementById('publishable_key').value;
var stripe = Stripe(pKey);

// Create an instance of Elements
var elements = stripe.elements();

// Custom styling can be passed to options when creating an Element.
// (Note that this demo uses a wider set of styles than the guide below.)
var style = {
  base: {
    color: '#32325d',
    lineHeight: '18px',
    fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
    fontSmoothing: 'antialiased',
    fontSize: '16px',
    '::placeholder': {
      color: '#aab7c4'
    }
  },
  invalid: {
    color: '#fa755a',
    iconColor: '#fa755a'
  }
};

// Create an instance of the card Element
var card = elements.create('cardNumber', {style: style});
var expiry = elements.create('cardExpiry', {style: style});
var code = elements.create('cardCvc', {style: style});
var zip = elements.create('postalCode', {style: style});

// Add an instance of the card Element into the `card-element` <div>
card.mount('#card-element');
expiry.mount('#expiry-element');
code.mount('#code-element');
zip.mount('#zip-element');

// Handle real-time validation errors from the card Element.
card.addEventListener('change', function(event) {
  var displayError = document.getElementById('card-errors');
  if (event.error) {
    displayError.textContent = event.error.message;
  } else {
    displayError.textContent = '';
  }
});

var $form = $('#payment-form');

function stripeTokenHandler(token, coupon) {

    var token = token.id;

    // Insert the token ID into the form so it gets submitted to the server:
    $form.append($('<input type="hidden" name="stripeToken">').val(token));
    $form.append($('<input type="hidden" name="couponCode">').val(coupon));
    <?php if($plid=="card"){ ?>
        $form.append($('<input type="hidden" name="submitType">').val("changeCard"));
    <?php } ?>
    // Submit the form:
    $form.get(0).submit();
}

// Handle form submission
var form = document.getElementById('payment-form');

form.addEventListener('submit', function(event) {
  event.preventDefault();
  var coupon = '';
  <?php if($plid<>"card" && isset($_GET['coupon'])){ ?>
      coupon = form.querySelector('input[name=coupon-code]').value;
  <?php } ?>
  var extraDetails = {
    name: form.querySelector('input[name=cardholder-name]').value,
  };
  $form.find('.submit').prop('disabled', true);

  if($.trim(extraDetails.name)=='') {
      $('input[name=cardholder-name]').addClass('StripeElement--invalid');
      $form.find('.submit').prop('disabled', false);
      var errorElement = document.getElementById('card-errors');
      errorElement.textContent = 'Name on Card is incomplete';
  } else {
      stripe.createToken(card, extraDetails).then(function(result) {
        if (result.error) {
          // Inform the user if there was an error
          var errorElement = document.getElementById('card-errors');
          errorElement.textContent = result.error.message;
          $form.find('.submit').prop('disabled', false);
        } else {
          // Send the token to your server
          stripeTokenHandler(result.token, coupon);
        }
      });
  }

});
</script>
<?php }
}?>
					</div>
					</div>

<!-- account end -->
<?php
    $this->InsideCardWrapperClose();

        $this->OutputMarketingFooter();
}
//

//
function OutputList(){
  $this->InsideHeader();
?>
	<div id="app">List Manager</div>
	<!-- Status View -->
	<div id="status"></div>
</body>
</html>
<?php
}

function OutputForm(){
	$m = "form";
    $this->InsideHeader();
    $forms=$this->lo->_listForms(array("uid"=>$this->lAccountOwner['_id'], "sort"=>$_GET['sort'], "tag"=>$_GET['tag']));

    $this->InsideCardWrapperOpen();
?>

<!-- main-content-->
<?php if($this->lAccount['blocked']) { ?>
    <h3 style="text-align:center;margin-top:100px">Your forms are blocked for review for violation of the Terms and conditions , please <a href="https://www.formlets.com/forms/571d42690acd41d175f57137/">contact</a> formlets support.</h3>
<?php } else { ?>

    <div class="row">
        <div class="col-sm-12 col-md-6">
            <?php if(count($forms) > 10 || isset($_GET['tag'])) { ?>

				<div class="row form_sort">
					<?php
					$taglink = '/form/';
					if(isset($_GET['sort'])) {
						$taglink = '/form/?sort='.$_GET['sort'];
					}
					if(isset($_GET['tag'])) {
					?>
					<div class="col-12 g12 tag_search">
						<div class="tag_search_label"><?php echo $_GET['tag']; ?></div>
						<div class="tag_search_close"><a href="<?php echo $taglink; ?>">X</a></div>
					</div>
					<?php
					}
					?>
					<div class="col-6">
					<?php
						if(isset($_GET['sort']) && $_GET['sort'] == 'name-asc') {
							echo '<a href="/form/?sort=name-desc">Name <i class="fas fa-sort-alpha-down"></i></a>';
						} else if(isset($_GET['sort']) && $_GET['sort'] == 'name-desc') {
							echo '<a href="/form/?sort=name-asc">Name <i class="fas fa-sort-alpha-up"></i></a>';
						} else {
							echo '<a href="/form/?sort=name-asc">Name <i class="fa fa-sort"></i></a>';
						}
					?>
					</div>
					<div class="col-6">
					<?php
						if(isset($_GET['sort']) && $_GET['sort'] == 'dateCreated-asc') {
							echo '<a href="/form/?sort=dateCreated-desc">Date Created <i class="fas fa-sort-amount-down"></i></a>';
						} else if(isset($_GET['sort']) && $_GET['sort'] == 'dateCreated-desc') {
							echo '<a href="/form/?sort=dateCreated-asc">Date Created <i class="fas fa-sort-amount-up"></i></a>';
						} else {
							echo '<a href="/form/?sort=dateCreated-asc">Date Created <i class="fa fa-sort"></i></a>';
						}
					?>
					</div>
				</div>
			<?php } ?>
        </div>
        <div class="col-sm-12 col-md-6">
            <?php if(count($forms)) { ?>
             <div class="newForm" style="margin-bottom:15px;text-align:right">
                <a href="<?php echo $GLOBALS['level'];?>editor/<?php echo $this->pl->insertId();?>/#enew">
					<button class="btn btn-success"><?php  echo $this->pl->trans($m,'Create New Form'); ?></button>
				</a>
            </div>
            <?php } ?>
        </div>
    </div>

  <div id="gridlistview" class="form-gridlistview list-view">
    <?php if(count($forms)) { ?>
        <ul class="row listview-header">
        <li class="form-name col-3 my-auto">Name</li>
        <li class="form-status col-2 my-auto">Status</li>
        <li class="form-created col-1 my-auto">Date Created</li>
        <li class="form-views col-1 my-auto">Page Views</li>
        <li class="form-responses col-1 my-auto">Responses</li>
        <li class="form-bounce col-1 my-auto">Bounce Rate</li>
        <li class="form-actions col-3 my-auto">Actions</li>
    </ul>
    <?php } ?>
    <ul class="row form-data-container">
      <?php
      $dateformat = $this->pl->getUserDateFormat($this->lUser);
      $timeformat = $this->pl->getUserTimeFormat($this->lUser);
      $shown=0;
		   	for($f=0;$f<count($forms);$f++) {

		   		$createdAt = date($dateformat . ' ' . $timeformat, strtotime($forms[$f]['dateCreated']));
				if($this->lUser['timezone']) {
					$tz = $this->lUser['timezone'];
					$timestamp = strtotime($forms[$f]['dateCreated']);
					$dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
					$dt->setTimestamp($timestamp); //adjust the object to correct timestamp
					$createdAt = $dt->format($dateformat . ' ' . $timeformat);
				}

				$zapier = false;
              if($forms[$f]['hooksCount']) {
                  $zapier = true;
              }

		   		if($this->pl->canUser($this->lAccount, 'read', $forms[$f])) {
                  $shown++;
		   			if($forms[$f]['type']=="ENDPOINT"){$icon="icon-endpoint";} else {$icon="icon-full";}
		    ?>

                <li class="form-item">
                  <ul class="row form-details">
                    <li class="form-name col-lg-3 my-auto">
                      <i class="far fa-file-alt"></i>
                      <div class="form-titletag">
                        <h6>
                            <?php if($this->pl->canUser($this->lAccount, 'edit', $forms[$f])) { ?>
                                <a href="<?php echo $GLOBALS['level'];?>editor/<?php echo $forms[$f]['_id'];?>/<?php echo $forms[$f]['type']=="ENDPOINT" ? '#eendpoint':'#eelements' ?>">
                                    <?php echo $forms[$f]['name']?stripslashes($forms[$f]['name']):'[no-title]';?>
                                </a>
                            <?php } else { ?>
                                <a href="<?php echo $GLOBALS['level'];?>forms/<?php echo $forms[$f]['_id'];?>/">
                                    <?php echo $forms[$f]['name']?stripslashes($forms[$f]['name']):'[no-title]';?>
                                </a>
                            <?php } ?>
                        </h6>
                        <?php if(count($forms) > 10 || isset($_GET['tag'])) { ?>
                            <span class="tags_container" data-form-id="<?php echo $forms[$f]['_id']; ?>" <?php if($zapier || ($forms[$f]['notifyUseTemplate'] == '1' && $forms[$f]['notifySubmitter'] == '1')) { echo 'style="margin-top:0px;"'; } ?>>
                                <a href="javascript:;" class="tag_link" <?php if($forms[$f]['tags']){echo'style="display:none";';} ?>>Add tags</a>
                                <?php if(trim($forms[$f]['tags'])) {
                                    $tags = explode(',', $forms[$f]['tags']);
                                ?>
                                    <div class="tags">
                                        <div class="tag_label">Tags: </div>
                                        <ul class="tag_lists">
                                            <?php foreach($tags as $tag) {
                                                if(trim($tag)) {
                                            ?>
                                                <li><?php echo $tag; ?></li>
                                            <?php
                                                }
                                            }
                                            ?>
                                        </ul>
                                        <div class="tag_edit"><a href="javascript:;">Edit tags</a></div>
                                    </div>
                                <?php } else { ?>
                                    <div class="tags" style="display:none"><div class="tag_label">Tags: </div><ul class="tag_lists"></ul><div class="tag_edit"><a href="javascript:;">Edit tags</a></div></div>
                                <?php } ?>
                                <div class="tag_input_container" style="display:none">
                                    <label>Tags:</label>
                                    <input type="text" class="tag_input" style="width:50%">
                                    <button class="btn btn-xs tag_save" style="padding:0px 10px;margin-top:-2px;margin-left:2px;line-height:1.6rem">Save</button>
                                </div>
                            </span>
                        <?php } ?>
                      </div>

                    </li>
                    <li class="form-status col-lg-2 my-auto">
                      <span class="font-weight-normal col-name">Status: </span>
                      <?php if($forms[$f]['active']=='1'){ ?>
                          <i class="fas fa-circle pub"></i>
                          <span>Published</span>
                      <?php } else { ?>
                          <i class="fas fa-circle unpub"></i>
                          <span>Unpublished</span>
                      <?php } ?>
                      <?php
    		            if($forms[$f]['seen']<$forms[$f]['submissions']){
    		            ?>
                        <span class="stat-response pl-1"><a href="<?php echo $GLOBALS['level'];?>response/<?php echo $forms[$f]['_id'];?>/new/" class="form-submission-notification"><?php echo ($forms[$f]['submissions']-$forms[$f]['seen']);?> <?php  echo $this->pl->trans($m,'New Responses'); ?></a></span>
    		            <?php } ?>

                    </li>
                    <li class="form-created col-lg-1 my-auto">
                      <span class="font-weight-normal col-name"><?php echo $this->pl->trans($m,'Created'); ?> </span>
                      <span><?php echo $createdAt;?></span>
                    </li>
                    <li class="form-views col-lg-1 my-auto">
                      <span class="font-weight-normal col-name">Page Views: </span>
                      <span><?php if(!$forms[$f]['views']){$forms[$f]['views']=0;} echo $forms[$f]['views'];?></span>
                    </li>
                    <li class="form-responses col-lg-1 my-auto">
                      <span class="font-weight-normal col-name">Responses: </span>
                      <span><?php if(!$forms[$f]['submissions']){$forms[$f]['submissions']=0;} echo $forms[$f]['submissions'];?></span>
                    </li>
                    <li class="form-bounce col-lg-1 my-auto">
                      <span class="font-weight-normal col-name">Bounce Rate: </span>
                      <span><?php if(!$forms[$f]['bounce']){$forms[$f]['bounce']=0;} echo $forms[$f]['bounce'];?>%</span>
                    </li>
                    <li class="form-actions col-lg-3 my-auto">
                        <?php if($forms[$f]['active']=='1'){ ?>
                            <a onclick="return confirm('Are you sure you want to Unpublish the form?')" href="<?php echo $this->pl->set_csrfguard($GLOBALS['level'].'form/deactivate/'.$forms[$f]['_id'].'/','deactivateform');?>" class="unpub"><?php  echo $this->pl->trans($m,'Unpublish'); ?></a>
                        <?php } else { ?>
                            <a href="<?php echo $this->pl->set_csrfguard($GLOBALS['level'].'form/activate/'.$forms[$f]['_id'].'/','activateform');?>" class="pub"><?php  echo $this->pl->trans($m,'Publish'); ?></a>
                        <?php } ?>
                      <?php if($this->pl->canUser($this->lAccount, 'edit', $forms[$f])) { ?>
                      <a class="form-action" href="<?php echo $GLOBALS['level'];?>editor/<?php echo $forms[$f]['_id'];?>/<?php echo $forms[$f]['type']=="ENDPOINT" ? '#eendpoint':'#eelements' ?>"><i class="fas fa-edit pr-1"></i> <?php  echo $this->pl->trans($m,'Edit form'); ?></a>
                      <?php } ?>
                      <?php if($forms[$f]['type']<>"ENDPOINT"){ ?>
                          <a class="form-action" href="<?php echo $GLOBALS['level'];?>forms/<?php echo $forms[$f]['_id'];?>/"><i class="fas fa-eye pr-1"></i> <?php  echo $this->pl->trans($m,'Public view'); ?></a>
                      <?php } ?>
                      <a class="form-action" href="<?php echo $GLOBALS['level'];?>response/<?php echo $forms[$f]['_id'];?>/"><i class="fas fa-reply pr-1"></i> <?php  echo $this->pl->trans($m,'Responses'); ?></a>
                      <a class="form-action" href="<?php echo $GLOBALS['level'];?>stats/<?php echo $forms[$f]['_id'];?>/"> <?php  echo $this->pl->trans($m,'Stats'); ?></a>
                      <?php if($this->pl->canUser($this->lAccount, 'create')) { ?>
                      <?php if($forms[$f]['type']<>"ENDPOINT") { ?>
                      <a class="form-action" href="<?php echo $this->pl->set_csrfguard($GLOBALS['level'].'form/duplicate/'.$forms[$f]['_id'].'/','duplicateform');?>"><i class="fas fa-copy pr-1"></i> <?php  echo $this->pl->trans($m,'Duplicate form'); ?></a>
                      <?php } ?>
                        <?php } ?>
                        <?php if($this->pl->canUser($this->lAccount, 'delete', $forms[$f])) { ?>
                      <a class="form-action delete" href="<?php echo $this->pl->set_csrfguard($GLOBALS['level'].'form/delete/'.$forms[$f]['_id'].'/','deleteform');?>" onclick="return confirm('Are you sure to permanently delete this form + all stored data ?');"><i class="fas fa-times-circle pr-1"></i> <?php  echo $this->pl->trans($m,'Delete form'); ?></a>
                      <?php } ?>
                    </li>
                  </ul>
                </li>
			<?php
				}
			}
			if($f==0 && $this->pl->canUser($this->lAccount, 'manage_forms')){
			?>
			<div class="form container center">
			  	<a href="<?php echo $GLOBALS['level'];?>editor/<?php echo $this->pl->insertId();?>/#enew"><button class="btn btn-lg btn-success form-new"><?php  echo $this->pl->trans($m,'Create Your First Form'); ?></button></a>
					<br>
			  	<!-- or <a href="<?php echo $GLOBALS['level'];?>templates/">Goto Templates</a> to start with a pre-made form -->
			</div>
          <div style="margin-top:100px" class="form container align-center">

            <object data="https://www.youtube.com/embed/13xHURwfBuo" width="560" height="315"></object>
            <br><br>
          </div>
			<?php
			}

          if($shown == 0 && count($forms)) {
          ?>
          <h3 style="text-align:center">You have not been given rights to create forms, no forms have been shared with you</h3>
          <?php
          }
			?>

    </ul>

  </div>
  <?php } ?>
<!-- End main-content-->
<?php $this->InsideCardWrapperClose(); ?>
<?php $this->OutputMarketingFooter(); ?>
<script>
$(function() {
	$(".tag_link").on("click", function() {
		var container = $(this).closest('.tags_container');
		var input_container = container.find('.tag_input_container');
		input_container.show();
		container.find('.tag_input').focus();
		$(this).hide();
	});

	$(".tag_save").on("click", function(e) {
		e.preventDefault();
		var container = $(this).closest('.tags_container');
		var input_container = container.find('.tag_input_container');
		input_container.hide();
		var tags = input_container.find('input.tag_input').val().split(",");
		var tags_container = container.find('.tag_lists');
		tags_container.html('');
		var tags_count = 0;
		$.each(tags, function(idx, tag) {
			if($.trim(tag)) {
				tags_count++;
				tags_container.append('<li>'+$.trim(tag)+'</li>');
			}
		});

		if(tags_count) {
			container.find('.tags').show();
			container.find('.tag_link').hide();
		} else {
			container.find('.tags').hide();
			container.find('.tag_link').show();
		}

		var req = {};
		req.tags = input_container.find('input.tag_input').val();
		req.formid = container.attr('data-form-id');

		$.post('/__api/json/', {method:'saveTags', json:JSON.stringify(req)});

	});

	$(".tag_edit").on("click", function(e) {
		e.preventDefault();
		var container = $(this).closest('.tags_container');
		var tags_container = $(this).closest('.tags');
		var tag_lists_container = tags_container.find('.tag_lists');
		var tags = tag_lists_container.find('li');
		var t = [];
		tags.each(function(idx, tag) {
			t.push($.trim($(tag).html()));
		});
		var tags_display = t.join(', ');

		container.find('.tag_input_container').show();
		container.find('.tag_input').val(tags_display);
		container.find('.tag_input').focus();
		tags_container.hide();
	});

	$(".tag_lists").on("click", "li", function(e) {
		e.preventDefault();
		window.location.href='/form/?tag='+$.trim($(this).html());
	});
});

</script>
</body>
</html>
<?php

}
//

function OutputPublicStats($forms) {
    $form = $forms[0];
?>
<!DOCTYPE html>
<html>
<head>
	<title><?php echo stripslashes($form['name']);?></title>
	<meta name="viewport" content="width=device-width, initial-scale=1, user-scalable=no">
	<meta name="description" content="<?php echo $form['description'] ? stripslashes($form['description']): $GLOBALS['ref']['meta']['description']; ?>">
	<meta name="twitter:card" content="summary" />
	<meta name="twitter:site" content="@formlets" />
	<meta name="twitter:title" content="<?php echo stripslashes($form['name']);?>" />
	<meta name="twitter:description" content="<?php echo $form['description'] ? stripslashes($form['description']): $GLOBALS['ref']['meta']['description']; ?>" />
	<meta name="twitter:url" content="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/stats/<?php echo $form['_id']; ?>/" />
	<meta property="og:type"   content="website" />
	<meta property="og:title" content="<?php echo $form['name'];?>" />
	<meta property="og:description" content="<?php echo $form['description'] ? strip_tags($this->correct_label($form['description'])): $GLOBALS['ref']['meta']['description']; ?>" />
	<meta property="og:url" content="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/stats/<?php echo $form['_id']; ?>/" />
	<link rel="icon" type="image/x-icon" href="<?php echo $GLOBALS['level'];?>static/img/favicon.ico" />
	<link rel="stylesheet" type="text/css" href="<?php echo $GLOBALS['level'];?>static/css/form.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>">
    <script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/chart.min.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>

		<style>
		body {
			line-height: 1.25;
		}
		a {
		    color: inherit;
		    text-decoration: none
		}
		</style>
  <script>
  (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
  (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
  m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
  })(window,document,'script','https://www.google-analytics.com/analytics.js','ga');

  ga('create', 'UA-60128463-3', 'auto');
  ga('send', 'pageview');
</script>
</head>
<body class="link" style="overflow-x:hidden; padding: 16px;">
	<div id="<?php echo $formcssid; ?>" class="gc fcc deployed centered small-12 medium-8 large-6 clearfix">
        <div style="text-align:left;margin-bottom:15px;">
    		<h1><?php echo stripslashes($form['name']); ?></h1>
        </div>
		<div class="fc gc pad">
			<?php $this->displayStats($forms); ?>
		</div>
	</div>
	<footer class="<?php if(!$_GET['iframe']): ?>small-12 medium-8 large-6<?php endif; ?>">
		<p>
			<a target="_top" href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>?f=y"><img src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/img/powered_by.svg" alt="Create an online form"></a>
		</p>
	</footer>
    <?php
    $this->_benchit();
    ?>
</body>
</html>

<?php
}

function displayStats($forms) {
    foreach($this->forms as $form) {
?>
    <div class="form_stats_container">
        <?php if($this->lUser) { ?>
            <h2><?php echo $form['name'] ?></h2>
        <?php } ?>
            <div class="col-3" style="text-align:left;"><strong>Responses</strong></div>
            <div class="col-9">
                <div class="stats_general">
                    <canvas id="myChart<?php echo $form['_id'] ?>" class="general_stats_canvas"></canvas>
                    <script>
                    var ctx = document.getElementById("myChart<?php echo $form['_id'] ?>");
                    var myChart = new Chart(ctx, {
                        type: 'bar',
                        data: {
                            labels: <?php echo json_encode($this->stats[$form['_id']]['labels']); ?>,
                            datasets: [{
                                label: '# of responses',
                                data: <?php echo json_encode($this->stats[$form['_id']]['data']); ?>,
                                backgroundColor: <?php echo json_encode($this->stats[$form['_id']]['backgroundColor']); ?>,
                                borderWidth: 1
                            }]
                        },
                        options: {
                            //responsive:true,
                            legend: {
                                display: false
                            },
                            title: {
                                display: false,
                                text: 'Responses'
                            },
                            tooltips: {
                                enabled: false
                            },
                            hover: {
                              animationDuration: 0
                            },
                            animation: {
                                duration: 0,
                                easing: "easeOutQuart",
                                onComplete: function () {
                                    var ctx = this.chart.ctx;
                                    ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontFamily, 'normal', Chart.defaults.global.defaultFontFamily);
                                    ctx.textAlign = 'center';
                                    ctx.textBaseline = 'bottom';

                                    this.data.datasets.forEach(function (dataset) {
                                        for (var i = 0; i < dataset.data.length; i++) {
                                            var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model,
                                                scale_max = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._yScale.maxHeight;
                                            ctx.fillStyle = 'gray';
                                            var y_pos = model.y - 5;
                                            // Make sure data value does not get overflown and hidden
                                            // when the bar's value is too close to max value of scale
                                            // Note: The y value is reverse, it counts from top down
                                            if ((scale_max - model.y) / scale_max >= 0.93) {
                                                y_pos = model.y + 20;
                                                ctx.fillStyle = 'gray';
                                            }

                                            ctx.fillText(dataset.data[i], model.x, y_pos);
                                        }
                                    });
                                }
                            },
                            maintainAspectRatio: false,
                            scales: {
                                yAxes: [{
                                    ticks: {
                                        beginAtZero:true,
                                        fontSize: 15,
                                        userCallback: function(label, index, labels) {
                                             // when the floored value is the same as the value we have a whole number
                                             if (Math.floor(label) === label) {
                                                 return label;
                                             }
                                         }
                                    }
                                }],
                                xAxes: [{
                                    barThickness: 33,
                                    ticks: {
                                        fontSize: 12,
                                        autoSkip: false
                                    }
                                }]
                            }
                        }
                    });
                    </script>
                </div>
            </div>
        <?php $ctr=1; foreach($this->additionalGraphs[$form['_id']] as $graph) { ?>
            <div class="col-12">
                <div class="col-3" style="text-align:left;min-height:250px">
                    <strong><?php echo $graph['title']; ?></strong>
                    <table style="margin-top:15px;">
                    <?php foreach($graph['labels'] as $k=>$label) { ?>

                            <tr>
                                <td><?php echo ucfirst($label) ?></td>
                                <td>:</td>
                                <td>
                                    <?php
                                    $percentage = $graph['data'][$k]/$graph['totalSubmissions']*100;
                                    echo round($percentage,2).'%';
                                    ?>
                                </td>
                            </tr>

                    <?php } ?>
                </table>
                </div>
                <div class="col-9">
                    <div>
                        <canvas id="additionalChart<?php echo $ctr; ?>" class="general_stats_canvas"></canvas>
                        <script>
                        var ctx = document.getElementById("additionalChart<?php echo $ctr; ?>");
                        var myChart = new Chart(ctx, {
                            type: 'bar',
                            data: {
                                labels: <?php echo json_encode($graph['labels']); ?>,
                                datasets: [{
                                    label: '# of responses',
                                    data: <?php echo json_encode($graph['data']); ?>,
                                    backgroundColor: <?php echo json_encode($graph['backgroundColor']); ?>,
                                    borderWidth: 1
                                }]
                            },
                            options: {
                                //responsive:true,
                                legend: {
                                    display: false
                                },
                                title: {
                                    display: false,
                                    text: '<?php echo addslashes($graph['title']); ?>'
                                },
                                tooltips: {
                                    enabled: false
                                },
                                hover: {
                                  animationDuration: 0
                                },
                                animation: {
                                    duration: 0,
                                    easing: "easeOutQuart",
                                    onComplete: function () {
                                        var ctx = this.chart.ctx;
                                        ctx.font = Chart.helpers.fontString(Chart.defaults.global.defaultFontFamily, 'normal', Chart.defaults.global.defaultFontFamily);
                                        ctx.textAlign = 'center';
                                        ctx.textBaseline = 'bottom';

                                        this.data.datasets.forEach(function (dataset) {
                                            for (var i = 0; i < dataset.data.length; i++) {
                                                var model = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._model,
                                                    scale_max = dataset._meta[Object.keys(dataset._meta)[0]].data[i]._yScale.maxHeight;
                                                ctx.fillStyle = 'gray';
                                                var y_pos = model.y - 5;
                                                // Make sure data value does not get overflown and hidden
                                                // when the bar's value is too close to max value of scale
                                                // Note: The y value is reverse, it counts from top down
                                                if ((scale_max - model.y) / scale_max >= 0.93) {
                                                    y_pos = model.y + 20;
                                                    ctx.fillStyle = 'gray';
                                                }

                                                ctx.fillText(dataset.data[i], model.x, y_pos);
                                            }
                                        });
                                    }
                                },
                                maintainAspectRatio: false,
                                scales: {
                                    yAxes: [{
                                        ticks: {
                                            beginAtZero:true,
                                            fontSize: 15,
                                            userCallback: function(label, index, labels) {
                                                 // when the floored value is the same as the value we have a whole number
                                                 if (Math.floor(label) === label) {
                                                     return label;
                                                 }
                                             }
                                        }
                                    }],
                                    xAxes: [{
                                        barThickness: 33,
                                        ticks: {
                                            fontSize: 12,
                                            autoSkip: false
                                        }
                                    }]
                                }
                            }
                        });
                        </script>
                    </div>
                </div></div>
        <?php $ctr++; } ?>
    </div>
<?php
    }
}

function OutputStats() {
    $m = "stats";
    if($this->lUser) {
        $this->InsideHeader();
        $this->InsideCardWrapperOpen();
    } else {
        $this->OutputPublicStats($this->forms);exit;
    }
    if(count($this->forms)==0){
?>
		<h1>No Forms</h1>
        You will find here the stats of forms you create<br>
<?php
  	} else {

        if(!$this->lUser) {
            echo '<section class="flush" well>';
        }
?>
        <?php if($this->lUser) { ?>
        <strong>Status: <?php echo ucfirst($this->forms[0]['stats']); ?></strong> |
        <?php
        if($this->forms[0]['stats'] == 'private') {
            echo '<a href="/stats/'.$this->urlpart[2].'/public/">Make Public</a>';
        } else {
            echo '<a href="/stats/'.$this->urlpart[2].'/private/">Make Private</a>';
        }
        ?>

        <?php } ?>
        <div class="gr centered form-list-dashboard container team">
            <?php if(count($this->submissions) == 0) { ?>
                <h2>No statistics to show</h2>
            <?php } else { $this->displayStats($this->forms); } ?>
        </div>
<?php
        if(!$this->lUser) {
            echo '</section>';
        }
    }
?>
<?php
    if($this->lUser) {
        $this->InsideCardWrapperClose();

        $this->OutputMarketingFooter();
    }
}

function outputNewIntegration() {
?>
<div id="app" class="stage">
    <div class="scroll">
        <div class="flush centered">
            <div class="gr centered small-11 large-12 xl-6 settings">
                <div class="gr fc flush height-100">
                    <div class="gr centered small-11 medium-8">
                        <h1>New Integrations</h1>
                        <form action="" method="POST">
                            <div class="row">
                                <div class="col-5">
                                    <label>Title</label>
                                    <input type="text" name="title" />
                                </div>
                                <div class="col-5">
                                    <label>Type</label>
                                    <select name="type">
                                        <option></option>
                                        <option value="stripe">Stripe</option>
                                    </select>
                                </div>
                                <div class="col-2">
                                    <label>&nbsp;</label>
                                    <button>Create</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <?php $this->OutputMarketingFooter(); ?>
        </div>
    </div>
</div>
<?php
}

function OutputIntegrations(){
    $m = "datasource";
	$this->InsideHeader();
	$integrationId = $this->urlpart[2];

    $this->InsideHeader();

    $this->InsideCardWrapperOpen();

    $this->InsidesettingsHeader('account');

    if($integrationId == 'new') {
        $this->outputNewIntegration();
    } else {

    	if($integrationId){
        	$integrations=$this->integrations;
      	} else {
        	$integrations=$this->lo->_listIntegrations(array("uid"=>$this->lAccountOwner['_id']));
      	}

      	if(count($integrations)==1 && $this->urlpart[2]){
          	$this->outputintegrationlist($integrations);
      	} else if(count($integrations)==0){
        ?>
            <h1>No Integrations</h1>
            You will find here the integrations you create<br>
            <div class="center">
                <a href="<?php echo $GLOBALS['level'];?>integrations/new/"><button class="large blue form-new">Create Your First Integration</button></a>
            </div>
        <?php
        } else {
        ?>
        	<table class="submissions-table submissions-static-header" cellpadding="0" cellspacing="0">
        		<thead>
        			<tr>
        				<th width="30%" align="left"><?php echo $this->pl->trans($m,'Title'); ?></th>
        				<th width="30%" align="left"><?php echo $this->pl->trans($m,'Type'); ?></th>
        				<th width="40%" align="left"><?php echo $this->pl->trans($m,'Action'); ?></th>
        			</tr>
        		</thead>
        		<tbody>
        		<?php

        		$ctr = 1;

        		for($f=0;$f<count($integrations);$f++) {
        			$bg = "#fff";
        			if($ctr%2 == 0) {
        				$bg = "#f0f0f2";
        			}
        		?>
        				<tr>
        					<td style="padding: 0px;border-bottom: 0;background: <?php echo $bg ?>;">
        						<h4 style="margin: 5px"><?php echo stripslashes($integrations[$f]['title']);?></h4>
        					</td>
                            <td><?php echo stripslashes($integrations[$f]['type']);?></td>
        					<td style="padding: 0px;border-bottom: 0;background: <?php echo $bg ?>;"><a href="/integrations/<?php echo $integrations[$f]['_id']; ?>/">Edit</a></td>
                        </tr>
        		<?php
        		        $ctr++;
        		}

        		?>
        		</tbody>
        	</table>
        <?php
        }
    }

    $this->InsideCardWrapperClose();
    $this->OutputMarketingFooter();
}


function outputintegrationlist($integration) {
    $lists = json_decode($integration[0]['configs']);
    //$columns = array("Label","Value");
    $columns = $GLOBALS['ref']['integration']['type'][$integration[0]['type']];
    $links = $this->lo->getIntegrationlink(array('formConfigsId'=>$integration[0]['_id']));
    $forms = $this->lo->_listForms(array('uid'=>$this->lAccountOwner['_id']));
?>
    <style>
    .list .list-col {
        float:left;
    }
    .list .list-col input {
        width: 200px;
    }
    .list .list-col:first-child {
        padding-top: 17px;
    }

    .list .list-col:last-child {
        padding-top: 17px;
    }

    .title .list-col {
        float:left;
    }
    .title .list-col {
        width: 200px;
        margin: 15px;
    }
    .input-title {
        padding:0 !important;
        font-size: 16px !important;
        font-weight: bold !important;
        box-shadow: none !important;
        border-bottom: 1px solid #d9d9d9 !important;
    }
    .listColumns {

    }
    </style>
    <div id="app" class="stage">
        <div class="scroll">
            <div class="flush centered">
                <div class="gr centered small-11 large-12 xl-6 settings">
                    <div class="gr fc flush height-100">
                        <div class="gr centered small-11 medium-8">
                            <div class="row">
                                <div class="col-12">
                                    <form id="datasource" action="/integrations/<?php echo $this->urlpart[2] ?>/?ajax=true">
                                        <label>Title</label>
                                        <div class="row">
                                            <div class="col-11">
                                                <input type="text" name="title" value="<?php echo stripslashes(htmlentities($integration[0]['title'])); ?>" style="width:430px" class="input-title">
                                            </div>
                                        </div>
                                        <label>Data Lists</label>
                                        <div class="row title column-titles">
                                            <?php foreach($columns as $column) { ?>
                                                <div class="list-col listColumns">
                                                    <input readonly type="text" name="columns[]" class="input-title" value="<?php echo htmlentities($column); ?>">
                                                </div>
                                            <?php } ?>
                                        </div>
                                        <div class="datalists" id="datalists">
                                            <div class="row list">
                                                <div class="column-values">
                                                <?php
                                                $ctr = 1;
                                                foreach($columns as $column) {
                                                    $value = $lists ? $lists->$column:'';
                                                ?>
                                                    <div class="list-col" style="padding:5px 15px">
                                                        <input type="text" name="<?php echo $column; ?>" value="<?php echo htmlentities($value); ?>" />
                                                    </div>
                                                <?php
                                                $ctr++;
                                                }
                                                ?>

                                                </div>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <label>Used in the following Form Elements</label>
                            <div class="row">
                                <div class="col-12">
                                    <table width="100%" style="border:1px solid #eee">
                                        <thead>
                                            <tr>
                                                <th align="left">Form</th>
                                                <th align="left">Element</th>
                                                <th></th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php
                                            foreach($links as $link) {
                                                $elementLabel = $this->lo->getElementLabel(array(
                                                    'owner' => $this->lAccountOwner['_id'],
                                                    'form_id'=>$link['formId'],
                                                    'el_id'=>$link['elementId']
                                                ));
                                            ?>
                                                <tr>
                                                    <td><a href="/editor/<?php echo $link['formId']; ?>/#e<?php echo $link['elementId']; ?>"><?php echo $link['form_name']; ?></td>
                                                    <td><?php echo $elementLabel; ?></td>
                                                    <td><a href="/integrations/<?php echo $this->urlpart[2]; ?>/remove/<?php echo $link['_id']; ?>/" onclick="return confirm('are you sure you want to unlink this element?')">Unlink</a></td>
                                                </tr>
                                            <?php } ?>

                                            <?php if(count($links) == 0) { ?>
                                                <tr><td colspan="3">No links at the moment</td></tr>
                                            <?php } ?>
                                        </tbody>
                                    </table>
                                    <br /><br />
                                    <label>Add new link</label>
                                    <div class="row">
                                        <div class="col-5">
                                            <form action="" method="GET">
                                                <select name="formid" onchange="this.form.submit()">
                                                    <option value="">Select Form</option>
                                                <?php foreach($forms as $form) { ?>
                                                    <option value="<?php echo $form['_id']; ?>" <?php if(isset($_GET['formid']) && $_GET['formid'] == $form['_id']) { echo "selected"; } ?>><?php echo $form['name']; ?></option>
                                                <?php } ?>
                                                </select>
                                            </form>
                                        </div>
                                        <div class="col-7">
                                            <?php
                                            if(isset($_GET['formid'])) {
                                                $elements=$this->lo->getFormElement(array(
                                                    'form_id'=>$_GET['formid']
                                                ));
                                            ?>
                                            <form action="" method="POST">
                                                <input type="hidden" name="formId" value="<?php echo $_GET['formid']; ?>" />
                                                <select name="elementId" style="width:65%;margin-right:20px">
                                                    <option value="">Select Element</option>
                                                <?php
                                                    foreach($elements as $element) {
                                                    $label = isset($element['queryName']) ? $element['queryName'] : $element['label'];
                                                    if(!$label) { $label = $element['inputLabel']; }
                                                ?>
                                                    <option value="<?php echo $element['name']; ?>"><?php echo $label; ?></option>
                                                <?php } ?>
                                                </select>

                                                <button>Add</button>
                                            </form>
                                            <?php
                                            }
                                            ?>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>



    (function() {
        var submitDataSourceChanges = function() {
            var form = $('form#datasource');

            $.ajax({
                type: "POST",
                url: form.attr( 'action' ),
                data: form.serialize(),
                success: function( response ) {
                }
            });
        };

        $(document).on("blur", "form#datasource input", function() {
            submitDataSourceChanges();
        });

        $(document).on("click", ".deleteList", function() {
            if(confirm("Are you sure you want to delete that list?")) {
                $(this).closest('.list').remove();
                submitDataSourceChanges();
            }
        });

        $(document).on("click", "#new", function() {
            var newList = $('<div class="row list">\
                <div class="list-col"><i class="fa fa-arrows" style="color:#959595;cursor:pointer" aria-hidden="true"></i></div>\
                <div class="column-values">\
                    <div class="list-col" style="padding:5px 15px">\
                        <input type="text" name="labels[]" value="" />\
                    </div>\
                    <div class="list-col" style="padding:5px 15px">\
                        <input type="text" name="values[]" value="" />\
                    </div>\
                </div>\
                <div class="list-col">\
                    <i class="fa fa-trash-o deleteList" style="color:#d1603d;cursor:pointer;" aria-hidden="true"></i>\
                </div>\
            </div>');


            var newList = $(".datalists").find('.row.list').get(0);
            newList = $(newList).clone();
            var inputs = newList.find('input');
            inputs.each(function() {
                $(this).val('');
            });

            newList.insertBefore("#datalists > #new");

            submitDataSourceChanges();
        });

        $(document).on("click", ".newColumn", function() {
            var columns = $(".list-col.listColumns");
            var input_name = '';
            if(columns.length == 1) {
                input_name = 'values[]';
                label = 'Value';
            } else {
                input_name = 'column_' + (columns.length+1) + '[]';
                label = 'Column ' + (columns.length+1);
            }

            var newColumn = $('<div class="list-col listColumns">\
                <input type="text" name="columns[]" class="input-title" value="">\
            </div>');

            newColumn.insertBefore(".column-titles > .new-col");
            newColumn.find('input').val(label);
            newColumn.find('input').focus();

            var newRowColumn = $('<div class="list-col" style="padding:5px 15px">\
                <input type="text" name="'+input_name+'" value="" />\
            </div>');

            $(".column-values").append(newRowColumn);
        });
    })();

    </script>
<?php
}


function OutputDatasource(){
    $m = "datasource";
	$this->InsideHeader();

    $this->InsideCardWrapperOpen();

	$sourceid = $this->urlpart[2];
	if($sourceid){
    	$datasources=$this->datasource;
  	} else {
    	$datasources=$this->lo->_listDatasources(array("uid"=>$this->lAccountOwner['_id']));
  	}

  	if(count($datasources)==1 && $this->urlpart[2]){
      	$this->outputdatasourcelist($datasources);
  	} else if(count($datasources)==0){
    ?>
        <h1>No Datasources</h1>
        You will find here the datasources you create<br>
    <?php
    } else {
    ?>

    <div class="row Data-Sources-Content">
      <div class="col">
        <table id="data-table" class="table table-striped" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th width="55%" scope="col"><?php echo $this->pl->trans($m,'Data source'); ?></th>
                    <th width="15%" scope="col"><?php echo $this->pl->trans($m,'Status'); ?></th>
                    <th width="30%" scope="col"><?php echo $this->pl->trans($m,'Action'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
        		for($f=0;$f<count($datasources);$f++) {
                    if($this->pl->canUser($this->lAccount, 'read', $forms[$f])) {
        		?>
                <tr>
                    <td data-label="Data source"><h6><?php echo stripslashes($datasources[$f]['title']);?></h6></td>
                    <td data-label="Status"><?php if($datasources[$f]['count']) { echo 'linked'; } else { echo 'not linked'; } ?></td>
                    <td data-label="Action">
                      <a href="/datasource/<?php echo $datasources[$f]['_id']; ?>/" class="form-action"><i class="fas fa-edit pr-1"></i>Edit</a>
                      <a href="/datasource/<?php echo $datasources[$f]['_id']; ?>/delete/" onclick="return confirm('are you sure you want to delete?')" class="form-action delete"><i class="fas fa-times-circle pr-1"></i>Delete</a>
                    </td>
                </tr>
                <?php
                    }
        		}
        		?>
            </tbody>
        </table>
      </div>
    </div>
    <?php
    }

    $this->InsideCardWrapperClose();

    $this->OutputMarketingFooter();
}

function outputdatasourcelist($datasource) {
    $lists = json_decode($datasource[0]['data']);
    $columns = array("Label","Value");
    if($datasource[0]['columns']) {
        $columns = json_decode($datasource[0]['columns'], true);
        if(count($columns) == 1) {
            $columns[1] = 'Value';
        }
    }
    $links = $this->lo->getDatasourcelink(array('datasourceId'=>$datasource[0]['_id']));
    $forms = $this->lo->_listForms(array('uid'=>$this->lAccountOwner['_id']));
?>
    <style>
    .list .list-col {
        float:left;
    }
    .list .list-col input {
        width: 130px;
    }
    .list .list-col:first-child {
        padding-top: 17px;
    }

    .list .list-col:last-child {
        padding-top: 17px;
    }

    .title .list-col {
        float:left;
    }
    .title .list-col {
        width: 130px;
        margin: 15px;
    }
    .title .list-col:first-child {
        width: 0px;
        margin: 7px;
    }
    .input-title {
        font-size: 16px !important;
        font-weight: bold !important;
        box-shadow: none !important;
        border-bottom: 1px solid #d9d9d9 !important;
    }
    .listColumns {

    }
    .removeColumn {
        margin-top: 5px;
        color:#d1603d;
        cursor: pointer;
    }
    </style>
    <div class="row">
        <div class="col-12">
            <form id="datasource" action="/datasource/<?php echo $this->urlpart[2] ?>/?ajax=true">
                <label>Datasource Name</label>
                <div class="row">
                    <div class="col-11">
                        <input type="text" name="title" value="<?php echo stripslashes(htmlentities($datasource[0]['title'])); ?>" style="width:430px" class="input-title form-control">
                    </div>
                </div>
                <label>Data Lists</label>
                <div class="row title column-titles">
                    <div class="list-col">&nbsp;</div>
                    <?php foreach($columns as $k=>$column) { ?>
                        <div class="list-col listColumns">
                            <input type="text" name="columns[]" class="input-title form-control" value="<?php echo htmlentities($column); ?>">
                            <?php if($k<>0){ ?>
                                <span class="removeColumn"><i class="fa fa-trash"></i> Delete </span>
                            <?php } ?>
                        </div>
                    <?php } ?>
                    <div class="list-col new-col"><a href="javascript:;" class="newColumn">+ new column</a></div>
                </div>
                <div class="datalists" id="datalists">
                    <?php if($lists) { ?>
                        <?php foreach($lists as $list) { ?>
                            <div class="row list">
                                <div class="list-col"><i class="fas fa-arrows-alt" style="color:#959595;cursor:pointer" aria-hidden="true"></i></div>
                                <div class="column-values">
                                <?php
                                $ctr = 1;
                                foreach($columns as $column) {
                                    if($ctr == 1) { $name = 'labels[]'; $value = $list->label; }
                                    else if($ctr == 2) { $name = 'values[]'; $value = $list->value; }
                                    else { $n = 'column_' . $ctr; $name = $n . '[]'; $value = $list->$n; }
                                ?>
                                    <div class="list-col" style="padding:5px 15px">
                                        <input type="text" class="form-control" name="<?php echo $name; ?>" value="<?php echo htmlentities($value); ?>" />
                                    </div>
                                <?php
                                $ctr++;
                                }
                                ?>

                                </div>
                                <div class="list-col">
                                    <i class="fas fa-trash deleteList" style="color:#d1603d;cursor:pointer;" aria-hidden="true"></i>
                                </div>
                            </div>
                        <?php } ?>
                    <?php } else { ?>
                        <div class="row list">
                            <div class="list-col"><i class="fas fa-arrows-alt" style="color:#959595;cursor:pointer" aria-hidden="true"></i></div>
                            <div class="column-values">
                            <?php
                            $ctr = 1;
                            foreach($columns as $column) {
                                if($ctr == 1) { $name = 'labels[]'; $value = ''; }
                                else if($ctr == 2) { $name = 'values[]'; $value = ''; }
                                else { $n = 'column_' . $ctr; $name = $n . '[]'; $value = ''; }
                            ?>
                                <div class="list-col" style="padding:5px 15px">
                                    <input class="form-control" type="text" name="<?php echo $name; ?>" value="<?php echo htmlentities($value); ?>" />
                                </div>
                            <?php
                            $ctr++;
                            }
                            ?>

                            </div>
                            <div class="list-col">
                                <i class="fas fa-trash deleteList" style="color:#d1603d;cursor:pointer;" aria-hidden="true"></i>
                            </div>
                        </div>
                    <?php } ?>
                        <div class="row" id="new"><div class="col-12"><a href="javascript:;" id="newList">+ new row</a></div></div>
                </div>
            </form>
        </div>
    </div>
    <label>Used in the following Form Elements</label>
    <div class="row">
        <div class="col-12">
            <table width="100%" style="border:1px solid #eee">
                <thead>
                    <tr>
                        <th align="left">Form</th>
                        <th align="left">Element</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    foreach($links as $link) {
                        $elementLabel = $this->lo->getElementLabel(array(
                            'owner' => $this->lAccountOwner['_id'],
                            'form_id'=>$link['formId'],
                            'el_id'=>$link['elementId']
                        ));
                    ?>
                        <tr>
                            <td><a href="/editor/<?php echo $link['formId']; ?>/#e<?php echo $link['elementId']; ?>"><?php echo $link['form_name']; ?></td>
                            <td><?php echo $elementLabel; ?></td>
                            <td><a href="/datasource/<?php echo $this->urlpart[2]; ?>/remove/<?php echo $link['_id']; ?>/" onclick="return confirm('are you sure you want to unlink this element?')">Unlink</a></td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
            <br /><br />
            <label>Add new link</label>
            <div class="row">
                <div class="col-5">
                    <form action="" method="GET">
                        <select name="formid" onchange="this.form.submit()" class="form-control">
                            <option value="">Select Form</option>
                        <?php foreach($forms as $form) { ?>
                            <option value="<?php echo $form['_id']; ?>" <?php if(isset($_GET['formid']) && $_GET['formid'] == $form['_id']) { echo "selected"; } ?>><?php echo $form['name']; ?></option>
                        <?php } ?>
                        </select>
                    </form>
                </div>
                <div class="col-7">
                    <?php
                    if(isset($_GET['formid'])) {
                        $elements=$this->lo->getFormElement(array(
                            'form_id'=>$_GET['formid']
                        ));
                    ?>
                    <form action="" method="POST">
                        <input type="hidden" name="formId" value="<?php echo $_GET['formid']; ?>" />
                        <select name="elementId" style="width:65%;margin-right:20px">
                            <option value="">Select Element</option>
                        <?php
                            foreach($elements as $element) {
                            $label = isset($element['queryName']) ? $element['queryName'] : $element['label'];
                            if(!$label) { $label = $element['inputLabel']; }
                        ?>
                            <option value="<?php echo $element['name']; ?>"><?php echo $label; ?></option>
                        <?php } ?>
                        </select>

                        <button>Add</button>
                    </form>
                    <?php
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
    <script>



    (function() {
        var submitDataSourceChanges = function() {
            var form = $('form#datasource');

            $.ajax({
                type: "POST",
                url: form.attr( 'action' ),
                data: form.serialize(),
                success: function( response ) {
                }
            });
        };

        $(document).on("blur", "form#datasource input", function() {
            submitDataSourceChanges();
        });

        $(document).on("click", ".deleteList", function() {
            if(confirm("Are you sure you want to delete that list?")) {
                $(this).closest('.list').remove();
                submitDataSourceChanges();
            }
        });

        $(document).on("click", "#new", function() {
            var newList = $('<div class="row list">\
                <div class="list-col"><i class="fa fa-arrows" style="color:#959595;cursor:pointer" aria-hidden="true"></i></div>\
                <div class="column-values">\
                    <div class="list-col" style="padding:5px 15px">\
                        <input type="text" class="form-control" name="labels[]" value="" />\
                    </div>\
                    <div class="list-col" style="padding:5px 15px">\
                        <input type="text" class="form-control" name="values[]" value="" />\
                    </div>\
                </div>\
                <div class="list-col">\
                    <i class="fa fa-trash-o deleteList" style="color:#d1603d;cursor:pointer;" aria-hidden="true"></i>\
                </div>\
            </div>');


            var newList = $(".datalists").find('.row.list').get(0);
            newList = $(newList).clone();
            var inputs = newList.find('input');
            inputs.each(function() {
                $(this).val('');
            });

            newList.insertBefore("#datalists > #new");

            submitDataSourceChanges();
        });

        $(document).on("click", ".removeColumn", function() {
            var $this = $(this);
            if(confirm('are you sure you want to delete that column and it\'s data?')) {
                var listColumn = $this.closest('.listColumns');
                var columns = $(document).find('.listColumns');
                var idx = columns.index(listColumn);
                if(idx > 0) {
                    var lists = $(document).find("#datalists .row.list");
                    lists.each(function() {
                        var cols = $(this).find('.list-col');
                        cols.get(idx+1).remove();
                    });
                }

                listColumn.remove();

                submitDataSourceChanges();
            }
        });

        $(document).on("click", ".newColumn", function() {
            var columns = $(".list-col.listColumns");
            var input_name = '';
            if(columns.length == 1) {
                input_name = 'values[]';
                label = 'Value';
            } else {
                input_name = 'column_' + (columns.length+1) + '[]';
                label = 'Column ' + (columns.length+1);
            }

            var newColumn = $('<div class="list-col listColumns">\
                <input type="text" name="columns[]" class="input-title form-control" value="">\
                <span class="removeColumn"><i class="fa fa-trash"></i> Delete </span>\
            </div>');

            newColumn.insertBefore(".column-titles > .new-col");
            newColumn.find('input').val(label);
            newColumn.find('input').focus();

            var newRowColumn = $('<div class="list-col" style="padding:5px 15px">\
                <input type="text" class="form-control" name="'+input_name+'" value="" />\
            </div>');

            $(".column-values").append(newRowColumn);
        });

        var el = $("#datalists").get(0);
        var s = Sortable.create(el, {
            handle: ".fa-arrows-alt",
            onSort: function (/**Event*/evt) {
                submitDataSourceChanges();
        	},
        });
    })();

    </script>
<?php
}


function Outputresponse(){
  	$m = "submission";
	$this->InsideHeader();

    $this->InsideCardWrapperOpen();

  	if(count($this->forms)==1 && $this->urlpart[2]){
      	$this->outputresponselist($this->forms);
  	} else if(count($this->forms)==0){
?>
			<h1>No Forms</h1>
            You will find here the responses of forms you create<br>
<?php
  	} else {

?>
<div class="row Responses-Content">
    <div class="col">
        <table id="data-table" class="table table-striped" cellspacing="0" width="100%">
            <thead>
                <tr>
                    <th width="60%" scope="col"><?php echo $this->pl->trans($m,'Form Name'); ?></th>
                    <th width="20%" scope="col"></th>
                    <th width="20%" scope="col"><?php echo $this->pl->trans($m,'Total Responses'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
        		for($f=0;$f<count($this->forms);$f++) {
                    if($this->pl->canUser($this->lAccount, 'read', $this->forms[$f])) {
        		?>
                    <tr>
                        <td data-label="Form Name"><h6><?php echo stripslashes($this->forms[$f]['name']);?></h6></td>
                        <td data-label="Action"><a href="/response/<?php echo $this->forms[$f]['_id']; ?>/">Data</a> | <a href="/stats/<?php echo $this->forms[$f]['_id']; ?>/">Stats</a></td>
                        <td data-label="Total Responses"><?php echo $this->forms[$f]['submissions'] ?: '0'; ?></td>
                    </tr>
                <?php
                    }
        		}

        		?>
            </tbody>
        </table>
    </div>
</div>
<?php
  	}

    $this->InsideCardWrapperClose();

    $this->OutputMarketingFooter();
}

function outputResponseDatasource() {
    $formid = $this->urlpart[2];
    $form = $this->lo->getForm(array('form_id'=>$formid));
    $elements=$this->lo->getFormElement(array('form_id' => $formid));
    $datasources=$this->lo->_listDatasources(array("uid"=>$this->lAccountOwner['_id']));
    $connectors = $this->lo->getDatasourceConnector(array('formId'=>$formid, 'accountId'=>$this->lAccount['_id']));

    $columns = array();
    if(count($connectors)) {

        $connectorArr = array();
        foreach($connectors as $connector) {
            $connectorArr[$connector['elementId']] = $connector['datasourceColumn'];
        }

        $ds = $this->lo->_listDatasources(array('source_id'=>$connectors[0]['datasourceId']));
        $ds = $ds[0];
        $columns = array("Label", "Value");
        if($ds['columns']) {
            $columns = json_decode($ds['columns'], true);
        }
    }
?>
<style>
.col-select {
    padding: 5px;
}
.newColumns_input input {
    width: 90%;
}
span.delete {
    margin-left: 5px;
    line-height: 33px;
    color:red;
    cursor: pointer;
}

#saveDSButton {
    float:right;
    margin-top:25px;
    margin-right:20px;
}
.disabled {
    background: gray;
    cursor: default;
}
</style>
    <ul class="submenu_settings" style="text-align:center;background:none;">
        <li><a href="/response/<?php echo $this->urlpart[2]; ?>/" class="button small ">Back to data list</a></li>
    </ul>
    <div class="gr centered form-list-dashboard container team">
        <div style="text-align:left">
            <p>Save responses as data sources for other forms.</p>
            <form action="" method="POST">
                <input type="hidden" name="type" value="newConnector">
                <h2><a href="/editor/<?php echo $form['_id'] ?>/"><?php echo $form['name']; ?></a> | Datasource Connection</h2>
                <div class="row">
                    <div class="col-6">
                        <?php if(count($connectors)) { ?>
                            <div style="margin-top:30px;">
                                <label>&nbsp;</label>
                                <?php if($connectors[0]['import']==0) { ?>
                                    <a href="/response/<?php echo $this->urlpart[2]; ?>/datasource/import/" class="button">Import Old Responses</a>
                                <?php } else { ?>
                                    <a href="javascript:;" class="button disabled">Import Old Responses</a>
                                <?php } ?>
                            </div>
                        <?php } ?>
                    </div>
                    <div class="col-6">
                        <label><h3>Datasource</h3></label>
                        <select name="datasourceId" id="datasourceId">
                            <option value=""></option>
                            <option value="new">New Datasource</option>
                            <?php
                            foreach ($datasources as $datasource) {
                                $selected="";
                                if($datasource['_id'] == $connectors[0]['datasourceId']) {
                                    $selected="selected";
                                    $foundDS = $datasource['_id'];
                                }
                            ?>
                                <option value="<?php echo $datasource['_id']; ?>" <?php echo $selected; ?>><?php echo stripslashes($datasource['title']); ?></option>
                            <?php } ?>
                        </select>
                    </div>
                </div>
                <div class="row">
                    <div class="col-6"><label><h3>Response Data</h3></label></div>
                    <div class="col-6"><label><h3>Column</h3></label></div>
                </div>
                <?php
                foreach ($elements as $element) {
                    $label=$element['inputLabel'];
                    if (!$label) {
                        $label=$element['label'];
                    }
                    if ($element['queryName']) {
                        $label=$element['queryName'];
                    }
                ?>
                    <div class="row">
                        <div class="col-6">
                            <input type="hidden" class="" name="element[]" value="<?php echo $element['_id']; ?>" />
                            <label><?php echo $label; ?>:</label>
                        </div>
                        <div class="col-6 col-select">
                            <select class="dColumns" name="column[]">
                                <option value="">Select Column</option>
                            <?php foreach($columns as $column) { ?>
                                <option <?php if(isset($connectorArr[$element['_id']]) && $connectorArr[$element['_id']] == $column) { echo 'selected'; } ?>><?php echo $column; ?></option>
                            <?php } ?>
                            </select>

                            <div class="newColumns" style="display:none;">
                                <div class="newColumns_input">
                                    <input type="text" name="newColumns_input[]">
                                    <span class="delete">X</span>
                                </div>
                                <div class="newColumns_select" style="display:none;">
                                    <select name="newColumns_select[]">
                                        <option></option>
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php
                }
                ?>

                <div id="saveDSButton" style="display:none;"><button>Save Datasource</button></div>
            </form>
        </div>
    </div>
<script>
(function() {
    $(document).on("click", ".newColumns .delete", function() {
        $(this).closest('.newColumns').find('.newColumns_input').hide();
        $(this).closest('.newColumns').find('.newColumns_input').find('input').val('');
        $(this).closest('.newColumns').find('.newColumns_select').show();
    });

    $(document).on("change", "#datasourceId", function() {
        if($(this).val() && $(this).val()!='new') {

            $('select.dColumns').show();
            $('.newColumns').hide();
            $('#saveDSButton').hide();

            $.ajax({
                type: "GET",
                url: '/response/<?php echo $this->urlpart[2] ?>/datasource/?getColumns='+$(this).val(),
                data:[],
                success: function( response ) {
                    var data = JSON.parse(response);
                    if(data[0]) {
                        var columns = JSON.parse(data[0]['columns']);
                        var selectColumns = $(".dColumns");
                        selectColumns.each(function() {
                            var $this = $(this);
                            $this.html("<option value=''>Select Column</option>");
                            $.each(columns, function(idx, column) {
                                $this.append("<option value='"+column+"'>"+column+"</option>")
                            });
                        });

                    }
                }
            });
        } else if($(this).val()=='new') {
            $('select.dColumns').hide();
            $('.newColumns').show();
            $('#saveDSButton').show();
        }

    });

    $(document).on("change", "select.dColumns", function() {
        var form = $(this).closest('form');
        $.ajax({
            type: "POST",
            url: form.attr( 'action' ) + '?ajax=true',
            data: form.serialize(),
            success: function( response ) {
            }
        });
    });
})();
</script>
<?php
}

function outputResponsedetail() {
    $formid = $this->urlpart[2];
    $form = $this->lo->getForm(array('form_id'=>$formid));
    $this->InsideHeader();

    $this->InsideCardWrapperOpen();

    $statuslists = array();
    $statuses = json_decode($form['responseStatusLists'], true);
    foreach($statuses as $status) {
        $statuslists[$status['_id']] = $status['label'];
    }

    $dateformat = $this->pl->getUserDateFormat($this->lUser);
    $timeformat = $this->pl->getUserTimeFormat($this->lUser);

    $submittedAt = date($dateformat . ' ' . $timeformat, strtotime($this->response['dateCreated']));

    if($this->lUser['timezone']) {
        $tz = $this->lUser['timezone'];
        $timestamp = strtotime($this->response['dateCreated']);
        $dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
        $dt->setTimestamp($timestamp); //adjust the object to correct timestamp
        $submittedAt = $dt->format($dateformat . ' ' . $timeformat);
    }

?>
<style>
.moveStatus select {
    padding:2px 10px;
    margin-left:3px;
}

.delete, .print {
    float:left;
    margin-right: 10px;
    margin-top: 4px;
}

.statusLabel {
    float:left;
    margin-left: 30px;
    margin-top: 4px;
}
.headerresponse {
    margin-top: 10px;
}

.headerresponse .title {
    float:left;
}

.headerresponse .date {
    margin-left: 20px;
}

.headerresponse .actions {
    float:right;
}
</style>
<div class="headerresponse">
    <div class="title"><a href="/response/<?php echo $this->urlpart[2]; ?>/"><?php echo $form['name']; ?></a> > Response <?php echo $this->response['_id']; ?> <span class="date">Submitted at: <strong><?php echo $submittedAt; ?></strong></span></div>
    <div class="actions">
        <div class="item" data-id="<?php echo $this->response['_id']; ?>">
            <a class="center print" no-wank="" href="/forms/<?php echo $form['_id']; ?>/?response=<?php echo $this->response['_id']; ?>&action=print" target="_blank" style="margin-right:0.5rem;float:left" title="print"><i class="fas fa-print"></i> Print</a>
            <?php if($this->pl->canUser($this->lAccount, 'delete', $form)) { ?>
                <a class="center delete" onclick="return confirm('Are you sure you want to delete?')" href="<?php echo $this->pl->set_csrfguard('/response/delete/'.$this->response['_id'].'/?f='.$form['_id'],'deleteresponse'); ?>" style="color:red;float:left" title="remove"><i class="icon-close-thick"></i> Delete</a>
            <?php } ?>
            <!-- <a class="center" no-wank="" href="/pdf/<?php echo $form['_id']; ?>/<?php echo $this->response['_id']; ?>/" target="_blank" style="margin-left:0.5rem;"><i class="icon-print"></i></a> -->
            <div class="statusLabel">Status:</div>
            <div class="moveStatus" style="float:left">
                <select name="moveStatusInput" class="<?php if($statuslists[$this->response['status']]) {echo 'hasStatus';} ?>">
                <?php if(!$statuslists[$this->response['status']]) { ?>
                    <option value="">Move to</option>
                <?php } ?>
                <?php
                foreach($statuslists as $k=>$tab) {
                    if($k!='all') {
                ?>
                    <option value="<?php echo $k ?>" <?php if($k==$this->response['status']) {echo 'selected';} ?>><?php echo $tab; ?></option>
                <?php
                    }
                } ?>
                </select>
            </div>
        </div>
    </div>
</div>
<table class="submissions-table submissions-static-header" style="margin-top:75px">
<?php

$this->response['data'] = str_replace('\r\n', '<br>', $this->response['data']);
$rdata = json_decode(str_replace('\\','',$this->response['data']), true);
if(!$rdata) {
    $rdata = json_decode($this->response['data'], true);
}
for($rd=0;$rd<count($rdata);$rd++){
    if($this->response['encrypted']) {
        $rdata[$rd]['value'] = $this->pl->decrypt($rdata[$rd]['value']);
    }
    $label='';
    if(!$rdata[$rd]['label']){
        $label = $this->pl->getElementLabel($form['elements'], $rdata[$rd]['field']);
    }
  ?><tr><td class="label" width="35%" valign="top"><strong><?php echo ucfirst($label ?: $rdata[$rd]['label']);?>:</strong></td>
  <td width="65%" valign="top">
  <?php
  if(is_array($rdata[$rd]['value'])){
    echo implode(', ', $rdata[$rd]['value']);
  } else if($this->pl->is_base64($rdata[$rd]['value'])) {
    echo '<img src="'.$rdata[$rd]['value'].'">';
  } else {
    $parts=explode('.',$rdata[$rd]['value']);
    if(count($parts) > 1 && strlen($parts[0]) == 32 && strlen($parts[1]) < 5){
        if(isset($rdata[$rd]['org_name'])) {
            if($this->pl->isUrlImage('/file/'.$rdata[$rd]['value'].'/?f='.urlencode($rdata[$rd]['org_name']))) {
                echo '<li><a href="/file/'.$rdata[$rd]['value'].'/?f='.urlencode($rdata[$rd]['org_name']).'" target="_blank"><img src="/file/'.$rdata[$rd]['value'].'/?f='.urlencode($rdata[$rd]['org_name']).'" style="max-width:200px;margin:5px;"></a></li>';
            } else {
                echo '<a href="/file/'.$rdata[$rd]['value'].'/?f='.urlencode($rdata[$rd]['org_name']).'" target="_blank">'.htmlentities($rdata[$rd]['org_name']).'</a>';
            }
        } else {
            if($this->pl->isUrlImage('/file/'.$rdata[$rd]['value'].'/?f='.urlencode($rdata[$rd]['org_name']))) {
                echo '<li><a href="/file/'.$rdata[$rd]['value'].'/" target="_blank"><img src="/file/'.$rdata[$rd]['value'].'/" style="max-width:200px;margin:5px;"></a></li>';
            } else {
                echo '<a href="/file/'.$rdata[$rd]['value'].'/" target="_blank">'.htmlentities($rdata[$rd]['value']).'</a>';
            }
        }
    } else {
           $parts = explode(';;', $rdata[$rd]['value']);
           if(count($parts) > 1 && isset($rdata[$rd]['org_name'])) {
               $org_names = explode(';;', $rdata[$rd]['org_name']);
               $ctr=0;
               echo '<ul>';
               foreach($parts as $file) {
                   if($this->pl->isUrlImage('/file/'.$file.'/?f='.urlencode($org_names[$ctr]))) {
                       echo '<li><a href="/file/'.$file.'/?f='.urlencode($org_names[$ctr]).'" target="_blank"><img src="/file/'.$file.'/?f='.urlencode($org_names[$ctr]).'" style="max-width:200px;margin:5px;"></a></li>';
                   } else {
                       echo '<li><a href="/file/'.$file.'/?f='.urlencode($org_names[$ctr]).'" target="_blank">'.htmlentities($org_names[$ctr]).'</a></li>';
                   }
                   $ctr++;
               }
               echo '</ul>';
           } else {
               $val = stripslashes(htmlentities($rdata[$rd]['value']));
               $val = str_replace("&lt;br&gt;", "<br>", $val);
               echo $val;
           }
    }

  }
   ?>
</td></tr><?php
}
?></table>

<script>
$('[name=moveStatusInput]').on("change", function() {
    var responseId = $(this).closest('.item').data('id');
    var newStatus = $(this).val();

    $.ajax({
        type: "POST",
        url: '/response/<?php echo $formid; ?>/?ajax=true',
        data: { status:newStatus, responseId:responseId, type:'moveStatus' },
        success: function( response ) {
        }
    });
});
</script>
<?php
    $this->InsideCardWrapperClose();

    $this->OutputMarketingFooter();
}

function outputresponselist($forms=null) {
    $m = "submission";
  	$f = 0;
  	$formid = $this->urlpart[2];
  	$viewmode = 'all';
	if($this->urlpart[3]) {$viewmode = $this->urlpart[3];}
	$page = 1;
	if($this->urlpart[4]) {$page = $this->urlpart[4];}

    if($this->urlpart[3] == 'datasource') {
        $this->outputResponseDatasource();
    } else {
?>
<ul class="submenu_settings" style="text-align:center;background:none;margin-bottom:15px">
    <li><a href="/stats/<?php echo $this->urlpart[2]; ?>/" class="btn btn-info">Stats</a></li>
    <li><a href="/response/<?php echo $this->urlpart[2]; ?>/datasource/" class="btn btn-info">Connect Datasource</a></li>
    <li><a href="/csv/<?php echo $forms[$f]['_id']; ?>/<?php echo $response; ?>" class="btn btn-info"><i class="fa fa-download"></i> <?php echo $this->pl->trans($m,'Export CSV'); ?></a></li>
</ul>
<div>
    <?php

    $totals = $this->lo->getFormTotalResponses(array('formid'=>$forms[$f]['_id']));

    $rows=$this->lo->paginatedSubmissions(array('formid'=>$forms[$f]['_id'],'uid'=>$this->uid, 'page'=>$page, 'status'=>$viewmode, 'totalRows'=>$totals[$viewmode]));

    $tabs = array();

    $statuses = json_decode($forms[$f]['responseStatusLists'], true);
    foreach($statuses as $status) {
        $tabs[$status['_id']] = $status['label'];
    }

    $dateformat = $this->pl->getUserDateFormat($this->lUser);
    $timeformat = $this->pl->getUserTimeFormat($this->lUser);
    ?>


    <div class="row Responses-Content">
        <div class="col">
            <div class="row">
              <div class="col-lg-12 pb-2 tabs">
                  <ul class="links">
                      <?php foreach($tabs as $k=>$tab) { ?>
                          <li id="tab_<?php echo $k; ?>" data-status="<?php echo $k; ?>">
                              <div class="btn mr-1 mb-1 <?php if($k==$viewmode) {echo "btn-secondary";} else {echo "btn-outline-secondary";} ?> editablemenu" style="position:relative;display:none">
                                  <div class="statusLabel statusEditable" contenteditable="true" data-id="<?php echo $k; ?>"><?php echo $tab; ?></div>
                                  <div class="count"><?php echo $totals[$k]; ?></div>
                                  <?php if(!in_array($k, ['all', 'new', 'viewed'])) { ?>
                                      <div class="delete statusDelete" data-id="<?php echo $k; ?>"><i class="fa fa-times"></i></div>
                                  <?php } ?>
                              </div>
                              <a class="btn mr-1 mb-1 <?php if($k==$viewmode) {echo "btn-secondary";} else {echo "btn-outline-secondary";} ?> noneditablemenu" href="/response/<?php echo $this->urlpart[2] ?>/<?php echo $k; ?>/">
                                  <div class="statusLabel">
                                      <?php echo $tab; ?>
                                  </div>
                                  <div class="count"><?php echo $totals[$k]; ?></div>
                              </a>
                          </li>
                      <?php } ?>
                      <li style="display:none">
                          <div class="newStatus newStatusForm btn btn-outline-secondary mr-1 mb-1" style="padding:5px;">
                              <div class="statusLabel">
                                  <input type="text" id="newStatusInput" class="form-control" style="height:25px;float:left;width:120px" placeholder="New Status"/>
                                  <span style="float:left;margin-left:5px;margin-top:5px;"><span class="newStatusSave">Done</span><span class="newStatusCancel" style="display:none">Cancel</span></span>
                              </div>
                          </div>
                      </li>
                      <li>
                          <a href="javascript:;" class="btn btn-outline-secondary mr-1 mb-1 newStatus newStatusLink">
                              <div class="statusLabel">
                                  <span style="color:#42A3B8;">Edit Status</span>
                              </div>
                          </a>
                      </li>
                  </ul>

              </div>
            </div>

            <div class="row">
              <div class="col">

                  <div class="content">

                      <?php foreach($tabs as $dk=>$d) { ?>
                          <div class="data <?php if($dk==$viewmode) {echo "active";} ?>" id="data_<?php echo $dk; ?>">
                              <ul>
                              <?php
                              if(count($rows['data']) == 0) {
                                  echo 'No content';
                              }
                              ?>

                              <?php
								  foreach($rows['data'] as $row){

									$submittedAt = date($dateformat . ' ' . $timeformat, strtotime($row['dateCreated']));

									if($this->lUser['timezone']) {
										$tz = $this->lUser['timezone'];
										$timestamp = strtotime($row['dateCreated']);
										$dt = new DateTime("now", new DateTimeZone($tz)); //first argument "must" be a string
										$dt->setTimestamp($timestamp); //adjust the object to correct timestamp
										$submittedAt = $dt->format($dateformat . ' ' . $timeformat);
									}
								?>

                            <li class="response-item mb-4" data-id="<?php echo $row['_id']; ?>">
                              <ul>
                                <li class="response-item-header">
                                  <div class="row">
                                    <div class="col-lg-6 my-auto">
                                      <h6 class="m-0">
                                          <a title="view details" class="formlets-blue" href="/responsedetail/<?php echo $forms[$f]['_id']; ?>/<?php echo $row['_id']; ?>/">Response <?php echo $row['_id'];?></a>
                                          <span class="px-2">|</span>
                                          <span>Submitted at: <strong><?php echo $submittedAt; ?></strong></span>
                                      </h6>
                                    </div>
                                    <div class="col-lg-6 mt-2 text-lg-right">
                                      <a href="/responsedetail/<?php echo $forms[$f]['_id']; ?>/<?php echo $row['_id']; ?>/" class="btn btn-outline-secondary mr-1"><i class="fas fa-eye"></i><span class="pl-1 hide-mobile">More Details</span></a>
                                      <a href="/forms/<?php echo $forms[$f]['_id']; ?>/?response=<?php echo $row['_id']; ?>&action=print" target="_blank" class="btn btn-outline-secondary mr-1"><i class="fas fa-print"></i><span class="pl-1 hide-mobile">Print</span></a>
                                      <?php if($this->pl->canUser($this->lAccount, 'delete', $forms[$f])) { ?>
                                      <a onclick="return confirm('Are you sure you want to delete?')" href="<?php echo $this->pl->set_csrfguard('/response/delete/'.$row['_id'].'/?f='.$forms[$f]['_id'],'deleteresponse'); ?>" class="btn btn-outline-danger mr-1"><i class="fas fa-trash-alt"></i><span class="pl-1 hide-mobile">Delete</span></a>
                                      <?php } ?>
                                      <select name="moveStatusInput" class="form-control d-inline w-auto">
                                      <?php if(!$tabs[$row['status']]) { ?>
                                          <option value="">Move to</option>
                                      <?php } ?>
                                      <?php
                                      foreach($tabs as $k=>$tab) {
                                          if($k!='all') {
                                      ?>
                                          <option value="<?php echo $k ?>" <?php if($k==$row['status']) {echo 'selected';} ?>><?php echo $tab; ?></option>
                                      <?php
                                          }
                                      } ?>
                                      </select>
                                    </div>
                                  </div>
                                </li>
                                <li class="response-item-details">
                                  <h6 class="mb-1">Details</h6>
                                    <div class="row">

                                        <?php
                                        unset($rdata);
                                       $row['data'] = str_replace('\r\n', '<br>', $row['data']);
                                       $rdata = json_decode(str_replace('\\','',$row['data']), true);
                                       if(!$rdata) {
                                           $rdata = json_decode($row['data'], true);
                                       }
                                       for($rd=0;$rd<count($rdata);$rd++){
                                           if($row['encrypted']) {
                                               $rdata[$rd]['value'] = $this->pl->decrypt($rdata[$rd]['value']);
                                           }
                                            if($rd==2) { break; }
                                            $label='';
                                            if(!$rdata[$rd]['label']){
                                                $label = $this->pl->getElementLabel($forms[$f]['elements'], $rdata[$rd]['field']);
                                            }
                                        ?>
                                            <div class="col-sm-6 data-col">
                                                <span class="d-block">
                                                    <strong><?php echo ucfirst($label ?: $rdata[$rd]['label']);?>:</strong>
                                                </span>
                                                <span class="d-block">
                                                    <?php
                                                     if(is_array($rdata[$rd]['value'])){
                                                       echo implode(', ', $rdata[$rd]['value']);
                                                     } else if($this->pl->is_base64($rdata[$rd]['value'])) {
                                                       echo '<img src="'.$rdata[$rd]['value'].'">';
                                                     } else {
                                                       $parts=explode('.',$rdata[$rd]['value']);
                                                       if(count($parts) > 1 && strlen($parts[0]) == 32 && strlen($parts[1]) < 5){
                                                           if(isset($rdata[$rd]['org_name'])) {
                                                               if($this->pl->isUrlImage('/file/'.$rdata[$rd]['value'].'/?f='.urlencode($rdata[$rd]['org_name']))) {
                                                                   echo '<a href="/file/'.$rdata[$rd]['value'].'/?f='.urlencode($rdata[$rd]['org_name']).'" target="_blank"><img src="/file/'.$rdata[$rd]['value'].'/?f='.urlencode($rdata[$rd]['org_name']).'" style="max-width:200px;margin:5px;" /></a>';
                                                               } else {
                                                                   echo '<a href="/file/'.$rdata[$rd]['value'].'/?f='.urlencode($rdata[$rd]['org_name']).'" target="_blank">'.htmlentities($rdata[$rd]['org_name']).'</a>';
                                                               }
                                                           } else {
                                                               if($this->pl->isUrlImage('/file/'.$rdata[$rd]['value'].'/?f='.urlencode($rdata[$rd]['org_name']))) {
                                                                   echo '<a href="/file/'.$rdata[$rd]['value'].'/" target="_blank"><img src="/file/'.$rdata[$rd]['value'].'/" style="max-width:200px;margin:5px;"></a>';
                                                               } else {
                                                                   echo '<a href="/file/'.$rdata[$rd]['value'].'/" target="_blank">'.htmlentities($rdata[$rd]['value']).'</a>';
                                                               }
                                                           }
                                                       } else {
                                                              $parts = explode(';;', $rdata[$rd]['value']);
                                                              if(count($parts) > 1 && isset($rdata[$rd]['org_name'])) {
                                                                  $org_names = explode(';;', $rdata[$rd]['org_name']);
                                                                  $ctr=0;
                                                                  echo '<ul>';
                                                                  foreach($parts as $file) {
                                                                      if($this->pl->isUrlImage('/file/'.$file.'/?f='.urlencode($org_names[$ctr]))) {
                                                                          echo '<li><a href="/file/'.$file.'/?f='.urlencode($org_names[$ctr]).'" target="_blank"><img src="/file/'.$file.'/?f='.urlencode($org_names[$ctr]).'" style="max-width:200px;margin:5px;"></a></li>';
                                                                      } else {
                                                                          echo '<li><a href="/file/'.$file.'/?f='.urlencode($org_names[$ctr]).'" target="_blank">'.htmlentities($org_names[$ctr]).'</a></li>';
                                                                      }
                                                                      $ctr++;
                                                                  }
                                                                  echo '</ul>';
                                                              } else {
                                                                  $val = stripslashes(htmlentities($rdata[$rd]['value']));
                                                                  $val = str_replace("&lt;br&gt;", "<br>", $val);
                                                                  echo $val;
                                                              }
                                                       }

                                                     }
                                                   ?>
                                               </span>
                                            </div>
                                        <?php
                                        }
                                        ?>
                                    </div>
                                </li>
                              </ul>
                            </li>
							   <?php } ?>
                            </ul>

                            <div class="pull-right"><?php echo $this->create_pagination($rows["page"], $rows["rows_count"], 10, 'response/' . $forms[$f]['_id'] . '/' . $viewmode); ?></div>
                          </div>
                      <?php } ?>
                  </div>
              </div>
            </div>
        </div>
    </div>
</div>

<script>

$('.newStatusLink').on("click", function() {

    $('.editablemenu').show();
    $('.noneditablemenu').hide();

    $(this).closest('li').hide();
    var form = $('.newStatusForm').closest('li');
    form.show();
    form.find('#newStatusInput').focus();
});

$('.newStatusCancel').on("click", function() {
    $(this).closest('li').hide();
    $('.newStatusLink').closest('li').show();
});

$('.statusDelete').on("click", function() {
    var status = $(this).data('id');
    if(confirm('are you sure you want to delete the status?')) {
        $.ajax({
            type: "POST",
            url: '/response/<?php echo $formid; ?>/?ajax=true',
            data: { status:status, type:'responseStatus', action:'delete' },
            success: function( response ) {
            }
        });

        $(this).closest('li').remove();
        $("#data_"+status).remove();

        setTimeout(function() {
            window.location.href='/response/<?php echo $formid; ?>/all/';
        }, 500);
    }

});

$('.newStatusSave').on("click", function() {
    var form = $('.newStatusForm').closest('li');
    var newStatus = form.find('#newStatusInput').val();
    if($.trim(newStatus) == '') {
        $('.newStatusCancel').trigger('click');
    } else {
        $.ajax({
            type: "POST",
            url: '/response/<?php echo $formid; ?>/?ajax=true',
            data: { label:newStatus, type:'responseStatus', action:'new' },
            success: function( response ) {
            }
        });

        $('.newStatusCancel').trigger('click');

        setTimeout(function() {
            window.location.reload();
        }, 500);
    }

    $('.editablemenu').hide();
    $('.noneditablemenu').show();
});

$('.statusEditable').on('focus', function() {
    before = $(this).html();
}).on('blur keyup paste', function() {
    if (before != $(this).html()) { $(this).trigger('change'); }
});

$('.statusEditable').on('change', function() {
    var status = $(this).data('id');
    var newLabel = $.trim($(this).html());

    var li = $(this).closest('li');
    li.find('.noneditablemenu .statusLabel').html(newLabel);

    $.ajax({
        type: "POST",
        url: '/response/<?php echo $formid; ?>/?ajax=true',
        data: { status:status, label:newLabel, type:'responseStatus', action:'edit' },
        success: function( response ) {
        }
    });

});

$('[name=moveStatusInput]').on("change", function() {
    var responseId = $(this).closest('.response-item').data('id');
    var newStatus = $(this).val();

    $.ajax({
        type: "POST",
        url: '/response/<?php echo $formid; ?>/?ajax=true',
        data: { status:newStatus, responseId:responseId, type:'moveStatus' },
        success: function( response ) {
        }
    });

    setTimeout(function() {
        window.location.reload();
    }, 500);
});
</script>
<?php

    }
}

function OutputAdvancethemes() {
	$m = "advancethemes";
	$themes = $this->lo->listThemes(array("uid"=>$this->uid, "type"=>"Advanced"));
	$theme = null;
	if($this->urlpart[2]<>'new' && $this->urlpart[2]<>'' && $this->urlpart[3]=='edit') {
		$theme = $this->lo->listThemes(array("uid"=>$this->uid, "id"=>$this->urlpart[2], "type"=>"Advanced"));
		if(count($theme)) {
			$theme = $theme[0];
		}
	}
	$this->InsideHeader();
?>
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.25.0/codemirror.min.css">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.25.0/theme/mdn-like.min.css">
	<link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.25.0/addon/scroll/simplescrollbars.min.css">
	<style>
	.formline {
		border: 0px;
	}
	.el {
		border: 0px;
	}
	#saveButton {
		position: fixed;
    	right: 0;
    	margin: 15px;
    	z-index: 10;
	}
	.page {
		overflow: inherit;
		background: inherit;
	}
	.gr {
		float: none;
	}
	.pad-half {
		margin-right: 0px;
	}
	.CodeMirror{
		height: 470px;
		max-width: 478px;
		font-size: 14px;
	}

<?php if($theme) {
echo $theme['themeCSS'];
} else { ?>
body {
	line-height: 1.25;
}
body, .fcc,
[class~="stage"] {
	background: #F5F5F5 !important;
}
.fc {
	background: #FFFFFF !important;
	border-color: #D6D7D6 !important;
	font-family: <?php echo $theme ? $theme['themeFont']:'Source Sans Pro'; ?> !important;
}
.fc button.button {
	background-color: #4BAEC2 !important;
	color: #ffffff !important;
}
.fc .text {
	background: #FFFFFF !important;
	border-color: #D6D7D6 !important;
	box-shadow: 0 0 0 1px #D6D7D6 !important;
	font-family: <?php echo $theme ? $theme['themeFont']:'Source Sans Pro'; ?> !important;
}
.fc .text:hover {
	border-color: #3E4943 !important;
	box-shadow: 0 0 0 1px #3E4943 !important;
}
.fc .text:focus {
	border-color: #4BAEC2 !important;
	box-shadow: 0 0 0 1px #4BAEC2 !important;
}
.fc input + i {
	border-color: #D6D7D6 !important;
}
.fc input + i:hover {
	border-color: #3E4943 !important;
}
.fc input:focus + i {
	border-color: #4BAEC2 !important;
}
.fc input + i:after {
	-webkit-box-shadow: none !important;
	box-shadow: none !important;
}
.fc input:checked + i {
	background: #4BAEC2 !important;
	border-color: #4BAEC2 !important;
}
.fc input + i:after {
	text-shadow: none !important;
}
.fc .input-group-help {
	background: inherit;
	font-family: <?php echo $theme ? $theme['themeFont']:'Source Sans Pro'; ?> !important;
}
.fc .help {
	background: inherit;
	font-family: <?php echo $theme ? $theme['themeFont']:'Source Sans Pro'; ?> !important;
}
label,
h1,h2,h3,h4,h5,
p:not(.help){
	color: #3E4943 !important;
	font-family: <?php echo $theme ? $theme['themeFont']:'Source Sans Pro'; ?> !important;
}
.fc input, .fc textarea, .fc select {
	font-family: <?php echo $theme ? $theme['themeFont']:'Source Sans Pro'; ?> !important;
}
.fc i.fa {
	color: #3E4943 !important;
}
.fcc h1, h2 {
	color: #3E4943 !important;
	font-family: <?php echo $theme ? $theme['themeFont']:'Source Sans Pro'; ?> !important;
}
.fcc hr {
	color: #3E4943 !important;
	font-family: <?php echo $theme ? $theme['themeFont']:'Source Sans Pro'; ?> !important;
}
.fcc input[type="range"]::-webkit-slider-thumb {
	background: #4BAEC2;
}
.fcc input[type="range"]::-moz-range-thumb {
	background: #4BAEC2;
}
.fcc input[type="range"]::-ms-thumb {
	background: #4BAEC2;
}
.fcc input[type="range"]::-webkit-slider-thumb:active {
	-webkit-box-shadow: 0 0 0 4px white, 0 0 0 6px #4BAEC2;
	box-shadow: 0 0 0 4px white, 0 0 0 6px #4BAEC2;
}
.fcc input[type="range"]::-moz-range-thumb:active {
	background: #4BAEC2;
	-webkit-box-shadow: 0 0 0 4px white, 0 0 0 6px #4BAEC2;
	box-shadow: 0 0 0 4px white, 0 0 0 6px #4BAEC2;
}
.fcc input[type="range"]::-ms-thumb:active {
	background: #4BAEC2;
	-webkit-box-shadow: 0 0 0 4px white, 0 0 0 6px #4BAEC2;
	box-shadow: 0 0 0 4px white, 0 0 0 6px #4BAEC2;
}
.fcc .req-error select,
.fcc .req-error input,
.fcc .req-error textarea,
.fcc .req-error input:focus,
.fcc .req-error input:active,
.fcc .req-error input:hover,
.fcc .req-error textarea:hover {
	border-color: #D1603D !important;
	box-shadow: 0 0 0 1px #D1603D !important;
}
.error .input-group-help,
.req-error .input-group-help,
.error label,
.req-error label {
	color: #D1603D !important;
}
.icon-left>i::after {
    padding-right: 0px;
}
.fc [disabled] {
	background: #f0f0f2;
	border-color: #f0f0f2;
}

.fc label[disabled] {
	background: inherit;
}
<?php } ?>
	</style>
	<?php if($this->urlpart[2]=='') { ?>
	<div id="app" class="stage">
		<div class="scroll">
			<div class="flush centered">
  				<div class="gr centered small-11 large-12 xl-6 settings">
  					<div class="gr fc flush height-100">
  						<div class="gr centered form-list-dashboard">
  							<div class="flush">
  								<div class="gr g12">
  									<div class="right">
			  							<a href="/advancethemes/new/"><button type="button" class="small red"><?php echo $this->pl->trans($m,'Create New Theme'); ?></button></a>
			  						</div>
  								</div>
		  						<h3><?php echo $this->pl->trans($m,'Themes'); ?></h3>
		  						<?php if(count($themes)) { ?>
		  						<table border="0" width="100%">
		  							<thead>
		  								<tr>
		  									<th align="left"><?php echo $this->pl->trans($m,'Name'); ?></th>
		  									<th align="left"><?php echo $this->pl->trans($m,'Action'); ?></th>
		  								</tr>
		  							</thead>
		  							<tbody>
		  								<?php foreach($themes as $theme) { ?>
		  								<tr>
		  									<td><?php echo $theme['name']; ?></td>
		  									<td>
		  										<a href="/advancethemes/<?php echo $theme['_id']; ?>/edit/"><?php echo $this->pl->trans($m,'Edit'); ?></a> |
		  										<a href="<?php echo $this->pl->set_csrfguard('/advancethemes/'.$theme['_id'].'/delete/','deleteadvancedtheme');?>" onclick="return confirm('Are you sure you want to delete that theme?')"><?php echo $this->pl->trans($m,'Delete'); ?></a>
		  									</td>
		  								</tr>
		  								<?php } ?>
		  							</tbody>
		  						</table>
		  						<?php } else { ?>
		  							<?php echo $this->pl->trans($m,'No Theme added yet'); ?>.
		  						<?php } ?>
	  						</div>
	  					</div>
  					</div>
  				</div>
  			</div>
  		</div>
  	</div>
  	<?php } ?>
  	<?php if($this->urlpart[2]<>'' && ($this->urlpart[2]=='new' || $this->urlpart[3]=='edit')) { ?>
  	<form action="" method="POST" id="editorForm">
  		<div class="page">
			<div class="flex-container">
				<div class="flex-row">
					<div id="sidebar" class="sidebar" style="width:500px;">
						<header class="button-header">
							<div class="pad">
							<fieldset>
								<label><?php echo $this->pl->trans($m,'Theme Name'); ?></label>
								<input type="text" name="name" value="<?php echo $theme ? $theme['name']:'' ?>" />
							</fieldset>
							</div>
						</header>
						<div class="sidebar-position">
							<div class="sidebar-settings scroll">
								<div id="stheme" class="sel">
									<div class="pad">
							            <fieldset style="margin-top: 20px;">
							                <label>
							                    <?php echo $this->pl->trans($m,'Global Font'); ?>
							                    <i class="icon-info">
							                        <div class="tooltip-wrapper">
							                            <div class="tooltip">
							                                <p>
							                                    <?php echo $this->pl->trans($m,'This selection changes the font'); ?>.
							                                </p>
							                            </div>
							                        </div>
							                    </i>
							                </label>
							                <div class="select">
							                    <select class="text" id="s_themeFont" prop="themeFont" name="themeFont">
							                        <option value="Arvo" <?php if($theme && $theme['themeFont']=='Arvo') {echo'selected';} ?>>
							                            Arvo
							                        </option>
							                        <option value="Droid Sans" <?php if($theme && $theme['themeFont']=='Droid Sans') {echo'selected';} ?>>
							                            Droid Sans
							                        </option>
							                        <option value="Josefin Slab" <?php if($theme && $theme['themeFont']=='Josefin Slab') {echo'selected';} ?>>
							                            Josefin Slab
							                        </option>
							                        <option value="Lato" <?php if($theme && $theme['themeFont']=='Lato') {echo'selected';} ?>>
							                            Lato
							                        </option>
							                        <option value="Open Sans" <?php if($theme && $theme['themeFont']=='Open Sans') {echo'selected';} ?>>
							                            Open Sans
							                        </option>
							                        <option value="PT Sans" <?php if($theme && $theme['themeFont']=='PT Sans') {echo'selected';} ?>>
							                            PT Sans
							                        </option>
							                        <option value="Roboto" <?php if($theme && $theme['themeFont']=='Roboto') {echo'selected';} ?>>
							                            Roboto
							                        </option>
							                        <option value="Source Sans Pro" <?php if(!$theme || $theme['themeFont']=='Source Sans Pro') {echo'selected';} ?>>
							                            Source Sans Pro
							                        </option>
							                        <option value="Ubuntu" <?php if($theme && $theme['themeFont']=='Ubuntu') {echo'selected';} ?>>
							                            Ubuntu
							                        </option>
							                        <option value="Vollkorn" <?php if($theme && $theme['themeFont']=='Vollkorn') {echo'selected';} ?>>
							                            Vollkorn
							                        </option>
							                    </select>
							                </div>
							            </fieldset>
							            <fieldset>
							            	<label><?php echo $this->pl->trans($m,'Custom CSS'); ?></label>
<textarea name="themeCSS" id="code" style="height: 470px;">
<?php if($theme) { echo $theme['themeCSS']; } else { ?>
body {
	line-height: 1.25;
}
body, .fcc,
[class~="stage"] {
	background: #F5F5F5 !important;
}
.fc {
	background: #FFFFFF !important;
	border-color: #D6D7D6 !important;
}
.fc button.button {
	background-color: #4BAEC2 !important;
	color: #ffffff !important;
}
.fc .text {
	background: #FFFFFF !important;
	border-color: #D6D7D6 !important;
	box-shadow: 0 0 0 1px #D6D7D6 !important;
}
.fc .text:hover {
	border-color: #3E4943 !important;
	box-shadow: 0 0 0 1px #3E4943 !important;
}
.fc .text:focus {
	border-color: #4BAEC2 !important;
	box-shadow: 0 0 0 1px #4BAEC2 !important;
}
.fc input + i {
	border-color: #D6D7D6 !important;
}
.fc input + i:hover {
	border-color: #3E4943 !important;
}
.fc input:focus + i {
	border-color: #4BAEC2 !important;
}
.fc input + i:after {
	-webkit-box-shadow: none !important;
	box-shadow: none !important;
}
.fc input:checked + i {
	background: #4BAEC2 !important;
	border-color: #4BAEC2 !important;
}
.fc input + i:after {
	text-shadow: none !important;
}
.fc .input-group-help {
	background: inherit;
}
.fc .help {
	background: inherit;
}
label,
h1,h2,h3,h4,h5,
p:not(.help){
	color: #3E4943 !important;
}
.fc i.fa {
	color: #3E4943 !important;
}
.fcc h1, h2 {
	color: #3E4943 !important;
}
.fcc hr {
	color: #3E4943 !important;
}
.fcc input[type="range"]::-webkit-slider-thumb {
	background: #4BAEC2;
}
.fcc input[type="range"]::-moz-range-thumb {
	background: #4BAEC2;
}
.fcc input[type="range"]::-ms-thumb {
	background: #4BAEC2;
}
.fcc input[type="range"]::-webkit-slider-thumb:active {
	-webkit-box-shadow: 0 0 0 4px white, 0 0 0 6px #4BAEC2;
	box-shadow: 0 0 0 4px white, 0 0 0 6px #4BAEC2;
}
.fcc input[type="range"]::-moz-range-thumb:active {
	background: #4BAEC2;
	-webkit-box-shadow: 0 0 0 4px white, 0 0 0 6px #4BAEC2;
	box-shadow: 0 0 0 4px white, 0 0 0 6px #4BAEC2;
}
.fcc input[type="range"]::-ms-thumb:active {
	background: #4BAEC2;
	-webkit-box-shadow: 0 0 0 4px white, 0 0 0 6px #4BAEC2;
	box-shadow: 0 0 0 4px white, 0 0 0 6px #4BAEC2;
}
.fcc .req-error select,
.fcc .req-error input,
.fcc .req-error textarea,
.fcc .req-error input:focus,
.fcc .req-error input:active,
.fcc .req-error input:hover,
.fcc .req-error textarea:hover {
	border-color: #D1603D !important;
	box-shadow: 0 0 0 1px #D1603D !important;
}
.error .input-group-help,
.req-error .input-group-help,
.error label,
.req-error label {
	color: #D1603D !important;
}
.icon-left>i::after {
    padding-right: 0px;
}
.fc [disabled] {
	background: #f0f0f2;
	border-color: #f0f0f2;
}

.fc label[disabled] {
	background: inherit;
}
<?php } ?>
</textarea>
							            </fieldset>
							    	</div>
								</div>
							</div>
						</div>
					</div>
					<div class="flex-col" style="height:93vh">
						<button type="submit" id="saveButton"><?php echo $this->pl->trans($m,'Save Theme'); ?></button>
						<div class="gc fcc deployed centered small-12 medium-8 large-6 clearfix" style="width:80%">
						    <h1>
						        <?php echo $this->pl->trans($m,'Sample Form'); ?>
						        <span class="help">
						            <?php echo $this->pl->trans($m,'this is a test description of the form'); ?>
						        </span>
						    </h1>
						    <div class="fc gc pad">
						            <div class="page " id="page_0">
						                <div class="gr flush">
						                    <div class="gc pad-compact">
						                        <div class="gc pad-half medium-6">
						                            <fieldset id="wm6Atch5Uhlbbc8S" type="TEXT">
						                                <label>
						                                    <?php echo $this->pl->trans($m,'Email'); ?>
						                                </label>
						                                <div class="controls-container">
						                                    <div class="icon icon-left">
						                                        <div class="controls">
						                                            <i class="ionicons ion-android-close" validate-clear="">
						                                            </i>
						                                        </div>
						                                        <input class="text" data-inputmask="" name="wm6Atch5Uhlbbc8S" placeholder="" type="text" validate-email="" value="">
						                                            <i class="fa fa-envelope">
						                                            </i>
						                                        </input>
						                                    </div>
						                                    <div class="gc input-group-help error">
						                                    </div>
						                                    <div class="gc input-group-help req-error">
						                                    </div>
						                                </div>
						                            </fieldset>
						                        </div>
						                        <div class="gc pad-half medium-6">
						                            <fieldset id="dAQPX62WGKs6itxA" type="TEXT">
						                                <label>
						                                    <?php echo $this->pl->trans($m,'Text input short'); ?>
						                                </label>
						                                <div class="controls-container">
						                                    <input class="text" data-inputmask="" name="dAQPX62WGKs6itxA" placeholder="" type="text" value="">
						                                        <div class="gc input-group-help error">
						                                        </div>
						                                        <div class="gc input-group-help req-error">
						                                        </div>
						                                    </input>
						                                </div>
						                            </fieldset>
						                        </div>
						                    </div>
						                </div>
						                <div class="gr flush">
						                    <div class="gc pad-compact">
						                        <div class="gc pad-half medium-12">
						                            <fieldset id="bwoxxEKIHDEAGF8u" type="SELECT">
						                                <label>
						                                    <?php echo $this->pl->trans($m,'Select Dropdown'); ?>
						                                </label>
						                                <div class="select">
						                                    <select class="text" name="bwoxxEKIHDEAGF8u">
						                                        <option disabled="" selected="">
						                                            <?php echo $this->pl->trans($m,'Select'); ?>
						                                        </option>
						                                    </select>
						                                </div>
						                                <p class="help inline">
						                                    <?php echo $this->pl->trans($m,'Example Inline Instruction'); ?>
						                                </p>
						                                <div class="gc input-group-help error">
						                                </div>
						                                <div class="gc input-group-help req-error">
						                                </div>
						                            </fieldset>
						                        </div>
						                    </div>
						                </div>
						                <div class="gr flush">
						                    <div class="gc pad-compact">
						                        <div class="gc pad-half medium-6">
						                            <fieldset id="hbRJKwflUDgjmpC4" type="RADIO">
						                                <label>
						                                    <?php echo $this->pl->trans($m,'Single choice'); ?>
						                                </label>
						                                <div>
						                                    <label class="option">
						                                        <input id="" name="hbRJKwflUDgjmpC4" type="radio" value="Option 1">
						                                            <?php echo $this->pl->trans($m,'Option 1'); ?>
						                                            <i>
						                                            </i>
						                                        </input>
						                                    </label>
						                                </div>
						                                <div>
						                                    <label class="option">
						                                        <input id="" name="hbRJKwflUDgjmpC4" type="radio" value="Option 2">
						                                            <?php echo $this->pl->trans($m,'Option 2'); ?>
						                                            <i>
						                                            </i>
						                                        </input>
						                                    </label>
						                                </div>
						                                <div>
						                                    <label class="option">
						                                        <input id="" name="hbRJKwflUDgjmpC4" type="radio" value="Option 3">
						                                            <?php echo $this->pl->trans($m,'Option 3'); ?>
						                                            <i>
						                                            </i>
						                                        </input>
						                                    </label>
						                                </div>
						                                <div class="gc input-group-help error">
						                                </div>
						                                <div class="gc input-group-help req-error">
						                                </div>
						                            </fieldset>
						                        </div>
						                        <div class="gc pad-half medium-6">
						                            <fieldset id="AntpnKmpVWzjHN6k" type="CHECKBOX">
						                                <label>
						                                    <?php echo $this->pl->trans($m,'Multiple choice'); ?>
						                                </label>
						                                <div>
						                                    <label class="option">
						                                        <input id="" name="AntpnKmpVWzjHN6k[]" type="checkbox" value="Option 1">
						                                            <?php echo $this->pl->trans($m,'Option 1'); ?>
						                                            <i>
						                                            </i>
						                                        </input>
						                                    </label>
						                                </div>
						                                <div>
						                                    <label class="option">
						                                        <input id="" name="AntpnKmpVWzjHN6k[]" type="checkbox" value="Option 2">
						                                            <?php echo $this->pl->trans($m,'Option 2'); ?>
						                                            <i>
						                                            </i>
						                                        </input>
						                                    </label>
						                                </div>
						                                <div>
						                                    <label class="option">
						                                        <input id="" name="AntpnKmpVWzjHN6k[]" type="checkbox" value="Option 3">
						                                            <?php echo $this->pl->trans($m,'Option 3'); ?>
						                                            <i>
						                                            </i>
						                                        </input>
						                                    </label>
						                                </div>
						                                <div class="gc input-group-help error">
						                                </div>
						                                <div class="gc input-group-help req-error">
						                                </div>
						                            </fieldset>
						                        </div>
						                    </div>
						                </div>
						                <div class="gr flush">
						                    <div class="gc pad-compact">
						                        <div class="gc pad-half medium-12" style="padding:0px">
						                            <fieldset id="2rchFidNsuFnd46u" type="US_ADDRESS">
						                                <div class="gc" style="padding-top: 7.5px;">
						                                    <label>
						                                        <?php echo $this->pl->trans($m,'Address'); ?>
						                                    </label>
						                                </div>
						                                <div class="gc pad-half-all" style="padding-top: 0; padding-bottom: 0;">
						                                    <div class="gc pad-half-compact g12">
						                                        <input class="text static" name="addr_1_2rchFidNsuFnd46u" placeholder="Address 1" type="text">
						                                        </input>
						                                    </div>
						                                    <div class="gc pad-half-compact g12">
						                                        <input class="text static" name="addr_2_2rchFidNsuFnd46u" placeholder="" type="text">
						                                        </input>
						                                    </div>
						                                    <div class="gc pad-half-compact medium-12 large-4">
						                                        <input class="text static" name="city_2rchFidNsuFnd46u" placeholder="" type="text">
						                                        </input>
						                                    </div>
						                                    <div class="gc pad-half-compact medium-0 large-1">
						                                    </div>
						                                    <div class="gc pad-half-compact medium-6 large-4">
						                                        <fieldset class="select">
						                                            <select class="text" name="state_2rchFidNsuFnd46u">
						                                                <option disabled="" selected="" style="display:none;" value="">
						                                                </option>
						                                                <option value="AL">
						                                                    AL
						                                                </option>
						                                                <option value="AK">
						                                                    AK
						                                                </option>
						                                                <option value="AZ">
						                                                    AZ
						                                                </option>
						                                                <option value="AR">
						                                                    AR
						                                                </option>
						                                                <option value="CA">
						                                                    CA
						                                                </option>
						                                                <option value="CO">
						                                                    CO
						                                                </option>
						                                                <option value="CT">
						                                                    CT
						                                                </option>
						                                                <option value="DC">
						                                                    DC
						                                                </option>
						                                                <option value="DE">
						                                                    DE
						                                                </option>
						                                                <option value="FL">
						                                                    FL
						                                                </option>
						                                                <option value="GA">
						                                                    GA
						                                                </option>
						                                                <option value="HI">
						                                                    HI
						                                                </option>
						                                                <option value="ID">
						                                                    ID
						                                                </option>
						                                                <option value="IL">
						                                                    IL
						                                                </option>
						                                                <option value="IN">
						                                                    IN
						                                                </option>
						                                                <option value="IA">
						                                                    IA
						                                                </option>
						                                                <option value="KS">
						                                                    KS
						                                                </option>
						                                                <option value="KY">
						                                                    KY
						                                                </option>
						                                                <option value="LA">
						                                                    LA
						                                                </option>
						                                                <option value="ME">
						                                                    ME
						                                                </option>
						                                                <option value="MD">
						                                                    MD
						                                                </option>
						                                                <option value="MA">
						                                                    MA
						                                                </option>
						                                                <option value="MI">
						                                                    MI
						                                                </option>
						                                                <option value="MN">
						                                                    MN
						                                                </option>
						                                                <option value="MS">
						                                                    MS
						                                                </option>
						                                                <option value="MO">
						                                                    MO
						                                                </option>
						                                                <option value="MT">
						                                                    MT
						                                                </option>
						                                                <option value="NE">
						                                                    NE
						                                                </option>
						                                                <option value="NV">
						                                                    NV
						                                                </option>
						                                                <option value="NH">
						                                                    NH
						                                                </option>
						                                                <option value="NJ">
						                                                    NJ
						                                                </option>
						                                                <option value="NM">
						                                                    NM
						                                                </option>
						                                                <option value="NY">
						                                                    NY
						                                                </option>
						                                                <option value="NC">
						                                                    NC
						                                                </option>
						                                                <option value="ND">
						                                                    ND
						                                                </option>
						                                                <option value="OH">
						                                                    OH
						                                                </option>
						                                                <option value="OK">
						                                                    OK
						                                                </option>
						                                                <option value="OR">
						                                                    OR
						                                                </option>
						                                                <option value="PA">
						                                                    PA
						                                                </option>
						                                                <option value="RI">
						                                                    RI
						                                                </option>
						                                                <option value="SC">
						                                                    SC
						                                                </option>
						                                                <option value="SD">
						                                                    SD
						                                                </option>
						                                                <option value="TN">
						                                                    TN
						                                                </option>
						                                                <option value="TX">
						                                                    TX
						                                                </option>
						                                                <option value="UT">
						                                                    UT
						                                                </option>
						                                                <option value="VT">
						                                                    VT
						                                                </option>
						                                                <option value="VA">
						                                                    VA
						                                                </option>
						                                                <option value="WA">
						                                                    WA
						                                                </option>
						                                                <option value="WV">
						                                                    WV
						                                                </option>
						                                                <option value="WI">
						                                                    WI
						                                                </option>
						                                                <option value="WY">
						                                                    WY
						                                                </option>
						                                            </select>
						                                        </fieldset>
						                                    </div>
						                                    <div class="gc pad-half-compact medium-6 large-3">
						                                        <input class="text static" name="zip_2rchFidNsuFnd46u" placeholder="" type="text">
						                                        </input>
						                                    </div>
						                                </div>
						                                <div class="gc" style="margin-top: -4px;">
						                                </div>
						                                <div class="gc input-group-help error">
						                                </div>
						                                <div class="gc input-group-help req-error">
						                                </div>
						                            </fieldset>
						                        </div>
						                    </div>
						                </div>
						            </div>
						            <script>
						                var pagecount=1;
						            </script>
						            <div class="gr flush">
						                <div class="gc g3 pad-double right" style="width:auto;">
						                    <button class="button-blue" id="nextPageButton" onclick="cPage(event,'next', pagecount)" style="float: right; display: none;" type="button">
						                        <?php echo $this->pl->trans($m,'Next'); ?>
						                    </button>
						                    <button class="button button-blue" id="submitButton" style="float: right; " type="button">
						                        <?php echo $this->pl->trans($m,'Submit'); ?>
						                    </button>
						                </div>
						                <div class="gc g3 pad-double left">
						                    <button class="button-blue" id="previousPageButton" onclick="cPage(event,'prev', pagecount)" style="float: left; display: none;" type="button">
						                        <?php echo $this->pl->trans($m,'Previous'); ?>
						                    </button>
						                </div>
						                <div class="gc g6 pad-double align-center left">
						                </div>
						            </div>
						    </div>
						</div>
					</div>
				</div>
			</div>
		</div>
  	</form>
  	<?php } ?>
  	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.25.0/codemirror.min.js"></script>
  	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.25.0/mode/css/css.min.js"></script>
  	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.25.0/addon/selection/active-line.min.js"></script>
  	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.25.0/addon/edit/matchbrackets.min.js"></script>
  	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.25.0/addon/edit/closebrackets.min.js"></script>
  	<script type="text/javascript" src="//cdnjs.cloudflare.com/ajax/libs/codemirror/5.25.0/addon/scroll/simplescrollbars.min.js"></script>
  	<script type="text/javascript">
	  var editor = CodeMirror.fromTextArea(document.getElementById("code"), {
	    //lineNumbers: true,
	    styleActiveLine: true,
	    matchBrackets: true,
	    autoCloseBrackets: true,
	    scrollbarStyle: "simple"
	  });

	  editor.setOption("theme", "mdn-like");
  	</script>
<?php
}

function OutputThemes() {
	$m = "themes";
	$themes = $this->lo->listThemes(array("uid"=>$this->uid, "type"=>"Basic"));
	$theme = null;
	if($this->urlpart[2]<>'new' && $this->urlpart[2]<>'' && $this->urlpart[3]=='edit') {
		$theme = $this->lo->listThemes(array("uid"=>$this->uid, "id"=>$this->urlpart[2], "type"=>"Basic"));
		if(count($theme)) {
			$theme = $theme[0];
		}
	}
	$this->InsideHeader();
?>
	<style>
	.formline {
		border: 0px;
	}
	.el {
		border: 0px;
	}
	#saveButton {
		position: fixed;
    	right: 0;
    	margin: 15px;
    	z-index: 10;
	}
	</style>
	<?php if($this->urlpart[2]=='') { ?>
	<div id="app" class="stage">
		<div class="scroll">
			<div class="flush centered">
  				<div class="gr centered small-11 large-12 xl-6 settings">
  					<div class="gr fc flush height-100">
  						<div class="gr centered form-list-dashboard">
  							<div class="flush">
  								<div class="gr g12">
  									<div class="right">
			  							<a href="/themes/new/"><button type="button" class="small red"><?php echo $this->pl->trans($m,'Create New Theme'); ?></button></a>
			  						</div>
  								</div>
		  						<h3>Themes</h3>
		  						<?php if(count($themes)) { ?>
		  						<table border="0" width="100%">
		  							<thead>
		  								<tr>
		  									<th align="left"><?php echo $this->pl->trans($m,'Name'); ?></th>
		  									<th align="left"><?php echo $this->pl->trans($m,'Action'); ?></th>
		  								</tr>
		  							</thead>
		  							<tbody>
		  								<?php foreach($themes as $theme) { ?>
		  								<tr>
		  									<td><?php echo $theme['name']; ?></td>
		  									<td>
		  										<a href="/themes/<?php echo $theme['_id']; ?>/edit/">Edit</a> |
		  										<a href="<?php echo $this->pl->set_csrfguard('/themes/'.$theme['_id'].'/delete/','deletetheme');?>" onclick="return confirm('Are you sure you want to delete that theme')"><?php echo $this->pl->trans($m,'Delete'); ?></a>
		  									</td>
		  								</tr>
		  								<?php } ?>
		  							</tbody>
		  						</table>
		  						<?php } else { ?>
		  							<?php echo $this->pl->trans($m,'No Theme added yet'); ?>.
		  						<?php } ?>
	  						</div>
	  					</div>
  					</div>
  				</div>
  			</div>
  		</div>
  	</div>
  	<?php } ?>
  	<?php if($this->urlpart[2]<>'' && ($this->urlpart[2]=='new' || $this->urlpart[3]=='edit')) { ?>
	<form action="" method="POST" id="editorForm">
		<div class="page">
			<div class="flex-container">
				<div class="flex-row">
					<div id="sidebar" class="sidebar">
						<header class="button-header">
							<div class="pad">
							<fieldset>
								<label><?php echo $this->pl->trans($m,'Theme Name'); ?></label>
								<input type="text" name="name" value="<?php echo $theme ? $theme['name']:'' ?>" />
							</fieldset>
							</div>
						</header>
						<div class="sidebar-position">
							<div class="sidebar-settings scroll">
								<div id="stheme" class="sel">
									<div class="pad">
							            <fieldset style="margin-top: 50px">
							                <label>
							                    Global Font
							                    <i class="icon-info">
							                        <div class="tooltip-wrapper">
							                            <div class="tooltip">
							                                <p>
							                                    <?php echo $this->pl->trans($m,'This selection changes the font'); ?>.
							                                </p>
							                            </div>
							                        </div>
							                    </i>
							                </label>
							                <div class="select">
							                    <select class="text" id="s_themeFont" prop="themeFont" name="themeFont">
							                        <option value="" <?php if($theme && $theme['themeFont']=='') {echo'selected';} ?>>
							                            Standard
							                        </option>
							                        <option value="Arvo" <?php if($theme && $theme['themeFont']=='Arvo') {echo'selected';} ?>>
							                            Arvo
							                        </option>
							                        <option value="Droid Sans" <?php if($theme && $theme['themeFont']=='Droid Sans') {echo'selected';} ?>>
							                            Droid Sans
							                        </option>
							                        <option value="Josefin Slab" <?php if($theme && $theme['themeFont']=='Josefin Slab') {echo'selected';} ?>>
							                            Josefin Slab
							                        </option>
							                        <option value="Lato" <?php if($theme && $theme['themeFont']=='Lato') {echo'selected';} ?>>
							                            Lato
							                        </option>
							                        <option value="Open Sans" <?php if($theme && $theme['themeFont']=='Open Sans') {echo'selected';} ?>>
							                            Open Sans
							                        </option>
							                        <option value="PT Sans" <?php if($theme && $theme['themeFont']=='PT Sans') {echo'selected';} ?>>
							                            PT Sans
							                        </option>
							                        <option value="Roboto" <?php if($theme && $theme['themeFont']=='Roboto') {echo'selected';} ?>>
							                            Roboto
							                        </option>
							                        <option value="Source Sans Pro" <?php if($theme && $theme['themeFont']=='Source Sans Pro') {echo'selected';} ?>>
							                            Source Sans Pro
							                        </option>
							                        <option value="Ubuntu" <?php if($theme && $theme['themeFont']=='Ubuntu') {echo'selected';} ?>>
							                            Ubuntu
							                        </option>
							                        <option value="Vollkorn" <?php if($theme && $theme['themeFont']=='Vollkorn') {echo'selected';} ?>>
							                            Vollkorn
							                        </option>
							                    </select>
							                </div>
							            </fieldset>
<?php

$colors=$GLOBALS['ref']['formcolors'];
 ?>
							            <fieldset>
							                <hr>
                              <?php
                              for($l=0;$l<count($colors);$l++){?>
							                    <fieldset class="backgrounds">
							                        <label><?php echo $this->pl->trans($m,$colors[$l]['label']); ?>
							                            <i class="icon-info"><div class="tooltip-wrapper"><div class="tooltip"><p><?php echo $this->pl->trans($m,$colors[$l]['tooltip']); ?>.</p></div></div></i>
							                        </label>
							                        <div class="controls-container">
							                            <input class="text small dark jscolor" data-colormode="HEX" data-default-value="<?php echo $colors[$l]['color'];?>" id="s_<?php echo $colors[$l]['prop'];?>" maxlength="7" prop="<?php echo $colors[$l]['prop'];?>" name="<?php echo $colors[$l]['prop'];?>" style="background-color:<?php $colors[$l]['color'];?>; color: <?php $colors[$l]['fontcolor'];?>;" value="<?php echo $theme ? $theme[$colors[$l]['prop']]:$colors[$l]['color']; ?>">
							                            </input>
							                        </div>
							                    </fieldset>
                                  <?php }

// TODO(fil) : add the other colors to the array
                                   ?>
							                    <fieldset class="backgrounds">
							                        <label>
							                            <?php echo $this->pl->trans($m,'Field Border'); ?>
							                            <i class="icon-info">
							                                <div class="tooltip-wrapper">
							                                    <div class="tooltip">
							                                        <p>
							                                            <?php echo $this->pl->trans($m,'The color of the field border when in natural state'); ?>
							                                        </p>
							                                    </div>
							                                </div>
							                            </i>
							                        </label>
							                        <div class="controls-container">
							                            <input class="text small dark jscolor" data-colormode="HEX" data-default-value="#D6D7D6" id="s_themeFieldBorder" maxlength="7" prop="themeFieldBorder" name="themeFieldBorder" style="background-color: rgb(66, 79, 66); color: rgb(221, 221, 221);" value="<?php echo $theme ? $theme['themeFieldBorder']:'#D6D7D6' ?>">
							                            </input>
							                        </div>
							                    </fieldset>
							                    <fieldset class="backgrounds">
							                        <label>
							                            <?php echo $this->pl->trans($m,'Hover Field Border'); ?>
							                            <i class="icon-info">
							                                <div class="tooltip-wrapper">
							                                    <div class="tooltip">
							                                        <p>
							                                            <?php echo $this->pl->trans($m,'This color changes the field border color when the field is hovered'); ?>.
							                                        </p>
							                                    </div>
							                                </div>
							                            </i>
							                        </label>
							                        <div class="controls-container">
							                            <input class="text small dark jscolor" data-colormode="HEX" data-default-value="#3E4943" id="s_themeFieldHover" maxlength="7" prop="themeFieldHover" name="themeFieldHover" style="background-color: rgb(62, 73, 67); color: rgb(221, 221, 221);" value="<?php echo $theme ? $theme['themeFieldHover']:'#3E4943' ?>">
							                            </input>
							                        </div>
							                    </fieldset>
							                    <fieldset class="backgrounds">
							                        <label>
							                            <?php echo $this->pl->trans($m,'Active Field Border'); ?>
							                            <i class="icon-info">
							                                <div class="tooltip-wrapper">
							                                    <div class="tooltip">
							                                        <p>
							                                            <?php echo $this->pl->trans($m,'This color changes the field border color when the field is active'); ?>.
							                                        </p>
							                                    </div>
							                                </div>
							                            </i>
							                        </label>
							                        <div class="controls-container">
							                            <input class="text small dark jscolor" data-colormode="HEX" data-default-value="#4BAEC2" id="s_themeFieldActive" maxlength="7" prop="themeFieldActive" name="themeFieldActive" style="background-color: rgb(75, 174, 194); color: rgb(34, 34, 34);" value="<?php echo $theme ? $theme['themeFieldActive']:'#4BAEC2' ?>">
							                            </input>
							                        </div>
							                    </fieldset>
							                    <fieldset class="backgrounds">
							                        <label>
							                            <?php echo $this->pl->trans($m,'Error Field Border'); ?>
							                            <i class="icon-info">
							                                <div class="tooltip-wrapper">
							                                    <div class="tooltip">
							                                        <p>
							                                            <?php echo $this->pl->trans($m,'This color changes the field border color and helper text when the the input is invalid or required'); ?>.
							                                        </p>
							                                    </div>
							                                </div>
							                            </i>
							                        </label>
							                        <div class="controls-container">
							                            <input class="text small dark jscolor" data-colormode="HEX" data-default-value="#D1603D" id="s_themeFieldError" maxlength="7" prop="themeFieldError" name="themeFieldError" style="background-color: rgb(209, 96, 61); color: rgb(34, 34, 34);" value="<?php echo $theme ? $theme['themeFieldError']:'#D1603D' ?>">
							                            </input>
							                        </div>
							                    </fieldset>
							                    <fieldset class="backgrounds">
							                        <label>
							                            <?php echo $this->pl->trans($m,'Selected Option'); ?>
							                            <i class="icon-info">
							                                <div class="tooltip-wrapper">
							                                    <div class="tooltip">
							                                        <p>
							                                            <?php echo $this->pl->trans($m,'This color changes the field border color when the field is selected'); ?>.
							                                        </p>
							                                    </div>
							                                </div>
							                            </i>
							                        </label>
							                        <div class="controls-container">
							                            <input class="text small dark jscolor" data-colormode="HEX" data-default-value="#4BAEC2" id="s_themeFieldSelected" maxlength="7" prop="themeFieldSelected" name="themeFieldSelected" style="background-color: rgb(75, 174, 194); color: rgb(34, 34, 34);" value="<?php echo $theme ? $theme['themeFieldSelected']:'#4BAEC2' ?>">
							                            </input>
							                        </div>
							                    </fieldset>
							                    <fieldset class="backgrounds">
							                        <label>
							                            <?php echo $this->pl->trans($m,'Button Background'); ?>
							                            <i class="icon-info">
							                                <div class="tooltip-wrapper">
							                                    <div class="tooltip">
							                                        <p>
							                                            <?php echo $this->pl->trans($m,'This color changes the button background color'); ?>.
							                                        </p>
							                                    </div>
							                                </div>
							                            </i>
							                        </label>
							                        <div class="controls-container">
							                            <input class="text small dark jscolor" data-colormode="HEX" data-default-value="#4BAEC2" id="s_themeSubmitButton" maxlength="7" prop="themeSubmitButton" name="themeSubmitButton" style="background-color: rgb(75, 174, 194); color: rgb(34, 34, 34);" value="<?php echo $theme ? $theme['themeSubmitButton']:'#4BAEC2' ?>">
							                            </input>
							                        </div>
							                    </fieldset>
							                    <fieldset class="backgrounds">
							                        <label>
							                            <?php echo $this->pl->trans($m,'Button Text'); ?>
							                            <i class="icon-info">
							                                <div class="tooltip-wrapper">
							                                    <div class="tooltip">
							                                        <p>
							                                            <?php echo $this->pl->trans($m,'This color changes the button text color'); ?>.
							                                        </p>
							                                    </div>
							                                </div>
							                            </i>
							                        </label>
							                        <div class="controls-container">
							                            <input class="text small dark jscolor" data-colormode="HEX" data-default-value="#FFFFFF" id="s_themeSubmitButtonText" maxlength="7" prop="themeSubmitButtonText" name="themeSubmitButtonText" style="background-color: rgb(255, 255, 255); color: rgb(34, 34, 34);" value="<?php echo $theme ? $theme['themeSubmitButtonText']:'#FFFFFF' ?>">
							                            </input>
							                        </div>
							                    </fieldset>
							                    <fieldset class="backgrounds">
							                        <label>
							                            <?php echo $this->pl->trans($m,'Form Label Text'); ?>
							                            <i class="icon-info">
							                                <div class="tooltip-wrapper">
							                                    <div class="tooltip">
							                                        <p>
							                                            <?php echo $this->pl->trans($m,'This color changes the field label text and title of the form'); ?>.
							                                        </p>
							                                    </div>
							                                </div>
							                            </i>
							                        </label>
							                        <div class="controls-container">
							                            <input class="text small dark jscolor" data-colormode="HEX" data-default-value="#3E4943" id="s_themeText" maxlength="7" prop="themeText" name="themeText" style="background-color: rgb(62, 73, 67); color: rgb(221, 221, 221);" value="<?php echo $theme ? $theme['themeText']:'#3E4943' ?>">
							                            </input>
							                        </div>
							                    </fieldset>
							                    <fieldset class="backgrounds">
							                        <label>
							                            <?php echo $this->pl->trans($m,'Form Field Text'); ?>
							                            <i class="icon-info">
							                                <div class="tooltip-wrapper">
							                                    <div class="tooltip">
							                                        <p>
							                                            <?php echo $this->pl->trans($m,'This color changes the field text and field icon colors'); ?>.
							                                        </p>
							                                    </div>
							                                </div>
							                            </i>
							                        </label>
							                        <div class="controls-container">
							                            <input class="text small dark jscolor" data-colormode="HEX" data-default-value="#3E4943" id="s_themeFieldText" maxlength="7" prop="themeFieldText" name="themeFieldText" style="background-color: rgb(62, 73, 67); color: rgb(221, 221, 221);" value="<?php echo $theme ? $theme['themeFieldText']:'#3E4943' ?>">
							                            </input>
							                        </div>
							                    </fieldset>
							                </hr>
							            </fieldset>
							        </hr>
							    </div>
								</div>
							</div>
						</div>
					</div>
					<div class="flex-col" style="height:93vh">
						<button type="submit" id="saveButton"><?php echo $this->pl->trans($m,'Save Theme'); ?></button>
						<div class="flush">
							<div class="viewport"></div>
							<div class="form fcc">
							    <h1 id="displayHeaderContainer">
							        <span class="editable" data-prop="name" data-trigger="s_name" id="name">
							            <?php echo $this->pl->trans($m,'Sample Form'); ?>
							        </span>
							        <span class="help editable" data-prop="description" data-trigger="s_description" id="description" placeholder="Click here to enter a description">
							            <?php echo $this->pl->trans($m,'this is a test description of the form'); ?>
							        </span>
							    </h1>
							    <div class="formElementContainer">
							        <div class="ellist" data-page="page1" id="formelements_0">
							            <div class="formline" id="line1">
							                <div class="el" id="fwm6Atch5Uhlbbc8S" validation-type="EMAIL" style="padding-right: 1px;">
							                    <div class="gc" et="text">
							                        <textarea disabled="" class="ed" placeholder="Field Label" prop="inputLabel" style="margin-left: 0px;"><?php echo $this->pl->trans($m,'Email'); ?></textarea>
							                    </div>
							                    <fieldset class="icon-left inline-edit-container">
							                        <input class="inline-edit input-text" prop="placeholderText" type="text">
							                            <i class="fa fa-envelope">
							                            </i>
							                        </input>
							                    </fieldset>
							                </div>
							                <div class="el" id="fdAQPX62WGKs6itxA">
							                    <div class="gc" et="text">
							                        <textarea disabled="" class="ed" placeholder="Field Label" prop="inputLabel" style="margin-left: 0px;"><?php echo $this->pl->trans($m,'Text input short'); ?></textarea>
							                    </div>
							                    <fieldset class="icon-left">
							                        <input prop="placeholderText" type="text"></input>
							                    </fieldset>
							                </div>
							            </div>
							            <div class="formline" id="line2">
							                <div class="el" id="fbwoxxEKIHDEAGF8u">
							                    <div class="gc" et="select">
							                        <textarea disabled="" class="ed" placeholder="Field Label" prop="inputLabel" style="margin-left: 0px;"><?php echo $this->pl->trans($m,'Select Dropdown'); ?></textarea>
							                        <fieldset class="select">
							                            <select class="text" prop="optionsList">
							                                <option disabled="" prop="placeholderText" selected="">
							                                </option>
							                            </select>
							                        </fieldset>
							                        <textarea disabled="" class="ed help" placeholder="Inline Instructions" prop="instructionText"><?php echo $this->pl->trans($m,'Example Inline Instruction'); ?></textarea>
							                    </div>
							                </div>
							            </div>
							            <div class="formline" id="line3">
							                <div class="el" id="fhbRJKwflUDgjmpC4">
							                    <div class="gc" et="radio">
							                        <textarea disabled="" class="ed" placeholder="Field Label" prop="inputLabel" style="margin-left: 0px;"><?php echo $this->pl->trans($m,'Single choice'); ?></textarea>
							                        <div class="option_container">
							                            <div class="foption">
							                                <fieldset class="option-container">
							                                    <label class="option">
							                                        <input type="checkbox">
							                                            <i>
							                                            </i>
							                                            <input disabled="" class="inline-edit input-text" type="text" value="Option 1"/>
							                                        </input>
							                                    </label>
							                                </fieldset>
							                            </div>
							                            <div class="foption">
							                                <fieldset class="option-container">
							                                    <label class="option">
							                                        <input type="checkbox">
							                                            <i>
							                                            </i>
							                                            <input disabled="" class="inline-edit input-text" type="text" value="Option 2"/>
							                                        </input>
							                                    </label>
							                                </fieldset>
							                            </div>
							                            <div class="foption">
							                                <fieldset class="option-container">
							                                    <label class="option">
							                                        <input type="checkbox">
							                                            <i>
							                                            </i>
							                                            <input disabled="" class="inline-edit input-text" type="text" value="Option 3"/>
							                                        </input>
							                                    </label>
							                                </fieldset>
							                            </div>
							                        </div>
							                        <textarea class="ed help" placeholder="<?php echo $this->pl->trans($m,'Inline Instructions'); ?>" prop="instructionText">
							                        </textarea>
							                    </div>
							                </div>
							                <div class="el" id="fAntpnKmpVWzjHN6k">
							                    <div class="gc" et="checkbox">
							                        <textarea disabled="" class="ed" placeholder="<?php echo $this->pl->trans($m,'Field Label'); ?>" prop="inputLabel" style="margin-left: 0px;"><?php echo $this->pl->trans($m,'Multiple choice'); ?></textarea>
							                        <div class="option_container">
							                            <div class="foption">
							                                <fieldset class="option-container">
							                                    <label class="option">
							                                        <input type="checkbox">
							                                            <i>
							                                            </i>
							                                            <input disabled="" class="inline-edit input-text" type="text" value="Option 1"/>
							                                        </input>
							                                    </label>
							                                </fieldset>
							                            </div>
							                            <div class="foption">
							                                <fieldset class="option-container">
							                                    <label class="option">
							                                        <input type="checkbox">
							                                            <i>
							                                            </i>
							                                            <input disabled="" class="inline-edit input-text" type="text" value="Option 2"/>
							                                        </input>
							                                    </label>
							                                </fieldset>
							                            </div>
							                            <div class="foption">
							                                <fieldset class="option-container">
							                                    <label class="option">
							                                        <input type="checkbox">
							                                            <i>
							                                            </i>
							                                            <input disabled="" class="inline-edit input-text" type="text" value="Option 3"/>
							                                        </input>
							                                    </label>
							                                </fieldset>
							                            </div>
							                        </div>
							                    </div>
							                </div>
							            </div>
							            <div class="formline" id="line4">
							                <div class="el" id="f2rchFidNsuFnd46u">
							                    <div class="flush">
							                        <div class="gr" et="usaddress">
							                            <textarea disabled="" class="ed" placeholder="<?php echo $this->pl->trans($m,'Field Label'); ?>" prop="inputLabel" style="margin-left: 0px;"><?php echo $this->pl->trans($m,'Address'); ?></textarea>
							                        </div>
							                        <div class="gr pad-half-all" style="padding-top: 0; padding-bottom: 0;">
							                            <div class="gr pad-half-compact g12">
							                                <fieldset>
							                                    <input class="text static" prop="placeholderAddress1Text" type="text" value="Address 1"/>
							                                </fieldset>
							                            </div>
							                            <div class="gr pad-half-compact g12">
							                                <fieldset>
							                                    <input class="text static" prop="placeholderAddress2Text" type="text" value="Address 2"/>
							                                </fieldset>
							                            </div>
							                            <div class="gr pad-half-compact g5">
							                                <fieldset>
							                                    <input class="text static" prop="placeholderCityText" type="text" value="City"/>
							                                </fieldset>
							                            </div>
							                            <div class="gr pad-half-compact g1">
							                            </div>
							                            <div class="gr pad-half-compact g3 state_text" style="display:none;">
							                                <fieldset style="margin-right:15px">
							                                    <input class="text static" prop="placeholderStateText" type="text" value="State"/>
							                                </fieldset>
							                            </div>
							                            <div class="gr pad-half-compact g3 state_select">
							                                <fieldset class="select" style="padding:8px;box-shadow: 0 0 0 1px #d9d9d9;">
							                                    <input class="text static" prop="placeholderStateText" type="text" value="State"/>
							                                </fieldset>
							                            </div>
							                            <div class="gr pad-half-compact g3">
							                                <fieldset>
							                                    <input class="text static" prop="placeholderZipText" type="text" value="Zip Code"/>
							                                </fieldset>
							                            </div>
							                            <div class="gr pad-half-compact g12 countrySelect" id="countrySelect" style="display:none;">
							                                <fieldset>
							                                    <input class="text static" prop="placeholderCountryText" type="text" value="Country"/>
							                                </fieldset>
							                            </div>
							                        </div>
							                        <div class="gr" style="margin-top: -4px;margin-left:0px">
							                            <textarea class="ed help" placeholder="<?php echo $this->pl->trans($m,'Inline Instructions'); ?>" prop="instructionText">
							                            </textarea>
							                        </div>
							                    </div>
							                </div>
							            </div>
							            <div class="footer">
							                <div class="gr flush" style="margin:0">
							                    <div class="gc g3 pad-double right">
							                        <span class="button button-blue nextButtonText editable" data-prop="nextButtonText" data-trigger="s_nextButtonText" style="float: right;" type="button">
							                            <?php echo $this->pl->trans($m,'Next'); ?>
							                        </span>
							                    </div>
							                    <div class="gc g3 pad-double left" style="float:left">
							                        <span class="button button-blue previousButtonText editable" data-prop="previousButtonText" data-trigger="s_previousButtonText" style="float: left; display: none;" type="button">
							                            <?php echo $this->pl->trans($m,'Previous'); ?>
							                        </span>
							                    </div>
							                    <div class="gc g6 pad-double align-center left" style="width:100%;">
							                        <span class="pagination" style="display:inline-block; margin-top:8px;">
							                            <?php echo $this->pl->trans($m,'Page 1 of 2'); ?>
							                        </span>
							                    </div>
							                </div>
							            </div>
							        </div>
							        <div class="ellist" data-page="gIMKVsomCmpSTlku" id="formelements_1">
							            <div class="formline" id="line6" placeholder="<?php echo $this->pl->trans($m,'Drag boxes here to add them to the form'); ?>">
							                <div class="el" data-default-value="Title display" did="pre_section" draggable="false" id="f8kNx2jk9OR37zEQk">
							                    <div>
							                        <div class="gc" et="section">
							                            <textarea disabled="" class="ed h2 inline-edit" placeholder="<?php echo $this->pl->trans($m,'Section Title'); ?>" prop="labelText"><?php echo $this->pl->trans($m,'Test Page 2'); ?></textarea>
							                        </div>
							                        <hr/>
							                    </div>
							                </div>
							            </div>
							            <div class="footer">
							                <div class="gr flush" style="margin:0">
							                    <div class="gc g3 pad-double right">
							                        <span class="button button-blue editable submitButtonText" data-prop="submitButtonText" data-trigger="s_submitButtonText" style="float: right;" type="button">
							                            <?php echo $this->pl->trans($m,'Submit'); ?>
							                        </span>
							                    </div>
							                    <div class="gc g3 pad-double left" style="float:left">
							                        <span class="button button-blue previousButtonText editable" data-prop="previousButtonText" data-trigger="s_previousButtonText" style="float: left;" type="button">
							                            <?php echo $this->pl->trans($m,'Previous'); ?>
							                        </span>
							                    </div>
							                    <div class="gc g6 pad-double align-center left" style="width:100%;">
							                        <span class="pagination" style="display:inline-block; margin-top:8px;">
							                            <?php echo $this->pl->trans($m,'Page 2 of 2'); ?>
							                        </span>
							                    </div>
							                </div>
							            </div>
							        </div>
							    </div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</form>
	<?php } ?>
<?php
?>
	<script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/zepto.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
	<script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/theme.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
	<script type="text/javascript">
		var colors = '';
		var selected_input = '';
		$(document).ready(function(){
			setTimeout(function(){
				$("input.jscolor").on("focus", function(){
					selected_input = '';
					selected_input = $(this);
					setTimeout(function(){
						$('.cp-app').detach().appendTo(selected_input.closest('.controls-container'));
					}, 10);
				});
				var theme = $("#stheme");
				$.each(theme.find("fieldset.backgrounds"), function(){
					var input_id = $(this).find('input').attr('id');
					var container = $(this).find('.controls-container');
					var default_value = $(this).find('input').data('default-value');
					var color = jsColorPicker('input#' + input_id, {
						customBG: default_value,
						readOnly: false,
						//patch: false,
						margin: {left: -1, top: -1},
						init: function(elm, colors){ // colors is a different instance (not connected to colorPicker)
							if(elm.value==''){
								elm.style.backgroundColor = default_value;
								elm.value = default_value;
							} else {
								elm.style.backgroundColor = elm.value;
							}
							elm.style.color = colors.rgbaMixCustom.luminance > 0.22 ? '#222' : '#ddd';
						},
						renderCallback: function(elm, mode){
							if(selected_input){
								selected_input.val('#'+elm.HEX);
								selected_input.css('backgroundColor', '#'+elm.HEX);

								selected_input.trigger( "change" );
							}

						},
						appendTo: container.get(0)
					});
				});
			}, 500);
		});

	</script>
<?php
	$this->InsideFooter();
}

function OutputNewaccount() {
    $m = "newaccount";
    $this->InsideHeader();
?>
<div id="app" class="stage">
    <div class="scroll">
        <div class="flush centered">
            <div class="gr centered small-11 large-12 xl-6 settings">
                <div class="gr fc flush height-100">
                    <div class="gr centered form-list-dashboard">
                        <form action="" method="POST">
                            <label><?php echo $this->pl->trans($m,'Account Name'); ?>*</label>
                            <input type="text" name="accountname" /><br /><br />
                            <label class="u-pull-left margin-right-15 option">
                                <input type="checkbox" style="display:none" class="" name="switch" value="1" checked />
                                <i></i>
                                <?php echo $this->pl->trans($m,'Switch to this account'); ?>
                            </label>
                            <br /><br />
                            <button><?php echo $this->pl->trans($m,'Create'); ?></button>
                        </form>
                    </div>
                </div>
            </div>
            <?php $this->OutputMarketingFooter(); ?>
        </div>
    </div>
</div>
<?php
}

function OutputEditor(){
	$m = "editor";
  if($this->urlpart[2]){
      $this->InsideHeader();

      $class='';
      if($this->pl->isPreviewUser($this->lAccount) || $this->lUser['emailVerified']<>1) {
          $class.='has_permanent_notification';
      }
?>
<style>
h1 {
    display: block;
    font-size: 2em;
    margin: 0;
    font-weight: bold;
}
h3 {
    display: block;
    font-size: 1.17em;
    margin: 0;
    font-weight: bold;
}
label {
    color: #3e4943;
    font-weight: 600;
    font-size: 0.95rem;
    line-height: 1.4em;
    letter-spacing: .0125em;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    display: block;
    margin-bottom: 4px;
}

input[type="text"], input[type="email"], input[type="number"], input[type="password"], select {
    background: white;
    color: #333;
    font-family: 'Source Sans Pro', 'Helvetica Neue', Helvetica, Arial, sans-serif;
    font-weight: 400;
    font-size: .9rem;
    line-height: 1.7em;
    letter-spacing: .0225em;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
    vertical-align: bottom;
    border: 0;
    box-shadow: 0 0 0 1px #d9d9d9;
    border-radius: 4px;
    background-clip: padding-box;
    border-color: #cececf;
    border-top-color: #b5b5b6;
    border-bottom-color: #dededf;
    padding: .5rem .7rem;
    -webkit-transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
    transition: border-color ease-in-out .15s, box-shadow ease-in-out .15s;
}

input[type="text"]:hover, input[type="email"]:hover, input[type="number"]:hover, input[type="password"]:hover, select:hover {
    box-shadow: 0 0 0 1px #333;
}

input[type="text"]:focus, input[type="email"]:focus, input[type="number"]:focus, input[type="password"]:focus, select:focus {
    box-shadow: 0 0 0 1px #4baec2;
    outline: none;
}
</style>
<script type="text/javascript">
<?php $this->OutputScriptComponents(); ?>
</script>
<script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/zepto.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
<script>
	var max_upload_size = <?php echo $GLOBALS['ref']['MAX_UPLOAD_SIZE']; ?>;
	<?php
    $format = $this->pl->getUserDateFormat($this->lUser);
    ?>

	var user_date_format = '<?php echo $format; ?>';
	$(document).ready(function(){
        $(".alert-container").find('span.close').on("click", function(){
     		$(".alert-container").removeClass("error");
            $(".alert-container").removeClass("success");
            $(".alert-container").html("");
            $(".alert-container").hide();
     	});
	});
</script>
<script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/datepicker/strtotime.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
<script type="text/javascript" src="<?php echo $GLOBALS['level'];?>static/js/app.js?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>"></script>
<div class="page editorPage <?php echo $class; ?>">
    <div class="flex-container">
        <div class="flex-row" style="width:100%;">
            <div id="sidebar" class="sidebar" style="<?php if(!$this->lUser['email']) { echo 'height:96%;'; } ?>">
                <header class="button-header">
                </header>
                <div class="sidebar-position"></div>
            </div>
            <div class="flex-col" style="<?php if(!$this->lUser['email']) { echo 'height:89vh;'; } else { echo 'height:93vh;'; } ?>flex:initial;width:calc(100% - 300px);">
                <div class="alert-container alert-fixed" style="display:none;"></div>
                <?php if($this->isAdmin) { ?>
            		<div class="form-template">
            			<?php if($this->lo->isFormTemplate(array('formid'=>$this->urlpart[2]))) { ?>
            				<a href="/saveorremovetemplate/<?php echo $this->urlpart[2]; ?>/" class="button active"><i class="fas fa-star"></i> <?php echo $this->pl->trans($m,'Remove as Template'); ?></a>
            			<?php } else { ?>
            				<a href="/saveorremovetemplate/<?php echo $this->urlpart[2]; ?>/" class="button"><i class="far fa-star"></i> <?php echo $this->pl->trans($m,'Save as Template'); ?></a>
            			<?php } ?>

            		</div>
            	<?php } ?>
                  <div class="flush">
                    <div class="viewport"></div>
                        <div class="form fcc">
                            <div class="form-header-container">
                                <div class="form-menu" style="font-family: 'Source Sans Pro' !important;color: #000 !important;height:37px; background: #ffffff;">
                               		<span class="right" style="position: relative;width: 300px;">
                               			<a href="<?php echo $GLOBALS['level'];?>response/<?php echo $this->urlpart[2];?>/"><?php echo $this->pl->trans($m,'Responses'); ?></a> -
                               			<a onclick="return confirm('Are you sure to permanently delete this form + all stored data ?');" href="<?php echo $this->pl->set_csrfguard($GLOBALS['level'].'form/delete/'.$this->urlpart[2].'/','deleteform');?>"><?php echo $this->pl->trans($m,'Delete'); ?></a> -
                               			<a class="" href="<?php echo $GLOBALS['level'];?>forms/<?php echo $this->urlpart[2];?>/" target="_blank"><?php echo $this->pl->trans($m,'Preview'); ?></a>
                               			<div id="formStateContainer" class="form-state-container"></div>
                               		</span>
                               	</div>
                               	<?php
                               	if($this->form['type'] <> 'ENDPOINT' || $this->form["error"]) {
                               	?>
                                <span id="delivery"><button class="button small" style="font-family: 'Source Sans Pro' !important;"><i class="fa fa-paper-plane"></i> <?php echo $this->pl->trans($m,'Delivery'); ?></button></span>
                                <span id="deliveryActive" style="display:none"><button class="button small active" style="font-family: 'Source Sans Pro' !important;"><i class="fa fa-paper-plane"></i> <?php echo $this->pl->trans($m,'Delivery'); ?></button></span>
                                <div id="deliveryContainer" style="display: none;font-family: 'Source Sans Pro' !important;color:#3E4943 !important">
                                	<div>
    	                            	<h1><i class="fa fa-link"></i> <?php echo $this->pl->trans($m,'Deliver by Link'); ?></h1>
    	                            	<p><?php echo $this->pl->trans($m,'Share this link with people to capture responses'); ?>:</p><br />
    	                            	<div class="code">
                                            <span class="copy_clipboard">Copy to clipboard</span>
    	                            		<code><?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $this->urlpart[2]; ?>/</code>
    	                            	</div>
                                	</div>
                                	<div style="position: relative;">
    	                            	<h1><i class="fa fa-code"></i> <?php echo $this->pl->trans($m,'Deliver by IFrame'); ?></h1>
    	                            	<p><?php echo $this->pl->trans($m,'Include this code on your own website'); ?>:</p><br />
    	                            	<div class="code">
                                            <span class="copy_clipboard">Copy to clipboard</span>
    <code>&lt;script type="text/javascript" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js/iframeResizer.min.js"&gt;&lt;/script&gt;
&lt;iframe class="formlets-iframe" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $this->urlpart[2]; ?>/?iframe=true" frameborder="0" width="100%"&gt;&lt;/iframe&gt;
&lt;script type="text/javascript" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js/iframe.js"&gt;&lt;/script&gt;</code>
    	                            	</div>
    	                            	<a target="_blank" href="/demo/<?php echo $this->urlpart[2]; ?>/iframe/" style="position: absolute;top:52px;right:0"><?php echo $this->pl->trans($m,'Check Demo'); ?></a>
                                	</div>
                                	<div style="position: relative;">
    	                            	<h1><i class="fa fa-arrows-alt"></i> <?php echo $this->pl->trans($m,'Deliver by Modal'); ?></h1>
    	                            	<p><?php echo $this->pl->trans($m,'Include this code on your own website'); ?>:</p><br />
    	                            	<div class="code">
                                            <span class="copy_clipboard">Copy to clipboard</span>
    <code>&lt;script type="text/javascript" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js/iframeResizer.min.js"&gt;&lt;/script&gt;
&lt;a target="_blank" onclick="FormletOpen(&#39;<?php echo $this->urlpart[2]; ?>&#39;);" id="<?php echo $this->urlpart[2]; ?>" href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/forms/<?php echo $this->urlpart[2]; ?>/?iframe=true"&gt;INSERT TEXT HERE&lt;/a&gt;
&lt;script type="text/javascript" src="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/static/js/modal.js"&gt;&lt;/script&gt;
&lt;script type="text/javascript"&gt;Formlet("<?php echo $this->urlpart[2]; ?>");&lt;/script&gt;</code>
    	                            	</div>
    	                            	<a target="_blank" href="/demo/<?php echo $this->urlpart[2]; ?>/modal/" style="position: absolute;top:52px;right:0"><?php echo $this->pl->trans($m,'Check Demo'); ?></a>
                                	</div>
                                	<button class="close"><i class="fa fa-times"></i> Close</button>
                                </div>
                            </div>

                            <div class="fc-logo-add"><a href="javascript:;" class="button small round toggle"><i class="fa fa-plus"></i> ADD YOUR LOGO</a></div>
                            <?php
	                        }
	                        ?>
                            <div class="fc-logo"></div>

                            <div class="passwordContainer" style="margin-top:30px;display:none;">
                                <div class="ellist pword">
                                    <div class="passwordField" style="margin-top:3px;">
                                        <div id="esettings" class="el selected">
                                            <div class="gc" et="text" type="TEXT">
                                                <textarea class="ed" prop="passwordLabel" placeholder="Field Label" style="margin:0">Access to this form is restricted.</textarea>
                                            </div>
                                            <fieldset class="icon-left" style="padding:0px;"><input prop="placeholderText" type="password" class="inline-edit input-text" style="opacity: 1 !important;box-shadow: none;"></fieldset>
                                        </div>
                                    </div>
                                    <div class="footer">
                                        <div class="gr flush" style="margin:0">
                                            <div class="gc g3 pad-double right"> <span contenteditable="" class="button button-blue editable passwordButtonLabel" type="button" style="float: right;" data-trigger="s_passwordButtonLabel" data-prop="passwordButtonLabel">Submit</span> </div>
                                            <div class="gc g3 pad-double left" style="float:left"></div>
                                            <div class="gc g6 pad-double align-center left" style="width:100%;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div id="displayHeaderContainer">
                                <h1>
                                	<span id="name" contenteditable="" class="editable" data-trigger="s_name" data-prop="name"></span>
                                	<span id="description" contenteditable="" class="help editable" data-trigger="s_description" data-prop="description" placeholder="<?php echo $this->pl->trans($m,'Click here to enter a description'); ?>"></span>
                                </h1>
                            </div>

                            <div class="formElementContainer">
                          	</div>
                            <div class="new_form_page">
                				<a href="javascript:;" class="new_page button small round"><i class="fa fa-plus"></i> Add new Page</a>
                				<div class="submit_confirmation_info">Success page</div>
                				<div class="formElementContainerSuccess">
                	            	<div id="formelements_success" class="submit_confirmation ellist" data-page="success">
                						<div class="formline" id="sp_line1">
                							<div class="el">
                			            		<div id="sc_redirects"><div>Redirects to:</div></div>
                			            		<div id="fconfirmation" class="el"></div>
                							</div>
                						</div>
                					</div>
                				</div>
                            </div>
                        </div>
                </div>
            </div>
              <div id="snew" style="display: none;">
                <div class="sidebar"></div>
                <div class="flex-col">
                	<?php
                  	$value = 'Form 1';
                  	if($this->urlpart[3] == 'source' && $this->urlpart[4]) {
                  		$form = $this->lo->getForm(array('form_id'=>$this->urlpart[4]));
                  		if(!$form['error']) {
                  			$value = htmlentities(stripslashes($form['name'])) . ' template';
                  		} else {
                  			$value = 'Untitled';
                  		}
                  	}
                  	?>
                  <div class="viewport" style="width: 100%;margin: auto; margin-top:30px; <?php echo isset($form) ? 'top:30%':'top:15%'; ?>">
                      <div class="g12">
                      	<div id="browsetheme">
                      		<h1><?php echo $this->pl->trans($m,'Create a new Form'); ?></h1><br />
                      		<?php echo $this->pl->trans($m,'Option 1: Choose a template to start from'); ?> <br /><br /><a href="/formtemplates/" style="font-size: 20px">Browse here</a><br /><br />
                      		<?php echo $this->pl->trans($m,'Option 2 : Start from a blank canvas'); ?><br /><br />
                      	</div>
                      	<h3><?php echo $this->pl->trans($m,'What is the name of the form?'); ?></h3>
                      	<input id="formname" name="formname" value="<?php echo $value; ?>" type="text" autofocus="">
                      </div>
                      <div class="gr pad" style="margin-bottom: 15px">
                      	<button id="createnewform" class="btn btn-sm btn-success" style="margin-left: 5px;margin-right: 25px;"><?php echo $this->pl->trans($m,'Create Form'); ?></button>
                      </div>
                      <?php if(!$this->urlpart[4] && $GLOBALS['ref']['plan_lists'][$this->account['index']]['endpoint'] == true) { ?>
                      <div class="g12">
                      		<?php echo $this->pl->trans($m,'Option 3: Make your own HTML and use our endpoint (For developers)'); ?><br /><br />
                      		<h3><?php echo $this->pl->trans($m,'What is the name of the form?'); ?></h3>
                      		<input id="endpointname" name="endpoint" value="<?php echo $value; ?>" type="text">
                      </div>
                      <div class="gr pad" style="margin-bottom: 15px">
                      		<button id="createnewendpoint" class="btn btn-sm btn-success" style="margin-left: 5px"><?php echo $this->pl->trans($m,'Create Endpoint'); ?></button>
                      </div>
                      <?php } ?>
                  </div>
                </div>
              </div>
        </div>
    </div>
</div>
<script type="text/javascript">
	var colors = '';
	var selected_input = '';
	var loginEmail = '<?php echo $this->lUser['email']; ?>';
	$(document).ready(function(){
		setTimeout(function(){
			$("input.jscolor").on("focus", function(){
				selected_input = '';
				selected_input = $(this);
				setTimeout(function(){
					$('.cp-app').detach().appendTo(selected_input.closest('.controls-container'));
				}, 10);
				if((gd.fd.themeEnabled == '' || gd.fd.themeEnabled == '0')){
					Ui.alert('error', 'Please turn on Custom form Theme first, before doing some changes!');
				}
			});
			var theme = $("#stheme");
			$.each(theme.find("fieldset.backgrounds"), function(){
				var input_id = $(this).find('input').attr('id');
				var container = $(this).find('.controls-container');
				var default_value = $(this).find('input').data('default-value');
				var color = jsColorPicker('input#' + input_id, {
					customBG: default_value,
					readOnly: false,
					//patch: false,
					margin: {left: -1, top: -1},
					init: function(elm, colors){ // colors is a different instance (not connected to colorPicker)
						if(elm.value==''){
							elm.style.backgroundColor = default_value;
							elm.value = default_value;
						} else {
							elm.style.backgroundColor = elm.value;
						}
						elm.style.color = colors.rgbaMixCustom.luminance > 0.22 ? '#222' : '#ddd';
					},
					renderCallback: function(elm, mode){
						if(selected_input){
							selected_input.val('#'+elm.HEX);
							selected_input.css('backgroundColor', '#'+elm.HEX);

							selected_input.trigger( "change" );
						}

					},
					appendTo: container.get(0)
				});
			});
		}, 500);
	});

    $('body').on('mouseenter mouseleave','.dropdown',function(e){
      var _d=$(e.target).closest('.dropdown');
      var _dm=_d.find('.dropdown-menu');
      _d.addClass('show');
      _dm.addClass('show');
      setTimeout(function(){
        _d[_d.is(':hover')?'addClass':'removeClass']('show');
        _dm[_d.is(':hover')?'addClass':'removeClass']('show');
      },300);
    });

</script>

<?php
  } else {
    header("location: ../form/");
    exit;
  }
}

function OutputImg(){

$img=str_replace('..','',$this->urlpart[2]);
$parts=explode('.',$img);
$ex=$parts[count($parts)-1];

switch($ex){
    case "gif": $ctype="image/gif"; break;
    case "png": $ctype="image/png"; break;
    case "jpeg":
    case "jpg": $ctype="image/jpeg"; break;
    default:
}

header('Content-type: ' . $ctype);
if($ctype){
  echo file_get_contents($GLOBALS['conf']['filepath'].'/'.$img);
} else {
  header("HTTP/1.0 404 Not Found");
}
exit;
}

function OutputSupport(){
	$m = "support";

if ($this->urlpart[2]){
  // show details
      $faq_data = $this->lo->getFaqDetail(array('url' => $this->urlpart[2],'lang'=>$GLOBALS['lang']));
      if($faq_data){
        $this->OutputMarketingHeader(array("title"=>$faq_data['title'],"descr"=>""));
?>

<section id="Content-Container">
      <div class="support">
        <div class="container">
        <div class="faq-info">
            <a href="../"><?php echo $this->pl->trans($m,'Back to Topic list'); ?></a>
                <br/>
                <p>
                     <div class="description">
                       <?php  if (!empty($faq_data['intro'])){?>
                            <p><?php echo htmlspecialchars_decode(nl2br($faq_data['intro'])); ?></p><br/>
                       <?php } ?>
                        <?php  if (!empty($faq_data['img1'])){?>
                           <p><img src="<?php echo $GLOBALS['level']."img/".$faq_data['img1']; ?>" class="img-fluid"></p>
                       <?php } ?>
                        <?php  if (!empty($faq_data['body1'])){?>
                            <p><?php echo htmlspecialchars_decode(nl2br($faq_data['body1'])); ?></p><br/>
                       <?php } ?>
                        <?php  if (!empty($faq_data['img2'])){?>
                            <p><img src="<?php echo $GLOBALS['level']."img/".$faq_data['img2']; ?>" class="img-fluid"></p>
                       <?php } ?>
                        <?php  if (!empty($faq_data['body2'])){?>
                            <p><?php echo htmlspecialchars_decode(nl2br($faq_data['body2'])); ?></p>
                       <?php } ?>
                    </div>
                </p>
            </div>
     </div>
    </div>
 </section>
 <?php
      } else {

        header("location: /support/");
        exit;
      }

} else {
  // show index
      $faqs = $this->lo->getFaqList(array('lang'=>$GLOBALS['lang']));
      $this->OutputMarketingHeader(array("title"=>"Support","descr"=>""));
?>

<section id="Content-Container">
      <div class="support">
        <div class="container">
          <div class="row sub-innerH">
            <h4>
              Support contact for free accounts: <br>
              Email: <a href="mailto:hello@formlets.com">hello[@]formlets.com</a> <br>
              Online form: <a href="https://www.formlets.com/forms/571d42690acd41d175f57137/">Our customer feedback form</a>
            </h4>
          </div>
          <div class="row">
              <?php
                  for ($f=0;$f<count($faqs);$f++){
                      if($oldcatname<>$faqs[$f]['catname']){
                          if($f<>0){?>
                              </ul></div>
                          <?php
                          }
                          ?>
                            <div class="col-12">
                                <h4><?php echo $faqs[$f]['catname'];?></h4>
                                <ul>
              <?php
                      }
              ?>
                                <li><a href="<?php echo $GLOBALS['level'];?>support/<?php  echo $faqs[$f]['url'];?>/"><?php echo $faqs[$f]['title'];?></a></li>
                        <?php
                        $oldcatname=$faqs[$f]['catname'];
                        }

              ?>
              </ul></div>
          </div>

        </div>
      </div>
    </section>
 <?php

 }
$this->OutputMarketingFooter2();
}

function OutputFeatures(){
	$m = "features";
  // show index

      $features = $this->lo->getFeatures();

      if ($this->urlpart[2]){
            $features_data = $this->lo->getFeatures(array('url' => $this->urlpart[2]));
                $this->OutputMarketingHeader(array("title"=>$features_data['title'],"descr"=>""));
          } else {
      $this->OutputMarketingHeader(array("title"=>"Feature list","descr"=>""));
    }
?>
<link href="<?php echo $GLOBALS['level'];?>static/css/wysiwyg/quill.snow.css?<?php echo $_SERVER["CURRENT_VERSION_ID"];?>" rel="stylesheet">
<style>
.faq-info {
    margin-bottom:30px;
}
.faq-info p {
    margin:0px;
}
.faq-info a {
    font-size:15px;
    color: #000;
}
.feature-title {
    font-size:20px;
}
.feature-list {
    background: #fac832;
    padding: 10px;
}
</style>

<section id="Content-Container">
      <div class="features">
        <div class="container">
          <div class="row">
            <div class="col-lg-4">
              <ul class="side-nav">
                <li class="sidenav-title">Features</li>
                <?php
                for ($f=0;$f<count($features);$f++){
                    if($features[$f]['url'] == $this->urlpart[2]) {
                        echo '<li>'.$features[$f]['title'].'</li>';
                    } else {
                ?>
                     <li><a href="<?php echo $GLOBALS['level'];?>features/<?php  echo $features[$f]['url'];?>/"><?php echo $features[$f]['title'];?></a></li>
                <?php
                    }
                }
                ?>
              </ul>
            </div>
            <div class="col-lg-8">
                <div>
                    <?php
                    if ($this->urlpart[2]){
                    ?>
                        <div class="faq-info ql-editor" style="padding:0px">
                            <?php echo $features_data['body']; ?>
                        </div>
                        <?php if (!empty($features_data['img1'])){?>
                           <p><img src="<?php echo $GLOBALS['level']."img/".$features_data['img1']; ?>" class="img-fluid"></p>
                       <?php } ?>
                       <?php  if (!empty($features_data['body2'])){?>
                       <div class="faq-info ql-editor" style="padding:0px">
                           <?php echo $features_data['body2']; ?>
                       </div>
                       <?php } ?>
                        <?php  if (!empty($features_data['img2'])){?>
                            <p><img src="<?php echo $GLOBALS['level']."img/".$features_data['img2']; ?>" class="img-fluid"></p>
                       <?php } ?>
                       <div class="cta-container">
                           <a href="/try/" class="btn btn1 btn-lg">Start now with a free form</a>
                           <span>No need to signup to try</span>
                       </div>
                    <?php
                    } else {
                        echo '<div class="text-center">Please select a feature from the left column</div>';
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>
</div>
</section>
 <?php
    $this->OutputMarketingFooter2();
}

function emailPassword($user){
	$m = "emailpassword";
	ob_start();
	$this->outputStripeMailHeader();
?>
	<table class="body-wrap">
		<tr>
			<td></td>
			<td class="container white-background">

				<!-- content -->
				<div class="content">
				<table>
					<tr>
						<td align="center" class="padding">
							<img src="<?php echo $GLOBALS['protocol'];?>://<?php echo $_SERVER['HTTP_HOST'];?>/static/img/logo-light.png" alt="Formlets" data-inline-ignore>
						</td>
					</tr>
					<tr>
						<td align="center">
							<h1><?php echo $this->pl->trans($m,'Hi'); ?>,</h1>
							<p><?php echo $this->pl->trans($m,'We received a request for a new password for your account. Please ignore it if you did not initiate this request.'); ?></p>
							<p><?php echo $this->pl->trans($m,'Please open the following URL to begin the password reset process:'); ?>' <?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/newpassword/<?php echo $user['id']; ?>/<?php echo $user['password_token']; ?>/</p>
							<p><?php echo $this->pl->trans($m,'The password set token is only active for a limited amount of time'); ?></p>
							<p><?php echo $this->pl->trans($m,'If you have any questions or concerns, please email us at'); ?> hello@formlets.com.</p>

							</br>
							<span class="lightGray"><?php echo $this->pl->trans($m,'By clicking the above link, you agree to our'); ?> <a href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/terms/"><?php echo $this->pl->trans($m,'Terms of Service'); ?></a> and <a href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/privacy/"><?php echo $this->pl->trans($m,'Privacy Policy'); ?></a>.</span>
						</td>
					</tr>
				</table>
				</div>
				<!-- /content -->
			</td>
			<td></td>
		</tr>
	</table>
<?php
	$this->outputStripeMailFooter();
  	return ob_get_clean();
}

//
function emailSignup($user, $type='new'){
	$m = "emailsignup";
	ob_start();
	$this->outputStripeMailHeader();
?>
	<table class="body-wrap">
		<tr>
			<td></td>
			<td class="container white-background">

				<!-- content -->
				<div class="content">
				<table>
					<tr>
						<td align="center" class="padding">
							<img src="<?php echo $GLOBALS['protocol'];?>://<?php echo $_SERVER['HTTP_HOST'];?>/static/img/logo-light.png" alt="Formlets" data-inline-ignore>
						</td>
					</tr>
					<tr>
						<td align="center">
							<h1><?php echo $this->pl->trans($m,'Hi'); ?>,</h1>
							<?php if($type=='new') {
								echo '<p>'.$this->pl->trans($m,'We received a request for the creation of a new account').'</p>';
							} else if($type=='change_email') {
								echo '<p>'.$this->pl->trans($m,'We received a request for the new email').'</p>';
							} else if($type=='resend_validation') {
								echo '<p>'.$this->pl->trans($m,'We received a request for new email validation').'</p>';
							} ?>

							<p><?php echo $this->pl->trans($m,'Please open the following URL to begin the account activation process'); ?>: <?php echo "<a href=\"".$GLOBALS['protocol']."://".$_SERVER['HTTP_HOST']."/checkemail/".$user['id']."/".$user['password_token']."/\">".$GLOBALS['protocol']."://".$_SERVER['HTTP_HOST']."/checkemail/".$user['id']."/".$user['password_token']."/</a>"; ?><br></p>
							<p><?php echo $this->pl->trans($m,'The link above is only active for a limited amount of time'); ?></p>
							<p><?php echo $this->pl->trans($m,'If you have any questions or concerns, please email us at'); ?> hello@formlets.com.</p>

							</br>
							<span class="lightGray"><?php echo $this->pl->trans($m,'By clicking the above link, you agree to our'); ?> <a href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/terms/"><?php echo $this->pl->trans($m,'Terms of Service'); ?></a> <?php echo $this->pl->trans($m,'and'); ?> <a href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/privacy/"><?php echo $this->pl->trans($m,'Privacy Policy'); ?></a>.</span>
						</td>
					</tr>
				</table>
				</div>
				<!-- /content -->
			</td>
			<td></td>
		</tr>
	</table>
<?php
	$this->outputStripeMailFooter();
  	return ob_get_clean();
}

function emailMember($user, $owner, $account, $userExists = false){
	ob_start();
	$this->outputStripeMailHeader();
?>
	<table class="body-wrap">
		<tr>
			<td></td>
			<td class="container white-background">
				<!-- content -->
				<div class="content">
				<table>
					<tr>
						<td align="center" class="padding">
							<img src="<?php echo $GLOBALS['protocol'];?>://<?php echo $_SERVER['HTTP_HOST'];?>/static/img/logo-light.png" alt="Formlets" data-inline-ignore>
						</td>
					</tr>
					<tr>
						<td align="center">
							<h1><?php echo $owner['companyName']; ?></h1>
							<p>You have been invited to join Team <?php echo $owner['companyName']; ?> on Formlets.</p>
							<p>Please confirm your email address by clicking below.</p>
							<!-- <p>Your temporary password is: <b><?php echo $user['id']; ?></b></p> -->

                            <?php
                            if($userExists) {
                            ?>
                                <p align="center"><?php echo "<a class=\"btn-primary\" href=\"".$GLOBALS['protocol']."://".$_SERVER['HTTP_HOST']."/acceptinvite/".$account['_id']."/".$user['_id']."/\">Join ".$owner['companyName']."'s Team</a>"; ?></p>
                            <?php
                            } else {
                            ?>
                                <p align="center"><?php echo "<a class=\"btn-primary\" href=\"".$GLOBALS['protocol']."://".$_SERVER['HTTP_HOST']."/newpassword/".$user['id']."/".$user['password_token']."/\">Join ".$owner['companyName']."'s Team</a>"; ?></p>
                            <?php
                            }
                            ?>

							</br>
							<span class="lightGray">By clicking the above link, you agree to our <a href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/terms/">Terms of Service</a> and <a href="<?php echo $GLOBALS['protocol']; ?>://<?php echo $_SERVER['HTTP_HOST']; ?>/privacy/">Privacy Policy</a>.</span>
						</td>
					</tr>
				</table>
				</div>
				<!-- /content -->
			</td>
			<td></td>
		</tr>
	</table>
<?php
  	$this->outputStripeMailFooter();
  	return ob_get_clean();
}
//

//
function Outputlogin(){
	$m = "login";
 $this->OutputMarketingHeader(array("title"=>"Login","descr"=>""));
?>

<section id="Content-Container">
  <div class="signup-login">
    <div class="container">
      <div class="row justify-content-md-center">
        <div class="col-md-5">
          <form action="<?php echo $this->pl->Xssenc($_SERVER['REQUEST_URI']);?>" method="post">
              <?php if(isset($this->errorMessage)){?>
                  <div class="" class="error">
                          <span class="help"><?php echo $this->errorMessage;?></span>
                  </div>
              <?php } ?>
            <div class="form-group">
              <label for="Email"><?php echo $this->pl->trans($m,'Email'); ?></label>
              <input type="email" id="Email" name="username" class="form-control" placeholder="<?php echo $this->pl->trans($m,'Email'); ?>">
            </div>
            <div class="form-group">
              <label for="Password">Password</label>
              <input type="password" id="Password" name="password" class="form-control" placeholder="Password">
              <a id="ForgotPassword" class="form-text" href="/password/"><?php echo $this->pl->trans($m,'Forgot your password'); ?>?</a>
            </div>
            <button type="Submit" class="btn btn1"><?php echo $this->pl->trans($m,'Login'); ?></button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <br><br><br><br><br>
</section>
<?php
  $this->OutputMarketingFooter2();
}
//


//
function Outputcheckemail(){
	$m = "checkemail";
    $this->iface='checkemail';
    $this->OutputHeader();
?>
<h1><?php echo $this->pl->trans($m,'Check email'); ?></h1>
<div class="small-column round">
  <?php if($this->okMessage){ echo $this->okMessage; } else { echo $this->pl->trans($m,'Thank you for your registration, please check your email (and spam folder) for your activation link');
  } ?>
</div>
  </div>
<?php
$this->OutputFooter();
}
//

//
function OutputNewpassword(){
	$m = "newpassword";

    $this->OutputHeader(array("title"=>"Set new Password | Formlets","descr"=>""));
    if(in_array($_GET['red'],$this->interfaces)) {
        $red="?red=".$_GET['red'];
    }
?>
    <h1><?php echo $this->pl->trans($m,'New Password'); ?></h1>
    <form accept-charset="UTF-8" action="<?php echo $this->pl->Xssenc($_SERVER['REQUEST_URI']);?>" class="fc" class="gr pad" method="POST">
        <?php
            if($this->errorMessage) { ?>
    			<div class="" class="error">
    				<span class="help"><?php echo $this->errorMessage;?></span>
    			</div>
        <?php
            }
            if($this->okMessage) {
        ?>
                <div>
                    <?php echo $this->okMessage;?>
                </div>
        <?php
            } else {
        ?>
                <div class="gc small-12 pad-half">
                    <label><?php echo $this->pl->trans($m,'New Password'); ?></label>
                    <div class="controls-container" style="width: 100%">
                        <input class="text " id="pwd" onkeyup="checkForm(this.form)" onchange="checkForm(this.form)" onblur="checkForm(this.form)" placeholder="<?php echo $this->pl->trans($m,'This will be used to login to Formlets'); ?>" validate-password validate-required type="password" name="password" value="">
                    </div>
                </div>
                <div class="gc small-12 pad-half">
                    <ul id="errors"></ul>
                </div>
    			<div class="gc small-12 pad-half">
                    <label for="user_password_confirmation"><?php echo $this->pl->trans($m,'New Password confirmation'); ?></label><br>
                    <input class="text" id="user_password_confirmation" name="password_confirmation" required="required" type="password" />
			    </div>
    			<div class="gc small-12 pad-half">
                    <input class="button button-blue disabled" id="submitButton" name="commit" type="submit" value="<?php echo $this->pl->trans($m,'Set new password'); ?>" />
                </div>
        <?php
            }
        ?>
    </form>
<?php
$this->_PasswordValidationScript();
$this->OutputFooter();
}
//


//
function OutputPassword(){
	$m = "password";
 $this->OutputMarketingHeader(array("title"=>"Password reset","descr"=>""));
        if(in_array($_GET['red'],$this->interfaces)){
    $red="?red=".$_GET['red'];
    }
?>

<section id="Content-Container">
  <div class="signup-login">
    <div class="container">
      <div class="row justify-content-md-center">
        <div class="col-md-5">
            <form accept-charset="UTF-8" action="<?php echo $this->pl->Xssenc($_SERVER['REQUEST_URI']);?>" method="POST" class="fc" class="gr pad">
                <?php if($this->errorMessage){?>
                    <div class="" class="error">
                        <span class="help"><?php echo $this->errorMessage;?></span>
                    </div>
                <?php } else if($this->okMessage){?>
                    <div>
                        <?php echo $this->okMessage;?>
                    </div>
                <?php } else { ?>
                    <div class="form-group">
                      <label for="Email"><?php echo $this->pl->trans($m,'Email'); ?></label>
                      <input type="email" id="Email" name="email" class="form-control" placeholder="<?php echo $this->pl->trans($m,'Email'); ?>">
                    </div>

                    <button type="Submit" class="btn btn1"><?php echo $this->pl->trans($m,'Send me reset password instructions'); ?></button>

                    <div class="subtext top-20">
                    <?php echo $this->pl->trans($m,'Already registered'); ?>? <a href="/login/<?php echo $red;?>"><?php echo $this->pl->trans($m,'Login'); ?></a><br />
                    <a href="/signup/<?php echo $red;?>"><?php echo $this->pl->trans($m,'Sign up'); ?></a><br />
                    </div>
                <?php } ?>
          </form>
        </div>
      </div>
    </div>
  </div>
  <br><br><br><br><br>
</section>
<?php
$this->OutputMarketingFooter2();
}
//

/// start of form components to render the outside forms
private function _fieldSubmissionData() {

}



//
function outputApi(){
	if($this->urlpart[2]=='forms'){
		$forms = $this->forms;
		foreach($forms as $key=>$form){
			$forms[$key]['id'] = $form['_id'];
			$forms[$key]['construction'] = [];
			$forms[$key]['views'] = $form['views'] ?: 0;
		}
		echo json_encode($forms, true);
	}
}
//

function outputSecretfileuploadtest() {

?>
<form action="" enctype="multipart/form-data" method="post">
    <input type="file" name="test" />
    <button>Submit</button>
</form>
<?php

}

}
?>
