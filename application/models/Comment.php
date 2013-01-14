<?php
class CommentModel extends Model{
	protected static $_adapter = 'MySql';
	protected static $_meta = array(
		'connection' => 'default',
		'source' => 'ptv_video_comments',
		'key' => 'id'
	);

	function add($data){
		$data['add_time'] = time();
		return $this->insert($data);
	}

	function get($vid,$id = 0){
		$where = array('fid' => $vid);
		if($id) $where["id"] = array('lt' => $id);
		$comment = $this->find('all',array(
			'where' => $where,
			'fields' => array('id','fid','uid','author','content','add_time','reply'),
			'order' => array('add_time' => 'DESC'),
			'limit' => 10
		));
		$comments = $comment?$comment->data():array();
		return $comments;
	}
}