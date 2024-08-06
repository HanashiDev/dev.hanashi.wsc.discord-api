{if $discordBotList|count}
	<ul class="scrollableCheckboxList" id="{$optionName}" style="height: 200px;">
		{foreach from=$discordBotList item=discordBot}
			<li>
				<label><input type="checkbox" name="values[{$optionName}][]" value="{unsafe:$discordBot->botID}"{if $discordBot->botID|in_array:$value} checked{/if}> {$discordBot->botName} ({lang}wcf.acp.discordBotList.server{/lang}: {$discordBot->guildName})</label>
			</li>
		{/foreach}
	</ul>

	<script data-relocate="true">
		require(['WoltLabSuite/Core/Ui/ItemList/Filter'], function(UiItemListFilter) {
			{jsphrase name='wcf.global.filter.button.visibility'}
			{jsphrase name='wcf.global.filter.button.clear'}
			{jsphrase name='wcf.global.filter.error.noMatches'}
			{jsphrase name='wcf.global.filter.placeholder'}
			{jsphrase name='wcf.global.filter.visibility.activeOnly'}
			{jsphrase name='wcf.global.filter.visibility.highlightActive'}
			{jsphrase name='wcf.global.filter.visibility.showAll'}
			
			new UiItemListFilter('{$optionName|encodeJS}');
		});
	</script>
{else}
	<p class="info">{lang}wcf.acp.discordBotSelectOptionType.noBot{/lang}</p>
{/if}
