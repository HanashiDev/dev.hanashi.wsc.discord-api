var __createBinding = (this && this.__createBinding) || (Object.create ? (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    var desc = Object.getOwnPropertyDescriptor(m, k);
    if (!desc || ("get" in desc ? !m.__esModule : desc.writable || desc.configurable)) {
      desc = { enumerable: true, get: function() { return m[k]; } };
    }
    Object.defineProperty(o, k2, desc);
}) : (function(o, m, k, k2) {
    if (k2 === undefined) k2 = k;
    o[k2] = m[k];
}));
var __setModuleDefault = (this && this.__setModuleDefault) || (Object.create ? (function(o, v) {
    Object.defineProperty(o, "default", { enumerable: true, value: v });
}) : function(o, v) {
    o["default"] = v;
});
var __importStar = (this && this.__importStar) || function (mod) {
    if (mod && mod.__esModule) return mod;
    var result = {};
    if (mod != null) for (var k in mod) if (k !== "default" && Object.prototype.hasOwnProperty.call(mod, k)) __createBinding(result, mod, k);
    __setModuleDefault(result, mod);
    return result;
};
var __importDefault = (this && this.__importDefault) || function (mod) {
    return (mod && mod.__esModule) ? mod : { "default": mod };
};
define(["require", "exports", "../../Discord/Gateway", "WoltLabSuite/Core/Ajax", "WoltLabSuite/Core/Dom/Util", "WoltLabSuite/Core/Event/Handler", "WoltLabSuite/Core/Language", "WoltLabSuite/Core/Ui/Dialog"], function (require, exports, Gateway_1, Ajax, DomUtil, EventHandler, Language, UiDialog) {
    "use strict";
    Object.defineProperty(exports, "__esModule", { value: true });
    exports.DiscordTester = void 0;
    Gateway_1 = __importDefault(Gateway_1);
    Ajax = __importStar(Ajax);
    DomUtil = __importStar(DomUtil);
    EventHandler = __importStar(EventHandler);
    Language = __importStar(Language);
    UiDialog = __importStar(UiDialog);
    class DiscordTester {
        constructor() {
            this.connected = false;
            this.template = "";
            const jsConnectBots = document.getElementsByClassName("jsConnectBot");
            for (const key of Object.keys(jsConnectBots)) {
                jsConnectBots[key].addEventListener("click", (ev) => this.connectBotClicked(ev));
            }
        }
        connectBotClicked(ev) {
            if (ev.target == null || !(ev.target instanceof HTMLElement)) {
                return;
            }
            const target = ev.target.parentNode;
            if (target == null || !(target instanceof HTMLElement)) {
                return;
            }
            const objectID = target.getAttribute("data-object-id");
            Ajax.api(this, {
                parameters: {
                    data: {
                        botID: objectID,
                    },
                },
            });
        }
        testBotConnection(botToken) {
            this.template =
                '<div id="BotTestText"><span class="icon icon24 fa-spinner"></span> <span style="margin-left: 10px">' +
                    Language.get("wcf.acp.discordBotList.gateway.connecting") +
                    "</span></div>";
            UiDialog.destroy(this);
            UiDialog.open(this);
            EventHandler.add("dev.hanashi.wsc.discord.gateway", "dispatch", (data) => this.dispatchData(data));
            const presence = {
                game: {
                    name: "Bot Test",
                    type: 0,
                },
                status: "online",
                afk: false,
            };
            new Gateway_1.default(botToken, presence);
            setTimeout(() => this.connectionError(), 15 * 1000);
        }
        dispatchData(data) {
            if (data.t == "READY") {
                this.connected = true;
                const botTestText = document.getElementById("BotTestText");
                if (botTestText != null) {
                    botTestText.innerHTML =
                        '<div id="BotTestText"><span class="icon icon24 fa-check-circle green"></span> <span style="margin-left: 10px">' +
                            Language.get("wcf.acp.discordBotList.gateway.connected") +
                            "</span></div>";
                }
            }
        }
        connectionError() {
            if (!this.connected) {
                const botTestText = document.getElementById("BotTestText");
                if (botTestText != null) {
                    botTestText.innerHTML =
                        '<div id="BotTestText"><span class="icon icon24 fa-times-circle red"></span> <span style="margin-left: 10px">' +
                            Language.get("wcf.acp.discordBotList.gateway.error") +
                            "</span></div>";
                }
            }
        }
        _dialogSetup() {
            return {
                id: DomUtil.getUniqueId(),
                source: DomUtil.createFragmentFromHtml(this.template),
                options: {
                    title: Language.get("wcf.acp.discordBotList.connectOnce"),
                },
            };
        }
        _ajaxSuccess(data) {
            if (data.actionName == "getBotToken") {
                this.testBotConnection(data.returnValues.token);
            }
        }
        _ajaxSetup() {
            return {
                data: {
                    actionName: "getBotToken",
                    className: "wcf\\data\\discord\\bot\\DiscordBotAction",
                },
            };
        }
    }
    exports.DiscordTester = DiscordTester;
    exports.default = DiscordTester;
});
