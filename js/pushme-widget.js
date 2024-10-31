if (!Function.prototype.pushme_createCallback) {
  Function.prototype.pushme_createCallback = function (obj)
  {
    var func = this;
    return function () {return func.apply(obj, arguments);};
  };
}


function PushmeForm() {
	this.host = 'http://pushme.to/';
}

PushmeForm.prototype.hide = function(id) {
	document.getElementById(id).style.display="none";
}

PushmeForm.prototype.show = function(id) {
	document.getElementById(id).style.display="block";
}

PushmeForm.prototype.val = function(id) {
	return document.getElementById(id).value;
}

PushmeForm.prototype.setVal = function(id, value) {
	document.getElementById(id).value = value;
}

PushmeForm.prototype.trim = function(str) {
	return str.replace(/^\s\s*/, '').replace(/\s\s*$/, '');
}

PushmeForm.prototype.clearValidation = function() {
	this.hide('push_form_too_long_message');
	this.hide('push_form_no_message');
	this.hide('push_form_no_signature');
	this.hide('push_form_wrong_captcha');
}

PushmeForm.prototype.clearData = function() {
	this.setVal('message', '');
	this.setVal('captcha', '');
}

PushmeForm.prototype.validate = function() {
	var isFormValid = true;

	if (this.trim(this.val('message'))=='') {
		this.show('push_form_no_message');
		isFormValid = false;
	}

	if (this.trim(this.val('signature'))=='') {
		this.show('push_form_no_signature');
		isFormValid = false;
	}

	return isFormValid;
}

PushmeForm.prototype.validateCaptcha = function() {
	if (this.trim(this.val('captcha'))=='') {
		this.show('push_form_wrong_captcha');
		return false;
	}
	return true;
}

PushmeForm.prototype.enableSubmitButton = function(doEnable) {
	var element = document.getElementById('send_message_submit');
	if (element) {
		if (doEnable) {
			element.disabled=undefined;
		} else {
			element.disabled='disabled';
		}
	}
}

PushmeForm.prototype.showInProgress = function(isInProgress) {
	if (isInProgress) {
		document.getElementById('push_in_progress').style.visibility='visible';
		document.getElementById('push_in_progress2').style.visibility='visible';
		this.enableSubmitButton(false);
	} else {
		document.getElementById('push_in_progress').style.visibility='hidden';
		document.getElementById('push_in_progress2').style.visibility='hidden';
		this.enableSubmitButton(true);
	}
}

PushmeForm.prototype.showSentSuccess = function()  {
	this.hide('send_message_form');
	this.hide('captcha_form');
	this.show('send_result');
}

PushmeForm.prototype.onSuccess = function(obj) {
	this.showInProgress(false);
	if ('success' == obj.status) {
		if (true == obj.needCaptcha) {
			this.showCaptchaSecondary(obj.captchaId);
		} else {
			this.showSentSuccess();
			this.clearData();
		}
	} else if ('error' == obj.status) {
		var errorShown=false;
		if (obj.error == 'noMessage') {
			errorShown=true;
			this.show('push_form_no_message');
		}
		if (obj.error == 'noSignature') {
			errorShown=true;
			this.show('push_form_no_signature');
		}
		if (obj.error == 'tooLong') {
			errorShown=true;
			this.show('push_form_too_long_message');
		}
		if (obj.error == 'wrongCaptcha') {
			errorShown=true;
			this.show('push_form_wrong_captcha');
			document.getElementById('captchaImg').src=this.host+'/captcha/?id='+obj.captchaId;
			this.setVal('captchaId', obj.captchaId);
		}

		if (!errorShown) {
			this.showError("Unknown error happened. Try again later.");
		}
	}
}

PushmeForm.prototype.clearError = function() {
}

PushmeForm.prototype.showError = function(errorString) {
}

PushmeForm.prototype.onFailure = function() {
	this.showInProgress(false);
	this.showError("Error happened. Try again later.");
}

PushmeForm.prototype.setHandlers = function() {
	this.clearValidation();
	this.showInProgress(false);
	this.clearError();

	this.hide('captcha_form');

	document.getElementById('send_message_form').onsubmit = this.onMainFormSubmit.pushme_createCallback(this);
	document.getElementById('captcha_form').onsubmit = this.onCaptchaFormSubmit.pushme_createCallback(this);

	document.getElementById('send_another').onclick = this.onClickSendAnother.pushme_createCallback(this);
}

PushmeForm.prototype.onClickSendAnother = function() {
	this.hide('send_result');
	this.show('send_message_form');
	return false;
}

PushmeForm.prototype.onMainFormSubmit = function() {
	this.clearValidation();
	this.clearError();

	if (!this.validate()) {
		return false;
	}
	
	this.showInProgress(true);

	var arguments = "message="+encodeURIComponent(this.val('message'))+
		"&signature="+encodeURIComponent(this.val('signature'))+
		"&nickname="+encodeURIComponent(this.val('nickname'))+
		"&jsonp_callback=pushmeForm.onSuccess"+
		"&isFirstTime=1";

  var script = document.createElement("script");
  script.src=this.host+"z/ajax/pushme/?"+arguments;
  document.body.appendChild(script);

	return false;
}


PushmeForm.prototype.onCaptchaFormSubmit = function() {
	this.clearValidation();
	this.clearError();

	if (!this.validateCaptcha()) {
		return false;
	}

	this.showInProgress(true);

	var arguments = "nickname="+encodeURIComponent(this.val('nickname'))+
		"&jsonp_callback=pushmeForm.onSuccess"+
		"&captcha[input]="+encodeURIComponent(this.val('captcha'))+
		"&captcha[id]="+encodeURIComponent(this.val('captchaId'));

  var script = document.createElement("script");
  script.src=this.host+"z/ajax/pushme/?"+arguments;
  document.body.appendChild(script);

	return false;
}

PushmeForm.prototype.showCaptchaSecondary = function(captchaId) {
	this.hide('send_message_form');
	document.getElementById('captchaImg').src=this.host+'/captcha/?id='+captchaId;
	this.setVal('captchaId', captchaId);
	this.show('captcha_form');
}


var pushmeForm = new PushmeForm();
