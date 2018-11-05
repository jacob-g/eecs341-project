<?php
$page_params['page_title'] = '&Eacute;amonBB Forum Home';

//select all forums and their associated category names that the user is able to read
//TODO: ONLY select forums that the current user is able to read
$statement = query('SELECT c.ID AS category_id,c.name AS category_name,f.name AS forum_name,f.ID AS forum_id,COUNT(DISTINCT t.ID),COUNT(p.id) FROM (forum AS f LEFT JOIN category AS c ON c.ID=f.category_ID LEFT JOIN topic AS t ON t.forum_ID=f.ID LEFT JOIN post AS p ON p.topic_ID=t.ID) GROUP BY f.ID');
$statement->bind_result($category_id,$category_name, $forum_name, $forum_id, $num_topics, $num_posts);

$category_rows = new MultiPageElement();
$last_category_id = -1;
$last_category_row = null;
$last_forum_rows = null;
while ($statement->fetch()) {
	if ($last_category_id != $category_id) {
		if ($last_category_id != -1) {
			$last_category_row->bind('forum_rows', $last_forum_rows->render());
		}
		$last_category_id = $category_id;
		
		$last_category_row = new PageElement('index_category.html');
		$last_category_row->bind('category_name', $category_name);
		$category_rows->addElement($last_category_row);
		$last_forum_rows = new MultiPageElement();
	}
	
	$forum_row = new PageElement('index_forum_row.html');
	$forum_row->bind('forum_name', htmlspecialchars($forum_name));
	$forum_row->bind('forum_id', $forum_id);
	$forum_row->bind('num_topics', $num_topics);
	$forum_row->bind('num_posts', $num_posts);
	$last_forum_rows->addElement($forum_row);
}
if ($last_category_id != -1) {
	$last_category_row->bind('forum_rows', $last_forum_rows->render());
}
$statement->close();
$page_params['category_tables'] = $category_rows->render();