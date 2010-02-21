<?php

require_once(sfConfig::get('sf_symfony_lib_dir') . '/helper/JavascriptBaseHelper.php');

/**
 * @desc  Load jquery library in header
 * tom@punkave.com: sfJqueryReloadedPlugin not sfJqueryPlugin.
 * Also, sf_jquery_web_dir wasn't being treated as a directory.
 *
 * tom@punkave.com 20090413: be consistent with the way global
 * settings.yml files work, it should always be the webdir, not the /js/
 * subfolder of the webdir.
 */

if (!$jq_path = sfConfig::get('sf_jquery_path'))
{
  $jq_path = sfConfig::get('sf_jquery_web_dir', '/sfJqueryReloadedPlugin') .
    '/js/' . sfConfig::get('sf_jquery_core', 'jquery-1.3.2.min.js');
}
sfContext::getInstance()->getResponse()->addJavascript($jq_path, 'first');

/**
 * Add jQuery plugins by name rather than by filename so that you don't have
 * to worry about what the current version is. Currently supported:
 *
 * sortable
 * autocomplete
 *
 * This is useful to app developers when the normal "just in time" approach 
 * doesn't work. For instance, if you are making helper calls in layout.php (or
 * components invoked by it...) and you have already called get_javascripts, 
 * it's too late to rely on the automatic calls to jq_add_plugin in the 
 * various helpers. So call this early instead, right after use_helper.
 *
 * Example:
 *   <?php echo jq_add_plugins_by_name(array('sortable', 'autocomplete')) ?>
 *
 */

function jq_add_plugins_by_name($args = array()) {
  /* 
   * When adding the capability to use a new plugin you must
   * extend this array, and keep it up to date when you update to
   * a new version. You must also update the plugin's
   * default config/settings.yml file
   */

  $plugins = array(
    // Backwards compatibility
    'sortable' => 'jquery-ui-1.7.2.custom.min.js',
    'ui' => 'jquery-ui-1.7.2.custom.min.js',
    'autocomplete' => 'jquery.autocomplete.min.js'
  );

  $pluginPaths = sfConfig::get('sf_jquery_plugin_paths');
  foreach ($args as $name)
  {
    if (!isset($plugins[$name]))
    {
      throw new Exception("Unknown jQuery plugin name $name");
    }
    if (!isset($pluginPaths[$name]))
    {
      $filename = sfConfig::get("sf_jquery_$name", $plugins[$name]);
      $filename = sfConfig::get('sf_jquery_web_dir', '/sfJqueryReloadedPlugin') . "/js/plugins/$filename";
    } else {
      $filename = $pluginPaths[$name];
    }
    sfContext::getInstance()->getResponse()->addJavascript($filename);
  }
}


/*
 * Backwards compatibility only. Don't use this.
 */

function jq_add_plugin($options = array()) {
	// tom@punkave.com: with a singular name (jq_add_plugin), this function
	// really should accept a non-array argument
	if (!is_array($options))
	{
		$options = array($options);
	}
	foreach ( $options as $o ) {
    $file = sfConfig::get('sf_jquery_web_dir', '/sfJqueryReloadedPlugin') . "/js/plugins/$o";
		sfContext::getInstance ()->getResponse ()->addJavascript ($file);
	}
}


/**
 * Periodically calls the specified url ('url') every 'frequency' seconds (default is 10).
 * Usually used to update a specified div ('update') with the results of the remote call.
 * The options for specifying the target with 'url' and defining callbacks is the same as 'link_to_remote()'.
 */
function jq_periodically_call_remote($options = array())
{
	$frequency = isset($options['frequency']) ? $options['frequency'] : 10; // every ten seconds by default
	$code = 'setInterval(function() {'.jq_remote_function($options).'}, '.($frequency * 1000).')';

	return javascript_tag($code);
}

/**
 * Returns an html button to a remote action defined by 'url' (using the
 * 'url_for()' format) that's called in the background using XMLHttpRequest.
 *
 * See link_to_remote() for details.
 *
 */
function jq_button_to_remote($name, $options = array(), $html_options = array())
{
	return jq_button_to_function($name, jq_remote_function($options), $html_options);
}


