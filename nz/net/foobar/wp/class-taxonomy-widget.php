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
        $walker = new WalkerTaxonomy;
        $tax = get_taxonomy($instance['tax']);
        $walker->tree_type = $tax->name;
        $terms = get_categories(array('taxonomy' => $tax->name));
        echo $args['before_widget'];
        $title = apply_filters('widget_title', $instance['title']);
        if (!empty($instance['title'])) {
            echo $args['before_title'].$title.$args['after_title'];
        }
        if ($instance['p']) {
            echo '<p>'.$instance['p'].'</p>';
        }
        printf(
            '<ul class="%s">%s</ul>',
            $tax->query_var,
            $walker->walk($terms, 0, $instance)
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
        user_error('def:='.print_r($this->defaults, true));
        user_error('old:='.print_r($oldInstance, true));
        user_error('new:='.print_r($newInstance, true));
        $instance = array_map('strip_tags', $newInstance);
        user_error('mrg:='.print_r($instance, true));
        $ret = array_merge($this->defaults, $oldInstance, $newInstance, $instance);
        user_error('ret:='.print_r($ret, true));
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
 * @SuppressWarnings(PHPMD.CamelCaseVariableName)
 */
function availableTerms($tax)
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
