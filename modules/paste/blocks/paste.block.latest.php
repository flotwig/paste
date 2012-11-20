<?php
function latest_buildContent($data,$db,$attributes){
	$statement=$db->prepare('getLatest20PastesByDomain','paste');
	$statement->execute(array(
		':domain' => $_SERVER['HTTP_HOST']
	));
	$data->output['latestPastes']=$statement->fetchAll(PDO::FETCH_ASSOC);
}
function latest_content($data,$attributes){
    echo '<ul class="latestPastes">';
    foreach ($data->output['latestPastes'] as $paste) {
        echo '<li>
			<a href="',$data->linkRoot.'paste/',base_convert($paste['id'],10,36),'">',(empty($paste['name'])?'Untitled':$paste['name']),'</a>
			<span class="addedAgo">',common_timeDiff(time(),strtotime($paste['created'],time())),'</span>
		</li>';
    }
    echo '</ul>';
}