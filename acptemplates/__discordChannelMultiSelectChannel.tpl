<li>
	<label>
		<input type="checkbox" name="values[{$optionName}][{$botID}][]" value="{$guildChannel['id']}"{if !$value[$botID]|empty && $value[$botID]|is_array && $guildChannel['id']|in_array:$value[$botID]} checked{/if}>
		{if $guildChannel['type'] == 0}
			<span class="icon icon16 fa-hashtag"></span>
		{else if $guildChannel['type'] == 2}
			<span class="icon icon16 fa-volume-up"></span>
		{else if $guildChannel['type'] == 5}
			<span class="icon icon16 fa-bullhorn"></span>
		{else if $guildChannel['type'] == 13}
			<span class="icon icon16 fa-podcast"></span>
		{else if $guildChannel['type'] == 14}
			<span class="icon icon16 fa-compass"></span>
		{else if $guildChannel['type'] == 15}
			<span class="icon icon16 fa-comments-o"></span>
		{/if}
		{$guildChannel['name']}
	</label>
</li>