{if $channelTypes|empty || $guildChannel['type']|in_array:$channelTypes}
	<li>
		<label>
			<input type="radio" name="values[{$optionName}][{$botID}]" value="{$guildChannel['id']}"{if !$value[$botID]|empty && $channel['id'] == $value[$botID]} checked{/if}>
			{if $guildChannel['type'] == 0}
				{icon size=16 name='hashtag'}
			{else if $guildChannel['type'] == 2}
				{icon size=16 name='volume-up'}
			{else if $guildChannel['type'] == 5}
				{icon size=16 name='bullhorn'}
			{else if $guildChannel['type'] == 13}
				{icon size=16 name='podcast'}
			{else if $guildChannel['type'] == 14}
				{icon size=16 name='compass'}
			{else if $guildChannel['type'] == 15}
				{icon size=16 name='comments-o'}
			{/if}
			{$guildChannel['name']}
		</label>
	</li>
{/if}
