<?php
	if ( ! defined( 'ABSPATH' ) ) exit;

	$field    = $this->get_field_key( $key );

	$options_based_on = apply_filters( 'flexible_shipping_method_rule_options_based_on', array(
			'none' 		=> __( 'None', 'flexible-shipping' ),
			'value'  	=> __( 'Price', 'flexible-shipping' ),
			'weight'  	=> __( 'Weight', 'flexible-shipping' ),
	));


	$key = 'method_rules[xxx][based_on]';
	$args_based_on = array(
			'type' 		=> 'select',
			'options' 	=> $options_based_on,
			'input_class'       => array( 'hs-beacon-search' ),
			'custom_attributes' => array(
				'data-beacon_search' => __( 'Based on', 'flexible-shipping' ),
			),
			'return' 	=> true,
	);
	$value = 'none';
	$field_based_on = woocommerce_form_field( $key, $args_based_on, $value );

	$key = 'method_rules[xxx][min]';
	$args_min = array(
			'type' 		 	=> 'text',
			'return' 	 	=> true,
			'input_class'	=> array( 'wc_input_price', 'hs-beacon-search' ),
			'custom_attributes' => array(
				'data-beacon_search' => __( 'Min', 'flexible-shipping' ),
			),
	);
	$value = '';
	$field_min = woocommerce_form_field( $key, $args_min, wc_format_localized_price( $value ) );

	$key = 'method_rules[xxx][max]';
	$args_max = array(
			'type' 		=> 'text',
			'return' 	=> true,
			'input_class'	=> array( 'wc_input_price', 'hs-beacon-search' ),
			'custom_attributes' => array(
				'data-beacon_search' => __( 'Max', 'flexible-shipping' ),
			),
	);
	$value = '';
	$field_max = woocommerce_form_field( $key, $args_max, wc_format_localized_price( $value ) );

	$key = 'method_rules[xxx][cost_per_order]';
	$args_cost_per_order = array(
			'type' 		=> 'text',
			'return' 	=> true,
			'input_class'	=> array( 'wc_input_price', 'hs-beacon-search' ),
			'custom_attributes' => array(
				'data-beacon_search' => __( 'Cost per order', 'flexible-shipping' ),
			),
	);
	$value = '';
	$field_cost_per_order = woocommerce_form_field( $key, $args_cost_per_order, wc_format_localized_price( $value ) );

	$count_rules = 0;
?>

