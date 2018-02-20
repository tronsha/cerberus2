<?php

$header = <<<'EOF'
Cerberus IRCBot
Copyright (C) 2008 - 2018 Stefan Hüsges

This program is free software; you can redistribute it and/or modify it
under the terms of the GNU General Public License as published by the Free
Software Foundation; either version 3 of the License, or (at your option)
any later version.

This program is distributed in the hope that it will be useful, but
WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY
or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License
for more details.

You should have received a copy of the GNU General Public License along
with this program; if not, see <http://www.gnu.org/licenses/>.
EOF;

return PhpCsFixer\Config::create()
    ->setRiskyAllowed(true)
    ->setRules(array(
        '@PSR2' => true,
        'array_syntax' => ['syntax' => 'short'],
        'blank_line_after_opening_tag' => true,
        'blank_line_before_statement' => true,
        'combine_consecutive_unsets' => true,
        'concat_space' => ['spacing' => 'one'],
        'declare_equal_normalize' => ['space' => 'single'],
        'declare_strict_types' => true,
        'function_typehint_space' => true,
        'header_comment' => ['header' => $header],
        'include' => true,
        'method_separation' => true,
        'no_alias_functions' => true,
        'no_empty_statement' => true,
        'no_leading_import_slash' => true,
        'no_leading_namespace_whitespace' => true,
        'no_unused_imports' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'no_short_echo_tag' => true,
        'no_whitespace_in_blank_line' => true,
        'ordered_imports' => true,
        'phpdoc_order' => true,
        'php_unit_construct' => true,
        'php_unit_dedicate_assert' => true,
        'php_unit_strict' => true,
        'no_mixed_echo_print' => ['use' => 'echo'],
        'single_quote' => true,
        'standardize_not_equals' => true,
        'strict_comparison' => true,
        'strict_param' => true,
        'whitespace_after_comma_in_array' => true,
        'yoda_style' => true,
    ))
    ->setFinder(
        PhpCsFixer\Finder::create()
            ->exclude('vendor')
            ->in(__DIR__)
    )
;
