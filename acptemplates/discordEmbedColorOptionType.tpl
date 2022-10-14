<div class="discordColorOption">
	<div class="discordColorWrapper">
		<div class="jsDiscordColorPicker{$optionName} discordColorPicker" style="background-color: {$value};" data-color="{$value}" data-store="{$optionName}"></div>
		<input type="hidden" id="{$optionName}" name="values[{$optionName}]" value="{$value}">
	</div>
</div>

<script data-relocate="true">
	require(['WoltLabSuite/Core/Ui/Color/Picker', 'Language'], function(UiColorPicker, Language) {
		Language.addObject({
			'wcf.style.colorPicker': '{jslang}wcf.style.colorPicker{/jslang}',
			'wcf.style.colorPicker.new': '{jslang}wcf.style.colorPicker.new{/jslang}',
			'wcf.style.colorPicker.current': '{jslang}wcf.style.colorPicker.current{/jslang}',
			'wcf.style.colorPicker.button.apply': '{jslang}wcf.style.colorPicker.button.apply{/jslang}'
		});

		UiColorPicker.fromSelector('.jsDiscordColorPicker{$optionName}');
	});
</script>
