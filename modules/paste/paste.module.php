<?php
function paste_buildContent($data,$db) {
	if(!empty($data->action[1])){
		// looks like we're getting a specific paste, woo
		$realId = @base_convert(strtolower($data->action[1]),36,10); // convert from packed base36 ID to numeric ID
		if(!$realId){ // couldn't convert the number, must mean that it's not something we generated originally
			$data->output['error']=$data->phrases['paste']['invalidId'];
			return;
		}
		$statement=$db->prepare('getPasteById','paste'); // defined in queries/paste.mysql.php
		$statement->execute(array(
			':id' => $realId, // dynamic parameter substitution
		));
		$paste=$statement->fetch(PDO::FETCH_ASSOC);
		if(!$paste){
			$data->output['pageTitle']=$data->phrases['paste']['error'];
			$data->output['error']=$data->phrases['paste']['pasteNotFound'];
			$data->httpHeaders[]='HTTP/1.1 404 Not Found';
			$data->httpHeaders[]='Status: 404 Not Found';
			return;
		}
		if($paste['private']){ // it's a private paste, safe to assume they don't want it being grabbed by SE traffic
			$data->metaList[]=array(
				'name' => 'robots',
				'content'=> 'noindex',
			);
			$data->httpHeaders[]='X-Robots-Tag: noindex';
		}
		$data->output['pageTitle']='Paste #'.base_convert($data->action[1],36,10).' - '.$paste['name'];
		switch($data->action[2]){
			case 'download':
				header('Content-Disposition: attachment; filename="paste-'.strtolower($data->action[1]).'.txt";');
			case 'raw':
				header('Content-Type: text/plain');
				header('Content-Length: '.mb_strlen($paste['paste']));
				echo $paste['paste'];
				die();
				break;
			default:
				common_loadPlugin($data,$paste['type'].'-transform');
				if(!empty($data->plugins[$paste['type'].'-transform'])){
					$data->output['pasteHighlighted']=$data->plugins[$paste['type'].'-transform']->transform($paste['paste']);
				}else{
					$data->output['pasteHighlighted']=nl2br(htmlentities($paste['paste']));
				}
				break;
		}
	}else{
		$data->output['pageTitle']=$data->phrases['paste']['newPaste'];
		common_include('libraries/forms.php');
		$data->output['pasteForm']=new formHandler('paste',$data);
		ksort($data->plugins);
		foreach($data->plugins as $key=>$plugin){
			if(substr($key,-10,10)==='-transform'){
				$data->output['pasteForm']->fields['type'][]=array(
					'text'=>$data->plugins[$paste['type'].'-transform']->name,
					'value'=>str_replace('-transform','',$key),
				);
			}
		}
		if (isset($_POST['fromForm']) && $_POST['fromForm']==$data->output['pasteForm']->fromForm) {
			$data->output['pasteForm']->populateFromPostData();
			if ($data->output['pasteForm']->validateFromPost()) {
				$statement=$db->prepare('saveNewPaste','paste');
				$data->output['pasteForm']->sendArray[':name']=htmlentities($data->output['pasteForm']->sendArray[':name']);
				$statement->execute($data->output['pasteForm']->sendArray);
				common_redirect_local($data,'paste/'.base_convert($db->lastInsertId(),10,36));
			}
		}
	}
}
function paste_content($data) {
	theme_contentBoxHeader($data->output['pageTitle']);
	if(!empty($data->output['error'])){ // uh oh, a bad thing happened
		echo $data->output['error'];
	}elseif(!empty($data->output['pasteHighlighted'])){
		echo $data->output['pasteHighlighted'];
	}else{
		$data->output['pasteForm']->build();
	}
	theme_contentBoxFooter();
}