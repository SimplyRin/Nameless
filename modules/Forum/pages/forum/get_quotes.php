<?php 
/*
 *	Made by Samerton
 *  https://github.com/NamelessMC/Nameless/
 *  NamelessMC version 2.0.0-pr2
 *
 *  License: MIT
 *
 *  Get a list of quotes
 */
 
if(!$user->isLoggedIn()){
	die(json_encode(array('error' => 'Not logged in')));
}
 
require_once('modules/Forum/classes/Forum.php');
 
// Always define page name
define('PAGE', 'forum');

// Initialise
$forum = new Forum();

// Get the post data
if(!isset($_POST) || empty($_POST)){
	die(json_encode(array('error' => 'No post data')));
}

// Markdown?
$cache->setCache('post_formatting');
$formatting = $cache->retrieve('formatting');

if($formatting == 'markdown'){
	// Markdown
	require(ROOT_PATH . '/core/includes/markdown/tomarkdown/autoload.php');
	$converter = new League\HTMLToMarkdown\HtmlConverter(array('strip_tags' => true));
}

$posts = array();

foreach($_POST['posts'] as $item){
	$post = $forum->getIndividualPost($item);
	
	$content = htmlspecialchars_decode($post['content']);
	$content = preg_replace("~<blockquote(.*?)>(.*)</blockquote>~si", "", $content);
	
	if($formatting == 'markdown'){
		$content = $converter->convert($content);
	}
	
	if($post['topic_id'] == $_POST['topic']){
		$posts[] = array(
			'content' => Output::getPurified($content),
			'author_username' => $user->idToName($post['creator']),
			'author_nickname' => $user->idToNickname($post['creator']),
			'link' => URL::build('/forum/topic/' . $post['topic_id'], 'pid=' . htmlspecialchars($item))
		);
	}
}


die(json_encode($posts));