<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

// fields module

/**
 * returns array with associative arrays like that:
 *   array(
 *     'ID' => 1,
 *     'NAME' => 'Full name',
 *     'ACTIVE' => 'Y',
 *     'SORT' => 500,
 *     'CODE' => 'name',
 *     'DEFAULT_VALUE' => '', 
 *     'PROPERTY_TYPE' => 'S',
 *     'FIELD_TYPE' => 'TEXT', # possible: 'NUMBER', 'TEXTAREA', 'LIST' and 'UNKNOWN'
 *     'ROW_COUNT' => 1,
 *     'COL_COUNT' => 30,
 *     'IS_REQUIRED' => 'N'
 *   )
 */
function getFieldsList($arResult, $arParams, $emailTemplate=false) {
    if ($emailTemplate) {
        $arParams['IBLOCK_TYPE'] = $arParams['EMAIL_TEMPLATES_IBLOCK_TYPE'];
        $arParams['IBLOCK_CODE'] = $arParams['EMAIL_TEMPLATES_IBLOCK_CODE'];
    }

    $fieldsList = array();
    $res = CIBlock::GetList(
        array(),
        array(
            'TYPE' => $arParams['IBLOCK_TYPE'],
            'CODE' => $arParams['IBLOCK_CODE'],
        )
    );
    $arItem = $res->Fetch();
    $IBLOCK_ID = $arItem['ID'];

    $res = CIBlock::GetProperties(
        $arItem['ID'],
        array('SORT'=>'ASC'),
        array(
            'ACTIVE' => 'Y',
        )
    );
    while ($arItem = $res->Fetch()) {
        // excluding
        if (empty($arItem['CODE'])) continue;
        if (substr($arItem['CODE'], 0, 3) == 'nf_') continue;
        if ( ! in_array($arItem['PROPERTY_TYPE'],
               $arParams['SUPPORTED_FIELDS_TYPES'])) continue;

        // get list-type values
        if ($arItem['PROPERTY_TYPE'] == 'L') {
            $res2 = CIBlockProperty::GetPropertyEnum(
                $arItem['CODE'],
                array('SORT' => 'ASC'),
                array('IBLOCK_ID' => $IBLOCK_ID)
            );
            $listValues = array();
            while ($val = $res2->Fetch()) {
                $val['~VALUE'] = $val['VALUE'];
                $val['VALUE'] = htmlspecialcharsEx($val['~VALUE']);
                $listValues[] = $val;
            }
            $arItem['LIST_ENUM'] = $listValues;
        }

        // detect difference between input and textarea
        if ($arItem['PROPERTY_TYPE'] == 'S'
        && !empty($arItem['USER_TYPE'])) {
            $arItem['FIELD_TYPE'] = 'TEXTAREA';
        } elseif ($arItem['PROPERTY_TYPE'] == 'S') {
            $arItem['FIELD_TYPE'] = 'TEXT';

        } elseif ($arItem['PROPERTY_TYPE'] == 'N') {
            $arItem['FIELD_TYPE'] = 'NUMBER';

        } elseif ($arItem['PROPERTY_TYPE'] == 'L') {
            $arItem['FIELD_TYPE'] = 'LIST';

        } else {
            $arItem['FIELD_TYPE'] = 'UNKNOWN';
        }

        $fieldsList[] = $arItem;
    }

    return $fieldsList;
}

/**
 * returns true if form has fields or string of error code with some of this values:
 *   'IBLOCK_TYPE_NOT_FOUND', 'IBLOCK_CODE_NOT_FOUND', 'HAS_NO_FIELDS'
 */
function formHasFields($arResult, $arParams) {
    // check for has iblock type
    $res = CIBlockType::GetByID($arParams['IBLOCK_TYPE']);
    if ($res->nSelectedCount < 1) {
        return 'IBLOCK_TYPE_NOT_FOUND';
    }

    // check for has iblock
    $res = CIBlock::GetList(
        array(),
        array(
            'TYPE' => $arParams['IBLOCK_TYPE'],
            'CODE' => $arParams['IBLOCK_CODE'],
        )
    );
    if ( ! $res->Fetch()) {
        return 'IBLOCK_CODE_NOT_FOUND';
    }

    // check for has fields list
    $fieldsList = getFieldsList($arResult, $arParams);
    if (empty($fieldsList)) {
        return 'HAS_NO_FIELDS';
    }

    return true;
}

// for check to has template by code
function foundTemplate($type, $arResult, $arParams) {
    $res = CIBlockElement::GetList(
        array(),
        array(
            'IBLOCK_TYPE' => $arParams['EMAIL_TEMPLATES_IBLOCK_TYPE'],
            'IBLOCK_CODE' => $arParams['EMAIL_TEMPLATES_IBLOCK_CODE'],
            'CODE' => $arParams[$type.'_EMAIL_TEMPLATE'],
            'ACTIVE' => 'Y',
        )
    );

    // because CIBlockElement::GetList returns not strong results
    while ($arItem = $res->Fetch()) {
        if ($arItem['IBLOCK_TYPE_ID'] == $arParams['EMAIL_TEMPLATES_IBLOCK_TYPE']
        && $arItem['IBLOCK_CODE'] == $arParams['EMAIL_TEMPLATES_IBLOCK_CODE']
        && $arItem['CODE'] == $arParams[$type.'_EMAIL_TEMPLATE']) {
            return true;
        }
    }

    return false;
}

