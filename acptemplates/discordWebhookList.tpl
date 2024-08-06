{include file='header' pageTitle='wcf.acp.menu.link.configuration.discord.discordWebhookList'}

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
		<table class="table jsObjectActionContainer"  data-object-action-class-name="wcf\data\discord\webhook\DiscordWebhookAction">
			<thead>
				<tr>
					<th class="columnIcon"></th>
					<th class="columnText columnWebhookID{if $sortField == 'webhookID'} active {unsafe:$sortOrder}{/if}"><a href="{link controller='DiscordWebhookList'}pageNo={unsafe:$pageNo}&sortField=webhookID&sortOrder={if $sortField == 'webhookID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.objectID{/lang}</a></th>
					<th class="columnText columnChannelID{if $sortField == 'channelID'} active {unsafe:$sortOrder}{/if}"><a href="{link controller='DiscordWebhookList'}pageNo={unsafe:$pageNo}&sortField=channelID&sortOrder={if $sortField == 'channelID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.discordWebhookList.channelID{/lang}</a></th>
					<th class="columnText columnWebhookTitle{if $sortField == 'webhookTitle'} active {unsafe:$sortOrder}{/if}"><a href="{link controller='DiscordWebhookList'}pageNo={unsafe:$pageNo}&sortField=webhookTitle&sortOrder={if $sortField == 'webhookTitle' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.discordWebhookList.webhookTitle{/lang}</a></th>
					<th class="columnText columnWebhook-Name{if $sortField == 'webhookName'} active {unsafe:$sortOrder}{/if}"><a href="{link controller='DiscordWebhookList'}pageNo={unsafe:$pageNo}&sortField=webhookName&sortOrder={if $sortField == 'webhookName' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.discordWebhookList.webhookName{/lang}</a></th>
					<th class="columnText columnBotID{if $sortField == 'botID'} active {unsafe:$sortOrder}{/if}"><a href="{link controller='DiscordWebhookList'}pageNo={unsafe:$pageNo}&sortField=botID&sortOrder={if $sortField == 'botID' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.acp.discordBotList.server{/lang}</a></th>
					<th class="columnDate columnWebhookTime{if $sortField == 'webhookTime'} active {unsafe:$sortOrder}{/if}"><a href="{link controller='DiscordWebhookList'}pageNo={unsafe:$pageNo}&sortField=webhookTime&sortOrder={if $sortField == 'webhookTime' && $sortOrder == 'ASC'}DESC{else}ASC{/if}{/link}">{lang}wcf.global.date{/lang}</a></th>

					{event name='columns'}
				</tr>
			</thead>

			<tbody>
				{foreach from=$objects item=webhook}
					<tr class="jsObjectActionObject" data-object-id="{$webhook->webhookID}">
						<td class="columnIcon">
							{objectAction action="delete" objectTitle=$webhook->webhookName}

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
							{time time=$webhook->webhookTime}
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
				{content}{unsafe:$pagesLinks}{/content}
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