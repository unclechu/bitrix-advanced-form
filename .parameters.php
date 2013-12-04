<?
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
if (!CModule::IncludeModule('iblock')) return;

$arIBlockTypes = CIBlockParameters::GetIBlockTypes(array('-'=>''));

// default values

$defaultIBlockType = 'forms';
if (empty($arCurrentValues['EMAIL_TEMPLATES_IBLOCK_TYPE'])) {
    $arCurrentValues['EMAIL_TEMPLATES_IBLOCK_TYPE'] = $defaultIBlockType;
}
if (empty($arCurrentValues['IBLOCK_TYPE'])) {
    $arCurrentValues['IBLOCK_TYPE'] = $defaultIBlockType;
}
$defaultTemplatesIBlockCode = 'email_templates';
if (empty($arCurrentValues['EMAIL_TEMPLATES_IBLOCK_CODE'])) {
    $arCurrentValues['EMAIL_TEMPLATES_IBLOCK_CODE'] = $defaultTemplatesIBlockCode;
}

// templates iblock

$arTemplatesIBlockCodes = array('-'=>'');
$res = CIBlock::GetList(
    array('SORT' => 'ASC'),
    array(
        'ACTIVE' => 'Y',
        'TYPE' => ($arCurrentValues['EMAIL_TEMPLATES_IBLOCK_TYPE'] != '-'
            ? $arCurrentValues['EMAIL_TEMPLATES_IBLOCK_TYPE'] : ''),
    )
);
while ($arRes = $res->Fetch()) {
    if (empty($arRes['CODE'])) continue;
    $arTemplatesIBlockCodes[$arRes['CODE']] = $arRes['NAME'];
}

$arEmailTemplates = array('-'=>GetMessage('DONT_SEND_MAIL'));
$res = CIBlockElement::GetList(
    array(),
    array(
        'IBLOCK_TYPE' => $arCurrentValues['EMAIL_TEMPLATES_IBLOCK_TYPE'],
        'IBLOCK_CODE' => $arCurrentValues['EMAIL_TEMPLATES_IBLOCK_CODE'],
        'ACTIVE' => 'Y',
    ),
    false,
    false,
    array('ID', 'CODE', 'NAME')
);
while ($arRes = $res->Fetch()) {
    if (empty($arRes['CODE'])) continue;
    $arEmailTemplates[$arRes['CODE']] = $arRes['NAME'];
}

// results iblock

$arResultsIBlockCodes = array('-'=>'');
$res = CIBlock::GetList(
    array('SORT' => 'ASC'),
    array(
        'ACTIVE' => 'Y',
        'TYPE' => ($arCurrentValues['IBLOCK_TYPE'] != '-'
            ? $arCurrentValues['IBLOCK_TYPE'] : ''),
    )
);
while ($arRes = $res->Fetch()) {
    if (empty($arRes['CODE'])) continue;
    $arResultsIBlockCodes[$arRes['CODE']] = $arRes['NAME'];
}

