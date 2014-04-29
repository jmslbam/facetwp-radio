<?php

class FacetWP_Facet_Radio
{

    function __construct() {
        $this->label = __( 'Radio', 'fwp' );
    }


    /**
     * Load the available choices
     */
    function load_values( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $where_clause = $params['where_clause'];

        // Orderby
        $orderby = 'counter DESC, f.facet_display_value ASC';
        if ( 'display_value' == $facet['orderby'] ) {
            $orderby = 'f.facet_display_value ASC';
        }
        elseif ( 'raw_value' == $facet['orderby'] ) {
            $orderby = 'f.facet_value ASC';
        }

        $orderby = apply_filters( 'facetwp_facet_orderby', $orderby, $facet );

        // Limit
        $limit = ctype_digit( $facet['count'] ) ? $facet['count'] : 10;

        $sql = "
        SELECT f.facet_value, f.facet_display_value, COUNT(*) AS counter
        FROM {$wpdb->prefix}facetwp_index f
        WHERE f.facet_name = '{$facet['name']}' $where_clause
        GROUP BY f.facet_value
        ORDER BY $orderby
        LIMIT $limit";

        return $wpdb->get_results( $sql );
    }


    /**
     * Generate the facet HTML
     */
    function render( $params ) {

        $output = '';
        $facet = $params['facet'];
        $values = (array) $params['values'];
        $selected_values = (array) $params['selected_values'];

        $is_empty = empty( $selected_values ) ? ' selected' : '';
        $output .= '<div class="facetwp-radio' . $is_empty  . '" data-value="">' . __( 'Any', 'fwp' ) . '</div>';

        foreach ( $values as $result ) {
            $selected = in_array( $result->facet_value, $selected_values ) ? ' selected' : '';

            // Determine whether to show counts
            $display_value = $result->facet_display_value;
            $display_value .= " <span class='counts'>($result->counter)</span>";
            $output .= '<div class="facetwp-radio' . $selected . '" data-value="' . $result->facet_value . '">' . $display_value . '</div>';
        }

        return $output;
    }


    /**
     * Filter the query based on selected values
     */
    function filter_posts( $params ) {
        global $wpdb;

        $facet = $params['facet'];
        $selected_values = $params['selected_values'];
        $selected_values = is_array( $selected_values ) ? $selected_values[0] : $selected_values;

        $sql = "
        SELECT DISTINCT post_id FROM {$wpdb->prefix}facetwp_index
        WHERE facet_name = '{$facet['name']}' AND facet_value IN ('$selected_values')";
        return $wpdb->get_col( $sql );
    }


    /**
     * Output any admin scripts
     */
    function admin_scripts() {
?>
<script>
(function($) {
    wp.hooks.addAction('facetwp/load/radio', function($this, obj) {
        $this.find('.facet-source').val(obj.source);
        $this.find('.facet-parent-term').val(obj.parent_term);
        $this.find('.type-radio .facet-orderby').val(obj.orderby);
        $this.find('.type-radio .facet-count').val(obj.count);
    });

    wp.hooks.addFilter('facetwp/save/radio', function($this, obj) {
        obj['source'] = $this.find('.facet-source').val();
        obj['parent_term'] = $this.find('.type-radio .facet-parent-term').val();
        obj['orderby'] = $this.find('.type-radio .facet-orderby').val();
        obj['count'] = $this.find('.type-radio .facet-count').val();
        return obj;
    });
})(jQuery);
</script>
<?php
    }


    /**
     * Output any front-end scripts
     */
    function front_scripts() {
?>

<link href="<?php echo WP_CONTENT_URL; ?>/plugins/facetwp-radio/assets/css/front.css" rel="stylesheet">

<script>
(function($) {
    wp.hooks.addAction('facetwp/refresh/radio', function($this, facet_name) {
        var selected_values = [];
        $this.find('.facetwp-radio.selected').each(function() {
            var val = $(this).attr('data-value');
            if ('' != val) {
                selected_values.push(val);
            }
        });
        FWP.facets[facet_name] = selected_values;
    });

    wp.hooks.addAction('facetwp/ready', function() {
        $(document).on('click', '.facetwp-radio', function() {
            var $facet = $(this).closest('.facetwp-facet');
            $facet.find('.facetwp-radio').removeClass('selected');
            $(this).addClass('selected');
            if ('' != $(this).attr('data-value')) {
                FWP.static_facet = $facet.attr('data-name');
            }
            FWP.autoload();
        });
    });
})(jQuery);
</script>
<?php
    }


    /**
     * Output admin settings HTML
     */
    function settings_html() {
?>
        <tr class="facetwp-conditional type-radio">
            <td>
                <?php _e('Parent term', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content">
                        If <strong>Data source</strong> is a taxonomy, enter the
                        parent term's ID if you want to show child terms.
                        Otherwise, leave blank.
                    </div>
                </div>
            </td>
            <td>
                <input type="text" class="facet-parent-term" value="" />
            </td>
        </tr>
        <tr class="facetwp-conditional type-radio">
            <td><?php _e('Sort by', 'fwp'); ?>:</td>
            <td>
                <select class="facet-orderby">
                    <option value="count"><?php _e( 'Facet Count', 'fwp' ); ?></option>
                    <option value="display_value"><?php _e( 'Display Value', 'fwp' ); ?></option>
                    <option value="raw_value"><?php _e( 'Raw Value', 'fwp' ); ?></option>
                </select>
            </td>
        </tr>
        <tr class="facetwp-conditional type-radio">
            <td>
                <?php _e('Count', 'fwp'); ?>:
                <div class="facetwp-tooltip">
                    <span class="icon-question">?</span>
                    <div class="facetwp-tooltip-content"><?php _e( 'The maximum number of facet choices to show', 'fwp' ); ?></div>
                </div>
            </td>
            <td><input type="text" class="facet-count" value="10" /></td>
        </tr>
<?php
    }
}
