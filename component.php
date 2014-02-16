<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
/**
 * Advanced Form
 * Bitrix component
 * 
 * License: GPLv3
 * Author: Viacheslav Lotsmanov (unclechu)
 * Contact: lotsmanov89@gmail.com
 */

CModule::IncludeModule('iblock');

// form identificator
$arResult['FORM_UID'] = substr(md5($APPLICATION->GetCurPage(0) . $arParams['FORM_SALT']), 0, 7);

$arResult['POST_PATHNAME'] = $APPLICATION->GetCurPage(0) . '?post=Y';
$arResult['SITE_URL'] = 'http://' . $_SERVER['SERVER_NAME'];
$arResult['FORM_URL'] = $arResult['SITE_URL'] . $APPLICATION->GetCurPage(0);

// strings (textareas too), numbers, lists
$arParams['SUPPORTED_FIELDS_TYPES'] = array('S', 'N', 'L');
$arParams['EMAIL_TEMPLATES_REQUIRED_FIELDS'] = array('from', 'to', 'subject', 'body');

require_once dirname(__FILE__) . '/captcha.php';
require_once dirname(__FILE__) . '/email.php';
require_once dirname(__FILE__) . '/fields.php';
require_once dirname(__FILE__) . '/params_validation.php';
require_once dirname(__FILE__) . '/store_result.php';

$res = validParams($arResult, $arParams);
if ($res !== true) {
    if (is_array($res)) {
        if ($res[0] == 'VLDERR_IBLOCK_HAS_NO_RQ_FIELD') {
            ShowError(GetMessage($res[0], array(
                '#FIELD_NAME#' => GetMessage('VLDERR_EMAIL_TEMPLATE_FIELD_'.$res[1]),
                '#FIELD_CODE#' => $res[1],
            )));
        } else {
            ShowError(GetMessage('UNKNOWN_ERROR'));
        }
    } else {
        ShowError(GetMessage($res));
        return false;
    }
}

$arResult['POST_DATA'] = array();

// replace list default values for email templates
$arResult['REPLACE_LIST'] = array(
    '#FORM_UID#' => $arResult['FORM_UID'],
    '#DOMAIN_NAME#' => $_SERVER['SERVER_NAME'],

    /* #SITE_NAME# - from site settings by SITE_ID
    
       Params:
       #EMAIL_FROM# #ADMIN_EMAIL#
       #HIDDEN_COPY_ADMIN# #HIDDEN_COPY_USER#

       Form fields as #TITLE_%FIELD_NAME%# and #VALUE_%FIELD_VALUE%#
       Examples (remember: case sensitive):
       #TITLE_name# = Name, #VALUE_name# = John Smith,
       #TITLE_email# = E-Mail, #VALUE_email# = john@domain.org */
);

// messages arrays about success or errors
$arResult['POST_ERROR'] = null;
$arResult['POST_SUCCESS'] = null;

$arResult['FORM_POSTED'] = false;
$arResult['FORM_HIDE'] = false;

$arResult['FIELDS_LIST'] = getFieldsList($arResult, $arParams);

function addErrorMsg($arResult, $arParams, $msg) {
    if ($arResult['POST_ERROR'] === null) {
        $arResult['POST_ERROR'] = array();
    }
    $arResult['POST_ERROR'][] = $msg;
    return $arResult['POST_ERROR'];
}

function addSuccessMsg($arResult, $arParams, $msg) {
    if ($arResult['POST_SUCCESS'] === null) {
        $arResult['POST_SUCCESS'] = array();
    }
    $arResult['POST_SUCCESS'][] = $msg;
    return $arResult['POST_SUCCESS'];
}

