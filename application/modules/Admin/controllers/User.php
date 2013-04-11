<?php
class UserController extends Controller{

	function profile(){
		
	}

	function password(){
		$old_pass = $this->request->data['old'];
		$new_pass = $this->request->data['new'];
		$rnew_pass = $this->request->data['rnew'];
		if($rnew_pass == $new_pass){
			$this->modifyPass($uid,$old_pass,$new_pass);
		}
	}
}