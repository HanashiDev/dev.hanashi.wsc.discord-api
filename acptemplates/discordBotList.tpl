{include file='header' pageTitle='wcf.acp.menu.link.configuration.discord.discordBotList'}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\discord\\bot\\DiscordBotAction', $('.jsRow'));
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.configuration.discord.discordBotList{/lang}</h1>
	</div>
	
	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='DiscordBotAddManager'}{/link}" class="button"><span class="icon icon16 fa-plus"></span> <span>{lang}wcf.acp.menu.link.configuration.discord.discordBotList.add{/lang}</span></a></li>
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign=pagesLinks controller='DiscordBotList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
    <div class="section tabularBox">
        <table class="table">
			<thead>
				<tr>
                    <th class="columnIcon"></th>
                    <th class="columnID columnBotID{if $sortField == 'botID'} active {@$sortOrder}{/if}"><a href="{link controller='DiscordBotList'}pageNo={@$pageNo}&sortField=botID&sortOrder={if $sortField == 'botID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
                    <th class="columnTitle columnBotName{if $sortField == 'botName'} active {@$sortOrder}{/if}"><a href="{link controller='DiscordBotList'}pageNo={@$pageNo}&sortField=botName&sortOrder={if $sortField == 'botName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.discordBotList.botName{/lang}</a></th>
                    <th class="columnText columnGuildName{if $sortField == 'guildName'} active {@$sortOrder}{/if}"><a href="{link controller='DiscordBotList'}pageNo={@$pageNo}&sortField=guildName&sortOrder={if $sortField == 'guildName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.discordBotList.server{/lang}</a></th>
                    <th class="columnDate columnBotTime{if $sortField == 'botTime'} active {@$sortOrder}{/if}"><a href="{link controller='DiscordBotList'}pageNo={@$pageNo}&sortField=botTime&sortOrder={if $sortField == 'botTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.date{/lang}</a></th>

                    {event name='columns'}
                </tr>
            </thead>

            <tbody>
                {foreach from=$objects item=discordBot}
                    <tr class="jsRow">
                        <td class="columnIcon">
                            <a href="{link controller='DiscordBotEdit' id=$discordBot->botID}{/link}" title="{lang}wcf.global.button.edit{/lang}" class="jsTooltip"><span class="icon icon24 fa-pencil"></span></a>
							<a href="#" class="jsDeleteButton jsTooltip" title="{lang}wcf.global.button.delete{/lang}" data-confirm-message-html="{lang}wcf.acp.discordBotList.deleteRequest{/lang}" data-object-id="{@$discordBot->botID}"><span class="icon icon24 fa-times"></span></a>
                            <a href="#" class="jsConnectBot jsTooltip" title="{lang}wcf.acp.discordBotList.connectOnce{/lang}" data-object-id="{@$discordBot->botID}"><span class="icon icon24 fa-power-off"></span></a>

                            {event name='icons'}
                        </td>
                        <td class="columnID">
                            {#$discordBot->botID}
                        </td>
                        <td class="columnTitle">
                            {$discordBot->botName}
                        </td>
                        <td class="columnText">
                            {if !$discordBot->guildIcon|empty}
                                <img src="https://cdn.discordapp.com/icons/{$discordBot->guildID}/{$discordBot->guildIcon}.png" style="max-width: 32px; border-radius: 50%; margin-right: 10px;">
                            {/if}
                            {$discordBot->guildName}
                        </td>
                        <td class="columnDate">
                            {@$discordBot->botTime|time}
                        </td>

                        {event name='columnsItem'}
                    </tr>
                {/foreach}
            </tbody>
        </table>
    </div>

    <footer class="contentFooter">
		{hascontent}
			<div class="paginationBottom">
				{content}{@$pagesLinks}{/content}
			</div>
		{/hascontent}
		
		{hascontent}
			<nav class="contentFooterNavigation">
				<ul>
					{content}
						{event name='contentFooterNavigation'}
					{/content}
				</ul>
			</nav>
		{/hascontent}
	</footer>
{else}
	<p class="info">{lang}wcf.global.noItems{/lang}</p>
{/if}

<script data-relocate="true">
    require(['Hanashi/Acp/Discord/Tester', 'Language'], function ({ DiscordTester }, Language) {
        Language.addObject({
            'wcf.acp.discordBotList.connectOnce': '{jslang}wcf.acp.discordBotList.connectOnce{/jslang}',
            'wcf.acp.discordBotList.gateway.connecting': '{jslang}wcf.acp.discordBotList.gateway.connecting{/jslang}',
            'wcf.acp.discordBotList.gateway.connected': '{jslang}wcf.acp.discordBotList.gateway.connected{/jslang}',
            'wcf.acp.discordBotList.gateway.error': '{jslang}wcf.acp.discordBotList.gateway.error{/jslang}'
        });
        
        new DiscordTester();
    });
</script>

{include file='footer'}