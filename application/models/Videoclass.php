<?php
class VideoclassModel extends Model{
	protected static $_adapter = 'MySql';
	protected static $_meta = array(
		'connection' => 'default',
		'source' => 'ptv_video_class',
		'key' => 'cid'
	);

	function add($data){
		$cid = $this->insert($data);
		if($cid){
			$update = $this->update(array('ifchild' => 0),array('cid' => $data['parentid']));
			$insert = $this->table('ptv_video_sum')->insert(array('type' => $cid));
			if($update) return true;
		}
		return false;
	}

	function del($id){
		$del = $this->delete(array('cid' => $id));
		if($del){
			$delCountInfo = $this->table('ptv_video_sum')->delete(array('type' => $id));
			if($delCountInfo) return true;
		}
		return false;
	}

	function getParentClass($cid){
		$class = $this->find('first',array(
			'where' => array('cid' => $cid),
			'fields' => array('parentid')
		));
		return $class?$class->data('parentid'):false;
	}
}