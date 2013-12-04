<?
// parameters validation errors messages

// form iblock
$MESS ['VLDERR_IBLOCK_TYPE_EMPTY'] = 'Не указан тип инфоблока формы';
$MESS ['VLDERR_IBLOCK_CODE_EMPTY'] = 'Не указан инфоблок формы'
    .' (обратите внимание: инфоблок и его свойства должны иметь символьные коды)';
$MESS ['VLDERR_IBLOCK_TYPE_NOT_FOUND'] = 'Не найден тип инфоблока формы';
$MESS ['VLDERR_IBLOCK_CODE_NOT_FOUND'] = 'Не найден инфоблок формы'
    .' (обратите внимание: инфоблок и его свойства должны иметь символьные коды)';
$MESS ['VLDERR_IBLOCK_HAS_NO_FIELDS'] = 'У инфоблока нет свойств для формы'
    .' (обратите внимание: свойства должны иметь символьные коды)';

// emails
$MESS ['VLDERR_EMAIL_FROM_EMPTY'] = 'Не указан e-mail отправителя писем';
$MESS ['VLDERR_ADMIN_EMAIL_EMPTY'] = 'Не указан e-mail администратора';
$MESS ['VLDERR_EMAIL_FROM_INCORRECT'] = 'Некорректно указан e-mail отправителя писем';
$MESS ['VLDERR_ADMIN_EMAIL_INCORRECT'] = 'Некорректно указан e-mail администратора';
$MESS ['VLDERR_HIDDEN_COPY_ADMIN_INCORRECT'] = 'Некорректно указан e-mail '
    .'скрытой копии письма администратору';
$MESS ['VLDERR_HIDDEN_COPY_USER_INCORRECT'] = 'Некорректно указан e-mail '
    .'скрытой копии письма заполнившему форму';

// email templates
$MESS ['VLDERR_EMAIL_TEMPLATE_FIELD_from'] = 'от кого';
$MESS ['VLDERR_EMAIL_TEMPLATE_FIELD_to'] = 'кому';
$MESS ['VLDERR_EMAIL_TEMPLATE_FIELD_subject'] = 'тема письма';
$MESS ['VLDERR_EMAIL_TEMPLATE_FIELD_body'] = 'тело письма';
$MESS ['VLDERR_IBLOCK_HAS_NO_RQ_FIELD'] = 'У инфоблока шаблонов e-mail'
    .' отсутствует обязательное свойство «#FIELD_NAME#»'
    .' (символьный код: #FIELD_CODE#)';

$MESS ['VLDERR_EMAIL_TEMPLATES_IBLOCK_TYPE_NOT_FOUND'] = 'Не найден тип инфоблока'
    .' e-mail шаблонов';
$MESS ['VLDERR_EMAIL_TEMPLATES_IBLOCK_CODE_NOT_FOUND'] = 'Не найден инфоблок'
    .' e-mail шаблонов'
    .' (обратите внимание: инфоблок и его свойства должны иметь символьные коды)';
$MESS ['VLDERR_ADMIN_TEMPLATE_NOT_FOUND'] = 'Не найден шаблон e-mail письма администратору'
    .' (обратите внимание: шаблоны писем должны иметь символьные коды)';
$MESS ['VLDERR_USER_TEMPLATE_NOT_FOUND'] = 'Не найден шаблон e-mail письма заполнившему форму'
    .' (обратите внимание: шаблоны писем должны иметь символьные коды)';

// other
$MESS ['UNKNOWN_ERROR'] = 'Неизвестная ошибка';
