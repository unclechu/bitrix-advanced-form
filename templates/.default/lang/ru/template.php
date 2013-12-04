<?
$MESS ['ERROR'] = 'Ошибка отправки формы';
$MESS ['SUCCESS'] = 'Данные формы успешно отправлены';
$MESS ['CAPTCHA'] = 'Защита от роботов';
$MESS ['REQUIRED_FIELDS'] = 'Поля, обязательные для заполнения';
$MESS ['SUBMIT_BUTTON'] = 'Отправить';

// errors messages
$MESS ['ERROR_REQUIRED_FIELD'] = 'Обязательное поле не заполнено'
    .' «#FIELD_NAME#»';
$MESS ['ERROR_UNVALID_EMAIL_SINGLE'] = 
    'Введённый вами e-mail адрес'
    .' (#USER_EMAIL#)'
    .' не соответствует допустимому формату.';
$MESS ['ERROR_UNVALID_EMAIL'] = $MESS ['ERROR_UNVALID_EMAIL_SINGLE']
    .'<br/>Адрес может быть указан двумя способами:'
    .'<ol><li>john@domain.org</li>'
    .'<li>John Smith &lt;john@domain.org&gt;</li></ol>'
    .'Также возможно указать несколько e-mail адресов'
    .', в таком случае разделяйте адреса запятыми.';
$MESS ['WRONG_CAPTCHA'] = 'Неверно введены символы с картинки в поле «'
    . $MESS['CAPTCHA'] .'»';
$MESS ['WRONG_LIST_VALUE'] = 'Некорректный элемент из поля списка «#FIELD_NAME#»';
$MESS ['WRONG_NUMBER_VALUE'] = 'Поле «#FIELD_NAME#» является числовым и может содержать только цифры';
$MESS ['CANT_STORE_RESULT'] = 'Не удалось записать данные'
    .', пожалуйста, уведомите об этом администратора сайта.'
    .'<br/>Отчёт об ошибке:<br/>#ERROR_MESSAGE#';
$MESS ['CANT_SEND_EMAIL'] = 'Ошибка при отправке письма #TO_TEXT#'
    .'. Пожалуйста, сообщите об этом администратору сайта.';
$MESS ['CANT_SEND_EMAIL_ADMIN'] = 'администратору';
$MESS ['CANT_SEND_EMAIL_USER'] = 'на ваш e-mail адрес (#USER_EMAIL#)';
$MESS ['UNKNOWN_ERROR_MSG'] = 'Неизвестное сообщение об ошибке';

// success messages
$MESS ['SUCCESS_SAVED_TO_BASE'] = 'Данные формы сохранены в базу';
$MESS ['SUCCESS_MAILED_TO_ADMIN'] = 'Администратору отправлено уведомление';
$MESS ['SUCCESS_MAILED_TO_USER'] = 'На указанный вами e-mail (#USER_EMAIL#) отправлено уведомление';
$MESS ['UNKNOWN_SUCCESS_MSG'] = 'Неизвестное сообщение об успешной отправке';
