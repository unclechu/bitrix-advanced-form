<?if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();

// captcha

$arResult['CAPTCHA'] = array(
    'CODE' => '',
    'ALT' => 'CAPTCHA',
    'SRC' => '',
    'WIDTH' => 0,
    'HEIGHT' => 0,
    'HIDDEN_FIELD_NAME' => 'captcha_sid_' . $arResult['FORM_UID'],
    'INPUT_FIELD_NAME' => 'captcha_' . $arResult['FORM_UID'],
    'VIEW_HTML_CODE' => '',
    'FIELD_HTML_CODE' => '',
);

if ($arParams['USE_CAPTCHA'] == 'Y') {
    include_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/classes/general/captcha.php');
    $captcha = new CCaptcha();

    $arResult['CAPTCHA']['CODE'] = htmlspecialchars($GLOBALS['APPLICATION']->CaptchaGetCode());
    $arResult['CAPTCHA']['SRC'] = '/bitrix/tools/captcha.php?captcha_code=' . $arResult['CAPTCHA']['CODE'];
    $arResult['CAPTCHA']['WIDTH'] = 180;
    $arResult['CAPTCHA']['HEIGHT'] = 40;
    $arResult['CAPTCHA']['VIEW_HTML_CODE'] = ''
        .'<input type="hidden" name="'. $arResult['CAPTCHA']['HIDDEN_FIELD_NAME']
            .'" value="'. $arResult['CAPTCHA']['CODE'] .'" />'
        .'<img alt="'. $arResult['CAPTCHA']['ALT'] .'" src="'. $arResult['CAPTCHA']['SRC']
            .'" width="'. $arResult['CAPTCHA']['WIDTH']
            .'" height="'. $arResult['CAPTCHA']['HEIGHT'] .'" />';
    $arResult['CAPTCHA']['FIELD_HTML_CODE'] = ''
        .'<input type="text" name="'. $arResult['CAPTCHA']['INPUT_FIELD_NAME'] .'" value="" />';
    $arResult['CAPTCHA']['CLASS_EXAMPLE'] = $captcha;
}
