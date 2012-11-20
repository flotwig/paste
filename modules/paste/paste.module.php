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
		$statement=$db->prepare('incrementPasteViews','paste');
		$statement->execute(array(
			':id' => $paste['id']
		));
		$data->output['pageTitle']='Paste #'.base_convert($data->action[1],36,10).' - '.$paste['name'];
		switch($data->action[2]){
			case 'download': // they want to get their file and force-download it
				header('Content-Disposition: attachment; filename="paste-'.strtolower($data->action[1]).'.txt";');
			case 'raw': // they just want to view the file as plain text in the browser
				header('Content-Type: text/plain');
				header('Content-Length: '.mb_strlen($paste['paste']));
				echo $paste['paste'];
				die();
				break;
			default: // they don't want to do anything special, just display the paste
				common_include('modules/paste/libraries/geshi.php');
				$geshi=new GeSHi($paste['paste'],$paste['type']); // loading GeSHi to highlight the code
				if(TRUE){
					$data->output['pasteHighlighted']=$geshi->parse_code();
				}else{
					$data->output['pasteHighlighted']=nl2br(htmlentities($paste['paste']));
				}
				break;
		}
	}else{
		common_include('modules/paste/libraries/geshi.php');
		$geshi=new GeSHi(); // we need GeSHi here so we can get a list of highlightable languages
		$data->output['pageTitle']=$data->phrases['paste']['newPaste'];
		common_include('libraries/forms.php');
		$data->output['pasteForm']=new formHandler('paste',$data);
		$languages=$geshi->get_supported_languages(TRUE);
		foreach($languages as $system=>$pretty){ // dynamically fill out the list of supported languages
			$data->output['pasteForm']->fields['type']['options'][]=array(
				'text'  => $pretty,
				'value' => $system,
			);
		}
		if (isset($_POST['fromForm']) && $_POST['fromForm']==$data->output['pasteForm']->fromForm) {
			$data->output['pasteForm']->populateFromPostData();
			if ($data->output['pasteForm']->validateFromPost()) { // everything's good, let's save to the database
				$statement=$db->prepare('saveNewPaste','paste');
				$data->output['pasteForm']->sendArray[':name']=htmlentities($data->output['pasteForm']->sendArray[':name']);
				$data->output['pasteForm']->sendArray[':private']=FALSE;
				$data->output['pasteForm']->sendArray[':domain']=$data->hostname;
				if($statement->execute($data->output['pasteForm']->sendArray)){
					common_redirect_local($data,'paste/'.base_convert($db->lastInsertId(),10,36)); // send user to their pretty URL
				}else{
					var_dump($statement->errorInfo());die();
				}
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
		$data->output['pasteForm']->build(); // generate the form for new pastes
	}
	theme_contentBoxFooter();
}