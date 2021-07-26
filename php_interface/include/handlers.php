<?php

require __DIR__ . "/lang/" . LANGUAGE_ID . "/handlers.php";

/*
 * обработчик для класса MyCurledType
 * - собственный тип пользовательского поля
 * "Привязка к элементам инф. блоков с сортировкой"
 */


class MyCurledType extends CUserTypeIBlockElement
{
	// инициализация пользовательского свойства для главного модуля
	public function GetUserTypeDescription()
	{
		return array(
			"USER_TYPE_ID" => "c_local",
			"CLASS_NAME" => "MyCurledType",
			"DESCRIPTION" => GetMessage("USER_TYPE_IBEL_SORT_DESCRIPTION"),
			"BASE_TYPE" => "int",
		);
	}

}
