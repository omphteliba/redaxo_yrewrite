<?php

/**
 * YRewrite.
 *
 * @author jan.kristinus@yakamara.de
 * @package redaxo\yrewrite
 *
 * @var rex_addon $this
 */

$table = rex_sql_table::get(rex::getTable('article'));
$urlTypeExists = $table->hasColumn('yrewrite_url_type');
$table
    ->ensureColumn(new rex_sql_column('yrewrite_url_type', "enum('AUTO','CUSTOM','REDIRECTION_INTERNAL','REDIRECTION_EXTERNAL')", false, 'AUTO'))
    ->ensureColumn(new rex_sql_column('yrewrite_url', 'text'), 'yrewrite_url_type')
    ->ensureColumn(new rex_sql_column('yrewrite_redirection', 'varchar(191)'), 'yrewrite_url')
    ->ensureColumn(new rex_sql_column('yrewrite_title', 'varchar(191)'), 'yrewrite_redirection')
    ->ensureColumn(new rex_sql_column('yrewrite_description', 'text'), 'yrewrite_title')
    ->ensureColumn(new rex_sql_column('yrewrite_changefreq', 'varchar(10)'), 'yrewrite_description')
    ->ensureColumn(new rex_sql_column('yrewrite_priority', 'varchar(5)'), 'yrewrite_changefreq')
    ->ensureColumn(new rex_sql_column('yrewrite_index', 'tinyint(1)'), 'yrewrite_priority')
    ->ensureColumn(new rex_sql_column('yrewrite_canonical_url', 'text'), 'yrewrite_index')
    ->alter()
;

if (!$urlTypeExists) {
    rex_sql::factory()->setQuery('UPDATE '.rex::getTable('article').' SET yrewrite_url_type = IF(yrewrite_url != "", "CUSTOM", "AUTO")');
}

$table = rex_sql_table::get(rex::getTable('yrewrite_domain'));
$table
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('domain', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('mount_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('start_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('notfound_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('clangs', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('clang_start', 'int(11)'))
    ->ensureColumn(new rex_sql_column('clang_start_auto', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('clang_start_hidden', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('robots', 'text'))
    ->ensureColumn(new rex_sql_column('title_scheme', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('description', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('auto_redirect', 'tinyint(1)'))
    ->ensureColumn(new rex_sql_column('auto_redirect_days', 'int(3)'))
    ->ensureIndex(new rex_sql_index('domain', ['domain'], rex_sql_index::UNIQUE))
    ->ensure();

$table = rex_sql_table::get(rex::getTable('yrewrite_alias'));
$table
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('alias_domain', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('domain_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('clang_start', 'int(11)'))
    ->ensureIndex(new rex_sql_index('alias_domain', ['alias_domain'], rex_sql_index::UNIQUE))
    ->ensure();

$table = rex_sql_table::get(rex::getTable('yrewrite_forward'));
$table
    ->ensurePrimaryIdColumn()
    ->ensureColumn(new rex_sql_column('domain_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('status', 'int(11)'))
    ->ensureColumn(new rex_sql_column('url', 'varchar(512)'))
    ->ensureColumn(new rex_sql_column('type', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('article_id', 'int(11)'))
    ->ensureColumn(new rex_sql_column('clang', 'int(11)'))
    ->ensureColumn(new rex_sql_column('extern', 'varchar(512)'))
    ->ensureColumn(new rex_sql_column('media', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('movetype', 'varchar(191)'))
    ->ensureColumn(new rex_sql_column('expiry_date', 'date'))
    //->ensureIndex(new rex_sql_index('domain_id_url', ['domain_id', 'url'], rex_sql_index::UNIQUE))
    ->ensure();

$c = rex_sql::factory();
$c->setQuery('ALTER TABLE `' . rex::getTable('yrewrite_domain') . '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
$c->setQuery('ALTER TABLE `' . rex::getTable('yrewrite_alias') . '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');
$c->setQuery('ALTER TABLE `' . rex::getTable('yrewrite_forward') . '` CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;');

rex_delete_cache();

if (!class_exists('rex_yrewrite_settings')) {
    require_once 'lib/yrewrite/settings.php';
}
rex_yrewrite_settings::install();
