{if $field->isFilterable()}
	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/ItemList/Filter'], (UiItemListFilter) => {
            {jsphrase name='wcf.global.filter.button.visibility'}
            {jsphrase name='wcf.global.filter.button.clear'}
            {jsphrase name='wcf.global.filter.error.noMatches'}
            {jsphrase name='wcf.global.filter.placeholder'}
            {jsphrase name='wcf.global.filter.visibility.activeOnly'}
            {jsphrase name='wcf.global.filter.visibility.highlightActive'}
            {jsphrase name='wcf.global.filter.visibility.showAll'}
			
			new UiItemListFilter('{unsafe:$field->getPrefixedId()|encodeJS}_list');
		});
	</script>
{/if}

<ul class="scrollableCheckboxList" id="{$field->getPrefixedId()}_list">
	{foreach from=$field->getOptions() item=__fieldOption}
		<li>
			<label>
                {if $__fieldOption['type'] != 4}
                    <input {*
                        *}type="checkbox" {*
                        *}name="{$field->getPrefixedId()}[]" {*
                        *}value="{$__fieldOption['id']}"{*
                        *}{if !$field->getFieldClasses()|empty} class="{implode from=$field->getFieldClasses() item='class' glue=' '}{$class}{/implode}"{/if}{*
                        *}{if $field->getValue() !== null && $__fieldOption['id']|in_array:$field->getValue()} checked{/if}{*
                        *}{if $field->isImmutable()} disabled{/if}{*
                        *}{foreach from=$field->getFieldAttributes() key='attributeName' item='attributeValue'} {$attributeName}="{$attributeValue}"{/foreach}{*
                    *}>
                {/if}
                {if $__fieldOption['type'] == 4}
                    <b>{$__fieldOption['name']}</b>
                {else}
                    {if $__fieldOption['type'] == 0}
                        {icon size=16 name='hashtag'}
                    {else if $__fieldOption['type'] == 2}
                        {icon size=16 name='volume-high'}
                    {else if $__fieldOption['type'] == 5}
                        {icon size=16 name='bullhorn'}
                    {else if $__fieldOption['type'] == 13}
                        {icon size=16 name='podcast'}
                    {else if $__fieldOption['type'] == 14}
                        {icon size=16 name='compass'}
                    {else if $__fieldOption['type'] == 15}
                        {icon size=16 name='comments'}
                    {/if}
				    {$__fieldOption['name']}
                {/if}
			</label>
		</li>
	{/foreach}
</ul>
