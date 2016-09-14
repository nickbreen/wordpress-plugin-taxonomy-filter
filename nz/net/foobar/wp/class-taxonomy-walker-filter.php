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
        $slugs = isset($wp_query->query[$tax->query_var]) ?
            explode($this->delimiter, $wp_query->query[$tax->query_var]) : array();
        $slugs = in_array($term->slug, $slugs) ?
            array_diff($slugs, array($term->slug)) : array_merge($slugs, array($term->slug));
        if ($slugs) {
            return sprintf(
                '%s?%s=%s',
                get_term_link($args['term']),
                $tax->query_var,
                implode($this->delimiter, $slugs)
            );
        }
        return get_term_link($args['term']); //parent::link($term, $args);
    }
}
