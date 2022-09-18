{foreach from=$botChannels item=channel}
	{if $channel['type'] == 14}
		{include file="__discordChannelMultiSelectChannel" guildChannel=$channel botID=$botID}
	{/if}
{/foreach}
{foreach from=$botChannels item=channel}
	{if $channel['type'] == 0 || $channel['type'] == 5 || $channel['type'] == 15}
		{include file="__discordChannelMultiSelectChannel" guildChannel=$channel botID=$botID}
	{/if}
{/foreach}
{foreach from=$botChannels item=channel}
	{if $channel['type'] == 2 || $channel['type'] == 13}
		{include file="__discordChannelMultiSelectChannel" guildChannel=$channel botID=$botID}
	{/if}
{/foreach}
