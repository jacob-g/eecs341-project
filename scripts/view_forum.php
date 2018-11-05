<?php
$page_params['page_title'] = '(forum name) - &Eacute;amonBB Forums';

$topics = array('Topic A', 'Topic B', 'Topic C');

$topic_rows = new MultiPageElement();
foreach ($topics as $subject) {
	$topic_row = new PageElement('topic_row.html');
	$topic_row->bind('subject', $subject);
	$topic_rows->addElement($topic_row);
}
$page_params['topic_rows'] = $topic_rows->render();