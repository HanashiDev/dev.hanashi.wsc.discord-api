<div class="discordColorOption">
    <div class="discordColorWrapper">
        <div class="jsShopDiscordColorPicker{$option->optionName} discordColorPicker" style="background-color: {$value};" data-color="{$value}" data-store="{$option->optionName}"></div>
        <input type="hidden" id="{$option->optionName}" name="values[{$option->optionName}]" value="{$value}">
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

        UiColorPicker.fromSelector('.jsShopDiscordColorPicker{$option->optionName}');
    });
</script>
