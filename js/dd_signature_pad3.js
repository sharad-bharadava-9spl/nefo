/**
* HTML5 Signature Pad Software
* Copyright (c) 2010 - 2013 Dream Designs (www.dreamdesignsweb.com)
* Under GPL (ww.dreamdesignsweb.com/license) licenses.
* All scripts & codes in this file was created by Dream Designs @ www.dreamdesignsweb.com
* ----------------------------------------------------
* Last Mod: Sep 11 2013 | version 1.4
*/

/**
* ------------------------------------
* Customizable Settings
* ***Read the 'how_to_install.pdf' file for more detailed info
* ------------------------------------
*/
// http abs path (i.e. http://mydomain.com)
// Path to images
var baseURL = "";
var dd_imgPath = "images/dd_signature_pad";
// Display alert on object load if there's 'toDataURL' support. false = show onclick of submit
var dd_noDataURLOnLoad = false;
// Save button ID; if it doesn't already exist, it will be created
var dd_objSaveButton = "savesig";
// Save button name
var dd_objSaveButtonName = "";
// Signature output to image file name
var dd_outputFileName = "";
// Data processor location file + path
var dd_dataProcessURL = "dd_signature_process3.php";
// Do an action 'AFTER' saving signature (exporting to image)
var dd_doPostAction = {
	dataSaved : function(f){
	
		// Add your code here. The 'f' var is the filename passed back
		
		// This line is for testing purposes and can be removed
		// along with the function it calls
		demo_postSaveAction(f);
	}
};
var dd_callbacks = {
	
	submit : function(t) {
	
		// Add your code here; i.e. function, statements, etc
	}
	
};

var w = window.innerWidth * 0.65
var h = window.innerHeight * 0.4
// Canvas element options
var dd_baseCanvas = {
	'format' : 'png',				// Final output format for converting to *DO NOT CHANGE AS IT CURRENTLY ONLY SUPPORTS PNG
	'width' : w,					// Canvas html width (not css width)
	'height' : h,					// Canvas html height (not css width)
	'border' : 'solid 2px #a80082',	// CSS notation; i.e. 'solid 1px #c0c0c0'
	'bg' : '#ffffff',				// CSS notation
	'linewidth' : 1,				// Drawing line thickness (caution: in webkit browser, if setting this to 1, set shadowblur to 0)
	'color' : '#000000',			// Drawing line color
	'shadowcolor' : '#000000',		// Drawing line shadow color
	'shadowblur' : 0,				// Drawing line shadow shadow blur (FF browsers)
	'shadowblur_wk' : 0,			// Drawing line shadow shadow blur (Webkit)
	'shadowblur_ie' : 0				// Drawing line shadow shadow blur (IE)
};
// Various jscript alert notices
var dd_alert = {
	// If no html5 support
	'nohtml5' : 'Your device does not yet support some important features of HTML5.',
	// If html5 suppport but no export to image support (toDataURL)
	'nodataurl' : 'Your device does not support exporting signatures to image.',
	// If submitting without signature
	'nosig' : 'Please add your signature first',
	// If signature could not be saved
	'savefail' : 'I could not save your signature. Please try again.'
}
// Enable or Disable tools
var dd_allowTools = {
	'picker' : false,
	'pencil' : false,
	'undo' : false,
	'delete' : true
};
// Position of pencil picker display;
var dd_pencilPicker_show = "left";
// Position of color picker display;
var dd_colorPicker_show = "left";
// Color pallete colors
var dd_colors = new Array(
	'000000', '993300','333300', '000080', '333399', '333333', '800000', 'FF6600', 
	'808000', '008000', '008080', '0000FF', '666699', '808080', 'FF0000', 'FF9900', 
	'99CC00', '339966', '33CCCC', '3366FF', '800080', '999999', 'FF00FF', 'FFCC00', 
	'FFFF00', '00FF00', '00FFFF', '00CCFF', '993366', 'C0C0C0', 'FF99CC', 'FFCC99', 
	'FFFF99' , 'CCFFFF', '99CCFF', 'FFFFFF'
);
// Default cursor position (draw position) for Google Chrome
var dd_webkitCursorOffset = "20 25";
// 'Draw' the signature area drawing to an existing element on page
var dd_print2Element = '';
/**
* ------------------------------------
* End of Customizable Settings
* ------------------------------------
*/

