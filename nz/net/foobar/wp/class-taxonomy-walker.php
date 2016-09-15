<?php

namespace nz\net\foobar\wp;

/**
 * @SuppressWarnings(PHPMD.CamelCasePropertyName)
 */
class WalkerTaxonomy extends \Walker
{
    public $tree_type = 'category';
    public $db_fields = array ('parent' => 'parent', 'id' => 'term_id');

    public function __construct($tax)
    {
        $this->tree_type = $tax;
    }

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
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function start_el(&$output, $term, $depth = 0, $args = array(), $termId = 0) // @codingStandardsIgnoreLine
    {
        $count = array_key_exists($term->term_id, $args['counts']) ? $args['counts'][$term->term_id] : 0;
        if ($count || !$args['instance']['hide_empty']) {
            $class = array();
            if (in_array($term, $args['current'])) {
                $class[] = 'current-cat';
            } elseif (in_array($term->term_id, array_map(function ($term) {
                return $term->parent;
            }, $args['current']))) {
                $class[] = 'current-cat-parent';
            } elseif (in_array($term, $args['ancestry'])) {
                $class[] = 'current-cat-ancestor';
            }
            $output .= sprintf(
                '<li class="%s" data-term-id="%s" data-term-slug="%s">',
                implode(' ', $class),
                $term->term_id,
                $term->slug
            );
            $output .= sprintf(
                '<a href="%s"%s>%s</a>',
                $this->link($term, $args),
                $args['instance']['rel'] ? sprintf(' rel="%s"', $args['instance']['rel']) : '',
                $term->name
            );
            if ($args['instance']['counts']) {
                $output .= sprintf('<span>&nbsp;(%d)</span>', $count);
            }
        }
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function link($term, $args = array())
    {
        return get_term_link($term);
    }

    /**
     * @SuppressWarnings(PHPMD.CamelCaseMethodName)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function end_el(&$output, $term, $depth = 0, $args = array()) // @codingStandardsIgnoreLine
    {
        $output .= '</li>';
    }
}
