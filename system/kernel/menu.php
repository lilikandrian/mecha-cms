<?php

/**
 * ====================================================================
 *  CONVERT ARRAY INTO HTML LIST WITH/WITHOUT LINK(S)
 * ====================================================================
 *
 * -- CODE: -----------------------------------------------------------
 *
 *    $array = array(
 *        'Example 1' => '/',
 *        'Example 2' => '#example',
 *        'Example 3' => '/example',
 *        'Example 4' => array(
 *            'Example 4.1' => '/example/example'
 *        ),
 *        'Example 5 (/parent)' => array(
 *            'Example 5.1' => '/parent/child-1',
 *            'Example 5.2' => '/parent/child-2'
 *        ),
 *        '|',
 *        'Text 1',
 *        'Text 2' => null
 *    );
 *
 *    echo Menu::get($array, 'ul');
 *
 * --------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter | Type   | Description
 *  --------- | ------ | ----------------------------------------------
 *  $array    | array  | Array of menu item
 *  $type     | string | The list type ... `<ul>` or `<ol>` ?
 *  $depth    | string | Depth extra before each list group/list item
 *  $FP       | string | Filter prefix for the generated HTML output
 *  --------- | ------ | ----------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

class Menu extends Base {

    public static $config = array(
        'classes' => array(
            'current' => 'current',
            'parent' => false,
            'child' => 'child-%d',
            'separator' => 'separator'
        )
    );

    public static function create($array, $type = 'ul', $depth = "", $FP = "", $i = 0) {
        $c_url = Config::get('url');
        $c_url_current = Config::get('url_current');
        $c_class = self::$config['classes'];
        $html = $depth . str_repeat(TAB, $i) . '<' . $type . ($i > 0 ? ($c_class['child'] !== false ? ' class="' . sprintf($c_class['child'], $i / 2) . '"' : "") : ($c_class['parent'] !== false ? ' class="' . $c_class['parent'] . '"' : "")) . '>' . NL;
        foreach($array as $key => $value) {
            if( ! is_array($value)) {
                // List item separator: `array('|')`
                if($key === '|' || is_int($key) && $value === '|') {
                    $html .= Filter::apply($FP . 'list.item.separator', Filter::apply($FP . 'list.item', $depth . str_repeat(TAB, $i + 1) . '<li class="' . $c_class['separator'] . '"></li>' . NL, $i + 1), $i + 1);
                // List item without link: `array('foo')`
                } else if(is_int($key)) {
                    $html .= Filter::apply($FP . 'list.item', $depth . str_repeat(TAB, $i + 1) . '<li><span class="a">' . $value . '</span></li>' . NL, $i + 1);
                // List item without link: `array('foo' => null)`
                } else if(is_null($value)) {
                    $html .= Filter::apply($FP . 'list.item', $depth . str_repeat(TAB, $i + 1) . '<li><span class="a">' . $key . '</span></li>' . NL, $i + 1);
                // List item with link: `array('foo' => '/')`
                } else {
                    $value = Filter::apply('menu:url', Filter::apply('url', Converter::url($value)));
                    $html .= Filter::apply($FP . 'list.item', $depth . str_repeat(TAB, $i + 1) . '<li' . ($value === $c_url_current || ($value !== $c_url && strpos($c_url_current . '/', $value . '/') === 0) ? ' class="' . $c_class['current'] . '"' : "") . '><a href="' . $value . '">' . $key . '</a></li>' . NL, $i + 1);
                }
            } else {
                if(preg_match('#(.*?)\s*\((.*?)\)\s*$#', $key, $matches)) {
                    $_key = $matches[1];
                    $_value = Converter::url($matches[2]);
                } else {
                    $_key = $key;
                    $_value = '#';
                }
                $_value = Filter::apply('menu:url', Filter::apply('url', $_value));
                $html .= Filter::apply($FP . 'list.item', $depth . str_repeat(TAB, $i + 1) . '<li' . ($_value === $c_url_current || ($_value !== $c_url && strpos($c_url_current . '/', $_value . '/') === 0) ? ' class="' . $c_class['current'] . '"' : "") . '>' . NL . str_repeat(TAB, $i + 2) . '<a href="' . $_value . '">' . $_key . '</a>' . NL . self::create($value, $type, $depth, $FP, $i + 2) . $depth . str_repeat(TAB, $i + 1) . '</li>' . NL, $i + 1);
            }
        }
        return Filter::apply($FP . 'list', rtrim($html, NL) . ( ! empty($array) ? NL . $depth . str_repeat(TAB, $i) : "") . '</' . $type . '>' . NL, $i);
    }

    public static function get($array = null, $type = 'ul', $depth = "", $FP = "") {
        return O_BEGIN . rtrim(self::create($array, $type, $depth, $FP, 0), NL) . O_END;
    }

}