// posting handler
if (array_key_exists('post', $_GET) && $_GET['post'] == 'Y'
&& array_key_exists('form_uid', $_POST)
&& $_POST['form_uid'] == $arResult['FORM_UID']) {
    $arResult['FORM_POSTED'] = true;

    foreach ($_POST as $key=>$val) {
        $val = trim($val);
        $arResult['POST_DATA'][$key] = htmlspecialcharsEx($val);
        $arResult['POST_DATA']['~'.$key] = $val; // pure
    }

    // errors catching

    // check for required fields
    foreach ($arResult['FIELDS_LIST'] as $arField) {
        if ( ! in_array($arField['CODE'], array_keys($arResult['POST_DATA']))
        ||   ($arField['IS_REQUIRED'] == 'Y'
           && empty($arResult['POST_DATA'][$arField['CODE']]))   ) {
            $arResult['POST_ERROR'] = addErrorMsg($arResult, $arParams,
                array(
                   'TYPE' => 'REQUIRED_FIELD',
                   'CODE' => $arField['CODE'],
                   'NAME' => $arField['NAME'],
                )
            );
        }
    }    
    if ($arParams['USE_CAPTCHA'] == 'Y') {
        if (empty($arResult['POST_DATA'][$arResult['CAPTCHA']['HIDDEN_FIELD_NAME']])
        || empty($arResult['POST_DATA'][$arResult['CAPTCHA']['INPUT_FIELD_NAME']])) {
            $arResult['POST_ERROR'] = addErrorMsg($arResult, $arParams, array(
                'TYPE' => 'REQUIRED_FIELD',
                'CAPTCHA' => 'Y',
            ));
        } elseif ( ! $arResult['CAPTCHA']['CLASS_EXAMPLE']->CheckCode(
        $arResult['POST_DATA'][$arResult['CAPTCHA']['INPUT_FIELD_NAME']],
        $arResult['POST_DATA'][$arResult['CAPTCHA']['HIDDEN_FIELD_NAME']]) ) {
            $arResult['POST_ERROR'] = addErrorMsg($arResult, $arParams, array(
                'TYPE' => 'INCORRECT_VALUE',
                'CAPTCHA' => 'Y',
            ));
        }
    }

    $userManyEmails = (($arParams['MANY_USER_EMAILS'] == 'Y') ? true : false);
    foreach ($arResult['FIELDS_LIST'] as $arField) {

        // check for correct email address
        if ( $arField['CODE'] == 'email'
        && !empty($arResult['POST_DATA']['~'.$arField['CODE']])
        && !validEmailAddress($arResult['POST_DATA']['~'.$arField['CODE']],
                              $userManyEmails) ) {
            $arResult['POST_ERROR'] = addErrorMsg($arResult, $arParams, array(
                'TYPE' => 'INCORRECT_VALUE',
                'CODE' => $arField['CODE'],
                'NAME' => $arField['NAME'],
            ));

        // check for correct values of list-type fields
        } elseif ( $arField['FIELD_TYPE'] == 'LIST' ) {
            $listIDS = array();
            foreach ($arField['LIST_ENUM'] as $item)
                $listIDS[] =  $item['XML_ID'];
            if (!in_array($arResult['POST_DATA'][$arField['CODE']], $listIDS)) {
                $arResult['POST_ERROR'] = addErrorMsg($arResult, $arParams, array(
                    'TYPE' => 'INCORRECT_VALUE',
                    'CODE' => $arField['CODE'],
                    'NAME' => $arField['NAME'],
                    'LIST' => 'Y',
                ));
            }

        // check for correct values of number-type fields
        } elseif ( $arField['FIELD_TYPE'] == 'NUMBER'
        && !preg_match('/^[0-9]*$/',
           $arResult['POST_DATA'][$arField['CODE']],
           $matches)) {
            $arResult['POST_ERROR'] = addErrorMsg($arResult, $arParams, array(
                'TYPE' => 'INCORRECT_VALUE',
                'CODE' => $arField['CODE'],
                'NAME' => $arField['NAME'],
                'NUMBER' => 'Y',
            ));

        // remove start line spaces
        } elseif ( $arField['FIELD_TYPE'] == 'TEXTAREA' ) {
            $arResult['POST_DATA'][$arField['CODE']] = preg_replace(
                '/^[\t ]+/m', '', $arResult['POST_DATA'][$arField['CODE']]
            );
            $arResult['POST_DATA']['~'.$arField['CODE']] = preg_replace(
                '/^[\t ]+/m', '', $arResult['POST_DATA']['~'.$arField['CODE']]
            );
        }

        // set filtered email to post data
        if ( $arField['CODE'] == 'email'
        && !empty($arResult['POST_DATA']['~'.$arField['CODE']])
        && $validEmail = validEmailAddress(
                $arResult['POST_DATA']['~'.$arField['CODE']],
                $userManyEmails )
        ) {
            $arResult['POST_DATA']['~'.$arField['CODE']] = $validEmail;
            $arResult['POST_DATA'][$arField['CODE']] = htmlspecialcharsEx($validEmail);
        }

    }

    if ($arResult['POST_ERROR'] === null) {
        if ($arParams['HIDE_SUCCESS_FORM']) {
            $arResult['FORM_HIDE'] = true;
        }

        $arResult['POST_SUCCESS'] = array();

        if ($arParams['SAVE_RESULTS_TO_IBLOCK'] == 'Y') {
            $res = storeResult($arResult, $arParams);
            if ($res === true) {
                $arResult['POST_SUCCESS'] = addSuccessMsg($arResult, $arParams, array(
                    'TYPE' => 'SAVED_TO_BASE',
                ));
            } else {
                $arResult['POST_ERROR'] = addErrorMsg($arResult, $arParams, array(
                    'TYPE' => 'CANT_STORE_RESULT',
                    'MESSAGE' => $res['MESSAGE'],
                ));
            }
        }

        if (needToSendEmail($arResult, $arParams, 'ANY')) {
            if (needToSendEmail($arResult, $arParams, 'ADMIN')) {
                $res = sendEmail($arResult, $arParams, 'ADMIN');
                if ($res === true) {
                    $arResult['POST_SUCCESS'] = addSuccessMsg($arResult, $arParams, array(
                        'TYPE' => 'MAILED_TO_ADMIN',
                        'EMAIL_TYPE' => 'ADMIN',
                        'EMAIL' => $arParams['ADMIN_EMAIL'],
                        '~EMAIL' => $arParams['~ADMIN_EMAIL'],
                    ));
                } else {
                    $arResult['POST_ERROR'] = addErrorMsg($arResult, $arParams, array(
                        'TYPE' => 'CANT_SEND_EMAIL',
                        'EMAIL_TYPE' => 'ADMIN',
                        'EMAIL' => $arParams['ADMIN_EMAIL'],
                        '~EMAIL' => $arParams['~ADMIN_EMAIL'],
                    ));
                }
            }
            if (needToSendEmail($arResult, $arParams, 'USER')
            && !empty($arResult['POST_DATA']['email'])) {
                $res = sendEmail($arResult, $arParams, 'USER');
                if ($res === true) {
                    $arResult['POST_SUCCESS'] = addSuccessMsg($arResult, $arParams, array(
                        'TYPE' => 'MAILED_TO_USER',
                        'EMAIL_TYPE' => 'USER',
                        'EMAIL' => $arResult['POST_DATA']['email'],
                        '~EMAIL' => $arResult['POST_DATA']['~email'],
                    ));
                } else {
                    $arResult['POST_ERROR'] = addErrorMsg($arResult, $arParams, array(
                        'TYPE' => 'CANT_SEND_EMAIL',
                        'EMAIL_TYPE' => 'USER',
                        'EMAIL' => $arResult['POST_DATA']['email'],
                        '~EMAIL' => $arResult['POST_DATA']['~email'],
                    ));
                }
            }
        }

        $arResult['POST_DATA'] = array();
    }
}

$arResult['POST_DATA'] = fillPostData($arResult, $arParams);

$this->IncludeComponentTemplate();
