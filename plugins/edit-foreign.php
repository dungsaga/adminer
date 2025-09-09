<?php

/** Select foreign key in edit form
* @link https://www.adminer.org/plugins/#use
* @author Jakub Vrana, https://www.vrana.cz/
* @license https://www.apache.org/licenses/LICENSE-2.0 Apache License, Version 2.0
* @license https://www.gnu.org/licenses/gpl-2.0.html GNU General Public License, version 2 (one or other)
*/
class AdminerEditForeign extends Adminer\Plugin {
	protected $limit;

	function __construct($limit = 0) {
		$this->limit = $limit;
	}

	function editInput($table, $field, $attrs, $value) {
		static $foreignTables = array();
		static $values = array();
		$foreignKeys = &$foreignTables[$table];
		if ($foreignKeys === null) {
			$foreignKeys = Adminer\column_foreign_keys($table);
		}
		foreach ((array) $foreignKeys[$field["field"]] as $foreignKey) {
			if (count($foreignKey["source"]) == 1) {
				$target = $foreignKey["table"];
				$id = $foreignKey["target"][0];
				$options = &$values[$target][$id];
				if (!$options) {
					$column = Adminer\idf_escape($id);
					if (preg_match('~binary~', $field["type"])) {
						$column = "HEX($column)";
					}
					$options = array("" => "") + Adminer\get_vals("SELECT $column FROM " . Adminer\table($target) . " ORDER BY 1" . ($this->limit ? " LIMIT " . ($this->limit + 1) : ""));
					if ($this->limit && count($options) - 1 > $this->limit) {
						return;
					}
				}
				$sid = $field["field"];
				$link = "<a target=_blank href='?server=".$_GET['server']."&username=".$_GET['username']."&db=".$_GET['db']."&edit=$target&where%5B$id%5D=$value'>$value</a>";
				// when user choose a value, update the link to the referenced row in the foreign table
				$script = Adminer\script("document.getElementById('$sid').addEventListener('change', function(e){var a=e.target.nextSibling,v=e.target.value;a.innerText=v;a.href=a.href.replace(/=[^=]+$/,'=')+v})");
				return "<select$attrs id='$sid'>" . Adminer\optionlist($options, $value) . "</select>$link$script";
			}
		}
	}

	protected $translations = array(
		'cs' => array('' => 'Výběr cizího klíče v editačním formuláři'),
		'de' => array('' => 'Wählen Sie im Bearbeitungsformular den Fremdschlüssel aus'),
		'pl' => array('' => 'Wybierz klucz obcy w formularzu edycji'),
		'ro' => array('' => 'Selectați cheia străină în formularul de editare'),
		'ja' => array('' => '外部キーを編集フォームで選択'),
	);
}