// parameters list
$arComponentParameters = array(
    'GROUPS' => array(
        'IBLOCK' => array(
            'SORT' => 100,
            'NAME' => GetMessage('G_IBLOCK'),
        ),
        'EMAIL_NOTIF' => array(
            'SORT' => 110,
            'NAME' => GetMessage('G_EMAIL_NOTIF'),
        ),
        'ADDITIONAL_SETTINGS' => array(
            'SORT' => 120,
            'NAME' => GetMessage('G_ADDITIONAL_SETTINGS'),
        ),
    ),
    'PARAMETERS' => array(

        // form iblock

        'IBLOCK_TYPE' => array(
            'PARENT' => 'IBLOCK',
            'NAME' => GetMessage('F_IBLOCK_TYPE'),
            'TYPE' => 'LIST',
            'VALUES' => $arIBlockTypes,
            'DEFAULT' => $defaultIBlockType,
            'REFRESH' => 'Y',
        ),
        'IBLOCK_CODE' => array(
            'PARENT' => 'IBLOCK',
            'NAME' => GetMessage('F_IBLOCK_CODE'),
            'TYPE' => 'LIST',
            'VALUES' => $arResultsIBlockCodes,
            'REFRESH' => 'Y',
        ),
        'SAVE_RESULTS_TO_IBLOCK' => array(
            'PARENT' => 'IBLOCK',
            'NAME' => GetMessage('F_SAVE_RESULTS_TO_IBLOCK'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),

        // e-mails

        'EMAIL_FROM' => array(
            'PARENT' => 'EMAIL_NOTIF',
            'NAME' => GetMessage('F_EMAIL_FROM'),
            'TYPE' => 'TEXT',
            'DEFAULT' => 'noreply@' . $_SERVER['SERVER_NAME'],
        ),
        'ADMIN_EMAIL' => array(
            'PARENT' => 'EMAIL_NOTIF',
            'NAME' => GetMessage('F_ADMIN_EMAIL'),
            'TYPE' => 'TEXT',
            'DEFAULT' => 'admin@' . $_SERVER['SERVER_NAME'],
        ),
        'HIDDEN_COPY_ADMIN' => array(
            'PARENT' => 'EMAIL_NOTIF',
            'NAME' => GetMessage('F_HIDDEN_COPY_ADMIN'),
            'TYPE' => 'TEXT',
        ),
        'HIDDEN_COPY_USER' => array(
            'PARENT' => 'EMAIL_NOTIF',
            'NAME' => GetMessage('F_HIDDEN_COPY_USER'),
            'TYPE' => 'TEXT',
        ),
        'MANY_USER_EMAILS' => array(
            'PARENT' => 'EMAIL_NOTIF',
            'NAME' => GetMessage('F_MANY_USER_EMAILS'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),

        // e-mail templates

        'EMAIL_TEMPLATES_IBLOCK_TYPE' => array(
            'PARENT' => 'EMAIL_NOTIF',
            'NAME' => GetMessage('F_EMAIL_TEMPLATES_IBLOCK_TYPE'),
            'TYPE' => 'LIST',
            'VALUES' => $arIBlockTypes,
            'DEFAULT' => $defaultIBlockType,
            'REFRESH' => 'Y',
        ),
        'EMAIL_TEMPLATES_IBLOCK_CODE' => array(
            'PARENT' => 'EMAIL_NOTIF',
            'NAME' => GetMessage('F_EMAIL_TEMPLATES_IBLOCK_CODE'),
            'TYPE' => 'LIST',
            'VALUES' => $arTemplatesIBlockCodes,
            'DEFAULT' => $defaultTemplatesIBlockCode,
            'REFRESH' => 'Y',
        ),

        'ADMIN_EMAIL_TEMPLATE' => array(
            'PARENT' => 'EMAIL_NOTIF',
            'NAME' => GetMessage('F_ADMIN_EMAIL_TEMPLATE'),
            'TYPE' => 'LIST',
            'VALUES' => $arEmailTemplates,
            'REFRESH' => 'Y',
        ),
        'USER_EMAIL_TEMPLATE' => array(
            'PARENT' => 'EMAIL_NOTIF',
            'NAME' => GetMessage('F_USER_EMAIL_TEMPLATE'),
            'TYPE' => 'LIST',
            'VALUES' => $arEmailTemplates,
            'REFRESH' => 'Y',
        ),

        // additional settings

        'HIDE_SUCCESS_FORM' => array(
            'PARENT' => 'ADDITIONAL_SETTINGS',
            'NAME' => GetMessage('F_HIDE_SUCCESS_FORM'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),
        'USE_CAPTCHA' => array(
            'PARENT' => 'ADDITIONAL_SETTINGS',
            'NAME' => GetMessage('F_USE_CAPTCHA'),
            'TYPE' => 'CHECKBOX',
            'DEFAULT' => 'Y',
        ),
        'FORM_SALT' => array(
            'PARENT' => 'ADDITIONAL_SETTINGS',
            'NAME' => GetMessage('F_FORM_SALT'),
            'TYPE' => 'TEXT',
            'DEFAULT' => '',
        ),

    ),
);
