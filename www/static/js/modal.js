window.Formlet = function(id) {


	// Detect ios 11_0_x affected
    // NEED TO BE UPDATED if new versions are affected
    var ua = navigator.userAgent,
    iOS = /iPad|iPhone|iPod/.test(ua),
    iOS11 = /OS 11_0_1|OS 11_0_2|OS 11_0_3|OS 11_1|OS 11_2/.test(ua);

    // ios 11 bug caret position
    if ( iOS && iOS11 ) {
		//document.body.className += ' ' + 'iosBugFixCaret';
		//document.body.style.position = 'fixed';
    }

	var el = document.getElementById(id);

	window.formletsHost = el.host || 'www.formlets.com';

	iframe = document.createElement('iframe');
	window['formlet'+id] = iframe;

	iframe.setAttribute('sandbox', 'allow-forms allow-scripts allow-top-navigation allow-same-origin');
	iframe.className += ' formlets-iframe';

	var overlay = document.createElement('div');

	overlay.style.position = 'fixed';
	overlay.style.background = 'rgba(0,0,0,0.6)';
	overlay.style.zIndex = '99998';
	overlay.style.top = 0;
	overlay.style.bottom = 0;
	overlay.style.left = 0;
	overlay.style.right = 0;
	overlay.style.display = 'none';
	overlay.style.padding = '32px 0';
	overlay.style.overflowX = 'hidden';
	overlay.style.WebkitOverflowScrolling = 'touch';
	overlay.style.WebkitTransition = 'opacity 200ms linear, background 200ms linear, -webkit-transform 300ms cubic-bezier(0.86, 0, 0.07, 1), transform 300ms cubic-bezier(0.86, 0, 0.07, 1)';
	overlay.style.MozTransition = 'opacity 200ms linear, background 200ms linear, -webkit-transform 300ms cubic-bezier(0.86, 0, 0.07, 1), transform 300ms cubic-bezier(0.86, 0, 0.07, 1)';
	overlay.style.transition = 'opacity 200ms linear, background 200ms linear, -webkit-transform 300ms cubic-bezier(0.86, 0, 0.07, 1), transform 300ms cubic-bezier(0.86, 0, 0.07, 1)';

	overlay.addEventListener('click', function(e) {
		document.body.style.overflow = "visible";
		if ( iOS && iOS11 ) {
			document.body.style.position = "relative";
		}
		overlay.style.opacity = 0;
		setTimeout(function() {
			overlay.style.display = 'none';
			overlay.style.opacity = 1;
		}, 200);
		//Hide all children
		for(var i = 0; i < overlay.children.length; i++) {
			overlay.children[i].style.display = 'none';
		}
	});

	document.body.appendChild(overlay);

	var closeContainer = document.createElement('div');
	closeContainer.style.margin = '0 auto';
	closeContainer.style.height = '36px';
	closeContainer.id = 'close-container-'+id;
	overlay.appendChild(closeContainer);

	var closeButton = document.createElement('img');
	closeButton.style.float = 'right';
	closeButton.style.cursor = 'pointer';
	closeButton.setAttribute('src', '//' + window.formletsHost + '/static/img/x.png');
	closeContainer.appendChild(closeButton);


	iframe.id = 'formlets-iframe-' + id;
	iframe.style.maxHeight = 'calc(100% - 36px)';
	iframe.style.margin = '0 auto';
	iframe.style.display = 'none';
	iframe.style.background = 'white';
	iframe.style.border = '2px solid #D6D7D6';
	iframe.style.borderRadius = '3px';
	iframe.style.WebkitTransition = 'opacity 200ms linear, -webkit-transform 300ms cubic-bezier(0.86, 0, 0.07, 1), transform 300ms cubic-bezier(0.86, 0, 0.07, 1)';
	iframe.style.MozTransition = 'opacity 200ms linear, -webkit-transform 300ms cubic-bezier(0.86, 0, 0.07, 1), transform 300ms cubic-bezier(0.86, 0, 0.07, 1)';
	iframe.style.transition = 'opacity 200ms linear, -webkit-transform 300ms cubic-bezier(0.86, 0, 0.07, 1), transform 300ms cubic-bezier(0.86, 0, 0.07, 1)';

	if (!document.querySelector('style')) document.head.appendChild(document.createElement('style'));
	document.querySelector('style').textContent +=
		"#formlets-iframe-"+id+", #close-container-"+id+" {width: 80%;} @media screen and (min-width:960px) { #formlets-iframe-"+id+", #close-container-"+id+" { width: 50%; }}"
	overlay.appendChild(iframe);
	el.addEventListener('click', function(e) {
		e.preventDefault();
		iframe=document.getElementById("formlets-iframe-" + e.currentTarget.id);
		document.body.style.overflow = "hidden";
		overlay.style.background = 'rgba(0,0,0,0)';
		overlay.style.display = 'block';
		iframe.style.display = 'block';
		iframe.style.opacity = 0;
		iframe.style.WebkitTransform = 'translate3d(0,40px,0)';
		iframe.style.MozTransform = 'translate3d(0,40px,0)';
		iframe.style.transform = 'translate3d(0,40px,0)';
		closeButton.style.display = 'block';
		closeButton.style.opacity = 0;
		closeContainer.style.display = 'block';
		closeContainer.style.opacity = 0;
		closeContainer.style.WebkitTransform = 'translate3d(0,40px,0)';
		closeContainer.style.MozTransform = 'translate3d(0,40px,0)';
		closeContainer.style.transform = 'translate3d(0,40px,0)';
		setTimeout(function() {
			overlay.style.background = 'rgba(0,0,0,0.6)';
			iframe.style.opacity = 1;
			iframe.style.WebkitTransform = 'translate3d(0,0,0)';
			iframe.style.MozTransform = 'translate3d(0,0,0)';
			iframe.style.transform = 'translate3d(0,0,0)';
			closeButton.style.opacity = 1;
			closeContainer.style.opacity = 1;
			closeContainer.style.WebkitTransform = 'translate3d(0,0,0)';
			closeContainer.style.MozTransform = 'translate3d(0,0,0)';
			closeContainer.style.transform = 'translate3d(0,0,0)';
		}, 0);

		return false;
	});

	iFrameResize({checkOrigin: false, scrolling: true}, iframe);
}

window.FormletOpen = function(id) {
	window['formlet'+id].setAttribute('src', '//' + window.formletsHost + '/forms/' + id + '/?iframe=true&modal=true');
}