/**
 * Returns a link to a remote action defined by 'url'
 * (using the 'url_for()' format) that's called in the background using
 * XMLHttpRequest. The result of that request can then be inserted into a
 * DOM object whose id can be specified with 'update'.
 * Usually, the result would be a partial prepared by the controller with
 * either 'render_partial()'.
 *
 * Examples:
 *  <?php echo link_to_remote('Delete this post'), array(
 *    'update' => 'posts',
 *    'url'    => 'destroy?id='.$post.id,
 *  )) ?>
 *  <?php echo link_to_remote(image_tag('refresh'), array(
 *    'update' => 'emails',
 *    'url'    => '@list_emails',
 *  )) ?>
 *
 * You can also specify a hash for 'update' to allow for
 * easy redirection of output to an other DOM element if a server-side error occurs:
 *
 * Example:
 *  <?php echo link_to_remote('Delete this post', array(
 *      'update' => array('success' => 'posts', 'failure' => 'error'),
 *      'url'    => 'destroy?id='.$post.id,
 *  )) ?>
 *
 * Optionally, you can use the 'position' parameter to influence
 * how the target DOM element is updated. It must be one of
 * 'before', 'top', 'bottom', or 'after'.
 *
 * By default, these remote requests are processed asynchronous during
 * which various JavaScript callbacks can be triggered (for progress indicators and
 * the likes). All callbacks get access to the 'request' object,
 * which holds the underlying XMLHttpRequest.
 *
 * To access the server response, use 'request.responseText', to
 * find out the HTTP status, use 'request.status'.
 *
 * If you are using JSON, you can access it via the 'data' parameter
 *
 * Example:
 *  <?php echo jq_link_to_remote($word, array(
 *    'url'      => '@undo?n='.$word_counter,
 *    'complete' => 'undoRequestCompleted(request)'
 *  )) ?>
 *
 * The callbacks that may be specified are (in order):
 *
 * 'loading'                 Called when the remote document is being
 *                           loaded with data by the browser.
 * 'loaded'                  Called when the browser has finished loading
 *                           the remote document.
 * 'interactive'             Called when the user can interact with the
 *                           remote document, even though it has not
 *                           finished loading.
 * 'success'                 Called when the XMLHttpRequest is completed,
 *                           and the HTTP status code is in the 2XX range.
 * 'failure'                 Called when the XMLHttpRequest is completed,
 *                           and the HTTP status code is not in the 2XX
 *                           range.
 * 'complete'                Called when the XMLHttpRequest is complete
 *                           (fires after success/failure if they are present).,
 *
 * You can further refine 'success' and 'failure' by adding additional
 * callbacks for specific status codes:
 *
 * Example:
 *  <?php echo jq_link_to_remote($word, array(
 *       'url'     => '@rule',
 *       '404'     => "alert('Not found...? Wrong URL...?')",
 *       'failure' => "alert('HTTP Error ' + request.status + '!')",
 *  )) ?>
 *
 * A status code callback overrides the success/failure handlers if present.
 *
 * If you for some reason or another need synchronous processing (that'll
 * block the browser while the request is happening), you can specify
 * 'type' => 'synchronous'.
 *
 * You can customize further browser side call logic by passing
 * in JavaScript code snippets via some optional parameters. In
 * their order of use these are:
 *
 * 'confirm'             Adds confirmation dialog.
 * 'condition'           Perform remote request conditionally
 *                       by this expression. Use this to
 *                       describe browser-side conditions when
 *                       request should not be initiated.
 * 'before'              Called before request is initiated.
 * 'after'               Called immediately after request was
 *                       initiated and before 'loading'.
 * 'submit'              Specifies the DOM element ID that's used
 *                       as the parent of the form elements. By
 *                       default this is the current form, but
 *                       it could just as well be the ID of a
 *                       table row or any other DOM element.
 */
function jq_link_to_remote($name, $options = array(), $html_options = array())
{
	return jq_link_to_function($name, jq_remote_function($options), $html_options);
}

