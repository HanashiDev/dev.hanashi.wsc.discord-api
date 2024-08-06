{if $bots|count > 1}
	<div class="section tabMenuContainer" data-active="{$optionName}" data-store="activeTabMenuItem">
		<div id="{$optionName}" class="tabMenuContainer tabMenuContent">
			<nav class="menu">
				<ul>
					{foreach from=$bots item=$bot}
						<li>
							<a href="#{$optionName}-{$bot['botID']}">{$bot['botName']}</a>
						</li>
					{/foreach}
				</ul>
			</nav>
			{foreach from=$bots item=$bot}
				<div id="{$optionName}-{$bot['botID']}" class="tabMenuContent hidden" data-name="{$optionName}-{$bot['botID']}">
					<div class="section">
						<ul class="scrollableCheckboxList" id="{$optionName}_{$bot['botID']}" style="height: 200px;">
							{include file="__discordChannelMultiSelectSub" botChannels=$bot['channels'] botID=$bot['botID']}
							{foreach from=$bot['channels'] item=channel}
								{if $channel['type'] == 4}
									<li>
										<label><input type="checkbox" name="values[{$optionName}][{$bot['botID']}][]" value="{$channel['id']}" style="display: none;"> <b>{$channel['name']}</b></label>
									</li>
									{include file="__discordChannelMultiSelectSub" botChannels=$channel['childs'] botID=$bot['botID']}
								{/if}
							{/foreach}
						</ul>
					</div>
				</div>
				<script data-relocate="true">
					require(['WoltLabSuite/Core/Ui/ItemList/Filter'], function(UiItemListFilter) {
						new UiItemListFilter('{unsafe:$optionName|encodeJS}_{unsafe:$bot['botID']|encodeJS}');
					});
				</script>
			{/foreach}
		</div>
	</div>
	<script data-relocate="true">
		{jsphrase name='wcf.global.filter.button.visibility'}
		{jsphrase name='wcf.global.filter.button.clear'}
		{jsphrase name='wcf.global.filter.error.noMatches'}
		{jsphrase name='wcf.global.filter.placeholder'}
		{jsphrase name='wcf.global.filter.visibility.activeOnly'}
		{jsphrase name='wcf.global.filter.visibility.highlightActive'}
		{jsphrase name='wcf.global.filter.visibility.showAll'}
	</script>
{else if $bots|count == 1}
	<ul class="scrollableCheckboxList" id="{$optionName}" style="height: 200px;">
		{include file="__discordChannelMultiSelectSub" botChannels=$bots[0]['channels'] botID=$bots[0]['botID']}
		{foreach from=$bots[0]['channels'] item=channel}
			{if $channel['type'] == 4}
				<li>
					<label><input type="checkbox" name="values[{$optionName}][{$bots[0]['botID']}][]" value="{$channel['id']}" style="display: none;"> <b>{$channel['name']}</b></label>
				</li>
				{include file="__discordChannelMultiSelectSub" botChannels=$channel['childs'] botID=$bots[0]['botID']}
			{/if}
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
			
			new UiItemListFilter('{unsafe:$optionName|encodeJS}');
		});
	</script>
{else}
	<p class="info">{lang}wcf.acp.discordBotSelectOptionType.noBot{/lang}</p>
{/if}
