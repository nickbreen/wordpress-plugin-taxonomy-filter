<?php

namespace nz\net\foobar\wp;

class WalkerTaxonomyFilter extends WalkerTaxonomy
{
    // '+' works as AND, ',' works as OR/IN
    const DELIM_ANY = ',';
    const DELIM_ALL = '+';

    private $delimiter;

    public function __construct($tax, $anyAll)
    {
        parent::__construct($tax);
        $this->delimiter = 'any' == $anyAll ? self::DELIM_ANY : self::DELIM_ALL;
    }

    /**
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     * @SuppressWarnings("CamelCase")
     */
    protected function link($term, $args = array())
    {
        global $wp_query;
        $tax = get_taxonomy($term->taxonomy);
        $slugs = array_filter(explode($this->delimiter, $wp_query->get($tax->query_var)));
        $slugs = in_array($term->slug, $slugs) ?
            array_diff($slugs, array($term->slug)) : array_merge($slugs, array($term->slug));
        $query = array_filter(array_merge(
            array_intersect_key($wp_query->query_vars, array_flip(array('s', $tax->query_var))),
            array($tax->query_var => implode($this->delimiter, $slugs))
        ));
        return trim(sprintf('%s?%s', $args['term'] ? get_term_link($args['term']) : '/', build_query($query)), '?');
    }
}
