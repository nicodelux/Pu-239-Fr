<?php
/**
 * @param $entry
 *
 * @return mixed|string
 */
function searchfield($entry)
{
    static $drop_char_match = [
        '^',
        '$',
        '&',
        '(',
        ')',
        '<',
        '>',
        '`',
        '"',
        '|',
        ',',
        '@',
        '_',
        '?',
        '%',
        '-',
        '~',
        '+',
        '.',
        '[',
        ']',
        '{',
        '}',
        ':',
        '\\',
        '/',
        '=',
        '#',
        '\'',
        ';',
        '!',
        '+',
        '-',
        '|',
    ];
    static $drop_char_replace = [
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        '',
        ' ',
        ' ',
        ' ',
        ' ',
        '',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
        ' ',
    ];
    $entry = strip_tags(strtolower($entry));
    $entry = str_replace(' +', ' and ', $entry);
    $entry = str_replace(' -', ' not ', $entry);
    $entry = str_replace(' |', ' or ', $entry);
    //
    // Filter out strange characters like ^, $, &, change "it's" to "its"
    //
    for ($i = 0; $i < count($drop_char_match); ++$i) {
        $entry = str_replace($drop_char_match[$i], $drop_char_replace[$i], $entry);
    }

    return $entry;
}

/**
 * @param        $entry
 * @param string $mode
 *
 * @return array
 */
function split_words($entry, $mode = 'post')
{
    return explode(' ', trim(preg_replace('#\s+#', ' ', $entry)));
}

/**
 * @param       $searchstr
 * @param       $base_sql
 * @param       $where_search
 * @param array $add_where
 * @param bool  $strict
 *
 * @return array
 */
function search_text_in_db($searchstr, $base_sql, $where_search, $add_where = [], $strict = false)
{
    global $db, $config;
    //$stopword_array = @file($root_path . 'languages/lang_' . $config['default_lang'] . '/search_stopwords.txt');
    //$synonym_array = @file($root_path . 'languages/lang_' . $config['default_lang'] . '/search_synonyms.txt');
    $match_types = [
        'or',
        'not',
        'and',
    ];
    $add_where       = (count($add_where) ? ' AND ' . implode(' AND ', $add_where) : '');
    $cleansearchstr  = searchfield($searchstr);
    $lower_searchstr = strtolower($searchstr);
    if ($strict) {
        $split_search = [
            $lower_searchstr,
        ];
    } else {
        $split_search = split_words($cleansearchstr);
        if ($lower_searchstr != $searchstr) {
            $search_full_string = true;
            foreach ($match_types as $_null => $match_type) {
                if (strpos($lower_searchstr, $match_type) !== false) {
                    $search_full_string = false;
                }
            }
            if ($search_full_string) {
                $split_search[] = $lower_searchstr;
            }
        }
    }
    $word_count         = 0;
    $current_match_type = 'and';
    $word_match         = [];
    $result_list        = [];
    for ($i = 0; $i < count($split_search); ++$i) {
        if (strlen(str_replace([
                                   '*',
                                   '%',
                               ], '', trim($split_search[$i]))) < $config['search_min_chars'] && !in_array($split_search[$i], $match_types)) {
            $split_search[$i] = '';
            continue;
        }
        switch ($split_search[$i]) {
            case 'and':
                $current_match_type = 'and';
                break;

            case 'or':
                $current_match_type = 'or';
                break;

            case 'not':
                $current_match_type = 'not';
                break;

            default:
                if (!empty($search_terms)) {
                    $current_match_type = 'and';
                }
                if ($strict) {
                    $search = $where_search . ' = \'' . sqlesc($split_search[$i]) . '\'' . $add_where;
                } else {
                    $match_word = str_replace('*', '%', $split_search[$i]);
                    $search     = $where_search . ' LIKE \'%' . sqlesc($match_word) . '%\'' . $add_where;
                    //$search = $where_search . ' REGEXP \'[[:<:]]' . $db->sql_escape($match_word) . '[[:>:]]\'' . $add_where;
                }
                $sql    = $base_sql . ' WHERE ' . $search;
                $result = sql_query($sql);
                $row    = [];
                while ($temp_row = mysqli_fetch_row($result)) {
                    $row[$temp_row['id']] = 1;
                    if (!$word_count) {
                        $result_list[$temp_row['id']] = 1;
                    } elseif ($current_match_type === 'or') {
                        $result_list[$temp_row['id']] = 1;
                    } elseif ($current_match_type === 'not') {
                        $result_list[$temp_row['id']] = 0;
                    }
                }
                if ($current_match_type === 'and' && $word_count) {
                    @reset($result_list);
                    foreach ($result_list as $id => $match_count) {
                        if (!isset($row[$id]) || !$row[$id]) {
                            //$result_list[$id] = 0;
                            @$result_list[$id] -= 1;
                        } else {
                            @$result_list[$id] += 1;
                        }
                    }
                }
                ++$word_count;
                mysqli_fetch_assoc($result);
        }
    }
    @reset($result_list);
    $search_ids = [];
    foreach ($result_list as $id => $matches) {
        if ($matches > 0) {
            //if ( $matches ) {
            $search_ids[] = $id;
        }
    }
    unset($result_list);

    return $search_ids;
}
