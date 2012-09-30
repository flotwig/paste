<?php
$this->action=$data->linkRoot.'paste';
$this->formPrefix='paste_';
$this->submitTitle=$data->phrases['paste']['savePaste'];
$this->fromForm='pasteForm';
$this->fields=array(
	'name' => array(
		'label' => 'Paste Name',
		'required' => false,
		'tag' => 'input',
		'params' => array(
			'type' => 'text',
			'size' => 255
		),
	),
	'paste' => array(
		'label' => 'Paste Contents',
		'required' => true,
		'value' => '',
		'tag' => 'textarea',
		'params' => array(
			'cols' => 100,
			'rows' => 80,
			'style' => 'width:100%;height:500px;',
		)
	),
	'private' => array(
		'label' => 'Private paste?',
		'params' => array(
			'type' => 'checkbox',
		),
		'value' => '',
	),
	'type' => array(
		'label' => 'Type',
		'value' => '',
		'required' => true,
		'tag' => 'select',
		'options' => array(
			array(
				'text' => 'Plain Text',
				'value'=> 'plain',
			),
			// the rest are dynamically added
		),
	),
	'password' => array(
		'value' => '',
		'params' => array(
			'type' => 'hidden'
		)
	),

);