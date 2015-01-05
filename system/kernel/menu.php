<?php

/**
 * ================================================================================
 *  CONVERT ARRAY INTO HTML LIST WITH LINKS
 * ================================================================================
 *
 * -- CODE: -----------------------------------------------------------------------
 *
 *    $array = array(
 *        'Example 1' => '/',
 *        'Example 2' => '#example',
 *        'Example 3' => '/example',
 *        'Example 4' => array(
 *            'Example 4.1' => '/example/example'
 *        ),
 *        'Example 5 (/parent)' => array(
 *            'Example 5.1' => '/parent/children-1',
 *            'Example 5.2' => '/parent/children-2'
 *        )
 *    );
 *
 *    echo Menu::get($array, 'ul');
 *
 * -- RESULT: ---------------------------------------------------------------------
 *
 *    <ul>
 *      <li><a href="http://example.com">Example 1</a></li>
 *      <li><a href="#example">Example 2</a></li>
 *      <li><a href="http://example.com/example">Example 3</a></li>
 *      <li><a href="#">Example 4</a>
 *        <ul class="children-1">
 *          <li><a href="http://example.com/example/example">Example 4.1</a></li>
 *        </ul>
 *      </li>
 *      <li><a href="http://example.com/parent">Example 5</a>
 *        <ul class="children-1">
 *          <li><a href="http://example.com/parent/children-1">Example 5.1</a></li>
 *          <li><a href="http://example.com/parent/children-2">Example 5.2</a></li>
 *        </ul>
 *      </li>
 *    </ul>
 *
 * --------------------------------------------------------------------------------
 *
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *  Parameter      | Type    | Description
 *  -------------- | ------- | ----------------------------------------------------
 *  $array         | array   | Array of menu
 *  $type          | string  | The list type ... `<ul>` or `<ol>` ?
 *  $filter_prefix | string  | Filter prefix for the generated HTML output
 *  $depth         | integer | Starting depth
 *  -------------- | ------- | ----------------------------------------------------
 * ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
 *
 */

class Menu {

    public static $config = array(
        'classes' => array(
            'selected' => 'selected',
            'children' => 'children-%s'
        )
    );

    protected static function create($array = null, $type = 'ul', $filter_prefix = 'menu:', $depth = 0) {
        $config = Config::get();
        $speak = Config::speak();
        $current = $config->url_current;
        // Use menu file from the cabinet if `$array` is not defined
        if(is_null($array)) {
            if($file = File::exist(STATE . DS . 'menus.txt')) {
                $array = Text::toArray(File::open($file)->read());
            } else {
                $array = array($speak->home => '/', $speak->about => '/about');
            }
            $filter_prefix = 'navigation:';
        }
        $html = str_repeat(TAB, $depth) . '<' . $type . ($depth > 0 ? ' class="' . sprintf(self::$config['classes']['children'], $depth) . '"' : "") . '>' . NL;
        foreach($array as $text => $url) {
            if(is_array($url)) {
                if(preg_match('#(.*?)\((.*?)\)$#', $text, $matches)) {
                    $_url = trim($matches[2], '/');
                    // Create full URL from value if the value does not contain a `://`
                    if(strpos($_url, '://') === false && strpos($_url, '#') !== 0) {
                        $_url = str_replace('/#', '#', rtrim($config->url . '/' . $_url, '/'));
                    }
                    $html .= Filter::apply($filter_prefix . 'list.item', str_repeat(TAB, $depth + 1) . '<li' . ($_url == $current || ($_url != $config->url && strpos($current . '/', $_url . '/') === 0) ? ' class="' . self::$config['classes']['selected'] . '"' : "") . '><a href="' . $_url . '">' . trim($matches[1]) . '</a>' . NL . self::create($url, $type, $filter_prefix, $depth + 1) . str_repeat(TAB, $depth + 1) . '</li>' . NL);
                } else {
                    $_url = $config->url . '#';
                    $html .= Filter::apply($filter_prefix . 'list.item', str_repeat(TAB, $depth + 1) . '<li' . ($_url == $current || ($_url != $config->url && strpos($current . '/', $_url . '/') === 0) ? ' class="' . self::$config['classes']['selected'] . '"' : "") . '><a href="#">' . $text . '</a>' . NL . self::create($url, $type, $filter_prefix, $depth + 1) . str_repeat(TAB, $depth + 1) . '</li>' . NL);
                }
            } else {
                // Create full URL from value if the value does not contain a `://`
                if(strpos($url, '://') === false && strpos($url, '#') !== 0) {
                    $url = str_replace('/#', '#', trim($config->url . '/' . trim($url, '/'), '/'));
                }
                $html .= Filter::apply($filter_prefix . 'list.item', str_repeat(TAB, $depth + 1) . '<li' . ($url == $current || ($url != $config->url && strpos($current . '/', $url . '/') === 0) ? ' class="' . self::$config['classes']['selected'] . '"' : "") . '><a href="' . $url . '">' . $text . '</a></li>' . NL);
            }
        }
        return Filter::apply($filter_prefix . 'list', $html . str_repeat(TAB, $depth) . '</' . $type . '>' . NL);
    }

    public static function get($array = null, $type = 'ul', $filter_prefix = 'menu:', $depth = 0) {
        return O_BEGIN . rtrim(self::create($array, $type, $filter_prefix, $depth), NL) . O_END;
    }

    public static function configure($key, $value = null) {
        if(is_array($key)) {
            self::$config = array_replace_recursive(self::$config, $key);
        } else {
            if(is_array($value)) {
                foreach($value as $k => $v) {
                    self::$config[$key][$k] = $v;
                }
            } else {
                self::$config[$key] = $value;
            }
        }
        return new static;
    }

}