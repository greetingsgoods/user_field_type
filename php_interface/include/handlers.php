<?php

use \Bitrix\Main\EventManager;
use Bitrix\Main\Loader;

require __DIR__ . "/lang/" . LANGUAGE_ID . "/handlers.php";

/*
 * обработчик для класса MyCurledType
 * - собственный тип пользовательского поля
 * "Привязка к элементам инф. блоков с сортировкой"
 */

$eventManager = EventManager::getInstance();
AddEventHandler("main", "OnUserTypeBuildList", array("MyCurledType", "GetUserTypeDescription"));

class MyCurledType extends CUserTypeIBlockElement
{
	// инициализация пользовательского свойства для главного модуля
	public function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => "c_local",
			"CLASS_NAME" => "MyCurledType",
			"DESCRIPTION" => GetMessage("USER_TYPE_LOCAL_SORT_DESCRIPTION"),
			"BASE_TYPE" => "int",
		);
	}

	/*	здесь добавляем новое свойство поля - поле элемента инфоблока для сортировки
	назовем его IBLOCK_SORT*/
	function GetSettingsHTML($arUserField = false, $arHtmlControl, $bVarsFromForm)
	{
		$result = '';

		if ($bVarsFromForm)
			$iblock_id = $GLOBALS[$arHtmlControl["NAME"]]["IBLOCK_ID"];
		elseif (is_array($arUserField))
			$iblock_id = $arUserField["SETTINGS"]["IBLOCK_ID"];
		else
			$iblock_id = "";

		if ($bVarsFromForm)
			$iblock_sort = $GLOBALS[$arHtmlControl["NAME"]]["IBLOCK_SORT"];
		elseif (is_array($arUserField))
			$iblock_sort = $arUserField["SETTINGS"]["IBLOCK_SORT"];
		else
			$iblock_sort = "";

		if (Loader::includeModule('iblock')) {
			$result .= '
			<tr>
				<td>' . GetMessage("USER_TYPE_LOCAL_DISPLAY") . ':</td>
				<td>
					' .
				GetIBlockDropDownList($iblock_id, $arHtmlControl["NAME"] . '[IBLOCK_TYPE_ID]', $arHtmlControl["NAME"] . '[IBLOCK_ID]', false, 'class="adm-detail-iblock-types"', 'class="adm-detail-iblock-list"')
				. '
				</td>
			</tr>
			';
		} else {
			$result .= '
			<tr>
				<td>' . GetMessage("USER_TYPE_LOCAL_DISPLAY") . ':</td>
				<td>
					<input type="text" size="6" name="' . $arHtmlControl["NAME"] . '[IBLOCK_ID]" value="' . htmlspecialcharsbx($value) . '">
				</td>
			</tr>
			';
		}
		// начало добавленного в эту функцию кода
		// добавлен один select для выбора поля сортировки

		$result .= '
			<tr>
				<td>' . GetMessage("USER_TYPE_LOCAL_SORT_DISPLAY") . ':</td>
				<td>';
		$result .= '<select  class="' . $iblock_sort . ' ' . $iblock_id . '" name="' . $arHtmlControl["NAME"] . '[IBLOCK_SORT]" class="adm-detail-iblock-sort" id="' . $arHtmlControl["NAME"] . '[IBLOCK_SORT]">' . "\n";
		$result .= '<option value="0">' . GetMessage("USER_TYPE_LOCAL_SORT_ANY") . '</option>' . "\n";
		$result .= '<option value="ID"' . ($iblock_sort == "ID" ? ' selected' : '') . '>' . GetMessage("USER_TYPE_LOCAL_SORT_BY_ID") . '</option>' . "\n";
		$result .= '<option value="NAME"' . ($iblock_sort == "NAME" ? ' selected' : '') . '>' . GetMessage("USER_TYPE_LOCAL_SORT_BY_NAME") . '</option>' . "\n";
		$result .= '<option value="SORT"' . ($iblock_sort == "SORT" ? ' selected' : '') . '>' . GetMessage("USER_TYPE_LOCAL_SORT_BY_SORT") . '</option>' . "\n";
		$result .= "</select></td>
				</tr>\n";
		// конец добавленного кода

		if ($bVarsFromForm)
			$ACTIVE_FILTER = $GLOBALS[$arHtmlControl["NAME"]]["ACTIVE_FILTER"] === "Y" ? "Y" : "N";
		elseif (is_array($arUserField))
			$ACTIVE_FILTER = $arUserField["SETTINGS"]["ACTIVE_FILTER"] === "Y" ? "Y" : "N";
		else
			$ACTIVE_FILTER = "N";

		if ($bVarsFromForm)
			$value = $GLOBALS[$arHtmlControl["NAME"]]["DEFAULT_VALUE"];
		elseif (is_array($arUserField))
			$value = $arUserField["SETTINGS"]["DEFAULT_VALUE"];
		else
			$value = "";

		if (
			$iblock_id > 0
			&& Loader::includeModule('iblock')
		) {
			$result .= '
			<tr>
				<td>' . GetMessage("USER_TYPE_LOCAL_DEFAULT_VALUE") . ':</td>
				<td>
					<select name="' . $arHtmlControl["NAME"] . '[DEFAULT_VALUE]" size="5">
						<option value="">' . GetMessage("IBLOCK_VALUE_ANY") . '</option>
			';

			$arFilter = array("IBLOCK_ID" => $iblock_id);
			if ($ACTIVE_FILTER === "Y")
				$arFilter["ACTIVE"] = "Y";

			// здесь изменен вызов getlist, задана сортировка по добавленному выше полю сортировки
			// по умолчанию сортировка по id
			$arSort = array("ID" => "ASC");
			if ($iblock_sort == "NAME") $arSort = array("NAME" => "ASC");
			if ($iblock_sort == "SORT") $arSort = array("SORT" => "ASC");
			$rs = CIBlockElement::GetList(
			// было так:
			//array("NAME" => "ASC", "ID" => "ASC"),
				$arSort,
				$arFilter,
				false,
				false,
				array("ID", "NAME")
			);
			while ($ar = $rs->GetNext())
				$result .= '<option value="' . $ar["ID"] . '"' . ($ar["ID"] == $value ? " selected" : "") . '>' . $ar["NAME"] . '</option>';

			$result .= '</select>';
		} else {
			$result .= '
			<tr>
				<td>' . GetMessage("USER_TYPE_LOCAL_DEFAULT_VALUE") . ':</td>
				<td>
					<input type="text" size="8" name="' . $arHtmlControl["NAME"] . '[DEFAULT_VALUE]" value="' . htmlspecialcharsbx($value) . '">
				</td>
			</tr>
			';
		}

		if ($bVarsFromForm)
			$value = $GLOBALS[$arHtmlControl["NAME"]]["DISPLAY"];
		elseif (is_array($arUserField))
			$value = $arUserField["SETTINGS"]["DISPLAY"];
		else
			$value = "LIST";
		$result .= '
		<tr>
			<td class="adm-detail-valign-top">' . GetMessage("USER_TYPE_ENUM_DISPLAY") . ':</td>
			<td>
				<label><input type="radio" name="' . $arHtmlControl["NAME"] . '[DISPLAY]" value="LIST" ' . ("LIST" == $value ? 'checked="checked"' : '') . '>' . GetMessage("USER_TYPE_LOCAL_LIST") . '</label><br>
				<label><input type="radio" name="' . $arHtmlControl["NAME"] . '[DISPLAY]" value="CHECKBOX" ' . ("CHECKBOX" == $value ? 'checked="checked"' : '') . '>' . GetMessage("USER_TYPE_LOCAL_CHECKBOX") . '</label><br>
			</td>
		</tr>
		';

		if ($bVarsFromForm)
			$value = (int)$GLOBALS[$arHtmlControl["NAME"]]["LIST_HEIGHT"];
		elseif (is_array($arUserField))
			$value = (int)$arUserField["SETTINGS"]["LIST_HEIGHT"];
		else
			$value = 5;
		$result .= '
		<tr>
			<td>' . GetMessage("USER_TYPE_LOCAL_LIST_HEIGHT") . ':</td>
			<td>
				<input type="text" name="' . $arHtmlControl["NAME"] . '[LIST_HEIGHT]" size="10" value="' . $value . '">
			</td>
		</tr>
		';

		$result .= '
		<tr>
			<td>' . GetMessage("USER_TYPE_LOCAL_ACTIVE_FILTER") . ':</td>
			<td>
				<input type="checkbox" name="' . $arHtmlControl["NAME"] . '[ACTIVE_FILTER]" value="Y" ' . ($ACTIVE_FILTER == "Y" ? 'checked="checked"' : '') . '>
			</td>
		</tr>
		';

		return $result;
	}


}
