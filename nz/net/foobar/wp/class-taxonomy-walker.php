<?php

namespace nz\net\foobar\wp;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 * @SuppressWarnings(PSR1.Methods.CamelCapsMethodName.NotCamelCaps)
 */
class WalkerTaxonomy extends \Walker
{
    public $tree_type = 'category';
    public $db_fields = array ('parent' => 'parent', 'id' => 'term_id');

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function start_lvl(&$output, $depth = 0, $args = array()) // @codingStandardsIgnoreLine
    {
        $output .= sprintf(
            '<ul class="%s">',
            '$ulClass'
        );
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function end_lvl(&$output, $depth = 0, $args = array()) // @codingStandardsIgnoreLine
    {
        $output .= '</ul>';
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function start_el(&$output, $term, $depth = 0, $args = array(), $termId = 0) // @codingStandardsIgnoreLine
    {
        $output .= sprintf(
            '<li class="%s"><a href="%s" class="%s">%s</a>%s',
            '', //$liClass
            '', // $aHref
            '', // $aClass
            $term->name,
            '' // $liContent
        );
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function end_el(&$output, $category, $depth = 0, $args = array()) // @codingStandardsIgnoreLine
    {
        $output .= '</li>';
    }
}
