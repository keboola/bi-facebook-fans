if (!$.browser.msie || ($.browser.msie && parseInt($.browser.version, 10)>8)) {
(function(a){function p(a){a=parseInt(a).toString(16);return a.length<2?"0"+a:a}function x(g){for(;g;){var b=a.css(g,"backgroundColor");if(b&&b!="transparent"&&b!="rgba(0, 0, 0, 0)")return b.indexOf("rgb")>=0?(g=b.match(/\d+/g),"#"+p(g[0])+p(g[1])+p(g[2])):b;if(g.nodeName.toLowerCase()=="html")break;g=g.parentNode}return"#ffffff"}function y(a,b,c){switch(a){case "round":return Math.round(c*(1-Math.cos(Math.asin(b/c))));case "cool":return Math.round(c*(1+Math.cos(Math.asin(b/c))));case "sharp":return c-
    b;case "bite":return Math.round(c*Math.cos(Math.asin((c-b-1)/c)));case "slide":return Math.round(c*Math.atan2(b,c/b));case "jut":return Math.round(c*Math.atan2(c,c-b-1));case "curl":return Math.round(c*Math.atan(b));case "tear":return Math.round(c*Math.cos(b));case "wicked":return Math.round(c*Math.tan(b));case "long":return Math.round(c*Math.sqrt(b));case "sculpt":return Math.round(c*Math.log(c-b-1,c));case "dogfold":case "dog":return b&1?b+1:c;case "dog2":return b&2?b+1:c;case "dog3":return b&3?
    b+1:c;case "fray":return b%2*c;case "notch":return c;case "bevelfold":case "bevel":return b+1;case "steep":return b/2+1;case "invsteep":return(c-b)/2+1}}var i=document.createElement("div").style,m=i.MozBorderRadius!==void 0,s=i.WebkitBorderRadius!==void 0,n=i.borderRadius!==void 0||i.BorderRadius!==void 0,i=document.documentMode||0,z=a.browser.msie&&(a.browser.version<8&&!i||i<8),u=a.browser.msie&&function(){var a=document.createElement("div");try{a.style.setExpression("width","0+0"),a.style.removeExpression("width")}catch(b){return false}return true}();
    a.support=a.support||{};a.support.borderRadius=m||s||n;a.fn.corner=function(g){if(this.length==0){if(!a.isReady&&this.selector){var b=this.selector,c=this.context;a(function(){a(b,c).corner(g)})}return this}return this.each(function(){var b,c,i,p,j=a(this),e=[j.attr(a.fn.corner.defaults.metaAttr)||"",g||""].join(" ").toLowerCase(),t=/keep/.test(e),h=(e.match(/cc:(#[0-9a-f]+)/)||[])[1];b=(e.match(/sc:(#[0-9a-f]+)/)||[])[1];var f=parseInt((e.match(/(\d+)px/)||[])[1])||10,v=(e.match(/round|bevelfold|bevel|notch|bite|cool|sharp|slide|jut|curl|tear|fray|wicked|sculpt|long|dog3|dog2|dogfold|dog|invsteep|steep/)||
        ["round"])[0],A=/dogfold|bevelfold/.test(e),w={T:0,B:1},e={TL:/top|tl|left/.test(e),TR:/top|tr|right/.test(e),BL:/bottom|bl|left/.test(e),BR:/bottom|br|right/.test(e)},q,l,d,k,r,o;!e.TL&&!e.TR&&!e.BL&&!e.BR&&(e={TL:1,TR:1,BL:1,BR:1});if(a.fn.corner.defaults.useNative&&v=="round"&&(n||m||s)&&!h&&!b)e.TL&&j.css(n?"border-top-left-radius":m?"-moz-border-radius-topleft":"-webkit-border-top-left-radius",f+"px"),e.TR&&j.css(n?"border-top-right-radius":m?"-moz-border-radius-topright":"-webkit-border-top-right-radius",
        f+"px"),e.BL&&j.css(n?"border-bottom-left-radius":m?"-moz-border-radius-bottomleft":"-webkit-border-bottom-left-radius",f+"px"),e.BR&&j.css(n?"border-bottom-right-radius":m?"-moz-border-radius-bottomright":"-webkit-border-bottom-right-radius",f+"px");else{j=document.createElement("div");a(j).css({overflow:"hidden",height:"1px",minHeight:"1px",fontSize:"1px",backgroundColor:b||"transparent",borderStyle:"solid"});b=parseInt(a.css(this,"paddingTop"))||0;c=parseInt(a.css(this,"paddingRight"))||0;i=parseInt(a.css(this,
        "paddingBottom"))||0;p=parseInt(a.css(this,"paddingLeft"))||0;if(typeof this.style.zoom!=void 0)this.style.zoom=1;if(!t)this.style.border="none";j.style.borderColor=h||x(this.parentNode);t=a(this).outerHeight();for(q in w)if((h=w[q])&&(e.BL||e.BR)||!h&&(e.TL||e.TR)){j.style.borderStyle="none "+(e[q+"R"]?"solid":"none")+" none "+(e[q+"L"]?"solid":"none");l=document.createElement("div");a(l).addClass("jquery-corner");d=l.style;h?this.appendChild(l):this.insertBefore(l,this.firstChild);if(h&&t!="auto"){if(a.css(this,
        "position")=="static")this.style.position="relative";d.position="absolute";d.bottom=d.left=d.padding=d.margin="0";u?d.setExpression("width","this.parentNode.offsetWidth"):d.width="100%"}else if(!h&&a.browser.msie){if(a.css(this,"position")=="static")this.style.position="relative";d.position="absolute";d.top=d.left=d.right=d.padding=d.margin="0";u?(k=(parseInt(a.css(this,"borderLeftWidth"))||0)+(parseInt(a.css(this,"borderRightWidth"))||0),d.setExpression("width","this.parentNode.offsetWidth - "+k+
        '+ "px"')):d.width="100%"}else d.position="relative",d.margin=!h?"-"+b+"px -"+c+"px "+(b-f)+"px -"+p+"px":i-f+"px -"+c+"px -"+i+"px -"+p+"px";for(d=0;d<f;d++)k=Math.max(0,y(v,d,f)),r=j.cloneNode(false),r.style.borderWidth="0 "+(e[q+"R"]?k:0)+"px 0 "+(e[q+"L"]?k:0)+"px",h?l.appendChild(r):l.insertBefore(r,l.firstChild);if(A&&a.support.boxModel&&(!h||!z))for(o in e)if(e[o]&&(!h||!(o=="TL"||o=="TR")))if(h||!(o=="BL"||o=="BR")){d={position:"absolute",border:"none",margin:0,padding:0,overflow:"hidden",
        backgroundColor:j.style.borderColor};k=a("<div/>").css(d).css({width:f+"px",height:"1px"});switch(o){case "TL":k.css({bottom:0,left:0});break;case "TR":k.css({bottom:0,right:0});break;case "BL":k.css({top:0,left:0});break;case "BR":k.css({top:0,right:0})}l.appendChild(k[0]);d=a("<div/>").css(d).css({top:0,bottom:0,width:"1px",height:f+"px"});switch(o){case "TL":d.css({left:f});break;case "TR":d.css({right:f});break;case "BL":d.css({left:f});break;case "BR":d.css({right:f})}l.appendChild(d[0])}}}})};
    a.fn.uncorner=function(){if(n||m||s)this.css(n?"border-radius":m?"-moz-border-radius":"-webkit-border-radius",0);a("div.jquery-corner",this).remove();return this};a.fn.corner.defaults={useNative:true,metaAttr:"data-corner"}})(jQuery);
} else {
    (function($){$.fn.corner = function(args) {};})(jQuery);
}