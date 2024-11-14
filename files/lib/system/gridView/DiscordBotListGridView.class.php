<?php

namespace wcf\system\gridView;

use Override;
use wcf\data\DatabaseObjectList;
use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\bot\DiscordBotList;
use wcf\system\gridView\action\DeleteAction;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\NumberColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\gridView\renderer\TitleColumnRenderer;
use wcf\system\WCF;

final class DiscordBotListGridView extends DatabaseObjectListGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('botID')
                ->label('wcf.global.objectID')
                ->renderer(new NumberColumnRenderer())
                ->sortable(),
            GridViewColumn::for('botName')
                ->label('wcf.acp.discordBotList.botName')
                ->renderer(new TitleColumnRenderer())
                ->sortable(),
            GridViewColumn::for('guildName')
                ->label('wcf.acp.discordBotList.server')
                ->renderer([
                    new class extends DefaultColumnRenderer {
                        public function render(mixed $value, mixed $context = null): string
                        {
                            \assert($context instanceof DiscordBot);

                            $content = '';
                            if (!empty($context->guildIcon)) {
                                $content = \sprintf(
                                    '<img
                                        src="https://cdn.discordapp.com/icons/%s/%s.png"
                                        style="max-width: 32px; border-radius: 50%%; margin-right: 10px;"
                                    >',
                                    $context->guildID,
                                    $context->guildIcon
                                );
                            }

                            return $content . $context->guildName;
                        }
                    },
                ])
                ->sortable(),
            GridViewColumn::for('botTime')
                ->label('wcf.global.date')
                ->renderer(new TimeColumnRenderer()),
        ]);

        $this->addActions([
            new DeleteAction('hanashi/discord/bot/%s'),
        ]);
        $this->setSortField('botID');
        $this->setSortOrder('ASC');
    }

    #[Override]
    public function isAccessible(): bool
    {
        return WCF::getSession()->getPermission('admin.discord.canManageConnection');
    }

    #[Override]
    protected function createObjectList(): DatabaseObjectList
    {
        return new DiscordBotList();
    }
}