/**
 * Returns a Javascript function (or expression) that will update a DOM element '$element_id'
 * according to the '$options' passed.
 *
 * Possible '$options' are:
 * 'content'            The content to use for updating. Can be left out if using block, see example.
 * 'action'             Valid options are 'update' (assumed by default), 'empty', 'remove'
 * 'position'           If the 'action' is 'update', you can optionally specify one of the following positions:
 *                      'before', 'top', 'bottom', 'after'.
 *
 * Example:
 *   <?php echo javascript_tag(
 *      update_element_function('products', array(
 *            'position' => 'bottom',
 *            'content'  => "<p>New product!</p>",
 *      ))
 *   ) ?>
 */

function jq_update_element_function($element_id, $options = array())
{


	$content = escape_javascript(isset($options['content']) ? $options['content'] : '');

	$value = isset($options['action']) ? $options['action'] : 'update';
	switch ($value)
	{
		case 'update':
			$updateMethod = _update_method(isset($options['position']) ? $options['position'] : '');
			$javascript_function = "jQuery('#$element_id').$updateMethod('$content')";
			break;

		case 'empty':
			$javascript_function = "jQuery('#$element_id').empty()";
			break;

		case 'remove':
			$javascript_function = "jQuery('#$element_id').remove()";
			break;

		default:
			throw new sfException('Invalid action, choose one of update, remove, empty');
	}

	$javascript_function .= ";\n";

	return (isset($options['binding']) ? $javascript_function.$options['binding'] : $javascript_function);
}

/**
 * Returns the javascript needed for a remote function.
 * Takes the same arguments as 'link_to_remote()'.
 *
 * Example:
 *   <select id="options" onchange="<?php echo remote_function(array('update' => 'options', 'url' => '@update_options')) ?>">
 *     <option value="0">Hello</option>
 *     <option value="1">World</option>
 *   </select>
 */
function jq_remote_function($options)
{
	// Defining elements to update
	if (isset($options['update']) && is_array($options['update']))
	{
		// On success, update the element with returned data
		if (isset($options['update']['success'])) $update_success = "#".$options['update']['success'];

		// On failure, execute a client-side function
		if (isset($options['update']['failure'])) $update_failure = $options['update']['failure'];
	}
	else if (isset($options['update'])) $update_success = "#".$options['update'];

	// Update method
	$updateMethod = _update_method(isset($options['position']) ? $options['position'] : '');

	// Callbacks
	if (isset($options['loading'])) $callback_loading = $options['loading'];
	if (isset($options['complete'])) $callback_complete = $options['complete'];
	if (isset($options['success'])) $callback_success = $options['success'];

	$execute = 'false';
	if ((isset($options['script'])) && ($options['script'] == '1')) $execute = 'true';

	// Data Type
	if (isset($options['dataType']))
	{
		$dataType = $options['dataType'];
	}
	elseif ($execute)
	{
		$dataType = 'html';
	}
	else
	{
		$dataType = 'text';
	}

	// POST or GET ?
	$method = 'POST';
	if ((isset($options['method'])) && (strtoupper($options['method']) == 'GET')) $method = $options['method'];

	// async or sync, async is default
	if ((isset($options['type'])) && ($options['type'] == 'synchronous')) $type = 'false';

	// Is it a form submitting
	if (isset($options['form'])) $formData = 'jQuery(this).serialize()';
	elseif (isset($options['submit'])) $formData = '{\'#'.$options['submit'].'\'}.serialize()';
	// boutell and JoeZ99: 'with' should not be quoted, it's not useful
	// that way, see the Symfony documentation for the original remote_function
	elseif (isset($options['with'])) $formData = $options['with'];
	// Is it a link with csrf protection
	elseif(isset($options['csrf']) && $options['csrf'] == '1')
	{
		$form = new sfForm();
  		if ($form->isCSRFProtected())
  		{
  			$formData = '{'.$form->getCSRFFieldName().': \''.$form->getCSRFToken().'\'}';
  		}
	}

	// build the function
	$function = "jQuery.ajax({";
	$function .= 'type:\''.$method.'\'';
	$function .= ',dataType:\'' . $dataType . '\'';
	if (isset($type)) $function .= ',async:'.$type;
	if (isset($formData)) $function .= ',data:'.$formData;
	if (isset($update_success) and !isset($callback_success)) $function .= ',success:function(data, textStatus){jQuery(\''.$update_success.'\').'.$updateMethod.'(data);}';
	if (isset($update_failure)) $function .= ',error:function(XMLHttpRequest, textStatus, errorThrown){'.$update_failure.'}';
	if (isset($callback_loading)) $function .= ',beforeSend:function(XMLHttpRequest){'.$callback_loading.'}';
	if (isset($callback_complete)) $function .= ',complete:function(XMLHttpRequest, textStatus){'.$callback_complete.'}';
	if (isset($callback_success)) $function .= ',success:function(data, textStatus){'.$callback_success.'}';
	$function .= ',url:\''.url_for($options['url']).'\'';
	$function .= '})';

	if (isset($options['before']))
	{
		$function = $options['before'].'; '.$function;
	}
	if (isset($options['after']))
	{
		$function = $function.'; '.$options['after'];
	}
	if (isset($options['condition']))
	{
		$function = 'if ('.$options['condition'].') { '.$function.'; }';
	}
	if (isset($options['confirm']))
	{
		$function = "if (confirm('".escape_javascript($options['confirm'])."')) { $function; }";
		if (isset($options['cancel']))
		{
			$function = $function.' else { '.$options['cancel'].' }';
		}
	}

	return $function;
}

