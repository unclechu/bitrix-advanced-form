<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

// parameters validation module

// procedure
function validParams($arResult, $arParams) {
    if (empty($arParams['IBLOCK_TYPE']) || $arParams['IBLOCK_TYPE'] == '-')
        return 'VLDERR_IBLOCK_TYPE_EMPTY';

    if (empty($arParams['IBLOCK_CODE']) || $arParams['IBLOCK_CODE'] == '-')
        return 'VLDERR_IBLOCK_CODE_EMPTY';

    // form iblock must have even only one field for form
    $res = formHasFields($arResult, $arParams);
    if ($res !== true) {
        if ($res == 'IBLOCK_TYPE_NOT_FOUND') {
            return 'VLDERR_IBLOCK_TYPE_NOT_FOUND';
        } elseif ($res == 'IBLOCK_CODE_NOT_FOUND') {
            return 'VLDERR_IBLOCK_CODE_NOT_FOUND';
        } elseif ($res == 'HAS_NO_FIELDS') {
            return 'VLDERR_IBLOCK_HAS_NO_FIELDS';
        }

        return 'UNKNOWN_ERROR';
    }

    /* checking for email templates iblock has required fields (if need to send email)
       see: $arParams['EMAIL_TEMPLATES_REQUIRED_FIELDS'] */
    $res = emailTemplatesHasRequiredFields($arResult, $arParams);
    if ($res !== true) {
        if (is_array($res) && $res[0] == 'IBLOCK_HAS_NO_RQ_FIELD') {
            return array('VLDERR_IBLOCK_HAS_NO_RQ_FIELD', $res[1]);
        } elseif ($res == 'IBLOCK_TYPE_NOT_FOUND') {
            return 'VLDERR_EMAIL_TEMPLATES_IBLOCK_TYPE_NOT_FOUND';
        } elseif ($res == 'IBLOCK_CODE_NOT_FOUND') {
            return 'VLDERR_EMAIL_TEMPLATES_IBLOCK_CODE_NOT_FOUND';
        } elseif ($res == 'ADMIN_TEMPLATE_NOT_FOUND') {
            return 'VLDERR_ADMIN_TEMPLATE_NOT_FOUND';
        } elseif ($res == 'USER_TEMPLATE_NOT_FOUND') {
            return 'VLDERR_USER_TEMPLATE_NOT_FOUND';
        }

        return 'UNKNOWN_ERROR';
    }

    // email addresses validation and filtering by format
    if (needToSendEmail($arResult, $arParams, 'ANY')) {
        if (empty($arParams['~EMAIL_FROM']))
            return 'VLDERR_EMAIL_FROM_EMPTY';
        $res = validEmailAddress($arParams['~EMAIL_FROM'], false);
        if ($res === false)
            return 'VLDERR_EMAIL_FROM_INCORRECT';
        else
            $arParams['~EMAIL_FROM'] = $res;
    }
    if (needToSendEmail($arResult, $arParams, 'ADMIN')) {
        if (empty($arParams['~ADMIN_EMAIL']))
            return 'VLDERR_ADMIN_EMAIL_EMPTY';
        $res = validEmailAddress($arParams['~ADMIN_EMAIL'], true);
        if ($res === false)
            return 'VLDERR_ADMIN_EMAIL_INCORRECT';
        else
            $arParams['~ADMIN_EMAIL'] = $res;

        if (!empty($arParams['~HIDDEN_COPY_ADMIN'])) {
            $res = validEmailAddress($arParams['~HIDDEN_COPY_ADMIN'], true);
            if ($res === false)
                return 'VLDERR_HIDDEN_COPY_ADMIN_INCORRECT';
            else
                $arParams['~HIDDEN_COPY_ADMIN'] = $res;
        }
    }
    if (needToSendEmail($arResult, $arParams, 'USER')) {
        if (!empty($arParams['~HIDDEN_COPY_USER'])) {
            $res = validEmailAddress($arParams['~HIDDEN_COPY_USER'], true);
            if ($res === false)
                return 'VLDERR_HIDDEN_COPY_USER_INCORRECT';
            else
                $arParams['~HIDDEN_COPY_USER'] = $res;
        }
    }

    return true;
}
