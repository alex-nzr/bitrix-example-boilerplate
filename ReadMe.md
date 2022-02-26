## Структура
Для всех проектов на Битриксе используется следующий подход:
1. Размещение своих классов, которые не относятся к модулям, в /local/php_interface/classes
2. При необходимости размещение файлов, которые нужно подключить к проекту (например constants.php)   
3. Автозагрузка файлов и классов через composer   
4. Подключение обработчиков событий через класс EventCollector (см. init.php)
5. В `init.php` в идеале находится только подключение `autoload.php` и вызов `EventCollector::bindEvents()`


## Роутинг и собственное rest api
Чтобы работал роутинг, нужно подключить его в файле /bitrix/.settings.php,
а сами роуты описаны в файле /local/routes/api.php. [Документация](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&CHAPTER_ID=013764&LESSON_PATH=3913.3516.5062.13764).

Конфигурация `.htaccess` будет уникальной для каждого проекта, в зависимости от структуры и задач.
В данном образце все запросы к директории /api/ обрабатывает новый роутер Битрикса,
а остальное работает, как и раньше — через urlrewrite.php

Модуль `mycompany.example` нужен для регистрации и подключения контроллеров. Его необходимо предварительно установить через админку.
В исходниках модуля регистрация контроллеров происходит в файле .settings.php, а сами контроллеры лежат в папке lib/controllers.
В примере реализовано четыре базовых метода CRUD для инфоблоков. Они далеко не идеальны и метод `add`
поддерживает не все типы свойств (думал передавать картинки в base64, но пока не дописал метод для их обработки).
Это сделано просто в качестве примера. Для реального проекта методы потребуют доработки.
В корневом index.php есть закомментированные примеры работы с api через штатный HttpClient.


## Настройка и расширение стандартного rest api для инфоблоков.
1. Заполнить "Символьный код API" у нужного инфоблока. В данном примере это будет `apiNews`. Поставить рядом галочку "Включен доступ через REST"
2. Создать класс-контроллер путём наследования стандартного контроллера Битрикса для инфоблоков и переопределить в нём ряд методов.
```
class NewsIBlockController extends \Bitrix\Iblock\Controller\DefaultElement{
    protected function getDefaultPreFilters(): array
    {
        return [];
    }

    //метод определяющий какие поля разрешены для запроса
    public static function getAllowedList(): array
    {
        return ['ID', 'IBLOCK_ID', "DETAIL_TEXT", "DETAIL_PICTURE", 'NAME', "PICS_NEWS"];
    }
}
```
Этот класс необходимо подключить в init.php

3. Создать сервис-локатор и зарегистрировать в нём свой контроллер. Можно сделать в отдельном файле, который потом подключить в init.php.
Подробнее о сервис-локаторах и способах регистрации [тут](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=43&LESSON_ID=14032)
Стоит обратить внимание на то, как формируется имя регистрируемого сервиса - `iblock.element.тутAPIкодИнфоблока.rest.controller`   
```
    $serviceName = 'iblock.element.apiNews.rest.controller';
    $serviceConfig = [
        'constructor' => static function () {
            return new NewsIBlockController();
        },
    ];

    $serviceLocator = ServiceLocator::getInstance();

    try {
        $serviceLocator->addInstanceLazy($serviceName, $serviceConfig);
        if ($serviceLocator->has('iblock.element.apiNews.rest.controller'))
        {
            //проверка успешной регистрации сервиса
            $newsService = $serviceLocator->get('iblock.element.apiNews.rest.controller');
            AddMessage2Log(print_r($newsService->getElementEntityAllowedList(), true));
        }
    }
    catch (Exception $e) {
        AddMessage2Log($serviceName ." error - " . $e->getMessage());
    }
```

4. Создать вебхук с доступом к модулю iblock и можно делать запросы к rest api.
Пример запроса к инфоблоку apiNews:
```
https://myBitrix.com/rest/1/asdasf3bz547365nk/iblock.Element.get.json?iblockId=4&elementId=8&select[0]=ID&select[1]=DETAIL_TEXT&select[2]=NAME
```
Путь до `iblock.Element.get` будет в настройках вебхука.

