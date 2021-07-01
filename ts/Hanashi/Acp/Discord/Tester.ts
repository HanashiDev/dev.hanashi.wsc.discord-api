import DiscordGateway from "../../Discord/Gateway";
import * as Ajax from "WoltLabSuite/Core/Ajax";
import { AjaxCallbackSetup, AjaxCallbackObject, ResponseData } from "WoltLabSuite/Core/Ajax/Data";
import * as DomUtil from "WoltLabSuite/Core/Dom/Util";
import * as EventHandler from "WoltLabSuite/Core/Event/Handler";
import * as Language from "WoltLabSuite/Core/Language";
import * as UiDialog from "WoltLabSuite/Core/Ui/Dialog";
import { DialogCallbackObject, DialogCallbackSetup } from "@woltlab/wcf/ts/WoltLabSuite/Core/Ui/Dialog/Data";

export class DiscordTester implements AjaxCallbackObject, DialogCallbackObject {
    private connected: boolean = false;
    private template: string = '';

    constructor() {
        const jsConnectBots: HTMLCollectionOf<Element> = document.getElementsByClassName('jsConnectBot');
        
        for (const key of Object.keys(jsConnectBots)) {
            jsConnectBots[key].addEventListener('click', (ev) => this.connectBotClicked(ev));
        }
    }

    protected connectBotClicked(ev: any): void {
        const target = ev.target.parentNode;
        const objectID = target.getAttribute('data-object-id');

        Ajax.api(this, {
            parameters: {
                data: {
                    botID: objectID
                }
            }
        });
    }

    protected testBotConnection(botToken: string): void {
        this.template = '<div id="BotTestText"><span class="icon icon24 fa-spinner"></span> <span style="margin-left: 10px">' + Language.get('wcf.acp.discordBotList.gateway.connecting') + '</span></div>';
        UiDialog.destroy(this);
        UiDialog.open(this);
        
        EventHandler.add('dev.hanashi.wsc.discord.gateway', 'dispatch', (data) => this.dispatchData(data));
        const presence = {
            "game": {
                "name": "Bot Test",
                "type": 0
            },
            "status": "online",
            "afk": false
        };
        new DiscordGateway(botToken, presence);
        setTimeout(() => this.connectionError, 15 * 1000);
    }

    protected dispatchData(data: any) {
        if (data.t == 'READY') {
            this.connected = true;
            const botTestText = document.getElementById('BotTestText');
            if (botTestText != null) {
                botTestText.innerHTML = '<div id="BotTestText"><span class="icon icon24 fa-check-circle green"></span> <span style="margin-left: 10px">' + Language.get('wcf.acp.discordBotList.gateway.connected') + '</span></div>';
            }
        }
    }

    protected connectionError() {
        if (!this.connected) {
            const botTestText = document.getElementById('BotTestText');
            if (botTestText != null) {
                botTestText.innerHTML = '<div id="BotTestText"><span class="icon icon24 fa-times-circle red"></span> <span style="margin-left: 10px">' + Language.get('wcf.acp.discordBotList.gateway.error') + '</span></div>';
            }
        }
    }

    public _dialogSetup(): ReturnType<DialogCallbackSetup> {
        return {
            id: DomUtil.getUniqueId(),
            source: DomUtil.createFragmentFromHtml(this.template),
            options: {
                title: Language.get('wcf.acp.discordBotList.connectOnce')
            }
        }
    }

    public _ajaxSuccess(data: ResponseData): void {
        if (data.actionName == 'getBotToken') {
            this.testBotConnection(data.returnValues.token);
        }
    }

    public _ajaxSetup(): ReturnType<AjaxCallbackSetup> {
        return {
            data: {
                actionName: 'getBotToken',
                className: 'wcf\\data\\discord\\bot\\DiscordBotAction'
            }
        };
    }
}

export default DiscordTester;
