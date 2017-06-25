<?php
error_reporting(E_ALL); // TODO: remove
require_once './app/functions.php';?>
<!DOCTYPE html>
<html>
<head>
    <title>Перенос часов в EVO</title>
    <meta http-equiv="content-type" content="text/html;charset=utf-8" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="https://code.getmdl.io/1.3.0/material.indigo-pink.min.css">
</head>

<body>
    <div class="mdl-layout mdl-js-layout mdl-layout--fixed-header">
        <header class="mdl-layout__header">
            <div class="mdl-layout__header-row">
                <span class="mdl-layout-title">Перенос часов в EVO</span>
                <div class="mdl-layout-spacer"></div>
                <nav class="mdl-navigation mdl-layout--large-screen-only">
                    <a class="mdl-navigation__link" href="https://github.com/u-mulder/ham2evo" target="_blank">Репозиторий проекта</a>
                    <!-- a class="mdl-navigation__link" href="/project_lookup.php" target="_blank">Справочник соответствий проектов</a -->
                </nav>
            </div>
        </header>
        <div class="mdl-layout__drawer">
            <span class="mdl-layout-title">Перенос часов в EVO</span>
            <nav class="mdl-navigation">
                <a class="mdl-navigation__link" href="https://github.com/u-mulder/ham2evo" target="_blank">Репозиторий проекта</a>
                <!-- a class="mdl-navigation__link" href="/project_lookup.php" target="_blank">Справочник соответствий проектов</a -->
            </nav>
        </div>
        <main class="mdl-layout__content">
            <div class="page-content" id="page-content">
