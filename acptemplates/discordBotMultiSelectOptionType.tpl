{if $discordBotList|count}
	<ul class="scrollableCheckboxList" id="{$optionName}" style="height: 200px;">
		{foreach from=$discordBotList item=discordBot}
			<li>
				<label><input type="checkbox" name="values[{$optionName}][]" value="{@$discordBot->botID}"{if $discordBot->botID|in_array:$value} checked{/if}> {$discordBot->botName} ({lang}wcf.acp.discordBotList.server{/lang}: {$discordBot->guildName})</label>
			</li>
		{/foreach}
	</ul>

	<script data-relocate="true">
		require(['Language', 'WoltLabSuite/Core/Ui/ItemList/Filter'], function(Language, UiItemListFilter) {
			Language.addObject({
				'wcf.global.filter.button.visibility': '{jslang}wcf.global.filter.button.visibility{/jslang}',
				'wcf.global.filter.button.clear': '{jslang}wcf.global.filter.button.clear{/jslang}',
				'wcf.global.filter.error.noMatches': '{jslang}wcf.global.filter.error.noMatches{/jslang}',
				'wcf.global.filter.placeholder': '{jslang}wcf.global.filter.placeholder{/jslang}',
				'wcf.global.filter.visibility.activeOnly': '{jslang}wcf.global.filter.visibility.activeOnly{/jslang}',
				'wcf.global.filter.visibility.highlightActive': '{jslang}wcf.global.filter.visibility.highlightActive{/jslang}',
				'wcf.global.filter.visibility.showAll': '{jslang}wcf.global.filter.visibility.showAll{/jslang}'
			});
			
			new UiItemListFilter('{$optionName|encodeJS}');
		});
	</script>
{else}
	<p class="info">{lang}wcf.acp.discordBotSelectOptionType.noBot{/lang}</p>
{/if}
