{include file='header' pageTitle='wcf.acp.menu.link.configuration.discord.discordWebhookList'}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.configuration.discord.discordWebhookList{/lang}</h1>
	</div>
</header>

<div class="section">
	{unsafe:$gridView->render()}
</div>

{include file='footer'}