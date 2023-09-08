{include file='header' pageTitle='wcf.acp.menu.link.configuration.discord.discordWebhookList'}

<script data-relocate="true">
	$(function() {
		new WCF.Action.Delete('wcf\\data\\discord\\webhook\\DiscordWebhookAction', $('.jsRow'));
	});
</script>

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.configuration.discord.discordWebhookList{/lang}</h1>
	</div>
</header>

{hascontent}
	<div class="paginationTop">
		{content}
			{pages print=true assign=pagesLinks controller='DiscordWebhookList' link="pageNo=%d&sortField=$sortField&sortOrder=$sortOrder"}
		{/content}
	</div>
{/hascontent}

{if $objects|count}
	<div class="section tabularBox">
		<table class="table">
			<thead>
				<tr>
					<th class="columnIcon"></th>
					<th class="columnText columnWebhookID{if $sortField == 'webhookID'} active {@$sortOrder}{/if}"><a href="{link controller='DiscordWebhookList'}pageNo={@$pageNo}&sortField=webhookID&sortOrder={if $sortField == 'webhookID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnChannelID{if $sortField == 'channelID'} active {@$sortOrder}{/if}"><a href="{link controller='DiscordWebhookList'}pageNo={@$pageNo}&sortField=channelID&sortOrder={if $sortField == 'channelID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.discordWebhookList.channelID{/lang}</a></th>
					<th class="columnText columnWebhookTitle{if $sortField == 'webhookTitle'} active {@$sortOrder}{/if}"><a href="{link controller='DiscordWebhookList'}pageNo={@$pageNo}&sortField=webhookTitle&sortOrder={if $sortField == 'webhookTitle' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.discordWebhookList.webhookTitle{/lang}</a></th>
					<th class="columnText columnWebhook-Name{if $sortField == 'webhookName'} active {@$sortOrder}{/if}"><a href="{link controller='DiscordWebhookList'}pageNo={@$pageNo}&sortField=webhookName&sortOrder={if $sortField == 'webhookName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.discordWebhookList.webhookName{/lang}</a></th>
					<th class="columnText columnBotID{if $sortField == 'botID'} active {@$sortOrder}{/if}"><a href="{link controller='DiscordWebhookList'}pageNo={@$pageNo}&sortField=botID&sortOrder={if $sortField == 'botID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.discordBotList.server{/lang}</a></th>
					<th class="columnDate columnWebhookTime{if $sortField == 'webhookTime'} active {@$sortOrder}{/if}"><a href="{link controller='DiscordWebhookList'}pageNo={@$pageNo}&sortField=webhookTime&sortOrder={if $sortField == 'webhookTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.date{/lang}</a></th>

					{event name='columns'}
				</tr>
			</thead>

			<tbody>
				{foreach from=$objects item=webhook}
					<tr class="jsRow">
						<td class="columnIcon">
							<a href="#" class="jsDeleteButton jsTooltip" title="{lang}wcf.global.button.delete{/lang}" data-confirm-message-html="{lang}wcf.acp.discordWebhookList.deleteRequest{/lang}" data-object-id="{@$webhook->webhookID}">{icon size=16 name='times'}</a>

							{event name='icons'}
						</td>
						<td class="webhookID">
							{$webhook->webhookID}
						</td>
						<td class="webhookID">
							{$webhook->channelID}
						</td>
						<td class="columnText">
							{$webhook->webhookTitle}
						</td>
						<td class="columnText">
							{$webhook->webhookName}
						</td>
						<td class="columnText">
							{if !$webhook->getDiscordBot()->guildIcon|empty}
								<img src="https://cdn.discordapp.com/icons/{$webhook->getDiscordBot()->guildID}/{$webhook->getDiscordBot()->guildIcon}.png" style="max-width: 32px; border-radius: 50%; margin-right: 10px;">
							{/if}
							{$webhook->getDiscordBot()->guildName}
						</td>
						<td class="columnDate">
							{@$webhook->webhookTime|time}
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

{include file='footer'}