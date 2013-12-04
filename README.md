Битрикс-компонент «продвинутая форма»
=====================================

Этот компонент работает с инфоблоками (рассматривается как замена модуля веб-форм). Поля формы берутся из свойств инфоблока и данные заполненной формы сохраняются в этот же инфоблок. Результаты формы так же отправляются на e-mail администратору и на e-mail, заполнившему форму (если имеется свойство с символьным кодом "email" и оно было заполнено). Шаблоны этих e-mail-ов хранятся как элементы другого инфоблока (см. инструкции ниже).

Установка
=========

1. [Скачиваем](https://github.com/unclechu/bitrix-advanced-form/archive/master.zip) и распаковываем содержимое в директорию /bitrix/components/custom/ от корня вашего сайта, где custom — это произвольное имя директории, для неофициальных компонентов Битрикса, можете создать директорию custom, если у вас таковой не имеется (что вполне вероятно), а можете создать директорию с другим именем и распаковать содержимое архива туда (но ни в коем случае ничего не кладите и не меняйте в директории /bitrix/components/bitrix/, почему этого делать не стоит — расскажет вам [документация Битрикс](http://dev.1c-bitrix.ru/docs/));
2. Переименовываем распакованную директорию из архива «bitrix-advanced-form-master» в «advanced_form», а впрочем при желании можете оставить как есть, работать и так должно;
3. По дальнейшей настройке см. ниже.

«Быстрая» установка через терминал
==================================

    cd КОРЕНЬ_САЙТА && cd ./bitrix/components && mkdir custom && cd ./custom && git clone git://github.com/unclechu/bitrix-advanced-form.git advanced_form

Предварительная настройка
=========================

Всего понадобится 2-а инфоблока:

1. Для результатов формы;
2. Для шаблонов E-Mail.

Рекомендую сразу создать отдельный тип инфоблоков с идентификатором (ID) "forms" специално под задачи формы.

Далее создаём обязательный инфоблок для сохранения результатов формы (даже если вы не хотите сохранять результаты формы — он вам всё-равно понадобится, потому как поля формы — определяются свойствами этого инфоблока):

1. Обязательно укажите символьный код для инфоблока (обратите внимание: символьные коды указывать нужно везде, где только увидите такую возможность, потому как компонент опирается всюду на эти символьные коды, а не на идентификаторы (ID) как обычно, поскольку такой способ более надёжен и гибок, и особенно удобен в задачах, которые реализует данный компонент);
2. В поле «название» введите нужное вам название инфоблока, например ваше название будующей формы;
3. Перейдите во вкладку «Поля» и укажите там значение по умолчанию для поля «название» (не путать с предыдущим полем) для элементов инфоблока с результатами формы. По каким-то причинам это поле является обязательным к заполнению;
4. В этой же вкладке укажите значение по умолчанию для поля «начало активности» — «текущие дата и время», чтобы отслеживать дату и время заполнения формы;
5. Сразу перейдите во вкладку «доступ» и поставьте «для всех пользователей» — «чтение», иначе неавторизованный пользователь не сможет отправить данные формы;
6. Переходим во вкладку «свойства» и начинаем наполнять будущую форму полями. Поддерживаемые типы полей смотрите ниже. Типы, которые не поддерживаются — игнорируются формой. Для каждого свойства нужно указывать символьный код. Если вам нужно иметь свойство, которое не попадёт в форму, то в начале его символьного кода должен быть префикс «nf_». Учитываются галочки «обязательное» для обязательных полей формы к заполнению. Если вам нужно отправлять пользователю e-mail уведомление о заполнении им формы, то должно быть текстовое поле с символьным кодом «email». Для полей типа TEXTAREA используется тип свойства «HTML/текст». Для типа «список» обязательно нужно указывать в значениях «XML_ID». Для типа «список» ещё не реализована поддержка чекбоксов, см. TODO;
7. Сохраняем инфоблок, и на этом инфоблок для самой формы готов;

По желанию можно перейти в сам инфоблок и настроить отображение списка элементов, убрав оттуда лишние поля («название») и добавив некоторые опознавательные поля из формы. Можно перейти к добавлению нового элемента и там тоже поубирать всё лишнее, оставив единственную вкладку с полями:

1. Название (которое убрать нельзя, т.к. оно является обязательным);
2. Начало активности (чтобы видеть, когда была заполнена форма);
3. И наконец сами поля формы.

При этом в этих настройках желательно ставить галочку «установить данные настройки по умолчанию для всех пользователей», потому как вряд ли кому-то понадобится отличное отображение (если понадобится — поменяют), а тем более стандартное.

Теперь нужно создать инфоблок шаблонов e-mail писем, что является не обязательным, если нет нужды отправлять e-mail уведомления, — просто проигнорируйте дальнейшие инструкции и перейдите к «настройке компонента».

1. Переходим к типу инфобоков «формы» и создаём новый инфоблок, желательно с символьным кодом: «email_templates» (можно указать и другой символьный код, но этот будет определяться по-умолчанию в настройках компонента);
2. Устанавливаем название инфоблока, например: «Шаблоны E-Mail»;
3. Перейдите во вкладку «доступ» и поставьте «для всех пользователей» — «чтение»;
4. Во вкладке «поля» делаем поле «символьный код» обязательным с помощью галочки, а справа от него ставим галочку «проверять на уникальность»;
5. Перейдите во вкладку «свойства» и укажите там 4-е обязательных свойства:
  4.1. «От кого» (строка), символьный код: «from»;
  4.2. «Кому» (строка), символьный код: «to»;
  4.3. «Тема письма» (строка), символьный код: «subject»;
  4.4. «Тело письма» (HTML/текст), символьный код: «body»;
  Символьные коды должны быть именно такими, какими указаны в списке выше;
6. Сохраняем инфоблок.

Можно также настроить отображение списка элементов этого блока, убрав лишние и добавив что-нибудь из 4-ёх добавленных свойств, но определяющим на этот раз будет «название».

Переходим к добавлению нового элемента в этом инфоблоке, заходим в настройки отображения. Удаляем всё возможное, все лишние вкладки кроме одной. На этой одной вкладке оставляем следующие поля:

1. «Название»;
2. «Символьный код»;
3. «Детальное описание» (не обязательно, в нём можно оставлять вспомогательные комментарии, например список возможных хеш-тегов для автозамены);
4. «От кого»;
5. «Кому»;
6. «Тема письма»;
7. «Тело письма».

В зависимости от того, выберите вы тип «текст» или «HTML», — будет зависеть тип отправляемого письма (Content-Type будет установлен в text/plain или text/html), данные полей будут соответствующим образом преобразованы.

Далее приведён список хеш-тегов автозамены:

1. #DOMAIN_NAME# — доменное имя сайта (например: domain.org);
2. #SITE_NAME# — наименование сайта, берётся из настроек сайта («администрирование» → «настройки» → «настройка продукта» → «сайты» → «список сайтов» → ваш сайт, в настройках которого имеется поле «название веб-сайта»);
3. #EMAIL_FROM# — адрес отправителя писем (устанавливается в настройках компонента);
4. #ADMIN_EMAIL# — адрес администратора, которому отправляются уведомления (устанавливается в настройках компонента);
5. #HIDDEN_COPY_ADMIN# — адрес для скрытой копии письма администратору (устанавливается в настройках компонента);
6. #HIDDEN_COPY_USER# — адрес для скрытой копии письма заполнившему форму (устанавливается в настройках компонента);
7. В остальном идут хеш-теги для полей формы следующего вида: #TITLE_code# — для названия поля, и #VALUE_code# — для значения поля, где «code» — символьный код поля (свойства инфоблока).

Чтобы было понятнее, — рассмотрим пример шаблона уведомления администратору. Будем предполагать что в инфоблоке формы у нас имеются следующие свойства:

1. «Имя» (строка), символьный код: «name»;
2. «E-Mail» (строка) символьный код: «email»;
3. «Сообщение» (HTML/текст), символьный код: «message»;

А теперь создаём новый шаблон (добавляем новый элемент в инфоблок шаблонов e-mail), заполняя поля:

1. «Название» = «Уведомление администратору о заполнении формы»;
2. «Символьный код» = «admin_email_template»;
3. «Детальное описание» — сюда можно скопировать список доступных хеш-тегов (если вы вообще оставили это поле, т.к. оно не обязательное);
4. «От кого» = «#EMAIL_FROM#»;
5. «Кому» = «#ADMIN_EMAIL#»;
6. «Тема письма» = «Некто #VALUE_name# заполнил форму»;
7. «Тело письма»:

        <h1>#SITE_NAME#</h1>

        <h2>Запись на приём к специалисту</h2>

        <dl>
            <dt>#TITLE_name#</dt>
            <dd>#VALUE_name#</dd>

            <dt>#TITLE_email#</dt>
            <dd>#VALUE_email#</dd>

            <dt>#TITLE_message#</dt>
            <dd>#VALUE_message#</dd>
        </dl>

        <p>Это письмо отправлено автоматически, отвечать на него не нужно. С вопросами обращаться по этому адресу: #ADMIN_EMAIL#</p>

Настройка компонента
====================

1. Добавляем компонент на выбранную вам страницу, на всякий случай сбросив кеш компонентов (он находится в разделе «служебные» под именем «продвинутая форма»);
2. В разделе «инфоблок с результатами» выбираем инфоблок формы, можно снять галочку «сохранять результаты формы в инфоблок», если это не нужно, но сам инфоблок всё-равно нужно указывать (из него берутся поля формы);
3. В разделе «уведомления на e-mail» указываем e-mail адреса (если e-mail уведомления не нужны, просто ничего в этом разделе не трогаем), — отправителя писем, администратора, скрытые копии (если нужно), все e-mail-ы, в т.ч. и в форме могут быть двух типов: простой e-mail адрес (например: vasia@domain.ru), и e-mail с указанием имени (например: Василий Иванов <vasia@domain.ru>), также можно указывать по нескольку адресов через запятую (за исключением отправителя и e-mail-а из формы, если убрана соответствующая галочка). Ниже выбираем инфоблок шаблонов e-mail писем и сами шаблоны;
4. В последнем разделе «дополнительные настройки» есть поле «уникальная „соль“ формы», — его лучше заполнять, хоть это и не обязательно. Обязательно в том случае, когда на одной странице находятся 2-е формы и более, тогда для каждой формы должна быть уникальная «соль», в которой могут быть совершенно произвольные символы, или например просто имя формы.

Поддерживаемые типы полей
=========================

1. Строка;
2. Число;
3. Список;
4. Текстовое поле;

Книга жалоб и предложений
=========================

https://github.com/unclechu/bitrix-advanced-form/issues
