{if $discordBotList|count}
	<select id="{$option->optionName}" name="values[{$option->optionName}]">
		<option></option>
		{foreach from=$discordBotList item=discordBot}
			<option value="{$discordBot->botID}"{if $discordBot->botID == $value} selected{/if}>
				{$discordBot->botName} (Server: {$discordBot->guildName})
			</option>
		{/foreach}
	</select>
{else}
	<p class="info">{lang}wcf.acp.discordBotSelectOptionType.noBot{/lang}</p>
{/if}