/**
 * Returns a form tag that will submit using XMLHttpRequest in the background instead of the regular
 * reloading POST arrangement. Even though it's using JavaScript to serialize the form elements, the form submission
 * will work just like a regular submission as viewed by the receiving side (all elements available in 'params').
 * The options for specifying the target with 'url' and defining callbacks are the same as 'link_to_remote()'.
 *
 * A "fall-through" target for browsers that don't do JavaScript can be specified
 * with the 'action'/'method' options on '$options_html'
 *
 * Example:
 *  <?php echo form_remote_tag(array(
 *    'url'      => '@tag_add',
 *    'update'   => 'question_tags',
 *    'loading'  => "Element.show('indicator'); \$('tag').value = ''",
 *    'complete' => "Element.hide('indicator');".visual_effect('highlight', 'question_tags'),
 *  )) ?>
 *
 * The hash passed as a second argument is equivalent to the options (2nd) argument in the form_tag() helper.
 *
 * By default the fall-through action is the same as the one specified in the 'url'
 * (and the default method is 'post').
 */
function jq_form_remote_tag($options = array(), $options_html = array())
{
	$options = _parse_attributes($options);
	$options_html = _parse_attributes($options_html);

	$options['form'] = true;

	$options_html['onsubmit'] = jq_remote_function($options).'; return false;';
	$options_html['action'] = isset($options_html['action']) ? $options_html['action'] : url_for($options['url']);
	$options_html['method'] = isset($options_html['method']) ? $options_html['method'] : 'post';

	return tag('form', $options_html, true);
}





/**
 * Returns a JavaScript snippet to be used on the AJAX callbacks for starting
 * visual effects.
 *
 *
 * Visual effect support 3 optionnal options :  callback,  speed, opacity.  Not all effect does support all options.
 *
 * List of effects (options that supported by this effect)
 * Effects:
 *         - hide(speed, callback)
 *         - show(speed, callback)
 *         - slideDown(speed, callback)
 *         - slideUp(speed, callback)
 *         - slideToggle(speed, callback)
 *         - fadeIn(speed, callback)
 *         - fadeOut(speed, callback)
 *         - fadeTo(speed, opacity, callback)
 *         - toggle
 *
 *
 *
 * Example of effect that support speed + callback:
 *  <?php echo link_to_remote('Reload', array(
 *        'update'  => 'posts',
 *        'url'     => '@reload',
 *        'complete => visual_effect('fade', '#posts', array('speed' => 'slow', 'callback' => 'function(){$("post_content").css("background", "yellow")}' )),
 *  )) ?>
 *
 * Speed support 4 arguments : 'fast', 'normal', 'slow', number_in_miliseconds
 *
 *
 *
 * Example of effect that support speed + opacity + callback:
 *  <?php echo link_to_remote('Reload', array(
 *        'update'  => 'posts',
 *        'url'     => '@reload',
 *        'complete => visual_effect('fadeTo', '#posts', array('speed' => 'slow', 'opacity' => '0.33', 'callback' => 'function(){$("post_content").css("background", "yellow")}' )),
 *  )) ?>
 *
 * Opacity must be between 0 and 1, if none are specify or not between 0 and 1m, it assumes 0.5(default value)
 *
 *
 *
 *  Element is optional so if you dont specify it assumes current element
 *  Example
 *  <?php echo form_to_remote(array(
 *        'update'  => 'posts',
 *        'url'     => '@reload',
 *        'complete => visual_effect('hide'),
 *  )) ?>
 *
 * This would hide the form when request is completed
 *
 *
 *
 * Fore more information check the jquery doc
 * http://http://docs.jquery.com/Effects.
 */
