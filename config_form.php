<div class="field">
    <div class="two columns alpha">
        <label for="mall_map_filter_tooltip"><?php echo __('Filter Tooltip Text'); ?></label>    
    </div>    
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('The text that highlights the map filters the first time a user visits the map.'); ?></p>
        <div class="input-block">        
            <textarea cols="50" name="mall_map_filter_tooltip" id="mall_map_filter_tooltip" rows="4"><?php echo get_option('mall_map_filter_tooltip'); ?></textarea>
        </div>
    </div>
</div>
<div class="field">
    <div class="two columns alpha">
        <label for="mall_map_tooltip_button"><?php echo __('Tooltip Button Text'); ?></label>    
    </div>    
    <div class="inputs five columns omega">
        <p class="explanation"><?php echo __('The text that appears on the button to close the tooltip.'); ?></p>
        <div class="input-block">        
            <input type="text" name="mall_map_tooltip_button" id="mall_map_tooltip_button" value="<?php echo get_option('mall_map_tooltip_button'); ?>" />
        </div>
    </div>
</div>
