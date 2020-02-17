{include file='header' pageTitle='wcf.acp.menu.link.configuration.discord.discordServer.'|concat:$action}

<header class="contentHeader">
	<div class="contentHeaderTitle">
		<h1 class="contentTitle">{lang}wcf.acp.menu.link.configuration.discord.discordServer.{$action}{/lang}</h1>
	</div>

    <nav class="contentHeaderNavigation">
		<ul>
			<li><a href="{link controller='DiscordServerList'}{/link}" class="button"><span class="icon icon16 fa-list"></span> <span>{lang}wcf.acp.menu.link.configuration.discord.discordServerList{/lang}</span></a></li>
			{event name='contentHeaderNavigation'}
		</ul>
	</nav>
</header>

{include file='formError'}

{if $success|isset}
	<p class="success">{lang}wcf.global.success.{$action}{/lang}</p>
{/if}

<form method="post" action="{if $action == 'add'}{link controller='DiscordServerAdd'}{/link}{else}{link controller='DiscordServerEdit' id=$discordServerID}{/link}{/if}"  enctype="multipart/form-data">
	<section class="section">
        <h2 class="sectionTitle">Servereinstellung</h2>
		<dl{if $errorField == 'guildID'} class="formError"{/if}>
			<dt><label for="guildID">{lang}wcf.acp.discordBotAdd.guildID{/lang}</label></dt>
			<dd>
				<input type="number" name="guildID" id="guildID" value="{$guildID}" class="long" required>
				{if $errorField == 'guildID'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else if $errorType == 'invalid'}
							{lang}wcf.acp.discordBotAdd.guildID.error.invalid{/lang}
						{else if $errorType == 'permission_denied'}
							{lang}wcf.acp.discordBotAdd.guildID.error.permissionDenied{/lang}
						{/if}
					</small>
				{/if}
                <small>{lang}wcf.acp.discordBotAdd.guildID.description{/lang}</small>
			</dd>
		</dl>

        {event name='generalSettings'}
    </section>

	<section class="section">
        <h2 class="sectionTitle">{lang}wcf.acp.discordBotAdd.webhookSettings{/lang}</h2>
		<dl{if $errorField == 'webhookName'} class="formError"{/if}>
			<dt><label for="webhookName">{lang}wcf.acp.discordBotAdd.webhookName{/lang}</label></dt>
			<dd>
				<input type="text" name="webhookName" id="webhookName" value="{$webhookName}" class="long" required>
				{if $errorField == 'webhookName'}
					<small class="innerError">
						{if $errorType == 'empty'}
							{lang}wcf.global.form.error.empty{/lang}
						{else if $errorType == 'tooLong'}
							{lang}wcf.acp.discordBotAdd.webhookName.error.tooLong{/lang}
						{/if}
					</small>
				{/if}
                <small>{lang}wcf.acp.discordBotAdd.webhookName.description{/lang}</small>
			</dd>
		</dl>
		<dl{if $errorField == 'webhookIcon'} class="formError"{/if}>
			<dt><label for="webhookIcon">{lang}wcf.acp.discordBotAdd.webhookIcon{/lang}</label></dt>
			<dd>
				<input type="file" name="webhookIcon" id="webhookIcon">
				{if $errorField == 'webhookIcon'}
					<small class="innerError">
						{if $errorType == 'tooBig'}
							{lang}wcf.acp.discordBotAdd.webhookIcon.error.tooBig{/lang}
						{else if $errorType == 'unknownFormat'}
							{lang}wcf.acp.discordBotAdd.webhookIcon.error.unknownFormat{/lang}
						{/if}
					</small>
				{/if}
                <small>{lang}wcf.acp.discordBotAdd.webhookIcon.description{/lang}</small>
			</dd>
		</dl>

        {event name='webhookSettings'}
	</section>

    {event name='moreSections'}

    <div class="formSubmit">
		<input type="submit" value="{lang}wcf.global.button.submit{/lang}" accesskey="s">
		{@SECURITY_TOKEN_INPUT_TAG}
	</div>
</form>

{include file='footer'}