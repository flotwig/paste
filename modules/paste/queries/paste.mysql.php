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
			       ( name, paste, private, type, password)
			VALUES (:name,:paste,:private,:type,:password)
		',
	);
}