function jq_visual_effect($effect, $element_id = false, $js_options = array())
{

	//format slide /fade effect name correctly.
	if(preg_match("/^(slide|fade)/i", $effect, $matches))
	{
		$count = strtolower($matches[1]) == 'fade'? 3 : 4;
		$effect = preg_replace("/(^|_|-)+(.)/e", '', $effect); //remove non alpha char
		$effect = preg_replace('/\ +/', '', $effect);  //remove space
			

			
		$effect = trim(strtolower($matches[1]).ucfirst(strtolower(substr($effect, $count))));
	}
	else
	{
		$effect = trim(strtolower($effect));
	}

	$element = $element_id ? "'$element_id'" : 'this';

	//Building speed
	$speed = isset($js_options['speed'])? is_numeric($js_options['speed'] )?$js_options['speed'] : "'". $js_options['speed'] ."'": "'normal'";

	//Building opacty
	$opacity = isset($js_options['opacity']) && is_numeric($js_options['opacity'] )?$js_options['opacity'] >= 0 && $js_options['opacity'] <= 1?$js_options['opacity'] :0.5:0.5;

	//Building callback
	$callback =  isset($js_options['callback']) ? ", ".  $js_options['callback'] :null;



	if(in_array($effect, array('hide', 'show','slideDown', 'slideUp', 'slideToggle', 'fadeIn', 'fadeOut')))
	{
		return  sprintf("jQuery(%s).%s(%s %s );", $element, $effect, $speed, $callback);
	}
	elseif($effect == "fadeTo")
	{
		return  sprintf("jQuery(%s).%s(%s, %s %s);", $element, $effect, $speed, $opacity, $callback);
	}
	else
	{
		return  sprintf("jQuery(%s).%s();", $element, $effect);
	}
}


/**
 *  Returns a button input tag that will submit form using XMLHttpRequest in the background instead of regular
 *  reloading POST arrangement. The '$options' argument is the same as in 'form_remote_tag()'.
 */
function jq_submit_to_remote($name, $value, $options = array(), $options_html = array())
{
	$options = _parse_attributes($options);
	$options_html = _parse_attributes($options_html);

	if (!isset($options['with']))
	{
		$options['with'] = 'jQuery(this.form.elements).serialize()';
	}

	$options_html['type'] = 'button';
	$options_html['onclick'] = jq_remote_function($options).'; return false;';
	$options_html['name'] = $name;
	$options_html['value'] = $value;

	return tag('input', $options_html, false);
}


/**
 *  Returns a image submit tag that will submit form using XMLHttpRequest in the background instead of regular
 *  reloading POST arrangement. The '$options' argument is the same as in 'form_remote_tag()'.
 */
function jq_submit_image_to_remote($name, $source, $options = array(), $options_html = array())
{
	$options = _parse_attributes($options);
	$options_html = _parse_attributes($options_html);

	if (!isset($options['with']))
	{
		$options['with'] = 'jQuery(this.form.elements).serialize()';
	}

	$options_html['type'] = 'image';
	$options_html['onclick'] = jq_remote_function($options).' return false;';
	$options_html['name'] = $name;
	$options_html['src'] = image_path($source);

	if (!isset($options_html['alt']))
	{
		$path_pos = strrpos($source, '/');
		$dot_pos = strrpos($source, '.');
		$begin = $path_pos ? $path_pos + 1 : 0;
		$nb_str = ($dot_pos ? $dot_pos : strlen($source)) - $begin;
		$options_html['alt'] = ucfirst(substr($source, $begin, $nb_str));
	}

	return tag('input', $options_html, false);
}

