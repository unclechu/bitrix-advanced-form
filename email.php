<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

// email send module

/**
 * check e-mail address(es) for correct formats:
 *   user@domain.org
 *   John Smith <user@domain.org>
 *   john@domain.org, maria@domain.org,
 *   John Smith <user@domain.org>, Maria <maria@domain.org>,
 *   user@domain.org, Maria <maria@domain.org>
 *   etc...
 * returns filtered string or false if address(es) is not valid
 */
function validEmailAddress($emailStr, $canMany=true) {
    $emailStr = preg_replace('/\s*,\s*/', ',', $emailStr);
    $emailStr = preg_replace('/^\s+|\s+$/', '', $emailStr);
    $emailStr = preg_replace('/\s*</', ' <', $emailStr);
    $emailStr = preg_replace('/<\s+/', '<', $emailStr);
    $emailStr = preg_replace('/\s+>/', '>', $emailStr);
    $emailStr = preg_replace('/\s+/', ' ', $emailStr);
    $emails = explode(',', $emailStr);
    $outEmails = array();

    if ( ! $canMany && count($emails) > 1) return false;

    foreach ($emails as $email) {
        if ( ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            if ( ! preg_match(
                     '/^(?P<name>[a-zA-Zа-яА-ЯёЁ0-9_\-\(\) ]+)'
                         .' <(?P<email>\S+)>$/u',
                     $email, $matches)) {
                return false;
            }

            if (filter_var($matches['email'], FILTER_VALIDATE_EMAIL)) {
                $outEmails[] = $email;
            } else {
                return false;
            }
        } else {
            $outEmails[] = $email;
        }
    }

    return implode(', ', $outEmails);
}

/**
 * check for need to send email
 * $type possible values:
 *   'ANY' - need to send email to admin or user
 *   'ADMIN' - need to send email to admin
 *   'USER' - need to send email to user
 * returns true or false
 */
function needToSendEmail($arResult, $arParams, $type='ANY') {
    // if no email templates iblock then we don't need to send email
    if (empty($arParams['EMAIL_TEMPLATES_IBLOCK_TYPE'])
    || $arParams['EMAIL_TEMPLATES_IBLOCK_TYPE'] == '-') {
        return false;
    }
    if (empty($arParams['EMAIL_TEMPLATES_IBLOCK_CODE'])
    || $arParams['EMAIL_TEMPLATES_IBLOCK_CODE'] == '-') {
        return false;
    }

    // check for admin email
    if ($type == 'ADMIN') {
        if (empty($arParams['ADMIN_EMAIL_TEMPLATE'])
        || $arParams['ADMIN_EMAIL_TEMPLATE'] == '-') {
            return false;
        }
        return true;

    // check for user email
    } elseif ($type == 'USER') {
        if (empty($arParams['USER_EMAIL_TEMPLATE'])
        || $arParams['USER_EMAIL_TEMPLATE'] == '-') {
            return false;
        }
        return true;

    // check for both admin and user
    } else { // 'ANY'
        if ( ( ! empty($arParams['ADMIN_EMAIL_TEMPLATE'])
            && $arParams['ADMIN_EMAIL_TEMPLATE'] != '-')
          || ( ! empty($arParams['USER_EMAIL_TEMPLATE'])
            && $arParams['USER_EMAIL_TEMPLATE'] != '-')) {
             return true;
        }
        return false;
    }
}

function getValueFromListByID($arField, $XML_ID) {
    foreach ($arField['LIST_ENUM'] as $item) {
        if ($item['XML_ID'] == $XML_ID) {
            return $item;
        }
    }

    return false;
}

/**
 * $type possible values: 'ADMIN', 'USER'
 */