/**
* Defined Variables (do not modify these)
*/
var dd_toDataURLExist = false;
var dd_signatureStarted = false;
var dd_drawnLines = new Array();
var dd_drawnPoint = 0;
/**
* Let's load in all our functions, methods, vars and more
*/
eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('5 5y=8(){5 1B=r.2C(\'3U\');3(!1B){1h(\'6o!!\\62 3I 4Q 2z 6M 6I: \\\'3U\\\'\');F}5 D=r.G(\'f\');q(D){9(\'C\',\'6C\');9(\'T\',l[\'T\']);9(\'1c\',l[\'1c\']);q(d){E="2O-1A";1b=l[\'1b\'];2b=l[\'5C\']}}8 31(o){5 I=o.35("27/P");F{\'38\':(!o.1C?6:m),\'2F\':(I.O("I:27/P")!=-1?m:6)}};5 22=31(D);3(22[\'38\']==6){1h(1G[\'6E\']);F}g 3(22[\'2F\']==m){3z=m}g 3(22[\'2F\']==6){3(64==m){1h(1G[\'3O\']);F}}5 S=r.G(\'1j\');q(S){9(\'C\',\'4Z\');q(d){1L="2s";3(!11[\'46\']&&!11[\'2q\']&&!11[\'23\']&&!11[\'2S\']){E="W"}g{E="2O-1A"}T="4i";2y="18";5n="18";2g="B"}}5 2K=r.G(\'Q\');q(2K){9(\'C\',\'45\');9(\'1f\',1K+1m+\'/6U.P\');9(\'1e\',\'43 6H 6q\');k(\'1a\',8(u){2V(b,u)},6);3(!11[\'2q\']){d.E="W"}}5 16=r.G(\'1j\');16.9(\'C\',\'71\');q(16.d){1L="3f";E="W";T="6R";1c="1P";2g="B";2y="18 32 18 18";V="3d";2b="#24";1b="1t 1l #3g";3j="3A";3H="1X 1X 3Q #3Y";4e="4g"}5 2u={\'2L\':\'59.P\',\'4f\':\'6D.P\',\'6K\':\'4s.P\'};1N(5 p 47 2u){5 Z=r.G(\'Q\');Z.9(\'1f\',1K+1m+\'/\'+2u[p]);Z.9(\'1e\',p);q(Z.d){1b="1t 1l #24"}Z.k(\'2Q\',8(u){b.d.1Z="44";b.d.1b="1t 1l #6f"},6);Z.k(\'1R\',8(u){b.d.1Z="1P";b.d.1b="1t 1l #24"},6);Z.k(\'1a\',8(u){1S(b,\'4r\',D)},6);16.H(Z)}5 2B=r.G(\'Q\');q(2B){9(\'C\',\'42\');9(\'1f\',1K+1m+\'/5N.P\');9(\'1e\',\'43 41 6z\');k(\'1a\',8(u){3Z(b,u)},6);3(!11[\'46\']){d.E="W"}}1M.k(\'1a\',8(u){3S(b,u)},6);5 1d=r.G(\'1j\');1d.9(\'C\',\'4G\');q(1d.d){1L="3f";E="W";T="4Y";1c="1P";2g="B";2y="18 32 18 18";V="3d";2b="#24";1b="1t 1l #3g";3j="3A";3H="1X 1X 3Q #3Y";4e="4g"}1N(5 p=0;p<1U.R;p++){5 2c=r.G(\'1j\');q(2c){9(\'C\',\'61\'+1U[p]);9(\'V\',1U[p]);q(d){1L="2s";E="2O-1A";T="3N";1c="3N";6r="1l";1b="1t 1l #6w";2b="#"+1U[p]}k(\'2Q\',8(u){b.d.1Z="44";b.d.3M="0.7"},6);k(\'1R\',8(u){b.d.3M="1"},6);k(\'1a\',8(u){1S(b,\'V\',D)},6)}1d.H(2c)}5 2h=r.G(\'Q\');q(2h){9(\'C\',\'6Z\');9(\'1f\',1K+1m+\'/4h.P\');9(\'1e\',\'4k 41 4l\');k(\'1a\',8(u){2i(b,D)},6);3(!11[\'2S\']){d.E="W"}}5 2j=r.G(\'Q\');q(2j){9(\'C\',\'4u\');9(\'1f\',1K+1m+\'/4w.P\');9(\'1e\',\'4x 4y 4D\');k(\'1a\',8(u){3K(b)},6);3(!11[\'23\']){d.E="W"}}S.H(2K);S.H(16);S.H(2B);S.H(1d);S.H(2j);S.H(2h);1B.H(D);1B.H(S);5 1n=r.2C(3J);3(!1n){5 1Y=r.G(\'1j\');q(1Y){9(\'C\',\'5l\');q(d){1L="2s";5m="5A"}}1n=r.G(\'1j\');q(1n){9(\'C\',3J);1J=5K}1Y.H(1n);1B.H(1Y)}1n.k(\'1a\',8(u){3G(b,u);5Q.5W(b)},6);1I=(U.12.O(\'6j\')!=-1?m:6);6n=(U.12.O(\'6p\')!=-1?m:6);21=(U.12.O(\'3x\')!=-1?m:6);3n=(6A(U.6B.3l(\'3x\')[1])==10?m:6);6F=(U.12.O(\'6G\')!=-1?m:6);6J=(U.12.O(\'2w 6L\')!=-1?m:6);6N=(U.12.O(\'2w 6O\')!=-1?m:6);6P=(U.12.O(\'2w 6Q\')!=-1?m:6);3(1I){D.k(\'2Q\',8(u){3(U.12.O(\'6S/29.0.\')!=-1){3k="7 25"}D.d.1Z="1v(\'"+1m+"/2q.4j\') "+3k+", 1P";D.3h=8(e){F 6};D.4n=8(e){F 6}})}8 2i(t,o){5 f=o.1C(\'2d\');f.4t(0,0,o.T,o.1c);1u=6;3(t!="1D"){j=14 2H();L=0}};8 3K(t){3(j.R==0){1u=6;F}2S j[j.R-1];L--;L=(L<0?0:L);j.4T((j.R-1),1);1S(\'23\',\'V\',D)};8 2V(t,e){5 M=t.28;5 J=t.2a;3(16){q(16.d){2N(5r){1g"B":B=(M-33)+"v";N=(J+10)+"v";15;1g"N-B":B=(M-33)+"v";N=(J-20)+"v";15;1g"N-2Y":B=(M+10)+"v";N=(J+20)+"v";15;2T:B=(M+10)+"v";N=(J+10)+"v"}E="1A"}}};8 3Z(t,e){5 M=t.28;5 J=t.2a;3(1d){q(1d.d){2N(5Z){1g"B":B=(M-3B)+"v";N=(J+10)+"v";15;1g"N-B":B=(M-3B)+"v";2W=(J+10)+"v";15;1g"N-2Y":B=(M+10)+"v";2W=(J+10)+"v";15;2T:B=(M+10)+"v";N=(J+10)+"v"}E="1A"}}};8 3S(t,e){5 2U=(e.2X?e.2X.C:e.6l.C);3(2U!="42"){1d.d.E="W"}3(2U!="45"){16.d.E="W"}};8 1S(t,a,o){2N(a){1g"V":5 f=o.1C(\'2d\');2i(\'1D\',o);3(t!="23"){f.2Z="#"+t.2R(\'V\');f.30="#"+t.2R(\'V\')}f.2P();1N(5 i=0;i<j.R;i++){f.2P();1N(5 z=0;z<j[i][\'x\'].R;z++){3(z==0){f.34(j[i][\'x\'][z],j[i][\'y\'][z])}g 3(j[i][\'x\'][z]!=""&&j[i][\'y\'][z]!=""){f.36(j[i][\'x\'][z],j[i][\'y\'][z]);f.37()}}}3(j.R==0){1u=6}g{1u=m}15;2T:h=o.1C(\'2d\');3(t.1e=="2L"){h.26=2}g 3(t.1e=="4f"){h.26=l[\'39\']}g{h.26=4}3(t.1e=="2L"){h.1i=(1I?0:1)}g{3(21){h.1i=l[\'3a\']}g 3(1I){h.1i=l[\'3b\']}g{h.1i=l[\'3c\']}}}};5 2G=1y;8 3e(t,1v,1w,c,2A){1r{A.3i()}1H(1F){}5 A=1y;3(1M.2x){A=14 2x()}g 3(1M.2v){1r{A=14 2v("4m.3m")}1H(1F){1r{A=14 2v("4o.3m")}1H(1F){A="4p 4q 3I 72 3o 3p, 3q 4v 3r 3s 3t 4z 3r 3o 3p 4A a 4B 4C 3u 4E."}}}g{A="4F 3v 4H 4I 4J 2z 2x 3t 3s. 4K 4L b 4M, 3q 4N a 3v q 4O 4P 2z 3u."}3(13(t)=="4R"){2G=1M.4S((8(){1r{A.3i()}1H(1F){}3(13(c)!=1y&&13(c)!=3w&&13(c)!=""){3(2A){c.4U(1y,2A)}g{4V(c)}}}),t)}3(1v!=""&&1v!=3w&&1v!=1y){A.4W(\'4X\',1v,m);A.2t("3y-50","51/x-52-53-54");A.2t("3y-55",1w.R);A.2t("56","57");A.58(1w);3(13(A)!="2r"){A="5a 5b a 5c 5d 5e b 5f.\\n\\5g 5h 5i 1r 5j."}}F A};8 3G(t,e){3(!3z){1h(1G[\'3O\']);F 6}3(!1u){1h(1G[\'5k\']);F 6}5 1O=6;3(t.1q.1p()!="Q"&&t.1q.1p()!="5o"&&t.1q.1p()!="5p"&&t.1q.1p()!="5q"){t.9(\'3C\',t.1J);t.1J="<Q 1f=\\""+1m+"/5s.5t\\" d=\\"5u-5v:5w;\\" /> 5x...";1O=m}5 3D=5z+"."+l[\'1D\'];5 3E=l[\'T\'];5 3F=l[\'1c\'];5 I=D.35(\'27/\'+l[\'1D\']);5 2p=I;I=I.5D(\'I:27/\'+l[\'1D\']+\';5E,\',\'\');5 1w="5F=5G&5H="+5I(3D)+"&T="+3E+"&1c="+3F+"&I="+I+"&5J="+(2o.5L()*5M);5 1k=3e(5O,5P,1w,2m,[t,1O]);3(13(1k)!="2r"){1h(1k);F 6}1k.5R=8(){3(1k.5S==4&&1k.5T==5U){1r{1M.5V(2G)}1H(1F){};5 2k=1k.5X;3(2k.O(\'5Y|\')!=-1){5 3L=2k.3l(\'|\');3(1O){t.1J="60!"}3(1z!=\'\'){5 e,17;3(13(1z)==\'63\'){17=r.2C(1z)}g 3(13(1z)==\'2r\'){17=1z}3(17){3(17.1q.1p()==\'Q\'){17.1f=2p}g{5 2f=[\'1j\',\'65\',\'p\',\'66\',\'67\',\'68\',\'69\',\'6a\',\'6b\',\'a\',\'6c\',\'s\',\'6d\',\'6e\'];1N(e=0;e<2f.R;e++){3(17.1q.1p()==2f[e]){5 1W=r.G(\'Q\');1W.9(\'C\',\'6g\');1W.9(\'1f\',2p);17.H(1W);15}}}}}6h.6i(3L[1])}g{2m(t)}}}};8 2m(t,w){3(w){t.1J=t.2R(\'3C\')}1h(1G[\'6k\'])};5 1V=(\'6m\'47 r.3P?m:6);5 h,K,1o,1s;5 3R=8(f){3(1V){1s=0;1o=0;o=f;6s{1o+=o.28;1s+=o.2a}6t(o=o.6u)}g{1s=f.2a;1o=f.28}h=f.1C(\'2d\');h.2Z=l[\'V\'];h.26=l[\'39\'];h.6v="1T";h.6x="1T";h.30=l[\'6y\'];3(21){h.1i=l[\'3a\']}g 3(1I){h.1i=l[\'3b\']}g{h.1i=l[\'3c\']}3(1V){f.k(\'3T\',19,6);f.k(\'3V\',19,6);f.k(\'3W\',19,6)}g{f.k(\'3X\',19,6);f.k(\'2I\',19,6);f.k(\'2E\',19,6);f.k(\'1R\',19,6)}K=14 40();8 40(){5 K=b;b.1E=6;2D=8(e){h.2P();h.34(e.X,e.Y);3(!1Q(e.X)&&!1Q(e.Y)){j.1x({\'x\':14 2H(),\'y\':14 2H()});j[L][\'x\'].1x(e.X);j[L][\'y\'].1x(e.Y);L++}K.1E=m};2M=8(e){3(K.1E){1u=m;h.36(e.X,e.Y);h.37();3(!1Q(e.X)){j[L-1][\'x\'].1x(e.X)}3(!1Q(e.Y)){j[L-1][\'y\'].1x(e.Y)}}};2J=8(e){3(K.1E){K.2I(e);K.1E=6}};b.3X=8(e){2D(e)};b.3T=8(e){2D(e)};b.2I=8(e){2M(e)};b.3V=8(e){2M(e)};b.2E=8(e){2J(e)};b.3W=8(e){2J(e)};f.3h=8(){F 6}};8 19(e){3(e.48=="1R"){5 49=K[\'2E\'];49(e);F 6}5 4a=1;5 4b=1;5 4c=(21&&!3n?r.3P.6T:0);3(1V){e.X=e.4d[0].6V-1o;e.Y=e.4d[0].6W-1s;e.6X()}g{3(e.2n||e.2n==0){e.X=2o.1T((e.2n-1o)*4a);e.Y=2o.1T((e.6Y-1s-4c)*4b)}g 3(e.2l||e.2l==0){e.X=e.2l;e.Y=e.70}}5 2e=K[e.48];3(2e){2e(e)}}};5 5B=14 3R(D)};',62,437,'|||if||var|false||function|setAttribute||this||style||canvas|else|context||dd_drawnLines|addEventListener|dd_baseCanvas|true||||with|document|||event|px|||||rqObj|left|id|objCanvas|display|return|createElement|appendChild|data|selfY|tool|dd_drawnPoint|selfX|top|indexOf|png|img|length|iconWrapper|width|navigator|color|none|_x|_y|pen||dd_allowTools|userAgent|typeof|new|break|objPencilPicker|isWriteObj|2px|mover|click|border|height|objPicker|title|src|case|alert|shadowBlur|div|rq|1px|dd_imgPath|objSaveButton|offX|toLowerCase|tagName|try|offY|solid|dd_signatureStarted|url|params|push|null|dd_print2Element|block|wrapper|getContext|format|started|er|dd_alert|catch|dd_agentWK|innerHTML|baseURL|position|window|for|elementWritable|auto|isNaN|mouseout|dd_canvasFormat|round|dd_colors|touchEnabled|imgElem|0pt|objSaveButtonWrapper|cursor||dd_agentIE|checks|undo|ffffff||lineWidth|image|offsetLeft||offsetTop|backgroundColor|col||doFunction|elemSet|textAlign|iconErasor|dd_clearCanvas|iconUndo|info|offsetX|dd_httpFail|layerX|Math|dataPrint|pencil|object|relative|setRequestHeader|pencilOptions|ActiveXObject|OS|XMLHttpRequest|padding|the|c_args|iconColorPicker|getElementById|eventInit|mouseup|todataurl|dd_xmlTimer|Array|mousemove|eventEnd|iconPencilPicker|Thin|eventDraw|switch|inline|beginPath|mouseover|getAttribute|delete|default|isID|dd_showPencilPicker|bottom|target|right|strokeStyle|shadowColor|checkFunctions|0px|80|moveTo|toDataURL|lineTo|stroke|html5|linewidth|shadowblur_ie|shadowblur_wk|shadowblur|inherit|dd_xmlObject|absolute|c0c0c0|onselectstart|abort|borderRadius|dd_webkitCursorOffset|split|XMLHTTP|dd_agentIE_10|Internet|Explorer|please|your|ActiveX|or|supports|browser|undefined|MSIE|Content|dd_toDataURLExist|3pt|134|dd_defaultvalue|fileName|imgWidth|imgHeight|dd_saveSignature|boxShadow|are|dd_objSaveButton|dd_undo|brkI|opacity|15px|nodataurl|documentElement|1pt|initCanvas|dd_hidePickers|touchstart|dd_signaturePadWrapper|touchmove|touchend|mousedown|808080|dd_showColorPicker|doPencil|Signature|dd_tool_colorPicker|Change|pointer|dd_tool_pencilPicker|picker|in|type|func|scaleOffsetX|scaleOffsetY|scrollOffset|targetTouches|zIndex|Normal|99|icon_erasor|74px|cur|Clear|Pad|Msxml2|onmousedown|Microsoft|If|you|line|pencil_option_3|clearRect|dd_tool_undo|enable|icon_undo|Undo|Last|update|to|version|that|Line|it|Your|dd_colorPicker|does|not|support|To|utilize|option|use|one|of|missing|number|setTimeout|splice|apply|eval|open|POST|155px|dd_signatureToolWrapper|Type|application|www|form|urlencoded|Length|Connection|close|send|pencil_option_1|There|is|connection|issue|at|time|nPlease|refresh|and|again|nosig|dd_objSaveButtonWrapper|clear|marginLeft|input|select|textarea|dd_pencilPicker_show|icon_loading_0|gif|vertical|align|middle|Saving|dd_buildStructure|dd_outputFileName|both|dd_signaturePad|bg|replace|base64|action|sig|file_name|escape|nocache|dd_objSaveButtonName|random|999|icon_color_picker|2500|dd_dataProcessURL|dd_callbacks|onreadystatechange|readyState|status|200|clearTimeout|submit|responseText|success|dd_colorPicker_show|Saved|dd_color_|nYou|string|dd_noDataURLOnLoad|span|li|blockquote|td|dd|body|em|small|cite|kbd|00ff00|dd_imgWriteViewer|dd_doPostAction|dataSaved|AppleWebKit|savefail|srcElement|ontouchstart|dd_agentFF|Error|Firefox|Thickness|margin|do|while|offsetParent|lineCap|000000|lineJoin|shadowcolor|Color|parseFloat|appVersion|dd_canvas|pencil_option_2|nohtml5|dd_agentOP|Opera|Pencil|Element|dd_iOS_3_0|Thick|3_0|Div|dd_iOS_3_1|3_1|dd_iOS_3_2|3_2|100px|Chrome|scrollTop|icon_pencil_picker|pageX|pageY|preventDefault|layerY|dd_tool_erasor|offsetY|dd_pencilPicker|using'.split('|'),0,{}))

window.addEventListener(
	'load',
	function() {
		dd_buildStructure();
	},
	false
);








