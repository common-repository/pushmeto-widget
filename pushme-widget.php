<?php

/*
	Plugin Name: Pushme.to Widget
	Plugin URI: http://pushme.to/
	Version: 1.0.2
	Description: Notification widget for Pushme.to service (http://pushme.to/)
 
	Copyright (c) 2010 Treebune s.r.l. (email: info@pushme.to)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

class PushmeWidget {
	protected $_availableStyles = array('default' => 'Default');
	protected $_defaultStyle = 'default';
	protected $_nickname = null;

	public function __construct() {
		if (function_exists('register_sidebar_widget') && function_exists('register_widget_control')) {
			$this->_drawWidget();
		} else {
			$this->_drawWidgetForOldWp();
		}
	}

	public function activate() {
		$widgetOptions = get_option('widget_pushme');
		$widgetOptions['currentStyle'] = $this->_defaultStyle;
		
		/*
		if (!isset($widgetOptions['currentStyle']) || !in_array($widgetOptions['currentStyle'], $this->_availableStyles)) {
			$widgetOptions['currentStyle'] = $this->_defaultStyle;
		}
	  */
		add_option('widget_pushme', $widgetOptions);
	}

	protected function _drawWidget() {
		register_activation_hook(__FILE__, array(&$this, 'activate'));
		register_sidebar_widget('Pushme Widget', array(&$this, 'getWidgetContent'));
		register_widget_control('Pushme Widget', array(&$this, 'getWidgetControlContent'));
		wp_enqueue_style("pushme_widget_css", "/wp-content/plugins/pushmeto-widget/css/pushme-widget.css");
		wp_enqueue_script('pushme_widget_js', "/wp-content/plugins/pushmeto-widget/js/pushme-widget.js");
	}

	protected function _drawWidgetForOldWp() {
		return ;
	}

	public function getWidgetContent() {
		$options = get_option('widget_pushme');
		if (!isset($options['nickname']) || !($options['nickname'])) {
			echo '<p>Please, specify your pushme.to nickname first';
			return ;
		}		
		$this->_nickname = $options['nickname'];
		
		$currentStyle = $this->_defaultStyle;
//		$currentStyle = isset($options['currentStyle']) ? $options['currentStyle'] : $this->_defaultStyle;
		$widgetClass = "pushme_widget ".$currentStyle;
		$content =
			'<div class="'.$widgetClass.'">'.
				'<div id="send_result" style="display:none" class="widget_success">'.
					'<h4>Message was sent successfully.</h4>'.
					'<button id="send_another"><span>Send another one</span></button>'.
				'</div>'.
				$this->_getFormContent().
				$this->_getCaptchaFormContent().
				'<script type="text/javascript" charset="utf-8">pushmeForm.setHandlers();</script>'.
			'</div>';
			
		echo $content;
	}

	protected function _getFormContent() {
		$content =
'<form class="pushme_widget_form" action="#" method="post" accept-charset="utf-8" id="send_message_form">'.
	'<fieldset><legend style="display: none;">Send message</legend>'.
		'<a href="http://pushme.to/" target="_blank" style="text-decoration: none;">&#160;</a>'.
		'<div><label for="pushme_widget_message_text">Message</label>'.
			'<table>'.
				'<tr><td class="pushme_widget_input_lt"></td>'.
					'<td class="pushme_widget_input_t"></td><td class="pushme_widget_input_rt"></td>'.
				'</tr><tr>'.
					'<td class="pushme_widget_input_l"></td><td class="pushme_widget_input_i">'.
						'<textarea name="message" id="message" class="pushme_widget_message_text"></textarea>'.
					'</td><td class="pushme_widget_input_r"></td>'.
				'</tr><tr><td class="pushme_widget_input_lb"></td>'.
					'<td class="pushme_widget_input_b"></td><td class="pushme_widget_input_rb"></td>'.
			'</tr></table>'.
			'<small id="push_form_no_message">Message cannot be blank</small>'.
			'<small id="push_form_too_long_message">Message is too long</small>'.
		'</div>'.
		'<div><label for="pushme_widget_message_signature">Signature</label>'.
			'<table>'.
				'<tr><td class="pushme_widget_input_lt"></td>'.
					'<td class="pushme_widget_input_t"></td>'.
					'<td class="pushme_widget_input_rt"></td>'.
				'</tr><tr><td class="pushme_widget_input_l"></td>'.
					'<td class="pushme_widget_input_i">'.
						'<input type="text" name="signature" id="signature" class="pushme_widget_message_signature" value="" />'.
					'</td><td class="pushme_widget_input_r"></td>'.
				'</tr><tr><td class="pushme_widget_input_lb"></td>'.
					'<td class="pushme_widget_input_b"></td>'.
					'<td class="pushme_widget_input_rb"></td>'.
				'</tr>'.
			'</table>'.
			'<small id="push_form_no_signature">Signature cannot be blank</small>'.
		'</div>'.
		'<button class="pushme_widget_message_submit" type="submit"><span>Push</span></button>'.
		'<img id="push_in_progress" src="/wp-content/plugins/pushmeto-widget/img/loading.gif" width="24" height="24" alt="" />'.
		'<input type="hidden" name="nickname" id="nickname" value="'.$this->_nickname.'" />'.
	'</fieldset>'.
'</form>';

		return $content;
	}

	protected function _getCaptchaFormContent() {
		$captchaContent =
'<form class="pushme_widget_form" action="#" method="post" accept-charset="utf-8" id="captcha_form">'.
	'<fieldset><legend style="display: none;">Send message</legend>'.
		'<a href="http://pushme.to/" target="_blank" style="text-decoration:none">&#160;</a>'.
		'<p class="pushme_widget_warning">Please type in the code on the image:</p>'.
		'<p id="pushme_widget_message_captcha"><img src="#" width="180" height="85" id="captchaImg" /></p>'.
		'<input type="hidden" value="" id="captchaId" name="captcha[id]" />'.
		'<div><table>'.
			'<tr><td class="pushme_widget_input_lt"></td>'.
				'<td class="pushme_widget_input_t"></td><td class="pushme_widget_input_rt"></td>'.
			'</tr><tr><td class="pushme_widget_input_l"></td>'.
				'<td class="pushme_widget_input_i">'.
					'<input type="text" name="captcha[input]" value="" id="captcha" class="pushme_widget_message_captha_text" />'.
				'</td>'.
				'<td class="pushme_widget_input_r"></td>'.
			'</tr><tr><td class="pushme_widget_input_lb"></td><td class="pushme_widget_input_b"></td>'.
				'<td class="pushme_widget_input_rb"></td>'.
			'</tr></table>'.
			'<small id="push_form_wrong_captcha">Wrong captcha</small>'.
		'</div>'.
		'<button class="pushme_widget_message_submit" type="submit"><span>Push</span></button>'.
		'<img id="push_in_progress2" src="/wp-content/plugins/pushmeto-widget/img/loading.gif" width="24" height="24" alt="" />'.
		'<input type="hidden" name="nickname" id="nickname" value="'.$this->_nickname.'" />'.
	'</fieldset>'.
'</form>';

		return $captchaContent;
	}

	public function getWidgetControlContent() {
		if ( isset($_POST['pushme-widget-submit'] )) {
			$options = array();
			$options['nickname'] = strip_tags(stripslashes($_POST['nickname']));
			$newCurrentStyle = strip_tags(stripslashes($_POST['currentStyle']));
			$options['currentStyle'] = $newCurrentStyle;
			update_option('widget_pushme', $options);
			$this->_currentStyle = $newCurrentStyle;
		} else {
			$options = get_option('widget_pushme');
//			$this->_currentStyle = isset($options['currentStyle']) ? $options['currentStyle'] : $this->_defaultStyle;
			$this->_currentStyle = $this->_defaultStyle;
		}

		$content = '<label>Input your pushme.to nickname:</label><br />';
		$content .= '<input type="text" name="nickname" id="nickname" value="'.(isset($options['nickname']) ? $options['nickname'] : '').'"><br />';

/*
		$content .= '<label>Choose widget style:</label><br />';
		foreach ($this->_availableStyles as $key=>$name) {
			$content .= '<input type="radio" name="currentStyle" id="currentStyle" value="'.$key.'"'.
				($this->_currentStyle == $key ? ' checked' : '').
				'>'.
				'<img src="/wp-content/plugins/pushmeto-widget/img/widget-design-'.$key.'.jpg"><br />'.
				$name.'<br /><br />';
		}
 */
		$content .= '<input type="hidden" id="pushme-widget-submit" name="pushme-widget-submit" value="1" />';

		echo $content;
	}
}

new PushmeWidget();

?>