function sendEmail($arResult, $arParams, $type=null) {
    if ($type != 'ADMIN' && $type != 'USER')
        return false;

    // get site name for #SITE_NAME# replacing
    $site = CSite::GetByID(SITE_ID);
    $site = $site->Fetch();
    if ( ! $site) return false;
    $siteName = $site['SITE_NAME'];

    $template = null;
    
    // get template
    $res = CIBlockElement::GetList(
        array(),
        array(
            'IBLOCK_TYPE' => $arParams['EMAIL_TEMPLATES_IBLOCK_TYPE'],
            'IBLOCK_CODE' => $arParams['EMAIL_TEMPLATES_IBLOCK_CODE'],
            'CODE' => $arParams[$type.'_EMAIL_TEMPLATE'],
            'ACTIVE' => 'Y',
        ),
        false,
        false,
        array(
            'IBLOCK_ID', 'ID', 'IBLOCK_TYPE_ID', 'IBLOCK_CODE',
            'NAME', 'CODE', 'DETAIL_TEXT',
            'PROPERTY_from', 'PROPERTY_to', 'PROPERTY_subject', 'PROPERTY_body'
        )
    );

    // because CIBlockElement::GetList returns not strong results
    while ($arItem = $res->Fetch()) {
        if ($arItem['IBLOCK_TYPE_ID'] == $arParams['EMAIL_TEMPLATES_IBLOCK_TYPE']
        && $arItem['IBLOCK_CODE'] == $arParams['EMAIL_TEMPLATES_IBLOCK_CODE']
        && $arItem['CODE'] == $arParams[$type.'_EMAIL_TEMPLATE']) {
            $template = $arItem;
        }
    }

    if ( ! $template) return false;

    $headers = '';
    $message = '';
    $messageReplaceList = $arResult['REPLACE_LIST'];
    $otherReplaceList = $arResult['REPLACE_LIST'];
    $messagePrefix = '~'; // pure html (for text/plain type)

    if (strtoupper($template['PROPERTY_BODY_VALUE']['TYPE']) == 'HTML') {
        $headers .= 'Content-Type: text/html; charset=utf-8'."\r\n";
        $messageReplaceList['#SITE_NAME#'] = htmlspecialcharsEx($siteName);
        $messagePrefix = ''; // transformed html to text
    } elseif (strtoupper($template['PROPERTY_BODY_VALUE']['TYPE']) == 'TEXT') {
        $headers .= 'Content-Type: text/plain; charset=utf-8'."\r\n";
        $messageReplaceList['#SITE_NAME#'] = $siteName;
    } else {
        return false;
    }

    $otherReplaceList['#SITE_NAME#'] = $siteName;

    $otherReplaceList['#EMAIL_FROM#'] = $arParams['~EMAIL_FROM'];
    $otherReplaceList['#ADMIN_EMAIL#'] = $arParams['~ADMIN_EMAIL'];
    $otherReplaceList['#HIDDEN_COPY_ADMIN#'] = $arParams['~HIDDEN_COPY_ADMIN'];
    $otherReplaceList['#HIDDEN_COPY_USER#'] = $arParams['~HIDDEN_COPY_USER'];

    $messageReplaceList['#EMAIL_FROM#'] = $arParams[$messagePrefix.'EMAIL_FROM'];
    $messageReplaceList['#ADMIN_EMAIL#'] = $arParams[$messagePrefix.'ADMIN_EMAIL'];
    $messageReplaceList['#HIDDEN_COPY_ADMIN#'] = $arParams[$messagePrefix.'HIDDEN_COPY_ADMIN'];
    $messageReplaceList['#HIDDEN_COPY_USER#'] = $arParams[$messagePrefix.'HIDDEN_COPY_USER'];

    // fields replaces
    foreach ($arResult['FIELDS_LIST'] as $arField) {
        $otherReplaceList['#TITLE_'.$arField['CODE'].'#'] = $arField['NAME'];
        $messageReplaceList['#TITLE_'.$arField['CODE'].'#'] = $arField['NAME'];
        if ($arField['FIELD_TYPE'] == 'LIST') {
            $val = getValueFromListByID(
                $arField,
                $arResult['POST_DATA'][$arField['CODE']]
            );
            if ($val === false) return false;
            $otherReplaceList['#VALUE_'.$arField['CODE'].'#'] = $val['~VALUE'];
            $messageReplaceList['#VALUE_'.$arField['CODE'].'#'] = $val[$messagePrefix.'VALUE'];
        } elseif ($arField['FIELD_TYPE'] == 'TEXTAREA'
        && empty($messagePrefix)) { // for html
            $otherReplaceList['#VALUE_'.$arField['CODE'].'#'] = $arResult['POST_DATA']['~'.$arField['CODE']];
            $val = $arResult['POST_DATA'][$arField['CODE']];
            $val = str_replace("\r", '', $val);
            $val = str_replace("\n", "<br/>\r\n", $val);
            $messageReplaceList['#VALUE_'.$arField['CODE'].'#'] = $val;
        } else {
            $otherReplaceList['#VALUE_'.$arField['CODE'].'#'] = $arResult['POST_DATA']['~'.$arField['CODE']];
            $messageReplaceList['#VALUE_'.$arField['CODE'].'#'] = $arResult['POST_DATA'][$messagePrefix.$arField['CODE']];
        }
    }

    // prepary arrays to replacing
    $otherReplaceListFrom = array();
    $otherReplaceListTo = array();
    foreach ($otherReplaceList as $key=>$val) {
        $otherReplaceListFrom[] = $key;
        $otherReplaceListTo[] = $val;
    }
    $messageReplaceListFrom = array();
    $messageReplaceListTo = array();
    foreach ($messageReplaceList as $key=>$val) {
        $messageReplaceListFrom[] = $key;
        $messageReplaceListTo[] = $val;
    }

    $from = str_replace(
        $otherReplaceListFrom,
        $otherReplaceListTo,
        $template['PROPERTY_FROM_VALUE'] );
    $to = str_replace(
        $otherReplaceListFrom,
        $otherReplaceListTo,
        $template['PROPERTY_TO_VALUE'] );
    $subject = str_replace(
        $otherReplaceListFrom,
        $otherReplaceListTo,
        $template['PROPERTY_SUBJECT_VALUE'] );
    $body = str_replace(
        $messageReplaceListFrom,
        $messageReplaceListTo,
        $template['PROPERTY_BODY_VALUE']['TEXT'] );

    $headers .= "From: $from\r\n";
    $headers .= "Reply-To: $from\r\n";
    if (!empty($arParams['~HIDDEN_COPY_'.$type])) {
        $headers .= "Bcc: {$arParams['~HIDDEN_COPY_'.$type]}\r\n";
    }
    $headers .= 'X-Mailer: PHP/'.phpversion()."\r\n";

    return mail($to, $subject, $body, $headers);
}
