<?php
function paste_addQueries() {
	return array(
		'getPasteById' => '
			SELECT * FROM !prefix!pastes
			WHERE id = :id
			LIMIT 1
		',
		'incrementPasteViews' => '
			UPDATE !prefix!pastes
			SET views=views+1
			WHERE id = :id
			LIMIT 1
		',
		'saveNewPaste' => '
			INSERT INTO !prefix!pastes
			       ( name, paste, private, type, password, domain)
			VALUES (:name,:paste,:private,:type,:password,:domain)
		',
		'getLatest20PastesByDomain' => '
			SELECT name, id, created FROM !prefix!pastes
			WHERE private != 1
			  AND domain  =  :domain
			ORDER BY created DESC
			LIMIT 20
		',
	);
}