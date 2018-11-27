<?php
$_REPORT = array();
$_REPORT['REPORT_CONSTRUCTOR'] = 'Конструктор отчетов';
$_REPORT['REPORT_PARAMS'] = 'Параметры отчета';
// Заголовки параметров отчета 
$_REPORT['CLASSIFIER'] = 'Классификатор';
$_REPORT['DATA_SOURCE'] = 'Источник данных';
$_REPORT['TIME_PERIODS'] = 'Периоды';
$_REPORT['SELECTED_REPORTS'] = 'Выбранные отчеты';
$_REPORT['REPORT_CREATION'] = 'Создать отчет';
$_REPORT['REPORTS_OUTPUT'] = 'Вывод отчетов';

// заголовок окон изменения параметров отчетов
$_REPORT['CHANGE_CLASSIFIER_SECTION'] = 'Выбрать раздел классификатора';
$_REPORT['CHANGE_DATA_SOURCE'] = 'Выбрать источник данных';
$_REPORT['SELECT_COUNTRIES'] = 'Страна';
$_REPORT['SELECT_REGION'] = 'Выбрать регион';
$_REPORT['CHANGE_PERIODS'] = 'Изменить периоды';
$_REPORT['AVAILABLE_REPORTS'] = 'Доступные отчеты';



// Кнопки параметров отчета
$_REPORT['BUTTON_CHANGE'] = "Изменить";

// элементы управления "Периоды"
$_REPORT['PERIOD_STEP'] = 'Шаг периода';
// элементы управления "Создание отчета"
$_REPORT['CREATE_REPORT'] = 'Создать отчет';
$_REPORT['SAVE_TO_EXCEL'] = 'Выгрузить отчет в файл Excel';
$_REPORT['LOAD_FOR_INDICATORS'] = 'Сохранить, как данные для формирования индикаторов';



$_REPORT['STEP_ARRAY'] = array (
        0 => 'Без разбивки на периоды',
	1 => 'Месяц',
	3 => 'Квартал',
	6 => 'Погугодие',
	12 => 'Год',
);
$_REPORT['MULTIPLIER_ARRAY'] = array (
	'none' => ['val' => 1, 'text' => 'один'],
	'thousand' => ['val' => 1000, 'text' => 'тыс.'],
	'million' => ['val' => 1000000, 'text' => 'млн.'],
	'billion' => ['val' => 1000000000, 'text' => 'млрд.']
);


$_REPORT['REPORT'] = 'Отчет';
$_REPORT['REPORT_LIST'] = 'К списку отчетов';

$_REPORT['NEW_REPORT'] = 'Новый отчет';
$_REPORT['REPORT_TYPE'] = 'Тип отчета';
$_REPORT['REPORT_PROPERTIES'] = 'Свойства отчета';
$_REPORT['CHANGE_OTHER_FILTERS'] = 'Дополнительные фильтры';

$_REPORT['CHANGE_TIME_PERIODS'] = 'Изменение временных периодов';
$_REPORT['ADD_TIME_PERIOD'] = 'Добавить период';
$_REPORT['PERIOD_ERR'] = 'Не верно задан интервал периода';

$_REPORT['OTHER_PARAMS'] = 'Прочие параметры';

$_REPORT['CHANGE_REPORT_TYPE'] = 'Выберите тип отчета';
$_REPORT['NO_ADD_REPORTS'] = 'Отсутствуют выбранные отчеты';
$_REPORT['ALL_REPORTS_SELECTED'] = 'Выбраны все доступные отчеты';
$_REPORT['ADD_REPORT'] = 'Добавить отчет';

// Названия отчетов
$_REPORT['DEFAULT_REPORT'] = 'Без дополнительных параметров';
$_REPORT['REGIONS_REPORT'] = 'Региональное распределение';
$_REPORT['GROUPS'] = 'Группы производителей';
$_REPORT['MODELS_CONTRACTOR_REPORT'] = 'Модели по производителям';

$_REPORT['MMACHINES_REPORT'] = 'Распределение по видам машин';
$_REPORT['SELECT_SUB_CLASSIFIER'] ='Выберите подразделы классификатора';
$_REPORT['SELECTED_SUB_CLASSIFIER'] ='Выбранные подразделы';
$_REPORT['ALL_SUB_CLASSIFIER'] ='Все подразделы';


$_REPORT['MANUFACTURERS_LIST_ERROR'] = 'По заданным условиям не найдено ни одного производителя';



// Названия отчетов
$_REPORT['MANUFACTURERS_REPORT'] = 'Производители';
$_REPORT['MODELS_REPORT'] = 'Модели';
$_REPORT['MODELS_CLASSIFIER_REPORT'] = 'Модели по классификатору';
$_REPORT['ECONOMIC_REPORT'] = 'Численность и зарплата';
$_REPORT['FULL_REPORT'] = 'Отчет с портала';

// Модели


// заголовки
$_REPORT['CLASSIFIER_SECTION'] = 'Раздел классификатора';
$_REPORT['MODEL_SECTION'] = 'Модели';
$_REPORT['CONTRACTOR_SECTION'] = 'Производители';
$_REPORT['COMPANY_SECTION'] = 'Компания';
$_REPORT['DENOMINATION_SECTION'] = 'Наименование';

//  фильтры
$_REPORT['NO_NULLS'] = 'Не включать в отчет нулевые значения';
$_REPORT['NO_SPARES'] = 'Кроме производителей запчастей';
$_REPORT['RUSSIAN'] = 'Российское производство';
$_REPORT['FOREIGN'] = 'Зарубежное производство';
$_REPORT['ONLY_CLASSIFIER'] = 'Сокращенный отчет (скрыть модели)';
$_REPORT['ASSEMBLY'] = 'Сборочное производство';
$_REPORT['PRESENTS'] = 'Только присутствующие на портале';
$_REPORT['ALL_REGIONS_TOGETHER'] = 'Показать все выбранные страны как один регион';



$_REPORT['DEALERS_REPORT'] = 'Дилеры';
$_REPORT['REPORT_FORM'] = 'Выберите форму отчета';
$_REPORT['DIAGRAM'] = 'Диаграмма';

$_REPORT['UNITS'] = 'Единицы измерения';
$_REPORT['UNIT'] = 'Единица измерения';
$_REPORT['DATAAVERAGESALARY'] = 'Средняя заработная плата работников';
$_REPORT['DATAFOND'] = 'Фонд оплаты труда работников';
$_REPORT['DATAFONDALL'] = 'Фонд оплаты труда работников';
$_REPORT['DATAEMPLOYEES'] = 'Численность работников, чел.';
$_REPORT['DATAEMPLOYEESALL'] = 'Численность работников общая, чел.';



$_REPORT['PROPORTIONS'] = array (
	'proportion1' => 'Изм., %',
	'proportion2' => 'Изм. к нач., %',
	'proportion3' => 'Изм. 1го ко 2му., %'
);

