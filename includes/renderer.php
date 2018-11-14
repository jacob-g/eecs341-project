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

//generate pagination links
function create_pagination($cur_page, $max_page) {
	if ($max_page == 1) {
		return '';
	} else {
		//generate the list of numbers we're going to show
		$page_numbers = array(1);
		for ($offset = -2; $offset <= 2; $offset++) {
			if ($cur_page + $offset > 1 && $cur_page + $offset < $max_page) {
				$page_numbers[] = $cur_page + $offset;
			}
		}
		$page_numbers[] = $max_page;
		
		$pagination = new PageElement('pagination.html');
		
		$page_links = new MultiPageElement();
		
		//add a link to the previous page
		$previous_page_link = new PageElement('pagination_item.html');
		$previous_page_link->bind('link_text', 'Previous');
		$previous_page_link->bind('active', '');
		$previous_page_link->bind('disabled', $cur_page == 1 ? 'disabled' : '');
		$previous_page_link->bind('page_number', $cur_page == 1 ? 1 : $cur_page - 1);
		$page_links->addElement($previous_page_link);
		
		//add a link to all the numbered pages
		$last_page_number = 0;
		foreach ($page_numbers as $page) {
			if ($page - $last_page_number > 1) { //show an ellipsis if there's a gap between the last two shown numbers
				$page_link = new PageElement('pagination_ellipsis.html');
				$page_links->addElement($page_link);
			}
			//generate a pagination link
			$page_link = new PageElement('pagination_item.html');
			$page_link->bind('page_number', $page);
			$page_link->bind('link_text', $page);
			$page_link->bind('active', $page == $cur_page ? 'active' : '');
			$page_link->bind('disabled', '');
			$page_links->addElement($page_link);
			$last_page_number = $page;
		}
		
		//add a link to the next page
		$previous_page_link = new PageElement('pagination_item.html');
		$previous_page_link->bind('link_text', 'Next');
		$previous_page_link->bind('active', '');
		$previous_page_link->bind('disabled', $cur_page == $max_page ? 'disabled' : '');
		$previous_page_link->bind('page_number', $cur_page == $max_page ? $max_page : $cur_page + 1);
		$page_links->addElement($previous_page_link);
		
		$pagination->bind('page_links', $page_links->render());
		return $pagination->render();
	}
}

//set special page parameters to blank by default
$global_page_params['above_page_text'] = '';
$global_page_params['breadcrumbs'] = '';
$global_page_params['pagination'] = '';
