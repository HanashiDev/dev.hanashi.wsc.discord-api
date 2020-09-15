{if $bots|count > 1}
    <div class="section tabMenuContainer" data-active="{$option->optionName}" data-store="activeTabMenuItem">
        <div id="{$option->optionName}" class="tabMenuContainer tabMenuContent">
            <nav class="menu">
                <ul>
                    {foreach from=$bots item=$bot}
                        <li>
                            <a href="#{$option->optionName}-{$bot['botID']}">{$bot['botName']}</a>
                        </li>
                    {/foreach}
                </ul>
            </nav>
            {foreach from=$bots item=$bot}
                <div id="{$option->optionName}-{$bot['botID']}" class="tabMenuContent hidden" data-name="{$option->optionName}-{$bot['botID']}">
                    <div class="section">
                        <select id="{$option->optionName}" name="values[{$option->optionName}][{$bot['botID']}]">
                            <option></option>
                            {foreach from=$bot['channels'] item=channel}
                                {if $channel['type'] == 4}
                                    <optgroup label="{$channel['name']}">
                                        {foreach from=$channel['childs'] item=$childChannel}
                                            {if $childChannel['type'] == 0 || $childChannel['type'] == 5}
                                                <option value="{$childChannel['id']}"{if !$value[$bot['botID']]|empty && $childChannel['id'] == $value[$bot['botID']]} selected{/if}>{$childChannel['name']}</option>
                                            {/if}
                                        {/foreach}
                                    </optgroup>
                                {else if $channel['type'] == 0 || $channel['type'] == 5}
                                    <option value="{$channel['id']}"{if !$value[$bot['botID']]|empty && $channel['id'] == $value[$bot['botID']]} selected{/if}>{$channel['name']}</option>
                                {/if}
                            {/foreach}
                        </select>
                    </div>
                </div>
            {/foreach}
        </div>
    </div>
{else if $bots|count == 1}
    <select id="{$option->optionName}" name="values[{$option->optionName}][{$bots[0]['botID']}]">
        <option></option>
        {foreach from=$bots[0]['channels'] item=channel}
            {if $channel['type'] == 4}
                <optgroup label="{$channel['name']}">
                    {foreach from=$channel['childs'] item=$childChannel}
                        {if $childChannel['type'] == 0 || $childChannel['type'] == 5}
                            <option value="{$childChannel['id']}"{if !$value[$bots[0]['botID']]|empty && $childChannel['id'] == $value[$bots[0]['botID']]} selected{/if}>{$childChannel['name']}</option>
                        {/if}
                    {/foreach}
                </optgroup>
            {else if $channel['type'] == 0 || $channel['type'] == 5}
                <option value="{$channel['id']}"{if !$value[$bots[0]['botID']]|empty && $channel['id'] == $value[$bots[0]['botID']]} selected{/if}>{$channel['name']}</option>
            {/if}
        {/foreach}
    </select>
{else}
    <p class="info">{lang}wcf.acp.discordBotSelectOptionType.noBot{/lang}</p>
{/if}