<?php

namespace wcf\system\gridView\admin;

use Override;
use wcf\acp\form\DiscordBotEditForm;
use wcf\data\DatabaseObjectList;
use wcf\data\discord\bot\DiscordBot;
use wcf\data\discord\bot\DiscordBotList;
use wcf\system\gridView\AbstractGridView;
use wcf\system\gridView\filter\TextFilter;
use wcf\system\gridView\filter\TimeFilter;
use wcf\system\gridView\GridViewColumn;
use wcf\system\gridView\renderer\DefaultColumnRenderer;
use wcf\system\gridView\renderer\ObjectIdColumnRenderer;
use wcf\system\gridView\renderer\TimeColumnRenderer;
use wcf\system\interaction\admin\DiscordBotInteractions;
use wcf\system\interaction\Divider;
use wcf\system\interaction\EditInteraction;
use wcf\system\WCF;

final class DiscordBotGridView extends AbstractGridView
{
    public function __construct()
    {
        $this->addColumns([
            GridViewColumn::for('botID')
                ->label('wcf.global.objectID')
                ->renderer(new ObjectIdColumnRenderer())
                ->sortable(),
            GridViewColumn::for('botName')
                ->label('wcf.acp.discordBotList.botName')
                ->sortable()
                ->titleColumn()
                ->filter(new TextFilter()),
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
                ->sortable()
                ->filter(new TextFilter()),
            GridViewColumn::for('botTime')
                ->label('wcf.global.date')
                ->renderer(new TimeColumnRenderer())
                ->sortable()
                ->filter(new TimeFilter()),
        ]);

        $provider = new DiscordBotInteractions();
        $provider->addInteractions([
            new Divider(),
            new EditInteraction(DiscordBotEditForm::class),
        ]);
        $this->setInteractionProvider($provider);

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