/**
 * Makes the elements matching the jQuery selector '$selector'
 * sortable by drag-and-drop and makes an AJAX call whenever the sort order
 * has changed. By default, the action called gets the serialized sortable
 * element as parameters.
 *
 * Example:
 *   <php echo sortable_element("#foo", array(
 *      'url' => '@order',
 *   )) ?>
 *
 * In the example, the action gets a 'foo' array parameter
 * containing the values of the ids of elements the sortable consists
 * of, in the current order.
 *
 * Additional options can be passed in the $options associative array
 * and will be sent to jquery as parameters. For example:
 * 'handle' => 'span' specifies that span elements within the
 * sortable element are the element the user actually clicks on
 *  (although entire first-generation child elements of the
 * sortable element get reordered as a result).
 *
 * Added by tom@punkave.com.
 */
function jq_sortable_element($selector, $options = array())
{
	// We need ui for this trick. It's now just ui, not sortable; for simplicity
	// we have a catch-all ui package, which is minimized to contain only the 
	// features that actually get used by the plugin. If you want fewer features,
	// or more features, from jQuery ui then get your own minimized package download
	// from the jquery ui site
  jq_add_plugins_by_name(array("ui"));
	$options = _parse_attributes($options);
	$options['url'] = url_for($options['url']);
  $options['type'] = 'POST';
  $selector = json_encode($selector);
  $options = json_encode($options);	
	
	$result = <<<EOM
$(document).ready(
  function() 
  {
    $($selector).sortable(
    { 
      update: function(e, ui) 
      {
        var serial = jQuery($selector).sortable('serialize', {});
        var options = $options;
        options['data'] = serial;
        $.ajax(options);
      }
    } );
  });
EOM;
  return javascript_tag($result);
}


/**
 * wrapper for script.aculo.us/prototype Ajax.Autocompleter.
 * @author Bruno Adele <bruno.adele@jesuislibre.org>
 * @param string name value of input field
 * @param string default value for input field
 * @param array input tag options. (size, autocomplete, etc...)
 * @param array completion options. (use_style, etc...)
 *
 * Example:
 * echo jq_input_auto_complete_tag('q','', 'search/index',array(
 * 		'size' => 15),array(
 * 				'use_style' => false,
 * 				'scrollHeight' => 480,
 * 				'scroll' => false,
 * 				'highlight' => false,
 *		) ) ?>
 *
 * @return string input field tag, div for completion results, and
 *                 auto complete javascript tags
 */
function jq_input_auto_complete_tag($name, $value, $url, $tag_options = array(), $completion_options = array()) {
	// We need ui.autocomplete for this trick
  jq_add_plugins_by_name(array("autocomplete"));

	$tag_options = _convert_options($tag_options);
	$comp_options = _convert_options($completion_options);

	// Convert to JSON parameters
	$jsonOptions = '';
	foreach ($comp_options as $key => $val)
	{
		if ($jsonOptions!='')
		{
			$jsonOptions .= ', ';
		}
		switch($key) {
			case 'formatItem':
			case 'formatResult':
				$jsonOptions .= "$key: " . $val;
				break;
			default:
				$jsonOptions .= "$key: " . json_encode($val);
				break;
		}
	}

	// Get Stylesheet
	$context = sfContext::getInstance();
	$response = $context->getResponse();
	$comp_options = _convert_options($completion_options);
	if (isset($comp_options['use_style']) && $comp_options['use_style'] == true)
	{
		$response->addStylesheet(sfConfig::get('sf_jquery_web_dir').'/css/JqueryAutocomplete');
	}

	// Get Id from name attribute
	$tag_options['id'] = get_id_from_name(isset($tag_options['id']) ? $tag_options['id'] : $name);

	// Add input form
	$javascript  = tag('input', array_merge(array('type' => 'text', 'name' => $name, 'value' => $value), _convert_options($tag_options)));

	// Calc JQuery Javascript code
	$autocomplete_script = sprintf('$("#%s").autocomplete("%s",{ %s	});',$name,$url,$jsonOptions);
	$javascript .=	javascript_tag($autocomplete_script);

	return $javascript;
}

/**
 * Makes the elements matching the selector $selector draggable.
 *
 * Example:
 *   <?php echo jq_draggable_element('ul.mydraggables li', array(
 *      'revert' => true,
 *   )) ?>
 *
 * You can change the behaviour with various options, see
 * http://script.aculo.us for more documentation.
 */
