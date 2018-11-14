<?php
//route a page with a given content file and script file
class RoutedPage {
	private $base_template; //the text of the base template
	private $raw_text; //the text of the page contents itself
	private $script_file; //the file that will be run to actually render the page
	
	function __construct($template_file, $content_file, $script_file) {
		$this->base_template = file_get_contents(SRV_ROOT . '/templates/' . $template_file);
		$this->raw_text = file_get_contents(SRV_ROOT . '/templates/' . $content_file);
		$this->script_file = $script_file;
	}
	
	//render the page by placing the page contents inside the template and then running the script and replacing the appropriate parameters with their values
	//you can pass in $url_params, the parameters specified in the URL definition
	function render($url_params = array()) {
		global $global_page_params, $user_info, $logged_in, $mysqli;
		$page_params = array();
		$out_text = str_replace('<$(page_contents)/>', $this->raw_text, $this->base_template);
		$page_params = array_merge($page_params, $global_page_params);
		include SRV_ROOT . '/scripts/' . $this->script_file;
		if (isset($breadcrumbs)) {
			$page_params['breadcrumbs'] = create_breadcrumbs($breadcrumbs);
		}
		if (isset($pagination)) {
			$page_params['pagination'] = create_pagination($cur_page, $max_page);
		}
		foreach ($page_params as $key => $val) {
			$out_text = str_replace('<$(' . $key . ')/>', $val, $out_text);
		}
		
		return $out_text;
	}
}

//route a page and return its contents
function route_page($path) {
	global $page_routes;
	
	$base_path = strtok($path, '?');
	$path_parts = explode('/', $base_path);
	
	foreach ($page_routes as $router_path => $router_attributes) {
		$good = true;
		$router_path_parts = explode('/', $router_path);
		$url_params = array();
		if (sizeof($router_path_parts) == sizeof($path_parts)) {
			foreach ($router_path_parts as $index => $router_path_part) {
				if (strpos($router_path_part, '$') === 0) {
					$url_params[substr($router_path_part, 1)] = $path_parts[$index];
				} else if ($router_path_part == $path_parts[$index]) {
				} else {
					$good = false;
				}
			}
			
			if ($good) {
				$page_attributes = $router_attributes;
				break;
			}
		}
	}
	
	if (isset($page_attributes)) {
		$page = new RoutedPage($page_attributes['template'], $page_attributes['content'], $page_attributes['behavior']);
		return $page->render($url_params);
	} else {
		//404 error
		header('HTTP/1.1 404 Not Found');
		$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
		echo $page->render();
	}
}

$global_page_params = array(); //the variable that will store all global page parameters