/**
 * returns true if email templates has required fields (properties)
 * or returns error code, possible error code values:
 *   'IBLOCK_TYPE_NOT_FOUND', 'IBLOCK_CODE_NOT_FOUND',
 *   array('IBLOCK_HAS_NO_RQ_FIELD', 'from'),
 *   array('IBLOCK_HAS_NO_RQ_FIELD', 'to'),
 *   array('IBLOCK_HAS_NO_RQ_FIELD', 'subject'),
 *   array('IBLOCK_HAS_NO_RQ_FIELD', 'body'),
 *   -- etc. required fields, see: $arParams['EMAIL_TEMPLATES_REQUIRED_FIELDS']
 *   'ADMIN_TEMPLATE_NOT_FOUND', 'USER_TEMPLATE_NOT_FOUND',
 */
function emailTemplatesHasRequiredFields($arResult, $arParams) {
    /* if we don't need to send any emails (empty fields of templates iblock
       or admin and user templates fields is empty) then returns true,
       just ignore checking for required fields of templates iblock */
    if ( ! needToSendEmail($arResult, $arParams, 'ANY')) {
        return true;
    }

    // check for has iblock type
    $res = CIBlockType::GetByID($arParams['EMAIL_TEMPLATES_IBLOCK_TYPE']);
    if ($res->nSelectedCount < 1) {
        return 'IBLOCK_TYPE_NOT_FOUND';
    }

    // check for has iblock
    $res = CIBlock::GetList(
        array(),
        array(
            'TYPE' => $arParams['EMAIL_TEMPLATES_IBLOCK_TYPE'],
            'CODE' => $arParams['EMAIL_TEMPLATES_IBLOCK_CODE'],
        )
    );
    if ( ! $res->Fetch()) {
        return 'IBLOCK_CODE_NOT_FOUND';
    }

    // check for iblock has required fields
    $fieldsList = getFieldsList($arResult, $arParams, true);
    $foundFields = array();
    foreach ($fieldsList as $arItem) {
        if ($arItem['PROPERTY_TYPE'] == 'S'
        && in_array($arItem['CODE'], $arParams['EMAIL_TEMPLATES_REQUIRED_FIELDS'])) {
            $foundFields[] = $arItem['CODE'];
        }
    }
    foreach ($arParams['EMAIL_TEMPLATES_REQUIRED_FIELDS'] as $item) {
        if ( ! in_array($item, $foundFields)) {
            return array('IBLOCK_HAS_NO_RQ_FIELD', $item);
        }
    }

    // check for exists admin template
    if ( ! empty($arParams['ADMIN_EMAIL_TEMPLATE'])
    && $arParams['ADMIN_EMAIL_TEMPLATE'] != '-'
    && ! foundTemplate('ADMIN', $arResult, $arParams)) {
        return 'ADMIN_TEMPLATE_NOT_FOUND';
    }

    // check for exists user template
    if ( ! empty($arParams['USER_EMAIL_TEMPLATE'])
    && $arParams['USER_EMAIL_TEMPLATE'] != '-'
    && ! foundTemplate('USER', $arResult, $arParams)) {
        return 'USER_TEMPLATE_NOT_FOUND';
    }

    return true;
}

function findCode($postDataList, $code) {
    foreach ($postDataList as $key=>$val) {
        if ($key == $code) {
            return $val;
        }
    }
    return false;
}

/**
 * Fill empty $arResult['POST_DATA'] for default values of fields
 */
function fillPostData($arResult, $arParams) {
    $postData = $arResult['POST_DATA'];

    foreach ($arResult['FIELDS_LIST'] as $arItem) {
        $find = findCode($arResult['POST_DATA'], $arItem['CODE']);
        if ($find !== false) {
            $postData[$arItem['CODE']] = $find;
            continue;
        }
       
        if ($arItem['FIELD_TYPE'] == 'TEXTAREA') {
            if (strtoupper($arItem['DEFAULT_VALUE']['TYPE']) == 'TEXT') {
                $postData[$arItem['CODE']] = $arItem['DEFAULT_VALUE']['TEXT'];
            } elseif (strtoupper($arItem['DEFAULT_VALUE']['TYPE']) == 'HTML') {
                $postData[$arItem['CODE']] = htmlspecialcharsEx($arItem['DEFAULT_VALUE']['TEXT']);
            } else {
                $postData[$arItem['CODE']] = '';
            }
        } elseif (in_array( $arItem['PROPERTY_TYPE'], array('S', 'N') )) {
            $postData[$arItem['CODE']] = $arItem['DEFAULT_VALUE'];
        } elseif ($arItem['PROPERTY_TYPE'] == 'L') {
            $postData[$arItem['CODE']] = '';
            foreach ($arItem['LIST_ENUM'] as $listItem) {
                if ($listItem['DEF'] == 'Y') {
                    $postData[$arItem['CODE']] = $listItem['XML_ID'];
                    break;
                }
            }
        }
    }

    return $postData;
}
