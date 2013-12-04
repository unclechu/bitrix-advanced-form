<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

// store result module

function storeResult($arResult, $arParams) {
    $iblock = CIBlock::GetList(
        array(),
        array(
            'TYPE' => $arParams['IBLOCK_TYPE'],
            'CODE' => $arParams['IBLOCK_CODE'],
        )
    );
    $iblock = $iblock->Fetch();
    
    $el = new CIBlockElement;

    $props = array();
    foreach ($arResult['FIELDS_LIST'] as $arField) {
        if ($arField['FIELD_TYPE'] == 'TEXT' || $arField['FIELD_TYPE'] == 'NUMBER') {
            $props[$arField['CODE']] = array(
                'VALUE' => $arResult['POST_DATA']['~'.$arField['CODE']],
            );
        } elseif ($arField['FIELD_TYPE'] == 'TEXTAREA') {
            $props[$arField['CODE']] = array(
                'VALUE' => array(
                    'TYPE' => 'text',
                    'TEXT' => $arResult['POST_DATA']['~'.$arField['CODE']],
                ),
            );
        } elseif ($arField['FIELD_TYPE'] == 'LIST') {
            $propEnum = CIBlockPropertyEnum::GetList(
                array(),
                array(
                    'XML_ID' => $arResult['POST_DATA']['~'.$arField['CODE']],
                    'CODE' => $arField['CODE'],
                    'IBLOCK_ID' => $iblock['ID'],
                )
            );
            $propEnum = $propEnum->Fetch();
            if ( ! $propEnum) return false;

            $props[$arField['CODE']] = array(
                'VALUE' => $propEnum['ID'],
            );
        }
    }

    $res = $el->Add(array(
        'IBLOCK_TYPE' => $arParams['IBLOCK_TYPE'],
        'IBLOCK_ID' => $iblock['ID'],
        'ACTIVE' => 'Y',
        'PROPERTY_VALUES' => $props,
        'ACTIVE_FROM' => ConvertTimeStamp(time()+CTimeZone::GetOffset(), 'FULL'),
        'NAME' => 'Form result', # !!! NEED TO REPLACE TO DEFAULT VALUE
    ));

    if (!$res) {
        return array(
            'MESSAGE' => $el->LAST_ERROR,
        );
    }

    return true;
}
