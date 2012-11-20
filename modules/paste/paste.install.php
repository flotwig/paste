<?php
function paste_settings() {
    return array(
        'name'      => 'paste',
        'shortName' => 'paste',
        'version'   => '0.1'
    );
}
function paste_install($db,$drop=false) {
	$structures = array(
		'pastes' => array(
			'id'                   => SQR_IDKey,
			'name'                 => SQR_title,
			'paste'                => 'MEDIUMTEXT DEFAULT "" NOT NULL',
			'views'                => SQR_ID.' DEFAULT 0',
			'private'              => SQR_boolean,
			'type'                 => 'VARCHAR(15) NOT NULL',
			'password'             => 'VARCHAR(127) DEFAULT "" NULL',
			'created'              => SQR_added,
			'domain'               => SQR_title,
		),
	);
	if ($drop)
		paste_uninstall($db);

	$db->createTable('pastes', $structures['pastes']);
}
function paste_uninstall($db){
	$db->dropTable('pastes');
}