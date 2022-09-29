{include file='header' pageTitle='wcf.acp.menu.link.configuration.discord.discordBotList.add'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.configuration.discord.discordBotList.add{/lang}</h1>
	</div>

	<nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='DiscordBotList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.configuration.discord.discordBotList{/lang}</span></a></li>
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{if $step == 6}
	<div class="success">{lang}wcf.acp.discordBotAddManager.success{/lang}</div>
{/if}

<div class="section">
	{if $step == 0}
		{lang}wcf.acp.discordBotAddManager.step0{/lang}
	{else if $step == 1}
		<h2 class="sectionTitle">{lang}wcf.acp.discordBotAddManager.title{/lang}</h2>

		{lang}wcf.acp.discordBotAddManager.step1{/lang}
	{else if $step == 2}
		<h2 class="sectionTitle">{lang}wcf.acp.discordBotAddManager.title{/lang}</h2>

		{lang}wcf.acp.discordBotAddManager.step2Intro{/lang}<br><br>

		<a href="https://discord.com/api/oauth2/authorize?client_id={$tempInfo['id']}&amp;permissions=8&amp;scope=bot%20applications.commands" class="button" target="_blank">
			<span class="icon icon16 fa-external-link"></span>
			{lang}wcf.acp.discordBotAddManager.step2Invite{/lang}
		</a><br><br>

		{lang}wcf.acp.discordBotAddManager.step2Outro{/lang}
	{else if $step == 3}
		<h2 class="sectionTitle">{lang}wcf.acp.discordBotAddManager.title{/lang}</h2>

		{lang}wcf.acp.discordBotAddManager.step3{/lang}
	{else if $step == 4}
		<h2 class="sectionTitle">{lang}wcf.acp.discordBotAddManager.title{/lang}</h2>

		{lang}wcf.acp.discordBotAddManager.step4{/lang}
	{else if $step == 5}
		<h2 class="sectionTitle">{lang}wcf.acp.discordBotAddManager.title{/lang}</h2>

		{lang}wcf.acp.discordBotAddManager.step5{/lang}
	{else if $step == 6}
		<h2 class="sectionTitle">{lang}wcf.acp.discordBotAddManager.title{/lang}</h2>

		{lang}wcf.acp.discordBotAddManager.step6Intro{/lang}<br><br>

		{if $discordSyncInstalled}
			{lang}wcf.acp.discordBotAddManager.step6DiscordSync{/lang}<br><br>
		{/if}

		{lang}wcf.acp.discordBotAddManager.step6Outro{/lang}
	{/if}
</div>
{if $step != 6 && $step != 0}
	{@$form->getHtml()}
{/if}
{if $step == 0}
	<div class="formSubmit">
		<a href="{link controller='DiscordBotAddManager'}step=1{/link}" class="button buttonPrimary">{lang}wcf.acp.discordBotAddManager.configurationAssistant{/lang}</a>
		<a href="{link controller='DiscordBotAdd'}{/link}" class="button">{lang}wcf.acp.discordBotAddManager.manualConfiguration{/lang}</a>
	</div>
{else if $step == 6}
	<div class="formSubmit">
		<a href="{link controller='DiscordBotList'}{/link}" class="button buttonPrimary">{lang}wcf.acp.discordBotAddManager.done{/lang}</a>
	</div>
{/if}

{include file='footer'}
