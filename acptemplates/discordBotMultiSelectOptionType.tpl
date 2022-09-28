{if $discordBotList|count}
	<select id="{$option->optionName}" name="values[{$option->optionName}][]" multiple size="10">
		{foreach from=$discordBotList item=discordBot}
			<option value="{@$discordBot->botID}"{if $discordBot->botID|in_array:$value} selected{/if}>
				{$discordBot->botName} ({lang}wcf.acp.discordBotList.server{/lang}: {$discordBot->guildName})
			</option>
		{/foreach}
	</select>
{else}
	<p class="info">{lang}wcf.acp.discordBotSelectOptionType.noBot{/lang}</p>
{/if}