5. Это пункт актуален, если нужно настроить rest api на БУС, где нет интерфейса для создания вебхуков.
Чтобы выйти из данной ситуации, нужно создать в публичной части страницу с компонентом bitrix:rest.hook.
В примере ниже компонент размещен по адресу `/rest-hook/index.php`    
```
<?php require_once($_SERVER['DOCUMENT_ROOT'] . "/bitrix/modules/main/include/prolog_admin.php");
/**
 * @var CMain $APPLICATION
 */
?>
<?$APPLICATION->IncludeComponent(
	"bitrix:rest.hook",
	".default",
	Array(
		"COMPONENT_TEMPLATE" => ".default",
		"SEF_FOLDER" => "/rest-hook/",
		"SEF_MODE" => "Y",
		"SEF_URL_TEMPLATES" => [
		    "list"=>"",
		    "event_list"=>"event/",
		    "event_edit"=>"event/#id#/",
		    "ap_list"=>"ap/",
		    "ap_edit"=>"ap/#id#/",
		]
	)
);?>   
//чуть-чуть стилей, иначе кнопка будет просто как текст
      <style>
        .webform-button.webform-button-create{
            background: #025ea1;
            padding: 10px 15px;
            text-align: center;
            color:#fff;
            cursor: pointer;
        }
      </style>
<? require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_admin.php");
```
В некоторых мануалах пишут, что это работает и с обычным прологом, но у меня при подключении `prolog_before.php` вебхук не сохранялся - при нажатии "Сохранить" просто перезагружалась страница.
При подключении `prolog_admin.php` всё работает норм. Также плюс в том, что доступ к странице автоматически закрыт от тех, кто не имеет доступа в админку.
Важный момент по ЧПУ:
После создания страницы нужно её пересохранить в html режиме, чтобы перезаписались настройки ЧПУ. Именно страницу, а не компонент, так как при сохранении компонента, по неизвестной причине, раздел параметров "SEF_URL_TEMPLATES" превращается в кашу и компонент перестаёт работать. Может это снова прикол именно моего сайта)
Также можно обновить настройки ЧПУ вручную через админку в разделе "Обработка адресов".
В файле urlrewrite.php должно появиться примерно следующее:
```
4 => 
  array (
    'CONDITION' => '#^/rest-hook/#',
    'RULE' => '',
    'ID' => 'bitrix:rest.hook',
    'PATH' => '/rest-hook/index.php',
    'SORT' => 100,
  ),
```

После вышеперечисленных манипуляций по адресу /rest-hook/ap/0/ будет доступна форма создания входящего вебхука.

6. Чтобы добавить к штатному rest api свои методы, нужно подписаться на событие `OnRestServiceBuildDescription`
   и в обработчике указать параметры нового маршрута. Подробнее [тут](https://dev.1c-bitrix.ru/learning/course/index.php?COURSE_ID=99&LESSON_ID=7985&LESSON_PATH=8771.5380.7985)
```
AddEventHandler('rest', 'OnRestServiceBuildDescription', ['\NewsIBlockController', 'addCustomRestMethods']);
```   

В качестве обработчика используем класс-контроллер NewsIBlockController, уже созданный ранее.
Например, добавим возможность создавать элементы инфоблока через REST - для этого создадим в классе NewsIBlockController
следующие методы:
```
        //обработчик события `OnRestServiceBuildDescription`. 
        //В нём регистрируются методы rest api и указываются их обработчики
        public static function addCustomRestMethods(): array
        {
            return [
                'iblock' => [
                    'iblock.Element.add' => [
                        'callback' => [__CLASS__, 'iBlockElementAdd'],
                        'options' => [],
                    ],
                ],
            ];
        }

        //упрощённый  метод для добавления нового элемента
        public static function iBlockElementAdd($query, $nav, CRestServer $server): array
        {
            try {
                if ($query['error'])
                {
                    throw new \Bitrix\Rest\RestException(
                        'Message',
                        402,
                        \CRestServer::STATUS_PAYMENT_REQUIRED
                    );
                }

                if (!isset($query['iblockId']))
                {
                    throw new \Bitrix\Rest\RestException(
                        'IBLOCK_ID can not be empty',
                        400,
                        \CRestServer::STATUS_WRONG_REQUEST
                    );
                }

                if (!isset($query['fields']))
                {
                     throw new \Bitrix\Rest\RestException(
                        'Iblock fields can not be empty',
                        400,
                        \CRestServer::STATUS_WRONG_REQUEST
                     );
                }

                Bitrix\Main\Loader::includeModule('iblock');
                global $USER;
                $el = new CIBlockElement;

                $arFields = Array(
                    "MODIFIED_BY"    => $USER->GetID(),
                    "IBLOCK_SECTION_ID" => false,
                    "IBLOCK_ID"      => (int)$query['iblockId'],
                    "NAME"           => $query['fields']['NAME'],
                    "CODE"           => $query['fields']['CODE'],
                    "ACTIVE"         => "Y",
                );

                if($elId = $el->Add($arFields))
                    return ['result' => $elId];
                else{
                     throw new \Bitrix\Rest\RestException( $el->LAST_ERROR );
                }
            }
            catch (Exception $e){
                return [
                    'error' => $e->getCode(),
                    'error_description' => $e->getMessage()
                ];
            }
        }
```   
Теперь можно добавлять новые элементы инфоблоков через запрос вида:
https://myBitrix.com/rest/1/webHookToken/iblock.Element.add.json?iblockId=4&fields[NAME]=newELEM&fields[CODE]=new_elem

Аналогичным образом можно добавить любые другие операции с элементами инфоблоков.