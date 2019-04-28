{if $discordBotList|count}
    <select id="{$option->optionName}" name="values[{$option->optionName}][]" multiple size="10">
        {foreach from=$discordBotList item=discordBot}
            <option value="{@$discordBot->botID}"{if $discordBot->botID|in_array:$value} selected{/if}>
                {$discordBot->botName} (Server: {$discordBot->guildName})
            </option>
        {/foreach}
    </select>
{else}
    {* TODO: lang *}
    <p class="info">Du hast noch keinen Discord-Bot angelegt. Dies kannst du unter <a href="{link controller="DiscordBotAdd"}{/link}">Discord-Bot</a> erledigen.</p>
{/if}
