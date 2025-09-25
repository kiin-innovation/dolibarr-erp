// CustomEvent doesn’t show up until IE 11 and Safari 10. Fortunately a simple polyfill pushes support back to any IE 9.
(function () {
	if ( typeof window.CustomEvent === "function" ) return false;
	function CustomEvent ( event, params ) {
		params = params || { bubbles: false, cancelable: false, detail: undefined };
		let evt = document.createEvent( 'CustomEvent' );
		evt.initCustomEvent( event, params.bubbles, params.cancelable, params.detail );
		return evt;
	}
	CustomEvent.prototype = window.Event.prototype;
	window.CustomEvent = CustomEvent;
})();
// End old browsers support

//
// window.onfocus = function() {
// 	focused = true;
// };
// window.onblur = function() {
// 	focused = false;
// };

// Utilisation d'une sorte de namespace en JS
collabTrack = {};
(function(o) {
	// lang par défaut, les valeurs son ecrasées lors du chargement de la page en fonction de la langue
	o.langs = {
		Saved:"Saved",
		errorAjaxCall:"Call ajax error",
		CloseDialog:"Close",
		errorAjaxCallDisconnected:"You are disconnected",
	};

	o.debugMode = true;
	o.newToken = '';
	o.calling = false;

	/**
	 * Default conf, values are overwritten when the page is loaded according to the configuration transmitted.
	 * @type {{}}
	 */
	o.config = {
		interfaceUrl: false,
		token: false, // to set at init
		elementid: 0,
		elementtype: '',
		edit: 0,
		pingWait : 30
	};

	/**
	 * function to call on document ready
	 */
	o.init = function (config = {}, langs= {}){

		if(config && typeof config === 'object'){
			o.config = Object.assign(o.config, config);
		}

		o.newToken = o.config.token;

		if(langs && typeof langs === 'object'){
			o.langs = Object.assign(o.langs, langs);
		}

		o.executeHook('collabTrackInit');


		o.ping();
		o.pingInterval = setInterval(()=>{
			o.ping();
		}, parseInt(o.config.pingWait) * 1000);

		// window.addEventListener("beforeunload", function () {
		// 	o.unloadCall();
		// });
		//
		// document.addEventListener('visibilitychange', function() {
		// 	if (document.visibilityState === "hidden") {
		// 		o.unloadCall();
		// 	}else{
		// 		o.ping();
		// 	}
		// });
	}

	o.unloadCall = function() {
		var url = o.config.interfaceUrl;
		let fd = new FormData();
		fd.append('action', 'unping');
		fd.append('elementid', o.config.elementid);
		fd.append('elementtype', o.config.elementtype);
		navigator.sendBeacon(url, fd);
	}

	/**
	 * Send user ping
	 */
	o.ping = function (){

		// Avoid multiple call
		if(o.calling ) { //|| !document.hasFocus()
			return;
		}

		o.calling = true;
		o.callInterface('ping', {
			elementid:  o.config.elementid,
			elementtype: o.config.elementtype,
			edit:  o.config.edit,
		}, (response)=>{
			o.calling = false;
			if (response.result > 0) {
				if(typeof response.data != undefined && response.data != null
					&& typeof response.data.users != undefined && response.data.users != null){
					o.displayUsers(response.data.users);
				}
			}
		},()=>{
			o.calling = false;
		});
	}

	o.displayUsers = function(dataResults){
		let domId = 'collab-user-connected';
		let $displayContainer = $('#'+domId);
		$displayContainer.empty();

		if($displayContainer.length === 0){
			$displayContainer = $('<div />').appendTo('body');
			$displayContainer.attr('id', domId);
			$displayContainer.addClass('--bottom-left');
		}

		let $listContainer = $('<ul />').appendTo($displayContainer);

		dataResults.forEach((user) => {

			let listItem = $('<li />');
			listItem.attr('title', user.userName);
			listItem.attr('data-user-id', user.userId);
			listItem.attr('data-edit', user.edit);

			if(user.userImg.length > 0){
				$(user.userImg).appendTo(listItem);``
			}

			listItem.appendTo($listContainer)
		});
	}

	/**
	 * Permet la déclaration de hook en js permettant aux autres modules de ce plug
	 * @param hookName
	 * @param paramsObject
	 * @returns {boolean}
	 *
	 * Usage exemple :
	 *  # Hook declaration
	 *  o.executeHook('MyHook', {
	 * 		message: 'There was a problem creating your account.'
	 * })
	 *
	 * # Hook usage in another module
	 * window.addEventListener('MyHook', function (e){
	 * 		alert(e.params.message);
	 * 		let o = e.detail.psLiveEdit;
	 * });
	 */
	o.executeHook = function (hookName, paramsObject = {}) {

		if (!(typeof hookName === 'string') && !(hookName instanceof String) || hookName.length === 0 ){
			return false;
		}

		if(paramsObject && typeof paramsObject !== 'object'){
			return false;
		}

		if(o.debugMode){
			console.log("collabTrack call hook : " + hookName);
		}

		// Assign default params
		let params = {
			'collabTrack': o // give global object each times
		};
		params = Object.assign(params, paramsObject);

		// Create a new event
		const event = new CustomEvent(hookName,  {
			"detail":params
		});

		// Dispatch the event// We are dispatching your custom event here.
		window.dispatchEvent(event);

		return true;
	};

	/**
	 * @param url
	 * @param action
	 * @param sendData
	 * @param successCallBackFunction
	 * @param errorCallBackFunction
	 * @param alwaysCallBackFunction
	 * @returns {Promise<object>}
	 */
	o.callInterface = async function (action, sendData = {}, successCallBackFunction = ()=>{}, errorCallBackFunction = ()=>{}, alwaysCallBackFunction = ()=>{}){
		let ajaxData = {
			'data': sendData,
			'token': o.newToken,
			'action': action,
		};
		return new Promise((resolve, reject) => {
			$.ajax({
				method: 'POST',
				url: o.config.interfaceUrl,
				dataType: 'json',
				data: ajaxData
			}).done( function (response) {

				if (typeof successCallBackFunction === 'function') {
					successCallBackFunction(response);
				} else {
					console.error('Callback function invalide');
				}

				if (response.newToken !== undefined) {
					o.newToken = response.newToken;
				}

				if (response.msg.length > 0) {
					o.setEventMessage(response.msg, response.result > 0); // , response.result == 0 ? true : false
				}

				resolve(response);
			}).fail(function (err) {

				if (typeof errorCallBackFunction === 'function') {
					errorCallBackFunction(err);
				} else {
					console.error('Error Callback function invalid');
				}

				if (err.responseText.length > 0) {

					// detect login page in case of just disconnected
					let loginPage = $(err.responseText).find('[name="actionlogin"]');
					if (loginPage !== undefined && loginPage.val() === 'login') {
						o.setEventMessage(o.langs.errorAjaxCallDisconnected, false);

						setTimeout(function () {
							location.reload();
						}, 2000);

					} else {
						o.setEventMessage(o.langs.errorAjaxCall, false);
					}
				} else {
					o.setEventMessage(o.langs.errorAjaxCall, false);
				}

				reject(err);
			}).always(function () {
				if (typeof alwaysCallBackFunction === 'function') {
					alwaysCallBackFunction();
				}
			});
		});
	}

	/**
	 *
	 * @param {string} msg
	 * @param {boolean} status
	 * @param {boolean} sticky
	 */
	o.setEventMessage = function (msg, status = true, sticky = false){

		let jnotifyConf = {
			delay: 1500                               // the default time to show each notification (in milliseconds)
			, type : 'error'
			, sticky: sticky                             // determines if the message should be considered "sticky" (user must manually close notification)
			, closeLabel: "&times;"                     // the HTML to use for the "Close" link
			, showClose: true                           // determines if the "Close" link should be shown if notification is also sticky
			, fadeSpeed: 150                           // the speed to fade messages out (in milliseconds)
			, slideSpeed: 250                           // the speed used to slide messages out (in milliseconds)
		}

		if(msg.length > 0){
			if(status){
				jnotifyConf.type = '';
				$.jnotify(msg, jnotifyConf);
			}
			else{
				$.jnotify(msg, jnotifyConf);
			}
		}
		else{
			$.jnotify('ErrorMessageEmpty', jnotifyConf);
		}
	}
})(collabTrack);
