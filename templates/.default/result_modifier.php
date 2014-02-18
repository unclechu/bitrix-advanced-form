<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

$arResult['ERROR_MESSAGES'] = null;
$arResult['SUCCESS_MESSAGES'] = null;

$MESS = array();
require dirname(__FILE__) .'/lang/'. LANGUAGE_ID .'/template.php';

function MyGetMessage($MESS, $msgCode, $replaceList=array()) {
    if ( ! array_key_exists($msgCode, $MESS)) return $msgCode;
    $result = $MESS[$msgCode];
    foreach ($replaceList as $key=>$val) {
        $result = str_replace($key, $val, $result);
    }
    return $result;
}

if (is_array($arResult['POST_ERROR'])) {
    $arResult['ERROR_MESSAGES'] = array();
    foreach ($arResult['POST_ERROR'] as $arItem) {
        if ($arItem['TYPE'] == 'REQUIRED_FIELD'
        && array_key_exists('CAPTCHA', $arItem)
        && $arItem['CAPTCHA'] == 'Y') {
            $arResult['ERROR_MESSAGES'][] = MyGetMessage($MESS,
                'ERROR_REQUIRED_FIELD', array(
                    '#FIELD_NAME#' => MyGetMessage($MESS, 'CAPTCHA'),
                ));
        } elseif ($arItem['TYPE'] == 'REQUIRED_FIELD') {
            $arResult['ERROR_MESSAGES'][] = MyGetMessage($MESS,
                'ERROR_REQUIRED_FIELD', array(
                    '#FIELD_NAME#' => $arItem['NAME'],
                ));
        } elseif ($arItem['TYPE'] == 'INCORRECT_VALUE'
        && array_key_exists('CAPTCHA', $arItem)
        && $arItem['CAPTCHA'] == 'Y') {
            $arResult['ERROR_MESSAGES'][] = MyGetMessage($MESS, 'WRONG_CAPTCHA');
        } elseif ($arItem['TYPE'] == 'INCORRECT_VALUE'
        && array_key_exists('LIST', $arItem)
        && $arItem['LIST'] == 'Y') {
            $arResult['ERROR_MESSAGES'][] = MyGetMessage($MESS,
                'WRONG_LIST_VALUE', array(
                    '#FIELD_NAME#' => $arItem['NAME']
                ));
        } elseif ($arItem['TYPE'] == 'INCORRECT_VALUE'
        && $arItem['CODE'] == 'email') {
            if ($arParams['MANY_USER_EMAILS'] == 'Y') {
                $arResult['ERROR_MESSAGES'][] = MyGetMessage($MESS,
                    'ERROR_UNVALID_EMAIL', array(
                        '#USER_EMAIL#' => $arResult['POST_DATA']['email'],
                    ));
            } else {
                $arResult['ERROR_MESSAGES'][] = MyGetMessage($MESS,
                    'ERROR_UNVALID_EMAIL_SINGLE', array(
                        '#USER_EMAIL#' => $arResult['POST_DATA']['email'],
                    ));
            }
        } elseif ($arItem['TYPE'] == 'INCORRECT_VALUE'
        && array_key_exists('NUMBER', $arItem)
        && $arItem['NUMBER'] == 'Y') {
            $arResult['ERROR_MESSAGES'][] = MyGetMessage($MESS,
                'WRONG_NUMBER_VALUE', array(
                    '#FIELD_NAME#' => $arItem['NAME']
                ));
        } elseif ($arItem['TYPE'] == 'CANT_STORE_RESULT') {
            $arResult['ERROR_MESSAGES'][] = MyGetMessage($MESS,
                'CANT_STORE_RESULT', array(
                    '#ERROR_MESSAGE#' => $arItem['MESSAGE']
                ));
        } elseif ($arItem['TYPE'] == 'CANT_SEND_EMAIL'
        && $arItem['EMAIL_TYPE'] == 'ADMIN') {
            $arResult['ERROR_MESSAGES'][] = MyGetMessage($MESS,
                'CANT_SEND_EMAIL', array(
                    '#TO_TEXT#' => MyGetMessage($MESS, 'CANT_SEND_EMAIL_ADMIN'),
                ));
        } elseif ($arItem['TYPE'] == 'CANT_SEND_EMAIL'
        && $arItem['EMAIL_TYPE'] == 'USER') {
            $arResult['ERROR_MESSAGES'][] = MyGetMessage($MESS,
                'CANT_SEND_EMAIL', array(
                    '#TO_TEXT#' => MyGetMessage($MESS, 'CANT_SEND_EMAIL_USER', array(
                        '#USER_EMAIL#' => $arItem['EMAIL'],
                    )),
                ));
        } else {
            $arResult['ERROR_MESSAGES'][] = MyGetMessage($MESS, 'UNKNOWN_ERROR_MSG');
        }
    }
}

if (is_array($arResult['POST_SUCCESS'])) {
    $arResult['SUCCESS_MESSAGES'] = array();
    foreach ($arResult['POST_SUCCESS'] as $arItem) {
        if ($arItem['TYPE'] == 'SAVED_TO_BASE') {
            $arResult['SUCCESS_MESSAGES'][] = MyGetMessage(
                $MESS, 'SUCCESS_SAVED_TO_BASE');
        } elseif ($arItem['TYPE'] == 'MAILED_TO_ADMIN') {
            $arResult['SUCCESS_MESSAGES'][] = MyGetMessage(
                $MESS, 'SUCCESS_MAILED_TO_ADMIN');
        } elseif ($arItem['TYPE'] == 'MAILED_TO_USER') {
            $arResult['SUCCESS_MESSAGES'][] = MyGetMessage(
                $MESS, 'SUCCESS_MAILED_TO_USER', array(
                    '#USER_EMAIL#' => $arItem['EMAIL']
                ));
        } else {
            $arResult['SUCCESS_MESSAGES'][] = MyGetMessage(
                $MESS, 'UNKNOWN_SUCCESS_MSG');
        }
    }
}
