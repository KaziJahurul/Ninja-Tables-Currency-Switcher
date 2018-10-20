<?php defined('ABSPATH') or die;
/**
 * Plugin Name: Ninja Tables Currency Switcher
 * Description: Tiny Plugin to be used with Ninja Tables for currency switcher
 * Author: WP Manage Ninja
 * Author URI: https://wpmanageninja.com
 * Version: 1.0
 * Plugin URI: https://wpmanageninja.com
 * Textdomain: ninja-currency
 */
class NinjaTablesCurrencySwitcher {
	public $version = '1.0.0';
	public function boot() {
		$this->common();
	}

	public function common() {
		add_shortcode( 'ninja_currency_switcher', array($this, 'ninja_currency_switcher_shortcode'));
	}

	public function ninja_currency_switcher_shortcode( $atts, $content = null ) {
		$atts = shortcode_atts(array(
			'table_id' => null,
			'columns' => '',
            'thousand_seperator' => '',
            'decimal_seperator' => '',
		), $atts);

		extract($atts);

		if ( !$table_id ) {
			return '';
		}

		echo $thousand_seperator . $decimal_seperator;
		$columns = explode(',', $columns);
		$content = '<div class="ninja_currency_changed ninja_currency_'.$table_id.'">'.$content.'</div>';
		add_action('wp_footer', function () use ($table_id, $columns, $thousand_seperator, $decimal_seperator) {
			 $this->pushCustomScripts($table_id, $columns, $thousand_seperator, $decimal_seperator);
        });

		return $content;
	}

	public function pushCustomScripts($table_id, $columns) {
		?>
		<script type="text/javascript">
            jQuery(document).ready(function($) {
                var alreadyAddedBase = false;

                function numberExtract(valueOrElement, thousandSeparator, decimalSeparator ) {

                    if(thousandSeparator)
                        thousandSeparator = '.';
                    if(decimalSeparator)
                        decimalSeparator = ',';

                    if (!valueOrElement) {
                        return '';
                    }

                    valueOrElement = valueOrElement.replace(/[^0-9\.,-]+/g, "");
                    valueOrElement = valueOrElement.split(thousandSeparator).join("");
                    valueOrElement = valueOrElement.split(decimalSeparator).join(".");
                    var numberValue = Number(valueOrElement);
                    // console.log(numberValue);
                    if (isNaN(numberValue)) {
                        return '';
                    }
                    return numberValue;
                }

                var tableId = <?php echo $table_id; ?>;

                var $buttons = $('.ninja_currency_'+tableId+' .ninja_changer');

	            <?php
	            $selectors = array();
	            foreach ($columns as $column) {
		            $selectors[] = '#footable_'.$table_id.' tbody td.'.$column;
	            }
	            ?>

                var cellSelectors = '<?php echo implode(',', $selectors); ?>';

                function  pushOriginalValues(callback) {
                    if(alreadyAddedBase) {
                        return callback();
                    }

                    $(cellSelectors).each(function(index, cell) {
                        $(cell).attr('data-original_value', $(cell).text());
                    });

                    alreadyAddedBase = true;

                    return callback();

                }

                $buttons.each(function (index, button) {
                    $(button).on('click', function (e) {
                        e.preventDefault();
                        var that = this;
                        var ratio = $(that).data('ratio');
                        pushOriginalValues(function () {

                            var formatter = new Intl.NumberFormat($(that).data('locale'), {
                                style: 'currency',
                                currency: $(that).data('currency'),
                                minimumFractionDigits: 2
                            });

                            $(cellSelectors).each(function(index, cell) {
                                var numberValue = numberExtract($(cell).attr('data-original_value'), '.', ',') * ratio;
                                $(cell).text(formatter.format(numberValue));
                            });
                        });
                    });
                });

            });
		</script>
		<?php
	}

}

add_action('plugins_loaded', function () {
	$ninjaTablesCurrencySwitcher = new NinjaTablesCurrencySwitcher();
	$ninjaTablesCurrencySwitcher->boot();
});