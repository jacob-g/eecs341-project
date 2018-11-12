<?php
$page_params['page_title'] = 'Manage Forums';

//make sure that the user has permission to access this
if (!$user_info['permissions']['access_admin_panel']) {
	$page = new RoutedPage('base_template.html', 'error404.html', 'error404.php');
	echo $page->render();
	die;
}

if (isset($_POST['add_category'])) {
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
	$statement = query('SELECT MAX(sort_order)+1 FROM category');
	$statement->bind_result($new_sort_order);
	$statement->fetch();
	$statement->close();
	query('INSERT INTO category(name,sort_order) VALUES(?,?)', 'si', array($_POST['new_category_name'], $new_sort_order))->close();
	$mysqli->commit();
}

if (isset($_POST['add_forum'])) {
	$new_category_id = intval($_POST['category_to_add_to']);
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
	$statement = query('SELECT 1 FROM category WHERE ID=?', 'i', array($new_category_id));
	$category_exists = $statement->fetch();
	$statement->close();
	if ($category_exists) {
		$statement = query('SELECT MAX(sort_order)+1 FROM forum WHERE category_ID=?', 'i', array($new_category_id));
		$statement->bind_result($new_max_sort_order);
		$statement->fetch();
		if (empty($new_max_sort_order)) {
			$new_max_sort_order = 1;
		}
		$statement->close();
		query('INSERT INTO forum(name,category_ID,sort_order) VALUES(?,?,?)', 'sii', array($_POST['new_forum_name'], $new_category_id, $new_max_sort_order))->close();
	}
	$mysqli->commit();
}

if (isset($_POST['form_sent'])) {
	$mysqli->begin_transaction(MYSQLI_TRANS_START_READ_WRITE);
	
	//get a list of existing category IDs (this is done again to be part of the transaction)
	$category_ids = array();
	$statement = query('SELECT ID FROM category');
	$statement->bind_result($category_id);
	while ($statement->fetch()) {
		$category_ids[] = $category_id;	
	}
	$statement->close();
			
	//for each category that exists, see if we sent in anything to update about it
	foreach ($category_ids as $category_id) {
		if (isset($_POST['categories']) &&
			isset($_POST['categories'][$category_id])) {
			query('UPDATE category SET name=?,sort_order=? WHERE ID=?', 'sii', array($_POST['categories'][$category_id]['name'], $_POST['categories'][$category_id]['sort_order'], $category_id));
		}
	}
	
	//get a list of existing forum IDs
	$forum_ids = array();
	$statement = query('SELECT ID FROM forum');
	$statement->bind_result($forum_id);
	while ($statement->fetch()) {
		$forum_ids[] = $forum_id;	
	}
	$statement->close();
			
	//for each category that exists, see if we sent in anything to update about it
	foreach ($forum_ids as $forum_id) {
		if (isset($_POST['forums']) &&
			isset($_POST['forums'][$forum_id])) {
			query('UPDATE forum SET name=?,description=?,sort_order=? WHERE ID=?', 'ssii', array($_POST['forums'][$forum_id]['name'], $_POST['forums'][$forum_id]['description'], $_POST['forums'][$forum_id]['sort_order'], $forum_id));
		}
	}
	$mysqli->commit();
}

//get a list of existing categories
$categories = array();
$statement = query('SELECT ID,name FROM category ORDER BY sort_order ASC');
$statement->bind_result($category_id, $name);
while ($statement->fetch()) {
	$categories[$category_id] = $name;
}
$statement->close();

$category_select_options = new MultiPageElement();
foreach ($categories as $category_id => $category_name) {
	$category_select_option = new PageElement('category_dropdown_item.html');
	$category_select_option->bind('category_id', $category_id);
	$category_select_option->bind('category_name', htmlspecialchars($category_name));
	$category_select_options->addElement($category_select_option);
}
$page_params['category_select_options'] = $category_select_options->render();

$category_tables = new MultiPageElement();
$last_category_id = -1;
$last_category_row = null;
$forum_rows = null;
//TODO: do some sort of outer join so that empty categories show up too
$statement = query('SELECT c.ID AS cid,c.name AS category_name,f.ID AS fid,f.name AS forum_name,f.description AS forum_description,c.sort_order AS cat_sort_order,f.sort_order AS f_sort_order FROM forum AS f LEFT JOIN category AS c ON c.ID=f.category_ID ORDER BY c.sort_order,f.sort_order ASC');
$statement->bind_result($category_id, $category_name, $forum_id, $forum_name, $forum_description, $category_sort_order, $forum_sort_order);
while ($statement->fetch()) {
	if ($last_category_id != $category_id) {
		if ($last_category_id != -1) {
			$last_category_row->bind('forum_rows', $forum_rows->render());
			$category_tables->addElement($last_category_row);
		}
		$last_category_id = $category_id;
		$last_category_row = new PageElement('manage_forums_category_table.html');
		$last_category_row->bind('category_name', htmlspecialchars($category_name));
		$last_category_row->bind('category_id', $category_id);
		$last_category_row->bind('sort_order', $category_sort_order);
		$forum_rows = new MultiPageElement();
	}
	$forum_row = new PageElement('manage_forums_forum_row.html');
	$forum_row->bind('forum_name', htmlspecialchars($forum_name));
	$forum_row->bind('forum_id', $forum_id);
	$forum_row->bind('sort_order', $forum_sort_order);
	$forum_row->bind('description', htmlspecialchars($forum_description));
	$forum_rows->addElement($forum_row);
}
$statement->close();

if ($last_category_id != -1) {
	$last_category_row->bind('forum_rows', $forum_rows->render());
	$category_tables->addElement($last_category_row);
}

$page_params['category_tables'] = $category_tables->render();