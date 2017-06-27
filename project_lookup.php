<?php
error_reporting(E_ALL); // TODO: remove
require_once './app/functions.php';
require_once './app/evoapi.php';?>
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
                    <a class="mdl-navigation__link" href="/">Индексная страница</a>
                    <a class="mdl-navigation__link" href="https://github.com/u-mulder/ham2evo" target="_blank">Репозиторий проекта</a>
                </nav>
            </div>
        </header>
        <div class="mdl-layout__drawer">
            <span class="mdl-layout-title">Перенос часов в EVO</span>
            <nav class="mdl-navigation">
                <a class="mdl-navigation__link" href="/">Индексная страница</a>
                <a class="mdl-navigation__link" href="https://github.com/u-mulder/ham2evo" target="_blank">Репозиторий проекта</a>
            </nav>
        </div>
        <main class="mdl-layout__content">
            <div class="page-content" id="page-content">
<?php
$params = getConfigParams();
$o = new EvoApi($params);
$projects = $o->getProjects();
$tags = getTags();
$currentLookup = getCurrentLookUp();?>
                <table class="mdl-data-table mdl-js-data-table mdl-shadow--1dp" style="width:98%; margin: 5px auto;" id="records_table">
                    <thead>
                        <tr>
                            <th class="mdl-data-table__cell--non-numeric">Название</th>
                            <th class="mdl-data-table__cell--non-numeric">Проект в EVO</th>
                            <th class="mdl-data-table__cell--non-numeric">&nbsp;</th>
                        </tr>
                    </thead>
                    <tbody>
<?php
foreach ($tags->records as $k => $v) {?>
                        <tr>
                            <td class="mdl-data-table__cell--non-numeric"><?=$v?></td>
                            <td class="mdl-data-table__cell--non-numeric">
                                <select name="" id="tag_<?=$k?>_selector" class="js-selector-lookup" data-tag-id="<?=$k?>">
                                    <option value="0">Выберите значение</option>
<?php
    $selected = !empty($currentLookup[$k]) ? $currentLookup[$k] : false;
    foreach ($projects as $p) {?>
                                    <option value="<?=$p->id?>"<?=$p->id == $selected? ' selected' : ''?>><?=$p->title?></option>
<?php
    }?>
                                </select>
                            </td>
                            <td class="mdl-data-table__cell--non-numeric">
                                <button class="mdl-button mdl-js-button js-set-lookup" id="tag_<?=$k?>" data-tag-id="<?=$k?>" style="width:60px;">
                                    <i class="material-icons">send</i>
                                </button>
                                <div class="mdl-tooltip mdl-tooltip--large" for="tag_<?=$k?>">Обновить соответствие</div>
                                <div class="mdl-spinner mdl-js-spinner is-active" style="display:none" id="tag_<?=$k?>_spinner"></div>
                            </td>
                        </tr>
<?php
}?>
                    </tbody>
                </table>
            </div>
        </main>
    </div>
    <script defer src="./assets/material.min.js"></script>
    <script src="./assets/jquery-3.2.1.min.js"></script>
    <script type="text/javascript" src="./assets/script.js"></script>
</body>
</html>