function jq_draggable_element($selector, $options = array())
{
	// We need ui for this trick
  jq_add_plugins_by_name(array("ui"));
	$options = json_encode(_parse_attributes($options));  
	$selector = json_encode($selector);
  return javascript_tag("jQuery($selector).draggable($options)");
}

/**
 * Makes the element with the DOM ID specified by '$element_id' receive
 * dropped draggable elements (created by 'draggable_element()') and make an AJAX call.
 * By default, the action called gets the DOM ID of the element as parameter.
 *
 * Example:
 *   <?php drop_receiving_element('my_cart', array(
 *      'url' => 'cart/add',
 *   )) ?>
 *
 * You can change the behaviour with various options, see
 * http://script.aculo.us for more documentation.
 */
function jq_drop_receiving_element($selector, $options = array())
{
  jq_add_plugins_by_name(array("ui"));
  if (!isset($options['with']))
  {
    $options['with'] = "'id=' + encodeURIComponent(element.id)";
  }
  if (!isset($options['drop']))
  {
    $options['drop'] = "function(element){".jq_remote_function($options)."}";
  }

  // For backwards compatibility with prototype
  if (isset($options['hoverclass']))
  {
    $options['hoverClass'] = $options['hoverclass'];
  }
  $options['hoverClass'] = json_encode('hoverclass');
  
  foreach (jq_get_ajax_options() as $key)
  {
    unset($options[$key]);
  }

  if (isset($options['accept']))
  {
    $options['accept'] = json_encode($options['accept']);
  }
  $options = jq_options_for_javascript($options);
  $selector = json_encode($selector);
  return javascript_tag("jQuery($selector).droppable($options);");
}

function _update_method($position) {
	// Updating method
	$updateMethod = 'html';
	switch ($position) {
		case 'before':$updateMethod='before';break;
		case 'after':$updateMethod='after';break;
		case 'top':$updateMethod='prepend';break;
		case 'bottom':$updateMethod='append';break;
	}

	return $updateMethod;
}

/***  This should be just a wrapper for the JavascriptBaseHelper link_to_function call, 
    but right now it is a copy that contains correct support for 'confirm' that 
    doesn't break IE or produce invalid HTML. It will make sense to turn this back 
    into a simple wrapper once it is fixed in a Symfony release. See:
    
    http://trac.symfony-project.org/ticket/4152 ***/
    
function jq_link_to_function($name, $function, $html_options = array())
{
  $html_options = _parse_attributes($html_options);

  $html_options['href'] = isset($html_options['href']) ? $html_options['href'] : '#';
  if ( isset($html_options['confirm']) )
  {
    $confirm = escape_javascript($html_options['confirm']);
    $html_options['onclick'] = "if(confirm('$confirm')){ $function;}; return false;";
    // tom@punkave.com: without this we get a confirm attribute, which breaks confirm() in IE
    // (we could call window.confirm, but there is no reason to have the
    // nonstandard confirm attribute) 
    unset($html_options['confirm']);
  }
  else
  {
    $html_options['onclick'] = $function.'; return false;';
  }

  return content_tag('a', $name, $html_options);
}
    
/***  This is a wrapper for the JavascriptHelper function  ***/
function jq_button_to_function($name, $function, $html_options = array())
{
	return button_to_function($name, $function, $html_options);
}


/***  This is a wrapper for the JavascriptHelper function  ***/
function jq_javascript_tag($content = null)
{
	return javascript_tag($content);
}



/***  This is a wrapper for the JavascriptHelper function  ***/
function jq_javascript_cdata_section($content)
{
	return javascript_cdata_section($content);
}


/***  This is a wrapper for the JavascriptHelper function  ***/
function jq_if_javascript()
{
	return if_javascript();
}


/***  This is a wrapper for the JavascriptHelper function  ***/
function jq_end_javascript_tag()
{
	return end_javascript_tag();
}

function _options_for_javascript($options)
{
  $opts = array();
  foreach ($options as $key => $value)
  {
    $opts[] = "$key:$value";
  }
  sort($opts);

  return '{'.join(', ', $opts).'}';
}