<?php
$err = checkConfigFile();
if ($err) {
    $keys = getConfigKeys();?>
                <div class="demo-card-wide mdl-card mdl-shadow--2dp" style="width:550px;margin: 10px auto;" id="settings">
                    <div class="mdl-card__title">
                        <h2 class="mdl-card__title-text">Установка параметров</h2>
                    </div>
                    <div class="mdl-card__supporting-text">
                        Ошибка при работе с конфигурационным файлом "config.json": <b><?=$err?></b><br/>
                        Исправьте ошибку самомстоятельно или создайте файл заново, заполнив форму:
                    </div>
                    <form id="settings_form" action="/handler.php" method="post">
<?php
    foreach ($keys as $k => $v) {?>
                        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width:480px; margin:0 20px;">
                            <input class="mdl-textfield__input" type="text" id="<?=$k?>" name="config[<?=$k?>]" value="<?=!empty($v['default']) ? $v['default'] : ''?>">
                            <label class="mdl-textfield__label" for="<?=$k?>"><?=$v['caption']?></label>
                            <span class="mdl-textfield__error">Укажите значение</span>
                        </div>
<?php
    }?>
                        <input type="submit" name="save-settings" value="Сохранить настройки" class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" style="margin:0 10px 10px;" />
                        <div class="mdl-spinner mdl-js-spinner is-active" style="display:none" id="settings_spinner"></div>
                    </form>
                </div>
<?php
}?>
                <div class="mdl-card mdl-shadow--2dp" style="width:550px;margin: 10px auto;<?=$err ? ' display:none;' : ''?>" id="filter">
                    <div class="mdl-card__title">
                        <h2 class="mdl-card__title-text">Временной период</h2>
                    </div>
                    <div class="mdl-card__supporting-text">
                        Укажите временной период, за который следует вывести записи:
                    </div>
                    <form id="filter_form" action="/handler.php" method="post">
                        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width:480px; margin:0 20px;">
                            <input class="mdl-textfield__input" type="text" id="dateFrom" name="filter[dateFrom]" value="" placeholder="dd.mm.yyyy" maxlength="10">
                            <label class="mdl-textfield__label" for="dateFrom">Начало периода</label>
                            <span class="mdl-textfield__error">Введите хотя бы одну из дат</span>
                        </div>
                        <div class="mdl-textfield mdl-js-textfield mdl-textfield--floating-label" style="width:480px; margin:0 20px;">
                            <input class="mdl-textfield__input" type="text" id="dateTo" name="filter[dateTo]" value="" placeholder="dd.mm.yyyy" maxlength="10">
                            <label class="mdl-textfield__label" for="dateTo">Конец периода</label>
                            <span class="mdl-textfield__error">Введите хотя бы одну из дат</span>
                        </div>
                        <input type="submit" name="filter-records" value="Вывести записи" class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" style="margin:0 10px 10px;" />
                        <div class="mdl-spinner mdl-js-spinner is-active" style="display:none" id="filter_spinner"></div>
                    </form>
                </div>
                <div class="mdl-card mdl-shadow--2dp" style="width:550px;margin: 10px auto; display:none; min-height:150px;" id="filter_errors">
                    <div class="mdl-card__title">
                        <h2 class="mdl-card__title-text">Ошибки получения записей</h2>
                    </div>
                    <ul class="mdl-list" id="filter_errors_list"></ul>
                    <button id="filter_retry" class="mdl-button mdl-button--colored mdl-js-button mdl-js-ripple-effect" style="margin:0 10px 10px;" />
                        Попробовать еще раз
                    </button>
                </div>

                <div class="demo-card-wide mdl-card mdl-shadow--2dp" style="width:95%;margin: 10px auto; display:none;" id="records">
                    <div class="mdl-card__title">
                        <h2 class="mdl-card__title-text">Записи</h2>
                    </div>
                    <div class="mdl-card__supporting-text" id="records_none">
                        Записей за данный период <b>не найдено</b>.
                        <button class="mdl-button mdl-js-button" id="new_filter" style="width:50px;">
                            <i class="material-icons">redo</i>
                        </button>
                        <div class="mdl-tooltip" for="new_filter">Новый поиск</div>
                    </div>
                    <table class="mdl-data-table mdl-js-data-table mdl-shadow--1dp" style="width:98%; margin: 5px auto;" id="records_table">
                        <thead>
                            <tr>
                                <th>
                                    <label class="mdl-checkbox mdl-js-checkbox mdl-js-ripple-effect mdl-data-table__select" for="table-header">
                                        <input type="checkbox" id="table-header" class="mdl-checkbox__input" />
                                    </label>
                                </th>
                                <th class="mdl-data-table__cell--non-numeric">Формулировка</th>
                                <th class="mdl-data-table__cell--non-numeric">Дата</th>
                                <th class="mdl-data-table__cell--non-numeric">
                                    <span id="hours">Затраченные часы</span>
                                    <div class="mdl-tooltip mdl-tooltip--large" for="hours">(Округление / Секунды)</div>
                                </th>
                                <th class="mdl-data-table__cell--non-numeric">Комментарий</th>
                                <th class="mdl-data-table__cell--non-numeric">Проект</th>
                            </tr>
                        </thead>
                        <tfoot>
                            <tr>
                                <td colspan="6">
                                    <div class="mdl-layout-spacer"></div>
                                    <span>Общее количество часов: <b id="hours_total"></b></span>&nbsp;&nbsp;&nbsp;
                                    <button  class="mdl-button mdl-js-button" id="send_records" style="width:60px;">
                                        <i class="material-icons">publish</i>
                                    </button>
                                    <div class="mdl-tooltip mdl-tooltip--large" for="send_records">Отправить в EVO</div>
                                </td>
                            </tr>
                        </tfoot>
                        <tbody></tbody>

                    </table>
                </div>

                <div id="toast" class="mdl-js-snackbar mdl-snackbar">
                    <div class="mdl-snackbar__text"></div>
                    <button class="mdl-snackbar__action" type="button"></button>
                </div>
            </div>
        </main>
    </div>
    <script defer src="./assets/material.min.js"></script>
    <script src="./assets/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="./assets/script.js"></script>
</body>
</html>
