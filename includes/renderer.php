<?php
//an element on a page that includes some base HTML and the ability to bind variables
class PageElement {
	private $raw_text;
	private $params = array();
	
	//initialize a PageElement with a given content file (in the templates/ directory)
	function __construct($content_file) {
		$this->raw_text = file_get_contents(SRV_ROOT . '/templates/' . $content_file);
	}
	
	//bind a parameter to a value (the parameter is stored in the content file as "<$(key)/>"
	function bind($key, $val) {
		$this->params[$key] = $val;
	}
	
	//return the text of this page element with the bound parameters
	function render() {
		$out_text = $this->raw_text; //set the raw text
		//bind each parameter to the raw text
		foreach ($this->params as $key => $val) {
			$out_text = str_replace('<$(' . $key . ')/>', $val, $out_text);
		}
		return $out_text; //return the text with the bound parameters
	}
}

//a collection of page elements that can be bundled together
class MultiPageElement {
	private $elements = array(); //each individual page element we have
	
	//add an element
	function addElement(PageElement $element) {
		$this->elements[] = $element;
	}
	
	//render the page by just rendering each page element and concatenating them
	function render() {
		$outstring = '';
		foreach ($this->elements as $element) {
			$outstring .= $element->render();
		}
		return $outstring;
	}
}

//create a form with given fields (array in the form [id] => caption)
function create_form($fields) {
	$form_elements = new MultiPageElement();
	foreach ($fields as $id => $info) {
		$form_element = new PageElement('formfield.html');
		$form_element->bind('name', $id);
		$form_element->bind('label', $info['caption']);
		$form_element->bind('type', $info['type']);
		$form_elements->addElement($form_element);
	}
	
	return $form_elements;
}

//create a set of breadcrumbs for navigation
function create_breadcrumbs($links) {
	$breadcrumbs_wrapper = new PageElement('breadcrumbs.html');
	$breadcrumbs = new MultiPageElement();
	foreach ($links as $url => $caption) {
		$breadcrumb = new PageElement('breadcrumbs_item.html');
		$breadcrumb->bind('url', $url);
		$breadcrumb->bind('text', $caption);
		$breadcrumbs->addElement($breadcrumb);
	}
	$breadcrumbs_wrapper->bind('breadcrumbs', $breadcrumbs->render());
	return $breadcrumbs_wrapper->render();
}

$global_page_params['above_page_text'] = '';
$global_page_params['breadcrumbs'] = '';
