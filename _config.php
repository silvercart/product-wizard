<?php
$dir       = __DIR__;
$sep       = DIRECTORY_SEPARATOR;
$moduleDir = str_replace([Director::baseFolder(), $sep], '', $dir);

Requirements::add_i18n_javascript("{$moduleDir}{$sep}client{$sep}javascript{$sep}lang");

$traitDisplayConditional = "{$dir}{$sep}src{$sep}Model{$sep}Wizard{$sep}DisplayConditional.php";
require_once $traitDisplayConditional;