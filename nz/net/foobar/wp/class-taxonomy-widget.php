<?php
namespace nz\net\foobar\wp;

class WidgetTaxonomy extends \WP_Widget
{
    public function __construct()
    {
        parent::__construct(
            // Slashes fail with Undefined index: nz.net.foobar.wp.widgettaxonomy-2
            // in /var/www/wp-admin/includes/ajax-actions.php on line 1952
            // Periods play havok with PHP's from element naming cleverness.
            str_replace('\\', '_', static::class),
            __('foo//bar Taxonomy Filter', ''),
            array(
                'description' => __(
                    'Displays refinement links for archive pages.',
                    ''
                ),
            )
        );
    }

    /**
     * Front-end display of widget.
     *
     * @see WP_Widget::widget()
     *
     * @param array $args
     *                        Widget arguments.
     * @param array $instance
     *                        Saved values from database.
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function widget($args, $instance)
    {
        $tax = get_taxonomy($instance['tax']);
        $walker = $instance['filter'] ?
            new WalkerTaxonomyFilter($tax->name, $instance['any_all']) : new WalkerTaxonomy($tax->name);
        $title = apply_filters('widget_title', $instance['title']);
        $order = !empty($instance['order']) ? explode(',', $instance['order']) : array();
        $terms = $instance['descend'] ? get_terms(array('taxonomy' => $tax->name)) : terms($tax, $order);
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'].$title.$args['after_title'];
        }
        if ($instance['p']) {
            echo '<p>'.$instance['p'].'</p>';
        }
        list($term, $counts)  = postCountPerTerm($tax, $instance['cum_counts'], $instance['filter']);
        printf(
            '<ul class="%s">%s</ul>',
            $tax->query_var,
            $walker->walk($terms, 0, [
                'instance' => $instance,
                'counts' => $counts,
                'current' => currentTerms($tax),
                'term' => $term,
                'ancestry' => ancestry(currentTerms($tax), $order)
            ])
        );
        echo $args['after_widget'];
    }

    /**
     * Back-end widget form.
     *
     * @see WP_Widget::form()
     *
     * @param array $instance
     *                        Previously saved values from database.
     *
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     */
    public function form($instance)
    {
        extract(array_merge($this->defaults, $instance));
        $taxonomy = get_taxonomy($tax);
        $title = isset($instance['title']) ? $instance['title'] : ($taxonomy ? $taxonomy->label : '');
        $taxes = get_taxonomies(array('public' => true), 'objects');
        require 'template-taxonomy-widget.php';
    }

    /**
     * Sanitize widget form values as they are saved.
     *
     * @see WP_Widget::update()
     *
     * @param array $newInstance
     *                            Values just sent to be saved.
     * @param array $oldInstance
     *                            Previously saved values from database.
     *
     * @return array Updated safe values to be saved.
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function update($newInstance, $oldInstance)
    {
        return array_merge($this->defaults, array_map('strip_tags', $newInstance));
    }

    private $defaults = array(
      'tax'=> 'category',
      'counts'=> '',
      'cum_counts'=> '',
      'order'=> '',
      'descend'=> '',
      'filter'=> '',
      'p'=> '',
      'any_all'=> 'any',
      'rel'=> '',
    );
}

function terms($tax, $order)
{
    $terms = $order ? get_terms(array( // If we've specified an order, get only those specified
        'include' => $order,
        'orderby' => 'include',
        'hide_empty' => false,
    )) : get_terms(array( // othewise fetch all the root terms
        'taxonomy' => $tax->name,
    ));
    // Add the current terms':
    $currentTerms = currentTerms($tax);
    // ancestors, excluding currents and roots
    $terms = array_merge($terms, array_filter(
        ancestry($currentTerms, $order),
        function ($term) use ($order, $currentTerms) {
            return !in_array($term->term_id, $order)
                && !in_array($term, $currentTerms)
                && $term->parent;
        }
    ));
    foreach ($currentTerms as $term) {
        // children
        $terms = array_merge($terms, get_terms(array(
            'taxonomy' => $tax->name,
            'parent' => $term->term_id,
            'hide_empty' => false,
        )));
        // siblings, unless their parent is the root, or in the top-level order!
        $terms = array_merge($terms, $term->parent  ? get_terms(array(
            'taxonomy' => $tax->name,
            'parent' => $term->parent,
            'hide_empty' => false,
            'exclude' => $order + array_map(function ($term) {
                return $term->term_id;
            }, $currentTerms)
        )) : array());
    }
    return $terms;
}

function ancestry($terms, $terminalTermIds = array())
{
    $ancestry = array();
    foreach (array_filter($terms, function ($term) use ($terminalTermIds) {
        return // before we even start, only process:
          $term->parent // when there're ancestors to get
          && !in_array($term->term_id, $terminalTermIds); // unless there're ancestors we don't want
    }) as $term) {
        for ($parent = get_term($term->parent);
          !is_wp_error($parent);
          $parent = get_term($parent->parent)) {
            $ancestry[] = $parent;
            if (in_array($parent->term_id, $terminalTermIds)) {
                break;
            }
        }
    }
    return $ancestry;
}

/**
 * @SuppressWarnings("CamelCase")
 */
function currentTerms($tax)
{
    global $wp_query;
    $currentTerms = array();
    if (!empty($wp_query->tax_query->queries)) {
        foreach ($wp_query->tax_query->queries as $tq) {
            if ($tq['taxonomy'] == $tax->name) {
                foreach ($tq['terms'] as $term) {
                    $currentTerms[] = get_term_by('slug', $term, $tq['taxonomy']);
                }
            }
        }
    }
    return $currentTerms;
}

/**
 * Count the number of posts per term in the current query *without* the specified taxonomy *filter* in effect.
 * @return list of: the queried object (i.e. the principal term) and the assoc-array of post counts ([term_id => count).
 *         note that the queries object will be a WP_Error object if the query is not a taxonomy term query!
 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
 */
function postCountPerTerm($tax, $accumulateCounts, $filter)
{
    global $wp_query;
    $args = array_merge(
        $wp_query->query,
        array('nopaging' => true, 'fields' => 'ids')
    );
    if ($filter) {
        unset($args[$tax->query_var]);
    }
    $query = new \WP_Query(); // Initialise a new query
    $postIds = $query->query($args); // with the un-paginated query arguments
    $counts = array();
    foreach ($postIds as $postId) {
        foreach (wp_get_post_terms($postId, $tax->name) as $term) {
            @$counts[$term->term_id] += 1;
            if ($accumulateCounts) {
                for ($term = get_term($term->parent);
                    !is_wp_error($term);
                    $term = get_term($term->parent)) {
                    @$counts[$term->term_id] += 1;
                }
            }
        }
    }
    return array($query->get_queried_object(), $counts);
}
