<p>
    <label for="<?php echo $this->get_field_id('title');
        ?>"><?php _e('Title:');
        ?></label>
    <input class="widefat"
        id="<?php echo $this->get_field_id('title');
        ?>"
        name="<?php echo $this->get_field_name('title');
        ?>" type="text"
        value="<?php echo esc_attr($title);
        ?>">
</p>

<p>
    <label for="<?php echo $this->get_field_id('p');
        ?>"><?php _e('Help:');
        ?></label>
    <textarea class="widefat"
        id="<?php echo $this->get_field_id('p');
        ?>"
        name="<?php echo $this->get_field_name('p');
        ?>" type="text"><?php echo esc_attr($p);
        ?></textarea>
    <br> Displayed Immediately below the widget title. Automatically
    wrapped in
    <tt>&lt;p&gt;</tt>
    tags.
</p>

<p>
    <label for="<?php echo $this->get_field_id('tax');
        ?>"><?php _e('Taxonomy:');
        ?></label>
    <select class="widefat" size="<?php echo count($taxes);
        ?>"
        id="<?php echo $this->get_field_id('tax');
        ?>"
        name="<?php echo $this->get_field_name('tax');
        ?>" type="text">
    <?php foreach ($taxes as $id => $t) : ?>
        <option <?php echo $id == $tax ? 'selected' : '';
        ?> value="<?php echo $id;
        ?>">
            <?php echo $t->labels->name?>
        </option>
    <?php endforeach;
        ?>
    </select>
</p>

<p>
    <input class="" id="<?php echo $this->get_field_id('filter');
        ?>"
        name="<?php echo $this->get_field_name('filter');
        ?>"
        type="checkbox" <?php echo $filter == 'filter' ? 'checked' : '';
        ?>
        value="filter"> <label
        for="<?php echo $this->get_field_id('filter');
        ?>"><?php _e('Filter');
        ?></label>
    <br>
    If checked links to <?php echo $taxonomy ? $taxonomy->label : 'taxonomy';
        ?> will <i>filter</i> the current page's results,
    otherwise the links will work just like normal <?php echo $taxonomy ? $taxonomy->label : 'taxonomy';
        ?> navigation links.
</p>

<p>
    <input class=""
        id="<?php echo $this->get_field_id('any_all');
        ?>_all"
        name="<?php echo $this->get_field_name('any_all');
        ?>" type="radio"
        <?php echo $any_all == 'all' ? 'checked' : '';
        ?> value="all"> <label
        for="<?php echo $this->get_field_id('any_all');
        ?>_all"><?php _e('All terms must match');
        ?></label>
    <br> <input class=""
        id="<?php echo $this->get_field_id('any_all');
        ?>_any"
        name="<?php echo $this->get_field_name('any_all');
        ?>" type="radio"
        <?php echo $any_all == 'any' ? 'checked' : '';
        ?> value="any"> <label
        for="<?php echo $this->get_field_id('any_all');
        ?>_any"><?php _e('Any term must match');
        ?></label>
    <br> Specified if <i>any</i> or <i>all</i> terms must match on a
    result.
</p>

<p>
    <input class="" id="<?php echo $this->get_field_id('descend');
        ?>"
        name="<?php echo $this->get_field_name('descend');
        ?>"
        type="checkbox" <?php echo $descend == 'descend' ? 'checked' : '';
        ?>
        value="descend"> <label
        for="<?php echo $this->get_field_id('descend');
        ?>"><?php _e('Descend');
        ?></label>
    <br> If checked all descendant terms (the full hierarchy) will be
    displayed, otherwise just the root terms and the ancestor and child
    terms of the currently selected term.
</p>

<p>
    <input class="" id="<?php echo $this->get_field_id('counts');
        ?>"
        name="<?php echo $this->get_field_name('counts');
        ?>"
        type="checkbox" <?php echo $counts == 'show' ? 'checked' : '';
        ?>
        value="show"> <label
        for="<?php echo $this->get_field_id('counts');
        ?>"><?php _e('Show Counts');
        ?></label>
    <br> If checked displays the count of posts <i>in the current
        loop/query</i> that have the term.
</p>

<p>
    <input class="" id="<?php echo $this->get_field_id('cum_counts');
        ?>"
        name="<?php echo $this->get_field_name('cum_counts');
        ?>"
        type="checkbox" <?php echo $cum_counts == 'show' ? 'checked' : '';
        ?>
        value="show"> <label
        for="<?php echo $this->get_field_id('cum_counts');
        ?>"><?php _e('Show Cummulative Counts');
        ?></label>
    <br> If checked displays the count of posts <i>in the current
        loop/query</i> that have the term <i>or a descendant of the term</i>.
</p>

<p>
    Link <tt>rel</tt>.<br>
    <label><input type="checkbox" value="nofollow" name="<?php echo $this->get_field_name('rel');
        ?>"
        <?php echo $rel == 'nofollow' ? 'checked' : '';
        ?>> nofollow</label>
    <br> Select <i>nofollow</i> to prevent search engines spidering the links.
</p>

<p>
    <label for="<?php echo $this->get_field_id('order');
        ?>"><?php _e('Top Level Order:');
        ?></label>
    <input class="widefat"
        id="<?php echo $this->get_field_id('order');
        ?>"
        name="<?php echo $this->get_field_name('order');
        ?>" type="text"
        value="<?php echo esc_attr($order);
        ?>"> <br> Comma separated list
    of term ID's to restirct and order the root terms.
</p>