<tr valign="top" class="flexible_shipping_method_rules">
	<th class="forminp" colspan="2">
        <label for="<?php echo esc_attr( $field ); ?>"><span id="<?php echo esc_attr( $field . '_label' ); ?>"><?php echo $data['title']; ?></span></label>
        <?php
            $fs_pro_link = get_locale() === 'pl_PL' ? 'https://www.wpdesk.pl/sklep/flexible-shipping-pro-woocommerce/' : 'https://flexibleshipping.com/table-rate/';

            if ( ! in_array( 'flexible-shipping-pro/flexible-shipping-pro.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ):
        ?>
            <p><?php printf( __( 'Check %sFlexible Shipping PRO &rarr;%s to add advanced rules based on shipment classes, product/item count or additional handling fees/insurance.', 'flexible-shipping' ), '<a href="' . $fs_pro_link . '?utm_campaign=flexible-shipping&utm_source=user-site&utm_medium=link&utm_term=flexible-shipping-pro&utm_content=fs-shippingzone-addnew-rules" target="_blank">', '</a>' ); ?></p>
        <?php endif; ?>
	</th>
</tr>
<tr valign="top" class="flexible_shipping_method_rules">
    <td colspan="2" style="padding:0;">
        <table id="<?php echo esc_attr( $field ); ?>" class="flexible_shipping_method_rules wc_input_table sortable widefat">
            <thead>
            	<tr>
            		<th class="sort">&nbsp;</th>
            		<th class="based_on">
            		    <?php _e( 'Based on', 'flexible-shipping' ); ?>
            		    <span class="woocommerce-help-tip" data-tip="<?php _e( 'Shipping cost will be calculated based on the selected parameter.', 'flexible-shipping' ); ?>"></span>
                    </th>
            		<th class="min">
            			<?php _e( 'Min', 'flexible-shipping' ); ?>
            			<span class="woocommerce-help-tip" data-tip="<?php _e( 'Enter minimum value for the &quot;Based on&quot; parameter. Value based on the price will be calculated by WooCommerce tax settings &quot;Display prices during cart and checkout&quot;', 'flexible-shipping' ); ?>"></span>
            		</th>
            		<th class="max">
            			<?php _e( 'Max', 'flexible-shipping' ); ?>
            			<span class="woocommerce-help-tip" data-tip="<?php _e( 'Enter maximum value for the &quot;Based on&quot; parameter. Value based on the price will be calculated by WooCommerce tax settings &quot;Display prices during cart and checkout&quot;', 'flexible-shipping' ); ?>"></span>
            		</th>
            		<th class="cost_per_order">
            			<?php _e( 'Cost per<br/>order', 'flexible-shipping' ); ?>
            			<span class="woocommerce-help-tip" data-tip="<?php _e( 'Enter shipment cost for this rule.', 'flexible-shipping' ); ?>"></span>
            		</th>
            		<?php do_action( 'flexible_shipping_method_rule_thead', '' ); ?>
            	</tr>
            </thead>
            <tbody>
            	<?php if ( isset( $data['default'] ) ) : ?>
            		<?php foreach ( $data['default'] as $key => $rule ) : $count_rules++; ?>
            			<tr>
            				<td class="sort"></td>
            				<td class="based_on">
            					<?php
            						$key = 'method_rules[' . $count_rules . '][based_on]';
            						$value = '';
            						if ( isset( $rule['based_on'] ) ) {
            							$value = $rule['based_on'];
            						}
            						echo woocommerce_form_field( $key, $args_based_on, $value );
            					?>
            				</td>
            				<td class="min">
            					<?php
            						$key = 'method_rules[' . $count_rules . '][min]';
            						$value = '';
            						if ( isset( $rule['min'] ) ) {
            							$value = $rule['min'];
            						}
            						echo woocommerce_form_field( $key, $args_min, wc_format_localized_price( $value ) );
            					?>
            				</td>
            				<td class="max">
            					<?php
            						$key = 'method_rules[' . $count_rules . '][max]';
            						$value = '';
            						if ( isset( $rule['max'] ) ) {
            							$value = $rule['max'];
            						}
            						echo woocommerce_form_field( $key, $args_max, wc_format_localized_price( $value ) );
            					?>
            				</td>
            				<td class="cost_per_order">
            					<?php
            						$key = 'method_rules[' . $count_rules . '][cost_per_order]';
            						$value = '';
            						if ( isset( $rule['cost_per_order'] ) ) {
            							$value = $rule['cost_per_order'];
            						}
            						echo woocommerce_form_field( $key, $args_cost_per_order, wc_format_localized_price( $value ) );
            					?>
            				</td>
            				<?php do_action( 'flexible_shipping_method_rule_row', $rule, $count_rules ); ?>
            			</tr>
            		<?php endforeach; ?>
            	<?php endif; ?>
            </tbody>

            <tfoot>
            	<tr>
            		<th colspan="99">
            			<a id="insert_rule" href="#" class="button plus insert"><?php _e( 'Insert rule', 'flexible-shipping' ); ?></a>
            			<a id="remove_rules" href="#" class="button minus"><?php _e( 'Delete selected rules', 'flexible-shipping' ); ?></a>
            		</th>
            	</tr>
            </tfoot>
        </table>

        <script type="text/javascript">
            function append_row( id ) {
            	var code = '<tr class="new">\
            					<td class="sort"></td>\
            					<td class="based_on">\
            						<?php echo str_replace( "'", '"', str_replace( "\r", "", str_replace( "\n", "", $field_based_on ) ) ); ?> \
            					</td>\
            					<td class="min">\
            					    <?php echo str_replace( "'", '"', str_replace( "\r", "", str_replace( "\n", "", $field_min ) ) ); ?> \
            					</td>\
            					<td class="max">\
            					    <?php echo str_replace( "'", '"', str_replace( "\r", "", str_replace( "\n", "", $field_max ) ) ); ?> \
            					</td>\
            					<td class="cost_per_order">\
            					   <?php echo str_replace( "'", '"', str_replace( "\r", "", str_replace( "\n", "", $field_cost_per_order ) ) ); ?> \
            					</td>\
            					<?php do_action( 'flexible_shipping_method_rule_js', '' ); ?>
            				</tr>';
            	var code2 = code.replace(/xxx/g, id );
            	var $tbody = jQuery('#<?php echo esc_attr( $field ); ?>').find('tbody');
            	$tbody.append( code2 );
            }
            jQuery(document).ready(function() {
            	var tbody = jQuery('#<?php echo esc_attr( $field ); ?>').find('tbody');
            	var append_id = <?php echo $count_rules ?>;
            	var size = tbody.find('tr').length;
            	if ( size == 0 ) {
            		append_id = append_id+1;
            		append_row(append_id);
                    jQuery('#insert_rule').trigger( 'insert_rule' , [append_id] );
            	}
            	jQuery('#insert_rule').click(function() {
            		append_id = append_id+1;
            		append_row(append_id);
            		jQuery('#rules_'+append_id+'_min').focus();
            		jQuery('#insert_rule').trigger( 'insert_rule' , [append_id] );
            		return false;
            	});
            	jQuery('#remove_rules').click(function() {
            		if ( current = tbody.children( '.current' ) ) {
            			current.each(function() {
            				jQuery(this).remove();
            			});
            		} else {
            			alert( '<?php _e( 'No rows selected.' , 'flexible-shipping' ); ?>' );
            		}
            		return false;
            	});
            	jQuery(document).on('click', '.delete_rule',  function() {
            		if (confirm('<?php _e( 'Are you sure?' , 'flexible-shipping' ); ?>')) {
            			jQuery(this).closest('tr').remove();
            		}
            		return false;
            	});
            	jQuery('#mainform').attr('action', '<?php echo remove_query_arg( 'added', add_query_arg( 'added', '1' ) ); ?>' );
            });
        </script>
<?php
	if( version_compare( WC()->version, '2.6.0', ">=" ) ) {
?>
<script type="text/javascript">
	<?php
		$zone            = WC_Shipping_Zones::get_zone_by( 'instance_id', sanitize_key( $_GET['instance_id'] ) );
		$shipping_method_woo = WC_Shipping_Zones::get_shipping_method( sanitize_key( $_GET['instance_id'] ) );
		$content = '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping' ) . '">' . __( 'Shipping Zones', 'woocommerce' ) . '</a> &gt ';
		$content .= '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&zone_id=' . absint( $zone->get_id() ) ) . '">' . esc_html( $zone->get_zone_name() ) . '</a> &gt ';
		$content .= '<a href="' . admin_url( 'admin.php?page=wc-settings&tab=shipping&instance_id=' . sanitize_key( $_GET['instance_id'] ) ) . '">' . esc_html( $shipping_method_woo->get_title() ) . '</a>';
		if ( isset( $data['method_title'] ) && $data['method_title'] != '' ) {
			$content .= ' &gt ';
			$content .= esc_html( $data['method_title'] );
		}
		else {
			$content .= ' &gt ';
			$content .= __( 'Add New', 'flexible-shipping' );
		}
	?>
	jQuery('#mainform h2').first().replaceWith( '<h2>' + '<?php echo $content; ?>' + '</h2>' );
</script>
<?php
	}
?>
    </td>
</tr>
