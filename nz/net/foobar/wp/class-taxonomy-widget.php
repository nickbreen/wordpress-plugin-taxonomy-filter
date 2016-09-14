<?php
namespace nz\net\foobar\wp;

class WidgetTaxonomy extends \WP_Widget
{
    // '+' works as AND, ',' works as OR/IN
    const DELIM_ANY = ',';
    const DELIM_ALL = '+';

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
     */
    public function widget($args, $instance)
    {
        $tax = get_taxonomy($instance['tax']);
        $walker = new WalkerTaxonomy($tax->name);
        $title = apply_filters('widget_title', $instance['title']);
        $order = !empty($instance['order']) ? explode(',', $instance['order']) : false;
        $terms = static::getTerms($tax, $order, 'descend' == $instance['descend']);
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'].$title.$args['after_title'];
        }
        if ($instance['p']) {
            echo '<p>'.$instance['p'].'</p>';
        }
        printf(
            '<ul class="%s">%s</ul>',
            $tax->query_var,
            $walker->walk($terms, 0, [
                'instance' => $instance,
                'postCountPerTerm' => postCountPerTerm($tax->name)
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
     */
    public function update($newInstance, $oldInstance)
    {
        $instance = array_map('strip_tags', $newInstance);
        $ret = array_merge($this->defaults, $oldInstance, $newInstance, $instance);
        return $ret;
        $instance = array();
        $fields = array('title', 'tax', 'counts', 'cum_counts', 'order', 'descend', 'p', 'filter', 'any_all', 'rel');
        foreach ($fields as $i) {
            $instance[$i] = !empty($newInstance[$i]) ? strip_tags(
                $newInstance[$i]
            ) : $oldInstance[$i];
        }
        return $instance;
    }

    private function getTerms($tax, $order, $descend)
    {
        // If we're always decsending, just get all terms.
        if ($descend) {
            return get_terms(array('taxonomy' => $tax->name));
        }
        // Start with nothing
        $terms = array();
        // If we've specified an order, add these (as the root terms).
        if ($order) {
            $terms = array_merge($terms, get_terms(array(
                'include' => $order,
                'orderby' => 'include',
                'hide_empty' => false,
            )));
        }
        // Add the current terms':
        foreach (static::currentTerms($tax) as $term) {
            // children
            $terms = array_merge($terms, get_terms(array(
                'taxonomy' => $tax->name,
                'parent' => $term->term_id,
                'hide_empty' => false,
            )));
            // siblings, unless their parent is the root, or in the top-level order!
            $terms = array_merge($terms, $term->parent && !in_array($term->term_id, $order) ? get_terms(array(
                'taxonomy' => $tax->name,
                'parent' => $term->parent,
                'hide_empty' => false,
            )) : array());
            // ancestors
            if ($term->parent) {
                for ($parent = get_term($term->parent); $parent->parent; $parent = get_term($parent->parent)) {
                    $terms[] = $parent;
                }
            }
        }
        return $terms;
    }

    /**
     * @SuppressWarnings("CamelCase")
     */
    public function currentTerms($tax)
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

    private $defaults = array(
      'tax'=> 'category',
      'counts'=> '',
      'cum_counts'=> '',
      'order'=> '',
      'descend'=> '',
      'filter'=> 'filter',
      'p'=> '',
      'any_all'=> 'any',
      'rel'=> 'none',
    );
}

/**
 * Count the number of posts per term in the current query with the specified taxonomy.
 * [
 *    term_id => count,
 * ]
 *
 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
 */
function postCountPerTerm($tax)
{
    global $wp_query;
    // Strip the pagination parameters from the query
    $args = array_merge(
        $wp_query->query,
        array($tax => '', 'offset' => 0, 'posts_per_page' => -1, 'paged' => 0)
    );

    // Initialise a new query with the un-paginated query arguments
    $query = new \WP_Query($args);
    $posts = $query->get_posts();

    // Build a list of each posts' terms
    $postsTerms = array();
    foreach ($posts as $post) {
        $postsTerms[$post->ID] = wp_get_post_terms($post->ID, $tax);
    }

    // Count up the posts that match each term
    $termPostCounts = array();
    foreach ($postsTerms as $postTerms) {
        foreach ($postTerms as $postTerm) {
            @$termPostCounts[$postTerm->term_id] += 1;
        }
    }

    return $termPostCounts;
}
