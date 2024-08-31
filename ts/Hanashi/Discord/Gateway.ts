/* eslint-disable @typescript-eslint/no-unsafe-assignment */
/* eslint-disable @typescript-eslint/no-unsafe-member-access */
import * as EventHandler from "WoltLabSuite/Core/Event/Handler";

/**
 * @deprecated
 */
export type GatewayEvent = {
  op: number;
  // eslint-disable-next-line @typescript-eslint/no-explicit-any
  d: any;
  s?: number;
  t?: string;
};

/**
 * @deprecated
 */
export type PresenceActivity = {
  name: string;
  type: number;
};

/**
 * @deprecated
 */
export type PresenceUpdate = {
  since?: number;
  activities: PresenceActivity[];
  status: string;
  afk: boolean;
};

/**
 * @deprecated
 */
export class DiscordGateway {
  private readonly token: string;
  private readonly intents: number;
  private readonly presence: PresenceUpdate | undefined;

  protected socket: WebSocket;

  private heartbeatInterval = 0;
  private seq: number | null = null;

  constructor(token: string, intents = 3276799, presence: PresenceUpdate | undefined) {
    this.token = token;
    this.intents = intents;
    this.presence = presence;

    this.socket = new WebSocket("wss://gateway.discord.gg/?v=10&encoding=json");
    this.socket.addEventListener("open", () => {
      this.onopen();
    });
    this.socket.addEventListener("error", (ev: Event) => {
      this.onerror(ev);
    });
    this.socket.addEventListener("message", (ev: MessageEvent) => {
      this.onmessage(ev);
    });

    const data = {
      token: this.token,
      socket: this.socket,
    };
    EventHandler.fire("dev.hanashi.wsc.discord.gateway", "init", data);
  }

  protected onopen(): void {
    EventHandler.fire("dev.hanashi.wsc.discord.gateway", "onopen");
  }

  protected onerror(ev: Event): void {
    EventHandler.fire("dev.hanashi.wsc.discord.gateway", "onerror", ev);
  }

  protected onmessage(ev: MessageEvent): void {
    if (ev.data === undefined || typeof ev.data !== "string") {
      return;
    }
    const data = JSON.parse(ev.data) as GatewayEvent;

    switch (data.op) {
      case 0: {
        if (data.s !== undefined) {
          this.seq = data.s;
        }
        EventHandler.fire("dev.hanashi.wsc.discord.gateway", "dispatch", data);
        break;
      }
      case 10: {
        // heartbeating
        if (data.d.heartbeat_interval !== undefined && typeof data.d.heartbeat_interval === "number") {
          this.heartbeatInterval = data.d.heartbeat_interval;
          this.sendHeartbeat();
        }

        EventHandler.fire("dev.hanashi.wsc.discord.gateway", "beforeVerify", data);

        // verify
        this.verify();

        EventHandler.fire("dev.hanashi.wsc.discord.gateway", "afterVerify", data);
        break;
      }
      case 11: {
        this.sendHeartbeat();
        break;
      }
      default: {
        EventHandler.fire("dev.hanashi.wsc.discord.gateway", "unknownReceived", data);
      }
    }
  }

  private sendHeartbeat() {
    setTimeout(() => {
      this.heartbeat();
    }, this.heartbeatInterval);
  }

  private heartbeat() {
    const sendData: GatewayEvent = {
      op: 1,
      d: this.seq,
    };
    this.socket.send(JSON.stringify(sendData));
  }

  private verify() {
    const sendData: GatewayEvent = {
      op: 2,
      d: {
        token: this.token,
        properties: {},
        presence: this.presence,
        intents: this.intents,
      },
    };
    this.socket.send(JSON.stringify(sendData));
  }
}

export default DiscordGateway;
